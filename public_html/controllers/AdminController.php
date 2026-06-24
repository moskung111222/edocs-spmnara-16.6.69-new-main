<?php
namespace App\Controllers;

use App\Config\Config;
use App\Config\Database;
use App\Models\Request;
use App\Models\Attachment;
use App\Models\RequestAttachment;
use App\Models\MeetingResult;
use App\Models\WorkflowHistory;
use App\Models\ApplicantAccount;
use App\Services\RequestService;
use App\Services\UploadService;
use App\Services\MailService;
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
     * Display detailed request page and handle administrative workflows.
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

                        WorkflowHistory::log($id, 'change_overall_status', "ปรับปรุงสถานะภาพรวมเป็น: " . $newStatus, $_SESSION['officer_id']);
                        
                        $successMessage = "ปรับปรุงสถานะคำขอเรียบร้อยแล้ว";
                    } 
                    
                    elseif ($action === 'change_process_1') {
                        $newStatus = trim($_POST['process_1_status'] ?? '');
                        $reason    = trim($_POST['reason'] ?? '');
                        
                        $db = Database::getConnection();
                        if ($newStatus === 'completed') {
                            $stmt = $db->prepare("UPDATE requests SET process_1_status = ?, process_2_status = 'waiting_report', status = 'completed', updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                            $stmt->bind_param("si", $newStatus, $id);
                        } else {
                            $overallStatus = ($newStatus === 'need_revision') ? Config::STATUS_NEED_INFO : Config::STATUS_IN_REVIEW;
                            $stmt = $db->prepare("UPDATE requests SET process_1_status = ?, status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                            $stmt->bind_param("ssi", $newStatus, $overallStatus, $id);
                        }
                        $stmt->execute();
                        $stmt->close();
                        
                        WorkflowHistory::log($id, 'change_process_1', "ปรับปรุงสถานะขั้นตอนที่ 1 (ขออนุญาตจัดการศึกษา) เป็น '{$newStatus}' - หมายเหตุ: {$reason}", $_SESSION['officer_id']);

                        if (isset($_POST['send_email']) && $_POST['send_email'] == 1) {
                            $subject = "อัปเดตสถานะการพิจารณาคำขอจัดตั้งบ้านเรียน หมายเลข {$request['request_no']}";
                            $body = "
                                <div style='font-family: Arial, sans-serif; padding: 20px; line-height: 1.6;'>
                                    <h2 style='color: #1e3a8a;'>แจ้งอัปเดตสถานะขั้นตอนการขออนุญาตจัดการศึกษา</h2>
                                    <p>เรียน คุณ <strong>{$request['applicant_name']}</strong>,</p>
                                    <p>เจ้าหน้าที่ได้ปรับปรุงสถานะขั้นตอนที่ 1 (ขออนุญาตจัดการศึกษา) ของคำขอหมายเลข <strong>{$request['request_no']}</strong> เป็น:</p>
                                    <p style='font-size: 18px; color: #1e3a8a;'><strong>{$newStatus}</strong></p>
                                    <p><strong>รายละเอียดเพิ่มเติม/ข้อชี้แจง:</strong> " . (!empty($reason) ? esc($reason) : '-') . "</p>
                                    <p>ท่านสามารถเข้าสู่ระบบติดตามคำขอเพื่อเปิดอ่านรายละเอียดได้ทางเว็บไซต์</p>
                                    <p><a href='" . Config::SITE_URL . "/request/track?no=" . urlencode($request['request_no']) . "' style='padding: 8px 16px; background-color: #1e3a8a; color: white; text-decoration: none; border-radius: 4px; display: inline-block;'>ติดตามคำขอของท่าน</a></p>
                                </div>
                            ";
                            MailService::send($request['applicant_email'], $subject, $body);
                        }

                        $successMessage = "ปรับปรุงสถานะขั้นตอนที่ 1 (ขออนุญาตจัดการศึกษา) สำเร็จ";
                    }

                    elseif ($action === 'change_process_2') {
                        $newStatus = trim($_POST['process_2_status'] ?? '');
                        $reason    = trim($_POST['reason'] ?? '');
                        
                        $db = Database::getConnection();
                        if ($newStatus === 'report_completed') {
                            $stmt = $db->prepare("UPDATE requests SET process_2_status = ?, status = 'completed', updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                            $stmt->bind_param("si", $newStatus, $id);
                        } else {
                            $overallStatus = ($newStatus === 'report_review') ? Config::STATUS_IN_REVIEW : Config::STATUS_IN_REVIEW;
                            $stmt = $db->prepare("UPDATE requests SET process_2_status = ?, status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                            $stmt->bind_param("ssi", $newStatus, $overallStatus, $id);
                        }
                        $stmt->execute();
                        $stmt->close();
                        
                        WorkflowHistory::log($id, 'change_process_2', "ปรับปรุงสถานะขั้นตอนที่ 2 (ประเมินผลการเรียนรู้) เป็น '{$newStatus}' - หมายเหตุ: {$reason}", $_SESSION['officer_id']);

                        if (isset($_POST['send_email']) && $_POST['send_email'] == 1) {
                            $subject = "อัปเดตสถานะการประเมินผลสัมฤทธิ์บ้านเรียน หมายเลข {$request['request_no']}";
                            $body = "
                                <div style='font-family: Arial, sans-serif; padding: 20px; line-height: 1.6;'>
                                    <h2 style='color: #1e3a8a;'>แจ้งอัปเดตสถานะขั้นตอนการประเมินผลการเรียนรู้</h2>
                                    <p>เรียน คุณ <strong>{$request['applicant_name']}</strong>,</p>
                                    <p>เจ้าหน้าที่ได้ปรับปรุงสถานะขั้นตอนที่ 2 (การวัดและประเมินผลสัมฤทธิ์) ของคำขอหมายเลข <strong>{$request['request_no']}</strong> เป็น:</p>
                                    <p style='font-size: 18px; color: #1e3a8a;'><strong>{$newStatus}</strong></p>
                                    <p><strong>รายละเอียดเพิ่มเติม/ข้อชี้แจง:</strong> " . (!empty($reason) ? esc($reason) : '-') . "</p>
                                    <p>ท่านสามารถเข้าสู่ระบบติดตามคำขอเพื่อเปิดอ่านรายละเอียดได้ทางเว็บไซต์</p>
                                    <p><a href='" . Config::SITE_URL . "/request/track?no=" . urlencode($request['request_no']) . "' style='padding: 8px 16px; background-color: #1e3a8a; color: white; text-decoration: none; border-radius: 4px; display: inline-block;'>ติดตามคำขอของท่าน</a></p>
                                </div>
                            ";
                            MailService::send($request['applicant_email'], $subject, $body);
                        }

                        $successMessage = "ปรับปรุงสถานะขั้นตอนที่ 2 (ประเมินผลการเรียนรู้) สำเร็จ";
                    }
                    
                    elseif ($action === 'assign_officer') {
                        // RBAC: Check permission for assigning officers
                        if (!AuthMiddleware::hasPermission('requests.assign')) {
                            throw new Exception("เฉพาะผู้มีสิทธิ์ 'requests.assign' เท่านั้นที่สามารถมอบหมายงานได้");
                        }

                        $officerId = isset($_POST['officer_id']) && $_POST['officer_id'] !== '' ? (int)$_POST['officer_id'] : null;
                        
                        Request::assignOfficer($id, $officerId);
                        
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

                        WorkflowHistory::log($id, 'assign_officer', "มอบหมายผู้รับผิดชอบสำนวนคดีเป็น: " . $officerName, $_SESSION['officer_id']);
                        
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
                        $logType = $isInternal ? 'บันทึกข้อความภายใน (Internal Note)' : 'ส่งข้อความคุยประสานงาน';
                        RequestService::logAudit(
                            $_SESSION['officer_id'],
                            'officer',
                            "{$logType} บนคำขอ {$request['request_no']}",
                            'Requests',
                            $_SERVER['REMOTE_ADDR'] ?? '',
                            $_SERVER['HTTP_USER_AGENT'] ?? ''
                        );

                        WorkflowHistory::log($id, 'post_message', "เขียนบันทึก (" . ($isInternal ? 'บันทึกภายใน' : 'ติดต่อประชาชน') . "): " . $body, $_SESSION['officer_id']);

                        $successMessage = "ส่งข้อความสำเร็จ";
                    }

                    elseif ($action === 'upload_official_doc') {
                        if (!isset($_FILES['official_file']) || $_FILES['official_file']['error'] === UPLOAD_ERR_NO_FILE) {
                            throw new Exception("กรุณาเลือกไฟล์ที่ต้องการอัปโหลด");
                        }
                        $attachmentType = trim($_POST['attachment_type'] ?? 'official_letter');
                        
                        $fileMeta = UploadService::uploadPDF($_FILES['official_file']);
                        
                        RequestAttachment::create(
                            $id,
                            $fileMeta['original_name'],
                            $fileMeta['file_path'],
                            $fileMeta['mime_type'],
                            $fileMeta['file_size'],
                            'officer',
                            $attachmentType
                        );
                        
                        // Save in attachments too for NWT compatibility
                        $nextVer = Attachment::getNextVersion($id, $fileMeta['original_name']);
                        Attachment::create($id, $fileMeta['original_name'], $fileMeta['file_path'], $fileMeta['mime_type'], $fileMeta['file_size'], 'officer', $nextVer);
                        
                        WorkflowHistory::log($id, 'upload_official_doc', "อัปโหลดเอกสารราชการ: " . $fileMeta['original_name'] . " ประเภท: {$attachmentType}", $_SESSION['officer_id']);
                        
                        $successMessage = "อัปโหลดเอกสารฝ่ายงานสำเร็จ";
                    }

                    elseif ($action === 'log_meeting') {
                        $meetingDate   = trim($_POST['meeting_date'] ?? '');
                        $resultSummary = trim($_POST['result_summary'] ?? '');
                        
                        if (empty($meetingDate) || empty($resultSummary)) {
                            throw new Exception("กรุณากรอกวันที่ประชุมและสรุปผลมติ");
                        }
                        
                        $fileName = null;
                        $filePath = null;
                        $mimeType = null;
                        $fileSize = null;
                        
                        if (isset($_FILES['meeting_file']) && $_FILES['meeting_file']['error'] !== UPLOAD_ERR_NO_FILE) {
                            $fileMeta = UploadService::uploadPDF($_FILES['meeting_file']);
                            $fileName = $fileMeta['original_name'];
                            $filePath = $fileMeta['file_path'];
                            $mimeType = $fileMeta['mime_type'];
                            $fileSize = $fileMeta['file_size'];
                        }
                        
                        MeetingResult::create(
                            $id,
                            $meetingDate,
                            $resultSummary,
                            $fileName,
                            $filePath,
                            $mimeType,
                            $fileSize,
                            $_SESSION['officer_id']
                        );
                        
                        WorkflowHistory::log($id, 'log_meeting', "บันทึกผลการประชุมคณะทำงาน วันที่ " . $meetingDate . " มติ: " . mb_substr($resultSummary, 0, 50) . "...", $_SESSION['officer_id']);
                        
                        $successMessage = "บันทึกสรุปผลการประชุมคณะทำงานสำเร็จ";
                    }

                    elseif ($action === 'notify_applicant') {
                        $subj = trim($_POST['subject'] ?? '');
                        $bodyText = trim($_POST['body'] ?? '');
                        
                        if (empty($subj) || empty($bodyText)) {
                            throw new Exception("กรุณากรอกหัวข้อและเนื้อหารายละเอียดข้อความประชาสัมพันธ์");
                        }
                        
                        $emailBody = "
                            <div style='font-family: Arial, sans-serif; padding: 20px; line-height: 1.6;'>
                                <h2 style='color: #1e3a8a;'>แจ้งเตือนระบบจัดการบ้านเรียน สพม.นราธิวาส</h2>
                                <p>เรียน คุณ <strong>{$request['applicant_name']}</strong>,</p>
                                <div style='background-color: #f9fafb; padding: 15px; border-left: 4px solid #1e3a8a; border-radius: 4px;'>
                                    " . nl2br(esc($bodyText)) . "
                                </div>
                                <p style='margin-top: 15px;'>ท่านสามารถตรวจสอบสถานะคำขอหมายเลข <strong>{$request['request_no']}</strong> ได้ตลอดเวลาทางหน้าเว็บไซต์</p>
                                <p><a href='" . Config::SITE_URL . "/request/track?no=" . urlencode($request['request_no']) . "' style='padding: 8px 16px; background-color: #1e3a8a; color: white; text-decoration: none; border-radius: 4px; display: inline-block;'>ติดตามคำขอของท่าน</a></p>
                            </div>
                        ";
                        
                        MailService::send($request['applicant_email'], $subj, $emailBody);
                        
                        // Save a chat message from officer to show it in message history
                        Request::postMessage($id, 'officer', "[อีเมลแจ้งเตือน] หัวข้อ: {$subj}\n\n{$bodyText}", 0);
                        
                        WorkflowHistory::log($id, 'notify_applicant', "ส่งอีเมลแจ้งเตือนผู้รับบริการ: " . $subj, $_SESSION['officer_id']);
                        
                        $successMessage = "ส่งอีเมลแจ้งเตือนผู้ยื่นคำขอสำเร็จ";
                    }

                    // Reload request data after modifications
                    $request = Request::findById($id);

                } catch (Exception $e) {
                    $errorMessage = $e->getMessage();
                }
            }

            // Fetch related timeline, attachments, and chats
            $history        = Request::getStatusHistory($id);
            $attachments    = RequestAttachment::findByRequestId($id);
            $messages       = Request::getMessages($id, true); // Admin reads all including internal notes
            $meetingResults = MeetingResult::findByRequestId($id);
            $workflowLogs   = WorkflowHistory::findByRequestId($id);
            $applicantAccount = ApplicantAccount::findByApplicantId($request['applicant_id']);

            // Fetch officers list for assignment dropdown
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
