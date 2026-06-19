<?php
namespace App\Middleware;

class CsrfMiddleware {
    /**
     * Start session if needed and generate a token if not exists.
     */
    public static function init() {
        if (session_status() === PHP_SESSION_NONE) {
            // Set secure cookies if connection is secure
            $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'domain' => isset($_SERVER['HTTP_HOST']) ? explode(':', $_SERVER['HTTP_HOST'])[0] : '',
                'secure' => $secure,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            session_start();
        }

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    /**
     * Get the current CSRF token.
     * @return string
     */
    public static function getToken() {
        self::init();
        return $_SESSION['csrf_token'];
    }

    /**
     * Generate HTML hidden input tag containing CSRF token.
     * @return string
     */
    public static function getHtmlField() {
        $token = self::getToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Validate the given token.
     * @param string $token
     * @return bool
     */
    public static function validate($token) {
        self::init();
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Validate any incoming POST requests automatically.
     */
    public static function validatePostRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
            if (!self::validate($token)) {
                http_response_code(403);
                die("
                    <div style='font-family: sans-serif; text-align: center; margin-top: 100px;'>
                        <h1 style='color: #dc2626; font-size: 48px;'>403 Forbidden</h1>
                        <p style='font-size: 18px; color: #4b5563;'>ข้อผิดพลาดด้านความปลอดภัย (CSRF Token ไม่ถูกต้อง)</p>
                        <p><a href='' onclick='window.history.back(); return false;' style='color: #1e3a8a; font-weight: bold;'>ย้อนกลับ</a></p>
                    </div>
                ");
            }
        }
    }
}
