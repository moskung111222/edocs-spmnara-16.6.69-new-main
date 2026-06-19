-- ============================================================
-- UPGRADE MIGRATION v2.0 — Incremental (Non-destructive)
-- NWT Document Submission System
-- Compatible with: migration.sql (base schema v1.0)
-- ============================================================
-- INSTRUCTIONS:
--   1. Backup database BEFORE running this script
--   2. Run this script on database that already has migration.sql applied
--   3. This script is SAFE to re-run (uses IF NOT EXISTS / IF NOT EXISTS checks)
-- ============================================================

-- ============================================================
-- NEW TABLE 1: departments (กลุ่มงาน/แผนก)
-- ============================================================
CREATE TABLE IF NOT EXISTS `departments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(20) NOT NULL UNIQUE,
    `name_th` VARCHAR(255) NOT NULL,
    `name_en` VARCHAR(255) NULL,
    `description` TEXT NULL,
    `active` TINYINT(1) DEFAULT 1,
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- NEW TABLE 2: roles (RBAC roles)
-- ============================================================
CREATE TABLE IF NOT EXISTS `roles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(50) NOT NULL UNIQUE,
    `name_th` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `is_system` TINYINT(1) DEFAULT 0,
    `active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- NEW TABLE 3: permissions (สิทธิ์การใช้งาน)
-- ============================================================
CREATE TABLE IF NOT EXISTS `permissions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(100) NOT NULL UNIQUE,
    `name_th` VARCHAR(255) NOT NULL,
    `module` VARCHAR(50) NOT NULL,
    `description` TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- NEW TABLE 4: role_permissions (ผูก role <-> permission)
