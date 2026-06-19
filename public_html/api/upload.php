<?php
// Set timezone to Asia/Bangkok (Thailand)
date_default_timezone_set('Asia/Bangkok');

// ==================================================================
// API: upload.php - Secure document upload (JSON output)
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
use App\Models\Attachment;
use App\Services\UploadService;
use App\Services\RequestService;
use App\Config\Config;
use App\Middleware\CsrfMiddleware;

try {
    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("รองรับการอัปโหลดผ่าน POST Method เท่านั้น");
    }

    // CSRF check
    CsrfMiddleware::validatePostRequest();

    $requestId = (int)($_POST['request_id'] ?? 0);
    if ($requestId <= 0) {
        throw new Exception("ไม่ระบุรหัสคำขอเอกสาร");
    }

    $request = Request::findById($requestId);
    if (!$request) {
        throw new Exception("ไม่พบรายการคำขอดังกล่าวในระบบ");
    }

    // Determine authentication (Officer session or Citizen tracking session)
    $hasPermission = false;
    $uploaderRole  = '';

    if (isset($_SESSION['officer_id'])) {
        $hasPermission = true;
        $uploaderRole  = 'officer';
    } elseif (isset($_SESSION['tracking_auth'][$request['request_no']]) && $_SESSION['tracking_auth'][$request['request_no']] === true) {
        $hasPermission = true;
        $uploaderRole  = 'applicant';

        // Citizens can only upload if request status is need_info
        if ($request['status'] !== Config::STATUS_NEED_INFO) {
            throw new Exception("ไม่สามารถอัปโหลดข้อมูลได้เนื่องจากไม่อยู่ในสถานะขอข้อมูลเพิ่มเติม");
        }
    }

    if (!$hasPermission) {
        throw new Exception("ไม่มีสิทธิ์อัปโหลดเอกสารสำหรับคำขอนี้");
    }

    // Validate uploaded file array
    if (!isset($_FILES['file']) || $_FILES['file']['error'] === UPLOAD_ERR_NO_FILE) {
        throw new Exception("ไม่พบไฟล์แนบ");
    }

    // Upload process
    $fileMeta = UploadService::uploadPDF($_FILES['file']);

    // Check version
    $nextVer = Attachment::getNextVersion($requestId, $fileMeta['original_name']);

    $attachmentId = Attachment::create(
        $requestId,
        $fileMeta['original_name'],
        $fileMeta['file_path'],
        $fileMeta['mime_type'],
        $fileMeta['file_size'],
        $uploaderRole,
        $nextVer
    );

    // If citizen uploaded, change status back to 'submitted'
    if ($uploaderRole === 'applicant') {
        RequestService::changeStatus(
            $requestId,
            Config::STATUS_SUBMITTED,
            "ผู้ยื่นคำขออัปโหลดเอกสารเพิ่มเติมผ่านระบบ: " . $fileMeta['original_name'],
            null,
            $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        );
    }

    echo json_encode([
        'status'  => 'success',
        'message' => 'อัปโหลดไฟล์สำเร็จ',
        'data'    => [
            'id'            => $attachmentId,
            'file_name'     => $fileMeta['original_name'],
            'version'       => $nextVer,
            'file_size'     => $fileMeta['file_size']
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (\Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status'  => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
