<?php
namespace App\Controllers;

use App\Config\Config;
use App\Models\Infographic;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use Exception;

class InfographicsAdminController {
    public function __construct() {
        AuthMiddleware::requireOfficer();
    }

    private function uploadImage($fileArray) {
        if (!isset($fileArray['tmp_name']) || !is_uploaded_file($fileArray['tmp_name'])) {
            throw new Exception("ไม่พบไฟล์รูปภาพที่อัปโหลด หรือเป็นไฟล์ที่ไม่ถูกต้อง");
        }
        if ($fileArray['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("เกิดข้อผิดพลาดในการอัปโหลดไฟล์รหัส: " . $fileArray['error']);
        }
        if ($fileArray['size'] > 5 * 1024 * 1024) { // Max 5MB
            throw new Exception("ขนาดไฟล์รูปภาพใหญ่เกินไป ห้ามเกิน 5 MB");
        }
        
        $ext = strtolower(pathinfo($fileArray['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
            throw new Exception("ประเภทไฟล์รูปภาพไม่ถูกต้อง อนุญาตเฉพาะ JPG, JPEG, PNG, WEBP, และ GIF เท่านั้น (ตรวจพบ: ." . htmlspecialchars($ext) . ")");
        }
        
        // Store in public web root assets folder so the client browser can access it directly
        $targetDir = dirname(__DIR__) . '/assets/images/infographics/';
        if (!is_dir($targetDir)) {
            if (!mkdir($targetDir, 0755, true)) {
                throw new Exception("ระบบจัดเก็บไฟล์รูปภาพขัดข้อง ไม่สามารถสร้างโฟลเดอร์ได้");
            }
        }
        
        $secureName = bin2hex(random_bytes(16)) . '.' . $ext;
        $targetLocation = $targetDir . $secureName;
        
        if (!move_uploaded_file($fileArray['tmp_name'], $targetLocation)) {
            throw new Exception("ไม่สามารถเคลื่อนย้ายไฟล์รูปภาพได้");
        }
        
        return [
            'original_name' => basename($fileArray['name']),
            'stored_name'   => $secureName,
            'image_path'    => 'assets/images/infographics/' . $secureName,
            'full_path'     => $targetLocation
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
                    $title = trim($_POST['title'] ?? '');

                    if (empty($title)) {
                        throw new Exception("กรุณากรอกชื่อหัวข้ออินโฟกราฟิก/รูปภาพ");
                    }

                    if (!isset($_FILES['image_file']) || $_FILES['image_file']['error'] === UPLOAD_ERR_NO_FILE) {
                        throw new Exception("กรุณาเลือกไฟล์รูปภาพที่ต้องการอัปโหลด");
                    }

                    $fileMeta = $this->uploadImage($_FILES['image_file']);
                    
                    Infographic::create($title, $fileMeta['original_name'], $fileMeta['image_path'], $_SESSION['officer_id']);
                    $successMessage = "อัปโหลดรูปภาพอินโฟกราฟิกประชาสัมพันธ์เรียบร้อยแล้ว";
                } elseif ($action === 'delete') {
                    $id = (int)($_POST['id'] ?? 0);
                    if ($id <= 0) {
                        throw new Exception("ไม่พบบันทึกที่ต้องการลบ");
                    }

                    $info = Infographic::findById($id);
                    if ($info) {
                        // Delete file
                        $fullPath = dirname(__DIR__) . '/' . $info['image_path'];
                        if (file_exists($fullPath)) {
                            unlink($fullPath);
                        }
                        Infographic::delete($id);
                        $successMessage = "ลบรูปภาพอินโฟกราฟิกเรียบร้อยแล้ว";
                    } else {
                        throw new Exception("ไม่พบรูปภาพอินโฟกราฟิกดังกล่าวในระบบ");
                    }
                }
            } catch (Exception $e) {
                $errorMessage = $e->getMessage();
            }
        }

        $items = Infographic::getAll();
        include dirname(__DIR__) . '/views/admin/infographics.php';
    }
}
