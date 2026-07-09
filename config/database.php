<?php
declare(strict_types=1);

/**
 * MESIGO ERP - Database Configuration
 */

return [
    'connections' => [
        'mysql' => [
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'port' => (int) ($_ENV['DB_PORT'] ?? 3306),
            'database' => $_ENV['DB_NAME'] ?? 'mesigo_erp',
            'username' => $_ENV['DB_USER'] ?? 'root',
            'password' => $_ENV['DB_PASS'] ?? '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
        ],
    ],
    
    'migrations' => APP_ROOT . '/database/migrations',
    'seeds' => APP_ROOT . '/database/seeds',
    'schema' => APP_ROOT . '/database/schema',
];