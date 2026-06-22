# UPGRADE_GUIDE.md — คู่มืออัปเกรด NWT Document Submission System v2.0

## ข้อกำหนดเบื้องต้น
- ระบบเดิม v1.0 ที่ทำงานได้ปกติ (migration.sql ถูก apply แล้ว)
- PHP >= 7.4
- MySQL >= 5.7
- สำรองข้อมูล (Backup) ก่อนดำเนินการ

---

## ขั้นตอนที่ 1: สำรองข้อมูล

```bash
# สำรอง Database
mysqldump -u USERNAME -p DATABASE_NAME > backup_before_upgrade.sql

# สำรองไฟล์ PHP
cp -r public_html/ public_html_backup/
```

---

## ขั้นตอนที่ 2: อัปโหลดไฟล์ใหม่

อัปโหลดไฟล์ใหม่ทั้งหมดตาม `NEW_FILES.md`:

### Models (4 ไฟล์)
```
public_html/models/Department.php
public_html/models/Officer.php
public_html/models/Role.php
public_html/models/Permission.php
```

### Services (1 ไฟล์)
```
public_html/services/RBACService.php
```

### Controllers (4 ไฟล์)
```
public_html/controllers/DepartmentController.php
public_html/controllers/ServiceController.php
public_html/controllers/OfficerController.php
public_html/controllers/RoleController.php
```

### Views (6 ไฟล์)
```
public_html/views/admin/departments.php
public_html/views/admin/services.php
public_html/views/admin/officers.php
public_html/views/admin/officer_form.php
public_html/views/admin/roles.php
public_html/views/admin/role_permissions.php
```

---

## ขั้นตอนที่ 3: อัปเดตไฟล์ที่แก้ไข

อัปโหลดไฟล์ที่แก้ไขตาม `MODIFIED_FILES.md`:

```
public_html/index.php
public_html/controllers/AuthController.php
public_html/controllers/AdminController.php
public_html/middleware/AuthMiddleware.php
public_html/views/admin/dashboard.php
```

---

## ขั้นตอนที่ 4: รัน Database Migration

เนื่องจากไฟล์ `upgrade_migration.sql` ถูกรวมเข้ากับสคริปต์ติดตั้งหลักและถูกลบออกจากระดับรูทโปรเจกต์แล้วเพื่อความสะอาดของซอร์สโค้ด ท่านสามารถคัดลอกคำสั่ง SQL ด้านล่างนี้ไปรันใน **phpMyAdmin** → SQL tab หรือนำไปรันบนระบบของท่านเพื่ออัปเกรดโครงสร้างฐานข้อมูลจาก v1.0 เป็น v2.0 ได้โดยไม่สูญเสียข้อมูลเดิม:

```sql
-- ============================================================
-- UPGRADE MIGRATION v2.0 — Incremental (Non-destructive)
-- ============================================================

-- NEW TABLE 1: departments (กลุ่มงาน/แผนก)
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

-- NEW TABLE 2: roles (RBAC roles)
CREATE TABLE IF NOT EXISTS `roles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(50) NOT NULL UNIQUE,
    `name_th` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `is_system` TINYINT(1) DEFAULT 0,
    `active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NEW TABLE 3: permissions (สิทธิ์การใช้งาน)
CREATE TABLE IF NOT EXISTS `permissions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(100) NOT NULL UNIQUE,
    `name_th` VARCHAR(255) NOT NULL,
    `module` VARCHAR(50) NOT NULL,
    `description` TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NEW TABLE 4: role_permissions (ผูก role <-> permission)
