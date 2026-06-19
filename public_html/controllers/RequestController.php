<?php
namespace App\Controllers;

use App\Config\Config;
use App\Config\Database;
use App\Models\Request;
use App\Models\Applicant;
use App\Models\Attachment;
use App\Services\RequestService;
use App\Services\UploadService;
use App\Services\MailService;
use App\Middleware\CsrfMiddleware;
use App\Middleware\AuthMiddleware;
use Exception;

class RequestController {
    /**
     * Show front page with list of request types.
     */
    public function index() {
        try {
            $types = Request::getRequestTypes();
            include dirname(__DIR__) . '/views/public/index.php';
        } catch (Exception $e) {
            throw new Exception("เกิดข้อผิดพลาดในการโหลดประเภทคำขอ: " . $e->getMessage());
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

                // Gather additional form fields (e.g. school name, year of graduation)
                $dynamicForm = $_POST['form_data'] ?? [];
                
                // Store in session temporarily
                $_SESSION['temp_submission'] = [
                    'type_id'        => $selectedType['id'],
                    'type_code'      => $selectedType['code'],
                    'type_name'      => $selectedType['name_th'],
                    'full_name'      => $fullName,
                    'email'          => $email,
                    'phone'          => $phone,
                    'form_data'      => $dynamicForm,
                    'uploaded_files' => $uploadedFiles
                ];

                // Trigger OTP
                RequestService::sendOtp($email);

                header("Location: " . Config::SITE_URL . "/request/verify");
                exit;

            } catch (Exception $e) {
                // If error occurs, clean up any uploaded files in this request
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
     * Handle OTP verification.
     */
    public function verify() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $temp = $_SESSION['temp_submission'] ?? null;
        if (!$temp) {
            header("Location: " . Config::SITE_URL);
            exit;
        }

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                CsrfMiddleware::validatePostRequest();
                
                $otpCode = trim($_POST['otp_code'] ?? '');
                if (empty($otpCode)) {
                    throw new Exception("กรุณากรอกรหัส OTP");
                }

                $email = $temp['email'];
                $isVerified = RequestService::verifyOtp($email, $otpCode);

                if ($isVerified) {
                    // 1. Create or fetch Applicant
                    $applicant = Applicant::findByEmail($email);
                    if ($applicant) {
                        $applicantId = $applicant['id'];
                    } else {
                        $applicantId = Applicant::create($temp['full_name'], $email, $temp['phone']);
                    }

                    // 2. Generate unique Request Number
                    $requestNo = RequestService::generateRequestNo($temp['type_code']);

                    // 3. Create Request
                    $requestId = Request::create($requestNo, $temp['type_id'], $applicantId, $temp['form_data']);

                    // 4. Save Attachments
                    foreach ($temp['uploaded_files'] as $file) {
                        Attachment::create(
                            $requestId,
                            $file['original_name'],
                            $file['file_path'],
                            $file['mime_type'],
                            $file['file_size'],
                            'applicant',
                            1
                        );
                    }

                    // 5. Initial status history log
                    Request::logStatusHistory($requestId, 'none', 'submitted', 'คำขอถูกยื่นผ่านหน้าเว็บโดยสมบูรณ์', null);

                    // Trigger Pusher notification to admin
                    \App\Services\PusherService::trigger('admin-channel', 'new-request', [
                        'request_no' => $requestNo,
                        'applicant_name' => $temp['full_name'],
                        'type_name' => $temp['type_name'],
                        'created_at' => date('d/m/Y H:i')
                    ]);

                    // 6. Audit logging
                    RequestService::logAudit(
                        $applicantId,
                        'applicant',
                        "ยื่นคำขอเอกสารออนไลน์ หมายเลข {$requestNo} สำเร็จ",
                        'Requests',
                        $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                        $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
                    );

                    // 7. Clear temp session
                    unset($_SESSION['temp_submission']);

                    // 8. Auto-auth for immediate tracking access
                    $_SESSION['tracking_auth'][$requestNo] = true;

                    // Send email notification of submission success
                    $subject = "รับข้อมูลคำขอเอกสารออนไลน์ หมายเลข {$requestNo} - สพม.นราธิวาส";
                    $body = "
                        <div style='font-family: Arial, sans-serif; padding: 20px; line-height: 1.6;'>
                            <h2 style='color: #1e3a8a;'>ได้รับคำขอเอกสารออนไลน์เรียบร้อยแล้ว</h2>
                            <p>เรียน คุณ <strong>{$temp['full_name']}</strong>,</p>
                            <p>ระบบงานสารบรรณอิเล็กทรอนิกส์ สพม.นราธิวาส ได้รับคำขอออนไลน์ของท่านเรียบร้อยแล้ว</p>
                            <ul>
                                <li><strong>เลขที่คำขอ:</strong> {$requestNo}</li>
                                <li><strong>ประเภทคำขอ:</strong> {$temp['type_name']}</li>
                                <li><strong>วันที่ยื่น:</strong> " . date('d/m/Y H:i') . "</li>
                            </ul>
                            <p>ขณะนี้คำขออยู่ในระหว่างรอการตรวจสอบเอกสารจากเจ้าหน้าที่ ท่านสามารถติดตามสถานะของเอกสารได้โดยการใช้รหัสคำขอด้านบนในหน้าเว็บไซต์</p>
                            <p><a href='" . Config::SITE_URL . "/request/track?no=" . urlencode($requestNo) . "' style='padding: 8px 16px; background-color: #1e3a8a; color: white; text-decoration: none; border-radius: 4px; display: inline-block;'>ติดตามคำขอของท่าน</a></p>
                            <hr style='border: 0; border-top: 1px solid #e5e7eb; margin: 20px 0;'>
                            <p style='font-size: 12px; color: #6b7280;'>สำนักงานเขตพื้นที่การศึกษามัธยมศึกษานราธิวาส (สพม.นราธิวาส)</p>
                        </div>
                    ";
                    MailService::send($email, $subject, $body);

                    $_SESSION['flash_success'] = "ยื่นคำขอเรียบร้อยแล้ว! รหัสคำขอของคุณคือ: " . $requestNo;
                    header("Location: " . Config::SITE_URL . "/request/track?no=" . urlencode($requestNo));
                    exit;
                } else {
                    throw new Exception("รหัส OTP ไม่ถูกต้องหรือหมดอายุการใช้งานแล้ว");
                }
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }

        // Fetch latest OTP for dev mode auto-fill
        $devOtp = '';
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT otp_code FROM otp_verifications WHERE email = ? ORDER BY created_at DESC LIMIT 1");
            $stmt->bind_param("s", $temp['email']);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            $devOtp = $res['otp_code'] ?? '';
        } catch (Exception $e) {
            // ignore
        }

