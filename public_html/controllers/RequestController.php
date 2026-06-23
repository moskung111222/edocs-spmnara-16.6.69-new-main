<?php
namespace App\Controllers;

use App\Config\Config;
use App\Config\Database;
use App\Models\Request;
use App\Models\Applicant;
use App\Models\Attachment;
use App\Models\Announcement;
use App\Models\Law;
use App\Models\DownloadDocument;
use App\Models\Infographic;
use App\Models\ApplicantAccount;
use App\Models\StaffMessage;
use App\Models\RequestAttachment;
use App\Models\MeetingResult;
use App\Models\WorkflowHistory;
use App\Services\RequestService;
use App\Services\UploadService;
use App\Services\MailService;
use App\Middleware\CsrfMiddleware;
use App\Middleware\AuthMiddleware;
use Exception;

class RequestController {
    /**
     * Helper to generate a random 8-character password.
     */
    private function generateRandomPassword($length = 8) {
        $chars = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        $pass = '';
        for ($i = 0; $i < $length; $i++) {
            $pass .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $pass;
    }

    /**
     * Show front page with announcements, laws, forms, infographics.
     */
    public function index() {
        try {
            $types = Request::getRequestTypes();
            $announcements = Announcement::getAll();
            $lawsGrouped = Law::getByCategory();
            $docsGrouped = DownloadDocument::getByCategory();
            $infographics = Infographic::getAll();
            
            include dirname(__DIR__) . '/views/public/index.php';
        } catch (Exception $e) {
            throw new Exception("เกิดข้อผิดพลาดในการโหลดหน้าหลัก: " . $e->getMessage());
        }
    }

    /**
     * Show and handle request creation.
     */
    public function create() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $typeId = (int)($_GET['type'] ?? 0);
        $types = Request::getRequestTypes();
        $selectedType = null;
        
        foreach ($types as $t) {
            if ((int)$t['id'] === $typeId) {
                $selectedType = $t;
                break;
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                CsrfMiddleware::validatePostRequest();

                if (!$selectedType) {
                    throw new Exception("ไม่พบประเภทคำขอที่ระบุ");
                }

                // Verify PDPA Consent
                if (!isset($_POST['pdpa_consent']) || $_POST['pdpa_consent'] != 1) {
                    throw new Exception("คุณต้องกดยอมรับนโยบายคุ้มครองข้อมูลส่วนบุคคล (PDPA) ก่อนดำเนินการยื่นคำขอ");
                }

                // Gather inputs
                $fullName = trim($_POST['full_name'] ?? '');
                $email    = trim($_POST['email'] ?? '');
                $phone    = trim($_POST['phone'] ?? '');

                if (empty($fullName) || empty($email) || empty($phone)) {
                    throw new Exception("กรุณากรอกข้อมูลส่วนตัวให้ครบถ้วน");
                }

                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception("รูปแบบอีเมลไม่ถูกต้อง");
                }

                // If applicant already exists, verify their password
                $existingApplicant = Applicant::findByEmail($email);
                $applicantId = null;
                $newAccountCreated = false;
                $generatedCode = null;
                $generatedPassword = null;

                if ($existingApplicant) {
                    $applicantId = $existingApplicant['id'];
                    $account = ApplicantAccount::findByApplicantId($applicantId);
                    if ($account) {
                        $inputPassword = trim($_POST['account_password'] ?? '');
                        if (empty($inputPassword)) {
                            throw new Exception("อีเมลนี้เคยลงทะเบียนในระบบแล้ว กรุณากรอกรหัสผ่านของคุณเพื่อยื่นคำขอเพิ่มเติม");
                        }
                        if (!password_verify($inputPassword, $account['password_hash'])) {
                            throw new Exception("รหัสผ่านไม่ถูกต้องสำหรับบัญชีอีเมลนี้");
                        }
                    }
                }

                // Gather dynamic checklist documents from files
                $checklist = $selectedType['doc_checklist'];
                $uploadedFiles = [];

                foreach ($checklist as $index => $docName) {
                    $fileKey = 'doc_file_' . $index;
                    if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] === UPLOAD_ERR_NO_FILE) {
                        throw new Exception("จำเป็นต้องแนบไฟล์: " . $docName);
                    }

                    // Process secure upload
                    $fileMeta = UploadService::uploadPDF($_FILES[$fileKey]);
                    $fileMeta['doc_label'] = $docName; // Attach the checklist label
                    $uploadedFiles[] = $fileMeta;
                }

