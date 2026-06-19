# Route Map

ตารางแสดงเส้นทางการเรียกเข้าใช้งานหน้าเว็บ (Routing) ซึ่งประกาศอยู่ภายในไฟล์ [index.php](file:///e:/edocs-spmnara%2016.6.69/public_html/index.php) โดยแบ่งสัดส่วนการเข้าใช้งาน ดังนี้:

---

## 1. เส้นทางสำหรับประชาชน (Public Routes)

| เส้นทาง (Route) | คำสั่งประมวลผล (Controller & Action) | ฟังก์ชันการทำงาน |
| :--- | :--- | :--- |
| `""` หรือ `/` | `\App\Controllers\RequestController::index()` | หน้าแรกบริการของประชาชน เลือกประเภทคำขอ |
| `request/create` | `\App\Controllers\RequestController::create()` | หน้าจอแบบฟอร์มการกรอกข้อมูลและแนบไฟล์ |
| `request/verify` | `\App\Controllers\RequestController::verify()` | หน้าจอกรอกยืนยันรหัสผ่านใช้ครั้งเดียว (OTP) |
| `request/track` | `\App\Controllers\RequestController::track()` | หน้าจอค้นหาใบคำขอ แชทโต้ตอบ และส่งไฟล์เพิ่ม |
| `download` | `\App\Controllers\RequestController::download()` | ประตูควบคุมสิทธิ์การดาวน์โหลดเอกสาร PDF |

---

## 2. เส้นทางสำหรับเจ้าหน้าที่ (Administrative Routes)

| เส้นทาง (Route) | คำสั่งประมวลผล (Controller & Action) | ฟังก์ชันการทำงาน |
| :--- | :--- | :--- |
| `admin/login` | `\App\Controllers\AuthController::login()` | หน้าจอล็อกอินเจ้าหน้าที่ด้วย Username & Password |
| `admin/logout` | `\App\Controllers\AuthController::logout()` | ออกจากระบบ เคลียร์คีย์เซสชันทั้งหมด |
| `admin/dashboard` | `\App\Controllers\AdminController::dashboard()` | หน้าจอประเมินภาพรวม ตารางคิวงาน และข้อมูลรายงานสถิติ |
| `admin/request` | `\App\Controllers\AdminController::requestDetail()` | หน้าจัดการคำขอ ตรวจไฟล์เอกสาร มอบหมายงาน แชท และเปลี่ยนสถานะ |

---

## 3. เส้นทางฝั่งผู้ให้บริการระบบ (API Routes)

| เส้นทาง (Route) | ไฟล์สคริปต์ที่เรียกทำงาน (API Script) | ฟังก์ชันการทำงาน |
| :--- | :--- | :--- |
| `api/requests` | `public_html/api/requests.php` | คิวรี่รายการคำขอทั้งหมดแยกตาม filter (JSON) |
| `api/upload` | `public_html/api/upload.php` | หน้าต่างส่งข้อมูลและรับอัปโหลดเอกสาร PDF เพิ่มเติม |
| `api/status` | `public_html/api/status.php` | คืนค่าหรือบันทึกปรับเปลี่ยนสถานะของคำขอ |

---

## 4. กรณีเกิดข้อผิดพลาด (Fallback Route)
หากผู้ใช้เรียกหน้าเว็บที่ไม่มีอยู่ในระบบ (เช่น `/does-not-exist`) ระบบจะส่งผู้ใช้งานไปยังหน้าจอข้อผิดพลาด 404:
- **Fallback URL**: `views/public/404.php` (คืนค่าสถานะ HTTP 404 Not Found)
- **ระบบทำงานผิดพลาด**: ส่งหน้าต่างข้อผิดพลาด HTTP 500 (Unhandled Application Exception)
