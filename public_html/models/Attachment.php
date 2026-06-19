<?php
namespace App\Models;

use App\Config\Database;
use Exception;

class Attachment {
    /**
     * Create an attachment record.
     * @param int $requestId
     * @param string $fileName
     * @param string $filePath
     * @param string $mimeType
     * @param int $fileSize
     * @param string $uploadedBy 'applicant' or 'officer'
     * @param int $version
     * @return int Inserted ID
     * @throws Exception
     */
    public static function create($requestId, $fileName, $filePath, $mimeType, $fileSize, $uploadedBy, $version = 1) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO attachments (request_id, file_name, file_path, mime_type, file_size, uploaded_by, version) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $db->error);
        }
        $stmt->bind_param("isssisi", $requestId, $fileName, $filePath, $mimeType, $fileSize, $uploadedBy, $version);
        if (!$stmt->execute()) {
            throw new Exception("Execute statement failed: " . $stmt->error);
        }
        $insertId = $stmt->insert_id;
        $stmt->close();
        return $insertId;
    }

    /**
     * Get attachments by Request ID.
     * @param int $requestId
     * @return array
     * @throws Exception
     */
    public static function findByRequestId($requestId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM attachments WHERE request_id = ? ORDER BY version DESC, created_at DESC");
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

    /**
     * Find attachment by ID.
     * @param int $id
     * @return array|null
     * @throws Exception
     */
    public static function findById($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM attachments WHERE id = ? LIMIT 1");
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

    /**
     * Determine next version number for a file in a request.
     * @param int $requestId
     * @param string $fileName
     * @return int
     * @throws Exception
     */
    public static function getNextVersion($requestId, $fileName) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT MAX(version) as max_ver FROM attachments WHERE request_id = ? AND file_name = ?");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $db->error);
        }
        $stmt->bind_param("is", $requestId, $fileName);
        if (!$stmt->execute()) {
            throw new Exception("Execute statement failed: " . $stmt->error);
        }
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row ? ((int)$row['max_ver'] + 1) : 1;
    }
}
