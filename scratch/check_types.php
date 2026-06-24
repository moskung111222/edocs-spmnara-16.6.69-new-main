<?php
require_once __DIR__ . '/../public_html/app/config/env.php';
require_once __DIR__ . '/../public_html/app/config/database.php';

use App\Config\Env;
use App\Config\Database;

Env::load(__DIR__ . '/../public_html/.env');

try {
    $db = Database::getConnection();
    $res = $db->query("SELECT id, code, name_th, active FROM request_types");
    while ($row = $res->fetch_assoc()) {
        echo "ID: {$row['id']} | Code: {$row['code']} | Active: {$row['active']} | Name: {$row['name_th']}\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
