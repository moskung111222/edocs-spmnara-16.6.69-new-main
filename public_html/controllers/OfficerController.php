<?php
namespace App\Controllers;

use App\Config\Config;
use App\Models\Officer;
use App\Models\Department;
use App\Models\Role;
use App\Services\RequestService;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use Exception;

class OfficerController {
    public function __construct() {
        AuthMiddleware::requireOfficer();
    }

    /**
     * List all officers.
     */
    public function index() {
        AuthMiddleware::requirePermission('officers.view');

        try {
            $filters = [
                'department_id' => trim($_GET['department_id'] ?? ''),
                'role'          => trim($_GET['role'] ?? ''),
                'search'        => trim($_GET['search'] ?? ''),
            ];
            $officers = Officer::getAll($filters);
            $departments = Department::getAll(true);
            $roles = Role::getAll(true);

            include dirname(__DIR__) . '/views/admin/officers.php';
        } catch (Exception $e) {
            http_response_code(500);
            throw new Exception("เกิดข้อผิดพลาดในการโหลดรายชื่อเจ้าหน้าที่: " . $e->getMessage());
        }
    }

    /**
     * Create a new officer.
     */
    public function create() {
        AuthMiddleware::requirePermission('officers.create');

        try {
            $successMessage = '';
            $errorMessage = '';

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                try {
                    CsrfMiddleware::validatePostRequest();

                    $username = trim($_POST['username'] ?? '');
                    $password = trim($_POST['password'] ?? '');
                    $name = trim($_POST['name'] ?? '');
                    $email = trim($_POST['email'] ?? '');
                    $role = trim($_POST['role'] ?? 'staff');
                    $departmentId = !empty($_POST['department_id']) ? (int)$_POST['department_id'] : null;

                    if (empty($username) || empty($password) || empty($name) || empty($email)) {
                        throw new Exception("กรุณากรอกข้อมูลให้ครบถ้วน");
                    }
                    if (strlen($password) < 6) {
                        throw new Exception("รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร");
                    }
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        throw new Exception("รูปแบบอีเมลไม่ถูกต้อง");
                    }

                    // Check duplicate username
                    $existing = Officer::findByUsername($username);
                    if ($existing) {
                        throw new Exception("ชื่อผู้ใช้งาน '{$username}' ถูกใช้งานแล้ว");
                    }

                    $passwordHash = password_hash($password, PASSWORD_BCRYPT);
                    $officerId = Officer::create($username, $passwordHash, $name, $email, $role, $departmentId);

                    // Assign additional departments if selected
                    $deptIds = $_POST['departments'] ?? [];
                    if (!empty($deptIds)) {
                        $headDeptId = !empty($_POST['head_department_id']) ? (int)$_POST['head_department_id'] : null;
                        Officer::syncDepartments($officerId, $deptIds, $headDeptId);
                    }

                    RequestService::logAudit(
                        $_SESSION['officer_id'], 'officer',
                        "สร้างเจ้าหน้าที่ใหม่: {$name} ({$username}) สิทธิ์: {$role}",
                        'Officers',
                        $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? ''
                    );

                    $_SESSION['flash_success'] = "สร้างเจ้าหน้าที่ใหม่สำเร็จ";
                    header("Location: " . Config::SITE_URL . "/admin/officers");
                    exit;

                } catch (Exception $e) {
                    $errorMessage = $e->getMessage();
                }
            }

            $departments = Department::getAll(true);
            $roles = Role::getAll(true);
            $editMode = false;
            $officer = null;
            $officerDepartments = [];

            include dirname(__DIR__) . '/views/admin/officer_form.php';
        } catch (Exception $e) {
            http_response_code(500);
            throw new Exception("เกิดข้อผิดพลาด: " . $e->getMessage());
        }
    }

    /**
     * Edit an existing officer.
     */
    public function edit() {
        AuthMiddleware::requirePermission('officers.edit');

        try {
            $id = (int)($_GET['id'] ?? 0);
            if ($id <= 0) {
                throw new Exception("ไม่ระบุรหัสเจ้าหน้าที่");
            }

            $officer = Officer::findById($id);
            if (!$officer) {
                throw new Exception("ไม่พบเจ้าหน้าที่");
            }

            $successMessage = '';
            $errorMessage = '';

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                try {
                    CsrfMiddleware::validatePostRequest();
                    $action = $_POST['action'] ?? 'update';

                    if ($action === 'update') {
                        $name = trim($_POST['name'] ?? '');
                        $email = trim($_POST['email'] ?? '');
                        $role = trim($_POST['role'] ?? 'staff');
                        $departmentId = !empty($_POST['department_id']) ? (int)$_POST['department_id'] : null;

                        if (empty($name) || empty($email)) {
                            throw new Exception("กรุณากรอกข้อมูลให้ครบถ้วน");
                        }

                        Officer::update($id, $name, $email, $role, $departmentId);

                        // Update password if provided
                        $newPassword = trim($_POST['password'] ?? '');
                        if (!empty($newPassword)) {
                            if (strlen($newPassword) < 6) {
                                throw new Exception("รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร");
                            }
                            Officer::updatePassword($id, password_hash($newPassword, PASSWORD_BCRYPT));
                        }

                        // Sync departments
                        $deptIds = $_POST['departments'] ?? [];
                        $headDeptId = !empty($_POST['head_department_id']) ? (int)$_POST['head_department_id'] : null;
                        Officer::syncDepartments($id, $deptIds, $headDeptId);

                        RequestService::logAudit(
                            $_SESSION['officer_id'], 'officer',
                            "แก้ไขเจ้าหน้าที่ ID:{$id}: {$name} สิทธิ์: {$role}",
                            'Officers',
                            $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? ''
                        );

                        $successMessage = "แก้ไขข้อมูลเจ้าหน้าที่สำเร็จ";
                        $officer = Officer::findById($id);

                    } elseif ($action === 'toggle') {
                        Officer::toggleActive($id);
                        $successMessage = "เปลี่ยนสถานะเจ้าหน้าที่สำเร็จ";
                        $officer = Officer::findById($id);
                    }

                } catch (Exception $e) {
                    $errorMessage = $e->getMessage();
                }
            }

            $departments = Department::getAll(true);
            $roles = Role::getAll(true);
            $officerDepartments = Officer::getDepartments($id);
            $editMode = true;

            include dirname(__DIR__) . '/views/admin/officer_form.php';
        } catch (Exception $e) {
            http_response_code(404);
            throw new Exception("เกิดข้อผิดพลาด: " . $e->getMessage());
        }
    }
}
