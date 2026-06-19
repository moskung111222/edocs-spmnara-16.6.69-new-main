# คู่มือติดตั้งบน Hosting จริง พร้อมแนะนำการเพิ่มประสิทธิภาพ

คู่มือฉบับนี้เขียนขึ้นจากข้อมูลจริงของเซิร์ฟเวอร์ DirectAdmin ที่จะใช้งาน โดยครอบคลุมตั้งแต่ขั้นตอนการนำโปรเจกต์ขึ้นโฮสติ้ง ไปจนถึงการปรับแต่งประสิทธิภาพ (Performance Tuning) เพื่อให้ระบบยื่นคำขอเอกสารออนไลน์ทำงานได้เสถียรและรวดเร็วที่สุด

---

## 1. ข้อมูลเซิร์ฟเวอร์ปัจจุบัน (Current Server Specifications)

| รายการ | ข้อมูลจริง |
| :--- | :--- |
| **Control Panel** | DirectAdmin 1.61.5 |
| **CPU** | 4x Intel Xeon L5639 @ 2.13GHz |
| **RAM** | 4 GB (Available ~2.43 GB) |
| **Swap** | ⚠️ ไม่มี (0 B) |
| **Web Server** | Nginx 1.19.6 (Reverse Proxy) + Apache 2.4.46 (Backend) |
| **Database** | MariaDB 10.4.17 |
| **PHP Versions** | 7.4.14 (หลัก), 7.3.26, 5.6.40, 5.3.29 |
| **Mail** | Exim 4.94 + Dovecot 2.3.13 |
| **FTP** | ProFTPd 1.3.7a |
| **DNS** | Named (BIND) 9.9.5 |
| **SpamAssassin** | ❌ ยังไม่เปิดใช้งาน |
| **Uptime** | 127 Days+ |
| **ฐานข้อมูลที่มี** | 4 DBs รวม ~20 MB (รวม `ssssonara_edocs`) |

---

## 2. ขั้นตอนการติดตั้ง (Step-by-Step Installation)

### 2.1 สร้างฐานข้อมูล MySQL

1. ล็อกอิน DirectAdmin → **MySQL Management** → **CREATE NEW DATABASE**
2. ระบุชื่อ:
   - Database Name: `edocs` (ระบบจะนำหน้าด้วย Username เช่น `ssssonara_edocs`)
   - Database User: `edocs_user` (จะกลายเป็น `ssssonara_edocs_user`)
   - Password: ใช้ตัวสร้างรหัสผ่านอัตโนมัติ (**บันทึกไว้**)
3. กดสร้าง → เข้า **phpMyAdmin**
4. เลือก Database ที่สร้าง → คลิก **Import** → เลือกไฟล์ `migration.sql` จากโปรเจกต์ → Execute
5. ตรวจสอบว่ามีตาราง 9 ตาราง: `applicants`, `request_types`, `officers`, `requests`, `attachments`, `status_history`, `messages`, `audit_logs`, `otp_verifications`

### 2.2 อัปโหลดไฟล์โปรเจกต์

**ผ่าน FTP (ProFTPd) หรือ DirectAdmin File Manager:**

```text
/home/ssssonara/
├── private_storage/           ← สร้างโฟลเดอร์นี้ใหม่
│   └── documents/             ← สร้างโฟลเดอร์นี้ใหม่
├── cron_backup.php            ← อัปโหลดมาจาก Root โปรเจกต์
│
└── domains/yourdomain.go.th/
    └── public_html/           ← อัปโหลดเนื้อหาทั้งหมดจาก public_html/
        ├── .env               ← คัดลอกจาก .env.example แล้วแก้ไข
        ├── .htaccess
        ├── index.php
        ├── api/
        ├── app/
        ├── assets/
        ├── controllers/
        ├── middleware/
        ├── models/
        ├── services/
        └── views/
```

### 2.3 ตั้งค่าสิทธิ์โฟลเดอร์ (Permissions)

```bash
# ผ่าน SSH หรือ DirectAdmin File Manager
chmod 700 /home/ssssonara/private_storage/
chmod 700 /home/ssssonara/private_storage/documents/
chmod 644 /home/ssssonara/domains/yourdomain.go.th/public_html/.env
chmod 644 /home/ssssonara/domains/yourdomain.go.th/public_html/.htaccess
```

> ⚠️ **สำคัญ**: `.env` ต้องเป็น 644 หรือ 600 เท่านั้น ห้ามตั้ง 777 โดยเด็ดขาด

### 2.4 ตั้งค่าไฟล์ `.env`

