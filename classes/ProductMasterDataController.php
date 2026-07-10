<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

class ProductMasterDataController extends Controller
{
    private PDO $db;
    private Validator $validator;
    private const PER_PAGE = 20;
    private const MASTERS = [
        'product-categories' => ['table' => 'product_categories', 'title' => 'Product Categories', 'singular' => 'Product Category', 'code_field' => 'code', 'label_field' => 'name', 'value_field' => 'id', 'fields' => ['name' => ['label' => 'Name', 'required' => true, 'max' => 100], 'code' => ['label' => 'Code', 'required' => true, 'max' => 20], 'description' => ['label' => 'Description', 'type' => 'textarea']], 'soft_delete' => false],
        'product-grades' => ['table' => 'product_grades', 'title' => 'Product Grades', 'singular' => 'Product Grade', 'code_field' => 'code', 'label_field' => 'name', 'value_field' => 'id', 'fields' => ['name' => ['label' => 'Name', 'required' => true, 'max' => 100], 'code' => ['label' => 'Code', 'required' => true, 'max' => 20], 'description' => ['label' => 'Description', 'type' => 'textarea']], 'soft_delete' => true],
        'product-origins' => ['table' => 'product_origins', 'title' => 'Product Origins', 'singular' => 'Product Origin', 'code_field' => 'code', 'label_field' => 'name', 'value_field' => 'id', 'fields' => ['name' => ['label' => 'Name', 'required' => true, 'max' => 100], 'code' => ['label' => 'Code', 'required' => true, 'max' => 20], 'description' => ['label' => 'Description', 'type' => 'textarea']], 'soft_delete' => true],
        'hs-codes' => ['table' => 'hs_codes', 'title' => 'HS Codes', 'singular' => 'HS Code', 'code_field' => 'hs_code', 'label_field' => 'hs_code', 'value_field' => 'hs_code', 'fields' => ['hs_code' => ['label' => 'HS Code', 'required' => true, 'max' => 20], 'description' => ['label' => 'Description', 'type' => 'textarea'], 'category' => ['label' => 'Category', 'max' => 255], 'duty_rate' => ['label' => 'Duty Rate', 'type' => 'number', 'step' => '0.01']], 'soft_delete' => true],
        'units' => ['table' => 'units', 'title' => 'Units', 'singular' => 'Unit', 'code_field' => 'code', 'label_field' => 'name', 'value_field' => 'id', 'fields' => ['name' => ['label' => 'Name', 'required' => true, 'max' => 50], 'code' => ['label' => 'Code', 'required' => true, 'max' => 10], 'description' => ['label' => 'Description', 'max' => 255]], 'soft_delete' => false],
        'packing-types' => ['table' => 'packing_types', 'title' => 'Packaging Types', 'singular' => 'Packaging Type', 'code_field' => 'code', 'label_field' => 'name', 'value_field' => 'id', 'fields' => ['name' => ['label' => 'Name', 'required' => true, 'max' => 100], 'code' => ['label' => 'Code', 'required' => true, 'max' => 20], 'description' => ['label' => 'Description', 'type' => 'textarea']], 'soft_delete' => false],
        'countries' => ['table' => 'countries', 'title' => 'Countries', 'singular' => 'Country', 'code_field' => 'code', 'label_field' => 'name', 'value_field' => 'id', 'fields' => ['name' => ['label' => 'Name', 'required' => true, 'max' => 100], 'code' => ['label' => 'Code', 'required' => true, 'max' => 2], 'phone_code' => ['label' => 'Phone Code', 'max' => 10]], 'soft_delete' => false],
        'ports' => ['table' => 'ports', 'title' => 'Ports', 'singular' => 'Port', 'code_field' => 'code', 'label_field' => 'name', 'value_field' => 'id', 'fields' => ['name' => ['label' => 'Name', 'required' => true, 'max' => 100], 'code' => ['label' => 'Code', 'required' => true, 'max' => 20], 'type' => ['label' => 'Type', 'max' => 10]], 'soft_delete' => false],
        'currencies' => ['table' => 'currencies', 'title' => 'Currencies', 'singular' => 'Currency', 'code_field' => 'code', 'label_field' => 'code', 'value_field' => 'id', 'fields' => ['code' => ['label' => 'Code', 'required' => true, 'max' => 3], 'name' => ['label' => 'Name', 'required' => true, 'max' => 100], 'symbol' => ['label' => 'Symbol', 'max' => 10], 'exchange_rate' => ['label' => 'Exchange Rate', 'type' => 'number', 'step' => '0.000001']], 'soft_delete' => false],
        'incoterms' => ['table' => 'incoterms', 'title' => 'Incoterms', 'singular' => 'Incoterm', 'code_field' => 'code', 'label_field' => 'code', 'value_field' => 'id', 'fields' => ['code' => ['label' => 'Code', 'required' => true, 'max' => 10], 'name' => ['label' => 'Name', 'required' => true, 'max' => 100], 'description' => ['label' => 'Description', 'type' => 'textarea']], 'soft_delete' => false],
        'payment-terms' => ['table' => 'payment_terms', 'title' => 'Payment Terms', 'singular' => 'Payment Term', 'code_field' => 'code', 'label_field' => 'name', 'value_field' => 'id', 'fields' => ['name' => ['label' => 'Name', 'required' => true, 'max' => 100], 'code' => ['label' => 'Code', 'required' => true, 'max' => 20], 'days' => ['label' => 'Days', 'type' => 'number'], 'description' => ['label' => 'Description', 'type' => 'textarea']], 'soft_delete' => false],
        'shipping-terms' => ['table' => 'shipping_terms', 'title' => 'Shipping Terms', 'singular' => 'Shipping Term', 'code_field' => 'code', 'label_field' => 'name', 'value_field' => 'name', 'fields' => ['name' => ['label' => 'Name', 'required' => true, 'max' => 100], 'code' => ['label' => 'Code', 'required' => true, 'max' => 20], 'description' => ['label' => 'Description', 'type' => 'textarea']], 'soft_delete' => true],
        'container-types' => ['table' => 'container_types', 'title' => 'Container Types', 'singular' => 'Container Type', 'code_field' => 'code', 'label_field' => 'name', 'value_field' => 'name', 'fields' => ['name' => ['label' => 'Name', 'required' => true, 'max' => 100], 'code' => ['label' => 'Code', 'required' => true, 'max' => 20], 'description' => ['label' => 'Description', 'type' => 'textarea']], 'soft_delete' => true],
        'banks' => ['table' => 'banks', 'title' => 'Banks', 'singular' => 'Bank', 'code_field' => 'code', 'label_field' => 'name', 'value_field' => 'id', 'fields' => ['name' => ['label' => 'Name', 'required' => true, 'max' => 100], 'code' => ['label' => 'Code', 'required' => true, 'max' => 20], 'branch' => ['label' => 'Branch', 'max' => 100], 'ifsc_code' => ['label' => 'IFSC Code', 'max' => 20], 'address' => ['label' => 'Address', 'type' => 'textarea']], 'soft_delete' => false],
        'warehouses' => ['table' => 'warehouses', 'title' => 'Warehouses', 'singular' => 'Warehouse', 'code_field' => 'code', 'label_field' => 'name', 'value_field' => 'id', 'fields' => ['name' => ['label' => 'Name', 'required' => true, 'max' => 255], 'code' => ['label' => 'Code', 'required' => true, 'max' => 50], 'branch_id' => ['label' => 'Branch ID', 'type' => 'number', 'required' => false]], 'soft_delete' => false],
        'cost-components' => ['table' => 'cost_components', 'title' => 'Cost Components', 'singular' => 'Cost Component', 'code_field' => 'code', 'label_field' => 'name', 'value_field' => 'id', 'fields' => ['name' => ['label' => 'Name', 'required' => true, 'max' => 100], 'code' => ['label' => 'Code', 'required' => true, 'max' => 50], 'category' => ['label' => 'Category', 'required' => true, 'max' => 50], 'calculation_type' => ['label' => 'Calculation Type', 'required' => true, 'max' => 20], 'default_value' => ['label' => 'Default Value', 'type' => 'number', 'step' => '0.01', 'required' => true], 'default_currency_id' => ['label' => 'Default Currency ID', 'type' => 'number', 'required' => true]], 'soft_delete' => false],
        'cost-templates' => ['table' => 'cost_templates', 'title' => 'Cost Templates', 'singular' => 'Cost Template', 'code_field' => 'name', 'label_field' => 'name', 'value_field' => 'id', 'fields' => ['name' => ['label' => 'Name', 'required' => true, 'max' => 100], 'description' => ['label' => 'Description', 'type' => 'textarea'], 'company_id' => ['label' => 'Company ID', 'type' => 'number', 'required' => true], 'incoterm_id' => ['label' => 'Incoterm ID', 'type' => 'number', 'required' => true], 'destination_port_id' => ['label' => 'Destination Port ID', 'type' => 'number', 'required' => false]], 'soft_delete' => false],
    ];

