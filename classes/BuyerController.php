<?php
declare(strict_types=1);

namespace App\Core;

class BuyerController extends Controller
{
    private Buyer $buyers;
    private Validator $validator;

    public function __construct()
    {
        parent::__construct();
        require_once APP_ROOT . '/classes/Buyer.php';
        $this->buyers = new Buyer(Database::getInstance());
        $this->validator = new Validator();
    }

    public function index(): void
    {
        $this->requireLogin();
        $this->requirePermission('buyers.view');

        $search = trim((string) ($_GET['search'] ?? ''));
        $status = trim((string) ($_GET['status'] ?? ''));

        $this->render('buyers/index', [
            'title' => 'Buyers',
            'buyers' => $this->buyers->getAll($search, $status),
            'search' => $search,
            'status' => $status,
        ]);
    }

    public function create(): void
    {
        $this->requireLogin();
        $this->requirePermission('buyers.create');

        $this->render('buyers/form', [
            'title' => 'Add Buyer',
            'buyer' => null,
            'profile' => [],
            'contacts' => [[]],
            'addresses' => [[]],
            'action' => '/buyers',
        ]);
    }

    public function store(): void
    {
        $this->requireLogin();
        $this->requirePermission('buyers.create');

        if (!$this->validateCsrf()) {
            Response::redirect('/buyers/create', 'Invalid security token.');
        }

        $data = $this->buyerDataFromRequest();
        $data['created_by'] = $this->currentUserId();

        $errors = $this->validateBuyer($data);
        if (!empty($errors)) {
            Response::redirect('/buyers/create', $this->formatValidationErrors($errors));
        }

        if ($this->buyers->findByCode($data['buyer_code'])) {
            Response::redirect('/buyers/create', 'Buyer code already exists.');
        }

        $id = $this->buyers->create($data);
        $this->logger->info('Buyer created', ['buyer_id' => $id]);
        Response::redirect('/buyers', 'Buyer created successfully.');
    }

    public function edit(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('buyers.update');

        $buyer = $this->buyers->findById((int) $id);
        if (!$buyer) {
            Response::redirect('/buyers', 'Buyer not found.');
        }

        $profile = !empty($buyer['address']) ? (json_decode($buyer['address'], true) ?: []) : [];
        $contacts = $this->buyers->getContacts((int) $id);
        $addresses = $this->buyers->getAddresses((int) $id);

        $this->render('buyers/form', [
            'title' => 'Edit Buyer',
            'buyer' => $buyer,
            'profile' => $profile,
            'contacts' => !empty($contacts) ? $contacts : [[]],
            'addresses' => !empty($addresses) ? $addresses : [[]],
            'action' => '/buyers/' . (int) $id,
        ]);
    }

    public function update(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('buyers.update');

        if (!$this->validateCsrf()) {
            Response::redirect('/buyers/' . (int) $id . '/edit', 'Invalid security token.');
        }

        $buyer = $this->buyers->findById((int) $id);
        if (!$buyer) {
            Response::redirect('/buyers', 'Buyer not found.');
        }

        $data = $this->buyerDataFromRequest();
        $data['updated_by'] = $this->currentUserId();

        $errors = $this->validateBuyer($data);
        if (!empty($errors)) {
            Response::redirect('/buyers/' . (int) $id . '/edit', $this->formatValidationErrors($errors));
        }

        $existing = $this->buyers->findByCode($data['buyer_code']);
        if ($existing && (int) $existing['id'] !== (int) $id) {
            Response::redirect('/buyers/' . (int) $id . '/edit', 'Buyer code already exists.');
        }

        $this->buyers->update((int) $id, $data);
        $this->logger->info('Buyer updated', ['buyer_id' => (int) $id]);
        Response::redirect('/buyers', 'Buyer updated successfully.');
    }

    public function delete(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('buyers.delete');

        if (!$this->validateCsrf()) {
            Response::redirect('/buyers', 'Invalid security token.');
        }

        $this->buyers->delete((int) $id, $this->currentUserId());
        $this->logger->warning('Buyer disabled', ['buyer_id' => (int) $id]);
        Response::redirect('/buyers', 'Buyer disabled successfully.');
    }

