<?php
declare(strict_types=1);

namespace App\Core;

class QuotationController extends Controller
{
    private Quotation $quotations;
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
        $this->quotations = new Quotation(Database::getInstance());
        $this->validator = new Validator();
    }

    public function index(): void
    {
        $this->requireLogin();
        $this->requirePermission('quotations.view');

        $search = trim((string) ($_GET['search'] ?? ''));
        $status = trim((string) ($_GET['status'] ?? ''));

        $this->render('quotations/index', [
            'title' => 'Quotations',
            'quotations' => $this->quotations->getAll($search, $status),
            'statuses' => Quotation::statuses(),
            'search' => $search,
            'status' => $status,
        ]);
    }

    public function create(): void
    {
        $this->requireLogin();
        $this->requirePermission('quotations.create');
        $this->renderForm('Create Quotation', null, [], [[]], '/quotations');
    }

    public function store(): void
    {
        $this->requireLogin();
        $this->requirePermission('quotations.create');

        if (!$this->validateCsrf()) {
            Response::redirect('/quotations/create', 'Invalid security token.');
        }

        $data = $this->quotationDataFromRequest();
        $data['created_by'] = $this->currentUserId();
        $errors = $this->validateQuotation($data);
        if (!empty($errors)) {
            Response::redirect('/quotations/create', $this->formatValidationErrors($errors));
        }

        $id = $this->quotations->create($data);
        $this->logger->info('Quotation created', ['quotation_id' => $id]);
        Response::redirect('/quotations/' . $id, 'Quotation created successfully.');
    }

    public function show(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('quotations.view');

        $quotation = $this->findQuotationOrRedirect((int) $id);
        $items = $this->enrichItems($this->quotations->getItems((int) $id));
        $meta = $this->quotations->meta($quotation['internal_notes'] ?? null);

        $this->render('quotations/view', [
            'title' => 'Quotation ' . $quotation['document_number'],
            'quotation' => $quotation,
            'items' => $items,
            'meta' => $meta,
            'statuses' => Quotation::statuses(),
            'revisions' => $this->quotations->revisions((int) $id),
            'history' => $this->quotations->statusHistory((int) $id),
        ]);
    }

    public function edit(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('quotations.update');

        $quotation = $this->findQuotationOrRedirect((int) $id);
        $items = $this->enrichItems($this->quotations->getItems((int) $id));
        $meta = $this->quotations->meta($quotation['internal_notes'] ?? null);

        $this->renderForm('Edit Quotation', $quotation, $meta, $items ?: [[]], '/quotations/' . (int) $id);
    }

    public function update(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('quotations.update');

        if (!$this->validateCsrf()) {
            Response::redirect('/quotations/' . (int) $id . '/edit', 'Invalid security token.');
        }

        $this->findQuotationOrRedirect((int) $id);
        $data = $this->quotationDataFromRequest();
        $data['updated_by'] = $this->currentUserId();
        $errors = $this->validateQuotation($data);
        if (!empty($errors)) {
            Response::redirect('/quotations/' . (int) $id . '/edit', $this->formatValidationErrors($errors));
        }

        $this->quotations->update((int) $id, $data);
        $this->logger->info('Quotation updated', ['quotation_id' => (int) $id]);
        Response::redirect('/quotations/' . (int) $id, 'Quotation updated successfully.');
    }

    public function status(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('quotations.update');

        if (!$this->validateCsrf()) {
            Response::redirect('/quotations/' . (int) $id, 'Invalid security token.');
        }

        $this->findQuotationOrRedirect((int) $id);
        $this->quotations->updateStatus((int) $id, (int) ($_POST['status'] ?? Quotation::STATUS_DRAFT), (int) $this->currentUserId(), trim((string) ($_POST['remarks'] ?? '')));
        Response::redirect('/quotations/' . (int) $id, 'Quotation status updated.');
    }

    public function revise(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('quotations.update');

        if (!$this->validateCsrf()) {
            Response::redirect('/quotations/' . (int) $id, 'Invalid security token.');
        }

        $this->findQuotationOrRedirect((int) $id);
        $this->quotations->revise((int) $id, trim((string) ($_POST['revision_notes'] ?? 'Revision saved')), $this->currentUserId());
        Response::redirect('/quotations/' . (int) $id, 'Quotation revision saved.');
    }

    public function print(string $id): void
    {
        $this->show($id);
    }

    public function email(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('quotations.view');
        $this->findQuotationOrRedirect((int) $id);
        Response::redirect('/quotations/' . (int) $id, 'Quotation is email-ready. Email sending will use this approved layout.');
    }

    public function convert(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('quotations.convert');

        if (!$this->validateCsrf()) {
            Response::redirect('/quotations/' . (int) $id, 'Invalid security token.');
        }

        $this->findQuotationOrRedirect((int) $id);
        $piId = $this->quotations->convertToProforma((int) $id, (int) $this->currentUserId());
        Response::redirect('/quotations/' . (int) $id, 'Quotation converted to Proforma Invoice reference #' . $piId . '.');
    }

    private function renderForm(string $title, ?array $quotation, array $meta, array $items, string $action): void
    {
        $this->render('quotations/form', [
            'title' => $title,
            'quotation' => $quotation,
            'meta' => $meta,
            'items' => $items,
            'action' => $action,
            'buyers' => $this->quotations->masterRows('buyers', 'company_name'),
            'buyerContacts' => $this->quotations->contacts(0),
            'currencies' => $this->quotations->masterRows('currencies', 'code'),
            'incoterms' => $this->quotations->masterRows('incoterms', 'code'),
            'paymentTerms' => $this->quotations->masterRows('payment_terms', 'name'),
            'ports' => $this->quotations->masterRows('ports', 'name'),
            'products' => $this->quotations->masterRows('products', 'name'),
            'grades' => $this->quotations->masterRows('product_grades', 'name'),
            'origins' => $this->quotations->masterRows('product_origins', 'name'),
            'packingTypes' => $this->quotations->masterRows('packing_types', 'name'),
            'units' => $this->quotations->masterRows('units', 'name'),
            'statuses' => Quotation::statuses(),
        ]);
    }

    private function quotationDataFromRequest(): array
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
            'status' => (int) ($_POST['status'] ?? Quotation::STATUS_DRAFT),
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

    private function validateQuotation(array $data): array
    {
        return $this->validator->validate($data, [
            'document_date' => 'required',
            'buyer_id' => 'required',
            'currency_id' => 'required',
        ]);
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

    private function findQuotationOrRedirect(int $id): array
    {
        $quotation = $this->quotations->findById($id);
        if (!$quotation) {
            Response::redirect('/quotations', 'Quotation not found.');
        }
        return $quotation;
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