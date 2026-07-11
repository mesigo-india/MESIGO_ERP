<?php
declare(strict_types=1);

namespace App\Core;

class ProformaInvoiceController extends Controller
{
    private ProformaInvoice $invoices;
    private Validator $validator;

    public function __construct()
    {
        parent::__construct();
        require_once APP_ROOT . '/classes/DocumentType.php';
        require_once APP_ROOT . '/classes/NumberGenerator.php';
        require_once APP_ROOT . '/classes/RevisionManager.php';
        require_once APP_ROOT . '/classes/DocumentStatusEngine.php';
        require_once APP_ROOT . '/classes/DocumentConversionEngine.php';
        require_once APP_ROOT . '/classes/DocumentHeader.php';
        require_once APP_ROOT . '/classes/DocumentItem.php';
        require_once APP_ROOT . '/classes/Quotation.php';
        require_once APP_ROOT . '/classes/ProformaInvoice.php';
        $this->invoices = new ProformaInvoice(Database::getInstance());
        $this->validator = new Validator();
    }

    public function index(): void
    {
        $this->requireLogin();
        $this->requirePermission('proforma_invoices.view');
        $search = trim((string) ($_GET['search'] ?? ''));
        $status = trim((string) ($_GET['status'] ?? ''));
        $this->render('proforma_invoices/index', [
            'title' => 'Proforma Invoices',
            'invoices' => $this->invoices->getAll($search, $status),
            'statuses' => ProformaInvoice::statuses(),
            'search' => $search,
            'status' => $status,
        ]);
    }

    public function create(): void
    {
        $this->requireLogin();
        $this->requirePermission('proforma_invoices.create');
        $this->renderForm('Create Proforma Invoice', null, [], [[]], '/proforma-invoices');
    }

    public function store(): void
    {
        $this->requireLogin();
        $this->requirePermission('proforma_invoices.create');
        if (!$this->validateCsrf()) {
            Response::redirect('/proforma-invoices/create', 'Invalid security token.');
        }
        $data = $this->invoiceDataFromRequest();
        $data['created_by'] = $this->currentUserId();
        $errors = $this->validateInvoice($data);
        if (!empty($errors)) {
            Response::redirect('/proforma-invoices/create', $this->formatValidationErrors($errors));
        }
        $id = $this->invoices->create($data);
        Response::redirect('/proforma-invoices/' . $id, 'Proforma Invoice created successfully.');
    }

    public function show(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('proforma_invoices.view');
        $invoice = $this->findInvoiceOrRedirect((int) $id);
        $this->render('proforma_invoices/view', [
            'title' => 'Proforma Invoice ' . $invoice['document_number'],
            'invoice' => $invoice,
            'items' => $this->enrichItems($this->invoices->getItems((int) $id)),
            'meta' => $this->invoices->meta($invoice['internal_notes'] ?? null),
            'statuses' => ProformaInvoice::statuses(),
            'revisions' => $this->invoices->revisions((int) $id),
            'history' => $this->invoices->statusHistory((int) $id),
        ]);
    }

    public function edit(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('proforma_invoices.update');
        $invoice = $this->findInvoiceOrRedirect((int) $id);
        $status = (int) ($invoice['status'] ?? 0);
        if ($status !== ProformaInvoice::STATUS_DRAFT && $status !== ProformaInvoice::STATUS_REJECTED) {
            Response::redirect('/proforma-invoices/' . (int) $id, 'Only draft or rejected proforma invoices can be modified.');
        }
        $this->renderForm(
            'Edit Proforma Invoice',
            $invoice,
            $this->invoices->meta($invoice['internal_notes'] ?? null),
            $this->enrichItems($this->invoices->getItems((int) $id)) ?: [[]],
            '/proforma-invoices/' . (int) $id
        );
    }

    public function update(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('proforma_invoices.update');
        if (!$this->validateCsrf()) {
            Response::redirect('/proforma-invoices/' . (int) $id . '/edit', 'Invalid security token.');
        }
        $invoice = $this->findInvoiceOrRedirect((int) $id);
        $status = (int) ($invoice['status'] ?? 0);
        if ($status !== ProformaInvoice::STATUS_DRAFT && $status !== ProformaInvoice::STATUS_REJECTED) {
            Response::redirect('/proforma-invoices/' . (int) $id, 'Only draft or rejected proforma invoices can be modified.');
        }
        $data = $this->invoiceDataFromRequest();
        $data['updated_by'] = $this->currentUserId();
        $errors = $this->validateInvoice($data);
        if (!empty($errors)) {
            Response::redirect('/proforma-invoices/' . (int) $id . '/edit', $this->formatValidationErrors($errors));
        }
        $this->invoices->update((int) $id, $data);
        Response::redirect('/proforma-invoices/' . (int) $id, 'Proforma Invoice updated successfully.');
    }

