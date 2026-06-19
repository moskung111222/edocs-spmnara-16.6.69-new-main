<?php
// Set timezone to Asia/Bangkok (Thailand)
date_default_timezone_set('Asia/Bangkok');

// ==================================================================
// API: requests.php - Query requests queue (JSON output)
// ==================================================================

header('Content-Type: application/json; charset=utf-8');

// Manual bootstrap in case of direct API file access
if (!class_exists('App\Config\Database')) {
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
        $base_dir     = dirname(__DIR__) . '/';
        
        if ($subNamespace === 'Config') {
            $file = $base_dir . 'app/config/' . strtolower($className) . '.php';
        } else {
            $folder = strtolower($subNamespace);
            $file = $base_dir . $folder . '/' . $className . '.php';
        }
        
        if (file_exists($file)) {
            require $file;
        }
    });

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

use App\Models\Request;
use App\Middleware\AuthMiddleware;

try {
    // Requires officer authentication
    AuthMiddleware::requireOfficer();

    $filters = [
        'status'     => trim($_GET['status'] ?? ''),
        'officer_id' => trim($_GET['officer_id'] ?? ''),
        'search'     => trim($_GET['search'] ?? '')
    ];

    $data = Request::getAll($filters);

    echo json_encode([
        'status' => 'success',
        'count'  => count($data),
        'data'   => $data
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (\Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status'  => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
