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
            'buyer' => [], // Empty array for new form
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

        $this->render('buyers/form', [
            'title' => 'Edit Buyer',
            'buyer' => $buyer,
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
        // Maps the new professional CRM form fields to the existing model structure
        return [
            'buyer_code' => trim((string) ($_POST['buyer_code'] ?? '')),
            'company_name' => trim((string) ($_POST['company_name'] ?? '')),
            'buyer_type' => trim((string) ($_POST['buyer_type'] ?? 'Domestic')),
            'priority' => trim((string) ($_POST['priority'] ?? 'Medium')),
            'lead_source' => trim((string) ($_POST['lead_source'] ?? 'Direct')),
            'status' => (int) ($_POST['status'] ?? 1),
            'contact_person' => trim((string) ($_POST['contact_person'] ?? '')),
            'designation' => trim((string) ($_POST['designation'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')),
            'mobile' => trim((string) ($_POST['mobile'] ?? '')),
            'phone' => trim((string) ($_POST['phone'] ?? '')),
            'website' => trim((string) ($_POST['website'] ?? '')),
            'whatsapp' => trim((string) ($_POST['whatsapp'] ?? '')),
            'billing_address' => trim((string) ($_POST['billing_address'] ?? '')),
            'shipping_address' => trim((string) ($_POST['shipping_address'] ?? '')),
            'country' => trim((string) ($_POST['country'] ?? '')),
            'state' => trim((string) ($_POST['state'] ?? '')),
            'city' => trim((string) ($_POST['city'] ?? '')),
            'zip' => trim((string) ($_POST['zip'] ?? '')),
            'gst_number' => trim((string) ($_POST['gst_number'] ?? '')),
            'iec_number' => trim((string) ($_POST['iec_number'] ?? '')),
            'registration_number' => trim((string) ($_POST['registration_number'] ?? '')),
            'tax_number' => trim((string) ($_POST['tax_number'] ?? '')),
            'bank_name' => trim((string) ($_POST['bank_name'] ?? '')),
            'account_name' => trim((string) ($_POST['account_name'] ?? '')),
            'account_number' => trim((string) ($_POST['account_number'] ?? '')),
            'swift_ifsc' => trim((string) ($_POST['swift_ifsc'] ?? '')),
            'payment_terms' => trim((string) ($_POST['payment_terms'] ?? '')),
            'credit_days' => trim((string) ($_POST['credit_days'] ?? '')),
            'shipping_mode' => trim((string) ($_POST['shipping_mode'] ?? '')),
            'preferred_port' => trim((string) ($_POST['preferred_port'] ?? '')),
            'shipping_marks' => trim((string) ($_POST['shipping_marks'] ?? '')),
            'assigned_to' => trim((string) ($_POST['assigned_to'] ?? '')),
            'last_contact' => trim((string) ($_POST['last_contact'] ?? '')),
            'next_followup' => trim((string) ($_POST['next_followup'] ?? '')),
            'notes' => trim((string) ($_POST['notes'] ?? '')),
        ];
    }

    private function validateBuyer(array $data): array
    {
        return $this->validator->validate($data, [
            'buyer_code' => 'required',
            'company_name' => 'required',
            'contact_person' => 'required',
            'email' => 'required|email',
            'mobile' => 'required',
            'country' => 'required',
            'city' => 'required',
            'buyer_type' => 'required',
            'status' => 'required',
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