คัดลอก `.env.example` เป็น `.env` แล้วแก้ไขค่าให้ตรงกับเซิร์ฟเวอร์:

```ini
# Database - ใช้ข้อมูลจากขั้นตอน 2.1
DB_HOST=localhost
DB_USER=ssssonara_edocs_user
DB_PASS=รหัสผ่านที่สร้างไว้
DB_NAME=ssssonara_edocs
DB_PORT=3306

# Mail - ใช้ Exim ของเซิร์ฟเวอร์เอง หรือ SMTP ภายนอก
SMTP_HOST=localhost
SMTP_PORT=25
SMTP_USER=
SMTP_PASS=
SMTP_ENCRYPTION=
SMTP_FROM_EMAIL=no-reply@yourdomain.go.th
SMTP_FROM_NAME="สพม.นราธิวาส - ระบบยื่นคำขอเอกสารออนไลน์"

# Pusher Realtime
PUSHER_APP_ID=your_app_id
PUSHER_KEY=your_key
PUSHER_SECRET=your_secret
PUSHER_CLUSTER=ap1
```

### 2.5 แก้ไข Config ที่ Hardcode

แก้ไข `public_html/app/config/config.php`:
```php
// เปลี่ยน URL ให้ตรงกับโดเมนจริง
public const SITE_URL = 'https://yourdomain.go.th';

// เปลี่ยน Path ให้ตรงกับ Username จริง
return '/home/ssssonara/private_storage/documents/';
```

### 2.6 เลือก PHP Version ให้ถูกต้อง

เซิร์ฟเวอร์มี PHP 4 เวอร์ชัน → **ต้องเลือกใช้ PHP 7.4.14** (php1):

1. DirectAdmin → **Domain Setup** → เลือกโดเมน
2. เลือก **PHP Version** → ตั้งเป็น **PHP 7.4** (php1)
3. กดบันทึก

> ⚠️ **ห้ามใช้ PHP 5.6 หรือ 5.3** เนื่องจากโปรเจกต์ใช้ฟีเจอร์ของ PHP 7.x เช่น `random_bytes()`, `??` null coalescing, `const` arrays, และ return type declarations

### 2.7 ตั้ง Cron Job สำรองข้อมูลรายวัน

DirectAdmin → **Advanced Features** → **Cron Jobs** → **Create Cron Job**:

| Field | Value |
| :--- | :--- |
| Minute | `0` |
| Hour | `23` |
| Day | `*` |
| Month | `*` |
| Weekday | `*` |
| Command | `/usr/local/bin/php /home/ssssonara/cron_backup.php > /dev/null 2>&1` |

### 2.8 ทดสอบการทำงาน

1. เปิดเบราว์เซอร์ไปที่ `https://yourdomain.go.th/` → ต้องแสดงหน้าแรกระบบ
2. ทดสอบ `https://yourdomain.go.th/admin/login` → ต้องแสดงหน้าล็อกอิน
3. ล็อกอินด้วย `admin` / `admin123` (จาก Seed Data)
4. ทดสอบยื่นคำขอหน้าประชาชน

---

## 3. แนะนำการเพิ่มประสิทธิภาพ (Performance Optimization)

### 3.1 🔴 วิกฤต: ไม่มี Swap Memory

เซิร์ฟเวอร์มี RAM 4 GB แต่ **Swap = 0 B** → หาก RAM เต็ม ระบบจะ OOM Kill (ปิดกระบวนการทันที) โดยไม่มีบัฟเฟอร์

**แนะนำ:** ติดต่อผู้ดูแลเซิร์ฟเวอร์ (Root Admin) ให้สร้าง Swap File:
```bash
# สำหรับ Root Admin เท่านั้น
fallocate -l 2G /swapfile
chmod 600 /swapfile
mkswap /swapfile
swapon /swapfile
echo '/swapfile none swap sw 0 0' >> /etc/fstab
```

### 3.2 🟡 ปรับแต่ง Nginx + Apache (Reverse Proxy)

เซิร์ฟเวอร์ใช้สถาปัตยกรรม **Nginx (Front) → Apache (Backend)** ซึ่งดีอยู่แล้ว แต่สามารถปรับปรุงเพิ่มเติมได้:

#### 3.2.1 เปิด Gzip Compression บน Nginx
ติดต่อ Root Admin ให้ตรวจสอบการตั้งค่า `/etc/nginx/nginx.conf`:
```nginx
gzip on;
gzip_comp_level 5;
gzip_min_length 256;
gzip_types
    text/plain
    text/css
    text/javascript
    application/javascript
    application/json
    application/xml
    image/svg+xml;
gzip_vary on;
```

