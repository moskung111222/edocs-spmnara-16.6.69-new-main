<?php
namespace App\Services;

use App\Config\Config;
use Exception;

class UploadService {
    /**
     * Validate and upload PDF files.
     * @param array $fileArray $_FILES['key'] format
     * @return array Metadata about stored file
     * @throws Exception
     */
    public static function uploadPDF(array $fileArray) {
        if (!isset($fileArray['tmp_name']) || !is_uploaded_file($fileArray['tmp_name'])) {
            throw new Exception("ไม่พบไฟล์ที่อัปโหลด หรือเป็นไฟล์ที่ไม่ถูกต้อง");
        }

        if ($fileArray['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("เกิดข้อผิดพลาดในการอัปโหลดไฟล์รหัส: " . $fileArray['error']);
        }

        // 1. Size Check
        if ($fileArray['size'] > Config::UPLOAD_MAX_SIZE) {
            throw new Exception("ขนาดไฟล์ใหญ่เกินไป ห้ามเกิน 10 MB");
        }

        // 2. MIME Validation
        $mime = '';
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $fileArray['tmp_name']);
            finfo_close($finfo);
        } else {
            // Fallback: Use file extension check. Magic bytes validation below will confirm PDF integrity.
            $ext = strtolower(pathinfo($fileArray['name'], PATHINFO_EXTENSION));
            if ($ext === 'pdf') {
                $mime = 'application/pdf';
            }
        }

        if (!in_array($mime, Config::ALLOWED_MIME_TYPES)) {
            throw new Exception("ประเภทไฟล์ไม่ถูกต้อง อนุญาตให้แนบเฉพาะ PDF เท่านั้น (ตรวจพบ: " . htmlspecialchars($mime) . ")");
        }

        // 3. PDF Magic Bytes Validation (%PDF-)
        $handle = fopen($fileArray['tmp_name'], 'rb');
        $magicBytes = fread($handle, 4);
        fclose($handle);

        if ($magicBytes !== '%PDF') {
            throw new Exception("เนื้อหาไฟล์ไม่ถูกต้อง ไม่ใช่โครงสร้างของเอกสาร PDF (Magic Bytes Check Failed)");
        }

        // Ensure storage path exists
        $storagePath = Config::getPrivateStoragePath();
        if (!is_dir($storagePath)) {
            if (!mkdir($storagePath, 0755, true)) {
                throw new Exception("ระบบจัดเก็บไฟล์ขัดข้อง ไม่สามารถสถาปนาพื้นที่จัดเก็บเอกสารได้");
            }
        }

        // 4. Secure Filename Generation
        $extension = 'pdf';
        $secureName = bin2hex(random_bytes(16)) . '.' . $extension;
        $targetLocation = $storagePath . $secureName;

        if (!move_uploaded_file($fileArray['tmp_name'], $targetLocation)) {
            throw new Exception("ไม่สามารถเคลื่อนย้ายไฟล์ไปยังที่จัดเก็บเอกสารได้");
        }

        return [
            'original_name' => basename($fileArray['name']),
            'stored_name'   => $secureName,
            'file_path'     => $targetLocation,
            'mime_type'     => $mime,
            'file_size'     => $fileArray['size']
        ];
    }
}