                // Gather additional form fields
                $dynamicForm = $_POST['form_data'] ?? [];

                // Generate request number
                $requestNo = RequestService::generateRequestNo($selectedType['code']);

                // Create applicant if new
                if (!$applicantId) {
                    $generatedPassword = $this->generateRandomPassword(8);
                    $year = date('Y');
                    $latestNum = ApplicantAccount::getLatestCodeNumberForYear($year);
                    $nextNum = $latestNum + 1;
                    $generatedCode = "HSU-" . $year . "-" . str_pad($nextNum, 6, '0', STR_PAD_LEFT);

                    $applicantId = Applicant::create($fullName, $email, $phone);
                    ApplicantAccount::create($applicantId, $generatedCode, $generatedPassword);
                    $newAccountCreated = true;
                }

                // Create Request
                $requestId = Request::create($requestNo, $selectedType['id'], $applicantId, $dynamicForm);

                // Save Attachments
                foreach ($uploadedFiles as $index => $file) {
                    // Save to NWT attachments table for compatibility
                    Attachment::create(
                        $requestId,
                        $file['original_name'],
                        $file['file_path'],
                        $file['mime_type'],
                        $file['file_size'],
                        'applicant',
                        1
                    );

                    // Save to new request_attachments table
                    $attachmentType = ($index < 2) ? 'completed_form' : 'supporting_document';
                    RequestAttachment::create(
                        $requestId,
                        $file['original_name'],
                        $file['file_path'],
                        $file['mime_type'],
                        $file['file_size'],
                        'applicant',
                        $attachmentType
                    );
                }

                // Initial status history log
                Request::logStatusHistory($requestId, 'none', 'submitted', 'คำขอถูกยื่นผ่านหน้าเว็บโดยสมบูรณ์ (ยอมรับ PDPA)', null);

                // Log to workflow_history
                WorkflowHistory::log($requestId, 'submit_request', 'ผู้ยื่นคำขอจัดส่งเอกสารและสร้างคำขอสำเร็จ', null, $applicantId);

                // Trigger Pusher notification to admin
                \App\Services\PusherService::trigger('admin-channel', 'new-request', [
                    'request_no' => $requestNo,
                    'applicant_name' => $fullName,
                    'type_name' => $selectedType['name_th'],
                    'created_at' => date('d/m/Y H:i')
                ]);

                // Audit logging
                RequestService::logAudit(
                    $applicantId,
                    'applicant',
                    "ยื่นคำขอจัดการศึกษาออนไลน์ หมายเลข {$requestNo} สำเร็จ",
                    'Requests',
                    $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                    $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
                );

                // Save submission detail in session for success display
                $_SESSION['last_submission'] = [
                    'request_no' => $requestNo,
                    'new_account' => $newAccountCreated,
                    'applicant_code' => $generatedCode,
                    'password' => $generatedPassword
                ];

                // Auto-auth for immediate tracking access
                $_SESSION['tracking_auth'][$requestNo] = true;

