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
        $statusId = (int) ($_POST['status'] ?? ProformaInvoice::STATUS_DRAFT);
        $this->invoices->updateStatus((int) $id, $statusId, (int) $this->currentUserId(), trim((string) ($_POST['remarks'] ?? '')));
        
        if ($statusId === 2) { // 2 = Approved
            $this->autoGenerateExportDocuments((int) $id, (int) $this->currentUserId());
        }
        
        Response::redirect('/commercial-invoices/' . (int) $id, 'Commercial Invoice status updated.');
    }

    private function autoGenerateExportDocuments(int $id, int $userId): void
    {
        $db = Database::getInstance();
        $attachmentManager = new AttachmentManager($db);
        
        // Check if documents are already auto-generated to avoid duplicate entries
        $checkStmt = $db->prepare("SELECT COUNT(*) FROM document_attachments WHERE document_header_id = :id AND file_path LIKE 'print-link:%'");
        $checkStmt->execute(['id' => $id]);
        if ((int)$checkStmt->fetchColumn() > 0) {
            return; // Already generated
        }

        $typesToGenerate = [
            'packing_list' => [
                'label' => 'Packing List',
                'route' => 'packing-lists',
                'attachment_type' => 'packing_list'
            ],
            'shipping_bill' => [
                'label' => 'Draft Shipping Bill',
                'route' => 'shipping-bills',
                'attachment_type' => 'shipping_bill'
            ],
            'bill_of_lading' => [
                'label' => 'Draft Bill of Lading',
                'route' => 'bill-of-ladings',
                'attachment_type' => 'bill_of_lading'
            ],
            'certificate_of_origin' => [
                'label' => 'Certificate of Origin',
                'route' => 'certificate-of-origins',
                'attachment_type' => 'certificate_of_origin'
            ],
            'non_hazardous_cert' => [
                'label' => 'Non-Hazardous Certificate',
                'route' => 'non-hazardous-certs',
                'attachment_type' => 'non_hazardous_cert'
            ]
        ];

        $converter = new DocumentConversionEngine($db);
        
        foreach ($typesToGenerate as $typeCode => $info) {
            try {
                $newDocId = $converter->convert($id, $typeCode, $userId, ['status' => 0]); // Status 0 (Draft)
                
                $numStmt = $db->prepare("SELECT document_number FROM document_headers WHERE id = :id");
                $numStmt->execute(['id' => $newDocId]);
                $docNumber = $numStmt->fetchColumn() ?: $info['label'];

                $attachmentManager->add(
                    $id,
                    $docNumber . '.pdf',
                    $info['label'] . ' ' . $docNumber . ' (Auto-Generated)',
                    'print-link:' . $info['route'] . '/' . $newDocId,
                    'text/html',
                    0,
                    $info['attachment_type'],
                    $userId
                );
            } catch (\Throwable $e) {
                $this->logger->error('Failed to auto-generate export document', [
                    'ci_id' => $id,
                    'type' => $typeCode,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Restore Commercial Invoice status to 2 (Approved) and clear converted_to_id
        $db->prepare("UPDATE document_headers SET status = 2, converted_to_id = NULL WHERE id = :id")->execute(['id' => $id]);
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
        $invoice = $this->findInvoiceOrRedirect((int) $id);
        if ((int) ($invoice['status'] ?? 0) !== CommercialInvoice::STATUS_APPROVED) {
            Response::redirect('/commercial-invoices/' . (int) $id, 'This Commercial Invoice must be Approved before converting to a Packing List.');
        }
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
            'statuses' => CommercialInvoice::statuses(),
        ]);
    }

    private function invoiceDataFromRequest(): array
    {
        return $this->extractDocumentDataFromRequest(CommercialInvoice::STATUS_DRAFT);
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

    public function delete(string $id): void
    {
        $this->requireLogin();
        if ($this->auth->user()['role_name'] !== 'admin') {
            Response::redirect('/commercial-invoices/' . (int) $id, 'Only administrators can delete transactions.');
        }
        if (!$this->validateCsrf()) {
            Response::redirect('/commercial-invoices/' . (int) $id, 'Invalid security token.');
        }
        $this->findInvoiceOrRedirect((int) $id);
        $stmt = Database::getInstance()->prepare("UPDATE document_headers SET deleted_at = NOW(), deleted_by = :user_id, status = 0 WHERE id = :id");
        $stmt->execute(['user_id' => $this->currentUserId(), 'id' => (int) $id]);
        Response::redirect('/commercial-invoices', 'Commercial Invoice deleted successfully.');
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