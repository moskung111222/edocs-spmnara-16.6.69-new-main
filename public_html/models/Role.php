<?php
namespace App\Models;

use App\Config\Database;
use Exception;

class Role {
    /**
     * Get all roles.
     * @param bool $activeOnly
     * @return array
     * @throws Exception
     */
    public static function getAll($activeOnly = false) {
        $db = Database::getConnection();
        $sql = "SELECT r.*, 
                (SELECT COUNT(*) FROM officers o WHERE o.role = r.code) AS officer_count
                FROM roles r";
        if ($activeOnly) {
            $sql .= " WHERE r.active = 1";
        }
        $sql .= " ORDER BY r.id ASC";

        $result = $db->query($sql);
        if (!$result) {
            throw new Exception("Query failed: " . $db->error);
        }
        $roles = [];
        while ($row = $result->fetch_assoc()) {
            $roles[] = $row;
        }
        return $roles;
    }

    /**
     * Find role by ID.
     * @param int $id
     * @return array|null
     * @throws Exception
     */
    public static function findById($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM roles WHERE id = ? LIMIT 1");
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
     * Find role by code.
     * @param string $code
     * @return array|null
     * @throws Exception
     */
    public static function findByCode($code) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM roles WHERE code = ? LIMIT 1");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $db->error);
        }
        $stmt->bind_param("s", $code);
        if (!$stmt->execute()) {
            throw new Exception("Execute statement failed: " . $stmt->error);
        }
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    /**
     * Get permissions assigned to a role.
     * @param int $roleId
     * @return array
     * @throws Exception
     */
    public static function getPermissions($roleId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT p.* 
            FROM permissions p
            JOIN role_permissions rp ON p.id = rp.permission_id
            WHERE rp.role_id = ?
            ORDER BY p.module ASC, p.code ASC
        ");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $db->error);
        }
        $stmt->bind_param("i", $roleId);
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
     * Get permission codes assigned to a role.
     * @param string $roleCode
     * @return array Array of permission code strings
     * @throws Exception
     */
    public static function getPermissionCodes($roleCode) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT p.code 
            FROM permissions p
            JOIN role_permissions rp ON p.id = rp.permission_id
            JOIN roles r ON r.id = rp.role_id
            WHERE r.code = ?
            ORDER BY p.code ASC
        ");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $db->error);
        }
        $stmt->bind_param("s", $roleCode);
        if (!$stmt->execute()) {
            throw new Exception("Execute statement failed: " . $stmt->error);
        }
        $result = $stmt->get_result();
        $codes = [];
        while ($row = $result->fetch_assoc()) {
            $codes[] = $row['code'];
        }
        $stmt->close();
        return $codes;
    }

    /**
     * Sync permissions for a role (replace all).
     * @param int $roleId
     * @param array $permissionIds
     * @return bool
     * @throws Exception
     */
    public static function syncPermissions($roleId, array $permissionIds) {
        $db = Database::getConnection();

        // Remove all existing
        $stmt = $db->prepare("DELETE FROM role_permissions WHERE role_id = ?");
        $stmt->bind_param("i", $roleId);
        $stmt->execute();
        $stmt->close();

        // Insert new
        foreach ($permissionIds as $permId) {
            $stmt = $db->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $roleId, $permId);
            $stmt->execute();
            $stmt->close();
        }
        return true;
    }

    /**
     * Create a new role.
     * @param string $code
     * @param string $nameTh
     * @param string|null $description
     * @return int
     * @throws Exception
     */
    public static function create($code, $nameTh, $description = null) {
        $db = Database::getConnection();
        $stmt = $db->prepare("INSERT INTO roles (code, name_th, description) VALUES (?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $db->error);
        }
        $stmt->bind_param("sss", $code, $nameTh, $description);
        if (!$stmt->execute()) {
            throw new Exception("Execute statement failed: " . $stmt->error);
        }
        $insertId = $stmt->insert_id;
        $stmt->close();
        return $insertId;
    }

    /**
     * Update a role.
     * @param int $id
     * @param string $nameTh
     * @param string|null $description
     * @return bool
     * @throws Exception
     */
    public static function update($id, $nameTh, $description = null) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE roles SET name_th = ?, description = ? WHERE id = ?");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $db->error);
        }
        $stmt->bind_param("ssi", $nameTh, $description, $id);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }
}
