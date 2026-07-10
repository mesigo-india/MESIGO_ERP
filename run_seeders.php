<?php
declare(strict_types=1);

/**
 * MESIGO ERP - Enterprise Seeder Runner
 */

define('APP_ROOT', __DIR__);
define('APP_URL', 'https://mesigoerp.com');

// Load Composer autoloader
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

// Load helper functions
require_once APP_ROOT . '/helpers/functions.php';

// Load seeder classes
require_once APP_ROOT . '/database/seeds/Seeder.php';
require_once APP_ROOT . '/database/seeds/MasterSeeder.php';
require_once APP_ROOT . '/database/seeds/CurrencySeeder.php';
require_once APP_ROOT . '/database/seeds/UnitSeeder.php';
require_once APP_ROOT . '/database/seeds/BuyerSeeder.php';
require_once APP_ROOT . '/database/seeds/SupplierSeeder.php';
require_once APP_ROOT . '/database/seeds/ProductSeeder.php';
require_once APP_ROOT . '/database/seeds/TransactionSeeder.php';
require_once APP_ROOT . '/database/seeds/DemoSeeder.php';

// Get database connection
$db = \App\Core\Database::getInstance($config['database']);

echo "\n=== MESIGO ERP DATABASE SEED RUNNER ===\n\n";

try {
    // Disable FK checks momentarily for clean run initialization if needed,
    // though our topology order handles this natively.
    $db->exec("SET FOREIGN_KEY_CHECKS=0");
    
    $seeder = new \App\Core\Seeds\DemoSeeder($db);
    $seeder->run();
    
    $db->exec("SET FOREIGN_KEY_CHECKS=1");
    echo "\nSeeder execution finished successfully!\n";
} catch (\Throwable $e) {
    $db->exec("SET FOREIGN_KEY_CHECKS=1");
    echo "\nFatal Error during seeding:\n";
    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
