<?php
declare(strict_types=1);

/**
 * MESIGO ERP - Autoloader
 * Loads all required classes and helpers
 */

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

// Load helper functions
require_once APP_ROOT . '/helpers/functions.php';

// Load core classes
require_once APP_ROOT . '/classes/Database.php';
require_once APP_ROOT . '/classes/Session.php';
require_once APP_ROOT . '/classes/Auth.php';
require_once APP_ROOT . '/classes/Response.php';
require_once APP_ROOT . '/classes/Validator.php';
require_once APP_ROOT . '/classes/Logger.php';
require_once APP_ROOT . '/classes/Pagination.php';
require_once APP_ROOT . '/classes/Router.php';
require_once APP_ROOT . '/classes/Controller.php';

// Load middleware
require_once APP_ROOT . '/middleware/AuthMiddleware.php';
require_once APP_ROOT . '/middleware/PermissionMiddleware.php';

// Set error handler
set_error_handler(function($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    
    $logger = new \App\Core\Logger();
    $logger->error("PHP Error: {$message}", ['file' => $file, 'line' => $line]);
    
    if (($severity & E_USER_ERROR) || ($severity & E_ERROR)) {
        http_response_code(500);
        require_once APP_ROOT . '/500.php';
        exit;
    }
    
    return true;
});

// Set exception handler
set_exception_handler(function($exception) {
    $logger = new \App\Core\Logger();
    $logger->error("Uncaught Exception: " . $exception->getMessage(), [
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTraceAsString()
    ]);
    
    if (!headers_sent()) {
        http_response_code(500);
    }
    
    require_once APP_ROOT . '/500.php';
    exit;
});