    public function __construct() { parent::__construct(); $this->db = Database::getInstance(); $this->validator = new Validator(); }

    public function index(string $key): void { $this->indexFor($key); }
    public function create(string $key): void { $this->createFor($key); }
    public function store(string $key): void { $this->storeFor($key); }
    public function edit(string $key, string $id): void { $this->editFor($key, (int) $id); }
    public function update(string $key, string $id): void { $this->updateFor($key, (int) $id); }
    public function delete(string $key, string $id): void { $this->deleteFor($key, (int) $id); }
    public function options(string $key): void { $this->optionsFor($key); }
    public function quickStore(string $key): void { $this->quickStoreFor($key); }

    public function categoriesIndex(): void { $this->indexFor('product-categories'); }
    public function categoriesCreate(): void { $this->createFor('product-categories'); }
    public function categoriesStore(): void { $this->storeFor('product-categories'); }
    public function categoriesEdit(string $id): void { $this->editFor('product-categories', (int) $id); }
    public function categoriesUpdate(string $id): void { $this->updateFor('product-categories', (int) $id); }
    public function categoriesDelete(string $id): void { $this->deleteFor('product-categories', (int) $id); }
    public function gradesIndex(): void { $this->indexFor('product-grades'); }
    public function gradesCreate(): void { $this->createFor('product-grades'); }
    public function gradesStore(): void { $this->storeFor('product-grades'); }
    public function gradesEdit(string $id): void { $this->editFor('product-grades', (int) $id); }
    public function gradesUpdate(string $id): void { $this->updateFor('product-grades', (int) $id); }
    public function gradesDelete(string $id): void { $this->deleteFor('product-grades', (int) $id); }
    public function originsIndex(): void { $this->indexFor('product-origins'); }
    public function originsCreate(): void { $this->createFor('product-origins'); }
    public function originsStore(): void { $this->storeFor('product-origins'); }
    public function originsEdit(string $id): void { $this->editFor('product-origins', (int) $id); }
    public function originsUpdate(string $id): void { $this->updateFor('product-origins', (int) $id); }
    public function originsDelete(string $id): void { $this->deleteFor('product-origins', (int) $id); }
    public function hsCodesIndex(): void { $this->indexFor('hs-codes'); }
    public function hsCodesCreate(): void { $this->createFor('hs-codes'); }
    public function hsCodesStore(): void { $this->storeFor('hs-codes'); }
    public function hsCodesEdit(string $id): void { $this->editFor('hs-codes', (int) $id); }
    public function hsCodesUpdate(string $id): void { $this->updateFor('hs-codes', (int) $id); }
    public function hsCodesDelete(string $id): void { $this->deleteFor('hs-codes', (int) $id); }
    public function unitsIndex(): void { $this->indexFor('units'); }
    public function unitsCreate(): void { $this->createFor('units'); }
    public function unitsStore(): void { $this->storeFor('units'); }
    public function unitsEdit(string $id): void { $this->editFor('units', (int) $id); }
    public function unitsUpdate(string $id): void { $this->updateFor('units', (int) $id); }
    public function unitsDelete(string $id): void { $this->deleteFor('units', (int) $id); }
    public function packingTypesIndex(): void { $this->indexFor('packing-types'); }
    public function packingTypesCreate(): void { $this->createFor('packing-types'); }
    public function packingTypesStore(): void { $this->storeFor('packing-types'); }
    public function packingTypesEdit(string $id): void { $this->editFor('packing-types', (int) $id); }
    public function packingTypesUpdate(string $id): void { $this->updateFor('packing-types', (int) $id); }
    public function packingTypesDelete(string $id): void { $this->deleteFor('packing-types', (int) $id); }

