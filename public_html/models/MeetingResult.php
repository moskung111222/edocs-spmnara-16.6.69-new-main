<?php
namespace App\Models;

use App\Config\Database;
use Exception;

class MeetingResult {
    public static function create($requestId, $meetingDate, $resultSummary, $fileName = null, $filePath = null, $mimeType = null, $fileSize = null, $officerId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO meeting_results (request_id, meeting_date, result_summary, file_name, file_path, mime_type, file_size, officer_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $db->error);
        }
        $stmt->bind_param("isssssii", $requestId, $meetingDate, $resultSummary, $fileName, $filePath, $mimeType, $fileSize, $officerId);
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
            SELECT mr.*, o.name AS officer_name 
            FROM meeting_results mr 
            JOIN officers o ON mr.officer_id = o.id 
            WHERE mr.request_id = ? 
            ORDER BY mr.meeting_date DESC, mr.created_at DESC
        ");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $db->error);
        }
        $stmt->bind_param("i", $requestId);
        if (!$stmt->execute()) {
            throw new Exception("Execute statement failed: " . $stmt->error);
        }
        $result = $stmt->get_result();
        $results = [];
        while ($row = $result->fetch_assoc()) {
            $results[] = $row;
        }
        $stmt->close();
        return $results;
    }
}
