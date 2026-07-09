<?php
declare(strict_types=1);

namespace App\Core;

class CompanyController extends Controller
{
    private Company $company;
    private Validator $validator;

    public function __construct()
    {
        parent::__construct();
        require_once APP_ROOT . '/classes/Company.php';
        $this->company = new Company(Database::getInstance());
        $this->validator = new Validator();
    }

    public function index(): void
    {
        $this->requireLogin();
        $this->requirePermission('company.view');

        $this->render('company/index', [
            'title' => 'Companies',
            'companies' => $this->company->getAll(),
        ]);
    }

    public function create(): void
    {
        $this->requireLogin();
        $this->requirePermission('company.create');

        $this->render('company/form', [
            'title' => 'Add Company',
            'company' => null,
            'address' => [],
            'action' => '/company',
        ]);
    }

    public function store(): void
    {
        $this->requireLogin();
        $this->requirePermission('company.create');

        if (!$this->validateCsrf()) {
            Response::redirect('/company/create', 'Invalid security token.');
        }

        $data = $this->companyDataFromRequest();
        $errors = $this->validateCompany($data);
        if (!empty($errors)) {
            Response::redirect('/company/create', $this->formatValidationErrors($errors));
        }

        $id = $this->company->create($data);
        $this->logger->info('Company created', ['company_id' => $id]);
        Response::redirect('/company', 'Company created successfully.');
    }

    public function edit(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('company.update');

        $company = $this->company->findById((int) $id);
        if (!$company) {
            Response::redirect('/company', 'Company not found.');
        }

        $address = !empty($company['address']) ? (json_decode($company['address'], true) ?: []) : [];

        $this->render('company/form', [
            'title' => 'Edit Company',
            'company' => $company,
            'address' => $address,
            'action' => '/company/' . (int) $id,
        ]);
    }

    public function update(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('company.update');

        if (!$this->validateCsrf()) {
            Response::redirect('/company/' . (int) $id . '/edit', 'Invalid security token.');
        }

        $data = $this->companyDataFromRequest();
        $errors = $this->validateCompany($data);
        if (!empty($errors)) {
            Response::redirect('/company/' . (int) $id . '/edit', $this->formatValidationErrors($errors));
        }

        if (!$this->company->findById((int) $id)) {
            Response::redirect('/company', 'Company not found.');
        }

        $this->company->update((int) $id, $data);
        $this->logger->info('Company updated', ['company_id' => (int) $id]);
        Response::redirect('/company', 'Company updated successfully.');
    }

    public function delete(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('company.delete');

        if (!$this->validateCsrf()) {
            Response::redirect('/company', 'Invalid security token.');
        }

        $this->company->delete((int) $id);
        $this->logger->warning('Company disabled', ['company_id' => (int) $id]);
        Response::redirect('/company', 'Company disabled successfully.');
    }

    private function companyDataFromRequest(): array
    {
        return [
            'company_name' => trim((string) ($_POST['company_name'] ?? '')),
            'contact_person' => trim((string) ($_POST['contact_person'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')),
            'phone' => trim((string) ($_POST['phone'] ?? '')),
            'gst_number' => trim((string) ($_POST['gst_number'] ?? '')),
            'iec_code' => trim((string) ($_POST['iec_code'] ?? '')),
            'address_line1' => trim((string) ($_POST['address_line1'] ?? '')),
            'address_line2' => trim((string) ($_POST['address_line2'] ?? '')),
            'city' => trim((string) ($_POST['city'] ?? '')),
            'state' => trim((string) ($_POST['state'] ?? '')),
            'country' => trim((string) ($_POST['country'] ?? '')),
            'zip' => trim((string) ($_POST['zip'] ?? '')),
            'status' => (int) ($_POST['status'] ?? 1),
        ];
    }

    private function validateCompany(array $data): array
    {
        return $this->validator->validate($data, [
            'company_name' => 'required|max:255',
            'contact_person' => 'max:255',
            'email' => 'email|max:255',
            'phone' => 'max:20',
            'gst_number' => 'max:50',
            'iec_code' => 'max:50',
            'address_line1' => 'max:255',
            'address_line2' => 'max:255',
            'city' => 'max:100',
            'state' => 'max:100',
            'country' => 'max:100',
            'zip' => 'max:20',
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
}