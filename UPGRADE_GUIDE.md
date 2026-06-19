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

เปิด **phpMyAdmin** หรือ MySQL CLI แล้วรัน:

```bash
mysql -u USERNAME -p DATABASE_NAME < upgrade_migration.sql
```

หรือคัดลอกเนื้อหา `upgrade_migration.sql` ไปวางใน phpMyAdmin → SQL tab แล้วกด Execute

**หมายเหตุ**: Migration script ใช้ `IF NOT EXISTS` และ `INSERT IGNORE` ทุกจุด สามารถรันซ้ำได้อย่างปลอดภัย

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
