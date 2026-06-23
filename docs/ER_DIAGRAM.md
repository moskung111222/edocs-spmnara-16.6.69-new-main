# Entity Relationship Diagram (ER Diagram)

ความสัมพันธ์เชิงความหมายระหว่างตารางข้อมูลต่าง ๆ ในระบบ Homeschool Management Module

```mermaid
erDiagram
    applicants ||--o{ requests : "ยื่นคำขอ"
    request_types ||--o{ requests : "กำหนดประเภท"
    officers ||--o{ requests : "รับผิดชอบตรวจ"
    requests ||--o{ request_attachments : "แนบเอกสารหลักฐานและการตอบกลับ"
    requests ||--o{ staff_messages : "มีบันทึกเจ้าหน้าที่"
    requests ||--o{ meeting_results : "มีบันทึกประชุมคณะทำงาน"
    requests ||--o{ workflow_history : "บันทึกประวัติการปรับปรุงสถานะ"
    applicants ||--|| applicant_accounts : "มีบัญชีและรหัสผ่านเข้าใช้"
    officers ||--o{ announcements : "เผยแพร่ข่าว"
    officers ||--o{ laws : "เผยแพร่ระเบียบข้อกฎหมาย"
    officers ||--o{ download_documents : "เผยแพร่แบบฟอร์ม"
    officers ||--o{ infographics : "เผยแพร่ภาพแบนเนอร์"
    officers ||--o{ staff_messages : "เขียนโดย"
    officers ||--o{ meeting_results : "บันทึกโดย"
    officers ||--o{ workflow_history : "ปรับปรุงโดย"

    applicants {
        int id PK
        string full_name
        string email UK
        string phone
        tinyint is_registered
        string password_hash
        timestamp created_at
    }

    applicant_accounts {
        int id PK
        int applicant_id FK
        string applicant_code UK
        string password_hash
        string password_plain
        timestamp created_at
    }

    request_types {
        int id PK
        string code UK
        string name_th
        json doc_checklist
        tinyint active
    }

    officers {
        int id PK
        string username UK
        string password_hash
        string name
        string email UK
        string role
        timestamp created_at
    }

    requests {
        int id PK
        string request_no UK
        int type_id FK
        int applicant_id FK
        int assigned_officer_id FK
        string status
        string process_1_status
        string process_2_status
        json form_data
        timestamp created_at
        timestamp updated_at
    }

    request_attachments {
        int id PK
        int request_id FK
        string file_name
        string file_path
        string mime_type
        int file_size
        enum uploaded_by
        string attachment_type
        timestamp created_at
    }

    staff_messages {
        int id PK
        int request_id FK
        int officer_id FK
        text message
        timestamp created_at
    }

    meeting_results {
        int id PK
        int request_id FK
        date meeting_date
        text result_summary
        string file_name
        string file_path
        string mime_type
        int file_size
        int officer_id FK
        timestamp created_at
    }

    workflow_history {
        int id PK
        int request_id FK
        string action
        text details
        int officer_id FK
        int applicant_id FK
        string ip_address
        string user_agent
        timestamp created_at
    }

    announcements {
        int id PK
        string title
        text content
        string type
        int author_id FK
        timestamp created_at
        timestamp updated_at
    }

    laws {
        int id PK
        string title
        string category
        string file_name
        string file_path
        int file_size
        int uploaded_by FK
        timestamp created_at
    }

    download_documents {
        int id PK
        string title
        string category
        string file_name
        string file_path
        int file_size
        int uploaded_by FK
        timestamp created_at
    }

    infographics {
        int id PK
        string title
        string image_name
        string image_path
        int uploaded_by FK
        timestamp created_at
    }
```
