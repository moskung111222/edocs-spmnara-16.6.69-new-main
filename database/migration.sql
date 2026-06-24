-- Migration: Deactivate legacy request types for Homeschool Management System
UPDATE request_types SET active = 0 WHERE code IN ('HS', 'TR', 'ED');
