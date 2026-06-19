<?php
namespace App\Middleware;

use App\Config\Config;

class AuthMiddleware {
    /**
     * Restrict page to logged-in officers, optionally checking their roles.
     * @param array $allowedRoles Role list e.g., ['staff', 'head', 'admin']
     * @return void
     */
    public static function requireOfficer(array $allowedRoles = []) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Check if officer is logged in
        if (!isset($_SESSION['officer_id'])) {
            header("Location: " . Config::SITE_URL . "/admin/login");
            exit;
        }

        // Check role permissions
        if (!empty($allowedRoles)) {
            $currentRole = $_SESSION['officer_role'] ?? '';
            if (!in_array($currentRole, $allowedRoles)) {
                http_response_code(403);
                die("
                    <div style='font-family: sans-serif; text-align: center; margin-top: 100px;'>
                        <h1 style='color: #dc2626; font-size: 48px;'>403 Forbidden</h1>
                        <p style='font-size: 18px; color: #4b5563;'>คุณไม่มีสิทธิ์เข้าใช้งานส่วนนี้ เฉพาะสิทธิ์: " . implode(', ', $allowedRoles) . " เท่านั้น</p>
                        <p><a href='" . Config::SITE_URL . "/admin/dashboard' style='color: #1e3a8a; font-weight: bold;'>กลับไปยังหน้าหลัก</a></p>
                    </div>
                ");
            }
        }
    }

    /**
     * Restrict request tracking view to only authenticated applicants who verified OTP.
     * @param string $requestNo
     * @return void
     */
    public static function requireTrackingAuth($requestNo) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['tracking_auth'][$requestNo]) || $_SESSION['tracking_auth'][$requestNo] !== true) {
            header("Location: " . Config::SITE_URL . "/request/track?no=" . urlencode($requestNo) . "&unauthorized=1");
            exit;
        }
    }

    /**
     * Check if any officer session is active.
     * @return bool
     */
    public static function isOfficerLoggedIn() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['officer_id']);
    }

    /**
     * Require a specific RBAC permission. Blocks with 403 if not granted.
     * @param string $permissionCode e.g., 'requests.approve', 'departments.create'
     * @return void
     */
    public static function requirePermission($permissionCode) {
        self::requireOfficer();
        if (!self::hasPermission($permissionCode)) {
            http_response_code(403);
            die("
                <div style='font-family: sans-serif; text-align: center; margin-top: 100px;'>
                    <h1 style='color: #dc2626; font-size: 48px;'>403 Forbidden</h1>
                    <p style='font-size: 18px; color: #4b5563;'>คุณไม่มีสิทธิ์ดำเนินการนี้ (ต้องมีสิทธิ์: " . htmlspecialchars($permissionCode, ENT_QUOTES, 'UTF-8') . ")</p>
                    <p><a href='" . Config::SITE_URL . "/admin/dashboard' style='color: #1e3a8a; font-weight: bold;'>กลับไปยังหน้าหลัก</a></p>
                </div>
            ");
        }
    }

    /**
     * Check if the current officer session has a specific permission.
     * @param string $permissionCode
     * @return bool
     */
    public static function hasPermission($permissionCode) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $permissions = $_SESSION['officer_permissions'] ?? [];
        return in_array($permissionCode, $permissions);
    }
}
