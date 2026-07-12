<?php
declare(strict_types=1);

namespace App\Core;

class PhytosanitaryController extends Controller
{
    private Phytosanitary $certificates;
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
        require_once APP_ROOT . '/classes/PackingList.php';
        require_once APP_ROOT . '/classes/ShippingBill.php';
        require_once APP_ROOT . '/classes/BillOfLading.php';
        require_once APP_ROOT . '/classes/CertificateOfOrigin.php';
        require_once APP_ROOT . '/classes/Phytosanitary.php';
        $this->certificates = new Phytosanitary(Database::getInstance());
        $this->validator = new Validator();
    }

    public function index(): void
    {
        $this->requireLogin();
        $this->requirePermission('phytosanitary.view');
        $search = trim((string) ($_GET['search'] ?? ''));
        $status = trim((string) ($_GET['status'] ?? ''));
        $this->render('phytosanitary/index', [
            'title' => 'Phytosanitary Certificates',
            'certificates' => $this->certificates->getAll($search, $status),
            'statuses' => Phytosanitary::statuses(),
            'search' => $search,
            'status' => $status
        ]);
    }

    public function create(): void
    {
        $this->requireLogin();
        $this->requirePermission('phytosanitary.create');
        $this->renderForm('Create Phytosanitary Certificate', null, [], [[]], '/phytosanitary');
    }

    public function store(): void
    {
        $this->requireLogin();
        $this->requirePermission('phytosanitary.create');
        if (!$this->validateCsrf()) {
            Response::redirect('/phytosanitary/create', 'Invalid security token.');
        }
        $data = $this->certificateDataFromRequest();
        $data['created_by'] = $this->currentUserId();
        $errors = $this->validateCertificate($data);
        if (!empty($errors)) {
            Response::redirect('/phytosanitary/create', $this->formatValidationErrors($errors));
        }
        $id = $this->certificates->create($data);
        Response::redirect('/phytosanitary/' . $id, 'Phytosanitary Certificate created successfully.');
    }

    public function show(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('phytosanitary.view');
        $certificate = $this->findCertificateOrRedirect((int) $id);
        $this->render('phytosanitary/view', [
            'title' => 'Phytosanitary Certificate ' . $certificate['document_number'],
            'certificate' => $certificate,
            'items' => $this->certificates->getItems((int) $id),
            'meta' => $this->certificates->meta($certificate['internal_notes'] ?? null),
            'statuses' => Phytosanitary::statuses(),
            'revisions' => $this->certificates->revisions((int) $id),
            'history' => $this->certificates->statusHistory((int) $id)
        ]);
    }

    public function edit(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('phytosanitary.update');
        $certificate = $this->findCertificateOrRedirect((int) $id);
        
        $this->verifyCanEdit($certificate, '/phytosanitary', 'phytosanitary');

        $this->renderForm('Edit Phytosanitary Certificate', $certificate, $this->certificates->meta($certificate['internal_notes'] ?? null), $this->certificates->getItems((int) $id) ?: [[]], '/phytosanitary/' . (int) $id);
    }

    public function update(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('phytosanitary.update');
        if (!$this->validateCsrf()) {
            Response::redirect('/phytosanitary/' . (int) $id . '/edit', 'Invalid security token.');
        }
        $certificate = $this->findCertificateOrRedirect((int) $id);
        
        $this->verifyCanEdit($certificate, '/phytosanitary', 'phytosanitary');

        $data = $this->certificateDataFromRequest();
        $data['updated_by'] = $this->currentUserId();
        $errors = $this->validateCertificate($data);
        if (!empty($errors)) {
            Response::redirect('/phytosanitary/' . (int) $id . '/edit', $this->formatValidationErrors($errors));
        }
        $this->certificates->update((int) $id, $data);
        $this->logLifecycleAudit('Edit', 'phytosanitary', (int)$id, $certificate['document_number'], (int)$certificate['status'], (int)$certificate['status'], 'Phytosanitary Certificate updated');
        Response::redirect('/phytosanitary/' . (int) $id, 'Phytosanitary Certificate updated successfully.');
    }

    public function status(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('phytosanitary.update');
        if (!$this->validateCsrf()) {
            Response::redirect('/phytosanitary/' . (int) $id, 'Invalid security token.');
        }
        $certificate = $this->findCertificateOrRedirect((int) $id);
        $statusId = (int) ($_POST['status'] ?? Phytosanitary::STATUS_DRAFT);
        
        $this->verifyCanChangeStatus((int)$id, (int)$certificate['status'], $statusId, '/phytosanitary');

        $remarks = trim((string) ($_POST['remarks'] ?? ''));
        $this->certificates->updateStatus((int) $id, $statusId, (int) $this->currentUserId(), $remarks);

        // Log status change audit
        $actionName = 'Status Change';
        if ($statusId === 2) $actionName = 'Approve';
        if ($statusId === 3) $actionName = 'Reject';
        $this->logLifecycleAudit($actionName, 'phytosanitary', (int)$id, $certificate['document_number'], (int)$certificate['status'], $statusId, $remarks);

        Response::redirect('/phytosanitary/' . (int) $id, 'Phytosanitary Certificate status updated.');
    }

    public function revise(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('phytosanitary.update');
        if (!$this->validateCsrf()) {
            Response::redirect('/phytosanitary/' . (int) $id, 'Invalid security token.');
        }
        $newId = $this->handleDocumentRevision((int)$id, '/phytosanitary', 'phytosanitary', $this->certificates);
        Response::redirect('/phytosanitary/' . $newId, 'New document revision created successfully.');
    }

    public function print(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('phytosanitary.view');

        $certificate = $this->findCertificateOrRedirect((int) $id);
        $items = $this->certificates->getItems((int) $id);
        $meta = $this->certificates->meta($certificate['internal_notes'] ?? null);

        $this->logLifecycleAudit('Print', 'phytosanitary', (int)$id, $certificate['document_number'], (int)$certificate['status'], (int)$certificate['status'], 'Document printed');

        $this->renderPrint('phytosanitary/print', [
            'title' => 'Phytosanitary Certificate ' . $certificate['document_number'],
            'certificate' => $certificate,
            'items' => $items,
            'meta' => $meta,
            'statuses' => \App\Core\Phytosanitary::statuses(),
            'revisions' => $this->certificates->revisions((int) $id),
        ]);
    }

    private function renderForm(string $title, ?array $certificate, array $meta, array $items, string $action): void
    {
        $this->render('phytosanitary/form', [
            'title' => $title,
            'certificate' => $certificate,
            'meta' => $meta,
            'items' => $items,
            'action' => $action,
            'buyers' => $this->certificates->masterRows('buyers', 'company_name'),
            'currencies' => $this->certificates->masterRows('currencies', 'code'),
            'products' => $this->certificates->masterRows('products', 'name'),
            'statuses' => Phytosanitary::statuses()
        ]);
    }

    private function certificateDataFromRequest(): array
    {
        return [
            'document_date' => trim((string) ($_POST['document_date'] ?? date('Y-m-d'))),
            'revision' => (int) ($_POST['revision'] ?? 0),
            'buyer_id' => (int) ($_POST['buyer_id'] ?? 0),
            'currency_id' => (int) ($_POST['currency_id'] ?? 0),
            'incoterm_id' => 0,
            'payment_term_id' => 0,
            'shipment_term' => '',
            'delivery_port_id' => 0,
            'loading_port_id' => 0,
            'valid_until' => '',
            'remarks' => trim((string) ($_POST['remarks'] ?? '')),
            'status' => (int) ($_POST['status'] ?? Phytosanitary::STATUS_DRAFT),
            'co_meta' => [
                'botanical_name' => trim((string) ($_POST['botanical_name'] ?? '')),
                'place_of_origin' => trim((string) ($_POST['place_of_origin'] ?? '')),
                'means_of_conveyance' => trim((string) ($_POST['means_of_conveyance'] ?? '')),
                'port_of_entry' => trim((string) ($_POST['port_of_entry'] ?? '')),
                'declared_quantity' => trim((string) ($_POST['declared_quantity'] ?? '')),
                'treatment_date' => trim((string) ($_POST['treatment_date'] ?? '')),
                'treatment_chemical' => trim((string) ($_POST['treatment_chemical'] ?? '')),
                'treatment_duration' => trim((string) ($_POST['treatment_duration'] ?? '')),
                'treatment_temperature' => trim((string) ($_POST['treatment_temperature'] ?? '')),
                'consignee' => trim((string) ($_POST['consignee'] ?? ''))
            ],
            'charges' => ['freight' => '0', 'insurance' => '0', 'other_charges' => '0'],
            'items' => $this->itemsFromRequest()
        ];
    }

    private function itemsFromRequest(): array
    {
        $items = [];
        foreach ($_POST['product_id'] ?? [] as $index => $productId) {
            $items[] = [
                'product_id' => (int) $productId,
                'hsn_code' => trim((string) ($_POST['hsn_code'][$index] ?? '')),
                'packing_type_id' => null,
                'no_of_bags' => trim((string) ($_POST['quantity'][$index] ?? '0')),
                'net_weight' => 0,
                'gross_weight' => 0,
                'dimensions' => '',
                'remarks' => trim((string) ($_POST['item_remarks'][$index] ?? ''))
            ];
        }
        return $items;
    }

    private function validateCertificate(array $data): array
    {
        return $this->validator->validate($data, [
            'document_date' => 'required',
            'buyer_id' => 'required',
            'currency_id' => 'required'
        ]);
    }

    private function findCertificateOrRedirect(int $id): array
    {
        $certificate = $this->certificates->findById($id);
        if (!$certificate) {
            Response::redirect('/phytosanitary', 'Phytosanitary Certificate not found.');
        }
        return $certificate;
    }

    public function delete(string $id): void
    {
        if (!$this->validateCsrf()) {
            Response::redirect('/phytosanitary/' . (int) $id, 'Invalid security token.');
        }
        $this->handleDocumentDelete((int)$id, '/phytosanitary', 'phytosanitary');
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
