<?php
namespace App\Controllers;

use App\Config\Config;
use App\Models\Law;
use App\Services\UploadService;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use Exception;

class LawsAdminController {
    public function __construct() {
        AuthMiddleware::requireOfficer();
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
                        throw new Exception("กรุณากรอกหัวข้อและเลือกหมวดหมู่ข้อกฎหมาย");
                    }

                    if (!isset($_FILES['law_file']) || $_FILES['law_file']['error'] === UPLOAD_ERR_NO_FILE) {
                        throw new Exception("กรุณาเลือกไฟล์เอกสาร PDF ที่ต้องการอัปโหลด");
                    }

                    $fileMeta = UploadService::uploadPDF($_FILES['law_file']);
                    
                    Law::create($title, $category, $fileMeta['original_name'], $fileMeta['file_path'], $fileMeta['file_size'], $_SESSION['officer_id']);
                    $successMessage = "บันทึกและอัปโหลดไฟล์กฎหมายระเบียบเรียบร้อยแล้ว";
                } elseif ($action === 'delete') {
                    $id = (int)($_POST['id'] ?? 0);
                    if ($id <= 0) {
                        throw new Exception("ไม่พบบันทึกที่ต้องการลบ");
                    }

                    $law = Law::findById($id);
                    if ($law) {
                        if (file_exists($law['file_path'])) {
                            unlink($law['file_path']);
                        }
                        Law::delete($id);
                        $successMessage = "ลบไฟล์กฎหมายเรียบร้อยแล้ว";
                    } else {
                        throw new Exception("ไม่พบไฟล์เอกสารในระบบ");
                    }
                }
            } catch (Exception $e) {
                $errorMessage = $e->getMessage();
            }
        }

        $items = Law::getAll();
        include dirname(__DIR__) . '/views/admin/laws.php';
    }
}
