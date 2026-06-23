<?php
namespace App\Models;

use App\Config\Database;
use Exception;

class WorkflowHistory {
    public static function log($requestId, $action, $details, $officerId = null, $applicantId = null, $ip = null, $ua = null) {
        $db = Database::getConnection();
        $ip = $ip ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $ua = $ua ?? $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        
        $stmt = $db->prepare("
            INSERT INTO workflow_history (request_id, action, details, officer_id, applicant_id, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $db->error);
        }
        
        $stmt->bind_param("issiiss", $requestId, $action, $details, $officerId, $applicantId, $ip, $ua);
        if (!$stmt->execute()) {
            throw new Exception("Execute statement failed: " . $stmt->error);
        }
        $insertId = $stmt->insert_id;
        $stmt->close();
        return $insertId;
    }

    public static function findByRequestId($requestId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT wh.*, o.name AS officer_name, a.full_name AS applicant_name 
            FROM workflow_history wh 
            LEFT JOIN officers o ON wh.officer_id = o.id 
            LEFT JOIN applicants a ON wh.applicant_id = a.id 
            WHERE wh.request_id = ? 
            ORDER BY wh.created_at ASC
        ");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $db->error);
        }
        $stmt->bind_param("i", $requestId);
        if (!$stmt->execute()) {
            throw new Exception("Execute statement failed: " . $stmt->error);
        }
        $result = $stmt->get_result();
        $logs = [];
        while ($row = $result->fetch_assoc()) {
            $logs[] = $row;
        }
        $stmt->close();
        return $logs;
    }
}
