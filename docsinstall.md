# คู่มือการติดตั้งระบบยื่นคำขอจัดตั้งและรายงานบ้านเรียน (Installation & Hosting Guide)

คู่มือฉบับนี้อธิบายรายละเอียดการติดตั้งระบบบริหารจัดการบ้านเรียนออนไลน์ (NWT Homeschool Management System) ทั้งบนเครื่องจำลองส่วนบุคคล (**Localhost**) และบนโฮสติ้งจริง (**Live Production Host / DirectAdmin**) 

---

## 1. ข้อมูลสเปกของระบบที่แนะนำ (System Requirements)

* **Web Server:** Apache 2.4+ หรือ Nginx 1.19+
* **PHP Version:** **PHP 7.4.x** (รองรับ 7.3.x ขึ้นไป แต่แนะนำ 7.4.x ไม่แนะนำ PHP 8.0+ ในขณะนี้เนื่องจากความเข้ากันได้ของไลบรารีบางตัว และห้ามใช้ต่ำกว่า 7.3.x)
  * ส่วนขยาย PHP ที่ต้องเปิด: `mysqli`, `fileinfo`, `zip`, `openssl`, `mbstring`
* **Database:** MySQL 8.0+ หรือ MariaDB 10.3+

---

## 2. การติดตั้งบนคอมพิวเตอร์ส่วนบุคคล (Localhost)

คู่มือนี้รองรับเครื่องจำลองที่ใช้ **AppServ**, **XAMPP**, **Laragon** หรือการเรียกใช้ **PHP Built-in Server** โดยตรง

### ขั้นตอนที่ 2.1: เตรียมไฟล์โปรเจกต์
คัดลอกโฟลเดอร์โปรเจกต์ไปวางไว้ในโฟลเดอร์ Web Root ของเครื่องจำลองของคุณ:
* **AppServ:** `C:\AppServ\www\home_school\`
* **XAMPP:** `C:\xampp\htdocs\home_school\`
* **Laragon:** `C:\laragon\www\home_school\`

### ขั้นตอนที่ 2.2: กำหนดโครงสร้างโฟลเดอร์ภายนอก Web Root
เพื่อความปลอดภัย ข้อมูลไฟล์แนบของประชาชนทั้งหมดจะถูกบันทึกไว้นอกโฟลเดอร์สาธารณะ (`public_html`) เพื่อป้องกันการเข้าถึงตรงผ่าน URL 
1. สร้างโฟลเดอร์ชื่อ `private_storage` ไว้ที่ระดับนอกสุดของโปรเจกต์ (คู่ขนานกับ `public_html`)
2. ด้านในให้สร้างโฟลเดอร์ย่อยชื่อ `documents` 
*โครงสร้างจะเป็นดังนี้:*
```text
C:\AppServ\www\home_school\
├── private_storage\
│   └── documents\      <-- พื้นที่เก็บเอกสารแนบส่วนตัว
└── public_html\        <-- เอกสารเว็บและหน้าเพจทั้งหมด
```

---

### ขั้นตอนที่ 2.3: ตั้งค่าฐานข้อมูล & ปลดล็อก MySQL 8.0+ (สำคัญมาก)

> [!WARNING]
> **สำหรับ MySQL 8.0+ (เช่น ใน AppServ รุ่นใหม่):** 
> ปัญหาที่พบบ่อยมากคือ PHP 7.3/7.4 แจ้งเตือนข้อผิดพลาด:
> `The server requested authentication method unknown to the client [caching_sha2_password]`
> เนื่องจาก MySQL 8.0+ ใช้ระบบปลั๊กอินตรวจสอบสิทธิ์แบบใหม่ซึ่ง PHP รุ่นเก่าไม่สนับสนุนโดยตรง วิธีแก้ไขและสร้างฐานข้อมูลใหม่ทำได้ดังนี้:

1. เปิดคอมมานด์ไลน์ (Command Prompt / PowerShell) หรือ phpMyAdmin
2. เข้าสู่ระบบ MySQL ในฐานะ `root` เพื่อดำเนินการสร้างฐานข้อมูลและปรับสิทธิ์ผู้ใช้:
```sql
-- 1. สร้างฐานข้อมูลใหม่
CREATE DATABASE IF NOT EXISTS `sesaonara_edocs` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 2. ปลดล็อกบัญชี root ให้เข้ากันได้กับ PHP 7.4 (เปลี่ยน 'รหัสผ่านroot' ให้ตรงกับรหัสผ่านจริงของคุณ)
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'รหัสผ่านroot';

