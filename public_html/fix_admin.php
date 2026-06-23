<?php
// fix_admin.php - Database self-healing tool for edocs-spmnara

date_default_timezone_set('Asia/Bangkok');

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
        $file = $base_dir . 'app/config/' . strtolower($className) . '.php';
    } else {
        $folder = strtolower($subNamespace);
        $file = $base_dir . $folder . '/' . $className . '.php';
    }
    
    if (file_exists($file)) {
        require $file;
    }
});

// Load environment variables from .env
\App\Config\Env::load(__DIR__ . '/.env');

use App\Config\Database;

try {
    $db = Database::getConnection();
    
    // Correct hash for 'admin123'
    $hash = '$2y$10$hR9eEDlmu8iIzBeB75y.funu03kNfw.f1h82yVOCNW7p/3Tvo.lwG';
    
    // Check if admin exists
    $stmt = $db->prepare("SELECT id FROM officers WHERE username = 'admin' LIMIT 1");
    $stmt->execute();
    $res = $stmt->get_result();
    $exists = $res->fetch_assoc();
    $stmt->close();
    
    if ($exists) {
        // Update
        $stmt = $db->prepare("UPDATE officers SET password_hash = ? WHERE username = 'admin'");
        $stmt->bind_param("s", $hash);
        $stmt->execute();
        $stmt->close();
        $msg = "อัปเดตความถูกต้องของรหัสผ่านสำหรับผู้ใช้งาน 'admin' เรียบร้อย!";
    } else {
        // Insert
        $stmt = $db->prepare("INSERT INTO officers (username, password_hash, name, email, role) VALUES ('admin', ?, 'ผู้ดูแลระบบ สพม.นราธิวาส', 'admin@spmnara.go.th', 'admin')");
        $stmt->bind_param("s", $hash);
        $stmt->execute();
        $stmt->close();
        $msg = "สร้างผู้ใช้งาน 'admin' และตั้งรหัสผ่านสำเร็จ!";
    }
    
    // Also fix head and staff
    $db->query("UPDATE officers SET password_hash = '$hash' WHERE username IN ('head', 'staff')");
    
    echo "
    <div style='font-family: sans-serif; text-align: center; margin-top: 100px; padding: 20px;'>
        <h1 style='color: #10b981;'>แก้ไขและนำเข้าข้อมูลบัญชีสำเร็จ!</h1>
        <p style='color: #4b5563; font-size: 16px;'>$msg</p>
        <p style='color: #1e3a8a; font-size: 15px;'>บัญชีที่ใช้งานได้: <strong>admin</strong> | รหัสผ่าน: <strong>admin123</strong></p>
        <p style='color: #ef4444; font-weight: bold;'>⚠️ เพื่อความปลอดภัย โปรดลบไฟล์ <u>fix_admin.php</u> ออกจากเซิร์ฟเวอร์หลังล็อกอินได้แล้ว</p>
        <p style='margin-top: 20px;'><a href='./admin/login' style='color: white; background-color: #1e3a8a; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;'>ไปหน้าล็อกอินเจ้าหน้าที่</a></p>
    </div>
    ";
} catch (Exception $e) {
    echo "
    <div style='font-family: sans-serif; text-align: center; margin-top: 100px; padding: 20px;'>
        <h1 style='color: #dc2626;'>เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล</h1>
        <p style='color: #4b5563; font-size: 16px;'>" . htmlspecialchars($e->getMessage()) . "</p>
        <p style='color: #6b7280; font-size: 14px;'>โปรดตรวจสอบว่าได้อัปโหลดไฟล์ .env และกรอกข้อมูลฐานข้อมูลในไฟล์ .env ถูกต้องแล้ว</p>
    </div>
    ";
}
