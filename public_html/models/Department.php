<?php
namespace App\Models;

use App\Config\Database;
use Exception;

class Department {
    /**
     * Create a new department.
     * @param string $code
     * @param string $nameTh
     * @param string|null $nameEn
     * @param string|null $description
     * @param int $sortOrder
     * @return int Inserted ID
     * @throws Exception
     */
    public static function create($code, $nameTh, $nameEn = null, $description = null, $sortOrder = 0) {
        $db = Database::getConnection();
        $stmt = $db->prepare("INSERT INTO departments (code, name_th, name_en, description, sort_order) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $db->error);
        }
        $stmt->bind_param("ssssi", $code, $nameTh, $nameEn, $description, $sortOrder);
        if (!$stmt->execute()) {
            throw new Exception("Execute statement failed: " . $stmt->error);
        }
        $insertId = $stmt->insert_id;
        $stmt->close();
        return $insertId;
    }

    /**
     * Find department by ID.
     * @param int $id
     * @return array|null
     * @throws Exception
     */
    public static function findById($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM departments WHERE id = ? LIMIT 1");
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
     * Get all departments with optional active filter.
     * @param bool $activeOnly
     * @return array
     * @throws Exception
     */
    public static function getAll($activeOnly = false) {
        $db = Database::getConnection();
        $sql = "SELECT d.*, 
                (SELECT COUNT(*) FROM officer_departments od WHERE od.department_id = d.id) AS officer_count,
                (SELECT COUNT(*) FROM request_types rt WHERE rt.department_id = d.id) AS service_count
                FROM departments d";
        if ($activeOnly) {
            $sql .= " WHERE d.active = 1";
        }
        $sql .= " ORDER BY d.sort_order ASC, d.name_th ASC";

        $result = $db->query($sql);
        if (!$result) {
            throw new Exception("Query failed: " . $db->error);
        }
        $departments = [];
        while ($row = $result->fetch_assoc()) {
            $departments[] = $row;
        }
        return $departments;
    }

    /**
     * Update department.
     * @param int $id
     * @param string $code
     * @param string $nameTh
     * @param string|null $nameEn
     * @param string|null $description
     * @param int $sortOrder
     * @return bool
     * @throws Exception
     */
    public static function update($id, $code, $nameTh, $nameEn = null, $description = null, $sortOrder = 0) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE departments SET code = ?, name_th = ?, name_en = ?, description = ?, sort_order = ? WHERE id = ?");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $db->error);
        }
        $stmt->bind_param("ssssii", $code, $nameTh, $nameEn, $description, $sortOrder, $id);
        $res = $stmt->execute();
        if (!$res) {
            throw new Exception("Execute statement failed: " . $stmt->error);
        }
        $stmt->close();
        return $res;
    }

    /**
     * Toggle department active/inactive.
     * @param int $id
     * @return bool
     * @throws Exception
     */
    public static function toggleActive($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE departments SET active = NOT active WHERE id = ?");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $db->error);
        }
        $stmt->bind_param("i", $id);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }

    /**
     * Get officers assigned to a department.
     * @param int $departmentId
     * @return array
     * @throws Exception
     */
    public static function getOfficers($departmentId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT o.*, od.is_head, od.assigned_at
            FROM officers o
            JOIN officer_departments od ON o.id = od.officer_id
            WHERE od.department_id = ?
            ORDER BY od.is_head DESC, o.name ASC
        ");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $db->error);
        }
        $stmt->bind_param("i", $departmentId);
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
}
