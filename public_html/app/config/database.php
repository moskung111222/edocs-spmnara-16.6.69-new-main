<?php
namespace App\Config;

use mysqli;
use Exception;

class Database {
    private static $connection = null;

    /**
     * Get the database connection using mysqli.
     * @return mysqli
     * @throws Exception
     */
    public static function getConnection() {
        if (self::$connection === null) {
            // Retrieve configuration from environment variables with safe defaults
            $host = Env::get('DB_HOST', '127.0.0.1');
            $user = Env::get('DB_USER', 'root');
            $pass = Env::get('DB_PASS', ''); 
            $db   = Env::get('DB_NAME', 'edocs_spmnara');
            $port = (int)Env::get('DB_PORT', 3306);

            // Initialize connection
            self::$connection = new mysqli($host, $user, $pass, $db, $port);

            if (self::$connection->connect_error) {
                throw new Exception("Database connection failed: " . self::$connection->connect_error);
            }

            // Set character set to utf8mb4
            if (!self::$connection->set_charset("utf8mb4")) {
                throw new Exception("Error loading character set utf8mb4: " . self::$connection->error);
            }
        }
        return self::$connection;
    }
}
