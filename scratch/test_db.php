<?php
// scratch/test_db.php
try {
    // Attempt PDO connection
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=sesaonara_edocs", "sesaonara_edocs", "Thi\$i\$spmnara15");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "SUCCESS: Connected via PDO.\n";
    
    // Attempt to alter plugin to mysql_native_password for both localhost and 127.0.0.1
    $pdo->exec("ALTER USER 'sesaonara_edocs'@'localhost' IDENTIFIED WITH mysql_native_password BY 'Thi\$i\$spmnara15'");
    echo "SUCCESS: Altered user 'sesaonara_edocs'@'localhost' to mysql_native_password.\n";
    
    try {
        $pdo->exec("ALTER USER 'sesaonara_edocs'@'%' IDENTIFIED WITH mysql_native_password BY 'Thi\$i\$spmnara15'");
        echo "SUCCESS: Altered user 'sesaonara_edocs'@'%' to mysql_native_password.\n";
    } catch (Exception $e) {
        // Ignored if % user doesn't exist
    }
} catch (Exception $e) {
    echo "ERROR: PDO failed: " . $e->getMessage() . "\n";
}
