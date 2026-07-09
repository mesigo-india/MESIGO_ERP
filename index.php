<?php
declare(strict_types=1);

/**
 * MESIGO ERP Enterprise Edition
 * Main Entry Point
 * 
 * @package MESIGO_ERP
 * @version 1.0.0
 */

// Define application root
define('APP_ROOT', __DIR__);
define('APP_URL', 'https://mesigoerp.com');

// Load Composer autoloader when dependencies are installed
$autoloadFile = APP_ROOT . '/vendor/autoload.php';
if (file_exists($autoloadFile)) {
    require_once $autoloadFile;
}

// Load environment configuration
require_once APP_ROOT . '/config/environment.php';

// Load application configuration
$config = require_once APP_ROOT . '/config/config.php';

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

// Load helper functions
require_once APP_ROOT . '/helpers/functions.php';

// Initialize session
\App\Core\Session::start($config['session']);

// Initialize database connection
$db = \App\Core\Database::getInstance($config['database']);

// Initialize authentication
$auth = new \App\Core\Auth($db);

// Initialize router
$router = new \App\Core\Router();

// Load routes
require_once APP_ROOT . '/config/routes.php';

// Process request
$router->dispatch();

// Handle 404
if (!$router->hasRoute()) {
    http_response_code(404);
    require_once APP_ROOT . '/404.php';
    exit;
}