                // Send email notification of submission success
                $subject = "รับข้อมูลคำขอจัดการบ้านเรียน หมายเลข {$requestNo} - สพม.นราธิวาส";
                $credentialsHtml = "";
                if ($newAccountCreated) {
                    $credentialsHtml = "
                        <div style='background-color: #f3f4f6; padding: 15px; border-radius: 8px; margin: 15px 0;'>
                            <p style='margin: 0;'><strong>รหัสบัญชีผู้ยื่น (Applicant Code):</strong> {$generatedCode}</p>
                            <p style='margin: 5px 0 0 0;'><strong>รหัสผ่านชั่วคราว (Password):</strong> {$generatedPassword}</p>
                        </div>
                    ";
                }
                $body = "
                    <div style='font-family: Arial, sans-serif; padding: 20px; line-height: 1.6;'>
                        <h2 style='color: #1e3a8a;'>ได้รับคำขอจัดการศึกษาขั้นพื้นฐานเรียบร้อยแล้ว</h2>
                        <p>เรียน คุณ <strong>{$fullName}</strong>,</p>
                        <p>ระบบจัดการบ้านเรียน สพม.นราธิวาส ได้รับข้อมูลคำขอของท่านเรียบร้อยแล้ว</p>
                        <ul>
                            <li><strong>เลขที่คำขอ:</strong> {$requestNo}</li>
                            <li><strong>ประเภทบริการ:</strong> {$selectedType['name_th']}</li>
                            <li><strong>วันที่ยื่น:</strong> " . date('d/m/Y H:i') . "</li>
                        </ul>
                        {$credentialsHtml}
                        <p>ท่านสามารถใช้เลขคำขอและรหัสผ่านเพื่อเข้าสู่ระบบติดตามสถานะคำขอทางหน้าเว็บไซต์</p>
                        <p><a href='" . Config::SITE_URL . "/request/track?no=" . urlencode($requestNo) . "' style='padding: 8px 16px; background-color: #1e3a8a; color: white; text-decoration: none; border-radius: 4px; display: inline-block;'>ติดตามคำขอของท่าน</a></p>
                        <hr style='border: 0; border-top: 1px solid #e5e7eb; margin: 20px 0;'>
                        <p style='font-size: 12px; color: #6b7280;'>กลุ่มส่งเสริมการจัดการศึกษา สำนักงานเขตพื้นที่การศึกษามัธยมศึกษานราธิวาส</p>
                    </div>
                ";
                MailService::send($email, $subject, $body);

