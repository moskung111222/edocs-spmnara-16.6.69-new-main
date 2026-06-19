# Database Schema

โครงสร้างฐานข้อมูลของระบบประกอบด้วย 9 ตาราง ภายใต้ระบบจัดการฐานข้อมูล MySQL/MariaDB โดยรองรับการบันทึกข้อความภาษาไทยเต็มรูปแบบด้วยคอลเลกชัน `utf8mb4_unicode_ci`

---

## 1. รายละเอียดตารางข้อมูล (Tables)

### 1.1 `applicants` (ข้อมูลประชาชนผู้ยื่นคำขอ)
เก็บข้อมูลส่วนบุคคลและข้อมูลติดต่อของประชาชนที่เข้าใช้งานระบบ
- `id` (INT, Primary Key, Auto Increment)
- `full_name` (VARCHAR(255), Not Null) - ชื่อ-นามสกุลจริง
- `email` (VARCHAR(255), Unique, Not Null) - อีเมลที่รับ OTP
- `phone` (VARCHAR(20), Not Null) - เบอร์โทรศัพท์มือถือ
- `is_registered` (TINYINT(1), Default 0) - สถานะการลงทะเบียนสมาชิก
- `password_hash` (VARCHAR(255), Null) - รหัสผ่านที่เข้ารหัส (กรณีสมัครสมาชิก)
- `created_at` (TIMESTAMP, Default CURRENT_TIMESTAMP)

### 1.2 `request_types` (ประเภทคำขอเอกสาร)
เก็บประเภทเอกสารและแบบรายการเอกสารหลักฐานบังคับที่กำหนดให้แนบ
- `id` (INT, Primary Key, Auto Increment)
- `code` (VARCHAR(10), Unique, Not Null) - อักษรย่ออ้างอิง เช่น `HS`, `TR`, `ED`
- `name_th` (VARCHAR(255), Not Null) - ชื่อภาษาไทยของประเภทคำขอ
- `doc_checklist` (JSON, Not Null) - อาเรย์เอกสารหลักฐาน เช่น `["สำเนาบัตรประชาชน", "ใบแจ้งความ"]`
- `active` (TINYINT(1), Default 1) - สถานะเปิด/ปิดใช้งานประเภทนี้

### 1.3 `officers` (เจ้าหน้าที่และสิทธิ์การทำงาน)
เก็บข้อมูลเจ้าหน้าที่ สพม.นราธิวาส ที่มีสิทธิ์ควบคุมหลังบ้าน
- `id` (INT, Primary Key, Auto Increment)
- `username` (VARCHAR(50), Unique, Not Null) - ชื่อเข้าใช้ระบบ
- `password_hash` (VARCHAR(255), Not Null) - รหัสผ่าน bcrypt hash
- `name` (VARCHAR(255), Not Null) - ชื่อและนามสกุลเจ้าหน้าที่
- `email` (VARCHAR(255), Unique, Not Null) - อีเมลติดต่อ
- `role` (ENUM('staff', 'head', 'admin'), Default 'staff') - ระดับสิทธิ์
- `created_at` (TIMESTAMP, Default CURRENT_TIMESTAMP)

### 1.4 `requests` (รายการคำขอเอกสาร)
ตารางหลักจัดเก็บคำขอเอกสารแต่ละรายการ
- `id` (INT, Primary Key, Auto Increment)
- `request_no` (VARCHAR(50), Unique, Not Null) - รหัสคำขอ เช่น `NWT-HS-2569-000001`
- `type_id` (INT, Not Null, FK) - ลิงก์กับ `request_types`
- `applicant_id` (INT, Not Null, FK) - ลิงก์กับ `applicants`
- `assigned_officer_id` (INT, Null, FK) - เจ้าหน้าที่ผู้รับผิดชอบ ลิงก์กับ `officers`
- `status` (VARCHAR(50), Default 'submitted') - สถานะปัจจุบัน
- `form_data` (JSON, Not Null) - คำตอบฟิลด์แบบฟอร์มการศึกษาแบบไดนามิก
- `created_at` (TIMESTAMP, Default CURRENT_TIMESTAMP)
- `updated_at` (TIMESTAMP, Default CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)

### 1.5 `attachments` (ไฟล์หลักฐานแนบ)
เก็บข้อมูลและเส้นทางเก็บไฟล์เอกสาร PDF
- `id` (INT, Primary Key, Auto Increment)
- `request_id` (INT, Not Null, FK) - ลิงก์กับ `requests`
- `file_name` (VARCHAR(255), Not Null) - ชื่อไฟล์เดิมตอนอัปโหลด
- `file_path` (VARCHAR(512), Not Null) - พาธเก็บไฟล์จริงบนดิสก์
- `mime_type` (VARCHAR(100), Not Null) - ประเภทไฟล์
- `file_size` (INT, Not Null) - ขนาดไฟล์เป็นไบต์
- `uploaded_by` (ENUM('applicant', 'officer'), Not Null) - ผู้อัปโหลด
- `version` (INT, Default 1) - เวอร์ชันเอกสารหลักฐาน (เพื่อประวัติการส่งใหม่)
- `created_at` (TIMESTAMP, Default CURRENT_TIMESTAMP)

