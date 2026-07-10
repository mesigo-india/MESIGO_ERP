<?php
$db = new PDO('mysql:host=127.0.0.1;dbname=mesigo_erp;charset=utf8mb4', 'root', 'mesigo', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

echo "Fetching countries and states...\n";
$json = file_get_contents('https://countriesnow.space/api/v0.1/countries/states');
$data = json_decode($json, true);

if (!$data || $data['error']) {
    die("Failed to fetch data\n");
}

try {
    $db->exec("SET FOREIGN_KEY_CHECKS=0");
    $db->exec("TRUNCATE TABLE states");
    $db->exec("TRUNCATE TABLE countries");
    $db->exec("SET FOREIGN_KEY_CHECKS=1");

    $db->beginTransaction();

    $stmtCountry = $db->prepare("INSERT INTO countries (name, code, status) VALUES (?, ?, 1)");
    $stmtState = $db->prepare("INSERT INTO states (country_id, name, code, status) VALUES (?, ?, ?, 1)");

    foreach ($data['data'] as $country) {
        $stmtCountry->execute([$country['name'], $country['iso2'] ?? substr($country['iso3'] ?? '', 0, 2)]);
        $countryId = $db->lastInsertId();

        foreach ($country['states'] as $state) {
            $stmtState->execute([$countryId, $state['name'], $state['state_code'] ?? '']);
        }
    }
    
    $db->commit();
    echo "Successfully seeded countries and states!\n";
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    die("Error: " . $e->getMessage() . "\n");
}
