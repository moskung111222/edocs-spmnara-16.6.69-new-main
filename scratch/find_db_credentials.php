<?php
$hosts = ['127.0.0.1', 'localhost'];
$users = ['root', 'sesaonara_edocs'];
$passes = ['', 'Thi$i$spmnara15', 'root'];

foreach ($hosts as $host) {
    foreach ($users as $user) {
        foreach ($passes as $pass) {
            try {
                $conn = @new mysqli($host, $user, $pass);
                if (!$conn->connect_error) {
                    echo "SUCCESS: Host=$host, User=$user, Pass=" . (empty($pass) ? "(empty)" : $pass) . "\n";
                    $conn->close();
                    exit(0);
                }
            } catch (Exception $e) {
                // ignore
            }
        }
    }
}
echo "FAILED to find working credentials.\n";
