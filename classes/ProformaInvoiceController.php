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
        $this->findInvoiceOrRedirect((int) $id);
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
        $this->findInvoiceOrRedirect((int) $id);
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
        return [
            'document_date' => trim((string) ($_POST['document_date'] ?? date('Y-m-d'))),
            'revision' => (int) ($_POST['revision'] ?? 0),
            'buyer_id' => (int) ($_POST['buyer_id'] ?? 0),
            'buyer_contact_id' => (int) ($_POST['buyer_contact_id'] ?? 0),
            'currency_id' => (int) ($_POST['currency_id'] ?? 0),
            'incoterm_id' => (int) ($_POST['incoterm_id'] ?? 0),
            'payment_term_id' => (int) ($_POST['payment_term_id'] ?? 0),
            'shipment_term' => trim((string) ($_POST['shipment_term'] ?? '')),
            'delivery_port_id' => (int) ($_POST['delivery_port_id'] ?? 0),
            'loading_port_id' => (int) ($_POST['loading_port_id'] ?? 0),
            'valid_until' => trim((string) ($_POST['valid_until'] ?? '')),
            'remarks' => trim((string) ($_POST['remarks'] ?? '')),
            'status' => (int) ($_POST['status'] ?? ProformaInvoice::STATUS_DRAFT),
            'charges' => [
                'freight' => trim((string) ($_POST['freight'] ?? '0')),
                'insurance' => trim((string) ($_POST['insurance'] ?? '0')),
                'other_charges' => trim((string) ($_POST['other_charges'] ?? '0')),
            ],
            'items' => $this->itemsFromRequest(),
        ];
    }

    private function itemsFromRequest(): array
    {
        $items = [];
        foreach ($_POST['product_id'] ?? [] as $index => $productId) {
            $items[] = [
                'product_id' => (int) $productId,
                'grade_id' => (int) ($_POST['grade_id'][$index] ?? 0),
                'origin_id' => (int) ($_POST['origin_id'][$index] ?? 0),
                'hsn_code' => trim((string) ($_POST['hsn_code'][$index] ?? '')),
                'packing_type_id' => (int) ($_POST['packing_type_id'][$index] ?? 0),
                'quantity' => trim((string) ($_POST['quantity'][$index] ?? '0')),
                'unit_id' => (int) ($_POST['unit_id'][$index] ?? 0),
                'rate' => trim((string) ($_POST['rate'][$index] ?? '0')),
                'discount_percent' => trim((string) ($_POST['discount_percent'][$index] ?? '0')),
                'tax_percent' => trim((string) ($_POST['tax_percent'][$index] ?? '0')),
            ];
        }
        return $items;
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