# MODIFIED_FILES.md — รายการไฟล์ที่แก้ไข

## ไฟล์ที่ถูกแก้ไข (5 ไฟล์)

### 1. `public_html/index.php` (Router)
- **การเปลี่ยนแปลง**: เพิ่ม 8 routes ใหม่
- **จุดที่แก้ไข**: หลัง `admin/request` route และก่อน API routes
- **Routes ใหม่**:
  - `admin/departments` → DepartmentController::index()
  - `admin/services` → ServiceController::index()
  - `admin/officers` → OfficerController::index()
  - `admin/officer/create` → OfficerController::create()
  - `admin/officer/edit` → OfficerController::edit()
  - `admin/roles` → RoleController::index()
  - `admin/role/edit` → RoleController::edit()
  - `admin/role/create` → RoleController::create()

---

### 2. `public_html/controllers/AuthController.php`
- **การเปลี่ยนแปลง**: เพิ่ม 2 บรรทัด หลัง session assignment
- **สิ่งที่เพิ่ม**:
  ```php
  $_SESSION['officer_department_id'] = $officer['department_id'] ?? null;
  \App\Services\RBACService::loadSessionPermissions($officer['id']);
  ```
- **เหตุผล**: โหลด RBAC permissions เข้า session ตอน login

---

### 3. `public_html/middleware/AuthMiddleware.php`
- **การเปลี่ยนแปลง**: เพิ่ม 2 methods ใหม่ (ไม่แก้ไข methods เดิม)
- **Methods ใหม่**:
  - `requirePermission($permissionCode)` — Block 403 ถ้าไม่มีสิทธิ์
  - `hasPermission($permissionCode)` — Return bool สำหรับ UI conditional

---

### 4. `public_html/controllers/AdminController.php`
- **การเปลี่ยนแปลง**: แก้ไข if condition 1 จุด ในฟังก์ชัน `requestDetail()`
- **ก่อน**:
  ```php
  if (!in_array($_SESSION['officer_role'], ['admin', 'head'])) {
  ```
- **หลัง**:
  ```php
  if (!AuthMiddleware::hasPermission('requests.assign')) {
  ```
- **เหตุผล**: ย้ายจาก hard-coded role check มาใช้ RBAC permission

---

### 5. `public_html/views/admin/dashboard.php`
- **การเปลี่ยนแปลง**: เพิ่ม sidebar navigation links 4 รายการ
- **จุดที่แก้ไข**: ภายใน `<nav>` sidebar (ระหว่าง link "รอคำชี้แจงเพิ่ม" กับ `</nav>`)
- **Links ใหม่** (ซ่อน/แสดงตาม RBAC):
  - กลุ่มงาน/แผนก (departments.view)
  - ประเภทบริการ (services.view)
  - จัดการเจ้าหน้าที่ (officers.view)
  - Roles & สิทธิ์ (roles.view)