-- 3. สร้างและกำหนดสิทธิ์ให้ผู้ใช้เฉพาะสำหรับระบบ โดยใช้การตรวจสอบสิทธิ์แบบดั้งเดิม (mysql_native_password)
CREATE USER IF NOT EXISTS 'sesaonara_edocs'@'localhost' IDENTIFIED WITH mysql_native_password BY 'Thi$i$spmnara15';
ALTER USER 'sesaonara_edocs'@'localhost' IDENTIFIED WITH mysql_native_password BY 'Thi$i$spmnara15';

-- 4. มอบสิทธิ์การใช้งานฐานข้อมูลทั้งหมดให้กับผู้ใช้รายนี้
GRANT ALL PRIVILEGES ON `sesaonara_edocs`.* TO 'sesaonara_edocs'@'localhost';
FLUSH PRIVILEGES;
```
3. นำเข้าข้อมูลระบบและข้อมูลเริ่มต้น (Seed Data):
   - เปิด phpMyAdmin เลือกฐานข้อมูล `sesaonara_edocs` → คลิกปุ่ม **Import** → เลือกไฟล์ [install.sql](file:///e:/สำรองระบบ 18.6.69/home_school/database/install.sql) จากโฟลเดอร์โปรเจกต์ → คลิกรันเพื่อนำเข้าโครงสร้างและข้อมูลเริ่มต้น

---

### ขั้นตอนที่ 2.4: คอนฟิกูเรชันไฟล์ `.env` บน Localhost
เปิดไฟล์ [public_html/.env](file:///e:/สำรองระบบ 18.6.69/home_school/public_html/.env) และแก้ไขข้อมูลเชื่อมต่อฐานข้อมูลตามสเต็ปข้างต้น:

```ini
# Database configuration สำหรับ localhost
DB_HOST=127.0.0.1
DB_USER=sesaonara_edocs
DB_PASS=
DB_NAME=sesaonara_edocs
DB_PORT=3306

# SMTP Mail configuration สำหรับทดสอบ (สามารถใช้ Mailtrap/Mailpit ในการดักรหัส OTP)
SMTP_HOST=localhost
SMTP_PORT=25
SMTP_USER=
SMTP_PASS=
SMTP_ENCRYPTION=
SMTP_FROM_EMAIL=no-reply@localhost
SMTP_FROM_NAME="ระบบยื่นเอกสารบ้านเรียน - Localhost"
```

### ขั้นตอนที่ 2.5: แก้ไข URL หลักในไฟล์ Config
แก้ไขในไฟล์ [public_html/app/config/config.php](file:///e:/สำรองระบบ 18.6.69/home_school/public_html/app/config/config.php):
```php
// เปลี่ยนค่าจากโดเมนจริง ให้ชี้มาที่ localhost:8080 หรือ path เว็บบนโฮสต์จำลองของคุณ
public const SITE_URL = 'http://localhost:8080';
```

### ขั้นตอนที่ 2.6: วิธีสั่งรันเว็บเพื่อทดสอบในเครื่องส่วนบุคคล
**วิธีรวดเร็วที่สุดโดยใช้ PHP CLI (ไม่ต้องใช้ Apache/XAMPP):**
1. เปิดคอมมานด์ไลน์แล้วนำทางเข้าไปยังโฟลเดอร์โครงการ:
   ```bash
   cd "e:\สำรองระบบ 18.6.69\home_school"
   ```
2. รันคำสั่งเปิดเซิร์ฟเวอร์จำลอง:
   ```bash
   php -S localhost:8080 -t public_html router.php
   ```
3. เปิดเว็บบราวเซอร์ของคุณแล้วเปิดเข้าไปทดสอบได้ทันที:
   * **หน้าหลักประชาชน:** `http://localhost:8080/`
   * **หน้าระบบติดตามสถานะคำขอ:** `http://localhost:8080/request/track`
   * **หน้าล็อกอินเจ้าหน้าที่:** `http://localhost:8080/admin/login`

