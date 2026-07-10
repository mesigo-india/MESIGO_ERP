<?php
declare(strict_types=1);

namespace App\Core;

class BillOfLadingController extends Controller
{
    private BillOfLading $bills;
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
        $this->bills = new BillOfLading(Database::getInstance());
        $this->validator = new Validator();
    }

    public function index(): void
    {
        $this->requireLogin();
        $this->requirePermission('bill_of_ladings.view');
        $search = trim((string) ($_GET['search'] ?? ''));
        $status = trim((string) ($_GET['status'] ?? ''));
        $this->render('bill_of_ladings/index', ['title' => 'Bills of Lading', 'bills' => $this->bills->getAll($search, $status), 'statuses' => BillOfLading::statuses(), 'search' => $search, 'status' => $status]);
    }

    public function create(): void
    {
        $this->requireLogin();
        $this->requirePermission('bill_of_ladings.create');
        $this->renderForm('Create Bill of Lading', null, [], [[]], '/bill-of-ladings');
    }

    public function store(): void
    {
        $this->requireLogin();
        $this->requirePermission('bill_of_ladings.create');
        if (!$this->validateCsrf()) {
            Response::redirect('/bill-of-ladings/create', 'Invalid security token.');
        }
        $data = $this->billDataFromRequest();
        $data['created_by'] = $this->currentUserId();
        $errors = $this->validateBill($data);
        if (!empty($errors)) {
            Response::redirect('/bill-of-ladings/create', $this->formatValidationErrors($errors));
        }
        $id = $this->bills->create($data);
        Response::redirect('/bill-of-ladings/' . $id, 'Bill of Lading created successfully.');
    }

    public function show(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('bill_of_ladings.view');
        $bill = $this->findBillOrRedirect((int) $id);
        $this->render('bill_of_ladings/view', ['title' => 'Bill of Lading ' . $bill['document_number'], 'bill' => $bill, 'items' => $this->enrichItems($this->bills->getItems((int) $id)), 'meta' => $this->bills->meta($bill['internal_notes'] ?? null), 'statuses' => BillOfLading::statuses(), 'revisions' => $this->bills->revisions((int) $id), 'history' => $this->bills->statusHistory((int) $id)]);
    }

    public function edit(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('bill_of_ladings.update');
        $bill = $this->findBillOrRedirect((int) $id);
        $this->renderForm('Edit Bill of Lading', $bill, $this->bills->meta($bill['internal_notes'] ?? null), $this->enrichItems($this->bills->getItems((int) $id)) ?: [[]], '/bill-of-ladings/' . (int) $id);
    }

    public function update(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('bill_of_ladings.update');
        if (!$this->validateCsrf()) {
            Response::redirect('/bill-of-ladings/' . (int) $id . '/edit', 'Invalid security token.');
        }
        $this->findBillOrRedirect((int) $id);
        $data = $this->billDataFromRequest();
        $data['updated_by'] = $this->currentUserId();
        $errors = $this->validateBill($data);
        if (!empty($errors)) {
            Response::redirect('/bill-of-ladings/' . (int) $id . '/edit', $this->formatValidationErrors($errors));
        }
        $this->bills->update((int) $id, $data);
        Response::redirect('/bill-of-ladings/' . (int) $id, 'Bill of Lading updated successfully.');
    }

    public function status(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('bill_of_ladings.update');
        if (!$this->validateCsrf()) {
            Response::redirect('/bill-of-ladings/' . (int) $id, 'Invalid security token.');
        }
        $this->findBillOrRedirect((int) $id);
        $this->bills->updateStatus((int) $id, (int) ($_POST['status'] ?? BillOfLading::STATUS_DRAFT), (int) $this->currentUserId(), trim((string) ($_POST['remarks'] ?? '')));
        Response::redirect('/bill-of-ladings/' . (int) $id, 'Bill of Lading status updated.');
    }

    public function revise(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('bill_of_ladings.update');
        if (!$this->validateCsrf()) {
            Response::redirect('/bill-of-ladings/' . (int) $id, 'Invalid security token.');
        }
        $this->findBillOrRedirect((int) $id);
        $this->bills->revise((int) $id, trim((string) ($_POST['revision_notes'] ?? 'Bill of Lading revision saved')), $this->currentUserId());
        Response::redirect('/bill-of-ladings/' . (int) $id, 'Bill of Lading revision saved.');
    }

    public function print(string $id): void
    {
        $this->show($id);
    }

    public function convert(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('bill_of_ladings.convert');
        if (!$this->validateCsrf()) {
            Response::redirect('/bill-of-ladings/' . (int) $id, 'Invalid security token.');
        }
        $this->findBillOrRedirect((int) $id);
        $coId = $this->bills->convertToCertificateOfOrigin((int) $id, (int) $this->currentUserId());
        Response::redirect('/bill-of-ladings/' . (int) $id, 'Bill of Lading converted to Certificate of Origin reference #' . $coId . '.');
    }

    private function renderForm(string $title, ?array $bill, array $meta, array $items, string $action): void
    {
        $this->render('bill_of_ladings/form', ['title' => $title, 'bill' => $bill, 'meta' => $meta, 'items' => $items, 'action' => $action, 'buyers' => $this->bills->masterRows('buyers', 'company_name'), 'currencies' => $this->bills->masterRows('currencies', 'code'), 'ports' => $this->bills->masterRows('ports', 'name'), 'products' => $this->bills->masterRows('products', 'name'), 'packingTypes' => $this->bills->masterRows('packing_types', 'name'), 'statuses' => BillOfLading::statuses()]);
    }

    private function billDataFromRequest(): array
    {
        return ['document_date' => trim((string) ($_POST['document_date'] ?? date('Y-m-d'))), 'revision' => (int) ($_POST['revision'] ?? 0), 'buyer_id' => (int) ($_POST['buyer_id'] ?? 0), 'currency_id' => (int) ($_POST['currency_id'] ?? 0), 'incoterm_id' => 0, 'payment_term_id' => 0, 'shipment_term' => trim((string) ($_POST['freight'] ?? '')), 'delivery_port_id' => (int) ($_POST['pod_id'] ?? 0), 'loading_port_id' => (int) ($_POST['pol_id'] ?? 0), 'valid_until' => '', 'remarks' => trim((string) ($_POST['remarks'] ?? '')), 'status' => (int) ($_POST['status'] ?? BillOfLading::STATUS_DRAFT), 'bl_meta' => ['bl_number' => trim((string) ($_POST['bl_number'] ?? '')), 'master_bl' => trim((string) ($_POST['master_bl'] ?? '')), 'house_bl' => trim((string) ($_POST['house_bl'] ?? '')), 'carrier' => trim((string) ($_POST['carrier'] ?? '')), 'shipping_line' => trim((string) ($_POST['shipping_line'] ?? '')), 'vessel' => trim((string) ($_POST['vessel'] ?? '')), 'voyage' => trim((string) ($_POST['voyage'] ?? '')), 'container' => trim((string) ($_POST['container'] ?? '')), 'seal' => trim((string) ($_POST['seal'] ?? '')), 'freight' => trim((string) ($_POST['freight'] ?? '')), 'etd' => trim((string) ($_POST['etd'] ?? '')), 'eta' => trim((string) ($_POST['eta'] ?? '')), 'pol' => trim((string) ($_POST['pol'] ?? '')), 'pod' => trim((string) ($_POST['pod'] ?? ''))], 'charges' => ['freight' => '0', 'insurance' => '0', 'other_charges' => '0'], 'items' => $this->itemsFromRequest()];
    }

    private function itemsFromRequest(): array
    {
        $items = [];
        foreach ($_POST['product_id'] ?? [] as $index => $productId) {
            $items[] = ['product_id' => (int) $productId, 'hsn_code' => trim((string) ($_POST['hsn_code'][$index] ?? '')), 'packing_type_id' => (int) ($_POST['packing_type_id'][$index] ?? 0), 'no_of_bags' => trim((string) ($_POST['quantity'][$index] ?? '0')), 'net_weight' => trim((string) ($_POST['net_weight'][$index] ?? '0')), 'gross_weight' => trim((string) ($_POST['gross_weight'][$index] ?? '0')), 'dimensions' => '', 'remarks' => trim((string) ($_POST['item_remarks'][$index] ?? ''))];
        }
        return $items;
    }

    private function validateBill(array $data): array
    {
        return $this->validator->validate($data, ['document_date' => 'required', 'buyer_id' => 'required', 'currency_id' => 'required']);
    }

    private function enrichItems(array $items): array
    {
        foreach ($items as &$item) {
            $quality = json_decode((string) ($item['quality'] ?? ''), true);
            $item['item_remarks'] = (string) ($quality['remarks'] ?? '');
        }
        return $items;
    }

    private function findBillOrRedirect(int $id): array
    {
        $bill = $this->bills->findById($id);
        if (!$bill) {
            Response::redirect('/bill-of-ladings', 'Bill of Lading not found.');
        }
        return $bill;
    }

    public function delete(string $id): void
    {
        $this->requireLogin();
        if ($this->auth->user()['role_name'] !== 'admin') {
            Response::redirect('/bill-of-ladings/' . (int) $id, 'Only administrators can delete transactions.');
        }
        if (!$this->validateCsrf()) {
            Response::redirect('/bill-of-ladings/' . (int) $id, 'Invalid security token.');
        }
        $this->findBillOrRedirect((int) $id);
        $stmt = Database::getInstance()->prepare("UPDATE document_headers SET deleted_at = NOW(), deleted_by = :user_id, status = 0 WHERE id = :id");
        $stmt->execute(['user_id' => $this->currentUserId(), 'id' => (int) $id]);
        Response::redirect('/bill-of-ladings', 'Bill of Lading deleted successfully.');
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