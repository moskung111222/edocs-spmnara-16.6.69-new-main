<?php
date_default_timezone_set('Asia/Bangkok');

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
    $base_dir     = __DIR__ . '/../public_html/';
    
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

\App\Config\Env::load(__DIR__ . '/../public_html/.env');

try {
    echo "Attempting to connect to the database...\n";
    $db = \App\Config\Database::getConnection();
    echo "Connection successful!\n";
    
    // Check if the 14 tables exist
    $tables = [
        'applicants', 'departments', 'roles', 'permissions', 'role_permissions',
        'officers', 'officer_departments', 'request_types', 'requests',
        'attachments', 'status_history', 'messages', 'audit_logs', 'otp_verifications'
    ];
    
    echo "Verifying tables:\n";
    foreach ($tables as $table) {
        $result = $db->query("SHOW TABLES LIKE '$table'");
        if ($result && $result->num_rows > 0) {
            echo "  - Table '$table': EXISTS\n";
        } else {
            echo "  - Table '$table': MISSING ❌\n";
        }
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
