# Database Schema (โครงสร้างฐานข้อมูล)

โครงสร้างฐานข้อมูลของระบบประยุกต์สำหรับ Homeschool Management Module ประกอบด้วย 23 ตาราง ภายใต้ฐานข้อมูล MySQL/MariaDB โดยใช้ `utf8mb4_unicode_ci`

---

## 1. ตารางข้อมูลหลัก (Core Tables)

### 1.1 `applicants`
เก็บข้อมูลเบื้องต้นของประชาชนผู้ยื่นคำขอ
- `id` (INT, PK, AI)
- `full_name` (VARCHAR(255))
- `email` (VARCHAR(255), UNIQUE)
- `phone` (VARCHAR(20))
- `is_registered` (TINYINT(1))
- `password_hash` (VARCHAR(255), NULL)
- `created_at` (TIMESTAMP)

### 1.2 `applicant_accounts`
เก็บข้อมูลบัญชีและรหัสผ่านเข้าใช้งานแบบคงที่ของประชาชนแต่ละคน
- `id` (INT, PK, AI)
- `applicant_id` (INT, FK -> applicants.id)
- `applicant_code` (VARCHAR(50), UNIQUE) - e.g., HSU-2026-000123
- `password_hash` (VARCHAR(255))
- `password_plain` (VARCHAR(50)) - สำหรับให้เจ้าหน้าที่อ่านกรณีแจ้งข้อมูลผู้ใช้ลืมรหัสผ่าน
- `created_at` (TIMESTAMP)

### 1.3 `requests`
เก็บคำขอจัดตั้งและประเมินผลการเรียนรู้ของบ้านเรียน
- `id` (INT, PK, AI)
- `request_no` (VARCHAR(50), UNIQUE)
- `type_id` (INT, FK)
- `applicant_id` (INT, FK)
- `assigned_officer_id` (INT, FK, NULL)
- `status` (VARCHAR(50)) - สถานะภาพรวม
- `process_1_status` (VARCHAR(50)) - ขั้นขออนุมัติจัดตั้ง
- `process_2_status` (VARCHAR(50)) - ขั้นการวัดประเมินผลสัมฤทธิ์
- `form_data` (JSON)
- `created_at` (TIMESTAMP)
- `updated_at` (TIMESTAMP)

### 1.4 `request_attachments`
เก็บไฟล์แนบการส่งคำขอและไฟล์ตอบกลับทางเอกสารจากฝ่ายเจ้าหน้าที่
- `id` (INT, PK, AI)
- `request_id` (INT, FK)
- `file_name` (VARCHAR(255))
- `file_path` (VARCHAR(512))
- `mime_type` (VARCHAR(100))
- `file_size` (INT)
- `uploaded_by` (ENUM('applicant', 'officer'))
- `attachment_type` (VARCHAR(100)) - completed_form, supporting_document, official_letter, approval_document, notification_letter, other
- `created_at` (TIMESTAMP)

### 1.5 `staff_messages`
ข้อความหรือหมายเหตุเตือนจากเจ้าหน้าที่ปฏิบัติงาน
- `id` (INT, PK, AI)
- `request_id` (INT, FK)
- `officer_id` (INT, FK)
- `message` (TEXT)
- `created_at` (TIMESTAMP)

### 1.6 `meeting_results`
บันทึกประวัติการประชุมและตรวจประเมินแผนบ้านเรียนของคณะทำงาน
- `id` (INT, PK, AI)
- `request_id` (INT, FK)
- `meeting_date` (DATE)
- `result_summary` (TEXT)
- `file_name` (VARCHAR(255), NULL)
- `file_path` (VARCHAR(512), NULL)
- `mime_type` (VARCHAR(100), NULL)
- `file_size` (INT, NULL)
- `officer_id` (INT, FK)
- `created_at` (TIMESTAMP)

### 1.7 `workflow_history`
บันทึกขั้นตอนและกิจกรรมการปรับปรุงสถานะหรือรายละเอียดคำขอ
- `id` (INT, PK, AI)
- `request_id` (INT, FK)
- `action` (VARCHAR(100))
- `details` (TEXT)
- `officer_id` (INT, FK, NULL)
- `applicant_id` (INT, FK, NULL)
- `ip_address` (VARCHAR(45))
- `user_agent` (VARCHAR(255))
- `created_at` (TIMESTAMP)

---

## 2. ตารางประชาสัมพันธ์และงานเอกสาร (Announcements & Documents)

### 2.1 `announcements`
ข่าวประชาสัมพันธ์และประกาศแจ้งเตือนหน้าเว็บ
- `id` (INT, PK, AI)
- `title` (VARCHAR(255))
- `content` (TEXT)
- `type` (VARCHAR(50)) - announcement, public_notice, update
- `author_id` (INT, FK -> officers.id)
- `created_at` (TIMESTAMP)

### 2.2 `laws`
ไฟล์ระเบียบและแนวทางการจัดการศึกษาแบบครอบครัว
- `id` (INT, PK, AI)
- `title` (VARCHAR(255))
- `category` (VARCHAR(100))
- `file_name` (VARCHAR(255))
- `file_path` (VARCHAR(512))
- `file_size` (INT)
- `uploaded_by` (INT, FK)
- `created_at` (TIMESTAMP)

### 2.3 `download_documents`
แบบฟอร์มเปล่าและโครงสร้างเอกสารแม่แบบให้ดาวน์โหลด
- `id` (INT, PK, AI)
- `title` (VARCHAR(255))
- `category` (VARCHAR(100))
- `file_name` (VARCHAR(255))
- `file_path` (VARCHAR(512))
- `file_size` (INT)
- `uploaded_by` (INT, FK)
- `created_at` (TIMESTAMP)

### 2.4 `infographics`
รูปภาพอินโฟกราฟิกประชาสัมพันธ์
- `id` (INT, PK, AI)
- `title` (VARCHAR(255))
- `image_name` (VARCHAR(255))
- `image_path` (VARCHAR(512))
- `uploaded_by` (INT, FK)
- `created_at` (TIMESTAMP)
