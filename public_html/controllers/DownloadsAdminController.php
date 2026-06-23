<?php
namespace App\Controllers;

use App\Config\Config;
use App\Models\DownloadDocument;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use Exception;

class DownloadsAdminController {
    public function __construct() {
        AuthMiddleware::requireOfficer();
    }

    private function uploadFile($fileArray) {
        if (!isset($fileArray['tmp_name']) || !is_uploaded_file($fileArray['tmp_name'])) {
            throw new Exception("ไม่พบไฟล์ที่อัปโหลด หรือเป็นไฟล์ที่ไม่ถูกต้อง");
        }
        if ($fileArray['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("เกิดข้อผิดพลาดในการอัปโหลดไฟล์รหัส: " . $fileArray['error']);
        }
        if ($fileArray['size'] > Config::UPLOAD_MAX_SIZE) {
            throw new Exception("ขนาดไฟล์ใหญ่เกินไป ห้ามเกิน 10 MB");
        }
        
        $ext = strtolower(pathinfo($fileArray['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['pdf', 'doc', 'docx'])) {
            throw new Exception("ประเภทไฟล์ไม่ถูกต้อง อนุญาตให้แนบเฉพาะ PDF, DOC, และ DOCX เท่านั้น (ตรวจพบ: ." . htmlspecialchars($ext) . ")");
        }
        
        $mime = 'application/pdf';
        if ($ext === 'docx') {
            $mime = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
        } elseif ($ext === 'doc') {
            $mime = 'application/msword';
        }
        
        // Ensure storage path exists
        $storagePath = Config::getPrivateStoragePath();
        if (!is_dir($storagePath)) {
            if (!mkdir($storagePath, 0755, true)) {
                throw new Exception("ระบบจัดเก็บไฟล์ขัดข้อง ไม่สามารถสถาปนาพื้นที่จัดเก็บเอกสารได้");
            }
        }
        
        $secureName = bin2hex(random_bytes(16)) . '.' . $ext;
        $targetLocation = $storagePath . $secureName;
        
        if (!move_uploaded_file($fileArray['tmp_name'], $targetLocation)) {
            throw new Exception("ไม่สามารถเคลื่อนย้ายไฟล์ไปยังที่จัดเก็บเอกสารได้");
        }
        
        return [
            'original_name' => basename($fileArray['name']),
            'stored_name'   => $secureName,
            'file_path'     => $targetLocation,
            'mime_type'     => $mime,
            'file_size'     => $fileArray['size']
        ];
    }

    public function index() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $successMessage = '';
        $errorMessage = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                CsrfMiddleware::validatePostRequest();
                $action = $_POST['action'] ?? '';

                if ($action === 'create') {
                    $title    = trim($_POST['title'] ?? '');
                    $category = trim($_POST['category'] ?? '');

                    if (empty($title) || empty($category)) {
                        throw new Exception("กรุณากรอกหัวข้อและเลือกประเภทแบบฟอร์ม");
                    }

                    if (!isset($_FILES['doc_file']) || $_FILES['doc_file']['error'] === UPLOAD_ERR_NO_FILE) {
                        throw new Exception("กรุณาเลือกไฟล์แบบฟอร์มที่ต้องการอัปโหลด");
                    }

                    $fileMeta = $this->uploadFile($_FILES['doc_file']);
                    
                    DownloadDocument::create($title, $category, $fileMeta['original_name'], $fileMeta['file_path'], $fileMeta['file_size'], $_SESSION['officer_id']);
                    $successMessage = "อัปโหลดแบบฟอร์มเปล่าเรียบร้อยแล้ว";
                } elseif ($action === 'delete') {
                    $id = (int)($_POST['id'] ?? 0);
                    if ($id <= 0) {
                        throw new Exception("ไม่พบบันทึกที่ต้องการลบ");
                    }

                    $doc = DownloadDocument::findById($id);
                    if ($doc) {
                        if (file_exists($doc['file_path'])) {
                            unlink($doc['file_path']);
                        }
                        DownloadDocument::delete($id);
                        $successMessage = "ลบไฟล์แบบฟอร์มออกจากศูนย์ดาวน์โหลดเรียบร้อยแล้ว";
                    } else {
                        throw new Exception("ไม่พบไฟล์แบบฟอร์มดังกล่าวในระบบ");
                    }
                }
            } catch (Exception $e) {
                $errorMessage = $e->getMessage();
            }
        }

        $items = DownloadDocument::getAll();
        include dirname(__DIR__) . '/views/admin/downloads.php';
    }
}
