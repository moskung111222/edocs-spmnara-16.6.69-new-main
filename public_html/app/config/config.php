<?php
namespace App\Config;

class Config {
    // Basic settings
    public const SITE_URL = 'http://localhost:8080';
    
    // Pusher settings (Realtime Notifications loaded from Env)
    public static function getPusherAppId() {
        return Env::get('PUSHER_APP_ID', '1820463');
    }
    public static function getPusherKey() {
        return Env::get('PUSHER_KEY', '7b203c94dfde5dbe96bc');
    }
    public static function getPusherSecret() {
        return Env::get('PUSHER_SECRET', '3e76a66699e34e56598c');
    }
    public static function getPusherCluster() {
        return Env::get('PUSHER_CLUSTER', 'ap1');
    }
    
    // File upload settings
    public const UPLOAD_MAX_SIZE = 10 * 1024 * 1024; // 10MB
    public const ALLOWED_MIME_TYPES = ['application/pdf'];
    
    // Private storage path - outside public_html.
    // For Windows local environment, it resolves to a sister folder of public_html named private_storage.
    // For DirectAdmin, it will fallback to the account-wide private storage folder.
    public static function getPrivateStoragePath() {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return dirname(__DIR__, 3) . '/private_storage/documents/';
        } else {
            // DirectAdmin path outside public_html (make sure account matches your actual hosting account)
            return '/home/account/private_storage/documents/';
        }
    }
    
    // Request status constants
    public const STATUS_SUBMITTED       = 'submitted';
    public const STATUS_RECEIVED        = 'received';
    public const STATUS_IN_REVIEW       = 'in_review';
    public const STATUS_NEED_INFO       = 'need_info';
    public const STATUS_PENDING_APPROVAL= 'pending_approval';
    public const STATUS_APPROVED        = 'approved';
    public const STATUS_COMPLETED       = 'completed';
    public const STATUS_REJECTED        = 'rejected';

    /**
     * Map request statuses to user-friendly Thai titles.
     * @return array
     */
    public static function getStatusList() {
        return [
            self::STATUS_SUBMITTED        => 'ยื่นคำขอแล้ว',
            self::STATUS_RECEIVED         => 'รับเรื่องแล้ว',
            self::STATUS_IN_REVIEW        => 'กำลังตรวจสอบเอกสาร',
            self::STATUS_NEED_INFO        => 'ขอข้อมูลเพิ่มเติม/แก้ไขเอกสาร',
            self::STATUS_PENDING_APPROVAL => 'รอการอนุมัติ',
            self::STATUS_APPROVED         => 'อนุมัติคำขอแล้ว',
            self::STATUS_COMPLETED        => 'ดำเนินการเสร็จสิ้น (รับเอกสาร)',
            self::STATUS_REJECTED         => 'ปฏิเสธคำขอ'
        ];
    }
}