-- ============================================================
CREATE TABLE IF NOT EXISTS `role_permissions` (
    `role_id` INT NOT NULL,
    `permission_id` INT NOT NULL,
    PRIMARY KEY (`role_id`, `permission_id`),
    FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- NEW TABLE 5: officer_departments (ผูก officer <-> department M:N)
-- ============================================================
CREATE TABLE IF NOT EXISTS `officer_departments` (
    `officer_id` INT NOT NULL,
    `department_id` INT NOT NULL,
    `is_head` TINYINT(1) DEFAULT 0,
    `assigned_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`officer_id`, `department_id`),
    FOREIGN KEY (`officer_id`) REFERENCES `officers` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- ALTER EXISTING: officers table
-- Add department_id (nullable FK) and active column
-- Change role from ENUM to VARCHAR for RBAC flexibility
-- ============================================================

-- Step 1: Change ENUM to VARCHAR (backward-compatible, existing values preserved)
ALTER TABLE `officers`
    MODIFY COLUMN `role` VARCHAR(50) NOT NULL DEFAULT 'staff';

-- Step 2: Add new columns (use stored procedure to avoid duplicate column errors)
DELIMITER //
CREATE PROCEDURE `_upgrade_officers_columns`()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'officers' AND COLUMN_NAME = 'department_id'
    ) THEN
        ALTER TABLE `officers` ADD COLUMN `department_id` INT NULL AFTER `role`;
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'officers' AND COLUMN_NAME = 'active'
    ) THEN
        ALTER TABLE `officers` ADD COLUMN `active` TINYINT(1) DEFAULT 1 AFTER `email`;
    END IF;

    -- Add FK only if not exists
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'officers' AND CONSTRAINT_NAME = 'fk_officers_department'
    ) THEN
        ALTER TABLE `officers`
            ADD CONSTRAINT `fk_officers_department` FOREIGN KEY (`department_id`)
            REFERENCES `departments` (`id`) ON DELETE SET NULL;
    END IF;
END //
DELIMITER ;
CALL `_upgrade_officers_columns`();
DROP PROCEDURE IF EXISTS `_upgrade_officers_columns`;

-- ============================================================
-- ALTER EXISTING: request_types table
-- Add department_id, description, sort_order
-- ============================================================
DELIMITER //
CREATE PROCEDURE `_upgrade_request_types_columns`()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'request_types' AND COLUMN_NAME = 'department_id'
    ) THEN
        ALTER TABLE `request_types` ADD COLUMN `department_id` INT NULL AFTER `doc_checklist`;
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'request_types' AND COLUMN_NAME = 'description'
    ) THEN
        ALTER TABLE `request_types` ADD COLUMN `description` TEXT NULL AFTER `name_th`;
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'request_types' AND COLUMN_NAME = 'sort_order'
    ) THEN
        ALTER TABLE `request_types` ADD COLUMN `sort_order` INT DEFAULT 0 AFTER `active`;
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'request_types' AND CONSTRAINT_NAME = 'fk_request_types_department'
    ) THEN
        ALTER TABLE `request_types`
            ADD CONSTRAINT `fk_request_types_department` FOREIGN KEY (`department_id`)
            REFERENCES `departments` (`id`) ON DELETE SET NULL;
    END IF;
END //
DELIMITER ;
CALL `_upgrade_request_types_columns`();
DROP PROCEDURE IF EXISTS `_upgrade_request_types_columns`;

-- ============================================================
-- SEED DATA: Default departments (กลุ่มงาน สพม.นราธิวาส)
-- ============================================================
INSERT IGNORE INTO `departments` (`code`, `name_th`, `name_en`, `sort_order`) VALUES
('ADMIN',   'กลุ่มอำนวยการ',                          'General Administration',              1),
('FINANCE', 'กลุ่มบริหารงานการเงินและสินทรัพย์',        'Finance and Assets Management',       2),
('SUPER',   'กลุ่มนิเทศ ติดตาม และประเมินผลฯ',        'Supervision and Evaluation',          3),
('HR',      'กลุ่มบริหารงานบุคคล',                     'Human Resources',                     4),
('PROMOTE', 'กลุ่มส่งเสริมการจัดการศึกษา',              'Education Promotion',                 5),
('PLAN',    'กลุ่มนโยบายและแผน',                       'Policy and Planning',                 6),
('AUDIT',   'หน่วยตรวจสอบภายใน',                       'Internal Audit',                      7),
('TEACHER', 'กลุ่มพัฒนาครูและบุคลากรฯ',                'Teacher Development',                 8),
('LAW',     'กลุ่มกฎหมายและคดี',                       'Legal Affairs',                       9),
('ICT',     'กลุ่มส่งเสริมการศึกษาทางไกลฯ',             'Distance Education and ICT',          10);

-- ============================================================
-- SEED DATA: Default roles (backward-compatible)
-- ============================================================
INSERT IGNORE INTO `roles` (`code`, `name_th`, `description`, `is_system`) VALUES
('admin', 'ผู้ดูแลระบบ',       'สิทธิ์สูงสุด สามารถจัดการทุกส่วนของระบบได้',  1),
('head',  'หัวหน้ากลุ่มงาน',    'สามารถอนุมัติคำขอ มอบหมายงาน และดูรายงาน',    1),
('staff', 'เจ้าหน้าที่',        'สามารถดำเนินการคำขอตามที่ได้รับมอบหมาย',       1);

-- ============================================================
-- SEED DATA: Default permissions
-- ============================================================
INSERT IGNORE INTO `permissions` (`code`, `name_th`, `module`) VALUES
-- Dashboard
('dashboard.view',          'ดู Dashboard',                   'dashboard'),
('dashboard.kpi',           'ดู KPI ผู้บริหาร',                'dashboard'),
-- Requests
('requests.view',           'ดูรายการคำขอ',                   'requests'),
('requests.view_all',       'ดูคำขอทุกกลุ่มงาน',              'requests'),
('requests.assign',         'มอบหมายคำขอ',                    'requests'),
('requests.change_status',  'เปลี่ยนสถานะคำขอ',               'requests'),
('requests.approve',        'อนุมัติคำขอ',                     'requests'),
('requests.reject',         'ปฏิเสธคำขอ',                     'requests'),
('requests.message',        'ส่งข้อความ',                      'requests'),
('requests.internal_note',  'เขียนบันทึกภายใน',               'requests'),
-- Officers
('officers.view',           'ดูรายชื่อเจ้าหน้าที่',              'officers'),
('officers.create',         'เพิ่มเจ้าหน้าที่',                 'officers'),
('officers.edit',           'แก้ไขเจ้าหน้าที่',                 'officers'),
('officers.delete',         'ลบเจ้าหน้าที่',                   'officers'),
-- Departments
('departments.view',        'ดูกลุ่มงาน',                      'departments'),
('departments.create',      'เพิ่มกลุ่มงาน',                   'departments'),
('departments.edit',        'แก้ไขกลุ่มงาน',                   'departments'),
('departments.delete',      'ลบกลุ่มงาน',                     'departments'),
-- Request Types / Services
('services.view',           'ดูประเภทบริการ',                   'services'),
('services.create',         'เพิ่มประเภทบริการ',                'services'),
('services.edit',           'แก้ไขประเภทบริการ',                'services'),
('services.delete',         'ลบประเภทบริการ',                  'services'),
-- Roles
('roles.view',              'ดู Roles',                        'roles'),
('roles.manage',            'จัดการ Roles & Permissions',       'roles'),
-- Audit
('audit.view',              'ดู Audit Log',                    'audit');

-- ============================================================
-- SEED DATA: Assign permissions to roles
-- ============================================================

-- Admin gets ALL permissions
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r, `permissions` p WHERE r.code = 'admin';

-- Head gets selected permissions
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r, `permissions` p
WHERE r.code = 'head' AND p.code IN (
    'dashboard.view', 'dashboard.kpi',
    'requests.view', 'requests.view_all', 'requests.assign',
    'requests.change_status', 'requests.approve', 'requests.reject',
    'requests.message', 'requests.internal_note',
    'officers.view',
    'departments.view',
    'services.view'
);

-- Staff gets limited permissions
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r, `permissions` p
WHERE r.code = 'staff' AND p.code IN (
    'dashboard.view',
    'requests.view', 'requests.change_status',
    'requests.message', 'requests.internal_note',
    'departments.view',
    'services.view'
);
