<?php
declare(strict_types=1);

namespace App\Core;

class CommercialInvoiceController extends Controller
{
    private CommercialInvoice $invoices;
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
        require_once APP_ROOT . '/classes/CommercialInvoice.php';
        $this->invoices = new CommercialInvoice(Database::getInstance());
        $this->validator = new Validator();
    }

    public function index(): void
    {
        $this->requireLogin();
        $this->requirePermission('commercial_invoices.view');
        $search = trim((string) ($_GET['search'] ?? ''));
        $status = trim((string) ($_GET['status'] ?? ''));
        $this->render('commercial_invoices/index', [
            'title' => 'Commercial Invoices',
            'invoices' => $this->invoices->getAll($search, $status),
            'statuses' => CommercialInvoice::statuses(),
            'search' => $search,
            'status' => $status,
        ]);
    }

    public function create(): void
    {
        $this->requireLogin();
        $this->requirePermission('commercial_invoices.create');
        $this->renderForm('Create Commercial Invoice', null, [], [[]], '/commercial-invoices');
    }

    public function store(): void
    {
        $this->requireLogin();
        $this->requirePermission('commercial_invoices.create');
        if (!$this->validateCsrf()) {
            Response::redirect('/commercial-invoices/create', 'Invalid security token.');
        }
        $data = $this->invoiceDataFromRequest();
        $data['created_by'] = $this->currentUserId();
        $errors = $this->validateInvoice($data);
        if (!empty($errors)) {
            Response::redirect('/commercial-invoices/create', $this->formatValidationErrors($errors));
        }
        $id = $this->invoices->create($data);
        Response::redirect('/commercial-invoices/' . $id, 'Commercial Invoice created successfully.');
    }

    public function show(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('commercial_invoices.view');
        $invoice = $this->findInvoiceOrRedirect((int) $id);
        $this->render('commercial_invoices/view', [
            'title' => 'Commercial Invoice ' . $invoice['document_number'],
            'invoice' => $invoice,
            'items' => $this->enrichItems($this->invoices->getItems((int) $id)),
            'meta' => $this->invoices->meta($invoice['internal_notes'] ?? null),
            'statuses' => CommercialInvoice::statuses(),
            'revisions' => $this->invoices->revisions((int) $id),
            'history' => $this->invoices->statusHistory((int) $id),
        ]);
    }

    public function edit(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('commercial_invoices.update');
        $invoice = $this->findInvoiceOrRedirect((int) $id);
        $this->renderForm('Edit Commercial Invoice', $invoice, $this->invoices->meta($invoice['internal_notes'] ?? null), $this->enrichItems($this->invoices->getItems((int) $id)) ?: [[]], '/commercial-invoices/' . (int) $id);
    }

    public function update(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('commercial_invoices.update');
        if (!$this->validateCsrf()) {
            Response::redirect('/commercial-invoices/' . (int) $id . '/edit', 'Invalid security token.');
        }
        $this->findInvoiceOrRedirect((int) $id);
        $data = $this->invoiceDataFromRequest();
        $data['updated_by'] = $this->currentUserId();
        $errors = $this->validateInvoice($data);
        if (!empty($errors)) {
            Response::redirect('/commercial-invoices/' . (int) $id . '/edit', $this->formatValidationErrors($errors));
        }
        $this->invoices->update((int) $id, $data);
        Response::redirect('/commercial-invoices/' . (int) $id, 'Commercial Invoice updated successfully.');
    }

    public function status(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('commercial_invoices.update');
        if (!$this->validateCsrf()) {
            Response::redirect('/commercial-invoices/' . (int) $id, 'Invalid security token.');
        }
        $this->findInvoiceOrRedirect((int) $id);
        $this->invoices->updateStatus((int) $id, (int) ($_POST['status'] ?? ProformaInvoice::STATUS_DRAFT), (int) $this->currentUserId(), trim((string) ($_POST['remarks'] ?? '')));
        Response::redirect('/commercial-invoices/' . (int) $id, 'Commercial Invoice status updated.');
    }

    public function revise(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('commercial_invoices.update');
        if (!$this->validateCsrf()) {
            Response::redirect('/commercial-invoices/' . (int) $id, 'Invalid security token.');
        }
        $this->findInvoiceOrRedirect((int) $id);
        $this->invoices->revise((int) $id, trim((string) ($_POST['revision_notes'] ?? 'CI revision saved')), $this->currentUserId());
        Response::redirect('/commercial-invoices/' . (int) $id, 'Commercial Invoice revision saved.');
    }

    public function print(string $id): void
    {
        $this->show($id);
    }

    public function email(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('commercial_invoices.view');
        $this->findInvoiceOrRedirect((int) $id);
        Response::redirect('/commercial-invoices/' . (int) $id, 'Commercial Invoice is email-ready.');
    }

    public function convert(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('commercial_invoices.convert');
        if (!$this->validateCsrf()) {
            Response::redirect('/commercial-invoices/' . (int) $id, 'Invalid security token.');
        }
        $this->findInvoiceOrRedirect((int) $id);
        $packingListId = $this->invoices->convertToPackingList((int) $id, (int) $this->currentUserId());
        Response::redirect('/commercial-invoices/' . (int) $id, 'Commercial Invoice converted to Packing List reference #' . $packingListId . '.');
    }

    private function renderForm(string $title, ?array $invoice, array $meta, array $items, string $action): void
    {
        $this->render('commercial_invoices/form', [
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
            'statuses' => CommercialInvoice::statuses(),
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
            'charges' => ['freight' => trim((string) ($_POST['freight'] ?? '0')), 'insurance' => trim((string) ($_POST['insurance'] ?? '0')), 'other_charges' => trim((string) ($_POST['other_charges'] ?? '0'))],
            'items' => $this->itemsFromRequest(),
        ];
    }

    private function itemsFromRequest(): array
    {
        $items = [];
        foreach ($_POST['product_id'] ?? [] as $index => $productId) {
            $items[] = ['product_id' => (int) $productId, 'grade_id' => (int) ($_POST['grade_id'][$index] ?? 0), 'origin_id' => (int) ($_POST['origin_id'][$index] ?? 0), 'hsn_code' => trim((string) ($_POST['hsn_code'][$index] ?? '')), 'packing_type_id' => (int) ($_POST['packing_type_id'][$index] ?? 0), 'quantity' => trim((string) ($_POST['quantity'][$index] ?? '0')), 'unit_id' => (int) ($_POST['unit_id'][$index] ?? 0), 'rate' => trim((string) ($_POST['rate'][$index] ?? '0')), 'discount_percent' => trim((string) ($_POST['discount_percent'][$index] ?? '0')), 'tax_percent' => trim((string) ($_POST['tax_percent'][$index] ?? '0'))];
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
            Response::redirect('/commercial-invoices', 'Commercial Invoice not found.');
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