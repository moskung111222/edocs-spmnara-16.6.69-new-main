# IMPACT_ANALYSIS.md — NWT Document Submission System Upgrade v2.0

## สรุปผลกระทบ (Impact Summary)

| หมวด | จำนวน | ความเสี่ยง |
|---|---|---|
| ตาราง DB ใหม่ | 5 | ต่ำ (CREATE IF NOT EXISTS) |
| ALTER ตารางเดิม | 2 (officers, request_types) | กลาง (เพิ่มคอลัมน์เท่านั้น) |
| ไฟล์ใหม่ | 15 | ไม่มี (additive) |
| ไฟล์แก้ไข | 5 | ต่ำ-กลาง |
| ไฟล์ลบ | 0 | ไม่มี |

---

## 1. Database Impact

### 1.1 ตารางใหม่ (5 ตาราง)
| ตาราง | คำอธิบาย | ผลกระทบ |
|---|---|---|
| `departments` | กลุ่มงาน/แผนก | ไม่มี — ตารางใหม่ |
| `roles` | RBAC roles | ไม่มี — ตารางใหม่ |
| `permissions` | สิทธิ์การใช้งาน | ไม่มี — ตารางใหม่ |
| `role_permissions` | ผูก role↔permission | ไม่มี — ตารางใหม่ |
| `officer_departments` | ผูก officer↔department M:N | ไม่มี — ตารางใหม่ |

### 1.2 ALTER ตารางเดิม
| ตาราง | การเปลี่ยนแปลง | ผลกระทบ |
|---|---|---|
| `officers` | MODIFY `role` ENUM→VARCHAR(50), ADD `department_id`, ADD `active` | **กลาง** — ค่าเดิม ('staff','head','admin') ยังคงอยู่ |
| `request_types` | ADD `department_id`, ADD `description`, ADD `sort_order` | ต่ำ — เพิ่มคอลัมน์ nullable |

### 1.3 Seed Data
- 10 กลุ่มงาน, 3 roles, 26 permissions, role_permissions mapping
- ใช้ INSERT IGNORE — ไม่กระทบข้อมูลเดิม

---

## 2. Code Impact

### 2.1 Router (`index.php`)
- **เพิ่ม** 8 routes ใหม่ — ไม่แก้ไข/ลบ routes เดิม
- **ความเสี่ยง**: ต่ำ

### 2.2 AuthController
- **เพิ่ม** 2 บรรทัด — load department_id + RBAC permissions เข้า session
- **ความเสี่ยง**: ต่ำ — เพิ่มหลัง session assignment เดิม

### 2.3 AuthMiddleware
- **เพิ่ม** 2 methods ใหม่ (`requirePermission`, `hasPermission`)
- **ไม่แก้ไข** methods เดิม (`requireOfficer`, `requireTrackingAuth`, `isOfficerLoggedIn`)
- **ความเสี่ยง**: ต่ำ

### 2.4 AdminController
- **เปลี่ยน** if condition 1 จุด: assign_officer role check → RBAC permission check
- **ความเสี่ยง**: กลาง — logic เดิมยัง backward-compatible

### 2.5 Dashboard View
- **เพิ่ม** sidebar menu items 4 รายการ (RBAC-gated)
- **ความเสี่ยง**: ต่ำ

---

## 3. Backward Compatibility

| ด้าน | สถานะ |
|---|---|
| Login เดิม | ✅ ใช้ได้ — credentials เดิมทำงานปกติ |
| Public submission flow | ✅ ไม่มีผลกระทบ |
| Status workflow | ✅ ไม่มีผลกระทบ |
| API endpoints | ✅ ไม่มีผลกระทบ |
| Existing officers | ✅ role values เดิมคงอยู่ |
| RBACService fallback | ✅ ถ้า RBAC tables ว่าง จะ fallback ไปใช้ role-based logic เดิม |

---

## 4. Rollback Plan

```sql
-- Remove RBAC tables
DROP TABLE IF EXISTS role_permissions;
DROP TABLE IF EXISTS permissions;
DROP TABLE IF EXISTS officer_departments;
DROP TABLE IF EXISTS roles;

-- Revert officer table changes
ALTER TABLE officers DROP FOREIGN KEY fk_officers_department;
ALTER TABLE officers DROP COLUMN department_id;
ALTER TABLE officers DROP COLUMN active;
ALTER TABLE officers MODIFY COLUMN role ENUM('staff','head','admin') NOT NULL DEFAULT 'staff';

-- Revert request_types changes
ALTER TABLE request_types DROP FOREIGN KEY fk_request_types_department;
ALTER TABLE request_types DROP COLUMN department_id;
ALTER TABLE request_types DROP COLUMN description;
ALTER TABLE request_types DROP COLUMN sort_order;

-- Remove departments table
DROP TABLE IF EXISTS departments;
```

สำหรับไฟล์ PHP สามารถลบไฟล์ใหม่ทั้งหมดตาม `NEW_FILES.md` และ revert ไฟล์แก้ไขตาม `MODIFIED_FILES.md` ผ่าน git
