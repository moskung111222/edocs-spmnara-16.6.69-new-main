<?php
/**
 * Quick smoke test for public routes.
 */
$routes = [
    '/',
    '/request/create?type=1',
    '/request/create?type=2',
    '/request/create?type=3',
    '/request/track',
    '/request/verify',
    '/admin/login',
];

foreach ($routes as $route) {
    $url = "http://localhost:8080$route";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $body = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $has500 = (strpos($body, '500 ขออภัย') !== false || strpos($body, 'เกิดข้อผิดพลาด') !== false);
    $status = ($httpCode == 200 && !$has500) ? '✅ OK' : '❌ FAIL';
    
    echo "$status | HTTP $httpCode | $route";
    if ($has500) {
        preg_match('/เกิดข้อผิดพลาด[^<]+/', $body, $errMatch);
        echo " | Error: " . ($errMatch[0] ?? 'Unknown');
    }
    echo "\n";
}
echo "\nDone.\n";
