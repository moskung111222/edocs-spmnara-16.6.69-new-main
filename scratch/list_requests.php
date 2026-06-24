<?php
require_once __DIR__ . '/../public_html/app/config/env.php';
require_once __DIR__ . '/../public_html/app/config/database.php';

use App\Config\Env;
use App\Config\Database;

Env::load(__DIR__ . '/../public_html/.env');

try {
    $db = Database::getConnection();
    $res = $db->query("
        SELECT r.id, r.request_no, r.status, r.process_1_status, r.process_2_status,
               a.applicant_code, a.password_plain
        FROM requests r
        LEFT JOIN applicant_accounts a ON r.applicant_id = a.applicant_id
    ");
    if ($res) {
        echo "Found " . $res->num_rows . " requests:\n";
        while ($row = $res->fetch_assoc()) {
            echo "  - ID: {$row['id']}, No: {$row['request_no']}, Status: {$row['status']}\n";
            echo "    P1: {$row['process_1_status']}, P2: {$row['process_2_status']}\n";
            echo "    Applicant Code: " . ($row['applicant_code'] ?? 'N/A') . "\n";
            echo "    Password (สำหรับติดตามคำขอ): " . ($row['password_plain'] ?? 'N/A') . "\n\n";
        }
    } else {
        echo "No requests table found or query failed: " . $db->error . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
