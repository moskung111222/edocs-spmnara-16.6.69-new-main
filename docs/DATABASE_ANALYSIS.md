# Database Analysis Report

This document provides an analysis of the database files, tables, and migrations for the NWT Document Submission System.

---

## 1. Existing SQL and Database Files
The project contains the following database-related files in its workspace:
* **`install.sql`** (Root): A consolidated full v2.0 schema script containing all 14 tables.
* **`migration.sql`** (Root): Base database schema for v1.0 (9 tables, legacy/obsolete database name `sesaonara_edocs`).
* **`upgrade_migration.sql`** (Root): Incremental update script to transition from v1.0 to v2.0 (adds 5 tables, alters 2 tables).
* **`migration.zip`** (Root): Archive containing `migration.sql` (obsolete).
* **`upgrade_migration.zip`** (Root): Archive containing `upgrade_migration.sql` (obsolete).
* **`database/install.sql`** (New): The newly created clean, production-ready, consolidated installation script.

---

## 2. Files Still Required
The following files are required for the system setup and backward compatibility:
1. **`database/install.sql`**: The new canonical installation script for new environments, compatible with MySQL 8+ and MariaDB.

---

## 3. Files Safe to Remove
The following legacy migration and archive files are safe to delete as they have been fully consolidated into the unified installation scripts:
* `migration.sql` (Deleted)
* `upgrade_migration.sql` (Deleted)
* `migration.zip` (Deleted)
* `upgrade_migration.zip` (Deleted)
* `install.sql` (Root) (Deleted)

---

## 4. Table Status Analysis

### Duplicate Tables
* **No Duplicate Tables Found**: There are no duplicate or redundant tables in the active schema.
* **Note on Migrations**: Previously, `migration.sql` and `upgrade_migration.sql` had overlapping schema definitions for tables like `officers` and `request_types` (where columns were altered/added). Consolidating them into `database/install.sql` removes this redundancy.

### Missing Tables
* **No Missing Tables**: All tables referenced by the application's models, services, and queries are present:
  1. `applicants` (Used in `Applicant.php`, `Request.php`, `AuthController.php`)
  2. `departments` (Used in `Department.php`, `Officer.php`, `ServiceController.php`)
  3. `roles` (Used in `Role.php`, `RBACService.php`, `RoleController.php`)
  4. `permissions` (Used in `Permission.php`, `RBACService.php`, `RoleController.php`)
  5. `role_permissions` (Used in `Role.php`, `Permission.php`, `RBACService.php`)
  6. `officers` (Used in `Officer.php`, `AuthController.php`, `Request.php`)
  7. `officer_departments` (Used in `Officer.php`, `Department.php` for M:N mapping)
  8. `request_types` (Used in `Request.php`, `ServiceController.php` for services)
  9. `requests` (Used in `Request.php`, `RequestService.php`)
  10. `attachments` (Used in `Attachment.php`, `RequestService.php`)
  11. `status_history` (Used in `Request.php` for transitions)
  12. `messages` (Used in `Request.php` for applicant-officer chat)
  13. `audit_logs` (Used in `RequestService.php` for tracking actions)
  14. `otp_verifications` (Used in `RequestService.php` for registration and tracking verification)

---

## 5. Compatibility & Production Readiness
* **Database Name**: Uses `edocs_spmnara` as standard to match the `.env` database configuration.
* **Character Set & Collation**: Standardized to `utf8mb4` with `utf8mb4_unicode_ci` for complete multi-language and emoji support (e.g. Thai language inputs).
* **Constraints**: Clean inline named constraints (e.g., `fk_officers_department`) instead of duplicate or redundant `ALTER TABLE` operations.
* **Engine**: Explicitly targets `InnoDB` for transactional integrity and foreign key support.
