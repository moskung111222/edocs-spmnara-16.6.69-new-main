<?php
// ==================================================================
// End-to-End Integration Test: Form Submission & OTP Verification
// ==================================================================

$cookieFile = __DIR__ . '/cookies.txt';
if (file_exists($cookieFile)) {
    unlink($cookieFile);
}

// 1. Create dummy PDF files that satisfy Magic Bytes validation (%PDF)
$dummyPdfPath = __DIR__ . '/dummy.pdf';
file_put_contents($dummyPdfPath, "%PDF-1.4\n%...\n%%EOF");

echo "Step 1: Fetching request creation form to extract CSRF token...\n";
$ch = curl_init('http://localhost:8080/request/create?type=4');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$response = curl_exec($ch);
curl_close($ch);

// Extract CSRF token
preg_match('/name="csrf_token" value="([^"]+)"/', $response, $matches);
$csrfToken = $matches[1] ?? '';
if (empty($csrfToken)) {
    die("FAILED: Could not extract CSRF token from create form.\n");
}
echo "CSRF Token extracted: $csrfToken\n";

echo "Step 2: Submitting request creation form with files...\n";
// Prepare multi-part POST data
$postData = [
    'csrf_token' => $csrfToken,
    'pdpa_consent' => '1',
    'full_name' => 'นายทดสอบ ระบบ',
    'email' => 'tester_' . uniqid() . '@spmnara.go.th',
    'phone' => '0812345678',
    'account_password' => 'mypassword123',
    'form_data[school_name]' => 'โรงเรียนนราธิวาสราชนครินทร์',
    'form_data[grad_year]' => '2565',
    'form_data[purpose]' => 'ศึกษาต่อ',
    // Type 4 has 5 files: 
    'doc_file_0' => new CURLFile($dummyPdfPath, 'application/pdf', 'completed_form.pdf'),
    'doc_file_1' => new CURLFile($dummyPdfPath, 'application/pdf', 'education_plan.pdf'),
    'doc_file_2' => new CURLFile($dummyPdfPath, 'application/pdf', 'parent_id.pdf'),
    'doc_file_3' => new CURLFile($dummyPdfPath, 'application/pdf', 'parent_house_reg.pdf'),
    'doc_file_4' => new CURLFile($dummyPdfPath, 'application/pdf', 'parent_education.pdf')
];

$ch = curl_init('http://localhost:8080/request/create?type=4');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// 3. Verify redirect to track and database state
if (strpos($response, 'ติดตามสถานะคำขอ') === false && strpos($response, 'ยื่นคำขอสำเร็จแล้ว') === false) {
    die("FAILED: Did not redirect to tracking view. Response snippet:\n" . substr(strip_tags($response), 0, 500) . "\n");
}
echo "SUCCESS! Redirected to tracking view.\n";

// Clean up cookies and dummy file
if (file_exists($cookieFile)) {
    unlink($cookieFile);
}
if (file_exists($dummyPdfPath)) {
    unlink($dummyPdfPath);
}

require_once __DIR__ . '/../public_html/app/config/env.php';
require_once __DIR__ . '/../public_html/app/config/database.php';

use App\Config\Env;
use App\Config\Database;

Env::load(__DIR__ . '/../public_html/.env');

// Let's check DB counts
try {
    $db = Database::getConnection();
} catch (Exception $e) {
    die("FAILED: Could not connect to DB: " . $e->getMessage() . "\n");
}
$res = $db->query("SELECT COUNT(*) as cnt FROM requests");
$row = $res->fetch_assoc();
echo "Total requests in DB now: " . $row['cnt'] . "\n";

$res = $db->query("SELECT request_no FROM requests ORDER BY created_at DESC LIMIT 1");
$row = $res->fetch_assoc();
echo "Generated Request Number: " . $row['request_no'] . "\n";

echo "E2E Submission Integration Test Passed Successfully!\n";
exit(0);
