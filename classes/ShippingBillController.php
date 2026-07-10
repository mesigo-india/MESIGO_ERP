<?php
declare(strict_types=1);

namespace App\Core;

class ShippingBillController extends Controller
{
    private ShippingBill $shippingBills;
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
        $this->shippingBills = new ShippingBill(Database::getInstance());
        $this->validator = new Validator();
    }

    public function index(): void
    {
        $this->requireLogin();
        $this->requirePermission('shipping_bills.view');
        $search = trim((string) ($_GET['search'] ?? ''));
        $status = trim((string) ($_GET['status'] ?? ''));
        $this->render('shipping_bills/index', [
            'title' => 'Shipping Bills',
            'shippingBills' => $this->shippingBills->getAll($search, $status),
            'statuses' => ShippingBill::statuses(),
            'search' => $search,
            'status' => $status,
        ]);
    }

    public function create(): void
    {
        $this->requireLogin();
        $this->requirePermission('shipping_bills.create');
        $this->renderForm('Create Shipping Bill', null, [], [[]], '/shipping-bills');
    }

    public function store(): void
    {
        $this->requireLogin();
        $this->requirePermission('shipping_bills.create');
        if (!$this->validateCsrf()) {
            Response::redirect('/shipping-bills/create', 'Invalid security token.');
        }
        $data = $this->shippingBillDataFromRequest();
        $data['created_by'] = $this->currentUserId();
        $errors = $this->validateShippingBill($data);
        if (!empty($errors)) {
            Response::redirect('/shipping-bills/create', $this->formatValidationErrors($errors));
        }
        $id = $this->shippingBills->create($data);
        Response::redirect('/shipping-bills/' . $id, 'Shipping Bill created successfully.');
    }

    public function show(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('shipping_bills.view');
        $shippingBill = $this->findShippingBillOrRedirect((int) $id);
        $this->render('shipping_bills/view', [
            'title' => 'Shipping Bill ' . $shippingBill['document_number'],
            'shippingBill' => $shippingBill,
            'items' => $this->enrichItems($this->shippingBills->getItems((int) $id)),
            'meta' => $this->shippingBills->meta($shippingBill['internal_notes'] ?? null),
            'statuses' => ShippingBill::statuses(),
            'revisions' => $this->shippingBills->revisions((int) $id),
            'history' => $this->shippingBills->statusHistory((int) $id),
        ]);
    }

    public function edit(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('shipping_bills.update');
        $shippingBill = $this->findShippingBillOrRedirect((int) $id);
        $this->renderForm('Edit Shipping Bill', $shippingBill, $this->shippingBills->meta($shippingBill['internal_notes'] ?? null), $this->enrichItems($this->shippingBills->getItems((int) $id)) ?: [[]], '/shipping-bills/' . (int) $id);
    }

    public function update(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('shipping_bills.update');
        if (!$this->validateCsrf()) {
            Response::redirect('/shipping-bills/' . (int) $id . '/edit', 'Invalid security token.');
        }
        $this->findShippingBillOrRedirect((int) $id);
        $data = $this->shippingBillDataFromRequest();
        $data['updated_by'] = $this->currentUserId();
        $errors = $this->validateShippingBill($data);
        if (!empty($errors)) {
            Response::redirect('/shipping-bills/' . (int) $id . '/edit', $this->formatValidationErrors($errors));
        }
        $this->shippingBills->update((int) $id, $data);
        Response::redirect('/shipping-bills/' . (int) $id, 'Shipping Bill updated successfully.');
    }

    public function status(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('shipping_bills.update');
        if (!$this->validateCsrf()) {
            Response::redirect('/shipping-bills/' . (int) $id, 'Invalid security token.');
        }
        $this->findShippingBillOrRedirect((int) $id);
        $this->shippingBills->updateStatus((int) $id, (int) ($_POST['status'] ?? ShippingBill::STATUS_DRAFT), (int) $this->currentUserId(), trim((string) ($_POST['remarks'] ?? '')));
        Response::redirect('/shipping-bills/' . (int) $id, 'Shipping Bill status updated.');
    }

    public function revise(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('shipping_bills.update');
        if (!$this->validateCsrf()) {
            Response::redirect('/shipping-bills/' . (int) $id, 'Invalid security token.');
        }
        $this->findShippingBillOrRedirect((int) $id);
        $this->shippingBills->revise((int) $id, trim((string) ($_POST['revision_notes'] ?? 'Shipping Bill revision saved')), $this->currentUserId());
        Response::redirect('/shipping-bills/' . (int) $id, 'Shipping Bill revision saved.');
    }

    public function print(string $id): void
    {
        $this->show($id);
    }

    public function email(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('shipping_bills.view');
        $this->findShippingBillOrRedirect((int) $id);
        Response::redirect('/shipping-bills/' . (int) $id, 'Shipping Bill is email-ready.');
    }

    public function convert(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('shipping_bills.convert');
        if (!$this->validateCsrf()) {
            Response::redirect('/shipping-bills/' . (int) $id, 'Invalid security token.');
        }
        $this->findShippingBillOrRedirect((int) $id);
        $billOfLadingId = $this->shippingBills->convertToBillOfLading((int) $id, (int) $this->currentUserId());
        Response::redirect('/shipping-bills/' . (int) $id, 'Shipping Bill converted to Bill of Lading reference #' . $billOfLadingId . '.');
    }

    private function renderForm(string $title, ?array $shippingBill, array $meta, array $items, string $action): void
    {
        $this->render('shipping_bills/form', [
            'title' => $title,
            'shippingBill' => $shippingBill,
            'meta' => $meta,
            'items' => $items,
            'action' => $action,
            'buyers' => $this->shippingBills->masterRows('buyers', 'company_name'),
            'currencies' => $this->shippingBills->masterRows('currencies', 'code'),
            'incoterms' => $this->shippingBills->masterRows('incoterms', 'code'),
            'paymentTerms' => $this->shippingBills->masterRows('payment_terms', 'name'),
            'ports' => $this->shippingBills->masterRows('ports', 'name'),
            'products' => $this->shippingBills->masterRows('products', 'name'),
            'packingTypes' => $this->shippingBills->masterRows('packing_types', 'name'),
            'statuses' => ShippingBill::statuses(),
        ]);
    }

    private function shippingBillDataFromRequest(): array
    {
        return [
            'document_date' => trim((string) ($_POST['document_date'] ?? date('Y-m-d'))),
            'revision' => (int) ($_POST['revision'] ?? 0),
            'buyer_id' => (int) ($_POST['buyer_id'] ?? 0),
            'currency_id' => (int) ($_POST['currency_id'] ?? 0),
            'incoterm_id' => (int) ($_POST['incoterm_id'] ?? 0),
            'payment_term_id' => (int) ($_POST['payment_term_id'] ?? 0),
            'shipment_term' => trim((string) ($_POST['shipment_term'] ?? '')),
            'delivery_port_id' => (int) ($_POST['delivery_port_id'] ?? 0),
            'loading_port_id' => (int) ($_POST['loading_port_id'] ?? 0),
            'valid_until' => '',
            'remarks' => trim((string) ($_POST['remarks'] ?? '')),
            'status' => (int) ($_POST['status'] ?? ShippingBill::STATUS_DRAFT),
            'shipping_meta' => [
                'shipping_bill_no' => trim((string) ($_POST['shipping_bill_no'] ?? '')),
                'shipping_bill_date' => trim((string) ($_POST['shipping_bill_date'] ?? '')),
                'port' => trim((string) ($_POST['port'] ?? '')),
                'cha' => trim((string) ($_POST['cha'] ?? '')),
                'custom_house' => trim((string) ($_POST['custom_house'] ?? '')),
                'container_details' => trim((string) ($_POST['container_details'] ?? '')),
                'leo' => trim((string) ($_POST['leo'] ?? '')),
                'drawback' => trim((string) ($_POST['drawback'] ?? '')),
                'scheme' => trim((string) ($_POST['scheme'] ?? '')),
                'exporter_details' => trim((string) ($_POST['exporter_details'] ?? '')),
            ],
            'charges' => ['freight' => '0', 'insurance' => '0', 'other_charges' => '0'],
            'items' => $this->itemsFromRequest(),
        ];
    }

    private function itemsFromRequest(): array
    {
        $items = [];
        foreach ($_POST['product_id'] ?? [] as $index => $productId) {
            $items[] = [
                'product_id' => (int) $productId,
                'hsn_code' => trim((string) ($_POST['hsn_code'][$index] ?? '')),
                'packing_type_id' => (int) ($_POST['packing_type_id'][$index] ?? 0),
                'no_of_bags' => trim((string) ($_POST['quantity'][$index] ?? '0')),
                'net_weight' => trim((string) ($_POST['net_weight'][$index] ?? '0')),
                'gross_weight' => trim((string) ($_POST['gross_weight'][$index] ?? '0')),
                'dimensions' => '',
                'remarks' => trim((string) ($_POST['item_remarks'][$index] ?? '')),
            ];
        }
        return $items;
    }

    private function validateShippingBill(array $data): array
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

    private function findShippingBillOrRedirect(int $id): array
    {
        $shippingBill = $this->shippingBills->findById($id);
        if (!$shippingBill) {
            Response::redirect('/shipping-bills', 'Shipping Bill not found.');
        }
        return $shippingBill;
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