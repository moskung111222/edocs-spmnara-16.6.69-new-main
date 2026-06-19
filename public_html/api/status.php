<?php
// Set timezone to Asia/Bangkok (Thailand)
date_default_timezone_set('Asia/Bangkok');

// ==================================================================
// API: status.php - Get or update request status (JSON output)
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
use App\Services\RequestService;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Config\Config;

try {
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        $requestNo = trim($_GET['no'] ?? '');
        if (empty($requestNo)) {
            throw new Exception("ไม่ระบุเลขที่คำขอ");
        }

        $request = Request::findByRequestNo($requestNo);
        if (!$request) {
            throw new Exception("ไม่พบคำขอในระบบ");
        }

        // Authorization check (must be officer or tracking-verified citizen)
        $hasPermission = false;
        if (isset($_SESSION['officer_id'])) {
            $hasPermission = true;
        } elseif (isset($_SESSION['tracking_auth'][$requestNo]) && $_SESSION['tracking_auth'][$requestNo] === true) {
            $hasPermission = true;
        }

        if (!$hasPermission) {
            throw new Exception("คุณไม่มีสิทธิ์เข้าถึงสถานะคำขอนี้");
        }

        $statusList = Config::getStatusList();
        echo json_encode([
            'status' => 'success',
            'data'   => [
                'request_no'  => $request['request_no'],
                'status_code' => $request['status'],
                'status_name' => $statusList[$request['status']] ?? $request['status'],
                'updated_at'  => $request['updated_at']
            ]
        ], JSON_UNESCAPED_UNICODE);

    } elseif ($method === 'POST') {
        // CSRF validation
        CsrfMiddleware::validatePostRequest();
        
        // Updating status is restricted to officers
        AuthMiddleware::requireOfficer();

        $requestId = (int)($_POST['request_id'] ?? 0);
        $newStatus = trim($_POST['status'] ?? '');
        $reason    = trim($_POST['reason'] ?? '');

        if ($requestId <= 0 || empty($newStatus)) {
            throw new Exception("กรุณาระบุข้อมูลรหัสคำขอและสถานะให้ครบถ้วน");
        }

        // Change status using request service
        RequestService::changeStatus(
            $requestId,
            $newStatus,
            $reason,
            $_SESSION['officer_id'],
            $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        );

        $statusList = Config::getStatusList();
        echo json_encode([
            'status'  => 'success',
            'message' => 'ปรับปรุงสถานะเรียบร้อยแล้ว',
            'data'    => [
                'status_code' => $newStatus,
                'status_name' => $statusList[$newStatus] ?? $newStatus
            ]
        ], JSON_UNESCAPED_UNICODE);
    } else {
        throw new Exception("ไม่สนับสนุน HTTP Method: " . $method);
    }

} catch (\Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status'  => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
