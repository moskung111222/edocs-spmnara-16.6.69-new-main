<?php
namespace App\Models;

use App\Config\Database;
use Exception;

class Permission {
    /**
     * Get all permissions.
     * @return array
     * @throws Exception
     */
    public static function getAll() {
        $db = Database::getConnection();
        $sql = "SELECT * FROM permissions ORDER BY module ASC, code ASC";
        $result = $db->query($sql);
        if (!$result) {
            throw new Exception("Query failed: " . $db->error);
        }
        $permissions = [];
        while ($row = $result->fetch_assoc()) {
            $permissions[] = $row;
        }
        return $permissions;
    }

    /**
     * Get all permissions grouped by module.
     * @return array Associative array [module => [permissions...]]
     * @throws Exception
     */
    public static function getAllGrouped() {
        $all = self::getAll();
        $grouped = [];
        foreach ($all as $perm) {
            $grouped[$perm['module']][] = $perm;
        }
        return $grouped;
    }

    /**
     * Get permissions by module.
     * @param string $module
     * @return array
     * @throws Exception
     */
    public static function getByModule($module) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM permissions WHERE module = ? ORDER BY code ASC");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $db->error);
        }
        $stmt->bind_param("s", $module);
        if (!$stmt->execute()) {
            throw new Exception("Execute statement failed: " . $stmt->error);
        }
        $result = $stmt->get_result();
        $permissions = [];
        while ($row = $result->fetch_assoc()) {
            $permissions[] = $row;
        }
        $stmt->close();
        return $permissions;
    }

    /**
     * Find permission by ID.
     * @param int $id
     * @return array|null
     * @throws Exception
     */
    public static function findById($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM permissions WHERE id = ? LIMIT 1");
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
     * Get distinct module names.
     * @return array
     * @throws Exception
     */
    public static function getModules() {
        $db = Database::getConnection();
        $result = $db->query("SELECT DISTINCT module FROM permissions ORDER BY module ASC");
        if (!$result) {
            throw new Exception("Query failed: " . $db->error);
        }
        $modules = [];
        while ($row = $result->fetch_assoc()) {
            $modules[] = $row['module'];
        }
        return $modules;
    }
}
