<?php
/**
 * Quick smoke test: verifies all admin routes respond without 500 errors.
 * Run: php scratch/smoke_test.php
 */

// 1. Get a session cookie by logging in
$loginUrl = 'http://localhost:8080/admin/login';

// First, GET login page to get CSRF token
$ch = curl_init($loginUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, __DIR__ . '/cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, __DIR__ . '/cookies.txt');
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Extract CSRF token
preg_match('/name="csrf_token"\s+value="([^"]+)"/', $response, $matches);
$csrfToken = $matches[1] ?? '';
echo "Login page: HTTP $httpCode | CSRF: " . ($csrfToken ? 'Found' : 'NOT FOUND') . "\n";

if (!$csrfToken) {
    echo "ERROR: Cannot proceed without CSRF token.\n";
    exit(1);
}

// 2. POST login
$ch = curl_init($loginUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'username' => 'admin',
    'password' => 'admin123',
    'csrf_token' => $csrfToken
]));
curl_setopt($ch, CURLOPT_COOKIEJAR, __DIR__ . '/cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, __DIR__ . '/cookies.txt');
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Login POST: HTTP $httpCode";
if ($httpCode == 302 || $httpCode == 301) {
    preg_match('/Location:\s*(.+)/i', $response, $locMatch);
    echo " -> Redirect to: " . trim($locMatch[1] ?? 'unknown');
}
echo "\n";

// 3. Test all admin routes
$routes = [
    'admin/dashboard',
    'admin/departments',
    'admin/services',
    'admin/officers',
    'admin/roles',
];

foreach ($routes as $route) {
    $url = "http://localhost:8080/$route";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEFILE, __DIR__ . '/cookies.txt');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $body = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Check for 500 error text
    $has500 = (strpos($body, '500 ขออภัย') !== false || strpos($body, 'เกิดข้อผิดพลาด') !== false);
    $status = ($httpCode == 200 && !$has500) ? '✅ OK' : '❌ FAIL';
    
    echo "$status | HTTP $httpCode | /$route";
    if ($has500) {
        // Extract error message
        preg_match('/เกิดข้อผิดพลาด[^<]+/', $body, $errMatch);
        echo " | Error: " . ($errMatch[0] ?? 'Unknown 500');
    }
    echo "\n";
}

// Cleanup
@unlink(__DIR__ . '/cookies.txt');
echo "\nDone.\n";
