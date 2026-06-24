<?php
$conn = @new mysqli('127.0.0.1', 'sesaonara_edocs', 'Thi$i$spmnara15');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected successfully to MySQL.\n";

// Show current user and grants
$res = $conn->query("SELECT CURRENT_USER()");
if ($res) {
    $row = $res->fetch_row();
    echo "Current User: " . $row[0] . "\n";
}

$res = $conn->query("SHOW GRANTS");
if ($res) {
    echo "Grants:\n";
    while ($row = $res->fetch_row()) {
        echo "  " . $row[0] . "\n";
    }
}

// Try creating database
$dbName = 'sesaonara_homeschool';
if ($conn->query("CREATE DATABASE IF NOT EXISTS `$dbName`")) {
    echo "Database `$dbName` created successfully!\n";
} else {
    echo "Failed to create database: " . $conn->error . "\n";
}

$conn->close();
