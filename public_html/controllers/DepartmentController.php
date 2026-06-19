<?php
namespace App\Controllers;

use App\Config\Config;
use App\Models\Department;
use App\Services\RequestService;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use Exception;

class DepartmentController {
    public function __construct() {
        AuthMiddleware::requireOfficer();
    }

    /**
     * Display department list and handle create/edit/toggle actions.
     */
    public function index() {
        AuthMiddleware::requirePermission('departments.view');

        try {
            $successMessage = '';
            $errorMessage = '';

            // Handle POST actions
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                try {
                    CsrfMiddleware::validatePostRequest();
                    $action = $_POST['action'] ?? '';

                    if ($action === 'create') {
                        AuthMiddleware::requirePermission('departments.create');
                        $code = strtoupper(trim($_POST['code'] ?? ''));
                        $nameTh = trim($_POST['name_th'] ?? '');
                        $nameEn = trim($_POST['name_en'] ?? '') ?: null;
                        $description = trim($_POST['description'] ?? '') ?: null;
                        $sortOrder = (int)($_POST['sort_order'] ?? 0);

                        if (empty($code) || empty($nameTh)) {
                            throw new Exception("กรุณากรอกรหัสและชื่อกลุ่มงาน");
                        }

                        Department::create($code, $nameTh, $nameEn, $description, $sortOrder);

                        RequestService::logAudit(
                            $_SESSION['officer_id'], 'officer',
                            "สร้างกลุ่มงานใหม่: {$nameTh} ({$code})",
                            'Departments',
                            $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? ''
                        );
                        $successMessage = "สร้างกลุ่มงานใหม่สำเร็จ";

                    } elseif ($action === 'edit') {
                        AuthMiddleware::requirePermission('departments.edit');
                        $id = (int)($_POST['id'] ?? 0);
                        $code = strtoupper(trim($_POST['code'] ?? ''));
                        $nameTh = trim($_POST['name_th'] ?? '');
                        $nameEn = trim($_POST['name_en'] ?? '') ?: null;
                        $description = trim($_POST['description'] ?? '') ?: null;
                        $sortOrder = (int)($_POST['sort_order'] ?? 0);

                        if ($id <= 0 || empty($code) || empty($nameTh)) {
                            throw new Exception("กรุณากรอกข้อมูลให้ครบถ้วน");
                        }

                        Department::update($id, $code, $nameTh, $nameEn, $description, $sortOrder);

                        RequestService::logAudit(
                            $_SESSION['officer_id'], 'officer',
                            "แก้ไขกลุ่มงาน ID:{$id} เป็น {$nameTh}",
                            'Departments',
                            $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? ''
                        );
                        $successMessage = "แก้ไขกลุ่มงานสำเร็จ";

                    } elseif ($action === 'toggle') {
                        AuthMiddleware::requirePermission('departments.edit');
                        $id = (int)($_POST['id'] ?? 0);
                        if ($id <= 0) {
                            throw new Exception("ไม่ระบุรหัสกลุ่มงาน");
                        }
                        Department::toggleActive($id);
                        $successMessage = "เปลี่ยนสถานะกลุ่มงานสำเร็จ";
                    }

                } catch (Exception $e) {
                    $errorMessage = $e->getMessage();
                }
            }

            $departments = Department::getAll();
            include dirname(__DIR__) . '/views/admin/departments.php';

        } catch (Exception $e) {
            http_response_code(500);
            throw new Exception("เกิดข้อผิดพลาดในการโหลดกลุ่มงาน: " . $e->getMessage());
        }
    }
}
