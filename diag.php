<?php
// Temporary diagnostic script - simulates bootstrap with full error reporting
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

// Start session
session_start();

// Simulate logged in user with all permissions
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'admin';
$_SESSION['email'] = 'admin@mesigo.com';
$_SESSION['role_id'] = 1;
$_SESSION['permissions'] = ['all'];
$_SESSION['authenticated'] = true;

echo "=== STEP 1: Load environment ===\n";
require_once APP_ROOT . '/config/environment.php';
echo "OK\n";

echo "=== STEP 2: Load config ===\n";
$config = require_once APP_ROOT . '/config/config.php';
echo "OK\n";

echo "=== STEP 3: Load core classes ===\n";
require_once APP_ROOT . '/classes/Database.php';
require_once APP_ROOT . '/classes/Session.php';
require_once APP_ROOT . '/classes/Auth.php';
require_once APP_ROOT . '/classes/Response.php';
require_once APP_ROOT . '/classes/Validator.php';
require_once APP_ROOT . '/classes/Logger.php';
require_once APP_ROOT . '/classes/Pagination.php';
require_once APP_ROOT . '/classes/Router.php';
require_once APP_ROOT . '/classes/Controller.php';
echo "OK\n";

echo "=== STEP 4: Load helpers ===\n";
require_once APP_ROOT . '/helpers/functions.php';
echo "OK\n";

echo "=== STEP 5: Init session ===\n";
\App\Core\Session::start($config['session']);
echo "OK\n";

echo "=== STEP 6: Init database ===\n";
try {
    $db = \App\Core\Database::getInstance($config['database']);
    echo "OK (PDO connected)\n";
} catch (Throwable $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    exit(1);
}

echo "=== STEP 7: Init auth ===\n";
$auth = new \App\Core\Auth($db);
echo "OK (isLoggedIn=" . ($auth->isLoggedIn() ? 'yes' : 'no') . ")\n";

echo "=== STEP 8: Load BuyerController ===\n";
require_once APP_ROOT . '/classes/BuyerController.php';
require_once APP_ROOT . '/classes/Buyer.php';
echo "OK\n";

echo "=== STEP 9: Instantiate BuyerController ===\n";
try {
    $controller = new \App\Core\BuyerController();
    echo "OK\n";
} catch (Throwable $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}

echo "=== STEP 10: Call index() ===\n";
try {
    $controller->index();
    echo "OK (rendered)\n";
} catch (Throwable $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== DONE ===\n";