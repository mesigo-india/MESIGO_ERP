<?php
// Diagnostic 2: Test through the ACTUAL index.php router path with error reporting
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

define('APP_ROOT', __DIR__);
define('APP_URL', 'http://localhost');

// Simulate a request to /buyers
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/buyers';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['HTTP_USER_AGENT'] = 'Diagnostic';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['HTTPS'] = '';

// DO NOT start session here - let index.php do it
// But we need to fake a logged-in session via cookie
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'admin';
$_SESSION['email'] = 'admin@mesigo.com';
$_SESSION['role_id'] = 1;
$_SESSION['permissions'] = ['all'];
$_SESSION['authenticated'] = true;
session_write_close();

echo "=== Testing ACTUAL index.php bootstrap path ===\n";

// Replicate index.php EXACTLY but with error reporting
require_once APP_ROOT . '/config/environment.php';
$config = require_once APP_ROOT . '/config/config.php';

require_once APP_ROOT . '/classes/Database.php';
require_once APP_ROOT . '/classes/Session.php';
require_once APP_ROOT . '/classes/Auth.php';
require_once APP_ROOT . '/classes/Response.php';
require_once APP_ROOT . '/classes/Validator.php';
require_once APP_ROOT . '/classes/Logger.php';
require_once APP_ROOT . '/classes/Pagination.php';
require_once APP_ROOT . '/classes/Router.php';
require_once APP_ROOT . '/classes/Controller.php';

require_once APP_ROOT . '/helpers/functions.php';

echo "=== Init session ===\n";
\App\Core\Session::start($config['session']);
echo "OK\n";

echo "=== Init database ===\n";
$db = \App\Core\Database::getInstance($config['database']);
echo "OK\n";

echo "=== Init auth ===\n";
$auth = new \App\Core\Auth($db);
echo "OK (isLoggedIn=" . ($auth->isLoggedIn() ? 'yes' : 'no') . ")\n";

echo "=== Init router ===\n";
$router = new \App\Core\Router();
echo "OK\n";

echo "=== Load routes ===\n";
require_once APP_ROOT . '/config/routes.php';
echo "OK\n";

echo "=== Dispatch (this calls the controller via router) ===\n";
try {
    $router->dispatch();
    echo "OK (dispatch returned)\n";
} catch (Throwable $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}

echo "=== hasRoute: " . ($router->hasRoute() ? 'true' : 'false') . " ===\n";
echo "\n=== DONE ===\n";