### 1.6 `status_history` (ประวัติความคืบหน้าและการอนุมัติ)
บันทึกประวัติการเปลี่ยนแปลงสถานะคำขอ (Audit Trail)
- `id` (INT, Primary Key, Auto Increment)
- `request_id` (INT, Not Null, FK) - ลิงก์กับ `requests`
- `from_status` (VARCHAR(50), Not Null) - สถานะก่อนเปลี่ยน
- `to_status` (VARCHAR(50), Not Null) - สถานะหลังเปลี่ยน
- `reason` (TEXT, Null) - บันทึกความคิดเห็นของเจ้าหน้าที่/เหตุผลการขอข้อมูลเพิ่ม
- `officer_id` (INT, Null, FK) - เจ้าหน้าที่ผู้แก้ไข ลิงก์กับ `officers`
- `created_at` (TIMESTAMP, Default CURRENT_TIMESTAMP)

### 1.7 `messages` (บทสนทนาแชทโต้ตอบ)
ห้องแชทสื่อสารระหว่างผู้ยื่นคำขอกับเจ้าหน้าที่ของระบบ
- `id` (INT, Primary Key, Auto Increment)
- `request_id` (INT, Not Null, FK) - ลิงก์กับ `requests`
- `sender_type` (ENUM('applicant', 'officer'), Not Null) - ผู้ส่ง
- `body` (TEXT, Not Null) - ข้อความแชท
- `internal_note` (TINYINT(1), Default 0) - บันทึกภายในของเจ้าหน้าที่ (ประชาชนไม่เห็น)
- `created_at` (TIMESTAMP, Default CURRENT_TIMESTAMP)

### 1.8 `audit_logs` (บันทึกกิจกรรมในระบบ)
เก็บประวัติการทำงานของประชาชน เจ้าหน้าที่ และระบบอัตโนมัติ
- `id` (INT, Primary Key, Auto Increment)
- `user_id` (INT, Null) - ไอดีผู้กระทำ
- `user_type` (ENUM('applicant', 'officer', 'system'), Default 'system')
- `action` (VARCHAR(255), Not Null) - การกระทำ
- `module` (VARCHAR(100), Not Null) - ส่วนงาน
- `ip_address` (VARCHAR(45), Not Null) - หมายเลขไอพี
- `user_agent` (VARCHAR(255), Not Null) - เว็บเบราว์เซอร์ที่ใช้
- `created_at` (TIMESTAMP, Default CURRENT_TIMESTAMP)

### 1.9 `otp_verifications` (การยืนยันรหัส OTP)
บันทึกข้อมูลเพื่อใช้ตรวจสอบความถูกต้องของรหัสยืนยันตัวตน
- `id` (INT, Primary Key, Auto Increment)
- `email` (VARCHAR(255), Not Null)
- `otp_code` (VARCHAR(10), Not Null)
- `expired_at` (DATETIME, Not Null)
- `attempts` (INT, Default 0) - จำนวนครั้งที่พยายามกรอก (จำกัดไม่เกิน 5 ครั้ง)
- `verified` (TINYINT(1), Default 0) - สถานะการใช้งานแล้ว
- `created_at` (TIMESTAMP, Default CURRENT_TIMESTAMP)

---

## 2. ดัชนีการค้นหา (Indexes & Keys)

- **Foreign Keys**:
  - `requests.type_id` -> `request_types.id` (RESTRICT)
  - `requests.applicant_id` -> `applicants.id` (CASCADE)
  - `requests.assigned_officer_id` -> `officers.id` (SET NULL)
  - `attachments.request_id` -> `requests.id` (CASCADE)
  - `status_history.request_id` -> `requests.id` (CASCADE)
  - `status_history.officer_id` -> `officers.id` (SET NULL)
  - `messages.request_id` -> `requests.id` (CASCADE)
- **Indexes**:
  - `requests.idx_status` (คอลัมน์ `status` เพื่อค้นหาเร็วในหน้า Dashboard)
  - `requests.idx_request_no` (คอลัมน์ `request_no` เพื่อค้นหาเลขคำขอ)
  - `otp_verifications.idx_email_otp` (คอลัมน์ `email` และ `otp_code` ร่วมกันเพื่อตรวจสอบรหัส)
