<?php
namespace App\Config;

class Env {
    private static $loaded = false;

    /**
     * Load keys and values from .env file into $_ENV and putenv()
     * @param string $path Absolute path to .env file
     * @return void
     */
    public static function load($path) {
        if (self::$loaded) return;
        if (!file_exists($path)) return;

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            // Skip comments and empty lines
            if (empty($line) || strpos($line, '#') === 0) continue;
            
            $parts = explode('=', $line, 2);
            if (count($parts) === 2) {
                $key = trim($parts[0]);
                $value = trim($parts[1]);
                
                // Strip quotes if any
                if ((strpos($value, '"') === 0 && strrpos($value, '"') === (strlen($value) - 1)) ||
                    (strpos($value, "'") === 0 && strrpos($value, "'") === (strlen($value) - 1))) {
                    $value = substr($value, 1, -1);
                }
                
                if (!array_key_exists($key, $_SERVER) && !array_key_exists($key, $_ENV)) {
                    putenv("{$key}={$value}");
                    $_ENV[$key] = $value;
                    $_SERVER[$key] = $value;
                }
            }
        }
        self::$loaded = true;
    }

    /**
     * Get environment variable value with a fallback default.
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = null) {
        $val = getenv($key);
        if ($val === false) {
            return $default;
        }
        
        if ($val === 'true' || $val === '(true)') return true;
        if ($val === 'false' || $val === '(false)') return false;
        if ($val === 'empty' || $val === '(empty)') return '';
        if ($val === 'null' || $val === '(null)') return null;
        return $val;
    }
}
