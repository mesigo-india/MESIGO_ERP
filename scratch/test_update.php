<?php
declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));
define('APP_URL', 'http://localhost');
require_once APP_ROOT . '/vendor/autoload.php';
require_once APP_ROOT . '/config/environment.php';
$config = require_once APP_ROOT . '/config/config.php';
require_once APP_ROOT . '/classes/Database.php';
require_once APP_ROOT . '/classes/Company.php';

$db = \App\Core\Database::getInstance($config['database']);
$companyModel = new \App\Core\Company($db);

$existing = $companyModel->findById(2);
echo "Before update - logo_path: " . var_export($existing['logo_path'], true) . "\n";

// Simulate removal of logo
$data = $existing;
// address has to be decoded because findById returns JSON string but Company::payload encodes it again!
$addressData = json_decode($existing['address'], true) ?: [];
$data['address_line1'] = $addressData['line1'] ?? '';
$data['address_line2'] = $addressData['line2'] ?? '';
$data['city'] = $addressData['city'] ?? '';
$data['state'] = $addressData['state'] ?? '';
$data['country'] = $addressData['country'] ?? '';
$data['zip'] = $addressData['zip'] ?? '';

$data['logo_path'] = null; // Set to null (remove logo)

$success = $companyModel->update(2, $data);
echo "Update result: " . var_export($success, true) . "\n";

$updated = $companyModel->findById(2);
echo "After update - logo_path: " . var_export($updated['logo_path'], true) . "\n";
