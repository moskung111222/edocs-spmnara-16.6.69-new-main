<?php
// ==================================================================
// Integration Test: Homeschool Workflow Transitions
// ==================================================================
require_once __DIR__ . '/../public_html/app/config/env.php';
require_once __DIR__ . '/../public_html/app/config/database.php';

use App\Config\Env;
use App\Config\Database;

Env::load(__DIR__ . '/../public_html/.env');

try {
    $db = Database::getConnection();
} catch (Exception $e) {
    die("FAILED: Could not connect to DB: " . $e->getMessage() . "\n");
}

// 1. Get the latest request
$res = $db->query("SELECT id, request_no, process_1_status, process_2_status FROM requests ORDER BY created_at DESC LIMIT 1");
if (!$res || $res->num_rows === 0) {
    die("FAILED: No requests found to transition.\n");
}
$row = $res->fetch_assoc();
$requestId = $row['id'];
$requestNo = $row['request_no'];

echo "Found request: $requestNo (ID: $requestId). Current P1: {$row['process_1_status']}, P2: {$row['process_2_status']}\n";

$cookieFile = __DIR__ . '/cookies_workflow.txt';
if (file_exists($cookieFile)) {
    unlink($cookieFile);
}

// 2. Fetch admin login page to get CSRF token
echo "Step 1: Fetching admin login page...\n";
$ch = curl_init('http://localhost:8080/admin/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$response = curl_exec($ch);
curl_close($ch);

preg_match('/name="csrf_token" value="([^"]+)"/', $response, $matches);
$csrfToken = $matches[1] ?? '';
if (empty($csrfToken)) {
    die("FAILED: Could not extract CSRF token from login page.\n");
}

// 3. Login as admin
echo "Step 2: Logging in as admin...\n";
$postData = [
    'csrf_token' => $csrfToken,
    'username' => 'admin',
    'password' => 'admin123'
];
$ch = curl_init('http://localhost:8080/admin/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 302) {
    die("FAILED: Admin login failed. HTTP Code: $httpCode\n");
}
echo "Admin logged in successfully.\n";

// 4. Fetch request detail page to get CSRF token for the action
echo "Step 3: Fetching request detail page for ID: $requestId...\n";
$ch = curl_init("http://localhost:8080/admin/request?id=$requestId");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$response = curl_exec($ch);
$infoCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

preg_match('/name="csrf_token" value="([^"]+)"/', $response, $matches);
$csrfTokenAction = $matches[1] ?? '';
if (empty($csrfTokenAction)) {
    echo "DEBUG: HTTP Code was $infoCode\n";
    echo "DEBUG: Response snippet:\n" . substr(strip_tags($response), 0, 800) . "\n";
    die("FAILED: Could not extract CSRF token from request detail page.\n");
}

// 5. Submit change_process_1 action to mark Process 1 as completed
echo "Step 4: Transitioning Process 1 to completed...\n";
$actionData = [
    'csrf_token' => $csrfTokenAction,
    'action' => 'change_process_1',
    'process_1_status' => 'completed',
    'reason' => 'เอกสารและผลการประชุมครบถ้วนสมบูรณ์',
    'send_email' => '0'
];

$ch = curl_init("http://localhost:8080/admin/request?id=$requestId");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $actionData);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if (file_exists($cookieFile)) {
    unlink($cookieFile);
}

if ($httpCode !== 200) {
    die("FAILED: Workflow update POST returned HTTP code $httpCode.\n");
}

// 6. Verify database state
$res = $db->query("SELECT process_1_status, process_2_status, status FROM requests WHERE id = $requestId");
$row = $res->fetch_assoc();

echo "Post-transition state:\n";
echo "  - Process 1 status: {$row['process_1_status']} (Expected: completed)\n";
echo "  - Process 2 status: {$row['process_2_status']} (Expected: waiting_report)\n";
echo "  - Overall status  : {$row['status']} (Expected: completed)\n";

if ($row['process_1_status'] === 'completed' && $row['process_2_status'] === 'waiting_report') {
    echo "SUCCESS: Workflow transition verified successfully!\n";
    exit(0);
} else {
    die("FAILED: State transition mismatch!\n");
}
