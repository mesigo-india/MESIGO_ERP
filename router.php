<?php
/**
 * MESIGO ERP - PHP Built-in Server Router
 * 
 * Usage: php -S localhost:8000 router.php
 * 
 * This router:
 * 1. Serves static files (assets, images, fonts) directly
 * 2. Routes all other requests through index.php
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Serve static files directly from the document root
$staticFile = __DIR__ . $uri;

if ($uri !== '/' && file_exists($staticFile) && !is_dir($staticFile)) {
    // Let PHP's built-in server handle the static file
    return false;
}

// Route all other requests through the main entry point
require_once __DIR__ . '/index.php';
