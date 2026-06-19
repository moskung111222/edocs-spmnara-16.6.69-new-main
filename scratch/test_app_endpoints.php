<?php
// ==================================================================
// Automated Integration Test: Checking Application Endpoints
// ==================================================================

$endpoints = [
    'Home Page' => 'http://localhost:8080/',
    'Request Creation' => 'http://localhost:8080/request/create?type=1',
    'Request Tracking' => 'http://localhost:8080/request/track',
    'Admin Login' => 'http://localhost:8080/admin/login'
];

$failed = false;

foreach ($endpoints as $name => $url) {
    echo "Testing endpoint: $name ($url)... ";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        // Specifically look for PHP generated error messages
        if (preg_match('/(PHP Warning|PHP Notice|PHP Fatal error|Fatal error|Parse error)/', $response, $matches)) {
            echo "FAILED (Contains PHP issue: {$matches[0]})\n";
            $failed = true;
            echo "--- Snippet ---\n" . substr(strip_tags($response), 0, 300) . "\n---------------\n";
        } else {
            echo "OK (200)\n";
        }
    } else {
        echo "FAILED (HTTP Code: $httpCode)\n";
        $failed = true;
    }
}

if ($failed) {
    echo "Verification test failed.\n";
    exit(1);
} else {
    echo "All endpoints verified successfully!\n";
    exit(0);
}