                $_SESSION['flash_success'] = "ยื่นคำขอสำเร็จแล้ว!";
                header("Location: " . Config::SITE_URL . "/request/track?no=" . urlencode($requestNo));
                exit;

            } catch (Exception $e) {
                // Clean up any uploaded files in this request
                if (isset($uploadedFiles)) {
                    foreach ($uploadedFiles as $file) {
                        if (file_exists($file['file_path'])) {
                            unlink($file['file_path']);
                        }
                    }
                }
                $error = $e->getMessage();
            }
        }

        include dirname(__DIR__) . '/views/public/create.php';
    }

    /**
     * Bypassed OTP verify controller action. Redirect to home.
     */
    public function verify() {
        header("Location: " . Config::SITE_URL);
        exit;
    }

    /**
     * Handle request status tracking, timeline, documents, and chat.
     */
    public function track() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $requestNo = trim($_GET['no'] ?? '');
        $request   = null;
        $history   = [];
        $attachments = [];
        $messages  = [];
        $error     = '';

        if (!empty($requestNo)) {
            try {
                $request = Request::findByRequestNo($requestNo);
                if (!$request) {
                    throw new Exception("ไม่พบข้อมูลคำขอหมายเลข: " . htmlspecialchars($requestNo));
                }

                // Check authorization
                $isAuthenticated = isset($_SESSION['tracking_auth'][$requestNo]) && $_SESSION['tracking_auth'][$requestNo] === true;

                if (!$isAuthenticated) {
                    // Handle password verification
                    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_password'])) {
                        CsrfMiddleware::validatePostRequest();
                        $password = trim($_POST['password'] ?? '');

                        if (empty($password)) {
                            throw new Exception("กรุณากรอกรหัสผ่าน");
                        }

                        $account = ApplicantAccount::findByApplicantId($request['applicant_id']);
                        if ($account && password_verify($password, $account['password_hash'])) {
                            $_SESSION['tracking_auth'][$requestNo] = true;
                            header("Location: " . Config::SITE_URL . "/request/track?no=" . urlencode($requestNo));
                            exit;
                        } else {
                            $error = "รหัสผ่านไม่ถูกต้อง";
                        }
                    }
                } else {
                    // Process citizen actions if authenticated
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        CsrfMiddleware::validatePostRequest();
                        $action = $_POST['action'] ?? '';

                        if ($action === 'post_message') {
                            $body = trim($_POST['body'] ?? '');
                            if (empty($body)) {
                                  throw new Exception("กรุณากรอกข้อความสนทนา");
                            }
                            // Save to messages table for compatibility
                            Request::postMessage($request['id'], 'applicant', $body, 0);
                            
                            // Log to workflow_history
                            WorkflowHistory::log($request['id'], 'post_message', "ผู้ยื่นคำขอส่งข้อความ: " . $body, null, $request['applicant_id']);

                            \App\Services\PusherService::trigger("request-{$requestNo}", 'new-message', [
                                'sender_type' => 'applicant',
                                'body' => $body,
                                'internal_note' => 0,
                                'created_at' => date('d/m/Y H:i')
                            ]);

                            RequestService::logAudit(
                                $request['applicant_id'],
                                'applicant',
                                "ส่งข้อความบนคำขอ {$request['request_no']}",
                                'Requests',
                                $_SERVER['REMOTE_ADDR'] ?? '',
                                $_SERVER['HTTP_USER_AGENT'] ?? ''
                            );
                            $_SESSION['flash_success'] = "ส่งข้อความสำเร็จ";
                            header("Location: " . Config::SITE_URL . "/request/track?no=" . urlencode($requestNo));
                            exit;
                        } elseif ($action === 'upload_doc') {
                            if ($request['status'] !== Config::STATUS_NEED_INFO) {
                                throw new Exception("สามารถแนบเอกสารเพิ่มเติมได้เฉพาะเมื่อคำขออยู่ในสถานะ 'ขอข้อมูลเพิ่มเติม/แก้ไขเอกสาร' เท่านั้น");
                            }

                            if (!isset($_FILES['additional_doc']) || $_FILES['additional_doc']['error'] === UPLOAD_ERR_NO_FILE) {
                                throw new Exception("กรุณาเลือกไฟล์เอกสารที่ต้องการแนบ");
                            }

                            // Secure PDF upload
                            $fileMeta = UploadService::uploadPDF($_FILES['additional_doc']);

                            // Calculate version
                            $nextVer = Attachment::getNextVersion($request['id'], $fileMeta['original_name']);

                            // Save to attachments
                            Attachment::create(
                                $request['id'],
                                $fileMeta['original_name'],
                                $fileMeta['file_path'],
                                $fileMeta['mime_type'],
                                $fileMeta['file_size'],
                                'applicant',
                                $nextVer
                            );

                            // Save to request_attachments
                            RequestAttachment::create(
                                $request['id'],
                                $fileMeta['original_name'],
                                $fileMeta['file_path'],
                                $fileMeta['mime_type'],
                                $fileMeta['file_size'],
                                'applicant',
                                'supporting_document'
                            );

                            // Change status back to submitted
                            RequestService::changeStatus(
                                $request['id'],
                                Config::STATUS_SUBMITTED,
                                "ผู้ยื่นคำขอแนบเอกสารเพิ่มเติม: " . $fileMeta['original_name'],
                                null,
                                $_SERVER['REMOTE_ADDR'] ?? '',
                                $_SERVER['HTTP_USER_AGENT'] ?? ''
                            );
                            
                            // Log to workflow_history
                            WorkflowHistory::log($request['id'], 'upload_attachment', "ผู้ยื่นคำขออัปโหลดไฟล์หลักฐานเพิ่ม: " . $fileMeta['original_name'], null, $request['applicant_id']);

                            $_SESSION['flash_success'] = "อัปโหลดเอกสารเพิ่มเติมสำเร็จ";
                            header("Location: " . Config::SITE_URL . "/request/track?no=" . urlencode($requestNo));
                            exit;
                        }
                    }

                    // Fetch request details
                    $history     = Request::getStatusHistory($request['id']);
                    $attachments = RequestAttachment::findByRequestId($request['id']);
                    $messages    = Request::getMessages($request['id'], false); // Public chat only
                    $meetingResults = MeetingResult::findByRequestId($request['id']);
                }
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }

        include dirname(__DIR__) . '/views/public/track.php';
    }

    /**
     * Secure controller streaming download.
     */
    public function download() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        try {
            $fileId = (int)($_GET['id'] ?? 0);
            if ($fileId <= 0) {
                throw new Exception("ไม่ระบุรหัสเอกสาร");
            }

            // Get source: download_center, laws, meeting, request_attachment
            $source = $_GET['source'] ?? 'request_attachment';
            $filePath = '';
            $fileName = '';
            $mimeType = 'application/pdf';
            $fileSize = 0;

            if ($source === 'laws') {
                $item = Law::findById($fileId);
                if (!$item) throw new Exception("ไม่พบไฟล์เอกสารดังกล่าว");
                $filePath = $item['file_path'];
                $fileName = $item['file_name'];
                $fileSize = $item['file_size'];
            } elseif ($source === 'download_center') {
                $item = DownloadDocument::findById($fileId);
                if (!$item) throw new Exception("ไม่พบไฟล์เอกสารดังกล่าว");
                $filePath = $item['file_path'];
                $fileName = $item['file_name'];
                $fileSize = $item['file_size'];
                // Detect mime if possible or default to pdf
                $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                if ($ext === 'docx') $mimeType = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
                elseif ($ext === 'doc') $mimeType = 'application/msword';
            } elseif ($source === 'meeting') {
                $item = Database::getConnection()->query("SELECT * FROM meeting_results WHERE id = " . $fileId)->fetch_assoc();
                if (!$item) throw new Exception("ไม่พบไฟล์รายงานการประชุม");
                $filePath = $item['file_path'];
                $fileName = $item['file_name'];
                $fileSize = $item['file_size'];
                $mimeType = $item['mime_type'] ?? 'application/pdf';
            } else {
                // Request attachment download
                $attachment = RequestAttachment::findById($fileId);
                if (!$attachment) {
                    throw new Exception("ไม่พบเอกสารดังกล่าวในระบบ");
                }

                $request = Request::findById($attachment['request_id']);
                if (!$request) {
                    throw new Exception("เอกสารไม่สัมพันธ์กับระบบคำขอ");
                }

                // Check permissions: Officers or authorized citizens
                $hasPermission = false;
                if (isset($_SESSION['officer_id'])) {
                    $hasPermission = true;
                } elseif (isset($_SESSION['tracking_auth'][$request['request_no']]) && $_SESSION['tracking_auth'][$request['request_no']] === true) {
                    $hasPermission = true;
                }

                if (!$hasPermission) {
                    throw new Exception("ขออภัย คุณไม่มีสิทธิ์ในการดาวน์โหลดเอกสารชุดนี้");
                }

                $filePath = $attachment['file_path'];
                $fileName = $attachment['file_name'];
                $fileSize = $attachment['file_size'];
                $mimeType = $attachment['mime_type'];
            }

            if (empty($filePath) || !file_exists($filePath)) {
                throw new Exception("ระบบไม่สามารถดึงไฟล์เอกสารได้ (File Not Found)");
            }

            // Clean output buffers
            if (ob_get_level()) {
                ob_end_clean();
            }

            $disposition = 'inline';
            if (isset($_GET['download']) && $_GET['download'] == 1) {
                $disposition = 'attachment';
            }
            
            header('Content-Description: File Transfer');
            header('Content-Type: ' . $mimeType);
            header('Content-Disposition: ' . $disposition . '; filename="' . rawurlencode($fileName) . '"');
            header('Expires: 0');
            header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: ' . $fileSize);
            
            readfile($filePath);
            exit;

        } catch (Exception $e) {
            http_response_code(403);
            die("
                <div style='font-family: sans-serif; text-align: center; margin-top: 100px;'>
                    <h1 style='color: #dc2626;'>403 Access Denied</h1>
                    <p style='color: #4b5563;'>" . esc($e->getMessage()) . "</p>
                </div>
            ");
        }
    }
}
