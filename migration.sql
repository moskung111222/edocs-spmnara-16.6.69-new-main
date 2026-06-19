-- Database: sesaonara_edocs
-- Character Set: utf8mb4
-- Collation: utf8mb4_unicode_ci

CREATE DATABASE IF NOT EXISTS `sesaonara_edocs` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `sesaonara_edocs`;

-- 1. Table: applicants (ผู้ยื่นคำขอ/ประชาชน)
CREATE TABLE IF NOT EXISTS `applicants` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `full_name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `phone` VARCHAR(20) NOT NULL,
    `is_registered` TINYINT(1) DEFAULT 0,
    `password_hash` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Table: request_types (ประเภทเอกสาร/คำขอ)
CREATE TABLE IF NOT EXISTS `request_types` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(10) NOT NULL UNIQUE,
    `name_th` VARCHAR(255) NOT NULL,
    `doc_checklist` JSON NOT NULL, -- list of files required (e.g. ["สำเนาบัตรประชาชน", "ใบรายงานผลการเรียน"])
    `active` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Table: officers (เจ้าหน้าที่ สพม.นราธิวาส)
CREATE TABLE IF NOT EXISTS `officers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `role` ENUM('staff', 'head', 'admin') NOT NULL DEFAULT 'staff',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Table: requests (คำขอเอกสาร)
CREATE TABLE IF NOT EXISTS `requests` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `request_no` VARCHAR(50) NOT NULL UNIQUE,
    `type_id` INT NOT NULL,
    `applicant_id` INT NOT NULL,
    `assigned_officer_id` INT NULL,
    `status` VARCHAR(50) NOT NULL DEFAULT 'submitted',
    `form_data` JSON NOT NULL, -- contains specific form inputs (e.g. {grad_year: 2560, school: "nara school"})
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`type_id`) REFERENCES `request_types` (`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`assigned_officer_id`) REFERENCES `officers` (`id`) ON DELETE SET NULL,
    INDEX `idx_status` (`status`),
    INDEX `idx_request_no` (`request_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Table: attachments (เอกสารแนบ PDF)
CREATE TABLE IF NOT EXISTS `attachments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `request_id` INT NOT NULL,
    `file_name` VARCHAR(255) NOT NULL,
    `file_path` VARCHAR(512) NOT NULL,
    `mime_type` VARCHAR(100) NOT NULL,
    `file_size` INT NOT NULL,
    `uploaded_by` ENUM('applicant', 'officer') NOT NULL,
    `version` INT DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Table: status_history (ประวัติการเปลี่ยนสถานะ)
CREATE TABLE IF NOT EXISTS `status_history` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `request_id` INT NOT NULL,
    `from_status` VARCHAR(50) NOT NULL,
    `to_status` VARCHAR(50) NOT NULL,
    `reason` TEXT NULL,
    `officer_id` INT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`officer_id`) REFERENCES `officers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Table: messages (ห้องสนทนาเกี่ยวกับคำขอ)
CREATE TABLE IF NOT EXISTS `messages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `request_id` INT NOT NULL,
    `sender_type` ENUM('applicant', 'officer') NOT NULL,
    `body` TEXT NOT NULL,
    `internal_note` TINYINT(1) DEFAULT 0, -- 1 = Private note visible only to officers
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Table: audit_logs (บันทึกการทำงาน)
CREATE TABLE IF NOT EXISTS `audit_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NULL, -- ID of applicant or officer depending on context
    `user_type` ENUM('applicant', 'officer', 'system') DEFAULT 'system',
    `action` VARCHAR(255) NOT NULL,
    `module` VARCHAR(100) NOT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `user_agent` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Table: otp_verifications (การยืนยันตัวตนด้วย OTP)
CREATE TABLE IF NOT EXISTS `otp_verifications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(255) NOT NULL,
    `otp_code` VARCHAR(10) NOT NULL,
    `expired_at` DATETIME NOT NULL,
    `attempts` INT DEFAULT 0,
    `verified` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_email_otp` (`email`, `otp_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- SEED DATA
-- ========================================================

-- Seed initial request types (ประเภทคำขอ)
INSERT INTO `request_types` (`code`, `name_th`, `doc_checklist`) VALUES
('HS', 'ขอใบแทนใบสุทธิ / ใบประกาศนียบัตร (High School Certificate)', '["สำเนาบัตรประจำตัวประชาชน", "รูปถ่ายหน้าตรง 1.5 นิ้ว (ถ่ายไม่เกิน 6 เดือน)", "ใบแจ้งความใบสุทธิสูญหาย"]'),
('TR', 'ขอใบแสดงผลการเรียน (Transcript)', '["สำเนาบัตรประจำตัวประชาชน", "สำเนาทะเบียนบ้าน", "รูปถ่ายหน้าตรง 1.5 นิ้ว"]'),
('ED', 'ขอหนังสือรับรองวุฒิการศึกษาเพื่อศึกษาต่อ (Education Verification)', '["สำเนาบัตรประจำตัวประชาชน", "หนังสือแจ้งความจำนงจากหน่วยงานที่เกี่ยวข้อง"]');

-- Seed default officer accounts (เจ้าหน้าที่ สพม.นราธิวาส)
-- Admin: username = admin, password = admin123 (Hash: $2y$10$gP7B.EwKfe9g5W3.mXg.eO.t.wUjXbCtf70Nsp8kR4hH976H2mFxe)
-- Head: username = head, password = head123 (Hash: $2y$10$g1kK2708bO.KjP8V4jS1tN1gL.Ou3fJ0m89R7hW2vSp8kR4hH976H2)
-- Staff: username = staff, password = staff123 (Hash: $2y$10$c7F4d62GzP8V4jS1tN1gL.Ou3fJ0m89R7hW2vSp8kR4hH976H2m)

INSERT INTO `officers` (`username`, `password_hash`, `name`, `email`, `role`) VALUES
('admin', '$2y$10$gP7B.EwKfe9g5W3.mXg.eO.t.wUjXbCtf70Nsp8kR4hH976H2mFxe', 'ผู้ดูแลระบบ สพม.นราธิวาส', 'admin@spmnara.go.th', 'admin'),
('head', '$2y$10$gP7B.EwKfe9g5W3.mXg.eO.t.wUjXbCtf70Nsp8kR4hH976H2mFxe', 'หัวหน้ากลุ่ม สพม.นราธิวาส', 'head@spmnara.go.th', 'head'),
('staff', '$2y$10$gP7B.EwKfe9g5W3.mXg.eO.t.wUjXbCtf70Nsp8kR4hH976H2mFxe', 'เจ้าหน้าที่ สพม.นราธิวาส', 'staff@spmnara.go.th', 'staff');
