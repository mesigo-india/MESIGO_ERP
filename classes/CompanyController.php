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

        // Handle file uploads
        $data['logo_path'] = $this->handleFileUpload('logo_file', 'logo');
        $data['stamp_path'] = $this->handleFileUpload('stamp_file', 'stamp');
        $data['seal_path'] = $this->handleFileUpload('seal_file', 'seal');
        $data['signature_path'] = $this->handleFileUpload('signature_file', 'sig');
        $data['digital_signature_path'] = $this->handleFileUpload('digital_signature_file', 'dig_sig');
        $data['letterhead_path'] = $this->handleFileUpload('letterhead_file', 'lh');
        $data['letterhead_export_path'] = $this->handleFileUpload('letterhead_export_file', 'lh_exp');
        $data['letterhead_domestic_path'] = $this->handleFileUpload('letterhead_domestic_file', 'lh_dom');

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

        $existing = $this->company->findById((int) $id);
        if (!$existing) {
            Response::redirect('/company', 'Company not found.');
        }

        $data = $this->companyDataFromRequest();
        $errors = $this->validateCompany($data);
        if (!empty($errors)) {
            Response::redirect('/company/' . (int) $id . '/edit', $this->formatValidationErrors($errors));
        }

        // Handle file uploads, retaining old values if no new files uploaded, or nullifying if removal is requested
        if (isset($_POST['remove_logo']) && $_POST['remove_logo'] === '1') {
            if (!empty($existing['logo_path']) && file_exists(APP_ROOT . '/uploads/' . $existing['logo_path'])) {
                @unlink(APP_ROOT . '/uploads/' . $existing['logo_path']);
            }
            $data['logo_path'] = null;
        } else {
            $data['logo_path'] = $this->handleFileUpload('logo_file', 'logo', $existing['logo_path'] ?? null);
        }

        if (isset($_POST['remove_stamp']) && $_POST['remove_stamp'] === '1') {
            if (!empty($existing['stamp_path']) && file_exists(APP_ROOT . '/uploads/' . $existing['stamp_path'])) {
                @unlink(APP_ROOT . '/uploads/' . $existing['stamp_path']);
            }
            $data['stamp_path'] = null;
        } else {
            $data['stamp_path'] = $this->handleFileUpload('stamp_file', 'stamp', $existing['stamp_path'] ?? null);
        }

        if (isset($_POST['remove_seal']) && $_POST['remove_seal'] === '1') {
            if (!empty($existing['seal_path']) && file_exists(APP_ROOT . '/uploads/' . $existing['seal_path'])) {
                @unlink(APP_ROOT . '/uploads/' . $existing['seal_path']);
            }
            $data['seal_path'] = null;
        } else {
            $data['seal_path'] = $this->handleFileUpload('seal_file', 'seal', $existing['seal_path'] ?? null);
        }

        if (isset($_POST['remove_signature']) && $_POST['remove_signature'] === '1') {
            if (!empty($existing['signature_path']) && file_exists(APP_ROOT . '/uploads/' . $existing['signature_path'])) {
                @unlink(APP_ROOT . '/uploads/' . $existing['signature_path']);
            }
            $data['signature_path'] = null;
        } else {
            $data['signature_path'] = $this->handleFileUpload('signature_file', 'sig', $existing['signature_path'] ?? null);
        }

        if (isset($_POST['remove_digital_signature']) && $_POST['remove_digital_signature'] === '1') {
            if (!empty($existing['digital_signature_path']) && file_exists(APP_ROOT . '/uploads/' . $existing['digital_signature_path'])) {
                @unlink(APP_ROOT . '/uploads/' . $existing['digital_signature_path']);
            }
            $data['digital_signature_path'] = null;
        } else {
            $data['digital_signature_path'] = $this->handleFileUpload('digital_signature_file', 'dig_sig', $existing['digital_signature_path'] ?? null);
        }

        if (isset($_POST['remove_letterhead']) && $_POST['remove_letterhead'] === '1') {
            if (!empty($existing['letterhead_path']) && file_exists(APP_ROOT . '/uploads/' . $existing['letterhead_path'])) {
                @unlink(APP_ROOT . '/uploads/' . $existing['letterhead_path']);
            }
            $data['letterhead_path'] = null;
        } else {
            $data['letterhead_path'] = $this->handleFileUpload('letterhead_file', 'lh', $existing['letterhead_path'] ?? null);
        }

        if (isset($_POST['remove_letterhead_export']) && $_POST['remove_letterhead_export'] === '1') {
            if (!empty($existing['letterhead_export_path']) && file_exists(APP_ROOT . '/uploads/' . $existing['letterhead_export_path'])) {
                @unlink(APP_ROOT . '/uploads/' . $existing['letterhead_export_path']);
            }
            $data['letterhead_export_path'] = null;
        } else {
            $data['letterhead_export_path'] = $this->handleFileUpload('letterhead_export_file', 'lh_exp', $existing['letterhead_export_path'] ?? null);
        }

        if (isset($_POST['remove_letterhead_domestic']) && $_POST['remove_letterhead_domestic'] === '1') {
            if (!empty($existing['letterhead_domestic_path']) && file_exists(APP_ROOT . '/uploads/' . $existing['letterhead_domestic_path'])) {
                @unlink(APP_ROOT . '/uploads/' . $existing['letterhead_domestic_path']);
            }
            $data['letterhead_domestic_path'] = null;
        } else {
            $data['letterhead_domestic_path'] = $this->handleFileUpload('letterhead_domestic_file', 'lh_dom', $existing['letterhead_domestic_path'] ?? null);
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

    private function handleFileUpload(string $fieldName, string $prefix, ?string $existingPath = null): ?string
    {
        if (isset($_FILES[$fieldName]) && $_FILES[$fieldName]['error'] === UPLOAD_ERR_OK) {
            $uploadDir = APP_ROOT . '/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }
            $originalName = basename($_FILES[$fieldName]['name']);
            $extension = pathinfo($originalName, PATHINFO_EXTENSION);
            $fileName = $prefix . '_' . uniqid() . '.' . $extension;
            $targetPath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES[$fieldName]['tmp_name'], $targetPath)) {
                // Delete old file if exists
                if ($existingPath && file_exists($uploadDir . $existingPath)) {
                    @unlink($uploadDir . $existingPath);
                }
                return $fileName;
            }
        }
        return $existingPath;
    }

    private function companyDataFromRequest(): array
    {
        return [
            'company_name' => trim((string) ($_POST['company_name'] ?? '')),
            'contact_person' => trim((string) ($_POST['contact_person'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')),
            'phone' => trim((string) ($_POST['phone'] ?? '')),
            'website' => trim((string) ($_POST['website'] ?? '')),
            
            'gst_number' => trim((string) ($_POST['gst_number'] ?? '')),
            'iec_code' => trim((string) ($_POST['iec_code'] ?? '')),
            'pan_number' => trim((string) ($_POST['pan_number'] ?? '')),
            'cin_number' => trim((string) ($_POST['cin_number'] ?? '')),
            'apeda_number' => trim((string) ($_POST['apeda_number'] ?? '')),
            'fssai_number' => trim((string) ($_POST['fssai_number'] ?? '')),
            'iso_number' => trim((string) ($_POST['iso_number'] ?? '')),
            'haccp_number' => trim((string) ($_POST['haccp_number'] ?? '')),
            
            'bank_name' => trim((string) ($_POST['bank_name'] ?? '')),
            'account_name' => trim((string) ($_POST['account_name'] ?? '')),
            'account_number' => trim((string) ($_POST['account_number'] ?? '')),
            'ifsc_code' => trim((string) ($_POST['ifsc_code'] ?? '')),
            'swift_code' => trim((string) ($_POST['swift_code'] ?? '')),
            
            'letterhead_type' => in_array($_POST['letterhead_type'] ?? '', ['plain', 'image', 'pdf']) ? $_POST['letterhead_type'] : 'plain',
            'declaration' => trim((string) ($_POST['declaration'] ?? '')),
            
            'address_line1' => trim((string) ($_POST['address_line1'] ?? '')),
            'address_line2' => trim((string) ($_POST['address_line2'] ?? '')),
            'city' => trim((string) ($_POST['city'] ?? '')),
            'state' => trim((string) ($_POST['state'] ?? '')),
            'country' => trim((string) ($_POST['country'] ?? '')),
            'zip' => trim((string) ($_POST['zip'] ?? '')),
            'status' => (int) ($_POST['status'] ?? 1),
            'print_margin_top' => (int) ($_POST['print_margin_top'] ?? 45),
            'print_margin_bottom' => (int) ($_POST['print_margin_bottom'] ?? 35),
            'print_margin_left' => (int) ($_POST['print_margin_left'] ?? 20),
            'print_margin_right' => (int) ($_POST['print_margin_right'] ?? 20),
            'signature_print_width' => (int) ($_POST['signature_print_width'] ?? 120),
            'seal_print_width' => (int) ($_POST['seal_print_width'] ?? 100),
            'stamp_print_width' => (int) ($_POST['stamp_print_width'] ?? 100),
        ];
    }

    private function validateCompany(array $data): array
    {
        return $this->validator->validate($data, [
            'company_name' => 'required|max:255',
            'contact_person' => 'max:255',
            'email' => 'email|max:255',
            'phone' => 'max:20',
            'website' => 'max:255',
            'gst_number' => 'max:50',
            'iec_code' => 'max:50',
            'pan_number' => 'max:50',
            'cin_number' => 'max:50',
            'apeda_number' => 'max:50',
            'fssai_number' => 'max:50',
            'iso_number' => 'max:50',
            'haccp_number' => 'max:50',
            'bank_name' => 'max:100',
            'account_name' => 'max:255',
            'account_number' => 'max:100',
            'ifsc_code' => 'max:50',
            'swift_code' => 'max:50',
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