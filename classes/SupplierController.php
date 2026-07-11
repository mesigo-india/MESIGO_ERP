<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

/**
 * SupplierController — Professional Export Supplier CRM
 */
class SupplierController extends Controller
{
    private Supplier  $suppliers;
    private Validator $validator;

    public function __construct()
    {
        parent::__construct();
        require_once __DIR__ . '/Supplier.php';
        $this->suppliers = new Supplier(Database::getInstance());
        $this->validator = new Validator();
    }

    // =========================================================================
    // LIST
    // =========================================================================

    public function index(): void
    {
        $this->requireLogin();

        // ── Filter params ─────────────────────────────────────────────────────
        $search   = trim($_GET['search']   ?? '');
        $status   = $_GET['status']   ?? '';
        $country  = trim($_GET['country']  ?? '');
        $type     = $_GET['type']     ?? '';
        $priority = $_GET['priority'] ?? '';
        $sort     = $_GET['sort']     ?? 'created_at';
        $dir      = strtoupper($_GET['dir'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';
        $page     = max(1, (int)($_GET['page'] ?? 1));
        $limit    = 50;
        $offset   = ($page - 1) * $limit;

        // ── Export: CSV / PDF / Print ───────────────────────────────────────────────
        $exportType = $_GET['export'] ?? '';
        if (in_array($exportType, ['csv', 'pdf', 'print'])) {
            $exportSuppliers = $this->suppliers->getAll($search, $status, $country, $type, $priority, $sort, $dir, 10000, 0);
            
            $this->handleExportData($exportType, 'Supplier CRM', 'Suppliers', $exportSuppliers, [
                'Code'            => 'supplier_code',
                'Company Name'    => 'company_name',
                'Contact Person'  => 'contact_person',
                'Country'         => 'country_name',
                'Phone'           => 'phone',
                'Email'           => 'email',
                'Type'            => function($s) { return ucfirst($s['supplier_type'] ?? ''); },
                'Rating'          => 'rating',
                'Status'          => function($s) { return $s['status'] == 1 ? 'Active' : 'Inactive'; }
            ]);
        }

        Session::generateCsrfToken();

        $suppliers  = $this->suppliers->getAll($search, $status, $country, $type, $priority, $sort, $dir, $limit, $offset);
        $totalRows  = $this->suppliers->count($search, $status, $country, $type, $priority);
        $totalPages = max(1, (int)ceil($totalRows / $limit));
        $stats      = $this->suppliers->getStats();
        
        $db = Database::getInstance();
        $countries  = $db->query('SELECT id, name FROM countries ORDER BY name ASC')->fetchAll(PDO::FETCH_ASSOC);

        $this->render('suppliers/index', [
            'suppliers'  => $suppliers,
            'totalRows'  => $totalRows,
            'totalPages' => $totalPages,
            'page'       => $page,
            'limit'      => $limit,
            'offset'     => $offset,
            'search'     => $search,
            'status'     => $status,
            'country'    => $country,
            'type'       => $type,
            'priority'   => $priority,
            'sort'       => $sort,
            'dir'        => $dir,
            'stats'      => $stats,
            'countries'  => $countries
        ]);
    }

    // =========================================================================
    // CREATE
    // =========================================================================

    public function create(): void
    {
        $supplier = [];
        if (!empty($_GET['duplicate_from'])) {
            $src = $this->suppliers->findById((int)$_GET['duplicate_from']);
            if ($src) {
                $supplier = $src;
                $supplier['supplier_code'] = 'COPY-' . $src['supplier_code'];
                unset($supplier['id'], $supplier['created_at'], $supplier['updated_at'], $supplier['deleted_at']);
            }
        } else {
            $supplier['supplier_code'] = $this->suppliers->getNextSupplierCode();
        }

        $this->renderForm(empty($_GET['duplicate_from']) ? 'Create Supplier' : 'Duplicate Supplier', $supplier);
    }

    public function store(): void
    {
        if (isset($_POST['ajax_action'])) {
            if ($_POST['ajax_action'] === 'add_city') {
                $this->ajaxAddCity();
            } elseif ($_POST['ajax_action'] === 'get_cities') {
                $this->ajaxGetCities();
            }
            return;
        }
        $data = $this->validateAndPrepareInput();
        if ($this->validator->hasErrors()) {
            Session::setFlash('error', 'Please fix the errors below.');
            $this->renderForm('Create Supplier', $_POST, $this->validator->getErrors());
            return;
        }

        $id = $this->suppliers->create($data);
        $this->handleRelations($id);

        Session::setFlash('success', 'Supplier created successfully.');
        $this->redirect('/suppliers');
    }

    // =========================================================================
    // UPDATE
    // =========================================================================

    public function edit(string $id): void
    {
        $supplier = $this->suppliers->findById((int)$id);
        if (!$supplier) {
            Session::setFlash('error', 'Supplier not found.');
            $this->redirect('/suppliers');
        }

        $supplier['contacts']  = $this->suppliers->getContacts((int)$id);
        $supplier['addresses'] = $this->suppliers->getAddresses((int)$id);
        $supplier['banks']     = $this->suppliers->getBankDetails((int)$id);

        $this->renderForm('Edit Supplier', $supplier);
    }

    public function update(string $id): void
    {
        if (isset($_POST['ajax_action'])) {
            if ($_POST['ajax_action'] === 'add_city') {
                $this->ajaxAddCity();
            } elseif ($_POST['ajax_action'] === 'get_cities') {
                $this->ajaxGetCities();
            }
            return;
        }
        $id = (int)$id;

        $supplier = $this->suppliers->findById($id);
        if (!$supplier) {
            Session::setFlash('error', 'Supplier not found.');
            $this->redirect('/suppliers');
        }

        $data = $this->validateAndPrepareInput($id);
        if ($this->validator->hasErrors()) {
            Session::setFlash('error', 'Please fix the errors below.');
            $_POST['id'] = $id;
            $_POST['contacts']  = $_POST['contacts']  ?? [];
            $_POST['addresses'] = $_POST['addresses'] ?? [];
            $_POST['banks']     = $_POST['banks']     ?? [];
            $this->renderForm('Edit Supplier', $_POST, $this->validator->getErrors());
            return;
        }

        $this->suppliers->update($id, $data);
        $this->handleRelations($id);

        Session::setFlash('success', 'Supplier updated successfully.');
        $this->redirect('/suppliers');
    }

    // =========================================================================
    // DELETE
    // =========================================================================

    public function delete(string $id): void
    {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== Session::get('csrf_token')) {
            Session::setFlash('error', 'Invalid token.');
            $this->redirect('/suppliers');
        }

        if ($this->suppliers->softDelete((int)$id, (int)$this->auth->id())) {
            Session::setFlash('success', 'Supplier deleted successfully.');
        } else {
            Session::setFlash('error', 'Failed to delete supplier.');
        }
        $this->redirect('/suppliers');
    }

    // =========================================================================
    // INTERNAL HELPERS
    // =========================================================================

    private function renderForm(string $title, array $data, array $errors = []): void
    {
        $db = Database::getInstance();
        
        $this->render('suppliers/form', [
            'title'     => $title,
            'supplier'  => $data,
            'errors'    => $errors,
            'countries' => $db->query('SELECT id, name FROM countries ORDER BY name ASC')->fetchAll(PDO::FETCH_ASSOC),
            'states'    => $db->query('SELECT id, country_id, name FROM states ORDER BY name ASC')->fetchAll(PDO::FETCH_ASSOC),
            'cities'    => $db->query('SELECT id, name FROM cities ORDER BY name ASC')->fetchAll(PDO::FETCH_ASSOC),
        ]);
    }

    private function validateAndPrepareInput(int $excludeId = 0): array
    {
        $v = $this->validator;
        $p = $_POST;

        $v->required('supplier_code', 'Supplier Code');
        $v->required('company_name', 'Company Name');
        $v->required('contact_person', 'Contact Person');
        $v->required('email', 'Email');

        if (!empty($p['supplier_code']) && $this->suppliers->existsByCode(trim($p['supplier_code']), $excludeId)) {
            $v->addError('supplier_code', 'This Supplier Code is already in use.');
        }
        if (!empty($p['email']) && $this->suppliers->existsByEmail(trim($p['email']), $excludeId)) {
            // Warning only - or enforce strict
            Session::setFlash('warning', 'Note: Email is already associated with another supplier.');
        }
        if (!empty($p['gst_number']) && $this->suppliers->existsByGst(trim($p['gst_number']), $excludeId)) {
            $v->addError('gst_number', 'This GST Number is already in use.');
        }

        if ($v->hasErrors()) {
            return [];
        }

        return [
            'supplier_code'      => trim($p['supplier_code']),
            'company_name'       => trim($p['company_name']),
            'contact_person'     => trim($p['contact_person']),
            'email'              => trim($p['email']),
            'phone'              => trim($p['phone'] ?? ''),
            'website'            => trim($p['website'] ?? ''),
            'gst_number'         => trim($p['gst_number'] ?? ''),
            'pan_number'         => trim($p['pan_number'] ?? ''),
            'iec_code'           => trim($p['iec_code'] ?? ''),
            'registration_number'=> trim($p['registration_number'] ?? ''),
            'fssai'              => trim($p['fssai'] ?? ''),
            'apeda'              => trim($p['apeda'] ?? ''),
            'iso'                => trim($p['iso'] ?? ''),
            'haccp'              => trim($p['haccp'] ?? ''),
            
            'supplier_type'      => in_array($p['supplier_type'] ?? '', ['domestic','international']) ? $p['supplier_type'] : 'domestic',
            'priority'           => in_array($p['priority'] ?? '', ['low','medium','high','critical']) ? $p['priority'] : 'medium',
            'rating'             => (float)($p['rating'] ?? 0),
            'status'             => isset($p['status']) ? (int)$p['status'] : 1,
            
            'is_preferred'       => !empty($p['is_preferred']) ? 1 : 0,
            'is_approved'        => !empty($p['is_approved']) ? 1 : 0,
            'is_blocked'         => !empty($p['is_blocked']) ? 1 : 0,
            
            'country_id'         => !empty($p['country_id']) ? (int)$p['country_id'] : null,
            'state_id'           => !empty($p['state_id']) ? (int)$p['state_id'] : null,
            'city_id'            => $this->resolveCityId($p['city_id'] ?? null, $p['state_id'] ?? null),
            
            'assigned_executive' => !empty($p['assigned_executive']) ? (int)$p['assigned_executive'] : null,
            'last_contact_date'  => !empty($p['last_contact_date']) ? $p['last_contact_date'] : null,
            'next_followup_date' => !empty($p['next_followup_date']) ? $p['next_followup_date'] : null,
            'remarks'            => trim($p['remarks'] ?? ''),
            
            'products_supplied'    => !empty($p['products_supplied']) ? json_encode($p['products_supplied']) : null,
            'preferred_categories' => !empty($p['preferred_categories']) ? json_encode($p['preferred_categories']) : null,
            
            'moq'                => trim($p['moq'] ?? ''),
            'lead_time_days'     => !empty($p['lead_time_days']) ? (int)$p['lead_time_days'] : null,
            'payment_terms'      => trim($p['payment_terms'] ?? ''),
            'default_currency'   => trim($p['default_currency'] ?? ''),
            'incoterm'           => trim($p['incoterm'] ?? ''),
            'default_port'       => trim($p['default_port'] ?? ''),
            'container_capacity' => trim($p['container_capacity'] ?? ''),
            
            'updated_by'         => $this->auth->id(),
        ] + ($excludeId === 0 ? ['created_by' => $this->auth->id()] : []);
    }

    private function ajaxAddCity(): void
    {
        header('Content-Type: application/json');
        
        $stateId = !empty($_POST['state_id']) ? (int)$_POST['state_id'] : 0;
        $cityName = trim($_POST['city_name'] ?? '');
        
        if (empty($stateId) || empty($cityName)) {
            echo json_encode(['success' => false, 'message' => 'State and City Name are required']);
            return;
        }
        
        try {
            $db = Database::getInstance();
            // Check if exists
            $stmt = $db->prepare('SELECT id FROM cities WHERE state_id = ? AND name = ?');
            $stmt->execute([$stateId, $cityName]);
            $existing = $stmt->fetchColumn();
            
            if ($existing) {
                echo json_encode([
                    'success' => true,
                    'city' => [
                        'id' => (int)$existing,
                        'state_id' => $stateId,
                        'name' => $cityName
                    ]
                ]);
                return;
            }
            
            $stmt = $db->prepare('INSERT INTO cities (state_id, name, status) VALUES (?, ?, 1)');
            $stmt->execute([$stateId, $cityName]);
            $cityId = (int)$db->lastInsertId();
            
            echo json_encode([
                'success' => true,
                'city' => [
                    'id' => $cityId,
                    'state_id' => $stateId,
                    'name' => $cityName
                ]
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function ajaxGetCities(): void
    {
        header('Content-Type: application/json');
        
        $stateId = !empty($_POST['state_id']) ? (int)$_POST['state_id'] : 0;
        
        if (empty($stateId)) {
            echo json_encode(['success' => true, 'cities' => []]);
            return;
        }
        
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare('SELECT id, name FROM cities WHERE state_id = ? ORDER BY name ASC');
            $stmt->execute([$stateId]);
            $cities = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'cities' => $cities]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function resolveCityId(mixed $cityValue, mixed $stateId): ?int
    {
        if (empty($cityValue)) return null;
        if (is_numeric($cityValue)) return (int)$cityValue;

        // It's a string (new city tag)
        $db = Database::getInstance();
        $stmt = $db->prepare('INSERT INTO cities (state_id, name, status) VALUES (?, ?, 1)');
        $stmt->execute([(int)($stateId ?? 0), trim((string)$cityValue)]);
        return (int)$db->lastInsertId();
    }

    private function handleRelations(int $supplierId): void
    {
        $this->suppliers->saveContacts($supplierId, $_POST['contacts'] ?? []);
        
        $addresses = $_POST['addresses'] ?? [];
        foreach ($addresses as &$addr) {
            if (isset($addr['city_id'])) {
                $addr['city_id'] = $this->resolveCityId($addr['city_id'], $addr['state_id'] ?? null);
            }
        }
        $this->suppliers->saveAddresses($supplierId, $addresses);
        
        $this->suppliers->saveBankDetails($supplierId, $_POST['banks'] ?? []);
    }

    public function details(string $id): void
    {
        $this->requireLogin();
        header('Content-Type: application/json');
        try {
            $supplier = $this->suppliers->findById((int)$id);
            if (!$supplier) {
                echo json_encode(['success' => false, 'message' => 'Supplier not found']);
                return;
            }
            echo json_encode(['success' => true, 'supplier' => $supplier]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
