<?php
// ==================================================================
// Integration Test: Tracking Search & Password Authentication
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

// 1. Get the latest request and password
$res = $db->query("
    SELECT r.request_no, a.password_plain 
    FROM requests r
    JOIN applicant_accounts a ON r.applicant_id = a.applicant_id
    ORDER BY r.created_at DESC
    LIMIT 1
");

if (!$res || $res->num_rows === 0) {
    die("FAILED: No requests or applicant accounts found in database.\n");
}

$row = $res->fetch_assoc();
$requestNo = $row['request_no'];
$password = $row['password_plain'];

echo "Found request: $requestNo with password: $password\n";

$cookieFile = __DIR__ . '/cookies_track_pwd.txt';
if (file_exists($cookieFile)) {
    unlink($cookieFile);
}

// 2. Fetch the tracking page to get the CSRF token
echo "Step 1: Fetching tracking page for: $requestNo...\n";
$ch = curl_init('http://localhost:8080/request/track?no=' . urlencode($requestNo));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$response = curl_exec($ch);
curl_close($ch);

// Extract CSRF token
preg_match('/name="csrf_token" value="([^"]+)"/', $response, $matches);
$csrfToken = $matches[1] ?? '';
if (empty($csrfToken)) {
    die("FAILED: Could not extract CSRF token from tracking screen.\n");
}
echo "CSRF Token extracted: $csrfToken\n";

// 3. Submit password verification
echo "Step 2: Submitting password verification...\n";
$postData = [
    'csrf_token' => $csrfToken,
    'verify_password' => '1',
    'password' => $password
];

$ch = curl_init('http://localhost:8080/request/track?no=' . urlencode($requestNo));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
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
    die("FAILED: HTTP Code was $httpCode, expected 200.\n");
}

if (strpos($response, $requestNo) === false) {
    echo "--- Response snippet ---\n" . substr(strip_tags($response), 0, 1000) . "\n------------------------\n";
    die("FAILED: Did not authenticate successfully to tracking page.\n");
}

echo "SUCCESS: Authenticated and tracked request successfully using password!\n";
exit(0);
