<?php
require_once __DIR__ . '/../public_html/app/config/env.php';
require_once __DIR__ . '/../public_html/app/config/database.php';

use App\Config\Env;
use App\Config\Database;

Env::load(__DIR__ . '/../public_html/.env');

try {
    $db = Database::getConnection();
    echo "Connected to database successfully.\n";

    $sql = "UPDATE request_types SET active = 0 WHERE code IN ('HS', 'TR', 'ED')";
    if ($db->query($sql)) {
        echo "Successfully deactivated legacy request types.\n";
    } else {
        echo "Failed to execute migration: " . $db->error . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
