<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use Exception;

class ProductMasterDataController extends Controller
{
    private PDO $db;
    private Validator $validator;
    private const PER_PAGE = 20;
    
    private const MASTERS = [
        'product-categories' => [
            'table' => 'product_categories',
            'title' => 'Product Categories',
            'singular' => 'Product Category',
            'code_field' => 'code',
            'label_field' => 'name',
            'value_field' => 'id',
            'fields' => [
                'code' => ['label' => 'Category Code', 'required' => true, 'max' => 20],
                'name' => ['label' => 'Category Name', 'required' => true, 'max' => 100],
                'parent_category_id' => ['label' => 'Parent Category', 'type' => 'select', 'lookup' => 'product-categories'],
                'commodity_group' => ['label' => 'Commodity Group', 'max' => 100],
                'hs_chapter' => ['label' => 'HS Chapter', 'max' => 10],
                'default_gst' => ['label' => 'Default GST %', 'type' => 'number', 'step' => '0.01'],
                'default_unit_id' => ['label' => 'Default Unit', 'type' => 'select', 'lookup' => 'units'],
                'default_currency_id' => ['label' => 'Default Currency', 'type' => 'select', 'lookup' => 'currencies'],
                'default_storage' => ['label' => 'Default Storage', 'max' => 150],
                'shelf_life' => ['label' => 'Shelf Life (Days)', 'type' => 'number'],
                'temperature' => ['label' => 'Storage Temperature', 'max' => 50],
                'export_allowed' => ['label' => 'Export Allowed', 'type' => 'checkbox'],
                'import_allowed' => ['label' => 'Import Allowed', 'type' => 'checkbox'],
                'default_packaging_id' => ['label' => 'Default Packaging', 'type' => 'select', 'lookup' => 'packing-types'],
                'preferred_warehouse_id' => ['label' => 'Preferred Warehouse', 'type' => 'select', 'lookup' => 'warehouses'],
                'quality_standard' => ['label' => 'Quality Standard', 'max' => 150],
                'description' => ['label' => 'Description', 'type' => 'textarea'],
                'remarks' => ['label' => 'Remarks', 'type' => 'textarea'],
                'internal_notes' => ['label' => 'Internal Notes', 'type' => 'textarea'],
                'tags' => ['label' => 'Tags', 'max' => 255]
            ],
            'soft_delete' => false
        ],
        'products' => [
            'table' => 'products',
            'title' => 'Product Master',
            'singular' => 'Product',
            'code_field' => 'product_code',
            'label_field' => 'name',
            'value_field' => 'id',
            'fields' => [
                'product_code' => ['label' => 'Product Code', 'required' => true, 'max' => 50],
                'name' => ['label' => 'Product Name', 'required' => true, 'max' => 255],
                'sku' => ['label' => 'SKU', 'required' => true, 'max' => 100],
                'scientific_name' => ['label' => 'Scientific Name', 'max' => 255],
                'trade_name' => ['label' => 'Trade Name', 'max' => 255],
                'category_id' => ['label' => 'Category', 'type' => 'select', 'lookup' => 'product-categories', 'required' => true],
                'sub_category_id' => ['label' => 'Sub Category', 'type' => 'select', 'lookup' => 'product-categories'],
                'hsn_code' => ['label' => 'HS Code', 'max' => 20],
                'gst' => ['label' => 'GST %', 'type' => 'number', 'step' => '0.01'],
                'brand' => ['label' => 'Brand', 'max' => 100],
                'country_of_origin_id' => ['label' => 'Country of Origin', 'type' => 'select', 'lookup' => 'countries'],
                'state' => ['label' => 'State', 'max' => 100],
                'district' => ['label' => 'District', 'max' => 100],
                'harvest_season' => ['label' => 'Harvest Season', 'max' => 100],
                'grade_id' => ['label' => 'Grade', 'type' => 'select', 'lookup' => 'product-grades'],
                'purity' => ['label' => 'Purity %', 'type' => 'number', 'step' => '0.01'],
                'moisture' => ['label' => 'Moisture %', 'type' => 'number', 'step' => '0.01'],
                'foreign_matter' => ['label' => 'Foreign Matter %', 'type' => 'number', 'step' => '0.01'],
                'machine_cleaned' => ['label' => 'Machine Cleaned', 'type' => 'checkbox'],
                'sortex' => ['label' => 'Sortex Cleaned', 'type' => 'checkbox'],
                'organic' => ['label' => 'Organic Certified', 'type' => 'checkbox'],
                'quality_standard' => ['label' => 'Quality Standard', 'max' => 150],
                'storage' => ['label' => 'Storage Condition', 'max' => 150],
                'shelf_life' => ['label' => 'Shelf Life (Days)', 'type' => 'number'],
                'moq' => ['label' => 'MOQ', 'type' => 'number', 'step' => '0.0001'],
                'lead_time' => ['label' => 'Lead Time (Days)', 'type' => 'number'],
                'buying_price' => ['label' => 'Buying Price', 'type' => 'number', 'step' => '0.0001'],
                'selling_price' => ['label' => 'Selling Price', 'type' => 'number', 'step' => '0.0001'],
                'default_currency_id' => ['label' => 'Default Currency', 'type' => 'select', 'lookup' => 'currencies'],
                'preferred_supplier_id' => ['label' => 'Preferred Supplier', 'type' => 'select', 'lookup' => 'suppliers'],
                'preferred_warehouse_id' => ['label' => 'Preferred Warehouse', 'type' => 'select', 'lookup' => 'warehouses'],
                'description' => ['label' => 'Product Description', 'type' => 'textarea'],
                'internal_notes' => ['label' => 'Internal Notes', 'type' => 'textarea'],
                'tags' => ['label' => 'Tags', 'max' => 255]
            ],
            'soft_delete' => true
        ],
        'product-grades' => [
            'table' => 'product_grades',
            'title' => 'Product Grades',
            'singular' => 'Product Grade',
            'code_field' => 'code',
            'label_field' => 'name',
            'value_field' => 'id',
            'fields' => [
                'code' => ['label' => 'Grade Code', 'required' => true, 'max' => 50],
                'name' => ['label' => 'Grade Name', 'required' => true, 'max' => 100],
                'product_id' => ['label' => 'Product', 'type' => 'select', 'lookup' => 'products'],
                'category_id' => ['label' => 'Category', 'type' => 'select', 'lookup' => 'product-categories'],
                'purity' => ['label' => 'Purity %', 'type' => 'number', 'step' => '0.01'],
                'moisture' => ['label' => 'Moisture %', 'type' => 'number', 'step' => '0.01'],
                'foreign_matter' => ['label' => 'Foreign Matter %', 'type' => 'number', 'step' => '0.01'],
                'broken' => ['label' => 'Broken %', 'type' => 'number', 'step' => '0.01'],
                'oil' => ['label' => 'Oil %', 'type' => 'number', 'step' => '0.01'],
                'sortex' => ['label' => 'Sortex', 'type' => 'checkbox'],
                'machine_cleaned' => ['label' => 'Machine Cleaned', 'type' => 'checkbox'],
                'steam_sterilized' => ['label' => 'Steam Sterilized', 'type' => 'checkbox'],
                'organic' => ['label' => 'Organic', 'type' => 'checkbox'],
                'color' => ['label' => 'Color', 'max' => 50],
                'aroma' => ['label' => 'Aroma', 'max' => 50],
                'packing' => ['label' => 'Packing', 'max' => 100],
                'quality_description' => ['label' => 'Quality Description', 'type' => 'textarea'],
                'description' => ['label' => 'Description', 'type' => 'textarea'],
                'internal_notes' => ['label' => 'Internal Notes', 'type' => 'textarea'],
                'tags' => ['label' => 'Tags', 'max' => 255]
            ],
            'soft_delete' => true
        ],
        'product-origins' => [
            'table' => 'product_origins',
            'title' => 'Product Origins',
            'singular' => 'Product Origin',
            'code_field' => 'code',
            'label_field' => 'name',
            'value_field' => 'id',
            'fields' => [
                'code' => ['label' => 'Origin Code', 'required' => true, 'max' => 50],
                'name' => ['label' => 'Origin Name', 'required' => true, 'max' => 100],
                'country_id' => ['label' => 'Country', 'type' => 'select', 'lookup' => 'countries'],
                'state' => ['label' => 'State', 'max' => 100],
                'district' => ['label' => 'District', 'max' => 100],
                'region' => ['label' => 'Region', 'max' => 100],
                'growing_area' => ['label' => 'Growing Area', 'max' => 150],
                'harvest_season' => ['label' => 'Harvest Season', 'max' => 100],
                'climate' => ['label' => 'Climate', 'max' => 100],
                'apeda_region' => ['label' => 'APEDA Region', 'max' => 150],
                'gi_tag' => ['label' => 'GI Tag', 'type' => 'checkbox'],
                'quality_notes' => ['label' => 'Quality Notes', 'type' => 'textarea'],
                'description' => ['label' => 'Description', 'type' => 'textarea'],
                'internal_notes' => ['label' => 'Internal Notes', 'type' => 'textarea'],
                'tags' => ['label' => 'Tags', 'max' => 255]
            ],
            'soft_delete' => true
        ],
        'units' => [
            'table' => 'units',
            'title' => 'Units',
            'singular' => 'Unit',
            'code_field' => 'code',
            'label_field' => 'name',
            'value_field' => 'id',
            'fields' => [
                'code' => ['label' => 'Short Name', 'required' => true, 'max' => 10],
                'name' => ['label' => 'Unit Name', 'required' => true, 'max' => 50],
                'base_unit' => ['label' => 'Base Unit', 'max' => 50],
                'conversion_formula' => ['label' => 'Conversion Formula', 'max' => 255],
                'decimal_precision' => ['label' => 'Decimal Precision', 'type' => 'number'],
                'description' => ['label' => 'Description', 'type' => 'textarea'],
                'internal_notes' => ['label' => 'Internal Notes', 'type' => 'textarea'],
                'tags' => ['label' => 'Tags', 'max' => 255]
            ],
            'soft_delete' => false
        ],
        'packing-types' => [
            'table' => 'packing_types',
            'title' => 'Packaging Types',
            'singular' => 'Packaging Type',
            'code_field' => 'code',
            'label_field' => 'name',
            'value_field' => 'id',
            'fields' => [
                'code' => ['label' => 'Packing Code', 'required' => true, 'max' => 20],
                'name' => ['label' => 'Packing Type', 'required' => true, 'max' => 100],
                'material' => ['label' => 'Material', 'max' => 100],
                'length' => ['label' => 'Length (mm)', 'type' => 'number', 'step' => '0.1'],
                'width' => ['label' => 'Width (mm)', 'type' => 'number', 'step' => '0.1'],
                'height' => ['label' => 'Height (mm)', 'type' => 'number', 'step' => '0.1'],
                'net_weight' => ['label' => 'Net Weight (kg)', 'type' => 'number', 'step' => '0.001'],
                'gross_weight' => ['label' => 'Gross Weight (kg)', 'type' => 'number', 'step' => '0.001'],
                'container_capacity' => ['label' => 'Container Capacity', 'type' => 'number'],
                'description' => ['label' => 'Description', 'type' => 'textarea'],
                'internal_notes' => ['label' => 'Internal Notes', 'type' => 'textarea'],
                'tags' => ['label' => 'Tags', 'max' => 255]
            ],
            'soft_delete' => false
        ],
        'countries' => [
            'table' => 'countries',
            'title' => 'Countries',
            'singular' => 'Country',
            'code_field' => 'code',
            'label_field' => 'name',
            'value_field' => 'id',
            'fields' => [
                'code' => ['label' => 'Code', 'required' => true, 'max' => 2],
                'name' => ['label' => 'Name', 'required' => true, 'max' => 100],
                'phone_code' => ['label' => 'Phone Code', 'max' => 10]
            ],
            'soft_delete' => false
        ],
        'ports' => [
            'table' => 'ports',
            'title' => 'Ports',
            'singular' => 'Port',
            'code_field' => 'code',
            'label_field' => 'name',
            'value_field' => 'id',
            'fields' => [
                'code' => ['label' => 'Port Code', 'required' => true, 'max' => 20],
                'name' => ['label' => 'Port Name', 'required' => true, 'max' => 100],
                'un_locode' => ['label' => 'UN LOCODE', 'max' => 20],
                'country_id' => ['label' => 'Country', 'type' => 'select', 'lookup' => 'countries'],
                'type' => ['label' => 'Primary Type', 'max' => 10],
                'sea_port' => ['label' => 'Sea Port', 'type' => 'checkbox'],
                'air_port' => ['label' => 'Air Port', 'type' => 'checkbox'],
                'land_port' => ['label' => 'Land Port', 'type' => 'checkbox'],
                'nearest_icd' => ['label' => 'Nearest ICD', 'max' => 100],
                'custom_office' => ['label' => 'Custom Office', 'max' => 150],
                'shipping_lines' => ['label' => 'Shipping Lines', 'type' => 'textarea'],
                'latitude' => ['label' => 'Latitude', 'type' => 'number', 'step' => '0.000001'],
                'longitude' => ['label' => 'Longitude', 'type' => 'number', 'step' => '0.000001'],
                'internal_notes' => ['label' => 'Internal Notes', 'type' => 'textarea'],
                'tags' => ['label' => 'Tags', 'max' => 255]
            ],
            'soft_delete' => false
        ],
        'currencies' => [
            'table' => 'currencies',
            'title' => 'Currencies',
            'singular' => 'Currency',
            'code_field' => 'code',
            'label_field' => 'code',
            'value_field' => 'id',
            'fields' => [
                'code' => ['label' => 'ISO Code', 'required' => true, 'max' => 3],
                'name' => ['label' => 'Currency Name', 'required' => true, 'max' => 100],
                'symbol' => ['label' => 'Symbol', 'max' => 10],
                'exchange_rate' => ['label' => 'Exchange Rate', 'type' => 'number', 'step' => '0.000001'],
                'decimal_places' => ['label' => 'Decimal Precision', 'type' => 'number'],
                'rate_source' => ['label' => 'Rate Source', 'max' => 100],
                'base_currency' => ['label' => 'Base Currency', 'type' => 'checkbox'],
                'auto_update' => ['label' => 'Auto Update Exchange Rate', 'type' => 'checkbox'],
                'internal_notes' => ['label' => 'Internal Notes', 'type' => 'textarea'],
                'tags' => ['label' => 'Tags', 'max' => 255]
            ],
            'soft_delete' => false
        ],
        'incoterms' => [
            'table' => 'incoterms',
            'title' => 'Incoterms',
            'singular' => 'Incoterm',
            'code_field' => 'code',
            'label_field' => 'code',
            'value_field' => 'id',
            'fields' => [
                'code' => ['label' => 'Code', 'required' => true, 'max' => 10],
                'name' => ['label' => 'Name', 'required' => true, 'max' => 100],
                'description' => ['label' => 'Description', 'type' => 'textarea']
            ],
            'soft_delete' => false
        ],
        'payment-terms' => [
            'table' => 'payment_terms',
            'title' => 'Payment Terms',
            'singular' => 'Payment Term',
            'code_field' => 'code',
            'label_field' => 'name',
            'value_field' => 'id',
            'fields' => [
                'code' => ['label' => 'Code', 'required' => true, 'max' => 20],
                'name' => ['label' => 'Name', 'required' => true, 'max' => 100],
                'days' => ['label' => 'Days', 'type' => 'number'],
                'description' => ['label' => 'Description', 'type' => 'textarea']
            ],
            'soft_delete' => false
        ],
        'banks' => [
            'table' => 'banks',
            'title' => 'Banks',
            'singular' => 'Bank',
            'code_field' => 'code',
            'label_field' => 'name',
            'value_field' => 'id',
            'fields' => [
                'code' => ['label' => 'Bank Code', 'required' => true, 'max' => 20],
                'name' => ['label' => 'Bank Name', 'required' => true, 'max' => 100],
                'branch' => ['label' => 'Branch', 'max' => 100],
                'ifsc_code' => ['label' => 'IFSC', 'max' => 20],
                'swift_code' => ['label' => 'SWIFT', 'max' => 20],
                'iban' => ['label' => 'IBAN', 'max' => 50],
                'account_number' => ['label' => 'Account Number', 'max' => 50],
                'account_name' => ['label' => 'Beneficiary Name', 'max' => 150],
                'correspondent_bank' => ['label' => 'Correspondent Bank', 'max' => 150],
                'currency_id' => ['label' => 'Currency', 'type' => 'select', 'lookup' => 'currencies'],
                'address' => ['label' => 'Address', 'type' => 'textarea'],
                'internal_notes' => ['label' => 'Internal Notes', 'type' => 'textarea'],
                'tags' => ['label' => 'Tags', 'max' => 255]
            ],
            'soft_delete' => false
        ],
        'warehouses' => [
            'table' => 'warehouses',
            'title' => 'Warehouses',
            'singular' => 'Warehouse',
            'code_field' => 'code',
            'label_field' => 'name',
            'value_field' => 'id',
            'fields' => [
                'code' => ['label' => 'Warehouse Code', 'required' => true, 'max' => 50],
                'name' => ['label' => 'Warehouse Name', 'required' => true, 'max' => 255],
                'warehouse_type' => ['label' => 'Warehouse Type', 'max' => 100],
                'capacity' => ['label' => 'Capacity (MT)', 'type' => 'number', 'step' => '0.01'],
                'temperature' => ['label' => 'Temperature Condition', 'max' => 50],
                'humidity' => ['label' => 'Humidity Level', 'max' => 50],
                'storage_type' => ['label' => 'Storage Type', 'max' => 100],
                'gps' => ['label' => 'GPS Coordinates', 'max' => 50],
                'manager' => ['label' => 'Warehouse Manager', 'max' => 100],
                'contact' => ['label' => 'Contact details', 'max' => 50],
                'internal_notes' => ['label' => 'Internal Notes', 'type' => 'textarea'],
                'tags' => ['label' => 'Tags', 'max' => 255]
            ],
            'soft_delete' => false
        ]
    ];

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
        $this->validator = new Validator();
    }

    public function index(string $key): void
    {
        $this->indexFor($key);
    }

    public function create(string $key): void
    {
        $this->createFor($key);
    }

    public function store(string $key): void
    {
        $this->storeFor($key);
    }

    public function edit(string $key, string $id): void
    {
        $this->editFor($key, (int)$id);
    }

    public function update(string $key, string $id): void
    {
        $this->updateFor($key, (int)$id);
    }

    public function delete(string $key, string $id): void
    {
        $this->deleteFor($key, (int)$id);
    }

    public function options(string $key): void
    {
        $this->optionsFor($key);
    }

    public function quickStore(string $key): void
    {
        $this->quickStoreFor($key);
    }

    // AI Settings Screens
    public function aiSettingsIndex(): void
    {
        $this->requireLogin();
        $this->requirePermission('settings.view');
        
        require_once APP_ROOT . '/classes/AiManager.php';
        $aiManager = new AiManager($this->db);
        $settings = $aiManager->getSettings();
        
        $this->render('product_master_data/ai_settings', [
            'title' => 'AI Configuration Panel',
            'settings' => $settings
        ]);
    }

    public function aiSettingsUpdate(): void
    {
        $this->requireLogin();
        $this->requirePermission('settings.update');
        
        if (!$this->validateCsrf()) {
            Response::redirect('/administration/ai-settings', 'Invalid security token.');
        }

        require_once APP_ROOT . '/classes/AiManager.php';
        $aiManager = new AiManager($this->db);
        
        $data = [
            'provider' => trim((string)$_POST['provider']),
            'api_key' => trim((string)$_POST['api_key']),
            'base_url' => trim((string)$_POST['base_url']),
            'model' => trim((string)$_POST['model']),
            'temperature' => (float)$_POST['temperature'],
            'max_tokens' => (int)$_POST['max_tokens'],
            'timeout' => (int)$_POST['timeout'],
            'retry_limit' => (int)$_POST['retry_limit']
        ];

        $aiManager->saveSettings($data);
        Response::redirect('/administration/ai-settings', 'AI Configuration updated successfully.');
    }

    // AI Suggestions API Endpoint
    public function suggestAi(): void
    {
        $this->requireLogin();
        header('Content-Type: application/json');
        
        $query = trim((string)($_GET['query'] ?? ''));
        $masterKey = trim((string)($_GET['master'] ?? ''));
        
        if (empty($query) || empty($masterKey)) {
            echo json_encode(['success' => false, 'message' => 'Missing query or master identifier']);
            return;
        }

        require_once APP_ROOT . '/classes/AiManager.php';
        $aiManager = new AiManager($this->db);
        $suggestions = $aiManager->getSuggestions($query, $masterKey);

        echo json_encode(['success' => true, 'suggestions' => $suggestions]);
    }

    // Idempotent CSV Import
    public function import(string $key): void
    {
        $this->requireLogin();
        $this->requirePermission('settings.create');
        
        if (!$this->validateCsrf()) {
            Response::redirect('/settings/master-data/' . $key, 'Invalid security token.');
        }

        $config = $this->config($key);
        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            Response::redirect('/settings/master-data/' . $key, 'No file uploaded or upload error occurred.');
        }

        $file = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($file, 'r');
        if ($handle === false) {
            Response::redirect('/settings/master-data/' . $key, 'Failed to open CSV file.');
        }

        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            Response::redirect('/settings/master-data/' . $key, 'Empty CSV file uploaded.');
        }

        // Clean headers mapping
        $headers = array_map('trim', $headers);
        $inserted = 0;
        $skipped = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $data = [];
            foreach ($headers as $index => $colName) {
                if (isset($row[$index])) {
                    $data[trim($colName)] = trim($row[$index]);
                }
            }

            // Map fields and apply default checks
            $mapped = [];
            foreach ($config['fields'] as $field => $meta) {
                if (isset($data[$field])) {
                    $mapped[$field] = $data[$field];
                }
            }
            
            // Check for duplicates
            if ($this->duplicateExists($config, $mapped)) {
                $skipped++;
                continue;
            }

            $this->insert($config, $mapped);
            $inserted++;
        }

        fclose($handle);
        Response::redirect('/settings/master-data/' . $key, "Import complete. Inserted: $inserted rows, Skipped: $skipped duplicates.");
    }

    // Export Master Data (CSV)
    public function export(string $key): void
    {
        $this->requireLogin();
        $this->requirePermission('settings.view');
        
        $config = $this->config($key);
        $stmt = $this->db->prepare("SELECT * FROM {$config['table']} WHERE " . implode(' AND ', $this->baseWhere($config)));
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $key . '_export_' . date('Ymd_His') . '.csv');

        $output = fopen('php://output', 'w');
        if ($rows) {
            // Header titles
            fputcsv($output, array_keys($rows[0]));
            foreach ($rows as $row) {
                fputcsv($output, $row);
            }
        }
        fclose($output);
        exit;
    }

    // Details endpoint helper
    public function details(string $key, string $id): void
    {
        $this->requireLogin();
        header('Content-Type: application/json');
        try {
            $config = $this->config($key);
            $row = $this->find($config, (int)$id);
            if (!$row) {
                echo json_encode(['success' => false, 'message' => $config['singular'] . ' not found']);
                return;
            }
            echo json_encode(['success' => true, 'data' => $row]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function indexFor(string $key): void
    {
        $this->requireLogin();
        $this->requirePermission('settings.view');
        $config = $this->config($key);
        
        $search = trim((string)($_GET['search'] ?? ''));
        $status = trim((string)($_GET['status'] ?? ''));
        $page = max(1, (int)($_GET['page'] ?? 1));
        
        $this->render('product_master_data/index', [
            'title' => $config['title'],
            'masterKey' => $key,
            'config' => $config,
            'masters' => self::MASTERS,
            'rows' => $this->rows($config, $search, $status, $page),
            'search' => $search,
            'status' => $status,
            'page' => $page,
            'totalPages' => max(1, (int)ceil($this->countRows($config, $search, $status) / self::PER_PAGE))
        ]);
    }

    private function createFor(string $key): void
    {
        $this->requireLogin();
        $this->requirePermission('settings.create');
        $config = $this->config($key);
        
        $this->render('product_master_data/form', [
            'title' => 'Add ' . $config['singular'],
            'masterKey' => $key,
            'config' => $config,
            'row' => null,
            'action' => '/settings/master-data/' . $key,
            'lookups' => $this->fetchFormLookups($config)
        ]);
    }

    private function storeFor(string $key): void
    {
        $this->requireLogin();
        $this->requirePermission('settings.create');
        
        if (!$this->validateCsrf()) {
            Response::redirect('/settings/master-data/' . $key . '/create', 'Invalid security token.');
        }

        $config = $this->config($key);
        $data = $this->dataFromRequest($config);
        $errors = $this->validateData($config, $data);
        
        if ($errors) {
            Response::redirect('/settings/master-data/' . $key . '/create', $this->formatValidationErrors($errors));
        }

        if ($this->duplicateExists($config, $data)) {
            Response::redirect('/settings/master-data/' . $key . '/create', 'Duplicate name or code already exists.');
        }

        $this->insert($config, $data);
        Response::redirect('/settings/master-data/' . $key, $config['singular'] . ' created successfully.');
    }

    private function editFor(string $key, int $id): void
    {
        $this->requireLogin();
        $this->requirePermission('settings.update');
        
        $config = $this->config($key);
        $row = $this->find($config, id: $id);
        
        if (!$row) {
            Response::redirect('/settings/master-data/' . $key, $config['singular'] . ' not found.');
        }

        $this->render('product_master_data/form', [
            'title' => 'Edit ' . $config['singular'],
            'masterKey' => $key,
            'config' => $config,
            'row' => $row,
            'action' => '/settings/master-data/' . $key . '/' . $id,
            'lookups' => $this->fetchFormLookups($config)
        ]);
    }

    private function updateFor(string $key, int $id): void
    {
        $this->requireLogin();
        $this->requirePermission('settings.update');
        
        if (!$this->validateCsrf()) {
            Response::redirect('/settings/master-data/' . $key . '/' . $id . '/edit', 'Invalid security token.');
        }

        $config = $this->config($key);
        $data = $this->dataFromRequest($config);
        $errors = $this->validateData($config, $data);
        
        if ($errors) {
            Response::redirect('/settings/master-data/' . $key . '/' . $id . '/edit', $this->formatValidationErrors($errors));
        }

        if ($this->duplicateExists($config, $data, $id)) {
            Response::redirect('/settings/master-data/' . $key . '/' . $id . '/edit', 'Duplicate name or code already exists.');
        }

        $this->updateRow($config, $id, $data);
        Response::redirect('/settings/master-data/' . $key, $config['singular'] . ' updated successfully.');
    }

    private function deleteFor(string $key, int $id): void
    {
        $this->requireLogin();
        $this->requirePermission('settings.delete');
        
        if (!$this->validateCsrf()) {
            Response::redirect('/settings/master-data/' . $key, 'Invalid security token.');
        }

        $config = $this->config($key);
        $this->disable($config, $id);
        Response::redirect('/settings/master-data/' . $key, $config['singular'] . ' disabled successfully.');
    }

    private function optionsFor(string $key): void
    {
        $this->requireLogin();
        Response::success('', ['options' => $this->optionRows($this->config($key))]);
    }

    private function quickStoreFor(string $key): void
    {
        $this->requireLogin();
        $this->requirePermission('settings.create');
        
        if (!$this->validateCsrf()) {
            Response::error('Invalid security token.', [], 403);
        }

        $config = $this->config($key);
        $data = $this->dataFromRequest($config);
        $errors = $this->validateData($config, $data);
        
        if ($errors) {
            Response::error($this->formatValidationErrors($errors), $errors, 422);
        }

        if ($this->duplicateExists($config, $data)) {
            Response::error('Duplicate name or code already exists.', [], 422);
        }

        $id = $this->insert($config, $data);
        Response::success($config['singular'] . ' created successfully.', [
            'option' => $this->formatOption($config, $this->find($config, $id) ?: ['id' => $id] + $data)
        ]);
    }

    /**
     * Fetch Lookup Dropdowns data
     */
    private function fetchFormLookups(array $config): array
    {
        $lookups = [];
        foreach ($config['fields'] as $field => $meta) {
            if (($meta['type'] ?? '') === 'select' && isset($meta['lookup'])) {
                $lookupKey = $meta['lookup'];
                if ($lookupKey === 'countries') {
                    $stmt = $this->db->query("SELECT id, name, code FROM countries ORDER BY name ASC");
                    $lookups[$field] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } elseif ($lookupKey === 'currencies') {
                    $stmt = $this->db->query("SELECT id, name, code FROM currencies ORDER BY code ASC");
                    $lookups[$field] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } elseif ($lookupKey === 'product-categories') {
                    $stmt = $this->db->query("SELECT id, name, code FROM product_categories ORDER BY name ASC");
                    $lookups[$field] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } elseif ($lookupKey === 'units') {
                    $stmt = $this->db->query("SELECT id, name, code FROM units ORDER BY name ASC");
                    $lookups[$field] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } elseif ($lookupKey === 'packing-types') {
                    $stmt = $this->db->query("SELECT id, name, code FROM packing_types ORDER BY name ASC");
                    $lookups[$field] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } elseif ($lookupKey === 'warehouses') {
                    $stmt = $this->db->query("SELECT id, name, code FROM warehouses ORDER BY name ASC");
                    $lookups[$field] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } elseif ($lookupKey === 'suppliers') {
                    $stmt = $this->db->query("SELECT id, name, code FROM suppliers ORDER BY name ASC");
                    $lookups[$field] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } elseif ($lookupKey === 'products') {
                    $stmt = $this->db->query("SELECT id, name, product_code as code FROM products ORDER BY name ASC");
                    $lookups[$field] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } elseif ($lookupKey === 'product-grades') {
                    $stmt = $this->db->query("SELECT id, name, code FROM product_grades ORDER BY name ASC");
                    $lookups[$field] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                }
            }
        }
        return $lookups;
    }

    private function rows(array $config, string $search, string $status, int $page): array
    {
        [$where, $params] = $this->filters($config, $search, $status);
        $hasCreatedAt = false;
        try {
            $descStmt = $this->db->query("DESCRIBE {$config['table']}");
            if ($descStmt) {
                $cols = $descStmt->fetchAll(PDO::FETCH_COLUMN);
                $hasCreatedAt = in_array('created_at', $cols, true);
            }
        } catch (\Throwable) {
            $hasCreatedAt = false;
        }
        $orderBy = $hasCreatedAt ? 'created_at DESC' : 'id DESC';
        $stmt = $this->db->prepare("SELECT * FROM {$config['table']} WHERE " . implode(' AND ', $where) . " ORDER BY {$orderBy} LIMIT :limit OFFSET :offset");
        $this->bindParams($stmt, $params);
        $stmt->bindValue(':limit', self::PER_PAGE, PDO::PARAM_INT);
        $stmt->bindValue(':offset', ($page - 1) * self::PER_PAGE, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function countRows(array $config, string $search, string $status): int
    {
        [$where, $params] = $this->filters($config, $search, $status);
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$config['table']} WHERE " . implode(' AND ', $where));
        $this->bindParams($stmt, $params);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    private function optionRows(array $config): array
    {
        $label = $config['label_field'];
        $stmt = $this->db->prepare("SELECT * FROM {$config['table']} WHERE " . implode(' AND ', $this->baseWhere($config)) . " AND status = 1 ORDER BY {$label} ASC");
        $stmt->execute();
        return array_map(fn(array $row): array => $this->formatOption($config, $row), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    private function formatOption(array $config, array $row): array
    {
        $label = (string)($row[$config['label_field']] ?? $row[$config['code_field']] ?? '');
        $code = (string)($row[$config['code_field']] ?? '');
        return [
            'id' => (int)($row['id'] ?? 0),
            'value' => (string)($row[$config['value_field']] ?? $row['id'] ?? ''),
            'label' => $code !== '' && $code !== $label ? $code . ' - ' . $label : $label
        ];
    }

    private function find(array $config, int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$config['table']} WHERE id = :id AND " . implode(' AND ', $this->baseWhere($config)));
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function insert(array $config, array $data): int
    {
        $data['status'] = (int)($data['status'] ?? 1);
        $columns = array_keys($data);
        $stmt = $this->db->prepare("INSERT INTO {$config['table']} (" . implode(', ', $columns) . ") VALUES (" . implode(', ', array_map(fn(string $column): string => ':' . $column, $columns)) . ")");
        $stmt->execute($data);
        return (int)$this->db->lastInsertId();
    }

    private function updateRow(array $config, int $id, array $data): bool
    {
        $data['status'] = (int)($data['status'] ?? 1);
        $sets = array_map(fn(string $column): string => "{$column} = :{$column}", array_keys($data));
        $data['id'] = $id;
        $stmt = $this->db->prepare("UPDATE {$config['table']} SET " . implode(', ', $sets) . ", updated_at = NOW() WHERE id = :id");
        return $stmt->execute($data);
    }

    private function disable(array $config, int $id): bool
    {
        $sql = $config['soft_delete'] ? "UPDATE {$config['table']} SET status = 0, deleted_at = NOW() WHERE id = :id" : "UPDATE {$config['table']} SET status = 0, updated_at = NOW() WHERE id = :id";
        return $this->db->prepare($sql)->execute(['id' => $id]);
    }

    private function dataFromRequest(array $config): array
    {
        $data = [];
        foreach ($config['fields'] as $field => $meta) {
            $type = $meta['type'] ?? 'text';
            if ($type === 'checkbox') {
                $data[$field] = isset($_POST[$field]) ? 1 : 0;
            } elseif ($type === 'number') {
                $val = trim((string)($_POST[$field] ?? ''));
                $data[$field] = $val === '' ? null : (float)$val;
            } else {
                $val = trim((string)($_POST[$field] ?? ''));
                // Category Code fallback support
                if ($field === $config['code_field'] && $val === '') {
                    $val = trim((string)($_POST['code'] ?? ''));
                }
                $data[$field] = $val === '' ? null : $val;
            }
        }
        $data['status'] = (int)($_POST['status'] ?? 1);
        return $data;
    }

    private function validateData(array $config, array $data): array
    {
        $rules = [];
        foreach ($config['fields'] as $field => $meta) {
            $fieldRules = [];
            if (!empty($meta['required'])) {
                $fieldRules[] = 'required';
            }
            if (!empty($meta['max'])) {
                $fieldRules[] = 'max:' . (int)$meta['max'];
            }
            if ($fieldRules) {
                $rules[$field] = implode('|', $fieldRules);
            }
        }
        return $this->validator->validate($data, $rules);
    }

    private function duplicateExists(array $config, array $data, ?int $exceptId = null): bool
    {
        $checks = [];
        $params = [];
        foreach ([$config['code_field'], 'name'] as $field) {
            if (array_key_exists($field, $data) && (string)$data[$field] !== '') {
                $checks[] = "{$field} = :{$field}";
                $params[$field] = $data[$field];
            }
        }
        if (!$checks) {
            return false;
        }
        $where = ['(' . implode(' OR ', $checks) . ')'];
        if ($exceptId !== null) {
            $where[] = 'id != :id';
            $params['id'] = $exceptId;
        }
        if ($config['soft_delete']) {
            $where[] = 'deleted_at IS NULL';
        }
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$config['table']} WHERE " . implode(' AND ', $where));
        $stmt->execute($params);
        return (int)$stmt->fetchColumn() > 0;
    }

    private function filters(array $config, string $search, string $status): array
    {
        $where = $this->baseWhere($config);
        $params = [];
        if ($search !== '') {
            $parts = [];
            foreach (array_keys($config['fields']) as $field) {
                $parts[] = "{$field} LIKE :search";
            }
            $where[] = '(' . implode(' OR ', $parts) . ')';
            $params['search'] = '%' . $search . '%';
        }
        if ($status !== '') {
            $where[] = 'status = :status';
            $params['status'] = (int)$status;
        }
        return [$where, $params];
    }

    private function baseWhere(array $config): array
    {
        return $config['soft_delete'] ? ['deleted_at IS NULL'] : ['1 = 1'];
    }

    private function bindParams(\PDOStatement $stmt, array $params): void
    {
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
    }

    private function config(string $key): array
    {
        if (!isset(self::MASTERS[$key])) {
            http_response_code(404);
            require APP_ROOT . '/404.php';
            exit;
        }
        return self::MASTERS[$key];
    }

    private function formatValidationErrors(array $errors): string
    {
        $messages = [];
        foreach ($errors as $fieldErrors) {
            foreach ($fieldErrors as $error) {
                $messages[] = $error;
            }
        }
        return implode(' ', $messages);
    }
}