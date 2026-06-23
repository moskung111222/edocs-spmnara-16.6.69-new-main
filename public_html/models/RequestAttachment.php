<?php
namespace App\Models;

use App\Config\Database;
use Exception;

class RequestAttachment {
    public static function create($requestId, $fileName, $filePath, $mimeType, $fileSize, $uploadedBy, $attachmentType) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO request_attachments (request_id, file_name, file_path, mime_type, file_size, uploaded_by, attachment_type) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $db->error);
        }
        $stmt->bind_param("isssiss", $requestId, $fileName, $filePath, $mimeType, $fileSize, $uploadedBy, $attachmentType);
        if (!$stmt->execute()) {
            throw new Exception("Execute statement failed: " . $stmt->error);
        }
        $insertId = $stmt->insert_id;
        $stmt->close();
        return $insertId;
    }

    public static function findByRequestId($requestId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM request_attachments WHERE request_id = ? ORDER BY created_at DESC");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $db->error);
        }
        $stmt->bind_param("i", $requestId);
        if (!$stmt->execute()) {
            throw new Exception("Execute statement failed: " . $stmt->error);
        }
        $result = $stmt->get_result();
        $attachments = [];
        while ($row = $result->fetch_assoc()) {
            $attachments[] = $row;
        }
        $stmt->close();
        return $attachments;
    }

    public static function findById($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM request_attachments WHERE id = ? LIMIT 1");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $db->error);
        }
        $stmt->bind_param("i", $id);
        if (!$stmt->execute()) {
            throw new Exception("Execute statement failed: " . $stmt->error);
        }
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }
}
