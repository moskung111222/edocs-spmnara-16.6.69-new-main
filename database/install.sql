-- ============================================================
-- NWT Document Submission System - edocs-spmnara
-- ระบบยื่นคำขอเอกสารออนไลน์ สพม.นราธิวาส
-- 
-- FULL INSTALL SCRIPT v2.0 (Consolidated & Production-Ready)
-- 
-- Usage:
--   1. Run this script in MySQL/MariaDB client or phpMyAdmin.
--   2. The script will create the `edocs_spmnara` database and all 14 tables.
--   3. Default seed data and testing accounts will be populated.
--
-- Character Set: utf8mb4
-- Collation    : utf8mb4_unicode_ci
-- Engine       : InnoDB
-- Compatibility: MySQL 8.0+, MariaDB 10.3+
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. DATABASE CREATION
-- ============================================================
CREATE DATABASE IF NOT EXISTS `sesaonara_edocs`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `sesaonara_edocs`;

-- ============================================================
-- 2. TABLE CREATION
-- ============================================================

-- Table 1: applicants (ข้อมูลผู้ยื่นคำขอ / ประชาชน)
CREATE TABLE IF NOT EXISTS `applicants` (
    `id`            INT AUTO_INCREMENT PRIMARY KEY,
    `full_name`     VARCHAR(255) NOT NULL,
    `email`         VARCHAR(255) NOT NULL UNIQUE,
    `phone`         VARCHAR(20)  NOT NULL,
    `is_registered` TINYINT(1)   DEFAULT 0,
    `password_hash` VARCHAR(255) NULL,
    `created_at`    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 2: departments (กลุ่มงาน / แผนก ใน สพม.นราธิวาส)
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

-- Table 3: roles (สิทธิ์การทำงาน RBAC Roles)
CREATE TABLE IF NOT EXISTS `roles` (
    `id`          INT AUTO_INCREMENT PRIMARY KEY,
    `code`        VARCHAR(50)  NOT NULL UNIQUE,
    `name_th`     VARCHAR(255) NOT NULL,
    `description` TEXT         NULL,
    `is_system`   TINYINT(1)   DEFAULT 0,
    `active`      TINYINT(1)   DEFAULT 1,
    `created_at`  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 4: permissions (รายการสิทธิ์เข้าถึงระบบย่อย)
CREATE TABLE IF NOT EXISTS `permissions` (
    `id`          INT AUTO_INCREMENT PRIMARY KEY,
    `code`        VARCHAR(100) NOT NULL UNIQUE,
    `name_th`     VARCHAR(255) NOT NULL,
    `module`      VARCHAR(50)  NOT NULL,
    `description` TEXT         NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 5: role_permissions (ความสัมพันธ์แบบ M:N ของ Roles และ Permissions)
CREATE TABLE IF NOT EXISTS `role_permissions` (
    `role_id`       INT NOT NULL,
    `permission_id` INT NOT NULL,
    PRIMARY KEY (`role_id`, `permission_id`),
    CONSTRAINT `fk_role_permissions_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_role_permissions_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 6: officers (เจ้าหน้าที่และผู้ใช้งานระบบหลังบ้าน)
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
    CONSTRAINT `fk_officers_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 7: officer_departments (ความสัมพันธ์แบบ M:N ของ เจ้าหน้าที่ และ กลุ่มงาน เพื่อรองรับหลายสังกัด)
CREATE TABLE IF NOT EXISTS `officer_departments` (
    `officer_id`    INT NOT NULL,
    `department_id` INT NOT NULL,
    `is_head`       TINYINT(1) DEFAULT 0,
    `assigned_at`   TIMESTAMP  DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`officer_id`, `department_id`),
    CONSTRAINT `fk_officer_departments_officer` FOREIGN KEY (`officer_id`) REFERENCES `officers` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_officer_departments_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 8: request_types (ประเภทคำขอและบริการเอกสาร)
CREATE TABLE IF NOT EXISTS `request_types` (
    `id`            INT AUTO_INCREMENT PRIMARY KEY,
    `code`          VARCHAR(10)  NOT NULL UNIQUE,
    `name_th`       VARCHAR(255) NOT NULL,
    `description`   TEXT         NULL,
    `doc_checklist` JSON         NOT NULL,
    `department_id` INT          NULL,
    `active`        TINYINT(1)   DEFAULT 1,
    `sort_order`    INT          DEFAULT 0,
    CONSTRAINT `fk_request_types_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 9: requests (รายการคำขอรับบริการ / เอกสาร)
CREATE TABLE IF NOT EXISTS `requests` (
    `id`                  INT AUTO_INCREMENT PRIMARY KEY,
    `request_no`          VARCHAR(50)  NOT NULL UNIQUE,
    `type_id`             INT          NOT NULL,
    `applicant_id`        INT          NOT NULL,
    `assigned_officer_id` INT          NULL,
    `status`              VARCHAR(50)  NOT NULL DEFAULT 'submitted',
    `process_1_status`    VARCHAR(50)  NOT NULL DEFAULT 'submitted',
    `process_2_status`    VARCHAR(50)  NOT NULL DEFAULT 'not_started',
    `form_data`           JSON         NOT NULL,
    `created_at`          TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    `updated_at`          TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_requests_type` FOREIGN KEY (`type_id`) REFERENCES `request_types` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_requests_applicant` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_requests_officer` FOREIGN KEY (`assigned_officer_id`) REFERENCES `officers` (`id`) ON DELETE SET NULL,
    INDEX `idx_status`     (`status`),
    INDEX `idx_request_no` (`request_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 10: attachments (ไฟล์แนบและหลักฐานประกอบคำขอ)
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
    CONSTRAINT `fk_attachments_request` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 11: status_history (ประวัติการปรับปรุงสถานะและขั้นตอนงาน)
CREATE TABLE IF NOT EXISTS `status_history` (
    `id`          INT AUTO_INCREMENT PRIMARY KEY,
    `request_id`  INT         NOT NULL,
    `from_status` VARCHAR(50) NOT NULL,
    `to_status`   VARCHAR(50) NOT NULL,
    `reason`      TEXT        NULL,
    `officer_id`  INT         NULL,
    `created_at`  TIMESTAMP   DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_status_history_request` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_status_history_officer` FOREIGN KEY (`officer_id`) REFERENCES `officers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 12: messages (ห้องแชทสื่อสารและบันทึกข้อความภายใน)
CREATE TABLE IF NOT EXISTS `messages` (
    `id`            INT AUTO_INCREMENT PRIMARY KEY,
    `request_id`    INT        NOT NULL,
    `sender_type`   ENUM('applicant','officer') NOT NULL,
    `body`          TEXT       NOT NULL,
    `internal_note` TINYINT(1) DEFAULT 0,
    `created_at`    TIMESTAMP  DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_messages_request` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 13: audit_logs (บันทึกกิจกรรมและเหตุการณ์สำคัญของระบบ)
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

-- Table 14: otp_verifications (การบันทึกรหัสผ่านครั้งเดียว OTP เพื่อเข้าใช้งานระบบยื่นคำขอ)
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

-- Table 15: announcements (ข่าวประชาสัมพันธ์และประกาศ)
CREATE TABLE IF NOT EXISTS `announcements` (
    `id`         INT AUTO_INCREMENT PRIMARY KEY,
    `title`      VARCHAR(255) NOT NULL,
    `content`    TEXT NOT NULL,
    `type`       VARCHAR(50) NOT NULL, -- 'announcement', 'public_notice', 'update'
    `author_id`  INT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_announcements_author` FOREIGN KEY (`author_id`) REFERENCES `officers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 16: laws (กฎหมายและระเบียบที่เกี่ยวข้อง)
CREATE TABLE IF NOT EXISTS `laws` (
    `id`          INT AUTO_INCREMENT PRIMARY KEY,
    `title`       VARCHAR(255) NOT NULL,
    `category`    VARCHAR(100) NOT NULL, -- 'PDF laws', 'ministerial regulations', 'official guidelines', 'related regulations'
    `file_name`   VARCHAR(255) NOT NULL,
    `file_path`   VARCHAR(512) NOT NULL,
    `file_size`   INT NOT NULL,
    `uploaded_by` INT NULL,
    `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_laws_uploader` FOREIGN KEY (`uploaded_by`) REFERENCES `officers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 17: download_documents (ศูนย์ดาวน์โหลดแบบฟอร์ม)
CREATE TABLE IF NOT EXISTS `download_documents` (
    `id`          INT AUTO_INCREMENT PRIMARY KEY,
    `title`       VARCHAR(255) NOT NULL,
    `category`    VARCHAR(100) NOT NULL, -- 'Homeschool Application', 'Education Plan Templates', 'Learning Report Templates', 'Transfer Requests', 'Graduation Requests', 'Other Documents'
    `file_name`   VARCHAR(255) NOT NULL,
    `file_path`   VARCHAR(512) NOT NULL,
    `file_size`   INT NOT NULL,
    `uploaded_by` INT NULL,
    `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_download_documents_uploader` FOREIGN KEY (`uploaded_by`) REFERENCES `officers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 18: infographics (อินโฟกราฟิกและแบนเนอร์)
CREATE TABLE IF NOT EXISTS `infographics` (
    `id`          INT AUTO_INCREMENT PRIMARY KEY,
    `title`       VARCHAR(255) NOT NULL,
    `image_name`  VARCHAR(255) NOT NULL,
    `image_path`  VARCHAR(512) NOT NULL,
    `uploaded_by` INT NULL,
    `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_infographics_uploader` FOREIGN KEY (`uploaded_by`) REFERENCES `officers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 19: applicant_accounts (บัญชีผู้ยื่นคำขอ)
CREATE TABLE IF NOT EXISTS `applicant_accounts` (
    `id`             INT AUTO_INCREMENT PRIMARY KEY,
    `applicant_id`   INT NOT NULL UNIQUE,
    `applicant_code` VARCHAR(50) NOT NULL UNIQUE,
    `password_hash`  VARCHAR(255) NOT NULL,
    `password_plain` VARCHAR(50) NOT NULL,
    `created_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_applicant_accounts_applicant` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 20: staff_messages (บันทึกข้อความจากเจ้าหน้าที่)
CREATE TABLE IF NOT EXISTS `staff_messages` (
    `id`         INT AUTO_INCREMENT PRIMARY KEY,
    `request_id` INT NOT NULL,
    `officer_id` INT NOT NULL,
    `message`    TEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_staff_messages_request` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_staff_messages_officer` FOREIGN KEY (`officer_id`) REFERENCES `officers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 21: request_attachments (เอกสารประกอบคำขอและการตอบกลับ)
CREATE TABLE IF NOT EXISTS `request_attachments` (
    `id`              INT AUTO_INCREMENT PRIMARY KEY,
    `request_id`      INT NOT NULL,
    `file_name`       VARCHAR(255) NOT NULL,
    `file_path`       VARCHAR(512) NOT NULL,
    `mime_type`       VARCHAR(100) NOT NULL,
    `file_size`       INT NOT NULL,
    `uploaded_by`     ENUM('applicant','officer') NOT NULL,
    `attachment_type` VARCHAR(100) NOT NULL, -- 'completed_form', 'supporting_document', 'official_letter', 'approval_document', 'notification_letter', 'other'
    `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_request_attachments_request` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 22: meeting_results (ผลการประชุมคณะทำงาน)
CREATE TABLE IF NOT EXISTS `meeting_results` (
    `id`             INT AUTO_INCREMENT PRIMARY KEY,
    `request_id`     INT NOT NULL,
    `meeting_date`   DATE NOT NULL,
    `result_summary` TEXT NOT NULL,
    `file_name`      VARCHAR(255) NULL,
    `file_path`      VARCHAR(512) NULL,
    `mime_type`      VARCHAR(100) NULL,
    `file_size`      INT NULL,
    `officer_id`     INT NOT NULL,
    `created_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_meeting_results_request` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_meeting_results_officer` FOREIGN KEY (`officer_id`) REFERENCES `officers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 23: workflow_history (บันทึกขั้นตอนและกิจกรรมการทำงาน)
CREATE TABLE IF NOT EXISTS `workflow_history` (
    `id`           INT AUTO_INCREMENT PRIMARY KEY,
    `request_id`   INT NOT NULL,
    `action`       VARCHAR(100) NOT NULL,
    `details`      TEXT NOT NULL,
    `officer_id`   INT NULL,
    `applicant_id` INT NULL,
    `ip_address`   VARCHAR(45) NOT NULL,
    `user_agent`   VARCHAR(255) NOT NULL,
    `created_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_workflow_history_request` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_workflow_history_officer` FOREIGN KEY (`officer_id`) REFERENCES `officers` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_workflow_history_applicant` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 3. DEFAULT SEED DATA
-- ============================================================

-- Seed: Departments (กลุ่มงานหลัก สพม.นราธิวาส)
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

-- Seed: Roles (RBAC Roles)
INSERT IGNORE INTO `roles` (`code`, `name_th`, `description`, `is_system`) VALUES
('admin', 'ผู้ดูแลระบบ',    'สิทธิ์สูงสุด สามารถจัดการทุกส่วนของระบบได้', 1),
('head',  'หัวหน้ากลุ่มงาน','สามารถอนุมัติคำขอ มอบหมายงาน และดูรายงาน',   1),
('staff', 'เจ้าหน้าที่',     'สามารถดำเนินการคำขอตามที่ได้รับมอบหมาย',      1);

-- Seed: Permissions (รายการสิทธิ์การใช้งานของระบบหลังบ้าน)
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

-- Seed: Role-Permissions mapping
-- 1. Admin gets all permissions
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r, `permissions` p WHERE r.code = 'admin';

-- 2. Head gets management permissions
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r, `permissions` p
WHERE r.code = 'head' AND p.code IN (
    'dashboard.view','dashboard.kpi',
    'requests.view','requests.view_all','requests.assign',
    'requests.change_status','requests.approve','requests.reject',
    'requests.message','requests.internal_note',
    'officers.view','departments.view','services.view'
);

-- 3. Staff gets basic operation permissions
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r, `permissions` p
WHERE r.code = 'staff' AND p.code IN (
    'dashboard.view',
    'requests.view','requests.change_status',
    'requests.message','requests.internal_note',
    'departments.view','services.view'
);

-- Seed: Request Types (ประเภทคำขอเอกสาร)
INSERT IGNORE INTO `request_types` (`code`, `name_th`, `doc_checklist`, `active`, `sort_order`) VALUES
('HS', 'ขอใบแทนใบสุทธิ / ใบประกาศนียบัตร (High School Certificate)',
    '["สำเนาบัตรประจำตัวประชาชน","รูปถ่ายหน้าตรง 1.5 นิ้ว (ถ่ายไม่เกิน 6 เดือน)","ใบแจ้งความใบสุทธิสูญหาย"]',
    1, 1),
('TR', 'ขอใบแสดงผลการเรียน (Transcript)',
    '["สำเนาบัตรประจำตัวประชาชน","สำเนาทะเบียนบ้าน","รูปถ่ายหน้าตรง 1.5 นิ้ว"]',
    1, 2),
('ED', 'ขอหนังสือรับรองวุฒิการศึกษาเพื่อศึกษาต่อ (Education Verification)',
    '["สำเนาบัตรประจำตัวประชาชน","หนังสือแจ้งความจำนงจากหน่วยงานที่เกี่ยวข้อง"]',
    1, 3),
('HMS', 'ขออนุญาตจัดการศึกษาขั้นพื้นฐานโดยครอบครัว (Homeschool Management)',
    '["แบบคำขอจัดการศึกษา (Completed Form)","แผนการจัดการศึกษา (Education Plan)","สำเนาบัตรประจำตัวประชาชนผู้ปกครอง","สำเนาทะเบียนบ้านผู้ปกครองและผู้เรียน","หลักฐานแสดงวุฒิการศึกษาของผู้ปกครอง"]',
    1, 4);

-- Seed: Default Officer Accounts (เจ้าหน้าที่จำลองในระบบ)
-- Password for all accounts: admin123 (Encrypted using Bcrypt)
INSERT IGNORE INTO `officers` (`username`, `password_hash`, `name`, `email`, `role`) VALUES
('admin', '$2y$10$hR9eEDlmu8iIzBeB75y.funu03kNfw.f1h82yVOCNW7p/3Tvo.lwG', 'ผู้ดูแลระบบ สพม.นราธิวาส',     'admin@spmnara.go.th', 'admin'),
('head',  '$2y$10$hR9eEDlmu8iIzBeB75y.funu03kNfw.f1h82yVOCNW7p/3Tvo.lwG', 'หัวหน้ากลุ่ม สพม.นราธิวาส',   'head@spmnara.go.th',  'head'),
('staff', '$2y$10$hR9eEDlmu8iIzBeB75y.funu03kNfw.f1h82yVOCNW7p/3Tvo.lwG', 'เจ้าหน้าที่ สพม.นราธิวาส',    'staff@spmnara.go.th', 'staff');

SET FOREIGN_KEY_CHECKS = 1;