        include dirname(__DIR__) . '/views/public/verify.php';
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
                    // Send OTP to tracking email if OTP request was submitted
                    if (isset($_POST['send_track_otp'])) {
                        RequestService::sendOtp($request['applicant_email']);
                        $_SESSION['track_otp_sent_to'] = $request['applicant_email'];
                        $_SESSION['flash_success'] = "ระบบได้ส่งรหัส OTP ไปยังอีเมล " . substr($request['applicant_email'], 0, 3) . "*****" . strstr($request['applicant_email'], '@') . " เรียบร้อยแล้ว";
                    }

                    // Process OTP verification
                    if (isset($_POST['verify_track_otp'])) {
                        CsrfMiddleware::validatePostRequest();
                        $code = trim($_POST['otp_code'] ?? '');
                        if (RequestService::verifyOtp($request['applicant_email'], $code)) {
                            $_SESSION['tracking_auth'][$requestNo] = true;
                            unset($_SESSION['track_otp_sent_to']);
                            header("Location: " . Config::SITE_URL . "/request/track?no=" . urlencode($requestNo));
                            exit;
                        } else {
                            $error = "รหัส OTP ไม่ถูกต้อง หรือหมดอายุ";
                        }
                    }

                    // Fetch latest tracking OTP for dev mode auto-fill
                    $devOtp = '';
                    if (isset($_SESSION['track_otp_sent_to'])) {
                        try {
                            $db = Database::getConnection();
                            $stmt = $db->prepare("SELECT otp_code FROM otp_verifications WHERE email = ? ORDER BY created_at DESC LIMIT 1");
                            $stmt->bind_param("s", $request['applicant_email']);
                            $stmt->execute();
                            $res = $stmt->get_result()->fetch_assoc();
                            $stmt->close();
                            $devOtp = $res['otp_code'] ?? '';
                        } catch (Exception $e) {
                            // ignore
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
                            Request::postMessage($request['id'], 'applicant', $body, 0);

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
                            $_SESSION['flash_success'] = "ส่งข้อความสนทนาสำเร็จ";
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

                            Attachment::create(
                                $request['id'],
                                $fileMeta['original_name'],
                                $fileMeta['file_path'],
                                $fileMeta['mime_type'],
                                $fileMeta['file_size'],
                                'applicant',
                                $nextVer
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

                            $_SESSION['flash_success'] = "อัปโหลดเอกสารเพิ่มเติมและแจ้งเจ้าหน้าที่ตรวจรับแล้ว";
                            header("Location: " . Config::SITE_URL . "/request/track?no=" . urlencode($requestNo));
                            exit;
                        }
                    }

                    // Fetch request details
                    $history     = Request::getStatusHistory($request['id']);
                    $attachments = Attachment::findByRequestId($request['id']);
                    $messages    = Request::getMessages($request['id'], false); // Public chat only
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

            $attachment = Attachment::findById($fileId);
            if (!$attachment) {
                throw new Exception("ไม่พบเอกสารดังกล่าวในระบบ");
            }

            $request = Request::findById($attachment['request_id']);
            if (!$request) {
                throw new Exception("เอกสารไม่สัมพันธ์กับระบบคำขอ");
            }

            // Check permissions: Officers (any role) or Citizens holding tracking auth
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
            if (!file_exists($filePath)) {
                throw new Exception("ระบบไม่สามารถดึงไฟล์เอกสารจากพื้นที่เก็บข้อมูลลับได้ (File Not Found)");
            }

            // Clean output buffers
            if (ob_get_level()) {
                ob_end_clean();
            }

            // Set headers for secure inline PDF display/download
            $disposition = 'inline';
            if (isset($_GET['download']) && $_GET['download'] == 1) {
                $disposition = 'attachment';
            }
            header('Content-Description: File Transfer');
            header('Content-Type: ' . $attachment['mime_type']);
            header('Content-Disposition: ' . $disposition . '; filename="' . rawurlencode($attachment['file_name']) . '"');
            header('Expires: 0');
            header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: ' . $attachment['file_size']);
            
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
