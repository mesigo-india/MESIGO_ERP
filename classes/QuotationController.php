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
        $this->verifyCanEdit($quotation, '/quotations', 'quotation');

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

        $quotation = $this->findQuotationOrRedirect((int) $id);
        $this->verifyCanEdit($quotation, '/quotations', 'quotation');

        $data = $this->quotationDataFromRequest();
        $data['updated_by'] = $this->currentUserId();
        $errors = $this->validateQuotation($data);
        if (!empty($errors)) {
            Response::redirect('/quotations/' . (int) $id . '/edit', $this->formatValidationErrors($errors));
        }

        $this->quotations->update((int) $id, $data);
        $this->logLifecycleAudit('Edit', 'quotation', (int)$id, $quotation['document_number'], (int)$quotation['status'], (int)$quotation['status'], 'Quotation updated');
        Response::redirect('/quotations/' . (int) $id, 'Quotation updated successfully.');
    }

    public function status(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('quotations.update');

        if (!$this->validateCsrf()) {
            Response::redirect('/quotations/' . (int) $id, 'Invalid security token.');
        }

        $quotation = $this->findQuotationOrRedirect((int) $id);
        $statusId = (int) ($_POST['status'] ?? Quotation::STATUS_DRAFT);
        
        $this->verifyCanChangeStatus((int)$id, (int)$quotation['status'], $statusId, '/quotations');

        $remarks = trim((string) ($_POST['remarks'] ?? ''));
        $this->quotations->updateStatus((int) $id, $statusId, (int) $this->currentUserId(), $remarks);
        
        // Log status change audit
        $actionName = 'Status Change';
        if ($statusId === 2) $actionName = 'Approve';
        if ($statusId === 3) $actionName = 'Reject';
        $this->logLifecycleAudit($actionName, 'quotation', (int)$id, $quotation['document_number'], (int)$quotation['status'], $statusId, $remarks);

        Response::redirect('/quotations/' . (int) $id, 'Quotation status updated.');
    }

    public function revise(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('quotations.update');

        if (!$this->validateCsrf()) {
            Response::redirect('/quotations/' . (int) $id, 'Invalid security token.');
        }

        $newId = $this->handleDocumentRevision((int)$id, '/quotations', 'quotation', $this->quotations);
        Response::redirect('/quotations/' . $newId, 'New document revision created successfully.');
    }

    public function print(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('quotations.view');

        $quotation = $this->findQuotationOrRedirect((int) $id);
        $items = $this->enrichItems($this->quotations->getItems((int) $id));

        $this->logLifecycleAudit('Print', 'quotation', (int)$id, $quotation['document_number'], (int)$quotation['status'], (int)$quotation['status'], 'Document printed');

        $this->handleDocumentPrint((int)$id, 'quotation', $quotation, $items);
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

        $quotation = $this->findQuotationOrRedirect((int) $id);
        if ((int) ($quotation['status'] ?? 0) !== Quotation::STATUS_APPROVED) {
            Response::redirect('/quotations/' . (int) $id, 'This Quotation must be Approved before converting to a Proforma Invoice.');
        }
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
            'companies' => $this->quotations->masterRows('company', 'company_name'),
            'warehouses' => $this->quotations->masterRows('warehouses', 'name'),
            'costTemplates' => $this->quotations->masterRows('cost_templates', 'name'),
            'costComponents' => $this->quotations->masterRows('cost_components', 'name'),
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
        return $this->extractDocumentDataFromRequest(Quotation::STATUS_DRAFT);
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


    public function delete(string $id): void
    {
        if (!$this->validateCsrf()) {
            Response::redirect('/quotations/' . (int) $id, 'Invalid security token.');
        }
        $this->handleDocumentDelete((int)$id, '/quotations', 'quotations');
    }

    /**
     * API to fetch items for a costing template
     */
    public function costTemplateItems(string $id): void
    {
        $this->requireLogin();
        $stmt = Database::getInstance()->prepare("
            SELECT t.*, c.name as component_name 
            FROM cost_template_items t
            LEFT JOIN cost_components c ON t.cost_component_id = c.id
            WHERE t.cost_template_id = :id
            ORDER BY t.sort_order ASC
        ");
        $stmt->execute(['id' => (int) $id]);
        $items = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        header('Content-Type: application/json');
        echo json_encode($items);
        exit;
    }
}