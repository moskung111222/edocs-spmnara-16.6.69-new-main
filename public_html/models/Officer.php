<?php
namespace App\Models;

use App\Config\Database;
use Exception;

class Officer {
    /**
     * Create a new officer.
     * @param string $username
     * @param string $passwordHash
     * @param string $name
     * @param string $email
     * @param string $role
     * @param int|null $departmentId
     * @return int Inserted ID
     * @throws Exception
     */
    public static function create($username, $passwordHash, $name, $email, $role = 'staff', $departmentId = null) {
        $db = Database::getConnection();
        $stmt = $db->prepare("INSERT INTO officers (username, password_hash, name, email, role, department_id) VALUES (?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $db->error);
        }
        $stmt->bind_param("sssssi", $username, $passwordHash, $name, $email, $role, $departmentId);
        if (!$stmt->execute()) {
            throw new Exception("Execute statement failed: " . $stmt->error);
        }
        $insertId = $stmt->insert_id;
        $stmt->close();
        return $insertId;
    }

    /**
     * Find officer by ID.
     * @param int $id
     * @return array|null
     * @throws Exception
     */
    public static function findById($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT o.*, d.name_th AS department_name, d.code AS department_code
            FROM officers o
            LEFT JOIN departments d ON o.department_id = d.id
            WHERE o.id = ? LIMIT 1
        ");
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
     * Find officer by username.
     * @param string $username
     * @return array|null
     * @throws Exception
     */
    public static function findByUsername($username) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM officers WHERE username = ? LIMIT 1");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $db->error);
        }
        $stmt->bind_param("s", $username);
        if (!$stmt->execute()) {
            throw new Exception("Execute statement failed: " . $stmt->error);
        }
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    /**
     * Get all officers with department info.
     * @param array $filters Optional filters (department_id, role, search, active)
     * @return array
     * @throws Exception
     */
    public static function getAll(array $filters = []) {
        $db = Database::getConnection();
        $sql = "
            SELECT o.*, d.name_th AS department_name, d.code AS department_code
            FROM officers o
            LEFT JOIN departments d ON o.department_id = d.id
            WHERE 1=1
        ";
        $params = [];
        $types = "";

        if (!empty($filters['department_id'])) {
            $sql .= " AND o.department_id = ?";
            $params[] = (int)$filters['department_id'];
            $types .= "i";
        }
        if (!empty($filters['role'])) {
            $sql .= " AND o.role = ?";
            $params[] = $filters['role'];
            $types .= "s";
        }
        if (isset($filters['active']) && $filters['active'] !== '') {
            $sql .= " AND o.active = ?";
            $params[] = (int)$filters['active'];
            $types .= "i";
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (o.name LIKE ? OR o.username LIKE ? OR o.email LIKE ?)";
            $searchTerm = "%" . $filters['search'] . "%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= "sss";
        }

        $sql .= " ORDER BY o.role ASC, o.name ASC";

        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $db->error);
        }
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        if (!$stmt->execute()) {
            throw new Exception("Execute statement failed: " . $stmt->error);
        }
        $result = $stmt->get_result();
        $officers = [];
        while ($row = $result->fetch_assoc()) {
            $officers[] = $row;
        }
        $stmt->close();
        return $officers;
    }

    /**
     * Update officer info.
     * @param int $id
     * @param string $name
     * @param string $email
     * @param string $role
     * @param int|null $departmentId
     * @return bool
     * @throws Exception
     */
    public static function update($id, $name, $email, $role, $departmentId = null) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE officers SET name = ?, email = ?, role = ?, department_id = ? WHERE id = ?");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $db->error);
        }
        $stmt->bind_param("sssii", $name, $email, $role, $departmentId, $id);
        $res = $stmt->execute();
        if (!$res) {
            throw new Exception("Execute statement failed: " . $stmt->error);
        }
        $stmt->close();
        return $res;
    }

    /**
     * Update officer password.
     * @param int $id
     * @param string $passwordHash
     * @return bool
     * @throws Exception
     */
    public static function updatePassword($id, $passwordHash) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE officers SET password_hash = ? WHERE id = ?");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $db->error);
        }
        $stmt->bind_param("si", $passwordHash, $id);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }

    /**
     * Toggle officer active/inactive.
     * @param int $id
     * @return bool
     * @throws Exception
     */
    public static function toggleActive($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE officers SET active = NOT active WHERE id = ?");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $db->error);
        }
        $stmt->bind_param("i", $id);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }

    /**
     * Get departments assigned to an officer (M:N relationship).
     * @param int $officerId
     * @return array
     * @throws Exception
     */
    public static function getDepartments($officerId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT d.*, od.is_head, od.assigned_at
            FROM departments d
            JOIN officer_departments od ON d.id = od.department_id
            WHERE od.officer_id = ?
            ORDER BY d.sort_order ASC
        ");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $db->error);
        }
        $stmt->bind_param("i", $officerId);
        if (!$stmt->execute()) {
            throw new Exception("Execute statement failed: " . $stmt->error);
        }
        $result = $stmt->get_result();
        $departments = [];
        while ($row = $result->fetch_assoc()) {
            $departments[] = $row;
        }
        $stmt->close();
        return $departments;
    }

    /**
     * Assign officer to department.
     * @param int $officerId
     * @param int $departmentId
     * @param bool $isHead
     * @return bool
     * @throws Exception
     */
    public static function assignDepartment($officerId, $departmentId, $isHead = false) {
        $db = Database::getConnection();
        $isHeadInt = $isHead ? 1 : 0;
        $stmt = $db->prepare("INSERT INTO officer_departments (officer_id, department_id, is_head) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE is_head = ?");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $db->error);
        }
        $stmt->bind_param("iiii", $officerId, $departmentId, $isHeadInt, $isHeadInt);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }

    /**
     * Remove officer from department.
     * @param int $officerId
     * @param int $departmentId
     * @return bool
     * @throws Exception
     */
    public static function removeDepartment($officerId, $departmentId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM officer_departments WHERE officer_id = ? AND department_id = ?");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $db->error);
        }
        $stmt->bind_param("ii", $officerId, $departmentId);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }

    /**
     * Sync officer departments (replace all assignments).
     * @param int $officerId
     * @param array $departmentIds Array of department IDs
     * @param int|null $headDepartmentId Department ID where officer is head
     * @return bool
     * @throws Exception
     */
    public static function syncDepartments($officerId, array $departmentIds, $headDepartmentId = null) {
        $db = Database::getConnection();

        // Remove all existing assignments
        $stmt = $db->prepare("DELETE FROM officer_departments WHERE officer_id = ?");
        $stmt->bind_param("i", $officerId);
        $stmt->execute();
        $stmt->close();

        // Insert new assignments
        foreach ($departmentIds as $deptId) {
            $isHead = ($headDepartmentId !== null && (int)$deptId === (int)$headDepartmentId) ? 1 : 0;
            $stmt = $db->prepare("INSERT INTO officer_departments (officer_id, department_id, is_head) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $officerId, $deptId, $isHead);
            $stmt->execute();
            $stmt->close();
        }
        return true;
    }
}
