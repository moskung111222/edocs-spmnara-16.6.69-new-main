<?php
// ==================================================================
// Integration Test: Tracking Search & Master OTP Bypass
// ==================================================================

$cookieFile = __DIR__ . '/cookies_track.txt';
if (file_exists($cookieFile)) {
    unlink($cookieFile);
}

$requestNo = 'NWT-HS-2569-000003';
echo "Step 1: Searching for request: $requestNo...\n";

$ch = curl_init('http://localhost:8080/request/track?no=' . urlencode($requestNo));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$response = curl_exec($ch);
curl_close($ch);

// Ensure it shows the security verification / OTP trigger step
if (strpos($response, 'การตรวจสอบความปลอดภัย') === false) {
    die("FAILED: Tracking search did not show security verification page.\n");
}
echo "Search success. Security check screen loaded.\n";

// Extract CSRF token
preg_match('/name="csrf_token" value="([^"]+)"/', $response, $matches);
$csrfToken = $matches[1] ?? '';
if (empty($csrfToken)) {
    die("FAILED: Could not extract CSRF token from security check screen.\n");
}
echo "CSRF Token extracted: $csrfToken\n";

echo "Step 2: Triggering Send OTP for tracking...\n";
$postDataSend = [
    'csrf_token' => $csrfToken,
    'send_track_otp' => '1'
];

$ch = curl_init('http://localhost:8080/request/track?no=' . urlencode($requestNo));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postDataSend);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$response = curl_exec($ch);
curl_close($ch);

if (strpos($response, 'ระบบได้ส่งรหัส OTP') === false) {
    die("FAILED: Did not trigger sending OTP successfully.\n");
}
echo "OTP triggered successfully. Now on OTP input screen.\n";

// Extract CSRF token from input screen
preg_match('/name="csrf_token" value="([^"]+)"/', $response, $matches);
$csrfTokenVerify = $matches[1] ?? '';
if (empty($csrfTokenVerify)) {
    die("FAILED: Could not extract CSRF token from OTP input screen.\n");
}

echo "Step 3: Submitting master OTP bypass code '123456'...\n";
$postDataVerify = [
    'csrf_token' => $csrfTokenVerify,
    'verify_track_otp' => '1',
    'otp_code' => '123456' // using master bypass code
];

$ch = curl_init('http://localhost:8080/request/track?no=' . urlencode($requestNo));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postDataVerify);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$response = curl_exec($ch);
curl_close($ch);

// Clean up
if (file_exists($cookieFile)) {
    unlink($cookieFile);
}

// Check if we reached the authenticated tracking page
if (strpos($response, $requestNo) === false) {
    echo "--- Response Snippet ---\n" . substr(strip_tags($response), 0, 1000) . "\n------------------------\n";
    die("FAILED: Master OTP bypass failed. Did not redirect to authenticated tracking page.\n");
}

echo "SUCCESS! Master OTP bypass '123456' works and loaded the tracking details page successfully.\n";
exit(0);
