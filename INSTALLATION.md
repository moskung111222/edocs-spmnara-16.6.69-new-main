# คู่มือการติดตั้งระบบยื่นคำขอเอกสารออนไลน์ สพม.นราธิวาส (NWT Document Submission System)

คู่มือฉบับนี้อธิบายถึงขั้นตอนการตั้งค่าเพื่อพัฒนาและทดสอบระบบบนเครื่องคอมพิวเตอร์ส่วนบุคคล (Local Machine/localhost) เช่น การใช้งานโปรแกรม XAMPP, WampServer หรือ Laragon

---

## 1. สิ่งที่ต้องเตรียมการก่อนติดตั้ง (Prerequisites)
- **Web Server**: Apache 2.4+
- **PHP**: เวอร์ชัน 7.4 (หรือขึ้นไป)
  - จำเป็นต้องเปิดใช้งานส่วนขยาย (Extensions): `mysqli`, `fileinfo`, `zip`, `openssl`, `mbstring`
- **Database**: MySQL 5.7+ หรือ MariaDB 10.3+
- **Mail Server (SMTP)**: สำหรับทดสอบอีเมล OTP เช่น Mailtrap, Mailpit หรือบัญชี Gmail App Password

---

## 2. ขั้นตอนการเตรียมโฟลเดอร์โปรเจกต์
1. นำโฟลเดอร์โครงการไปวางไว้ในที่ตั้งของเซิร์ฟเวอร์จำลอง:
   - **XAMPP**: `C:\xampp\htdocs\edocs-spmnara\`
   - **Laragon**: `C:\laragon\www\edocs-spmnara\`
2. โครงสร้างพื้นฐานของโปรเจกต์:
   - โฟลเดอร์ที่เปิดเผยสาธารณะ (Web Root): `public_html/`
   - โฟลเดอร์เก็บเอกสารลับ (นอก Web Root): `private_storage/documents/` (ระบบจะสร้างให้อัตโนมัติในระดับเดียวกับ `public_html`)

---

## 3. ขั้นตอนการตั้งค่าระบบฐานข้อมูล
1. เปิดเครื่องมือบริหารจัดการฐานข้อมูล (เช่น phpMyAdmin หรือ HeidiSQL)
2. สร้างฐานข้อมูลใหม่ขึ้นมา โดยใช้รหัสอักขระ:
   - **Database Name**: `edocs_spmnara`
   - **Collation**: `utf8mb4_unicode_ci`
3. นำเข้าไฟล์ SQL โครงสร้างและข้อมูลเริ่มต้น:
   - นำเข้าไฟล์ [migration.sql](file:///c:/Users/mos/OneDrive/เอกสาร/edocs-spmnara/migration.sql) เข้าไปรันยังฐานข้อมูลที่สร้างขึ้น
   - สคริปต์นี้จะสถาปนาตารางข้อมูล 9 ตาราง และข้อมูลประเภทคำขอเริ่มต้น รวมถึงบัญชีเจ้าหน้าที่ระดับผู้ดูแลระบบ (Admin)

---

## 4. ขั้นตอนการตั้งค่าคอนฟิก (Configuration)
โปรดแก้ไขไฟล์การตั้งค่าดังนี้ตามความเหมาะสมของการทดสอบ:

### 4.1 เชื่อมต่อฐานข้อมูล
เปิดและแก้ไขข้อมูลในไฟล์ [database.php](file:///c:/Users/mos/OneDrive/เอกสาร/edocs-spmnara/public_html/app/config/database.php)
```php
$host = '127.0.0.1';
$user = 'root';
$pass = ''; 
$db   = 'edocs_spmnara';
$port = 3306;
```

### 4.2 เชื่อมต่อระบบส่งอีเมล (SMTP)
เปิดและแก้ไขข้อมูลในไฟล์ [mail.php](file:///c:/Users/mos/OneDrive/เอกสาร/edocs-spmnara/public_html/app/config/mail.php)
*หมายเหตุ: สามารถใช้บริการ Mailtrap.io เพื่อทำการดักจับอีเมลและรหัส OTP ในระหว่างการทดสอบในเครื่องจำลองได้ฟรี*
```php
'host'       => 'sandbox.smtp.mailtrap.io',
'port'       => 2525,
'username'   => 'YOUR_SMTP_USERNAME',
'password'   => 'YOUR_SMTP_PASSWORD',
'encryption' => 'tls',
```

### 4.3 เชื่อมต่อการตั้งค่าระบบหลัก
เปิดและแก้ไขข้อมูลในไฟล์ [config.php](file:///c:/Users/mos/OneDrive/เอกสาร/edocs-spmnara/public_html/app/config/config.php)
- ตรวจสอบ `SITE_URL` ให้ตรงกับพอร์ตและที่อยู่จริงที่เปิดเว็บเบราว์เซอร์ เช่น:
  ```php
  public const SITE_URL = 'http://localhost/edocs-spmnara/public_html';
  ```

---

## 5. เริ่มต้นเข้าใช้งานระบบ
1. **สำหรับประชาชน**:
   - เปิดหน้าแรกผ่าน URL: `http://localhost/edocs-spmnara/public_html/`
   - เลือกประเภทบริการที่ต้องการ เช่น *ขอใบแทนใบสุทธิ / ใบประกาศนียบัตร*
   - กรอกฟอร์มข้อมูล แนบไฟล์ PDF ตัวอย่าง และกดยื่นคำรับรหัส OTP
   - นำรหัส OTP ที่ดักได้จากเครื่องเซิร์ฟเวอร์ส่งเมลจำลอง มากรอกยืนยันคำรับบริการ
   
2. **สำหรับเจ้าหน้าที่ (Back Office)**:
   - เปิดหน้าจอเข้าใช้งานของเจ้าหน้าที่: `http://localhost/edocs-spmnara/public_html/admin/login`
   - **รหัสเจ้าหน้าที่เริ่มต้นในการทดสอบ**:
     - **สิทธิ์ผู้ดูแลระบบ (Admin)**: Username = `admin`, Password = `admin123`
     - **สิทธิ์ผู้อำนวยการกลุ่ม (Head)**: Username = `head`, Password = `admin123`
     - **สิทธิ์เจ้าหน้าที่ (Staff)**: Username = `staff`, Password = `admin123`
   - เจ้าหน้าที่สามารถจัดการเอกสารคิว มอบหมายงาน และเปลี่ยนสถานะคำขอต่างๆ ได้ทันที
