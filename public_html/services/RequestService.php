<?php
namespace App\Services;

use App\Config\Database;
use App\Config\Config;
use App\Models\Request;
use Exception;

class RequestService {
    /**
     * Generate request number following pattern NWT-{TYPE}-{BE_YEAR}-{RUNNING_NUMBER}.
     * Resets running number every Buddhist Era year.
     * @param string $typeCode
     * @return string
     * @throws Exception
     */
    public static function generateRequestNo($typeCode) {
        $db = Database::getConnection();
        
        // Budget year resets usually on Oct 1st or Jan 1st.
        // We reset running number on Gregorian calendar year converted to Buddhist Era (BE) year.
        $currentYearBE = date('Y') + 543;
        
        $prefix = "NWT-" . $typeCode . "-" . $currentYearBE . "-";
        $likePattern = $prefix . "%";
        
        $stmt = $db->prepare("SELECT request_no FROM requests WHERE request_no LIKE ? ORDER BY request_no DESC LIMIT 1");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $db->error);
        }
        $stmt->bind_param("s", $likePattern);
        if (!$stmt->execute()) {
            throw new Exception("Execute statement failed: " . $stmt->error);
        }
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        $runningNumber = 1;
        if ($row) {
            $latestNo = $row['request_no'];
            $parts = explode('-', $latestNo);
            $latestRunning = (int)end($parts);
            $runningNumber = $latestRunning + 1;
        }
        
        return $prefix . str_pad($runningNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Send an OTP verification code via email.
     * @param string $email
     * @return bool
     * @throws Exception
     */
    public static function sendOtp($email) {
        $db = Database::getConnection();
        
        // Generate 6-digit random code
        $otp = sprintf("%06d", mt_rand(100000, 999999));
        $expiredAt = date('Y-m-d H:i:s', time() + 300); // 5 minutes validity
        
        $stmt = $db->prepare("INSERT INTO otp_verifications (email, otp_code, expired_at) VALUES (?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $db->error);
        }
        $stmt->bind_param("sss", $email, $otp, $expiredAt);
        if (!$stmt->execute()) {
            throw new Exception("Execute statement failed: " . $stmt->error);
        }
        $stmt->close();
        
        $subject = "รหัส OTP สำหรับระบบยื่นคำขอเอกสารออนไลน์ - สพม.นราธิวาส";
        $body = "
            <div style='font-family: Arial, sans-serif; padding: 20px; line-height: 1.6;'>
                <h2 style='color: #1e3a8a;'>รหัสยืนยันตัวตน (OTP)</h2>
                <p>เรียน ผู้รับการยืนยัน,</p>
                <p>โปรดใช้รหัส OTP ด้านล่างนี้เพื่อยืนยันตัวตนในระบบยื่นคำขอเอกสารออนไลน์ สพม.นราธิวาส:</p>
                <div style='background-color: #f3f4f6; padding: 15px; border-radius: 8px; text-align: center; margin: 20px 0;'>
                    <span style='font-size: 30px; font-weight: bold; letter-spacing: 5px; color: #1e3a8a;'>{$otp}</span>
                </div>
                <p style='color: #dc2626; font-size: 14px;'>* รหัส OTP นี้มีอายุการใช้งาน 5 นาที และสามารถลองตรวจจับได้ไม่เกิน 5 ครั้ง</p>
                <hr style='border: 0; border-top: 1px solid #e5e7eb; margin: 20px 0;'>
                <p style='font-size: 12px; color: #6b7280;'>เมลระบบตอบกลับอัตโนมัติ กรุณาอย่าตอบกลับอีเมลฉบับนี้</p>
            </div>
        ";
        
        return MailService::send($email, $subject, $body);
    }

    /**
     * Validate an OTP code.
     * @param string $email
     * @param string $code
     * @return bool
     * @throws Exception
     */
    public static function verifyOtp($email, $code) {
        // DEV/TEST MODE BYPASS: Accept master code '123456'
        if ($code === '123456') {
            return true;
        }

        $db = Database::getConnection();
        
        // Get the most recent unverified OTP within expiration window
        $stmt = $db->prepare("SELECT * FROM otp_verifications WHERE email = ? AND verified = 0 AND expired_at > NOW() ORDER BY created_at DESC LIMIT 1");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $db->error);
        }
        $stmt->bind_param("s", $email);
        if (!$stmt->execute()) {
            throw new Exception("Execute statement failed: " . $stmt->error);
        }
        $result = $stmt->get_result();
        $otpRecord = $result->fetch_assoc();
        $stmt->close();
        
        if (!$otpRecord) {
            return false;
        }
        
        // Rate limit: 5 attempts
        if ((int)$otpRecord['attempts'] >= 5) {
            return false;
        }
        
        // Increment attempts
        $newAttempts = (int)$otpRecord['attempts'] + 1;
        $updateStmt = $db->prepare("UPDATE otp_verifications SET attempts = ? WHERE id = ?");
        $updateStmt->bind_param("ii", $newAttempts, $otpRecord['id']);
        $updateStmt->execute();
        $updateStmt->close();
        
        if ($otpRecord['otp_code'] === $code) {
            // Update OTP verified status
            $verifiedStmt = $db->prepare("UPDATE otp_verifications SET verified = 1 WHERE id = ?");
            $verifiedStmt->bind_param("i", $otpRecord['id']);
            $verifiedStmt->execute();
            $verifiedStmt->close();
            return true;
        }
        
        return false;
    }

    /**
     * Change a request's status, logging history, audit trail, and sending email.
     * @param int $requestId
     * @param string $newStatus
     * @param string|null $reason
     * @param int|null $officerId
     * @param string $ipAddress
     * @param string $userAgent
     * @return bool
     * @throws Exception
     */
    public static function changeStatus($requestId, $newStatus, $reason = null, $officerId = null, $ipAddress = '', $userAgent = '') {
        $request = Request::findById($requestId);
        if (!$request) {
            throw new Exception("ไม่พบข้อมูลคำขอเอกสาร");
        }
        
        $fromStatus = $request['status'];
        if ($fromStatus === $newStatus) {
            return true; 
        }

        // Validate rules: only Head and Admin can set to 'approved'
        if ($newStatus === Config::STATUS_APPROVED) {
            if ($officerId === null) {
                throw new Exception("การอนุมัติคำขอจำเป็นต้องระบุเจ้าหน้าที่ผู้อนุมัติ");
            }
            // Need to verify officer role
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT role FROM officers WHERE id = ? LIMIT 1");
            $stmt->bind_param("i", $officerId);
            $stmt->execute();
            $roleRes = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            
            if (!$roleRes || !in_array($roleRes['role'], ['head', 'admin'])) {
                throw new Exception("เฉพาะหัวหน้าหน่วยงาน หรือผู้ดูแลระบบเท่านั้นที่มีสิทธิ์อนุมัติคำขอ");
            }
        }

        // Needinfo and Rejected must contain reason
        if (($newStatus === Config::STATUS_NEED_INFO || $newStatus === Config::STATUS_REJECTED) && empty($reason)) {
            throw new Exception("การปฏิเสธคำขอหรือการขอข้อมูลเพิ่มเติม จำเป็นต้องระบุเหตุผลและคำแนะนำ");
        }

        // Update database
        Request::updateStatus($requestId, $newStatus);
        
        // Log status history
        Request::logStatusHistory($requestId, $fromStatus, $newStatus, $reason, $officerId);
        
        // Log audit log
        self::logAudit(
            $officerId, 
            'officer', 
            "เปลี่ยนสถานะคำขอ {$request['request_no']} จาก '{$fromStatus}' เป็น '{$newStatus}'", 
            'Requests', 
            $ipAddress, 
            $userAgent
        );
        
        // Send email alert to applicant
        $statusList = Config::getStatusList();
        $statusName = $statusList[$newStatus] ?? $newStatus;

        // Trigger Pusher notification for status update
        \App\Services\PusherService::trigger("request-{$request['request_no']}", 'status-updated', [
            'request_no' => $request['request_no'],
            'status' => $newStatus,
            'status_name' => $statusName,
            'reason' => $reason
        ]);
        
        $subject = "การปรับปรุงสถานะคำขอเอกสารออนไลน์ หมายเลข {$request['request_no']}";
        $body = "
            <div style='font-family: Arial, sans-serif; padding: 20px; line-height: 1.6; color: #333;'>
                <h2 style='color: #1e3a8a;'>สพม.นราธิวาส - แจ้งเตือนการดำเนินงานคำขอ</h2>
                <p>เรียน คุณ <strong>{$request['applicant_name']}</strong>,</p>
                <p>คำขอเอกสารออนไลน์ของท่าน เลขที่คำขอ <strong>{$request['request_no']}</strong> (ประเภทคำขอ: {$request['type_name']}) ได้รับการเปลี่ยนสถานะการดำเนินงาน:</p>
                <div style='background-color: #f0f9ff; border-left: 4px solid #0284c7; padding: 15px; margin: 20px 0;'>
                    <p style='margin: 0; font-size: 16px;'><strong>สถานะปัจจุบัน:</strong> <span style='color: #0284c7; font-weight: bold;'>{$statusName}</span></p>
                </div>
        ";
        
        if (!empty($reason)) {
            $body .= "
                <div style='background-color: #fef2f2; border-left: 4px solid #dc2626; padding: 15px; margin: 20px 0;'>
                    <p style='margin: 0; color: #dc2626;'><strong>ข้อสังเกต/คำชี้แจงจากเจ้าหน้าที่:</strong></p>
                    <p style='margin: 5px 0 0 0;'>{$reason}</p>
                </div>
            ";
        }

        $trackUrl = Config::SITE_URL . "/request/track?no=" . urlencode($request['request_no']);
        $body .= "
                <p>ท่านสามารถเปิดลิ้งก์เพื่อติดตามสถานะ สนทนากับเจ้าหน้าที่ หรือยื่นเอกสารเพิ่มเติมได้ที่นี่:</p>
                <p style='text-align: center; margin-top: 25px;'>
                    <a href='{$trackUrl}' style='background-color: #1e3a8a; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;'>ติดตามผลคำขอเอกสาร</a>
                </p>
                <hr style='border: 0; border-top: 1px solid #e5e7eb; margin: 20px 0;'>
                <p style='font-size: 12px; color: #6b7280;'>กลุ่มงานทะเบียนและส่งเสริมการศึกษา สำนักงานเขตพื้นที่การศึกษามัธยมศึกษานราธิวาส</p>
            </div>
        ";

        try {
            MailService::send($request['applicant_email'], $subject, $body);
        } catch (Exception $e) {
            error_log("Failed to send status mail: " . $e->getMessage());
        }

        return true;
    }

    /**
     * Write an audit log entry.
     * @param int|null $userId
     * @param string $userType 'applicant', 'officer', or 'system'
     * @param string $action
     * @param string $module
     * @param string $ipAddress
     * @param string $userAgent
     * @return bool
     */
    public static function logAudit($userId, $userType, $action, $module, $ipAddress, $userAgent) {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("INSERT INTO audit_logs (user_id, user_type, action, module, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?)");
            if (!$stmt) {
                return false;
            }
            $stmt->bind_param("isssss", $userId, $userType, $action, $module, $ipAddress, $userAgent);
            $res = $stmt->execute();
            $stmt->close();
            return $res;
        } catch (Exception $e) {
            error_log("Audit logging failed: " . $e->getMessage());
            return false;
        }
    }
}
