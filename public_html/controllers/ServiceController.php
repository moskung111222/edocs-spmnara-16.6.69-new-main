<?php
namespace App\Controllers;

use App\Config\Config;
use App\Config\Database;
use App\Models\Request;
use App\Models\Department;
use App\Services\RequestService;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use Exception;

class ServiceController {
    public function __construct() {
        AuthMiddleware::requireOfficer();
    }

    /**
     * Display request types (services) list and handle CRUD.
     */
    public function index() {
        AuthMiddleware::requirePermission('services.view');

        try {
            $successMessage = '';
            $errorMessage = '';

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                try {
                    CsrfMiddleware::validatePostRequest();
                    $action = $_POST['action'] ?? '';

                    if ($action === 'create') {
                        AuthMiddleware::requirePermission('services.create');
                        $code = strtoupper(trim($_POST['code'] ?? ''));
                        $nameTh = trim($_POST['name_th'] ?? '');
                        $description = trim($_POST['description'] ?? '') ?: null;
                        $departmentId = !empty($_POST['department_id']) ? (int)$_POST['department_id'] : null;
                        $sortOrder = (int)($_POST['sort_order'] ?? 0);

                        // Parse checklist from textarea (one item per line)
                        $checklistRaw = trim($_POST['doc_checklist'] ?? '');
                        $checklistItems = array_filter(array_map('trim', explode("\n", $checklistRaw)));
                        if (empty($code) || empty($nameTh) || empty($checklistItems)) {
                            throw new Exception("กรุณากรอกรหัส ชื่อ และรายการเอกสารที่ต้องแนบ");
                        }
                        $checklistJson = json_encode(array_values($checklistItems), JSON_UNESCAPED_UNICODE);

                        $db = Database::getConnection();
                        $stmt = $db->prepare("INSERT INTO request_types (code, name_th, description, doc_checklist, department_id, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
                        if (!$stmt) {
                            throw new Exception("Prepare failed: " . $db->error);
                        }
                        $stmt->bind_param("ssssii", $code, $nameTh, $description, $checklistJson, $departmentId, $sortOrder);
                        if (!$stmt->execute()) {
                            throw new Exception("Execute failed: " . $stmt->error);
                        }
                        $stmt->close();

                        RequestService::logAudit(
                            $_SESSION['officer_id'], 'officer',
                            "สร้างประเภทบริการใหม่: {$nameTh} ({$code})",
                            'Services',
                            $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? ''
                        );
                        $successMessage = "สร้างประเภทบริการใหม่สำเร็จ";

                    } elseif ($action === 'edit') {
                        AuthMiddleware::requirePermission('services.edit');
                        $id = (int)($_POST['id'] ?? 0);
                        $nameTh = trim($_POST['name_th'] ?? '');
                        $description = trim($_POST['description'] ?? '') ?: null;
                        $departmentId = !empty($_POST['department_id']) ? (int)$_POST['department_id'] : null;
                        $sortOrder = (int)($_POST['sort_order'] ?? 0);

                        $checklistRaw = trim($_POST['doc_checklist'] ?? '');
                        $checklistItems = array_filter(array_map('trim', explode("\n", $checklistRaw)));
                        if ($id <= 0 || empty($nameTh) || empty($checklistItems)) {
                            throw new Exception("กรุณากรอกข้อมูลให้ครบถ้วน");
                        }
                        $checklistJson = json_encode(array_values($checklistItems), JSON_UNESCAPED_UNICODE);

                        $db = Database::getConnection();
                        $stmt = $db->prepare("UPDATE request_types SET name_th = ?, description = ?, doc_checklist = ?, department_id = ?, sort_order = ? WHERE id = ?");
                        if (!$stmt) {
                            throw new Exception("Prepare failed: " . $db->error);
                        }
                        $stmt->bind_param("sssiii", $nameTh, $description, $checklistJson, $departmentId, $sortOrder, $id);
                        if (!$stmt->execute()) {
                            throw new Exception("Execute failed: " . $stmt->error);
                        }
                        $stmt->close();

                        RequestService::logAudit(
                            $_SESSION['officer_id'], 'officer',
                            "แก้ไขประเภทบริการ ID:{$id}: {$nameTh}",
                            'Services',
                            $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? ''
                        );
                        $successMessage = "แก้ไขประเภทบริการสำเร็จ";

                    } elseif ($action === 'toggle') {
                        AuthMiddleware::requirePermission('services.edit');
                        $id = (int)($_POST['id'] ?? 0);
                        if ($id <= 0) {
                            throw new Exception("ไม่ระบุรหัสประเภทบริการ");
                        }
                        $db = Database::getConnection();
                        $db->query("UPDATE request_types SET active = NOT active WHERE id = " . (int)$id);

                        $successMessage = "เปลี่ยนสถานะประเภทบริการสำเร็จ";
                    }

                } catch (Exception $e) {
                    $errorMessage = $e->getMessage();
                }
            }

            // Fetch all request types with department info
            $db = Database::getConnection();
            $result = $db->query("
                SELECT rt.*, d.name_th AS department_name 
                FROM request_types rt 
                LEFT JOIN departments d ON rt.department_id = d.id 
                ORDER BY rt.sort_order ASC, rt.id ASC
            ");
            $services = [];
            while ($row = $result->fetch_assoc()) {
                $row['doc_checklist'] = json_decode($row['doc_checklist'], true);
                $services[] = $row;
            }

            $departments = Department::getAll(true);

            include dirname(__DIR__) . '/views/admin/services.php';

        } catch (Exception $e) {
            http_response_code(500);
            throw new Exception("เกิดข้อผิดพลาดในการโหลดประเภทบริการ: " . $e->getMessage());
        }
    }
}
