# SQL Cleanup Plan

This plan organizes the database-related files in the repository and provides recommendations for deletion and retention to streamline the repository structure.

---

## 1. Safe To Delete
The following files are obsolete and redundant legacy migration resources. They have been fully consolidated into the unified installation scripts and are safe to delete immediately:

* **`migration.sql`** (Root) - Legacy base schema (v1.0) database script. (Deleted)
* **`upgrade_migration.sql`** (Root) - Legacy upgrade migration (v1.0 -> v2.0) script. (Deleted)
* **`migration.zip`** (Root) - Compressed version of `migration.sql`. (Deleted)
* **`upgrade_migration.zip`** (Root) - Compressed version of `upgrade_migration.sql`. (Deleted)
* **`install.sql`** (Root) - Consolidated full v2.0 schema script. (Deleted)

---

## 2. Need Review
* **None**: There are no files requiring further review. All SQL files have been cataloged and validated against the system models and controllers.

---

## 3. Required
The following files are active, up-to-date, and required by the application. They must be kept in the repository:

* **`database/install.sql`** (New) - Consolidated clean installation script containing full v2.0 schema, indexes, constraints, and default seed data.
