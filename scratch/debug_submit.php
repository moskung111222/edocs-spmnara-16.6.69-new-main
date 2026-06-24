<?php
$cookieFile = __DIR__ . '/cookies_debug.txt';
if (file_exists($cookieFile)) unlink($cookieFile);

$dummyPdfPath = __DIR__ . '/dummy_debug.pdf';
file_put_contents($dummyPdfPath, "%PDF-1.4\n%...\n%%EOF");

echo "Fetching form...\n";
$ch = curl_init('http://localhost:8080/request/create?type=4');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$response = curl_exec($ch);
curl_close($ch);

preg_match('/name="csrf_token" value="([^"]+)"/', $response, $matches);
$csrfToken = $matches[1] ?? '';
echo "CSRF Token: $csrfToken\n";

$postData = [
    'csrf_token' => $csrfToken,
    'full_name' => 'นายทดสอบ ระบบ',
    'email' => 'tester@spmnara.go.th',
    'phone' => '0812345678',
    'form_data[school_name]' => 'โรงเรียนนราธิวาสราชนครินทร์',
    'form_data[grad_year]' => '2565',
    'form_data[purpose]' => 'ศึกษาต่อ',
    'doc_file_0' => new CURLFile($dummyPdfPath, 'application/pdf', 'completed_form.pdf'),
    'doc_file_1' => new CURLFile($dummyPdfPath, 'application/pdf', 'education_plan.pdf'),
    'doc_file_2' => new CURLFile($dummyPdfPath, 'application/pdf', 'parent_id.pdf'),
    'doc_file_3' => new CURLFile($dummyPdfPath, 'application/pdf', 'parent_house_reg.pdf'),
    'doc_file_4' => new CURLFile($dummyPdfPath, 'application/pdf', 'parent_education.pdf')
];

echo "Submitting form...\n";
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

echo "HTTP Code: $httpCode\n";
// Find any alert alert-danger or errors
if (preg_match('/<div class="alert alert-danger[^>]*>(.*?)<\/div>/is', $response, $errMatches)) {
    echo "Validation Error: " . trim(strip_tags($errMatches[1])) . "\n";
} else {
    echo "No standard alert-danger found. Outputting raw body snippet:\n";
    echo substr(strip_tags($response), 0, 1000) . "\n";
}

if (file_exists($cookieFile)) unlink($cookieFile);
if (file_exists($dummyPdfPath)) unlink($dummyPdfPath);