#### 3.2.2 เปิด Browser Caching สำหรับ Static Files
เพิ่มในไฟล์ `.htaccess` ของโปรเจกต์:
```apache
# Browser Caching for Static Assets
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType image/jpeg "access plus 1 month"
    ExpiresByType image/gif "access plus 1 month"
    ExpiresByType image/svg+xml "access plus 1 month"
    ExpiresByType application/font-woff2 "access plus 1 year"
</IfModule>
```

### 3.3 🟡 ปรับแต่ง MariaDB 10.4 สำหรับ RAM 4 GB

ติดต่อ Root Admin ให้ตรวจสอบ `/etc/my.cnf` หรือ `/etc/mysql/my.cnf`:

```ini
[mysqld]
# === Memory Allocation (สำหรับเครื่อง RAM 4 GB ที่ใช้ร่วมกับ Web Server) ===
innodb_buffer_pool_size = 512M       # ค่าเริ่มต้นมักเป็น 128M → เพิ่มเป็น 512M
innodb_log_file_size = 64M           # ช่วยลดการเขียนดิสก์
innodb_flush_log_at_trx_commit = 2   # เร็วขึ้น ยอมรับ risk เล็กน้อย
innodb_flush_method = O_DIRECT       # ลด double buffering

# === Query Cache (มีประโยชน์สำหรับเว็บที่อ่านข้อมูลเยอะ) ===
query_cache_type = 1
query_cache_size = 32M
query_cache_limit = 2M

# === Connection Tuning ===
max_connections = 100                 # ลดจากค่าเริ่มต้น 151 เพื่อประหยัด RAM
thread_cache_size = 16

# === Temporary Tables ===
tmp_table_size = 32M
max_heap_table_size = 32M

# === Slow Query Log (เปิดเพื่อวิเคราะห์ Query ที่ช้า) ===
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow.log
long_query_time = 2
```

### 3.4 🟡 ปรับแต่ง PHP 7.4 (php.ini)

DirectAdmin → **PHP Settings** (หรือแก้ไข php.ini ผ่าน CustomBuild):

```ini
; === ประสิทธิภาพ ===
opcache.enable = 1                    ; เปิด OPcache (สำคัญมาก!)
opcache.memory_consumption = 128      ; MB สำหรับแคชโค้ด PHP
opcache.interned_strings_buffer = 16
opcache.max_accelerated_files = 4000
opcache.revalidate_freq = 60         ; ตรวจไฟล์ใหม่ทุก 60 วินาที
opcache.fast_shutdown = 1

; === ขนาดอัปโหลด ===
upload_max_filesize = 10M            ; ตรงกับ Config::UPLOAD_MAX_SIZE
post_max_size = 12M                  ; ต้องมากกว่า upload_max_filesize
max_file_uploads = 10

; === เซสชัน ===
session.gc_maxlifetime = 3600        ; Session หมดอายุ 1 ชั่วโมง
session.cookie_httponly = 1          ; ป้องกัน JS อ่าน Cookie
session.cookie_samesite = Lax        ; ป้องกัน CSRF

; === ความปลอดภัย ===
expose_php = Off                     ; ซ่อนเวอร์ชัน PHP จาก HTTP Headers
display_errors = Off                 ; ปิดแสดง Error บน Production
log_errors = On
error_log = /home/ssssonara/logs/php_error.log

; === ประสิทธิภาพหน่วยความจำ ===
memory_limit = 128M                  ; เพียงพอสำหรับแอปนี้
max_execution_time = 60
max_input_time = 60
```

> ⚠️ **OPcache เป็นการปรับแต่งที่สำคัญที่สุด** สำหรับ PHP — สามารถเพิ่มความเร็วได้ 2-5 เท่า โดยแคชโค้ด PHP ที่แปลงแล้วไว้ในหน่วยความจำ ไม่ต้องคอมไพล์ซ้ำทุกคำขอ

### 3.5 🟢 ปรับแต่งระดับแอปพลิเคชัน (.htaccess)

เพิ่มเติมใน `.htaccess` ที่มีอยู่เดิม:

```apache
# === Security Headers ===
<IfModule mod_headers.c>
    # ป้องกัน Clickjacking
    Header always set X-Frame-Options "SAMEORIGIN"
    # ป้องกัน MIME sniffing
    Header always set X-Content-Type-Options "nosniff"
    # ป้องกัน XSS (เบราว์เซอร์รุ่นเก่า)
    Header always set X-XSS-Protection "1; mode=block"
    # ซ่อน Server Software
    Header always unset X-Powered-By
    # Referrer Policy
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>

# === ป้องกันการเข้าถึง .env จากภายนอก ===
<FilesMatch "^\.env">
    Order allow,deny
    Deny from all
</FilesMatch>

# === ป้องกันการเข้าถึง .git ===
<DirectoryMatch "^/.*/\.git">
    Order allow,deny
    Deny from all
</DirectoryMatch>
```

