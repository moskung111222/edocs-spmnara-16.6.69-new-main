<?php
namespace App\Models;

use App\Config\Database;
use Exception;

class Applicant {
    /**
     * Create a new applicant record.
     * @param string $fullName
     * @param string $email
     * @param string $phone
     * @param int $isRegistered
     * @param string|null $passwordHash
     * @return int Inserted ID
     * @throws Exception
     */
    public static function create($fullName, $email, $phone, $isRegistered = 0, $passwordHash = null) {
        $db = Database::getConnection();
        $stmt = $db->prepare("INSERT INTO applicants (full_name, email, phone, is_registered, password_hash) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $db->error);
        }
        $stmt->bind_param("sssis", $fullName, $email, $phone, $isRegistered, $passwordHash);
        if (!$stmt->execute()) {
            throw new Exception("Execute statement failed: " . $stmt->error);
        }
        $insertId = $stmt->insert_id;
        $stmt->close();
        return $insertId;
    }

    /**
     * Find applicant by email address.
     * @param string $email
     * @return array|null
     * @throws Exception
     */
    public static function findByEmail($email) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM applicants WHERE email = ? LIMIT 1");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $db->error);
        }
        $stmt->bind_param("s", $email);
        if (!$stmt->execute()) {
            throw new Exception("Execute statement failed: " . $stmt->error);
        }
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    /**
     * Find applicant by ID.
     * @param int $id
     * @return array|null
     * @throws Exception
     */
    public static function findById($id) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM applicants WHERE id = ? LIMIT 1");
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
