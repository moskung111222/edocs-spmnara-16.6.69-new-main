# Changelog (บันทึกการแก้ไขและปรับปรุงระบบ)

บันทึกประวัติการพัฒนา ปรับปรุง และแก้ไขระบบยื่นคำขอเอกสารออนไลน์ สพม.นราธิวาส (NWT Document Submission System)

---

## [1.1.0] - 2026-06-16

### เพิ่มเติมฟังก์ชันการทำงานใหม่ (Added)
* **การรวมสถาปัตยกรรมตัวแปรสภาพแวดล้อม (Environmental Variables Integration):**
  * พัฒนาคลาส [Env.php](file:///e:/edocs-spmnara%2016.6.69/public_html/app/config/Env.php) สำหรับโหลดค่าคอนฟิกูเรชันต่างๆ จากไฟล์ภายนอก `.env`
  * อัปเดตไฟล์ [Database.php](file:///e:/edocs-spmnara%2016.6.69/public_html/app/config/database.php) และ [Mail.php](file:///e:/edocs-spmnara%2016.6.69/public_html/app/config/mail.php) ให้เรียกใช้ค่าผ่านสภาพแวดล้อมโดยตรงเพื่อความปลอดภัย
* **ระบบแจ้งเตือนแบบเรียลไทม์ (Real-time Event Broadcasting with Pusher):**
  * ผสานการทำงานของ Pusher Channels สำหรับการสื่อสารแบบเรียลไทม์ในฟังก์ชันห้องแชต
  * เจ้าหน้าที่และประชาชนจะได้รับการแจ้งเตือนข้อความใหม่และอัปเดตข้อมูลบนหน้าจอทันทีโดยไม่ต้องทำการรีเฟรชเบราว์เซอร์
* **เอกสารคูมือการทำงานระบบและสถาปัตยกรรม (System Documentation Suite under `/docs`):**
  * [PROJECT_OVERVIEW.md](file:///e:/edocs-spmnara%2016.6.69/docs/PROJECT_OVERVIEW.md) - สรุปวัตถุประสงค์ระบบและเทคโนโลยีหลัก
  * [SYSTEM_ARCHITECTURE.md](file:///e:/edocs-spmnara%2016.6.69/docs/SYSTEM_ARCHITECTURE.md) - แสดงสถาปัตยกรรมและเวิร์กโฟลว์ของระบบ
  * [DATABASE_SCHEMA.md](file:///e:/edocs-spmnara%2016.6.69/docs/DATABASE_SCHEMA.md) - ข้อมูลสเปกตาราง คีย์นอก และดัชนี
  * [ER_DIAGRAM.md](file:///e:/edocs-spmnara%2016.6.69/docs/ER_DIAGRAM.md) - ภาพโครงสร้างความสัมพันธ์แบบแผนภาพ Mermaid
  * [ROUTE_MAP.md](file:///e:/edocs-spmnara%2016.6.69/docs/ROUTE_MAP.md) - สรุปเส้นทางการส่งงานทั้งหมดในโครงการ
  * [API_DOCUMENTATION.md](file:///e:/edocs-spmnara%2016.6.69/docs/API_DOCUMENTATION.md) - อธิบายการเรียกใช้งาน Web API ทั้งสามตัว
  * [BUSINESS_RULES.md](file:///e:/edocs-spmnara%2016.6.69/docs/BUSINESS_RULES.md) - กฎเกณฑ์การเปลี่ยนสถานะและการประมวลผลระบบ
  * [USER_ROLES.md](file:///e:/edocs-spmnara%2016.6.69/docs/USER_ROLES.md) - แผนผังการอนุญาตเข้าถึงฟังก์ชันงานของแต่ละระดับสิทธิ์
  * [SECURITY_DESIGN.md](file:///e:/edocs-spmnara%2016.6.69/docs/SECURITY_DESIGN.md) - สรุปแนวทางความปลอดภัยด้าน CSRF, XSS, Session และ Uploads
  * [DEPLOYMENT_GUIDE.md](file:///e:/edocs-spmnara%2016.6.69/docs/DEPLOYMENT_GUIDE.md) - คู่มือติดตั้งระบบบน DirectAdmin โฮสติ้งจริง
  * [FOLDER_STRUCTURE.md](file:///e:/edocs-spmnara%2016.6.69/docs/FOLDER_STRUCTURE.md) - อธิบายโครงสร้างและความรับผิดชอบของแต่ละโฟลเดอร์ในโครงการ
  * [FEATURES_STATUS.md](file:///e:/edocs-spmnara%2016.6.69/docs/FEATURES_STATUS.md) - สรุปสถานะการพัฒนาฟังก์ชันการทำงานปัจจุบัน
  * [TODO_LIST.md](file:///e:/edocs-spmnara%2016.6.69/docs/TODO_LIST.md) - แผนงานพัฒนาต่อยอดที่ยังคงเหลือในอนาคต

### การปรับปรุงความปลอดภัย (Security Enhancements)
* ย้ายการเก็บรหัสผ่านฐานข้อมูล MySQL และบัญชีส่งเมล SMTP ออกจากโค้ดที่เป็นตัวประมวลผลหลัก (Hardcoded Credentials Removal) ไปอยู่ใน `.env` ที่ปลอดภัย
* ตั้งค่าคุกกี้เซสชันของระบบให้มีสิทธิ์คุ้มครองระดับสูง (เพิ่มคุณลักษณะ HttpOnly และ SameSite ในระดับ Lax)
* ป้องกันภัยคุกคามประเภท CSRF บน API ทุกส่วนด้วยการบังคับผ่านด่านตรวจสอบ CsrfMiddleware ก่อนทำงาน

### การปรับแต่ง UI/UX (UI Improvements)
* ปรับแต่งหน้าแดชบอร์ดเจ้าหน้าที่และรายงานภาพรวมด้วย CSS ให้รองรับมุมมองหน้าจอแบบ Responsive (Mobile View) เพื่อให้ตารางข้อมูลไม่ล้นหน้าจอ (Table Overflow fixes)
* อัปเกรดความพรีเมียมของ UI บนหน้าเข้าสู่ระบบและแดชบอร์ดเป็นธีมสีเข้ม (Dark Mode Layout) และเพิ่มลูกเล่น Visual Micro-animations บนส่วนควบคุมปุ่มตัวกรองคำขอ

---

## [1.0.0] - 2026-06-15

### เริ่มต้นเปิดโครงการ (Initial Release)
* โครงสร้างเว็บแอปพลิเคชันแบบ MVC เผยแพร่ผ่านตัวเชื่อมต่อ `index.php`
* หน้าสร้างคำขอ คัดเลือกประเภท จัดเก็บไฟล์ PDF ข้อมูลตาราง 9 ตารางระบบ
* ระบบจัดการ OTP ผ่าน SMTP และหน้าจอส่งข้อมูลเข้าคิวงานของเจ้าหน้าที่
* ระบบสำรองข้อมูลประจำวันอัตโนมัติ (สคริปต์ `cron_backup.php` แบบแบ็กกราว)
