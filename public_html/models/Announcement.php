<?php
namespace App\Models;

use App\Config\Database;
use Exception;

class Announcement {
    public static function create($title, $content, $type, $authorId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("INSERT INTO announcements (title, content, type, author_id) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $db->error);
        }
        $stmt->bind_param("sssi", $title, $content, $type, $authorId);
        if (!$stmt->execute()) {
            throw new Exception("Execute statement failed: " . $stmt->error);
        }
        $insertId = $stmt->insert_id;
        $stmt->close();
        return $insertId;
    }

    public static function getAll() {
        $db = Database::getConnection();
        $result = $db->query("SELECT a.*, o.name AS author_name FROM announcements a LEFT JOIN officers o ON a.author_id = o.id ORDER BY a.created_at DESC");
        if (!$result) {
            throw new Exception("Query failed: " . $db->error);
        }
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        return $items;
    }

    public static function findById($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM announcements WHERE id = ? LIMIT 1");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $db->error);
        }
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $res;
    }

    public static function update($id, $title, $content, $type) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE announcements SET title = ?, content = ?, type = ? WHERE id = ?");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $db->error);
        }
        $stmt->bind_param("sssi", $title, $content, $type, $id);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }

    public static function delete($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM announcements WHERE id = ?");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $db->error);
        }
        $stmt->bind_param("i", $id);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }
}