    private function indexFor(string $key): void
    {
        $this->requireLogin(); $this->requirePermission('settings.view');
        $config = $this->config($key); $search = trim((string) ($_GET['search'] ?? '')); $status = trim((string) ($_GET['status'] ?? '')); $page = max(1, (int) ($_GET['page'] ?? 1));
        $this->render('product_master_data/index', ['title' => $config['title'], 'masterKey' => $key, 'config' => $config, 'masters' => self::MASTERS, 'rows' => $this->rows($config, $search, $status, $page), 'search' => $search, 'status' => $status, 'page' => $page, 'totalPages' => max(1, (int) ceil($this->countRows($config, $search, $status) / self::PER_PAGE))]);
    }

    private function createFor(string $key): void { $this->requireLogin(); $this->requirePermission('settings.create'); $config = $this->config($key); $this->render('product_master_data/form', ['title' => 'Add ' . $config['singular'], 'masterKey' => $key, 'config' => $config, 'row' => null, 'action' => '/settings/master-data/' . $key]); }
    private function storeFor(string $key): void { $this->requireLogin(); $this->requirePermission('settings.create'); if (!$this->validateCsrf()) Response::redirect('/settings/master-data/' . $key . '/create', 'Invalid security token.'); $config = $this->config($key); $data = $this->dataFromRequest($config); $errors = $this->validateData($config, $data); if ($errors) Response::redirect('/settings/master-data/' . $key . '/create', $this->formatValidationErrors($errors)); if ($this->duplicateExists($config, $data)) Response::redirect('/settings/master-data/' . $key . '/create', 'Duplicate name or code already exists.'); $this->insert($config, $data); Response::redirect('/settings/master-data/' . $key, $config['singular'] . ' created successfully.'); }
    private function editFor(string $key, int $id): void { $this->requireLogin(); $this->requirePermission('settings.update'); $config = $this->config($key); $row = $this->find($config, $id); if (!$row) Response::redirect('/settings/master-data/' . $key, $config['singular'] . ' not found.'); $this->render('product_master_data/form', ['title' => 'Edit ' . $config['singular'], 'masterKey' => $key, 'config' => $config, 'row' => $row, 'action' => '/settings/master-data/' . $key . '/' . $id]); }
    private function updateFor(string $key, int $id): void { $this->requireLogin(); $this->requirePermission('settings.update'); if (!$this->validateCsrf()) Response::redirect('/settings/master-data/' . $key . '/' . $id . '/edit', 'Invalid security token.'); $config = $this->config($key); $data = $this->dataFromRequest($config); $errors = $this->validateData($config, $data); if ($errors) Response::redirect('/settings/master-data/' . $key . '/' . $id . '/edit', $this->formatValidationErrors($errors)); if ($this->duplicateExists($config, $data, $id)) Response::redirect('/settings/master-data/' . $key . '/' . $id . '/edit', 'Duplicate name or code already exists.'); $this->updateRow($config, $id, $data); Response::redirect('/settings/master-data/' . $key, $config['singular'] . ' updated successfully.'); }
    private function deleteFor(string $key, int $id): void { $this->requireLogin(); $this->requirePermission('settings.delete'); if (!$this->validateCsrf()) Response::redirect('/settings/master-data/' . $key, 'Invalid security token.'); $config = $this->config($key); $this->disable($config, $id); Response::redirect('/settings/master-data/' . $key, $config['singular'] . ' disabled successfully.'); }
    private function optionsFor(string $key): void { $this->requireLogin(); Response::success('', ['options' => $this->optionRows($this->config($key))]); }
    private function quickStoreFor(string $key): void { $this->requireLogin(); $this->requirePermission('settings.create'); if (!$this->validateCsrf()) Response::error('Invalid security token.', [], 403); $config = $this->config($key); $data = $this->dataFromRequest($config); $errors = $this->validateData($config, $data); if ($errors) Response::error($this->formatValidationErrors($errors), $errors, 422); if ($this->duplicateExists($config, $data)) Response::error('Duplicate name or code already exists.', [], 422); $id = $this->insert($config, $data); Response::success($config['singular'] . ' created successfully.', ['option' => $this->formatOption($config, $this->find($config, $id) ?: ['id' => $id] + $data)]); }

