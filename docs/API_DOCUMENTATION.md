# API Documentation

ระบบนี้ให้บริการ API ในรูปแบบ JSON สำหรับใช้ในการดึงข้อมูลสถานะ ตรวจรับคิวเอกสาร หรืออัปโหลดไฟล์หลักฐานเพิ่มเติม

---

## 1. ดึงรายการคำขอทั้งหมด (GET /api/requests)
สำหรับเจ้าหน้าที่ใช้ดึงคิวข้อมูลเพื่ออัปเดต Dashboard
- **Method**: `GET`
- **Authentication**: จำเป็นต้องใช้สิทธิ์เจ้าหน้าที่ (Officer Session)
- **Request Parameters**:
  - `status` (String, Optional) - กรองสถานะ เช่น `submitted`, `received`
  - `officer_id` (Integer, Optional) - กรองไอดีเจ้าหน้าที่เจ้าของสำนวน
  - `search` (String, Optional) - ค้นหาเลขคำขอ ชื่อประชาชน หรืออีเมล
- **Response (200 OK)**:
  ```json
  {
    "status": "success",
    "count": 1,
    "data": [
      {
        "id": 1,
        "request_no": "NWT-HS-2569-000001",
        "type_id": 1,
        "applicant_id": 1,
        "assigned_officer_id": null,
        "status": "submitted",
        "form_data": "{\"school_name\":\"โรงเรียนนราสิขาลัย\",\"grad_year\":\"2560\",\"purpose\":\"ศึกษาต่อ\"}",
        "created_at": "2026-06-16 10:24:00",
        "updated_at": "2026-06-16 10:24:00",
        "type_name": "ขอใบแทนใบสุทธิ / ใบประกาศนียบัตร",
        "applicant_name": "นายรักดี เอนกภพ",
        "officer_name": null
      }
    ]
  }
  ```
- **Response (400 Bad Request / Unauthenticated)**:
  ```json
  {
    "status": "error",
    "message": "กรุณาเข้าสู่ระบบก่อนใช้งาน"
  }
  ```

---

## 2. ตรวจสอบสถานะเอกสาร (GET /api/status)
สำหรับประชาชนหรือเจ้าหน้าที่ใช้ติดตามสถานะผ่านหมายเลขคำขอ
- **Method**: `GET`
- **Authentication**: เจ้าหน้าที่ หรือ ประชาชนที่เข้าสู่ระบบยืนยันตัวตน OTP ของหมายเลขคำขอแล้ว
- **Request Parameters**:
  - `no` (String, Required) - เลขที่คำขอ เช่น `NWT-HS-2569-000001`
- **Response (200 OK)**:
  ```json
  {
    "status": "success",
    "data": {
      "request_no": "NWT-HS-2569-000001",
      "status_code": "submitted",
      "status_name": "ยื่นคำขอแล้ว",
      "updated_at": "2026-06-16 10:24:00"
    }
  }
  ```

---

## 3. ปรับปรุงสถานะคำขอ (POST /api/status)
สำหรับเจ้าหน้าที่ใช้อัปเดตสถานะของเอกสารคำขอ
- **Method**: `POST`
- **Authentication**: สิทธิ์เจ้าหน้าที่เท่านั้น (Officer Session) + ต้องแนบ CSRF Token
- **Request Body (form-data)**:
  - `request_id` (Integer, Required) - รหัสไอดีของคำขอ
  - `status` (String, Required) - สถานะใหม่
  - `reason` (String, Optional) - คำชี้แจง (บังคับสำหรับ `need_info` และ `rejected`)
  - `csrf_token` (String, Required) - รหัส token ตรวจสอบ
- **Response (200 OK)**:
  ```json
  {
    "status": "success",
    "message": "ปรับปรุงสถานะเรียบร้อยแล้ว",
    "data": {
      "status_code": "received",
      "status_name": "รับเรื่องแล้ว"
    }
  }
  ```

---

## 4. อัปโหลดไฟล์หลักฐานเพิ่มเติม (POST /api/upload)
สำหรับประชาชนใช้อัปโหลดไฟล์เพิ่มเติมเมื่อเจ้าหน้าที่ขอข้อมูลเพิ่ม หรือเจ้าหน้าที่อัปโหลดเอกสารประกอบเพิ่มเติม
- **Method**: `POST`
- **Authentication**: เจ้าหน้าที่ หรือ ประชาชนที่ได้รับอนุมัติ OTP ของใบคำขอตัวนี้
- **Request Body (multipart/form-data)**:
  - `request_id` (Integer, Required) - รหัสไอดีคำขอ
  - `file` (File, Required) - ไฟล์หลักฐาน PDF (ขนาดไม่เกิน 10MB)
  - `csrf_token` (String, Required) - รหัส token ตรวจสอบ
- **Response (200 OK)**:
  ```json
  {
    "status": "success",
    "message": "อัปโหลดไฟล์สำเร็จ",
    "data": {
      "id": 12,
      "file_name": "transcript_new.pdf",
      "version": 2,
      "file_size": 2048576
    }
  }
  ```
