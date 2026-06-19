# Deployment Guide (คู่มือการติดตั้งระบบ)

คู่มือฉบับนี้อธิบายถึงขั้นตอนการนำระบบยื่นคำขอเอกสารออนไลน์ ขึ้นติดตั้งและเปิดใช้งานจริงบน Production Server (Web Hosting) ที่ทำงานร่วมกับระบบควบคุม **DirectAdmin Panel**, **PHP 7.4** และฐานข้อมูล **MySQL** โดยมีการนำไฟล์ตั้งค่าสำคัญแยกเก็บไว้ในระบบ `.env` เพื่อเพิ่มความปลอดภัย

---

## 1. ความต้องการของระบบ (System Requirements)
* **Web Server:** Apache (พร้อมโมดูล `mod_rewrite` เปิดใช้งานสำหรับไฟล์ `.htaccess`)
* **PHP Version:** PHP 7.4 (แนะนำใช้ PHP 7.4 หรือ 8.0/8.1)
  * ส่วนเสริม PHP ที่จำเป็น: `mysqli`, `json`, `filter`, `openssl`, `session`, `date`
* **Database:** MySQL 5.7 ขึ้นไป หรือ MariaDB 10.3 ขึ้นไป
* **SMTP Server:** สำหรับใช้ส่งรหัส OTP ทางอีเมล
* **Pusher Channels Account:** สำหรับใช้ระบบ Realtime Chat Notification (หน้าแดชบอร์ดเจ้าหน้าที่)

---

## 2. การจัดโครงสร้างโฟลเดอร์สำหรับติดตั้งบน DirectAdmin
เพื่อป้องกันไม่ให้ผู้ใช้งานภายนอกสามารถเรียกใช้หรือแฮกเข้าถึงไฟล์สำคัญของระบบได้โดยตรง โครงสร้างของระบบจะถูกแยกออกเป็น 2 ส่วนหลัก:

```text
/home/USERNAME/
├── private_storage/                <-- โฟลเดอร์เก็บไฟล์ PDF เอกสารแนบของประชาชน (อยู่นอกพื้นที่เรียกเข้าเว็บ)
│   └── documents/                  <-- ตั้งสิทธิ์โฟลเดอร์เป็น 700 (Private)
│
├── cron_backup.php                 <-- ไฟล์ระบบสำรองข้อมูลอัตโนมัติประจำวัน
│
└── domains/
    └── yourdomain.go.th/
        └── public_html/            <-- โฟลเดอร์หน้าเว็บปกติที่เข้าผ่านอินเทอร์เน็ตได้
            ├── .env                <-- ไฟล์เก็บคอนฟิกูเรชันของระบบ (เช่น DB, Mail, Pusher)
            ├── index.php           <-- Front Controller และ Router
            ├── app/
            ├── controllers/
            ├── views/
            ├── api/
            └── assets/
```

### 2.1 โฟลเดอร์เก็บเอกสารลับ (`private_storage`)
* สร้างโฟลเดอร์ชื่อ `private_storage` และโฟลเดอร์ย่อย `documents` ไว้ที่โฮมไดเรกทอรีของผู้ใช้งาน (Home Directory) เช่น `/home/USERNAME/private_storage/documents/`
* ตั้งค่าสิทธิ์โฟลเดอร์ (Folder Permissions) เป็น **700 (rwx------)** หรือ **750 (rwxr-x---)** เพื่อไม่ให้บัญชีผู้ใช้งานโฮสติ้งรายอื่นในเครื่องเซิร์ฟเวอร์เดียวกันหรือผู้เข้าใช้ปกติเข้ามาส่องดูไฟล์เอกสารประชาชนได้

### 2.2 โฟลเดอร์แสดงผลบนอินเทอร์เน็ต (`public_html`)
* อัปโหลดไฟล์และโฟลเดอร์ทั้งหมดจากไดเรกทอรี `public_html/` ของโปรเจกต์ ขึ้นไปวางไว้ในไดเรกทอรี `/home/USERNAME/domains/yourdomain.go.th/public_html/`
* ไฟล์สำคัญ เช่น `.htaccess`, `.env`, และ `index.php` จะต้องอยู่ในราก (Root) ของ `public_html`

---

## 3. ขั้นตอนการตั้งค่าระบบฐานข้อมูล MySQL
1. เข้าใช้งาน **DirectAdmin Control Panel** ของท่าน
2. ไปที่เมนู **System Info & Files** -> **MySQL Management**
3. คลิก **Create New Database**
4. ระบุข้อมูลฐานข้อมูล:
   * **Database Name:** เช่น `spm_edocs` (ระบบจะนำหน้าด้วยชื่อผู้ใช้กลายเป็น `USERNAME_spm_edocs`)
   * **Database User:** เช่น `spm_user` (ระบบจะนำหน้าด้วยชื่อผู้ใช้กลายเป็น `USERNAME_spm_user`)
   * **Password:** ระบุรหัสผ่านที่มีความปลอดภัยสูง
