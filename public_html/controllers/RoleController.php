<?php
namespace App\Controllers;

use App\Config\Config;
use App\Models\Role;
use App\Models\Permission;
use App\Services\RequestService;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use Exception;

class RoleController {
    public function __construct() {
        AuthMiddleware::requireOfficer();
    }

    /**
     * List all roles.
     */
    public function index() {
        AuthMiddleware::requirePermission('roles.view');

        try {
            $roles = Role::getAll();
            include dirname(__DIR__) . '/views/admin/roles.php';
        } catch (Exception $e) {
            http_response_code(500);
            throw new Exception("เกิดข้อผิดพลาดในการโหลด Roles: " . $e->getMessage());
        }
    }

    /**
     * Edit role permissions.
     */
    public function edit() {
        AuthMiddleware::requirePermission('roles.manage');

        try {
            $id = (int)($_GET['id'] ?? 0);
            if ($id <= 0) {
                throw new Exception("ไม่ระบุรหัส Role");
            }

            $role = Role::findById($id);
            if (!$role) {
                throw new Exception("ไม่พบ Role");
            }

            $successMessage = '';
            $errorMessage = '';

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                try {
                    CsrfMiddleware::validatePostRequest();
                    $action = $_POST['action'] ?? 'update_permissions';

                    if ($action === 'update_info') {
                        $nameTh = trim($_POST['name_th'] ?? '');
                        $description = trim($_POST['description'] ?? '') ?: null;
                        if (empty($nameTh)) {
                            throw new Exception("กรุณากรอกชื่อ Role");
                        }
                        Role::update($id, $nameTh, $description);
                        $successMessage = "แก้ไข Role สำเร็จ";
                        $role = Role::findById($id);

                    } elseif ($action === 'update_permissions') {
                        $permissionIds = $_POST['permissions'] ?? [];
                        $permissionIds = array_map('intval', $permissionIds);
                        Role::syncPermissions($id, $permissionIds);

                        RequestService::logAudit(
                            $_SESSION['officer_id'], 'officer',
                            "อัปเดตสิทธิ์ของ Role: {$role['name_th']} ({$role['code']})",
                            'Roles',
                            $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? ''
                        );
                        $successMessage = "อัปเดตสิทธิ์สำเร็จ";
                    }

                } catch (Exception $e) {
                    $errorMessage = $e->getMessage();
                }
            }

            // Get all permissions grouped by module
            $allPermissions = Permission::getAllGrouped();

            // Get current role's permission IDs
            $rolePermissions = Role::getPermissions($id);
            $rolePermissionIds = array_column($rolePermissions, 'id');

            include dirname(__DIR__) . '/views/admin/role_permissions.php';
        } catch (Exception $e) {
            http_response_code(404);
            throw new Exception("เกิดข้อผิดพลาด: " . $e->getMessage());
        }
    }

    /**
     * Create a new role.
     */
    public function create() {
        AuthMiddleware::requirePermission('roles.manage');

        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                CsrfMiddleware::validatePostRequest();

                $code = strtolower(trim($_POST['code'] ?? ''));
                $nameTh = trim($_POST['name_th'] ?? '');
                $description = trim($_POST['description'] ?? '') ?: null;

                if (empty($code) || empty($nameTh)) {
                    throw new Exception("กรุณากรอกรหัสและชื่อ Role");
                }

                Role::create($code, $nameTh, $description);

                RequestService::logAudit(
                    $_SESSION['officer_id'], 'officer',
                    "สร้าง Role ใหม่: {$nameTh} ({$code})",
                    'Roles',
                    $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? ''
                );

                $_SESSION['flash_success'] = "สร้าง Role ใหม่สำเร็จ";
                header("Location: " . Config::SITE_URL . "/admin/roles");
                exit;
            }
        } catch (Exception $e) {
            http_response_code(500);
            throw new Exception("เกิดข้อผิดพลาด: " . $e->getMessage());
        }
    }
}
