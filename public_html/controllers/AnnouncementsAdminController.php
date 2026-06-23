<?php
namespace App\Controllers;

use App\Config\Config;
use App\Models\Announcement;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use Exception;

class AnnouncementsAdminController {
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
                    $title   = trim($_POST['title'] ?? '');
                    $content = trim($_POST['content'] ?? '');
                    $type    = trim($_POST['type'] ?? 'announcement');

                    if (empty($title) || empty($content)) {
                        throw new Exception("กรุณากรอกข้อมูลให้ครบถ้วน");
                    }

                    Announcement::create($title, $content, $type, $_SESSION['officer_id']);
                    $successMessage = "สร้างประกาศเรียบร้อยแล้ว";
                } elseif ($action === 'edit') {
                    $id      = (int)($_POST['id'] ?? 0);
                    $title   = trim($_POST['title'] ?? '');
                    $content = trim($_POST['content'] ?? '');
                    $type    = trim($_POST['type'] ?? 'announcement');

                    if ($id <= 0 || empty($title) || empty($content)) {
                        throw new Exception("กรุณากรอกข้อมูลให้ครบถ้วน");
                    }

                    Announcement::update($id, $title, $content, $type);
                    $successMessage = "แก้ไขประกาศเรียบร้อยแล้ว";
                } elseif ($action === 'delete') {
                    $id = (int)($_POST['id'] ?? 0);
                    if ($id <= 0) {
                        throw new Exception("ไม่พบบันทึกที่ต้องการลบ");
                    }

                    Announcement::delete($id);
                    $successMessage = "ลบประกาศเรียบร้อยแล้ว";
                }
            } catch (Exception $e) {
                $errorMessage = $e->getMessage();
            }
        }

        $items = Announcement::getAll();
        include dirname(__DIR__) . '/views/admin/announcements.php';
    }
}
