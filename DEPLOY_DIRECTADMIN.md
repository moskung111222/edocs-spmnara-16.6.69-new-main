# คู่มือการติดตั้งระบบยื่นคำขอเอกสารออนไลน์บน DirectAdmin Hosting (Production Deployment)

คู่มือฉบับนี้อธิบายถึงขั้นตอนการนำระบบยื่นคำขอเอกสารออนไลน์ ขึ้นติดตั้งและเปิดใช้งานจริงบนเซิร์ฟเวอร์ปลายทางที่ควบคุมด้วยระบบ DirectAdmin Control Panel

---

## 1. การจัดสรรแผนผังไฟล์บน Hosting (File System Structure)
เพื่อความปลอดภัยระดับสูงสุดของข้อมูลประชาชน ไฟล์ทั้งหมดจะต้องไม่เก็บไว้ในที่ที่สามารถเรียกใช้งานผ่านเบราว์เซอร์ได้โดยตรง:

1. **โฟลเดอร์สำหรับเก็บไฟล์หลักฐาน PDF (ลับ)**:
   - นำโฟลเดอร์ `private_storage` ไปสร้างไว้ภายนอก `public_html` ในไดเรกทอรีบ้านหลัก (Home Directory) ของผู้ใช้
   - ตัวอย่างไดเรกทอรี: `/home/USERNAME/private_storage/documents/`
   
2. **ไฟล์โครงการทั้งหมด (หน้าบ้านและโปรแกรมควบคุม)**:
   - อัปโหลดเนื้อหาทั้งหมดภายในโฟลเดอร์ `public_html/` (รวมถึง `.htaccess`, `index.php`, `app/`, `controllers/`, `views/`, `api/`, `assets/`) ไปไว้ในไดเรกทอรี `public_html/` ของโดเมนเป้าหมาย
   - ตัวอย่างไดเรกทอรี: `/home/USERNAME/domains/domain.com/public_html/`

3. **ไฟล์สำรองข้อมูล (cron_backup.php)**:
   - อัปโหลดไฟล์ `cron_backup.php` ไปวางไว้ระดับเดียวกับ `domains` หรือระดับไดเรกทอรีบ้านหลัก เพื่อไม่ให้บุคคลภายนอกเรียกสั่งงานแบ็คอัพได้ผ่านเว็บ
   - ตัวอย่างไดเรกทอรี: `/home/USERNAME/cron_backup.php`

---

## 2. ขั้นตอนการตั้งค่าในหน้า DirectAdmin Panel

### 2.1 สร้างฐานข้อมูล MySQL
1. ลงชื่อเข้าใช้งานระบบ DirectAdmin ของโฮสติ้งของท่าน
2. ไปที่เมนู **MySQL Management** -> คลิก **Create New Database**
3. ระบุชื่อฐานข้อมูลและชื่อผู้ใช้งาน (เช่น `spm_db`, `spm_user`) จากนั้นสุ่มรหัสผ่านที่ปลอดภัยสูง (บันทึกรหัสผ่านนี้ไว้)
4. กดสร้างฐานข้อมูล จากนั้นเข้าระบบ **phpMyAdmin** นำเข้าไฟล์ `migration.sql`

### 2.2 ตั้งค่าการเข้าสิทธิ์ของไดเรกทอรี (Permissions & Security)
1. เปิด **File Manager** บน DirectAdmin
2. ค้นหาโฟลเดอร์ `/home/USERNAME/private_storage/`
3. ตั้งค่าการอนุญาตเข้าถึงสิทธิ์ (Permissions) เป็น **700 (rwx------)** หรือ **750 (rwxr-x---)** เพื่อไม่ให้ผู้ใช้งานโฮสติ้งรายอื่นในเครื่องเดียวกันสามารถดึงข้อมูลเอกสารประชาชนไปได้
4. คอนฟิกไฟล์ใน `public_html/` ให้เป็นสิทธิ์ **755** สำหรับโฟลเดอร์ และ **644** สำหรับไฟล์ปกติ

---

## 3. การแก้ไขการตั้งค่าโครงการ (Configuration Update)
ผ่านตัวแก้ไขไฟล์ของ DirectAdmin หรือ FTP ให้ไปแก้ไขไฟล์ตั้งค่าการใช้งานจริงดังนี้:

### 3.1 แก้ไข `app/config/config.php`
- เปลี่ยนบัญชีผู้ใช้ในเส้นทางจัดเก็บข้อมูลจริง:
  ```php
  public static function getPrivateStoragePath() {
      // แทนค่า USERNAME ด้วยชื่อผู้ใช้ DirectAdmin จริงของท่าน
      return '/home/USERNAME/private_storage/documents/';
  }
  ```
- อัปเดตที่ตั้ง URL โดเนมจริง:
  ```php
  public const SITE_URL = 'https://www.yourdomain.go.th';
  ```

### 3.2 แก้ไข `app/config/database.php`
- อัปเดตพารามิเตอร์การเชื่อมโยงข้อมูลจริงที่สร้างขึ้นใน DirectAdmin:
  ```php
  $host = 'localhost';
  $user = 'USERNAME_spm_user';
  $pass = 'PASSWORD_CREATED';
  $db   = 'USERNAME_spm_db';
  $port = 3306;
  ```

### 3.3 แก้ไข `app/config/mail.php`
- อัปเดต SMTP ของระบบอีเมลหน่วยงานของ สพม.นราธิวาส หรือผู้ให้บริการอีเมลจริง เพื่อให้แน่ใจว่าอีเมลและ OTP จะไม่ตกหล่นไปอยู่ในกล่องสแปม:
  ```php
  'host'       => 'smtp.yourmail.go.th',
  'port'       => 587,
  'username'   => 'no-reply@yourmail.go.th',
  'password'   => 'MailPassword',
  'encryption' => 'tls',
  ```

---

## 4. ตั้งค่าระบบสำรองข้อมูลอัตโนมัติ (Cron Jobs Setup)
ตั้งเวลาทำงานของ `cron_backup.php` เพื่อสั่งสำรองข้อมูลรายวันในเวลา 23:00 น.
1. ไปที่เมนู **Advanced Features** ในหน้า DirectAdmin -> เลือก **Cron Jobs**
2. คลิก **Create Cron Job**
3. ตั้งค่าคาบเวลาการทำงานดังนี้:
   - **Minute**: `0`
   - **Hour**: `23`
   - **Day of Month**: `*`
   - **Month**: `*`
   - **Day of Week**: `*`
4. ระบุคำสั่งที่จะประมวลผล (Command):
   ```bash
   /usr/local/bin/php /home/USERNAME/cron_backup.php > /dev/null 2>&1
   ```
   *(หมายเหตุ: เส้นทางพาธ `/usr/local/bin/php` อาจจะแปรผันตามผู้ให้บริการโฮสติ้ง สามารถตรวจสอบพาธ PHP CLI บนหน้าหลักของ DirectAdmin ได้)*
5. กดปุ่ม **Create** เพื่อยืนยันการตั้งระบบแบ็คอัพอัตโนมัติรายวัน