CREATE TABLE IF NOT EXISTS `role_permissions` (
    `role_id` INT NOT NULL,
    `permission_id` INT NOT NULL,
    PRIMARY KEY (`role_id`, `permission_id`),
    FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NEW TABLE 5: officer_departments (ผูก officer <-> department M:N)
CREATE TABLE IF NOT EXISTS `officer_departments` (
    `officer_id` INT NOT NULL,
    `department_id` INT NOT NULL,
    `is_head` TINYINT(1) DEFAULT 0,
    `assigned_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`officer_id`, `department_id`),
    FOREIGN KEY (`officer_id`) REFERENCES `officers` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ALTER EXISTING: officers table
ALTER TABLE `officers` MODIFY COLUMN `role` VARCHAR(50) NOT NULL DEFAULT 'staff';

-- Add columns and constraint dynamically if not exist
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

-- ALTER EXISTING: request_types table
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

-- SEED DATA: Default departments
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

-- SEED DATA: Default roles
INSERT IGNORE INTO `roles` (`code`, `name_th`, `description`, `is_system`) VALUES
('admin', 'ผู้ดูแลระบบ',       'สิทธิ์สูงสุด สามารถจัดการทุกส่วนของระบบได้',  1),
('head',  'หัวหน้ากลุ่มงาน',    'สามารถอนุมัติคำขอ มอบหมายงาน และดูรายงาน',    1),
('staff', 'เจ้าหน้าที่',        'สามารถดำเนินการคำขอตามที่ได้รับมอบหมาย',       1);

-- SEED DATA: Default permissions
INSERT IGNORE INTO `permissions` (`code`, `name_th`, `module`) VALUES
('dashboard.view',          'ดู Dashboard',                   'dashboard'),
('dashboard.kpi',           'ดู KPI ผู้บริหาร',                'dashboard'),
('requests.view',           'ดูรายการคำขอ',                   'requests'),
('requests.view_all',       'ดูคำขอทุกกลุ่มงาน',              'requests'),
('requests.assign',         'มอบหมายคำขอ',                    'requests'),
('requests.change_status',  'เปลี่ยนสถานะคำขอ',               'requests'),
('requests.approve',        'อนุมัติคำขอ',                     'requests'),
('requests.reject',         'ปฏิเสธคำขอ',                     'requests'),
('requests.message',        'ส่งข้อความ',                      'requests'),
('requests.internal_note',  'เขียนบันทึกภายใน',               'requests'),
('officers.view',           'ดูรายชื่อเจ้าหน้าที่',              'officers'),
('officers.create',         'เพิ่มเจ้าหน้าที่',                 'officers'),
('officers.edit',           'แก้ไขเจ้าหน้าที่',                 'officers'),
('officers.delete',         'ลบเจ้าหน้าที่',                   'officers'),
('departments.view',        'ดูกลุ่มงาน',                      'departments'),
('departments.create',      'เพิ่มกลุ่มงาน',                   'departments'),
('departments.edit',        'แก้ไขกลุ่มงาน',                   'departments'),
('departments.delete',      'ลบกลุ่มงาน',                     'departments'),
('services.view',           'ดูประเภทบริการ',                   'services'),
('services.create',         'เพิ่มประเภทบริการ',                'services'),
('services.edit',           'แก้ไขประเภทบริการ',                'services'),
('services.delete',         'ลบประเภทบริการ',                  'services'),
('roles.view',              'ดู Roles',                        'roles'),
('roles.manage',            'จัดการ Roles & Permissions',       'roles'),
('audit.view',              'ดู Audit Log',                    'audit');

-- SEED DATA: Assign permissions to roles
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r, `permissions` p WHERE r.code = 'admin';

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r, `permissions` p
WHERE r.code = 'head' AND p.code IN (
    'dashboard.view', 'dashboard.kpi',
    'requests.view', 'requests.view_all', 'requests.assign',
    'requests.change_status', 'requests.approve', 'requests.reject',
    'requests.message', 'requests.internal_note',
    'officers.view', 'departments.view', 'services.view'
);

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r, `permissions` p
WHERE r.code = 'staff' AND p.code IN (
    'dashboard.view',
    'requests.view', 'requests.change_status',
    'requests.message', 'requests.internal_note',
    'departments.view', 'services.view'
);
```

**หมายเหตุ**: คำสั่งด้านบนใช้ `IF NOT EXISTS` และ `INSERT IGNORE` ทุกจุด สามารถรันบนฐานข้อมูลเดิมได้อย่างปลอดภัย

---

## ขั้นตอนที่ 5: ทดสอบ

### 5.1 ทดสอบ Login เดิม
1. เข้า `/admin/login`
2. Login ด้วย admin / head / staff ที่มีอยู่
3. ตรวจสอบว่า Dashboard โหลดปกติ + มี sidebar links ใหม่

### 5.2 ทดสอบ Public Flow
1. เข้าหน้า `/` (หน้าแรกประชาชน)
2. ยื่นคำขอเอกสาร
3. ตรวจสอบว่า flow เดิมยังทำงานปกติ

### 5.3 ทดสอบ Features ใหม่
1. **กลุ่มงาน**: เข้า `/admin/departments` → เพิ่ม/แก้ไข/เปิด-ปิด
2. **ประเภทบริการ**: เข้า `/admin/services` → เพิ่ม/แก้ไข/ผูกกลุ่มงาน
3. **เจ้าหน้าที่**: เข้า `/admin/officers` → ดูรายชื่อ → สร้าง/แก้ไข
4. **Head Management**: แก้ไขเจ้าหน้าที่ → เลือก "เป็นหัวหน้าของกลุ่มงาน"
5. **Staff Assignment**: แก้ไขเจ้าหน้าที่ → เลือกกลุ่มงานที่มอบหมาย
6. **RBAC**: เข้า `/admin/roles` → เลือก Role → แก้ไขสิทธิ์

### 5.4 ทดสอบ RBAC
1. Login ด้วย staff → ไม่เห็นเมนู "จัดการเจ้าหน้าที่" (ถ้าไม่มีสิทธิ์)
2. Login ด้วย admin → เห็นทุกเมนู
3. ลอง assign_officer ด้วย staff → ต้องถูก block

---

## ขั้นตอนที่ 6: Re-login

> **สำคัญ**: หลังจากรัน migration แล้ว ต้อง **Logout แล้ว Login ใหม่** เพื่อให้ RBAC permissions ถูกโหลดเข้า session

---

## การ Rollback (ถ้าจำเป็น)

ดูคำสั่ง SQL สำหรับ Rollback ใน `IMPACT_ANALYSIS.md` ส่วน "Rollback Plan"

สำหรับไฟล์ PHP:
```bash
# คืนค่าไฟล์จาก backup
cp -r public_html_backup/ public_html/

# หรือใช้ git
git checkout -- public_html/
```
