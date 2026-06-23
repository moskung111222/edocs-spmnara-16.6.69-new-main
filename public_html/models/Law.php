<?php
namespace App\Models;

use App\Config\Database;
use Exception;

class Law {
    public static function create($title, $category, $fileName, $filePath, $fileSize, $uploadedBy) {
        $db = Database::getConnection();
        $stmt = $db->prepare("INSERT INTO laws (title, category, file_name, file_path, file_size, uploaded_by) VALUES (?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $db->error);
        }
        $stmt->bind_param("ssssii", $title, $category, $fileName, $filePath, $fileSize, $uploadedBy);
        if (!$stmt->execute()) {
            throw new Exception("Execute statement failed: " . $stmt->error);
        }
        $insertId = $stmt->insert_id;
        $stmt->close();
        return $insertId;
    }

    public static function getAll() {
        $db = Database::getConnection();
        $result = $db->query("SELECT l.*, o.name AS uploader_name FROM laws l LEFT JOIN officers o ON l.uploaded_by = o.id ORDER BY l.created_at DESC");
        if (!$result) {
            throw new Exception("Query failed: " . $db->error);
        }
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        return $items;
    }

    public static function getByCategory() {
        $laws = self::getAll();
        $grouped = [];
        foreach ($laws as $l) {
            $grouped[$l['category']][] = $l;
        }
        return $grouped;
    }

    public static function findById($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM laws WHERE id = ? LIMIT 1");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $db->error);
        }
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $res;
    }

    public static function delete($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM laws WHERE id = ?");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $db->error);
        }
        $stmt->bind_param("i", $id);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }
}
