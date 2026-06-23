<?php
namespace App\Models;

use App\Config\Database;
use Exception;

class ApplicantAccount {
    public static function create($applicantId, $applicantCode, $passwordPlain) {
        $db = Database::getConnection();
        $passwordHash = password_hash($passwordPlain, PASSWORD_BCRYPT);
        $stmt = $db->prepare("INSERT INTO applicant_accounts (applicant_id, applicant_code, password_hash, password_plain) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $db->error);
        }
        $stmt->bind_param("isss", $applicantId, $applicantCode, $passwordHash, $passwordPlain);
        if (!$stmt->execute()) {
            throw new Exception("Execute statement failed: " . $stmt->error);
        }
        $insertId = $stmt->insert_id;
        $stmt->close();
        return $insertId;
    }

    public static function findByApplicantId($applicantId) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM applicant_accounts WHERE applicant_id = ? LIMIT 1");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $db->error);
        }
        $stmt->bind_param("i", $applicantId);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $res;
    }

    public static function findByCode($code) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM applicant_accounts WHERE applicant_code = ? LIMIT 1");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $db->error);
        }
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $res;
    }

    public static function verifyPassword($applicantId, $password) {
        $account = self::findByApplicantId($applicantId);
        if (!$account) {
            return false;
        }
        return password_verify($password, $account['password_hash']);
    }

    public static function getLatestCodeNumberForYear($year) {
        $db = Database::getConnection();
        $pattern = "HSU-" . $year . "-%";
        $stmt = $db->prepare("SELECT applicant_code FROM applicant_accounts WHERE applicant_code LIKE ? ORDER BY applicant_code DESC LIMIT 1");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $db->error);
        }
        $stmt->bind_param("s", $pattern);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($res) {
            $parts = explode('-', $res['applicant_code']);
            if (isset($parts[2])) {
                return (int)$parts[2];
            }
        }
        return 0;
    }

    public static function updatePassword($applicantId, $passwordPlain) {
        $db = Database::getConnection();
        $passwordHash = password_hash($passwordPlain, PASSWORD_BCRYPT);
        $stmt = $db->prepare("UPDATE applicant_accounts SET password_hash = ?, password_plain = ? WHERE applicant_id = ?");
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $db->error);
        }
        $stmt->bind_param("ssi", $passwordHash, $passwordPlain, $applicantId);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }
}
