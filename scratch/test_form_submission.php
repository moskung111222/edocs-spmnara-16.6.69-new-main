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
$ch = curl_init('http://localhost:8080/request/create?type=1');
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
    'full_name' => 'นายทดสอบ ระบบ',
    'email' => 'tester@spmnara.go.th',
    'phone' => '0812345678',
    'form_data[school_name]' => 'โรงเรียนนราธิวาสราชนครินทร์',
    'form_data[grad_year]' => '2565',
    'form_data[purpose]' => 'ศึกษาต่อ',
    // Type 1 has 3 files: 
    'doc_file_0' => new CURLFile($dummyPdfPath, 'application/pdf', 'id_card.pdf'),
    'doc_file_1' => new CURLFile($dummyPdfPath, 'application/pdf', 'photo.pdf'),
    'doc_file_2' => new CURLFile($dummyPdfPath, 'application/pdf', 'police_report.pdf')
];

$ch = curl_init('http://localhost:8080/request/create?type=1');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// We should be redirected to the verify screen
if (strpos($response, 'ยืนยันตัวตนด้วยรหัส OTP') === false) {
    die("FAILED: Did not redirect to OTP verification screen. Response snippet:\n" . substr(strip_tags($response), 0, 500) . "\n");
}
echo "Redirected to OTP Verification Screen successfully.\n";

// 3. Extract CSRF token from verification screen
preg_match('/name="csrf_token" value="([^"]+)"/', $response, $matches);
$csrfTokenVerify = $matches[1] ?? '';
if (empty($csrfTokenVerify)) {
    die("FAILED: Could not extract CSRF token from verification form.\n");
}

echo "Step 3: Fetching OTP code directly from database...\n";
// We will query the DB using root to get the latest OTP sent to tester@spmnara.go.th
$db = new mysqli('127.0.0.1', 'root', 'Thi$i$spm15!', 'edocs_spmnara');
if ($db->connect_error) {
    die("FAILED: Could not connect to DB to fetch OTP: " . $db->connect_error . "\n");
}
$res = $db->query("SELECT otp_code FROM otp_verifications WHERE email = 'tester@spmnara.go.th' ORDER BY created_at DESC LIMIT 1");
$row = $res->fetch_assoc();
$otpCode = $row['otp_code'] ?? '';
$db->close();

if (empty($otpCode)) {
    die("FAILED: No OTP code generated in database for email tester@spmnara.go.th.\n");
}
echo "Retrieved OTP from database: $otpCode\n";

echo "Step 4: Submitting OTP verification code...\n";
$postDataVerify = [
    'csrf_token' => $csrfTokenVerify,
    'otp_code' => $otpCode
];

$ch = curl_init('http://localhost:8080/request/verify');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postDataVerify);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$response = curl_exec($ch);
curl_close($ch);

// Clean up cookies and dummy file
if (file_exists($cookieFile)) {
    unlink($cookieFile);
}
if (file_exists($dummyPdfPath)) {
    unlink($dummyPdfPath);
}

// 5. Verify redirect to track and database state
if (strpos($response, 'ยื่นคำขอเรียบร้อยแล้ว!') === false) {
    die("FAILED: Verification failed or did not redirect to tracking view. Response snippet:\n" . substr(strip_tags($response), 0, 500) . "\n");
}
echo "SUCCESS! OTP confirmed and redirected to tracking view.\n";

// Let's check DB counts
$db = new mysqli('127.0.0.1', 'root', 'Thi$i$spm15!', 'edocs_spmnara');
$res = $db->query("SELECT COUNT(*) as cnt FROM requests");
$row = $res->fetch_assoc();
echo "Total requests in DB now: " . $row['cnt'] . "\n";

$res = $db->query("SELECT request_no FROM requests ORDER BY created_at DESC LIMIT 1");
$row = $res->fetch_assoc();
echo "Generated Request Number: " . $row['request_no'] . "\n";
$db->close();

echo "E2E Submission Integration Test Passed Successfully!\n";
exit(0);
