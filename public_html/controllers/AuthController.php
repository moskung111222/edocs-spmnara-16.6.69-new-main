<?php
namespace App\Controllers;

use App\Config\Database;
use App\Config\Config;
use App\Middleware\CsrfMiddleware;
use App\Services\RequestService;
use Exception;

class AuthController {
    /**
     * Handle officer login view and logic.
     */
    public function login() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // If already logged in, skip login screen
        if (isset($_SESSION['officer_id'])) {
            header("Location: " . Config::SITE_URL . "/admin/dashboard");
            exit;
        }

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Verify CSRF token
                CsrfMiddleware::validatePostRequest();

                $username = trim($_POST['username'] ?? '');
                $password = trim($_POST['password'] ?? '');

                if (empty($username) || empty($password)) {
                    throw new Exception("โปรดกรอกชื่อผู้ใช้งานและรหัสผ่าน");
                }

                $db = Database::getConnection();
                $stmt = $db->prepare("SELECT * FROM officers WHERE username = ? LIMIT 1");
                if (!$stmt) {
                    throw new Exception("ฐานข้อมูลเกิดข้อผิดพลาดในการประมวลผล");
                }
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $result = $stmt->get_result();
                $officer = $result->fetch_assoc();
                $stmt->close();

                // Verify bcrypt password
                if ($officer && password_verify($password, $officer['password_hash'])) {
                    // Prevent session fixation
                    session_regenerate_id(true);

                    $_SESSION['officer_id']       = $officer['id'];
                    $_SESSION['officer_username'] = $officer['username'];
                    $_SESSION['officer_role']     = $officer['role'];
                    $_SESSION['officer_name']     = $officer['name'];
                    $_SESSION['officer_email']    = $officer['email'];
                    $_SESSION['officer_department_id'] = $officer['department_id'] ?? null;

                    // Load RBAC permissions into session
                    \App\Services\RBACService::loadSessionPermissions($officer['id']);

                    // Audit log entry
                    RequestService::logAudit(
                        $officer['id'],
                        'officer',
                        'เข้าสู่ระบบสําเร็จ',
                        'Auth',
                        $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                        $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
                    );

                    header("Location: " . Config::SITE_URL . "/admin/dashboard");
                    exit;
                } else {
                    throw new Exception("ชื่อผู้ใช้งานหรือรหัสผ่านไม่ถูกต้อง");
                }
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }

        // Include login view
        include dirname(__DIR__) . '/views/admin/login.php';
    }

    /**
     * Handle officer logout.
     */
    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['officer_id'])) {
            RequestService::logAudit(
                $_SESSION['officer_id'],
                'officer',
                'ออกจากระบบ',
                'Auth',
                $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
            );
        }

        // Clear session variables
        $_SESSION = [];

        // Destroy session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();

        header("Location: " . Config::SITE_URL . "/admin/login");
        exit;
    }
}