    private function buyerDataFromRequest(): array
    {
        return [
            'buyer_code' => trim((string) ($_POST['buyer_code'] ?? '')),
            'company_name' => trim((string) ($_POST['company_name'] ?? '')),
            'contact_person' => trim((string) ($_POST['contact_person'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')),
            'phone' => trim((string) ($_POST['phone'] ?? '')),
            'gst_number' => trim((string) ($_POST['gst_number'] ?? '')),
            'iec_number' => trim((string) ($_POST['iec_number'] ?? '')),
            'country_id' => (int) ($_POST['country_id'] ?? 0),
            'state_id' => (int) ($_POST['state_id'] ?? 0),
            'city_id' => (int) ($_POST['city_id'] ?? 0),
            'status' => (int) ($_POST['status'] ?? 1),
            'profile' => [
                'billing_address' => trim((string) ($_POST['billing_address'] ?? '')),
                'shipping_address' => trim((string) ($_POST['shipping_address'] ?? '')),
                'bank_name' => trim((string) ($_POST['bank_name'] ?? '')),
                'bank_account_name' => trim((string) ($_POST['bank_account_name'] ?? '')),
                'bank_account_number' => trim((string) ($_POST['bank_account_number'] ?? '')),
                'bank_swift_code' => trim((string) ($_POST['bank_swift_code'] ?? '')),
                'payment_terms' => trim((string) ($_POST['payment_terms'] ?? '')),
                'credit_days' => trim((string) ($_POST['credit_days'] ?? '')),
                'preferred_shipping_mode' => trim((string) ($_POST['preferred_shipping_mode'] ?? '')),
                'preferred_port' => trim((string) ($_POST['preferred_port'] ?? '')),
                'shipping_marks' => trim((string) ($_POST['shipping_marks'] ?? '')),
            ],
            'contacts' => $this->contactsFromRequest(),
            'addresses' => $this->addressesFromRequest(),
        ];
    }

    private function contactsFromRequest(): array
    {
        $contacts = [];
        foreach ($_POST['contact_name'] ?? [] as $index => $name) {
            $contacts[] = [
                'name' => trim((string) $name),
                'designation' => trim((string) ($_POST['contact_designation'][$index] ?? '')),
                'email' => trim((string) ($_POST['contact_email'][$index] ?? '')),
                'phone' => trim((string) ($_POST['contact_phone'][$index] ?? '')),
                'mobile' => trim((string) ($_POST['contact_mobile'][$index] ?? '')),
                'is_primary' => (int) ($_POST['contact_primary'][$index] ?? 0),
            ];
        }

        return $contacts;
    }

    private function addressesFromRequest(): array
    {
        $addresses = [];
        foreach ($_POST['address_type'] ?? [] as $index => $type) {
            $addresses[] = [
                'address_type' => trim((string) $type),
                'address' => trim((string) ($_POST['address_text'][$index] ?? '')),
                'country_id' => (int) ($_POST['address_country_id'][$index] ?? 0),
                'state_id' => (int) ($_POST['address_state_id'][$index] ?? 0),
                'city_id' => (int) ($_POST['address_city_id'][$index] ?? 0),
                'zip' => trim((string) ($_POST['address_zip'][$index] ?? '')),
                'is_default' => (int) ($_POST['address_default'][$index] ?? 0),
            ];
        }

        return $addresses;
    }

    private function validateBuyer(array $data): array
    {
        return $this->validator->validate($data, [
            'buyer_code' => 'required|max:50',
            'company_name' => 'required|max:255',
            'contact_person' => 'max:255',
            'email' => 'email|max:255',
            'phone' => 'max:20',
            'gst_number' => 'max:50',
            'iec_number' => 'max:50',
        ]);
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

    private function currentUserId(): ?int
    {
        $user = $this->auth->user();
        return $user ? (int) $user['id'] : null;
    }
}