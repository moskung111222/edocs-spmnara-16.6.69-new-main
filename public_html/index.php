<?php
// Set timezone to Asia/Bangkok (Thailand)
date_default_timezone_set('Asia/Bangkok');

// ==================================================================
// Front Controller & Router - NWT Document Submission System
// ==================================================================

// 1. Case-aware Class Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $parts = explode('\\', $relative_class);
    if (count($parts) < 2) {
        return;
    }
    
    $subNamespace = $parts[0];
    $className    = $parts[1];
    $base_dir     = __DIR__ . '/';
    
    if ($subNamespace === 'Config') {
        // App\Config\Database -> app/config/database.php
        $file = $base_dir . 'app/config/' . strtolower($className) . '.php';
    } else {
        // App\Controllers\AuthController -> controllers/AuthController.php
        $folder = strtolower($subNamespace);
        $file = $base_dir . $folder . '/' . $className . '.php';
    }
    
    if (file_exists($file)) {
        require $file;
    }
});

// Load environment variables from .env
\App\Config\Env::load(__DIR__ . '/.env');

// 2. Global Escaping Helper for XSS Protection
if (!function_exists('esc')) {
    function esc($string) {
        return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
    }
}

// 3. Initialize CSRF Token & Session Security Settings
use App\Middleware\CsrfMiddleware;
CsrfMiddleware::init();

// 4. Request Dispatcher (Router)
$route = $_GET['route'] ?? '';
$route = trim($route, '/');

try {
    // Public Applicant Routes
    if ($route === '' || $route === 'index.php') {
        $controller = new \App\Controllers\RequestController();
        $controller->index();
    } elseif ($route === 'request/create') {
        $controller = new \App\Controllers\RequestController();
        $controller->create();
    } elseif ($route === 'request/verify') {
        $controller = new \App\Controllers\RequestController();
        $controller->verify();
    } elseif ($route === 'request/track') {
        $controller = new \App\Controllers\RequestController();
        $controller->track();
    } elseif ($route === 'download') {
        $controller = new \App\Controllers\RequestController();
        $controller->download();
        
    // Administrative Routes
    } elseif ($route === 'admin/login') {
        $controller = new \App\Controllers\AuthController();
        $controller->login();
    } elseif ($route === 'admin/logout') {
        $controller = new \App\Controllers\AuthController();
        $controller->logout();
    } elseif ($route === 'admin/dashboard') {
        $controller = new \App\Controllers\AdminController();
        $controller->dashboard();
    } elseif ($route === 'admin/request') {
        $controller = new \App\Controllers\AdminController();
        $controller->requestDetail();

    // Department Management Routes
    } elseif ($route === 'admin/departments') {
        $controller = new \App\Controllers\DepartmentController();
        $controller->index();

    // Service (Request Types) Management Routes
    } elseif ($route === 'admin/services') {
        $controller = new \App\Controllers\ServiceController();
        $controller->index();

    // Officer Management Routes
    } elseif ($route === 'admin/officers') {
        $controller = new \App\Controllers\OfficerController();
        $controller->index();
    } elseif ($route === 'admin/officer/create') {
        $controller = new \App\Controllers\OfficerController();
        $controller->create();
    } elseif ($route === 'admin/officer/edit') {
        $controller = new \App\Controllers\OfficerController();
        $controller->edit();

    // Role & Permission Management Routes
    } elseif ($route === 'admin/roles') {
        $controller = new \App\Controllers\RoleController();
        $controller->index();
    } elseif ($route === 'admin/role/edit') {
        $controller = new \App\Controllers\RoleController();
        $controller->edit();
    } elseif ($route === 'admin/role/create') {
        $controller = new \App\Controllers\RoleController();
        $controller->create();
        
    // API Route delegation
    } elseif ($route === 'api/requests') {
        require_once __DIR__ . '/api/requests.php';
    } elseif ($route === 'api/upload') {
        require_once __DIR__ . '/api/upload.php';
    } elseif ($route === 'api/status') {
        require_once __DIR__ . '/api/status.php';
        
    // Fallback 404
    } else {
        http_response_code(404);
        include __DIR__ . '/views/public/404.php';
    }
} catch (\Exception $e) {
    error_log("Unhandled Application Exception: " . $e->getMessage());
    http_response_code(500);
    die("
        <div style='font-family: sans-serif; text-align: center; margin-top: 100px; padding: 20px;'>
            <h1 style='color: #dc2626;'>500 ขออภัย เกิดข้อผิดพลาดภายในระบบ</h1>
            <p style='color: #4b5563; font-size: 16px;'>" . esc($e->getMessage()) . "</p>
            <p><a href='" . \App\Config\Config::SITE_URL . "' style='color: #1e3a8a; font-weight: bold;'>กลับสู่หน้าแรก</a></p>
        </div>
    ");
}