### 3.6 🟢 เปิด SpamAssassin

จากภาพพบว่า SpamAssassin **ยังไม่เปิดใช้งาน** — หากเซิร์ฟเวอร์จะใช้ Exim SMTP ในการส่งอีเมล OTP ให้ประชาชน ควรเปิด SpamAssassin เพื่อ:
- ป้องกันอีเมลขาเข้าที่เป็น Spam
- เพิ่มความน่าเชื่อถือของ Mail Server (SPF/DKIM alignment)

**วิธีเปิด:** DirectAdmin → **Extra Features** → **SpamAssassin Setup** → กดปุ่ม **ENABLE SPAMASSASSIN**

### 3.7 🟢 ตั้งค่า HTTPS (SSL Certificate)

1. DirectAdmin → **SSL Certificates** → เลือกโดเมน
2. เลือก **Let's Encrypt** → กด Issue Certificate
3. ตรวจสอบว่า Force HTTPS Redirect เปิดอยู่

เมื่อเปิด HTTPS แล้ว เพิ่ม Redirect ใน `.htaccess`:
```apache
# Force HTTPS
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

---

## 4. ตารางสรุปการปรับแต่งตามลำดับความสำคัญ

| ลำดับ | รายการ | ผลลัพธ์ที่ได้ | ระดับ | ใครทำ |
| :---: | :--- | :--- | :---: | :--- |
| 1 | เปิด **OPcache** | เร็วขึ้น 2-5x | 🔴 | PHP Settings |
| 2 | เพิ่ม **Swap 2GB** | ป้องกัน OOM Kill | 🔴 | Root Admin |
| 3 | ปรับ **innodb_buffer_pool** | Query เร็วขึ้น 30-50% | 🟡 | Root Admin |
| 4 | เปิด **Gzip** บน Nginx | ลดขนาดหน้าเว็บ 60-70% | 🟡 | Root Admin |
| 5 | เพิ่ม **Browser Caching** | ลดโหลดซ้ำ Static Files | 🟡 | .htaccess |
| 6 | เพิ่ม **Security Headers** | ป้องกัน XSS, Clickjacking | 🟢 | .htaccess |
| 7 | ป้องกัน **.env** จากเว็บ | ปิดรูรั่วข้อมูลสำคัญ | 🟢 | .htaccess |
| 8 | เปิด **HTTPS** | เข้ารหัสข้อมูลทุกชั้น | 🟢 | DirectAdmin |
| 9 | เปิด **SpamAssassin** | ป้องกัน Spam Mail | 🟢 | DirectAdmin |
| 10 | เปิด **Slow Query Log** | วิเคราะห์ Query ที่ช้า | 🟢 | Root Admin |

---

## 5. การตรวจสอบหลังติดตั้ง (Post-Installation Checklist)

```text
[ ] ฐานข้อมูลนำเข้าครบ 9 ตาราง + Seed Data
[ ] .env ตั้งค่าครบ (DB, SMTP, Pusher)
[ ] SITE_URL ใน config.php ตรงกับโดเมนจริง
[ ] Private Storage Path ตรงกับ Username จริง
[ ] PHP Version ตั้งเป็น 7.4
[ ] OPcache เปิดใช้งาน
[ ] SSL/HTTPS ใช้งานได้
[ ] หน้าแรก (/) โหลดได้
[ ] หน้าล็อกอิน (/admin/login) โหลดได้
[ ] ล็อกอินด้วย admin/admin123 สำเร็จ
[ ] แดชบอร์ด (/admin/dashboard) แสดงข้อมูลได้
[ ] ยื่นคำขอทดสอบ + อัปโหลด PDF ได้
[ ] Cron Job ตั้งเวลาสำรองข้อมูลแล้ว
[ ] .env ไม่สามารถเข้าถึงผ่าน URL ได้
[ ] เปลี่ยนรหัสผ่าน admin/head/staff จากค่าเริ่มต้น
```

> ⚠️ **อย่าลืม:** หลังติดตั้งเสร็จ ให้เปลี่ยนรหัสผ่านของบัญชีเจ้าหน้าที่ทุกบัญชีทันที เนื่องจาก Seed Data ใน `migration.sql` ใช้รหัสผ่านทดสอบ (`admin123`, `head123`, `staff123`)