5. กดปุ่ม **Create** เพื่อยืนยันการสร้าง
6. ไปที่เมนู **phpMyAdmin** ของเซิร์ฟเวอร์ ล็อกอินเข้าฐานข้อมูลตัวใหม่ที่สร้างขึ้น
7. ทำการนำเข้า (Import) ข้อมูลจากไฟล์ `migration.sql` ที่อยู่ในรูทของโครงการ

---

## 4. ขั้นตอนการตั้งค่าสภาพแวดล้อม (`.env`)
บนเซิร์ฟเวอร์ผ่าน File Manager ของ DirectAdmin ให้ไปคัดลอกไฟล์ `.env.example` ใน `public_html` และเปลี่ยนชื่อเป็น `.env` จากนั้นตั้งค่าคีย์ต่างๆ ดังนี้:

### 4.1 ตั้งค่าการเชื่อมต่อฐานข้อมูล (Database Connection)
```ini
DB_HOST=127.0.0.1
DB_USER=USERNAME_spm_user
DB_PASS=รหัสผ่านที่ตั้งในข้อ 3
DB_NAME=USERNAME_spm_edocs
DB_PORT=3306
```

### 4.2 ตั้งค่าการส่งอีเมล (SMTP Configuration)
อัปเดตระบบส่งเมลเพื่อให้สามารถส่ง OTP ยืนยันตัวตนได้
```ini
SMTP_HOST=smtp.yourmail.go.th
SMTP_PORT=587
SMTP_USER=no-reply@yourmail.go.th
SMTP_PASS=รหัสผ่านอีเมลระบบ
SMTP_ENCRYPTION=tls
SMTP_FROM_EMAIL=no-reply@yourmail.go.th
SMTP_FROM_NAME="สพม.นราธิวาส - ระบบยื่นคำขอเอกสารออนไลน์"
```

### 4.3 ตั้งค่าระบบ Realtime (Pusher Configuration)
```ini
PUSHER_APP_ID=1820463
PUSHER_KEY=7b203c94dfde5dbe96bc
PUSHER_SECRET=3e76a66699e34e56598c
PUSHER_CLUSTER=ap1
```

### 4.4 ตรวจเช็คค่าคงที่ใน `public_html/app/config/config.php`
* อัปเดต `SITE_URL` ให้เป็น Domain Name จริงของหน่วยงาน เช่น:
  ```php
  public const SITE_URL = 'https://yourdomain.go.th';
  ```
* ตรวจสอบความถูกต้องของเส้นทางการจัดเก็บไฟล์แบบลับในเครื่องเซิร์ฟเวอร์จริง:
  ```php
  public static function getPrivateStoragePath() {
      if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
          return dirname(__DIR__, 3) . '/private_storage/documents/';
      } else {
          // แทนที่ USERNAME ด้วยชื่อผู้ใช้ DirectAdmin ของท่าน
          return '/home/USERNAME/private_storage/documents/';
      }
  }
  ```

---

## 5. การตั้งค่าระบบสำรองข้อมูลอัตโนมัติ (Cron Jobs)
ระบบเตรียมไฟล์ `cron_backup.php` ไว้สำหรับการแบ็คอัพฐานข้อมูล MySQL และเอกสาร PDF ทั้งหมดที่ประชาชนส่งมาเป็นไฟล์ซิป (`.zip`) และจะบันทึกเก็บรักษาไว้ภายในเซิร์ฟเวอร์ โดยตั้งสิทธิ์ห้ามเรียกเข้าถึงผ่านหน้าเบราว์เซอร์

### ขั้นตอนการผูก Cron Jobs บน DirectAdmin
1. อัปโหลดไฟล์ `cron_backup.php` ไปวางไว้ภายนอกโฟลเดอร์หน้าเว็บที่ `/home/USERNAME/cron_backup.php`
2. ล็อกอิน DirectAdmin ไปที่เมนู **Advanced Features** -> คลิก **Cron Jobs**
3. คลิกปุ่ม **Create Cron Job**
4. ระบุข้อมูลคาบเวลาสำหรับส่งคำสั่งทำงาน (แนะนำทำงานวันละครั้งเวลา 23:00 น.)
   * **Minute:** `0`
   * **Hour:** `23`
   * **Day of Month:** `*`
   * **Month:** `*`
   * **Day of Week:** `*`
5. ระบุคำสั่งประมวลผล (Command):
   ```bash
   /usr/local/bin/php /home/USERNAME/cron_backup.php > /dev/null 2>&1
   ```
   *(หมายเหตุ: บน DirectAdmin บางโฮสติ้ง พาธของ PHP CLI อาจแตกต่างกัน เช่น `/usr/bin/php` หรือ `/usr/local/php74/bin/php` ท่านสามารถยืนยันตำแหน่งคำสั่ง PHP จากหน้าหลักของ DirectAdmin หรือติดต่อแอดมินผู้ดูแลระบบ)*
6. กดปุ่ม **Create** เพื่อเสร็จสิ้นขั้นตอนการตั้งระบบสำรองข้อมูลประจำวัน