    private function rows(array $config, string $search, string $status, int $page): array {
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
    private function countRows(array $config, string $search, string $status): int { [$where, $params] = $this->filters($config, $search, $status); $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$config['table']} WHERE " . implode(' AND ', $where)); $this->bindParams($stmt, $params); $stmt->execute(); return (int) $stmt->fetchColumn(); }
    private function optionRows(array $config): array { $label = $config['label_field']; $stmt = $this->db->prepare("SELECT * FROM {$config['table']} WHERE " . implode(' AND ', $this->baseWhere($config)) . " AND status = 1 ORDER BY {$label} ASC"); $stmt->execute(); return array_map(fn(array $row): array => $this->formatOption($config, $row), $stmt->fetchAll(PDO::FETCH_ASSOC)); }
    private function formatOption(array $config, array $row): array { $label = (string) ($row[$config['label_field']] ?? $row[$config['code_field']] ?? ''); $code = (string) ($row[$config['code_field']] ?? ''); return ['id' => (int) ($row['id'] ?? 0), 'value' => (string) ($row[$config['value_field']] ?? $row['id'] ?? ''), 'label' => $code !== '' && $code !== $label ? $code . ' - ' . $label : $label]; }
    private function find(array $config, int $id): ?array { $stmt = $this->db->prepare("SELECT * FROM {$config['table']} WHERE id = :id AND " . implode(' AND ', $this->baseWhere($config))); $stmt->execute(['id' => $id]); $row = $stmt->fetch(PDO::FETCH_ASSOC); return $row ?: null; }
    private function insert(array $config, array $data): int { $data['status'] = (int) ($data['status'] ?? 1); $columns = array_keys($data); $stmt = $this->db->prepare("INSERT INTO {$config['table']} (" . implode(', ', $columns) . ") VALUES (" . implode(', ', array_map(fn(string $column): string => ':' . $column, $columns)) . ")"); $stmt->execute($data); return (int) $this->db->lastInsertId(); }
    private function updateRow(array $config, int $id, array $data): bool { $data['status'] = (int) ($data['status'] ?? 1); $sets = array_map(fn(string $column): string => "{$column} = :{$column}", array_keys($data)); $data['id'] = $id; $stmt = $this->db->prepare("UPDATE {$config['table']} SET " . implode(', ', $sets) . ", updated_at = NOW() WHERE id = :id"); return $stmt->execute($data); }
    private function disable(array $config, int $id): bool { $sql = $config['soft_delete'] ? "UPDATE {$config['table']} SET status = 0, deleted_at = NOW() WHERE id = :id" : "UPDATE {$config['table']} SET status = 0, updated_at = NOW() WHERE id = :id"; return $this->db->prepare($sql)->execute(['id' => $id]); }
    private function dataFromRequest(array $config): array { $data = []; foreach ($config['fields'] as $field => $meta) { $value = trim((string) ($_POST[$field] ?? '')); if ($field === $config['code_field'] && $value === '') $value = trim((string) ($_POST['code'] ?? '')); if ($field === 'type' && $value === '') $value = 'sea'; if ($field === 'exchange_rate' && $value === '') $value = '1'; $data[$field] = ($meta['type'] ?? '') === 'number' ? ($value === '' ? null : (float) $value) : $value; } $data['status'] = (int) ($_POST['status'] ?? 1); return $data; }
    private function validateData(array $config, array $data): array { $rules = []; foreach ($config['fields'] as $field => $meta) { $fieldRules = []; if (!empty($meta['required'])) $fieldRules[] = 'required'; if (!empty($meta['max'])) $fieldRules[] = 'max:' . (int) $meta['max']; if ($fieldRules) $rules[$field] = implode('|', $fieldRules); } return $this->validator->validate($data, $rules); }
    private function duplicateExists(array $config, array $data, ?int $exceptId = null): bool { $checks = []; $params = []; foreach ([$config['code_field'], 'name'] as $field) { if (array_key_exists($field, $data) && (string) $data[$field] !== '') { $checks[] = "{$field} = :{$field}"; $params[$field] = $data[$field]; } } if (!$checks) return false; $where = ['(' . implode(' OR ', $checks) . ')']; if ($exceptId !== null) { $where[] = 'id != :id'; $params['id'] = $exceptId; } if ($config['soft_delete']) $where[] = 'deleted_at IS NULL'; $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$config['table']} WHERE " . implode(' AND ', $where)); $stmt->execute($params); return (int) $stmt->fetchColumn() > 0; }
    private function filters(array $config, string $search, string $status): array { $where = $this->baseWhere($config); $params = []; if ($search !== '') { $parts = []; foreach (array_keys($config['fields']) as $field) $parts[] = "{$field} LIKE :search"; $where[] = '(' . implode(' OR ', $parts) . ')'; $params['search'] = '%' . $search . '%'; } if ($status !== '') { $where[] = 'status = :status'; $params['status'] = (int) $status; } return [$where, $params]; }
    private function baseWhere(array $config): array { return $config['soft_delete'] ? ['deleted_at IS NULL'] : ['1 = 1']; }
    private function bindParams(\PDOStatement $stmt, array $params): void { foreach ($params as $key => $value) $stmt->bindValue(':' . $key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR); }
    private function config(string $key): array { if (!isset(self::MASTERS[$key])) { http_response_code(404); require APP_ROOT . '/404.php'; exit; } return self::MASTERS[$key]; }
    private function formatValidationErrors(array $errors): string { $messages = []; foreach ($errors as $fieldErrors) foreach ($fieldErrors as $error) $messages[] = $error; return implode(' ', $messages); }
}