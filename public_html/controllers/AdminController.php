<?php
namespace App\Controllers;

use App\Config\Config;
use App\Config\Database;
use App\Models\Request;
use App\Models\Attachment;
use App\Services\RequestService;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use Exception;

class AdminController {
    public function __construct() {
        // All admin actions require a logged-in officer session
        AuthMiddleware::requireOfficer();
    }

    /**
     * Display admin dashboard.
     */
    public function dashboard() {
        try {
            // Get dashboard stats
            $counts = Request::getStatusCounts();
            $typeCounts = Request::getTypeCounts();
            $monthlyCounts = Request::getMonthlyCounts();
            $kpis = Request::getExecutiveKPIs();
            
            // Build filter criteria
            $filters = [
                'status'     => trim($_GET['status'] ?? ''),
                'officer_id' => trim($_GET['officer_id'] ?? ''),
                'search'     => trim($_GET['search'] ?? '')
            ];

            $requests = Request::getAll($filters);

            // Fetch officers list for filters
            $db = Database::getConnection();
            $officersRes = $db->query("SELECT id, name, role FROM officers ORDER BY name ASC");
            $officers = [];
            while ($row = $officersRes->fetch_assoc()) {
                $officers[] = $row;
            }

            include dirname(__DIR__) . '/views/admin/dashboard.php';
        } catch (Exception $e) {
            http_response_code(500);
            throw new Exception("เกิดข้อผิดพลาดในการโหลดระบบผู้ดูแลระบบ: " . $e->getMessage());
        }
    }

    /**
     * Display detailed request page.
     */
    public function requestDetail() {
        try {
            $id = (int)($_GET['id'] ?? 0);
            if ($id <= 0) {
                throw new Exception("ไม่ระบุรหัสคำขอเอกสาร");
            }

            $request = Request::findById($id);
            if (!$request) {
                throw new Exception("ไม่พบคำขอเอกสารที่ระบุ");
            }

            // Handle actions like Change Status, Assign, Chat Post
            $successMessage = '';
            $errorMessage = '';

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                try {
                    CsrfMiddleware::validatePostRequest();

                    $action = $_POST['action'] ?? '';
                    
                    if ($action === 'change_status') {
                        $newStatus = trim($_POST['status'] ?? '');
                        $reason    = trim($_POST['reason'] ?? '');
                        
                        RequestService::changeStatus(
                            $id, 
                            $newStatus, 
                            $reason, 
                            $_SESSION['officer_id'],
                            $_SERVER['REMOTE_ADDR'] ?? '',
                            $_SERVER['HTTP_USER_AGENT'] ?? ''
                        );
                        
                        $successMessage = "ปรับปรุงสถานะคำขอเรียบร้อยแล้ว";
                    } 
                    
                    elseif ($action === 'assign_officer') {
                        // RBAC: Check permission for assigning officers
                        if (!AuthMiddleware::hasPermission('requests.assign')) {
                            throw new Exception("เฉพาะผู้มีสิทธิ์ 'requests.assign' เท่านั้นที่สามารถมอบหมายงานได้");
                        }

                        $officerId = isset($_POST['officer_id']) && $_POST['officer_id'] !== '' ? (int)$_POST['officer_id'] : null;
                        
                        Request::assignOfficer($id, $officerId);
                        
                        // Log audit
                        $officerName = 'ยกเลิกการมอบหมาย';
                        if ($officerId) {
                            $db = Database::getConnection();
                            $oStmt = $db->prepare("SELECT name FROM officers WHERE id = ?");
                            $oStmt->bind_param("i", $officerId);
                            $oStmt->execute();
                            $oRes = $oStmt->get_result()->fetch_assoc();
                            $oStmt->close();
                            $officerName = $oRes['name'] ?? 'เจ้าหน้าที่';
                        }

                        RequestService::logAudit(
                            $_SESSION['officer_id'],
                            'officer',
                            "มอบหมายคำขอ {$request['request_no']} ให้กับ {$officerName}",
                            'Requests',
                            $_SERVER['REMOTE_ADDR'] ?? '',
                            $_SERVER['HTTP_USER_AGENT'] ?? ''
                        );
                        
                        $successMessage = "มอบหมายงานสำเร็จ";
                    } 
                    
                    elseif ($action === 'post_message') {
                        $body = trim($_POST['body'] ?? '');
                        $isInternal = isset($_POST['internal_note']) ? 1 : 0;

                        if (empty($body)) {
                            throw new Exception("กรุณากรอกข้อความ");
                        }

                        Request::postMessage($id, 'officer', $body, $isInternal);

                        \App\Services\PusherService::trigger("request-{$request['request_no']}", 'new-message', [
                            'sender_type' => 'officer',
                            'body' => $body,
                            'internal_note' => $isInternal,
                            'created_at' => date('d/m/Y H:i')
                        ]);

                        // Audit log
                        $logType = $isInternal ? 'บันทึกบันทึกข้อความภายใน' : 'ส่งข้อความหาประชาชน';
                        RequestService::logAudit(
                            $_SESSION['officer_id'],
                            'officer',
                            "{$logType} บนคำขอ {$request['request_no']}",
                            'Requests',
                            $_SERVER['REMOTE_ADDR'] ?? '',
                            $_SERVER['HTTP_USER_AGENT'] ?? ''
                        );

                        $successMessage = "ส่งข้อความสำเร็จ";
                    }

                    // Reload request data after modifications
                    $request = Request::findById($id);

                } catch (Exception $e) {
                    $errorMessage = $e->getMessage();
                }
            }

            // Fetch related timeline, attachments, and chats
            $history     = Request::getStatusHistory($id);
            $attachments = Attachment::findByRequestId($id);
            $messages    = Request::getMessages($id, true); // Admin reads all including internal notes

            // Fetch officers list for assignment assignment dropdown
            $db = Database::getConnection();
            $officersRes = $db->query("SELECT id, name, role FROM officers ORDER BY name ASC");
            $officers = [];
            while ($row = $officersRes->fetch_assoc()) {
                $officers[] = $row;
            }

            include dirname(__DIR__) . '/views/admin/request_detail.php';
        } catch (Exception $e) {
            http_response_code(404);
            throw new Exception("เกิดข้อผิดพลาดในการโหลดรายละเอียดคำขอ: " . $e->getMessage());
        }
    }
}
