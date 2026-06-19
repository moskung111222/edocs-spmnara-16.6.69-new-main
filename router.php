<?php
// Simple router for PHP built-in web server.
// Runs with: php -S localhost:8080 -t public_html router.php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . '/public_html' . $uri;

// If the file exists in public_html and is not a directory, serve it directly
if (file_exists($file) && !is_dir($file)) {
    return false; 
}

// Map the clean URL to the 'route' query parameter used by index.php
$_GET['route'] = ltrim($uri, '/');

// Dispatch through front controller
require_once __DIR__ . '/public_html/index.php';