    public function status(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('proforma_invoices.update');
        if (!$this->validateCsrf()) {
            Response::redirect('/proforma-invoices/' . (int) $id, 'Invalid security token.');
        }
        $this->findInvoiceOrRedirect((int) $id);
        $this->invoices->updateStatus((int) $id, (int) ($_POST['status'] ?? ProformaInvoice::STATUS_DRAFT), (int) $this->currentUserId(), trim((string) ($_POST['remarks'] ?? '')));
        Response::redirect('/proforma-invoices/' . (int) $id, 'Proforma Invoice status updated.');
    }

    public function revise(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('proforma_invoices.update');
        if (!$this->validateCsrf()) {
            Response::redirect('/proforma-invoices/' . (int) $id, 'Invalid security token.');
        }
        $this->findInvoiceOrRedirect((int) $id);
        $this->invoices->revise((int) $id, trim((string) ($_POST['revision_notes'] ?? 'PI revision saved')), $this->currentUserId());
        Response::redirect('/proforma-invoices/' . (int) $id, 'Proforma Invoice revision saved.');
    }

    public function print(string $id): void
    {
        $this->show($id);
    }

    public function email(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('proforma_invoices.view');
        $this->findInvoiceOrRedirect((int) $id);
        Response::redirect('/proforma-invoices/' . (int) $id, 'Proforma Invoice is email-ready.');
    }

    public function convert(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('proforma_invoices.convert');
        if (!$this->validateCsrf()) {
            Response::redirect('/proforma-invoices/' . (int) $id, 'Invalid security token.');
        }
        $invoice = $this->findInvoiceOrRedirect((int) $id);
        if ((int) ($invoice['status'] ?? 0) !== ProformaInvoice::STATUS_APPROVED) {
            Response::redirect('/proforma-invoices/' . (int) $id, 'This Proforma Invoice must be Approved before converting to a Commercial Invoice.');
        }
        $ciId = $this->invoices->convertToCommercialInvoice((int) $id, (int) $this->currentUserId());
        Response::redirect('/proforma-invoices/' . (int) $id, 'Proforma Invoice converted to Commercial Invoice reference #' . $ciId . '.');
    }

    private function renderForm(string $title, ?array $invoice, array $meta, array $items, string $action): void
    {
        $this->render('proforma_invoices/form', [
            'title' => $title,
            'invoice' => $invoice,
            'meta' => $meta,
            'items' => $items,
            'action' => $action,
            'companies' => $this->invoices->masterRows('company', 'company_name'),
            'warehouses' => $this->invoices->masterRows('warehouses', 'name'),
            'costTemplates' => $this->invoices->masterRows('cost_templates', 'name'),
            'costComponents' => $this->invoices->masterRows('cost_components', 'name'),
            'buyers' => $this->invoices->masterRows('buyers', 'company_name'),
            'buyerContacts' => $this->invoices->contacts(0),
            'currencies' => $this->invoices->masterRows('currencies', 'code'),
            'incoterms' => $this->invoices->masterRows('incoterms', 'code'),
            'paymentTerms' => $this->invoices->masterRows('payment_terms', 'name'),
            'ports' => $this->invoices->masterRows('ports', 'name'),
            'products' => $this->invoices->masterRows('products', 'name'),
            'grades' => $this->invoices->masterRows('product_grades', 'name'),
            'origins' => $this->invoices->masterRows('product_origins', 'name'),
            'packingTypes' => $this->invoices->masterRows('packing_types', 'name'),
            'units' => $this->invoices->masterRows('units', 'name'),
            'statuses' => ProformaInvoice::statuses(),
        ]);
    }

    private function invoiceDataFromRequest(): array
    {
        return $this->extractDocumentDataFromRequest(ProformaInvoice::STATUS_DRAFT);
    }

    private function validateInvoice(array $data): array
    {
        return $this->validator->validate($data, ['document_date' => 'required', 'buyer_id' => 'required', 'currency_id' => 'required']);
    }

    private function enrichItems(array $items): array
    {
        foreach ($items as &$item) {
            $quality = json_decode((string) ($item['quality'] ?? ''), true);
            $item['grade_id'] = (int) ($quality['grade_id'] ?? 0);
            $item['origin_id'] = (int) ($quality['origin_id'] ?? 0);
        }
        return $items;
    }

    private function findInvoiceOrRedirect(int $id): array
    {
        $invoice = $this->invoices->findById($id);
        if (!$invoice) {
            Response::redirect('/proforma-invoices', 'Proforma Invoice not found.');
        }
        return $invoice;
    }

    public function delete(string $id): void
    {
        $this->requireLogin();
        if ($this->auth->user()['role_name'] !== 'admin') {
            Response::redirect('/proforma-invoices/' . (int) $id, 'Only administrators can delete transactions.');
        }
        if (!$this->validateCsrf()) {
            Response::redirect('/proforma-invoices/' . (int) $id, 'Invalid security token.');
        }
        $this->findInvoiceOrRedirect((int) $id);
        $stmt = Database::getInstance()->prepare("UPDATE document_headers SET deleted_at = NOW(), deleted_by = :user_id, status = 0 WHERE id = :id");
        $stmt->execute(['user_id' => $this->currentUserId(), 'id' => (int) $id]);
        Response::redirect('/proforma-invoices', 'Proforma Invoice deleted successfully.');
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