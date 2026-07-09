<?php
declare(strict_types=1);

/**
 * MESIGO ERP - Application Configuration
 */

return [
    'app' => [
        'name' => 'MESIGO ERP',
        'version' => '1.0.0',
        'env' => $_ENV['APP_ENV'] ?? 'production',
        'debug' => ($_ENV['APP_DEBUG'] ?? 'false') === 'true',
        'url' => APP_URL,
        'timezone' => 'Asia/Kolkata',
    ],
    
    'database' => [
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'port' => (int) ($_ENV['DB_PORT'] ?? 3306),
        'name' => $_ENV['DB_NAME'] ?? 'mesigo_erp',
        'user' => $_ENV['DB_USER'] ?? 'root',
        'pass' => $_ENV['DB_PASS'] ?? '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => true,
        ],
    ],
    
    'session' => [
        'name' => 'MESIGO_SESSID',
        'lifetime' => 10800, // 3 hours
        'cookie_lifetime' => 0,
        'cookie_path' => '/',
        'cookie_secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'cookie_httponly' => true,
        'cookie_samesite' => 'Strict',
        'use_strict_mode' => true,
    ],
    
    'mail' => [
        'host' => $_ENV['MAIL_HOST'] ?? '',
        'port' => (int) ($_ENV['MAIL_PORT'] ?? 587),
        'user' => $_ENV['MAIL_USER'] ?? '',
        'pass' => $_ENV['MAIL_PASS'] ?? '',
        'encryption' => 'tls',
        'from_address' => 'noreply@mesigoerp.com',
        'from_name' => 'MESIGO ERP',
    ],
    
    'whatsapp' => [
        'api_key' => $_ENV['WHATSAPP_API_KEY'] ?? '',
        'api_url' => $_ENV['WHATSAPP_API_URL'] ?? '',
    ],
    
    'upload' => [
        'max_size' => 10485760, // 10MB
        'allowed_types' => [
            'documents' => ['pdf', 'doc', 'docx', 'xls', 'xlsx'],
            'images' => ['jpg', 'jpeg', 'png', 'gif'],
            'certificates' => ['pdf'],
        ],
        'paths' => [
            'company' => APP_ROOT . '/uploads/company',
            'products' => APP_ROOT . '/uploads/products',
            'documents' => APP_ROOT . '/uploads/documents',
            'users' => APP_ROOT . '/uploads/users',
        ],
    ],
    
    'pagination' => [
        'per_page' => 15,
        'max_per_page' => 100,
    ],
    
    'security' => [
        'password_min_length' => 12,
        'login_attempts' => 5,
        'lockout_time' => 900, // 15 minutes
        'csrf_token_name' => 'csrf_token',
    ],
];