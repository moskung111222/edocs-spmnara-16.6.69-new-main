-- ============================================================
-- NWT Document Submission System - edocs-spmnara
-- ระบบยื่นคำขอเอกสารออนไลน์ สพม.นราธิวาส
-- 
-- FULL INSTALL SCRIPT v2.0 (Combined — ไฟล์เดียว พร้อมใช้งาน)
-- 
-- วิธีใช้งาน:
--   1. สร้างฐานข้อมูลใหม่ก่อน หรือปล่อยให้สคริปต์นี้สร้างเอง
--   2. รันไฟล์นี้ผ่าน phpMyAdmin หรือ mysql CLI
--   3. ระบบพร้อมใช้งานทันที ไม่ต้องรันไฟล์อื่นเพิ่ม
--
-- Character Set: utf8mb4
-- Collation    : utf8mb4_unicode_ci
-- Engine       : InnoDB
-- Tested on    : MySQL 8.0+, MariaDB 10.4+
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- สร้างและเลือกใช้งานฐานข้อมูล
-- ============================================================
CREATE DATABASE IF NOT EXISTS `edocs_spmnara`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `edocs_spmnara`;

-- ============================================================
-- ตาราง 1: applicants (ผู้ยื่นคำขอ / ประชาชน)
-- ============================================================
CREATE TABLE IF NOT EXISTS `applicants` (
    `id`            INT AUTO_INCREMENT PRIMARY KEY,
    `full_name`     VARCHAR(255) NOT NULL,
    `email`         VARCHAR(255) NOT NULL UNIQUE,
    `phone`         VARCHAR(20)  NOT NULL,
    `is_registered` TINYINT(1)   DEFAULT 0,
    `password_hash` VARCHAR(255) NULL,
    `created_at`    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- ตาราง 2: departments (กลุ่มงาน / แผนก)
-- ============================================================
CREATE TABLE IF NOT EXISTS `departments` (
    `id`          INT AUTO_INCREMENT PRIMARY KEY,
    `code`        VARCHAR(20)  NOT NULL UNIQUE,
    `name_th`     VARCHAR(255) NOT NULL,
    `name_en`     VARCHAR(255) NULL,
    `description` TEXT         NULL,
    `active`      TINYINT(1)   DEFAULT 1,
    `sort_order`  INT          DEFAULT 0,
    `created_at`  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- ตาราง 3: roles (RBAC roles)
-- ============================================================
CREATE TABLE IF NOT EXISTS `roles` (
    `id`          INT AUTO_INCREMENT PRIMARY KEY,
    `code`        VARCHAR(50)  NOT NULL UNIQUE,
    `name_th`     VARCHAR(255) NOT NULL,
    `description` TEXT         NULL,
    `is_system`   TINYINT(1)   DEFAULT 0,
    `active`      TINYINT(1)   DEFAULT 1,
    `created_at`  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- ตาราง 4: permissions (สิทธิ์การใช้งาน)
-- ============================================================
CREATE TABLE IF NOT EXISTS `permissions` (
    `id`          INT AUTO_INCREMENT PRIMARY KEY,
    `code`        VARCHAR(100) NOT NULL UNIQUE,
    `name_th`     VARCHAR(255) NOT NULL,
    `module`      VARCHAR(50)  NOT NULL,
    `description` TEXT         NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- ตาราง 5: role_permissions (ผูก role <-> permission)
-- ============================================================
CREATE TABLE IF NOT EXISTS `role_permissions` (
    `role_id`       INT NOT NULL,
    `permission_id` INT NOT NULL,
    PRIMARY KEY (`role_id`, `permission_id`),
    FOREIGN KEY (`role_id`)       REFERENCES `roles`       (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- ตาราง 6: officers (เจ้าหน้าที่ สพม.นราธิวาส)
-- ============================================================
CREATE TABLE IF NOT EXISTS `officers` (
    `id`            INT AUTO_INCREMENT PRIMARY KEY,
    `username`      VARCHAR(50)  NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `name`          VARCHAR(255) NOT NULL,
    `email`         VARCHAR(255) NOT NULL UNIQUE,
    `role`          VARCHAR(50)  NOT NULL DEFAULT 'staff',
    `department_id` INT          NULL,
    `active`        TINYINT(1)   DEFAULT 1,
    `created_at`    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- ตาราง 7: officer_departments (ผูก officer <-> department M:N)
-- ============================================================
CREATE TABLE IF NOT EXISTS `officer_departments` (
    `officer_id`    INT NOT NULL,
    `department_id` INT NOT NULL,
    `is_head`       TINYINT(1) DEFAULT 0,
    `assigned_at`   TIMESTAMP  DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`officer_id`, `department_id`),
    FOREIGN KEY (`officer_id`)    REFERENCES `officers`    (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- ตาราง 8: request_types (ประเภทเอกสาร / คำขอ)
-- ============================================================
CREATE TABLE IF NOT EXISTS `request_types` (
    `id`            INT AUTO_INCREMENT PRIMARY KEY,
    `code`          VARCHAR(10)  NOT NULL UNIQUE,
    `name_th`       VARCHAR(255) NOT NULL,
    `description`   TEXT         NULL,
    `doc_checklist` JSON         NOT NULL,
    `department_id` INT          NULL,
    `active`        TINYINT(1)   DEFAULT 1,
    `sort_order`    INT          DEFAULT 0,
    FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- ตาราง 9: requests (คำขอเอกสาร)
-- ============================================================
CREATE TABLE IF NOT EXISTS `requests` (
    `id`                  INT AUTO_INCREMENT PRIMARY KEY,
    `request_no`          VARCHAR(50)  NOT NULL UNIQUE,
    `type_id`             INT          NOT NULL,
    `applicant_id`        INT          NOT NULL,
    `assigned_officer_id` INT          NULL,
    `status`              VARCHAR(50)  NOT NULL DEFAULT 'submitted',
    `form_data`           JSON         NOT NULL,
    `created_at`          TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    `updated_at`          TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`type_id`)             REFERENCES `request_types` (`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`applicant_id`)        REFERENCES `applicants`    (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`assigned_officer_id`) REFERENCES `officers`      (`id`) ON DELETE SET NULL,
    INDEX `idx_status`     (`status`),
    INDEX `idx_request_no` (`request_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- ตาราง 10: attachments (เอกสารแนบ PDF)
-- ============================================================
CREATE TABLE IF NOT EXISTS `attachments` (
    `id`          INT AUTO_INCREMENT PRIMARY KEY,
    `request_id`  INT          NOT NULL,
    `file_name`   VARCHAR(255) NOT NULL,
    `file_path`   VARCHAR(512) NOT NULL,
    `mime_type`   VARCHAR(100) NOT NULL,
    `file_size`   INT          NOT NULL,
    `uploaded_by` ENUM('applicant','officer') NOT NULL,
    `version`     INT          DEFAULT 1,
    `created_at`  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- ตาราง 11: status_history (ประวัติการเปลี่ยนสถานะ)
-- ============================================================
CREATE TABLE IF NOT EXISTS `status_history` (
    `id`          INT AUTO_INCREMENT PRIMARY KEY,
    `request_id`  INT         NOT NULL,
    `from_status` VARCHAR(50) NOT NULL,
    `to_status`   VARCHAR(50) NOT NULL,
    `reason`      TEXT        NULL,
    `officer_id`  INT         NULL,
    `created_at`  TIMESTAMP   DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`officer_id`) REFERENCES `officers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- ตาราง 12: messages (ห้องสนทนาเกี่ยวกับคำขอ)
-- ============================================================
CREATE TABLE IF NOT EXISTS `messages` (
    `id`            INT AUTO_INCREMENT PRIMARY KEY,
    `request_id`    INT        NOT NULL,
    `sender_type`   ENUM('applicant','officer') NOT NULL,
    `body`          TEXT       NOT NULL,
    `internal_note` TINYINT(1) DEFAULT 0,
    `created_at`    TIMESTAMP  DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- ตาราง 13: audit_logs (บันทึกการทำงาน)
-- ============================================================
CREATE TABLE IF NOT EXISTS `audit_logs` (
    `id`         INT AUTO_INCREMENT PRIMARY KEY,
    `user_id`    INT          NULL,
    `user_type`  ENUM('applicant','officer','system') DEFAULT 'system',
    `action`     VARCHAR(255) NOT NULL,
    `module`     VARCHAR(100) NOT NULL,
    `ip_address` VARCHAR(45)  NOT NULL,
    `user_agent` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- ตาราง 14: otp_verifications (การยืนยันตัวตนด้วย OTP)
-- ============================================================
CREATE TABLE IF NOT EXISTS `otp_verifications` (
    `id`         INT AUTO_INCREMENT PRIMARY KEY,
    `email`      VARCHAR(255) NOT NULL,
    `otp_code`   VARCHAR(10)  NOT NULL,
    `expired_at` DATETIME     NOT NULL,
    `attempts`   INT          DEFAULT 0,
    `verified`   TINYINT(1)   DEFAULT 0,
    `created_at` TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_email_otp` (`email`, `otp_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Add Foreign Keys on officers after all tables exist
-- ============================================================
ALTER TABLE `officers`
    ADD CONSTRAINT `fk_officers_department`
    FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL;

ALTER TABLE `request_types`
    ADD CONSTRAINT `fk_request_types_department`
    FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL;

-- ============================================================
-- SEED DATA 1: กลุ่มงาน สพม.นราธิวาส (Departments)
-- ============================================================
INSERT IGNORE INTO `departments` (`code`, `name_th`, `name_en`, `sort_order`) VALUES
('ADMIN',   'กลุ่มอำนวยการ',                       'General Administration',       1),
('FINANCE', 'กลุ่มบริหารงานการเงินและสินทรัพย์',   'Finance and Assets Management', 2),
('SUPER',   'กลุ่มนิเทศ ติดตาม และประเมินผลฯ',   'Supervision and Evaluation',    3),
('HR',      'กลุ่มบริหารงานบุคคล',                  'Human Resources',               4),
('PROMOTE', 'กลุ่มส่งเสริมการจัดการศึกษา',         'Education Promotion',           5),
('PLAN',    'กลุ่มนโยบายและแผน',                    'Policy and Planning',           6),
('AUDIT',   'หน่วยตรวจสอบภายใน',                    'Internal Audit',                7),
('TEACHER', 'กลุ่มพัฒนาครูและบุคลากรฯ',            'Teacher Development',           8),
('LAW',     'กลุ่มกฎหมายและคดี',                    'Legal Affairs',                 9),
('ICT',     'กลุ่มส่งเสริมการศึกษาทางไกลฯ',        'Distance Education and ICT',   10);

-- ============================================================
-- SEED DATA 2: Roles (RBAC)
-- ============================================================
INSERT IGNORE INTO `roles` (`code`, `name_th`, `description`, `is_system`) VALUES
('admin', 'ผู้ดูแลระบบ',    'สิทธิ์สูงสุด สามารถจัดการทุกส่วนของระบบได้', 1),
('head',  'หัวหน้ากลุ่มงาน','สามารถอนุมัติคำขอ มอบหมายงาน และดูรายงาน',   1),
('staff', 'เจ้าหน้าที่',     'สามารถดำเนินการคำขอตามที่ได้รับมอบหมาย',      1);

-- ============================================================
-- SEED DATA 3: Permissions
-- ============================================================
INSERT IGNORE INTO `permissions` (`code`, `name_th`, `module`) VALUES
('dashboard.view',         'ดู Dashboard',               'dashboard'),
('dashboard.kpi',          'ดู KPI ผู้บริหาร',           'dashboard'),
('requests.view',          'ดูรายการคำขอ',               'requests'),
('requests.view_all',      'ดูคำขอทุกกลุ่มงาน',         'requests'),
('requests.assign',        'มอบหมายคำขอ',               'requests'),
('requests.change_status', 'เปลี่ยนสถานะคำขอ',          'requests'),
('requests.approve',       'อนุมัติคำขอ',                'requests'),
('requests.reject',        'ปฏิเสธคำขอ',                'requests'),
('requests.message',       'ส่งข้อความ',                 'requests'),
('requests.internal_note', 'เขียนบันทึกภายใน',          'requests'),
('officers.view',          'ดูรายชื่อเจ้าหน้าที่',       'officers'),
('officers.create',        'เพิ่มเจ้าหน้าที่',           'officers'),
('officers.edit',          'แก้ไขเจ้าหน้าที่',           'officers'),
('officers.delete',        'ลบเจ้าหน้าที่',              'officers'),
('departments.view',       'ดูกลุ่มงาน',                 'departments'),
('departments.create',     'เพิ่มกลุ่มงาน',              'departments'),
('departments.edit',       'แก้ไขกลุ่มงาน',              'departments'),
('departments.delete',     'ลบกลุ่มงาน',                 'departments'),
('services.view',          'ดูประเภทบริการ',              'services'),
('services.create',        'เพิ่มประเภทบริการ',           'services'),
('services.edit',          'แก้ไขประเภทบริการ',           'services'),
('services.delete',        'ลบประเภทบริการ',              'services'),
('roles.view',             'ดู Roles',                   'roles'),
('roles.manage',           'จัดการ Roles & Permissions', 'roles'),
('audit.view',             'ดู Audit Log',               'audit');

-- ============================================================
-- SEED DATA 4: กำหนด Permissions ให้แต่ละ Role
-- ============================================================
-- Admin ได้ทุก permission
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r, `permissions` p WHERE r.code = 'admin';

-- Head ได้ permission ที่เลือก
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r, `permissions` p
WHERE r.code = 'head' AND p.code IN (
    'dashboard.view','dashboard.kpi',
    'requests.view','requests.view_all','requests.assign',
    'requests.change_status','requests.approve','requests.reject',
    'requests.message','requests.internal_note',
    'officers.view','departments.view','services.view'
);

-- Staff ได้ permission จำกัด
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r, `permissions` p
WHERE r.code = 'staff' AND p.code IN (
    'dashboard.view',
    'requests.view','requests.change_status',
    'requests.message','requests.internal_note',
    'departments.view','services.view'
);

-- ============================================================
-- SEED DATA 5: ประเภทคำขอ (Request Types)
-- ============================================================
INSERT IGNORE INTO `request_types` (`code`, `name_th`, `doc_checklist`, `active`, `sort_order`) VALUES
('HS', 'ขอใบแทนใบสุทธิ / ใบประกาศนียบัตร (High School Certificate)',
    '["สำเนาบัตรประจำตัวประชาชน","รูปถ่ายหน้าตรง 1.5 นิ้ว (ถ่ายไม่เกิน 6 เดือน)","ใบแจ้งความใบสุทธิสูญหาย"]',
    1, 1),
('TR', 'ขอใบแสดงผลการเรียน (Transcript)',
    '["สำเนาบัตรประจำตัวประชาชน","สำเนาทะเบียนบ้าน","รูปถ่ายหน้าตรง 1.5 นิ้ว"]',
    1, 2),
('ED', 'ขอหนังสือรับรองวุฒิการศึกษาเพื่อศึกษาต่อ (Education Verification)',
    '["สำเนาบัตรประจำตัวประชาชน","หนังสือแจ้งความจำนงจากหน่วยงานที่เกี่ยวข้อง"]',
    1, 3);

-- ============================================================
-- SEED DATA 6: บัญชีเจ้าหน้าที่เริ่มต้น (Default Officer Accounts)
-- ============================================================
-- รหัสผ่านทั้งหมดเข้ารหัสด้วย bcrypt (password_hash)
-- admin  -> รหัสผ่าน: admin123
-- head   -> รหัสผ่าน: admin123
-- staff  -> รหัสผ่าน: admin123
--
-- ⚠️ กรุณาเปลี่ยนรหัสผ่านทันทีหลังติดตั้งเสร็จ!
INSERT IGNORE INTO `officers` (`username`, `password_hash`, `name`, `email`, `role`) VALUES
('admin', '$2y$10$gP7B.EwKfe9g5W3.mXg.eO.t.wUjXbCtf70Nsp8kR4hH976H2mFxe', 'ผู้ดูแลระบบ สพม.นราธิวาส',     'admin@spmnara.go.th', 'admin'),
('head',  '$2y$10$gP7B.EwKfe9g5W3.mXg.eO.t.wUjXbCtf70Nsp8kR4hH976H2mFxe', 'หัวหน้ากลุ่ม สพม.นราธิวาส',   'head@spmnara.go.th',  'head'),
('staff', '$2y$10$gP7B.EwKfe9g5W3.mXg.eO.t.wUjXbCtf70Nsp8kR4hH976H2mFxe', 'เจ้าหน้าที่ สพม.นราธิวาส',    'staff@spmnara.go.th', 'staff');

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- เสร็จสิ้น! ระบบพร้อมใช้งาน
-- ตาราง 14 ตาราง | กลุ่มงาน 10 | Roles 3 | Permissions 25
-- ============================================================
