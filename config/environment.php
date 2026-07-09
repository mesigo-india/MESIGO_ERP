<?php
declare(strict_types=1);

/**
 * MESIGO ERP - Environment Configuration
 * Loads environment variables from .env file
 */

// Load .env file if exists
$envFile = APP_ROOT . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) {
            continue;
        }
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $key = trim($parts[0]);
            $value = trim($parts[1]);
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

// Set default environment values
$_ENV['APP_ENV'] = $_ENV['APP_ENV'] ?? 'production';
$_ENV['APP_DEBUG'] = $_ENV['APP_DEBUG'] ?? 'false';
$_ENV['APP_KEY'] = $_ENV['APP_KEY'] ?? '';

$_ENV['DB_HOST'] = $_ENV['DB_HOST'] ?? 'localhost';
$_ENV['DB_PORT'] = $_ENV['DB_PORT'] ?? '3306';
$_ENV['DB_NAME'] = $_ENV['DB_NAME'] ?? 'mesigo_erp';
$_ENV['DB_USER'] = $_ENV['DB_USER'] ?? 'root';
$_ENV['DB_PASS'] = $_ENV['DB_PASS'] ?? '';

$_ENV['MAIL_HOST'] = $_ENV['MAIL_HOST'] ?? '';
$_ENV['MAIL_PORT'] = $_ENV['MAIL_PORT'] ?? '587';
$_ENV['MAIL_USER'] = $_ENV['MAIL_USER'] ?? '';
$_ENV['MAIL_PASS'] = $_ENV['MAIL_PASS'] ?? '';

$_ENV['WHATSAPP_API_KEY'] = $_ENV['WHATSAPP_API_KEY'] ?? '';
$_ENV['WHATSAPP_API_URL'] = $_ENV['WHATSAPP_API_URL'] ?? '';