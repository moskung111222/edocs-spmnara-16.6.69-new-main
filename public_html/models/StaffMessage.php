<?php
namespace App\Models;

use App\Config\Database;
use Exception;

class StaffMessage {
    public static function create($requestId, $officerId, $message) {
        $db = Database::getConnection();
        $stmt = $db->prepare("INSERT INTO staff_messages (request_id, officer_id, message) VALUES (?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $db->error);
        }
        $stmt->bind_param("iis", $requestId, $officerId, $message);
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
            SELECT sm.*, o.name AS officer_name 
            FROM staff_messages sm 
            JOIN officers o ON sm.officer_id = o.id 
            WHERE sm.request_id = ? 
            ORDER BY sm.created_at ASC
        ");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $db->error);
        }
        $stmt->bind_param("i", $requestId);
        if (!$stmt->execute()) {
            throw new Exception("Execute statement failed: " . $stmt->error);
        }
        $result = $stmt->get_result();
        $messages = [];
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
        $stmt->close();
        return $messages;
    }
}
