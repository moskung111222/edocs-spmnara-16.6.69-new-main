# NEW_FILES.md — รายการไฟล์ที่เพิ่มใหม่ทั้งหมด

## Database Migration
| ไฟล์ | คำอธิบาย |
|---|---|
| `upgrade_migration.sql` | Migration script สำหรับเพิ่ม 5 ตาราง + ALTER 2 ตาราง + Seed data |

## Models (4 ไฟล์)
| ไฟล์ | คำอธิบาย |
|---|---|
| `public_html/models/Department.php` | Model จัดการกลุ่มงาน — CRUD, toggleActive, getOfficers |
| `public_html/models/Officer.php` | Model จัดการเจ้าหน้าที่ — CRUD, password, dept assignment M:N |
| `public_html/models/Role.php` | Model จัดการ roles — CRUD, permission sync |
| `public_html/models/Permission.php` | Model จัดการ permissions — getAll, grouped, byModule |

## Services (1 ไฟล์)
| ไฟล์ | คำอธิบาย |
|---|---|
| `public_html/services/RBACService.php` | RBAC permission checking + session loading + fallback logic |

## Controllers (4 ไฟล์)
| ไฟล์ | คำอธิบาย |
|---|---|
| `public_html/controllers/DepartmentController.php` | CRUD กลุ่มงาน + RBAC gating |
| `public_html/controllers/ServiceController.php` | CRUD ประเภทบริการ (request_types) + dept linking |
| `public_html/controllers/OfficerController.php` | CRUD เจ้าหน้าที่ + dept assignment + head management |
| `public_html/controllers/RoleController.php` | CRUD roles + permission matrix editor |

## Views (6 ไฟล์)
| ไฟล์ | คำอธิบาย |
|---|---|
| `public_html/views/admin/departments.php` | UI จัดการกลุ่มงาน — inline create, edit modals, toggle |
| `public_html/views/admin/services.php` | UI จัดการประเภทบริการ — create, edit, doc checklist |
| `public_html/views/admin/officers.php` | UI รายชื่อเจ้าหน้าที่ — filters, table |
| `public_html/views/admin/officer_form.php` | UI เพิ่ม/แก้ไขเจ้าหน้าที่ — dept checkboxes, head selection |
| `public_html/views/admin/roles.php` | UI รายการ roles — create form, table |
| `public_html/views/admin/role_permissions.php` | UI permission matrix — checkbox grid by module |

## Documentation (4 ไฟล์)
| ไฟล์ | คำอธิบาย |
|---|---|
| `IMPACT_ANALYSIS.md` | ผลกระทบการอัปเกรด |
| `NEW_FILES.md` | รายการไฟล์ใหม่ (ไฟล์นี้) |
| `MODIFIED_FILES.md` | รายการไฟล์ที่แก้ไข |
| `UPGRADE_GUIDE.md` | คู่มืออัปเกรดทีละขั้นตอน |

---
**รวมทั้งหมด: 15 ไฟล์โค้ดใหม่ + 1 SQL migration + 4 เอกสาร = 20 ไฟล์**
