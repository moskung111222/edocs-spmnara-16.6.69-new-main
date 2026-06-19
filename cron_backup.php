<?php
// Set timezone to Asia/Bangkok (Thailand)
date_default_timezone_set('Asia/Bangkok');

// ==================================================================
// Cron Job: Daily Backup (DB & Attachments)
// Scheduled: Daily at 23:00 (Run via CLI or DirectAdmin Cron)
// Retention: 30 Days
// ==================================================================

// Prevent execution via HTTP request for security (CLI only)
if (php_sapi_name() !== 'cli' && isset($_SERVER['REMOTE_ADDR'])) {
    http_response_code(403);
    die("Error: This script can only be executed via the command line interface (CLI).");
}

// Bootstrap autoloader & configuration
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $parts = explode('\\', $relative_class);
    if (count($parts) < 2) {
        return;
    }
    
    $subNamespace = $parts[0];
    $className    = $parts[1];
    $base_dir     = __DIR__ . '/public_html/';
    
    if ($subNamespace === 'Config') {
        // App\Config\Database -> app/config/database.php
        $file = $base_dir . 'app/config/' . strtolower($className) . '.php';
    } else {
        $folder = strtolower($subNamespace);
        $file = $base_dir . $folder . '/' . $className . '.php';
    }
    
    if (file_exists($file)) {
        require $file;
    }
});

// Load environment variables from .env
\App\Config\Env::load(__DIR__ . '/public_html/.env');

use App\Config\Database;
use App\Config\Config;

$backupDir = dirname(__DIR__) . '/edocs-spmnara/backups/';
if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
    // Production path outside public_html on hosting
    $backupDir = '/home/account/private_storage/backups/';
}

// Ensure backup directory exists
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

$dateString = date('Y-m-d_H-i-s');
$dbBackupFile = $backupDir . "db_backup_{$dateString}.sql";
$filesBackupFile = $backupDir . "files_backup_{$dateString}.zip";

echo "=== starting backup procedure at " . date('Y-m-d H:i:s') . " ===\n";

// ------------------------------------------------------------------
// 1. Native Database Exporter (mysqli)
// ------------------------------------------------------------------
try {
    echo "Exporting database to: {$dbBackupFile}\n";
    $db = Database::getConnection();
    
    $tables = [];
    $result = $db->query("SHOW TABLES");
    while ($row = $result->fetch_row()) {
        $tables[] = $row[0];
    }
    
    $sqlContent = "-- NWT Document Submission System SQL Backup\n";
    $sqlContent .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n\n";
    $sqlContent .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
    
    foreach ($tables as $table) {
        // Table structure
        $res = $db->query("SHOW CREATE TABLE `{$table}`");
        $row = $res->fetch_row();
        $sqlContent .= "\n\n-- Table structure for table `{$table}`\n";
        $sqlContent .= "DROP TABLE IF EXISTS `{$table}`;\n";
        $sqlContent .= $row[1] . ";\n\n";
        
        // Table data
        $dataRes = $db->query("SELECT * FROM `{$table}`");
        $columnCount = $dataRes->field_count;
        
        while ($dataRow = $dataRes->fetch_row()) {
            $sqlContent .= "INSERT INTO `{$table}` VALUES(";
            for ($j = 0; $j < $columnCount; $j++) {
                if (isset($dataRow[$j])) {
                    // Escape string characters
                    $val = $db->real_escape_string($dataRow[$j]);
                    $sqlContent .= "'" . $val . "'";
                } else {
                    $sqlContent .= "NULL";
                }
                if ($j < ($columnCount - 1)) {
                    $sqlContent .= ",";
                }
            }
            $sqlContent .= ");\n";
        }
    }
    
    $sqlContent .= "\n\nSET FOREIGN_KEY_CHECKS=1;\n";
    
    file_put_contents($dbBackupFile, $sqlContent);
    echo "Database backup successful.\n";
    
} catch (Exception $e) {
    echo "ERROR during Database Backup: " . $e->getMessage() . "\n";
}

// ------------------------------------------------------------------
// 2. Private documents folder archiver (zip)
// ------------------------------------------------------------------
try {
    $docsPath = Config::getPrivateStoragePath();
    echo "Archiving private documents from: {$docsPath}\n";
    echo "Target zip: {$filesBackupFile}\n";
    
    if (is_dir($docsPath)) {
        $zip = new ZipArchive();
        if ($zip->open($filesBackupFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($docsPath),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            $fileCount = 0;
            foreach ($files as $name => $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($docsPath));
                    
                    $zip->addFile($filePath, $relativePath);
                    $fileCount++;
                }
            }
            $zip->close();
            echo "Archiving documents successful. Added {$fileCount} files to zip.\n";
        } else {
            throw new Exception("Unable to create zip file.");
        }
    } else {
        echo "Warning: Documents storage folder is empty or does not exist yet. Created empty zip backup.\n";
        $zip = new ZipArchive();
        $zip->open($filesBackupFile, ZipArchive::CREATE);
        $zip->addFromString('info.txt', 'No files present at backup time.');
        $zip->close();
    }
} catch (Exception $e) {
    echo "ERROR during File Archiving: " . $e->getMessage() . "\n";
}

// ------------------------------------------------------------------
// 3. Purge history backups older than 30 Days (86400 sec * 30 = 2592000)
// ------------------------------------------------------------------
try {
    echo "Pruning backup history older than 30 days...\n";
    $retentionSeconds = 30 * 24 * 60 * 60; // 30 Days
    $now = time();
    
    $backupFiles = glob($backupDir . '*');
    $deletedCount = 0;
    
    foreach ($backupFiles as $file) {
        if (is_file($file)) {
            $fileAge = $now - filemtime($file);
            if ($fileAge > $retentionSeconds) {
                unlink($file);
                echo "Deleted old backup: " . basename($file) . "\n";
                $deletedCount++;
            }
        }
    }
    echo "Backup cleanup completed. Deleted {$deletedCount} old files.\n";
    
} catch (Exception $e) {
    echo "ERROR during Backup Cleanup: " . $e->getMessage() . "\n";
}

echo "=== Backup procedure finished successfully ===\n";