---

## 3. การติดตั้งบนเซิร์ฟเวอร์จริง (Live Production Host / DirectAdmin)

สำหรับการนำขึ้นไปใช้งานจริงบนระบบเซิร์ฟเวอร์ DirectAdmin หรือ Control Panel อื่นๆ

### ขั้นตอนที่ 3.1: สร้างฐานข้อมูลและนำเข้าข้อมูล
1. ล็อกอินเข้าแผงควบคุม DirectAdmin → เข้าเมนู **MySQL Management** → คลิก **Create New Database**
2. กำหนดรายละเอียด (ระบบจะเพิ่ม Username ของโฮสติ้งเป็น Prefix อัตโนมัติ):
   * Database Name: `sesaonara_edocs`
   * Database User: `sesaonara_edocs`
   * Database Password: *ตั้งค่าให้ปลอดภัยและจัดเก็บค่านี้ไว้*
3. เข้าใช้งาน **phpMyAdmin** นำเข้าข้อมูลโดยคลิก **Import** เลือกไฟล์ [install.sql](file:///e:/สำรองระบบ 18.6.69/home_school/database/install.sql) จากนั้นกดยืนยันการนำเข้า

### ขั้นตอนที่ 3.2: การอัปโหลดโครงสร้างไฟล์บนโฮสติ้ง
การอัปโหลดต้องแบ่งไฟล์ให้อยู่ในตำแหน่งที่ปลอดภัยตามโครงสร้างดังนี้:

```text
/home/sesaonara/                              <-- โฟลเดอร์บ้านของผู้ใช้เซิร์ฟเวอร์ (นอก Public Directory)
├── private_storage/
│   └── documents/                            <-- (สร้างโฟลเดอร์นี้และกำหนดสิทธิ์เป็น chmod 700)
├── cron_backup.php                           <-- (อัปโหลดมาไว้ที่ระดับนี้ สำหรับใช้รัน Backup ประจำวัน)
│
└── domains/sesaonara.org/
    └── public_html/                          <-- (อัปโหลดเนื้อหาจากภายในโฟลเดอร์ public_html/ ของโปรเจกต์)
        ├── .env                              <-- คัดลอกและตั้งค่าคอนฟิกจริง
        ├── .htaccess                         <-- ไฟล์เราท์ติ้งและตั้งค่าความปลอดภัย
        ├── index.php
        ├── router.php
        ├── api/
        ├── app/
        ├── assets/
        ├── controllers/
        ├── middleware/
        ├── models/
        ├── services/
        └── views/
```

### ขั้นตอนที่ 3.3: การอนุญาตสิทธิ์โฟลเดอร์ (Directory Permissions)
ปรับแต่งผ่าน SSH Command Line หรือใช้งาน File Manager บน DirectAdmin เพื่อความปลอดภัยสูงสุด:
```bash
chmod 700 /home/sesaonara/private_storage/
chmod 700 /home/sesaonara/private_storage/documents/
chmod 600 /home/sesaonara/domains/sesaonara.org/public_html/.env
chmod 644 /home/sesaonara/domains/sesaonara.org/public_html/.htaccess
```

### ขั้นตอนที่ 3.4: ตั้งค่าไฟล์ `.env` สำหรับโปรดักชัน
ปรับปรุงคอนฟิกูเรชันไฟล์ `.env` ที่โฮสต์จริงให้ตรงกับสเปกและรหัสผ่านฐานข้อมูลและเมล์จริง:
```ini
DB_HOST=localhost
DB_USER=sesaonara_edocs
DB_PASS=รหัสผ่านฐานข้อมูลจริงของคุณบนโฮสติ้ง
DB_NAME=sesaonara_edocs
DB_PORT=3306

# การตั้งค่า SMTP สำหรับส่ง OTP จริงผ่านระบบ Exim ของโฮสต์
SMTP_HOST=localhost
SMTP_PORT=25
SMTP_USER=
SMTP_PASS=
SMTP_ENCRYPTION=
SMTP_FROM_EMAIL=no-reply@sesaonara.org
SMTP_FROM_NAME="สพม.นราธิวาส - ระบบยื่นคำขอเอกสารออนไลน์"

# ตัวอย่างการกำหนดเส้นทางพื้นที่เก็บข้อมูลลับแบบเจาะจง
PRIVATE_STORAGE_PATH=/home/sesaonara/private_storage/documents/
```

### ขั้นตอนที่ 3.5: คอนฟิก URL และโฟลเดอร์ใน `config.php`
แก้ไขไฟล์ [public_html/app/config/config.php](file:///e:/สำรองระบบ 18.6.69/home_school/public_html/app/config/config.php) บนเซิร์ฟเวอร์จริง:
```php
// โดเมนการใช้งานจริง
public const SITE_URL = 'https://sesaonara.org/edocs';
```

---

## 4. แนะนำการตั้งค่าความปลอดภัยและการจูนเซิร์ฟเวอร์ (Live Tuning)

> [!TIP]
> เพื่อความเสถียรและประสิทธิภาพสูงสุดบนเครื่องโปรดักชันจริง (เช่น RAM 4 GB) ควรเปิดฟีเจอร์เพิ่มประสิทธิภาพของ PHP และเซิร์ฟเวอร์ดังนี้:

### 4.1 เปิดใช้งาน OPcache บน PHP 7.4 (สำคัญที่สุด)
OPcache จะแคชโค้ด PHP ที่ผ่านการแปลแล้วไว้บนหน่วยความจำ ทำให้การโหลดและทำงานของแอปพลิเคชันรวดเร็วขึ้นประมาณ 2-5 เท่าตัวโดยไม่ต้องประมวลซ้ำทุก Request 
ตั้งค่าในส่วนควบคุม DirectAdmin หรือไฟล์ `php.ini`:
```ini
opcache.enable = 1
opcache.memory_consumption = 128
opcache.interned_strings_buffer = 16
opcache.max_accelerated_files = 4000
opcache.revalidate_freq = 60
```

### 4.2 การเข้ารหัส SSL/HTTPS
* ไปที่ DirectAdmin -> **SSL Certificates** -> เลือกเปิดใช้งาน **Let's Encrypt** และสั่ง Issue ใบรับรอง
* เปิดเปิดการตั้งค่า **Force SSL/HTTPS Redirect** เพื่อบังคับความปลอดภัยของข้อมูลประชาชน

### 4.3 การตั้งค่า Cron Job สำรองข้อมูลอัตโนมัติประจำวัน
ตั้งค่าใน DirectAdmin -> **Cron Jobs** -> **Create Cron Job** เพื่อสั่งรันการสำรองข้อมูลเป็นไฟล์ zip ในเวลาดึก (เช่น 23:00 น. ของทุกวัน):
```text
นาที: 0 | ชั่วโมง: 23 | วัน: * | เดือน: * | วันในสัปดาห์: *
คำสั่ง (Command):
/usr/local/bin/php /home/sesaonara/cron_backup.php > /dev/null 2>&1
```

---

## 5. บัญชีเริ่มต้นและข้อมูลการทดสอบระบบ (Default Test Accounts)

คุณสามารถทดสอบเข้าใช้งานห้องควบคุมผู้ดูแลและเจ้าหน้าที่โดยใช้รหัสผ่านทดสอบเริ่มต้นเหล่านี้ (กรุณาเปลี่ยนหลังการใช้งานจริงเพื่อความปลอดภัย):

* **ระดับผู้ดูแลระบบสูงสุด (Admin):** Username = `admin` | Password = `admin123`
* **ระดับหัวหน้ากลุ่มงาน (Head):** Username = `head` | Password = `admin123`
* **ระดับเจ้าหน้าที่ผู้ปฏิบัติการ (Staff):** Username = `staff` | Password = `admin123`
