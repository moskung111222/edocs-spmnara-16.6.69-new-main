<?php
require_once __DIR__ . '/../public_html/app/config/env.php';
require_once __DIR__ . '/../public_html/app/config/database.php';

use App\Config\Env;
use App\Config\Database;

Env::load(__DIR__ . '/../public_html/.env');

try {
    $db = Database::getConnection();
    echo "Connected to database successfully.\n";

    $sqlFile = __DIR__ . '/../database/install.sql';
    if (!file_exists($sqlFile)) {
        die("Error: install.sql not found at $sqlFile\n");
    }

    $sqlContent = file_get_contents($sqlFile);

    // Remove CREATE DATABASE and USE statements to prevent permission errors
    $sqlContent = preg_replace('/CREATE DATABASE IF NOT EXISTS [`"\'\w]+(.*?);/is', '', $sqlContent);
    $sqlContent = preg_replace('/USE [`"\'\w]+;/is', '', $sqlContent);

    // Execute the SQL queries
    echo "Running install.sql queries...\n";
    if ($db->multi_query($sqlContent)) {
        do {
            // Keep fetching results to clean the connection state
            if ($result = $db->store_result()) {
                $result->free();
            }
        } while ($db->next_result());
        echo "SUCCESS: Database schema and seeds installed successfully!\n";
    } else {
        echo "ERROR: Failed to run install.sql: " . $db->error . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
