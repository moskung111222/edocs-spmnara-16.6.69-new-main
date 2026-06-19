<?php
namespace App\Services;

use App\Config\Database;
use App\Models\Role;
use Exception;

class RBACService {
    /**
     * Check if a specific officer has a given permission.
     * @param int $officerId
     * @param string $permissionCode
     * @return bool
     */
    public static function hasPermission($officerId, $permissionCode) {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("
                SELECT 1
                FROM officers o
                JOIN roles r ON r.code = o.role
                JOIN role_permissions rp ON rp.role_id = r.id
                JOIN permissions p ON p.id = rp.permission_id
                WHERE o.id = ? AND p.code = ?
                LIMIT 1
            ");
            if (!$stmt) {
                return false;
            }
            $stmt->bind_param("is", $officerId, $permissionCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $found = $result->fetch_assoc() !== null;
            $stmt->close();
            return $found;
        } catch (Exception $e) {
            error_log("RBAC check failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all permission codes for a given officer (based on their role).
     * @param int $officerId
     * @return array Array of permission code strings
     */
    public static function getOfficerPermissions($officerId) {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("
                SELECT p.code
                FROM officers o
                JOIN roles r ON r.code = o.role
                JOIN role_permissions rp ON rp.role_id = r.id
                JOIN permissions p ON p.id = rp.permission_id
                WHERE o.id = ?
                ORDER BY p.code ASC
            ");
            if (!$stmt) {
                return self::getFallbackPermissions($officerId);
            }
            $stmt->bind_param("i", $officerId);
            if (!$stmt->execute()) {
                return self::getFallbackPermissions($officerId);
            }
            $result = $stmt->get_result();
            $codes = [];
            while ($row = $result->fetch_assoc()) {
                $codes[] = $row['code'];
            }
            $stmt->close();

            // If no RBAC data found (tables might not be seeded yet), use fallback
            if (empty($codes)) {
                return self::getFallbackPermissions($officerId);
            }

            return $codes;
        } catch (Exception $e) {
            error_log("RBAC load failed: " . $e->getMessage());
            return self::getFallbackPermissions($officerId);
        }
    }

    /**
     * Load officer permissions into session.
     * Call this after successful login.
     * @param int $officerId
     * @return void
     */
    public static function loadSessionPermissions($officerId) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['officer_permissions'] = self::getOfficerPermissions($officerId);
    }

    /**
     * Check if the current session has a specific permission.
     * @param string $permissionCode
     * @return bool
     */
    public static function sessionHasPermission($permissionCode) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $permissions = $_SESSION['officer_permissions'] ?? [];
        return in_array($permissionCode, $permissions);
    }

    /**
     * Fallback permissions based on legacy role column.
     * Used when RBAC tables don't exist or are empty (backward compatibility).
     * @param int $officerId
     * @return array
     */
    private static function getFallbackPermissions($officerId) {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT role FROM officers WHERE id = ? LIMIT 1");
            if (!$stmt) {
                return [];
            }
            $stmt->bind_param("i", $officerId);
            $stmt->execute();
            $result = $stmt->get_result();
            $officer = $result->fetch_assoc();
            $stmt->close();

            if (!$officer) {
                return [];
            }

            $role = $officer['role'];

            // Legacy permission mapping — matches original hard-coded behavior
            $basePerms = [
                'dashboard.view',
                'requests.view',
                'requests.change_status',
                'requests.message',
                'requests.internal_note',
                'departments.view',
                'services.view'
            ];

            if ($role === 'head') {
                return array_merge($basePerms, [
                    'dashboard.kpi',
                    'requests.view_all',
                    'requests.assign',
                    'requests.approve',
                    'requests.reject',
                    'officers.view'
                ]);
            }

            if ($role === 'admin') {
                return array_merge($basePerms, [
                    'dashboard.kpi',
                    'requests.view_all',
                    'requests.assign',
                    'requests.approve',
                    'requests.reject',
                    'officers.view', 'officers.create', 'officers.edit', 'officers.delete',
                    'departments.create', 'departments.edit', 'departments.delete',
                    'services.create', 'services.edit', 'services.delete',
                    'roles.view', 'roles.manage',
                    'audit.view'
                ]);
            }

            // Default: staff
            return $basePerms;
        } catch (Exception $e) {
            error_log("Fallback permissions failed: " . $e->getMessage());
            return [];
        }
    }
}
