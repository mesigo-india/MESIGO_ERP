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
        $this->buyers = new Buyer(Database::getInstance());
        $this->validator = new Validator();
    }

    public function index(): void
    {
        $this->requireLogin();
        
        $page = (int)($_GET['page'] ?? 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        $this->render('buyers/index', [
            'buyers' => $this->buyers->getAll($_GET['search'] ?? '', $_GET['status'] ?? '', $_GET['country'] ?? '', $_GET['type'] ?? '', $_GET['priority'] ?? '', $limit, $offset),
            'total' => $this->buyers->count($_GET['search'] ?? '', $_GET['status'] ?? '', $_GET['country'] ?? '', $_GET['type'] ?? '', $_GET['priority'] ?? '')
        ]);
    }

    public function create(): void
    {
        $this->render('buyers/form', ['buyer' => [], 'action' => '/buyers/store']);
    }

    public function store(): void
    {
        if (!$this->validateCsrf()) Response::redirect('/buyers', 'Invalid token');
        
        $data = $this->buyerDataFromRequest();
        $errors = $this->validateBuyer($data);
        
        if (!empty($errors)) {
            $this->render('buyers/form', ['buyer' => $data, 'errors' => $errors, 'action' => '/buyers/store']);
            return;
        }

        $this->buyers->create($data);
        Response::redirect('/buyers', 'Buyer created successfully.');
    }

    public function edit(string $id): void
    {
        $buyer = $this->buyers->findById((int)$id);
        $this->render('buyers/form', ['buyer' => $buyer, 'action' => '/buyers/update/' . $id]);
    }

    public function update(string $id): void
    {
        if (!$this->validateCsrf()) Response::redirect('/buyers', 'Invalid token');
        
        $data = $this->buyerDataFromRequest();
        $errors = $this->validateBuyer($data);
        
        if (!empty($errors)) {
            $this->render('buyers/form', ['buyer' => array_merge(['id' => (int)$id], $data), 'errors' => $errors, 'action' => '/buyers/update/' . $id]);
            return;
        }

        $this->buyers->update((int)$id, $data);
        Response::redirect('/buyers', 'Buyer updated successfully.');
    }

    private function buyerDataFromRequest(): array
    {
        return [
            'buyer_code' => $_POST['buyer_code'] ?? '', 'company_name' => $_POST['company_name'] ?? '',
            'buyer_type' => $_POST['buyer_type'] ?? '', 'priority' => $_POST['priority'] ?? '',
            'lead_source' => $_POST['lead_source'] ?? '', 'status' => $_POST['status'] ?? '1',
            'contact_person' => $_POST['contact_person'] ?? '', 'designation' => $_POST['designation'] ?? '',
            'email' => $_POST['email'] ?? '', 'mobile' => $_POST['mobile'] ?? '', 'phone' => $_POST['phone'] ?? '',
            'website' => $_POST['website'] ?? '', 'whatsapp' => $_POST['whatsapp'] ?? '',
            'billing_address' => $_POST['billing_address'] ?? '', 'shipping_address' => $_POST['shipping_address'] ?? '',
            'country' => $_POST['country'] ?? '', 'state' => $_POST['state'] ?? '', 'city' => $_POST['city'] ?? '', 'zip' => $_POST['zip'] ?? '',
            'gst_number' => $_POST['gst_number'] ?? '', 'iec_number' => $_POST['iec_number'] ?? '',
            'registration_number' => $_POST['registration_number'] ?? '', 'tax_number' => $_POST['tax_number'] ?? '',
            'bank_name' => $_POST['bank_name'] ?? '', 'account_name' => $_POST['account_name'] ?? '',
            'account_number' => $_POST['account_number'] ?? '', 'swift_ifsc' => $_POST['swift_ifsc'] ?? '',
            'payment_terms' => $_POST['payment_terms'] ?? '', 'credit_days' => $_POST['credit_days'] ?? '',
            'shipping_mode' => $_POST['shipping_mode'] ?? '', 'preferred_port' => $_POST['preferred_port'] ?? '',
            'shipping_marks' => $_POST['shipping_marks'] ?? '', 'assigned_to' => $_POST['assigned_to'] ?? '',
            'last_contact' => $_POST['last_contact'] ?? '', 'next_followup' => $_POST['next_followup'] ?? '', 'notes' => $_POST['notes'] ?? ''
        ];
    }

    private function validateBuyer(array $d): array
    {
        $err = [];
        if (empty($d['buyer_code'])) $err['buyer_code'] = 'Required';
        if (empty($d['company_name'])) $err['company_name'] = 'Required';
        if (empty($d['email'])) $err['email'] = 'Required';
        return $err;
    }
}
