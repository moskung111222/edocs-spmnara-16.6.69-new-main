# System Architecture

ระบบนี้ขับเคลื่อนด้วยสถาปัตยกรรมแบบ **MVC (Model-View-Controller)** แบบกำหนดขึ้นเอง (Custom Light MVC) โดยแยกส่วนตรรกะทางธุรกิจ การแสดงผล และโครงสร้างข้อมูลออกจากกันอย่างชัดเจน

---

## 1. MVC Structure

```
public_html/
├── app/
│   └── config/
│       ├── Env.php        <- ตัวโหลดและจัดการตัวแปรสภาพแวดล้อม (.env)
│       ├── Database.php   <- จัดการการเชื่อมต่อฐานข้อมูล MySQL (mysqli)
│       ├── Mail.php       <- ตั้งค่าการเชื่อมต่อผู้ให้บริการ SMTP
│       └── Config.php     <- เก็บสถานะ และค่าคงที่ที่ใช้งานร่วมกัน
├── controllers/
│   ├── AuthController.php    <- ตรรกะการล็อกอินและออกจากระบบของเจ้าหน้าที่
│   ├── RequestController.php <- ตรรกะฝั่งประชาชน (ยื่นคำขอ, ยืนยัน OTP, แชท, ดาวน์โหลด)
│   └── AdminController.php   <- ตรรกะฝั่งเจ้าหน้าที่ (คิวงาน, มอบหมายงาน, เปลี่ยนสถานะ, ตอบแชท)
├── models/
│   ├── Applicant.php   <- ข้อมูลและคิวรี่ตาราง applicants
│   ├── Attachment.php  <- ข้อมูลและคิวรี่ตาราง attachments
│   └── Request.php     <- ข้อมูลและคิวรี่ตาราง requests, status_history, messages
├── middleware/
│   ├── CsrfMiddleware.php  <- สร้างและตรวจสอบ token ป้องกันการยิงข้ามไซต์
│   └── AuthMiddleware.php  <- ตรวจสอบสิทธิ์การเข้าใช้งาน Session เจ้าหน้าที่
├── services/
│   ├── MailService.php    <- จัดการจัดส่ง HTML Mail (PHPMailer / mail() fallback)
│   ├── PusherService.php  <- บริการส่งข้อมูล Realtime ผ่าน Pusher API (cURL)
│   └── UploadService.php  <- ตรวจสอบความถูกต้องและจัดเก็บเอกสาร PDF แบบปลอดภัย
└── views/
    ├── admin/             <- หน้าจอควบคุมของเจ้าหน้าที่ (login, dashboard, request_detail)
    └── public/            <- หน้าจอฝั่งประชาชน (index, create, verify, track, 404)
```

- **Router**: อยู่ใน [index.php](file:///e:/edocs-spmnara%2016.6.69/public_html/index.php) ทำหน้าที่แยกแยะ Query `$_GET['route']` แล้วเรียกใช้งาน Controller และ Action ที่สัมพันธ์กัน
- **Autoloader**: ใช้การค้นหาระดับเนมสเปซแบบกำหนดเองในการค้นหาคลาสภายใต้โฟลเดอร์ `public_html/`

---

## 2. Data Flow

### การยื่นคำขอเอกสารของประชาชน
1. ประชาชนส่งข้อมูลส่วนตัวและแบบฟอร์มผ่านทาง `public_html/request/create`
2. **RequestController** รับค่า ตรวจสอบ และบันทึกหลักฐานผ่าน **UploadService** ไปไว้ในที่จัดเก็บชั่วคราวบน Session `$_SESSION['temp_submission']`
3. ระบบสร้างรหัส OTP และจัดส่งเมลแจ้งเตือนผ่าน **MailService** จากนั้นส่งผู้ใช้ไปที่หน้าจอ `/request/verify`
4. เมื่อประชาชนกรอกรหัส OTP ถูกต้อง ระบบจะสร้างแถวในตาราง `applicants`, `requests`, `attachments` และเรียกบันทึกประวัติการเปลี่ยนสถานะชุดแรกผ่าน **Request::logStatusHistory**
5. ระบบยิงการแจ้งเตือน Realtime ไปยัง **PusherService** เพื่อส่งข้อมูลไปยังหน้าจอเจ้าหน้าที่ (Dashboard)

---

## 3. Authentication Flow

### การเข้าสู่ระบบของเจ้าหน้าที่
1. เจ้าหน้าที่กรอกรหัสผ่านผ่านหน้าจอ `/admin/login`
2. **AuthController** ตรวจสอบรหัสผ่านที่ป้อนเข้ามาด้วยฟังก์ชัน `password_verify` เทียบกับฟิลด์ `password_hash` ในตาราง `officers`
3. เมื่อรหัสถูกต้อง ระบบจะสร้าง Session ดังนี้:
   - `$_SESSION['officer_id']` = รหัสผู้ใช้งาน
   - `$_SESSION['officer_name']` = ชื่อเจ้าหน้าที่
   - `$_SESSION['officer_role']` = สิทธิ์การทำงาน (`staff`, `head`, `admin`)
4. ทุกเส้นทางควบคุมของเจ้าหน้าที่ (เช่น `/admin/dashboard`, `/admin/request`) จะถูกป้องกันผ่าน **AuthMiddleware::requireOfficer()**

---

## 4. File Upload Flow

### การจัดเก็บไฟล์แบบปลอดภัย (Secure File Upload)
```
[User Form] -> finfo (MIME Check) -> Magic Bytes Check (%PDF) -> Random Name Generator (bin2hex) -> [private_storage/documents/]
```
1. **MIME Validation**: ตรวจสอบกับฟังก์ชัน `finfo_file` ป้องกันการดักแปลงนามสกุลหลอกลวง
2. **Structure Verification**: ตรวจสอบหัวไฟล์ (Magic Bytes) ขนาด 4 ไบต์แรกว่าต้องตรงกับตัวอักษร `%PDF`
3. **Storage Placement**: เคลื่อนย้ายไฟล์แนบไปเก็บไว้นอก Web Root ที่โฟลเดอร์ `private_storage/documents/` ป้องกันไม่ให้บุคคลภายนอกเข้าถึงไฟล์ได้โดยตรงผ่าน URL
4. **Access Control**: การดาวน์โหลดไฟล์จะต้องเรียกผ่าน Controller `/download?id=X` ซึ่งจะตรวจสอบสิทธิ์ Session ประชาชน (Tracking Auth) หรือ Session เจ้าหน้าที่ ก่อนจะดึงข้อมูลผ่าน PHP `readfile()`
