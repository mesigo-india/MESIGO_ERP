<?php
declare(strict_types=1);

namespace App\Core;

/**
 * BuyerController — Professional Export Buyer CRM
 */
class BuyerController extends Controller
{
    private Buyer     $buyers;
    private Validator $validator;

    public function __construct()
    {
        parent::__construct();
        require_once __DIR__ . '/Buyer.php';
        $this->buyers    = new Buyer(Database::getInstance());
        $this->validator = new Validator();
    }

    // =========================================================================
    // LIST
    // =========================================================================

    public function index(): void
    {
        $this->requireLogin();

        // ── Filter params ─────────────────────────────────────────────────────
        $search      = trim($_GET['search']      ?? '');
        $status      = $_GET['status']      ?? '';
        $country     = trim($_GET['country']     ?? '');
        $type        = $_GET['type']        ?? '';
        $priority    = $_GET['priority']    ?? '';
        $lead_source = $_GET['lead_source'] ?? '';
        $lead_status = $_GET['lead_status'] ?? '';
        $sort        = $_GET['sort']        ?? 'created_at';
        $dir         = strtoupper($_GET['dir'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';
        $page        = max(1, (int)($_GET['page'] ?? 1));
        $limit       = 50;
        $offset      = ($page - 1) * $limit;

        // ── Export: CSV / PDF / Print ───────────────────────────────────────────────
        $exportType = $_GET['export'] ?? '';
        if (in_array($exportType, ['csv', 'pdf', 'print'])) {
            $exportBuyers = $this->buyers->getAll(
                $search, $status, $country, $type, $priority,
                $lead_source, $lead_status, $sort, $dir, 10000, 0
            );
            $this->handleExportData($exportType, 'Buyer CRM', 'Buyers', $exportBuyers, [
                'Buyer Code'        => 'buyer_code',
                'Company Name'      => 'company_name',
                'Country'           => 'country_name',
                'Contact Person'    => 'contact_person',
                'Email'             => 'email',
                'Mobile'            => 'mobile',
                'Buyer Type'        => 'buyer_type',
                'Priority'          => 'priority',
                'Lead Status'       => 'lead_status',
                'Lead Source'       => 'lead_source',
                'Payment Terms'     => 'payment_terms',
                'Credit Days'       => 'credit_days',
                'Preferred Incoterm'=> 'preferred_incoterm',
                'Assigned To'       => 'assigned_to',
                'Last Contact'      => 'last_contact',
                'Next Follow-up'    => 'next_followup',
                'Customer Since'    => 'customer_since',
                'Status'            => function($b) { return $b['status'] == 1 ? 'Active' : 'Inactive'; }
            ]);
        }

        // ── Generate fresh CSRF token for delete forms in the table ───────────
        Session::generateCsrfToken();

        // ── Data ──────────────────────────────────────────────────────────────
        $buyers     = $this->buyers->getAll(
            $search, $status, $country, $type, $priority,
            $lead_source, $lead_status, $sort, $dir, $limit, $offset
        );
        $total      = $this->buyers->count(
            $search, $status, $country, $type, $priority, $lead_source, $lead_status
        );
        $stats      = $this->buyers->getStats();
        $totalPages = max(1, (int)ceil($total / $limit));

        $this->render('buyers/index', [
            'title'       => 'Buyer CRM',
            'buyers'      => $buyers,
            'total'       => $total,
            'stats'       => $stats,
            'page'        => $page,
            'limit'       => $limit,
            'totalPages'  => $totalPages,
            'offset'      => $offset,
            'sort'        => $sort,
            'dir'         => $dir,
        ]);
    }

    // =========================================================================
    // CREATE
    // =========================================================================

    public function create(): void
    {
        $this->requireLogin();
        Session::generateCsrfToken();

        $buyer = [];
        if (!empty($_GET['duplicate_from'])) {
            $src = $this->buyers->findById((int)$_GET['duplicate_from']);
            if ($src) {
                $buyer = $src;
                $buyer['buyer_code'] = 'COPY-' . $src['buyer_code'];
                unset($buyer['id'], $buyer['created_at'], $buyer['updated_at'],
                      $buyer['deleted_at'], $buyer['created_by'], $buyer['updated_by'], $buyer['deleted_by']);
            }
        } else {
            $buyer['buyer_code'] = $this->buyers->getNextBuyerCode();
        }

        $this->renderForm(empty($_GET['duplicate_from']) ? 'Add New Buyer' : 'Duplicate Buyer', $buyer);
    }

    // =========================================================================
    // STORE
    // =========================================================================

    public function store(): void
    {
        $this->requireLogin();

        if (isset($_POST['ajax_action'])) {
            if ($_POST['ajax_action'] === 'add_city') {
                $this->ajaxAddCity();
            } elseif ($_POST['ajax_action'] === 'get_cities') {
                $this->ajaxGetCities();
            }
            return;
        }

        // ── CSV Import ────────────────────────────────────────────────────────
        if (($_POST['_action'] ?? '') === 'import') {
            $this->handleCsvImport();
            return;
        }

        if (!$this->validateCsrf()) {
            Session::generateCsrfToken();
            Session::setFlash('error', 'Security token expired. Please resubmit the form.');
            $this->renderForm('Add New Buyer', $_POST);
            return;
        }

        $data = $this->validateAndPrepareInput();
        if ($this->validator->hasErrors()) {
            Session::setFlash('error', 'Please fix the errors below.');
            $this->renderForm('Add New Buyer', $_POST, $this->validator->getErrors());
            return;
        }

        try {
            $id = $this->buyers->create($data);
            if ($id > 0) {
                Session::setFlash('success', 'Buyer created successfully.');
                $this->redirect('/buyers');
            } else {
                throw new \RuntimeException('INSERT did not return a valid ID.');
            }
        } catch (\Exception $e) {
            Session::setFlash('error', 'Database error: ' . $e->getMessage());
            $this->renderForm('Add New Buyer', $data);
        }
    }

    // =========================================================================
    // EDIT
    // =========================================================================

    public function edit(string $id): void
    {
        $this->requireLogin();

        $buyer = $this->buyers->findById((int)$id);
        if (!$buyer) {
            Session::setFlash('error', 'Buyer not found.');
            $this->redirect('/buyers');
        }

        Session::generateCsrfToken();
        $this->renderForm('Edit Buyer: ' . ($buyer['company_name'] ?? ''), $buyer);
    }

    // =========================================================================
    // UPDATE
    // =========================================================================

    public function update(string $id): void
    {
        $this->requireLogin();

        if (isset($_POST['ajax_action'])) {
            if ($_POST['ajax_action'] === 'add_city') {
                $this->ajaxAddCity();
            } elseif ($_POST['ajax_action'] === 'get_cities') {
                $this->ajaxGetCities();
            }
            return;
        }

        if (!$this->validateCsrf()) {
            Session::generateCsrfToken();
            Session::setFlash('error', 'Security token expired. Please resubmit the form.');
            $this->renderForm('Edit Buyer', $_POST);
            return;
        }

        $data = $this->validateAndPrepareInput((int)$id);
        if ($this->validator->hasErrors()) {
            Session::setFlash('error', 'Please fix the errors below.');
            $_POST['id'] = $id;
            $this->renderForm('Edit Buyer', $_POST, $this->validator->getErrors());
            return;
        }

        try {
            $this->buyers->update((int)$id, $data);
            Session::setFlash('success', 'Buyer updated successfully.');
            $this->redirect('/buyers');
        } catch (\Exception $e) {
            Session::setFlash('error', 'Update failed: ' . $e->getMessage());
            $_POST['id'] = $id;
            $this->renderForm('Edit Buyer', $_POST);
        }
    }

    // =========================================================================
    // DELETE
    // =========================================================================

    public function delete(string $id): void
    {
        $this->requireLogin();

        if (!$this->validateCsrf()) {
            Session::setFlash('error', 'Security token expired.');
            $this->redirect('/buyers');
        }

        try {
            $this->buyers->softDelete((int)$id);
            Session::setFlash('success', 'Buyer deleted successfully.');
        } catch (\Exception $e) {
            Session::setFlash('error', 'Delete failed: ' . $e->getMessage());
        }
        $this->redirect('/buyers');
    }

    // =========================================================================
    // PRIVATE: CSV IMPORT
    // =========================================================================

    private function handleCsvImport(): void
    {
        if (!$this->validateCsrf()) {
            Response::redirect('/buyers', 'Security token expired.');
            return;
        }

        if (empty($_FILES['csv_file']['tmp_name'])) {
            Response::redirect('/buyers', 'No file uploaded.');
            return;
        }

        $file = $_FILES['csv_file']['tmp_name'];
        $f    = fopen($file, 'r');
        if (!$f) {
            Response::redirect('/buyers', 'Could not read the uploaded file.');
            return;
        }

        // Skip header row
        $header = fgetcsv($f);
        $imported = 0;
        $skipped  = 0;

        while (($row = fgetcsv($f)) !== false) {
            if (count($row) < 5) { $skipped++; continue; }
            $data = [
                'buyer_code'    => trim($row[0] ?? ''),
                'company_name'  => trim($row[1] ?? ''),
                'country'       => trim($row[2] ?? ''),
                'contact_person'=> trim($row[3] ?? ''),
                'email'         => trim($row[4] ?? ''),
                'mobile'        => trim($row[5] ?? ''),
                'buyer_type'    => trim($row[6] ?? ''),
                'priority'      => trim($row[7] ?? 'Medium'),
                'status'        => 1,
            ];

            if (empty($data['buyer_code']) || empty($data['company_name'])) {
                $skipped++;
                continue;
            }

            if ($this->buyers->existsByCode($data['buyer_code'])) {
                $skipped++;
                continue;
            }

            try {
                $this->buyers->create($data);
                $imported++;
            } catch (\Exception $e) {
                $skipped++;
            }
        }
        fclose($f);

        Response::redirect('/buyers', "Import complete. {$imported} buyers imported, {$skipped} skipped.");
    }

    // =========================================================================
    // PRIVATE: DATA MAPPING
    // =========================================================================

    private function renderForm(string $title, array $data, array $errors = []): void
    {
        $db = Database::getInstance();
        
        $this->render('buyers/form', [
            'title'     => $title,
            'buyer'     => $data,
            'errors'    => $errors,
            'action'    => empty($data['id']) ? '/buyers' : '/buyers/' . $data['id'],
            'countries' => $db->query('SELECT id, name FROM countries ORDER BY name ASC')->fetchAll(\PDO::FETCH_ASSOC),
            'states'    => $db->query('SELECT id, country_id, name FROM states ORDER BY name ASC')->fetchAll(\PDO::FETCH_ASSOC),
            'cities'    => $db->query('SELECT id, name FROM cities ORDER BY name ASC')->fetchAll(\PDO::FETCH_ASSOC),
        ]);
    }

    private function validateAndPrepareInput(int $excludeId = 0): array
    {
        $v = $this->validator;
        $p = $_POST;

        $v->required('buyer_code', 'Buyer Code');
        $v->required('company_name', 'Company Name');
        $v->required('contact_person', 'Contact Person');
        $v->required('email', 'Email');
        $v->required('mobile', 'Mobile');

        if (!empty($p['email']) && !filter_var($p['email'], FILTER_VALIDATE_EMAIL)) {
            $v->addError('email', 'Please enter a valid email address.');
        }

        if (!empty($p['buyer_code']) && $this->buyers->existsByCode(trim($p['buyer_code']), $excludeId)) {
            $v->addError('buyer_code', 'This buyer code is already in use.');
        }

        if (!empty($p['email']) && $this->buyers->existsByEmail(trim($p['email']), $excludeId)) {
            Session::setFlash('warning', 'Warning: This email address belongs to another buyer.');
        }

        if ($v->hasErrors()) {
            return [];
        }

        return [
            // Company
            'buyer_code'                => trim($p['buyer_code']),
            'company_name'              => trim($p['company_name']),
            'buyer_type'                => $p['buyer_type']                     ?? '',
            'status'                    => $p['status']                         ?? '1',
            'customer_since'            => $p['customer_since']                 ?? '',
            // CRM
            'lead_source'               => $p['lead_source']                    ?? '',
            'lead_status'               => $p['lead_status']                    ?? 'New Lead',
            'priority'                  => $p['priority']                       ?? '',
            'assigned_to'               => trim($p['assigned_to']               ?? ''),
            'last_contact'              => $p['last_contact']                   ?? '',
            'next_followup'             => $p['next_followup']                  ?? '',
            // Contact
            'contact_person'            => trim($p['contact_person']),
            'designation'               => trim($p['designation']               ?? ''),
            'email'                     => trim($p['email']),
            'mobile'                    => trim($p['mobile']),
            'phone'                     => trim($p['phone']                     ?? ''),
            'website'                   => trim($p['website']                   ?? ''),
            'whatsapp'                  => trim($p['whatsapp']                  ?? ''),
            // Address
            'billing_address'           => trim($p['billing_address']           ?? ''),
            'shipping_address'          => trim($p['shipping_address']          ?? ''),
            'country_id'                => !empty($p['country_id']) ? (int)$p['country_id'] : null,
            'state_id'                  => !empty($p['state_id']) ? (int)$p['state_id'] : null,
            'city_id'                   => $this->resolveCityId($p['city_id'] ?? null, $p['state_id'] ?? null),
            'zip'                       => trim($p['zip']                       ?? ''),
            // Business / Compliance
            'gst_number'                => trim($p['gst_number']                ?? ''),
            'iec_number'                => trim($p['iec_number']                ?? ''),
            'registration_number'       => trim($p['registration_number']       ?? ''),
            'tax_number'                => trim($p['tax_number']                ?? ''),
            // Bank
            'bank_name'                 => trim($p['bank_name']                 ?? ''),
            'account_name'              => trim($p['account_name']              ?? ''),
            'account_number'            => trim($p['account_number']            ?? ''),
            'swift_ifsc'                => trim($p['swift_ifsc']                ?? ''),
            // Payment
            'payment_terms'             => $p['payment_terms']                  ?? '',
            'credit_days'               => $p['credit_days']                    ?? '',
            'preferred_currency'        => $p['preferred_currency']             ?? '',
            'preferred_incoterm'        => $p['preferred_incoterm']             ?? '',
            // Shipping
            'shipping_mode'             => $p['shipping_mode']                  ?? '',
            'preferred_port'            => trim($p['preferred_port']            ?? ''),
            'preferred_destination_port'=> trim($p['preferred_destination_port']?? ''),
            'preferred_container'       => $p['preferred_container']            ?? '',
            'preferred_packing'         => trim($p['preferred_packing']         ?? ''),
            'shipping_marks'            => trim($p['shipping_marks']            ?? ''),
            // Export Business
            'preferred_products'        => trim($p['preferred_products']        ?? ''),
            'import_countries'          => trim($p['import_countries']          ?? ''),
            // Notes
            'notes'                     => trim($p['notes']                     ?? ''),
        ];
    }

    // =========================================================================
    // AJAX ENDPOINTS
    // =========================================================================
    
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
            $cities = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'cities' => $cities]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
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
        } catch (\Exception $e) {
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

    public function details(string $id): void
    {
        $this->requireLogin();
        header('Content-Type: application/json');
        try {
            $buyer = $this->buyers->findById((int)$id);
            if (!$buyer) {
                echo json_encode(['success' => false, 'message' => 'Buyer not found']);
                return;
            }
            echo json_encode(['success' => true, 'buyer' => $buyer]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
