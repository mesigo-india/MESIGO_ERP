<?php
declare(strict_types=1);

namespace App\Core;

class PackingListController extends Controller
{
    private PackingList $packingLists;
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
        $this->packingLists = new PackingList(Database::getInstance());
        $this->validator = new Validator();
    }

    public function index(): void
    {
        $this->requireLogin();
        $this->requirePermission('packing_lists.view');
        $search = trim((string) ($_GET['search'] ?? ''));
        $status = trim((string) ($_GET['status'] ?? ''));
        $this->render('packing_lists/index', [
            'title' => 'Packing Lists',
            'packingLists' => $this->packingLists->getAll($search, $status),
            'statuses' => PackingList::statuses(),
            'search' => $search,
            'status' => $status,
        ]);
    }

    public function create(): void
    {
        $this->requireLogin();
        $this->requirePermission('packing_lists.create');
        $this->renderForm('Create Packing List', null, [], [[]], '/packing-lists');
    }

    public function store(): void
    {
        $this->requireLogin();
        $this->requirePermission('packing_lists.create');
        if (!$this->validateCsrf()) {
            Response::redirect('/packing-lists/create', 'Invalid security token.');
        }
        $data = $this->packingListDataFromRequest();
        $data['created_by'] = $this->currentUserId();
        $errors = $this->validatePackingList($data);
        if (!empty($errors)) {
            Response::redirect('/packing-lists/create', $this->formatValidationErrors($errors));
        }
        $id = $this->packingLists->create($data);
        require_once APP_ROOT . '/classes/UnitConversionEngine.php';
        require_once APP_ROOT . '/classes/ContainerLoadingEngine.php';
        $containerEngine = new ContainerLoadingEngine(Database::getInstance(), new UnitConversionEngine(Database::getInstance()));
        $containerEngine->estimateContainers((int) $id);
        Response::redirect('/packing-lists/' . $id, 'Packing List created successfully.');
    }

    public function show(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('packing_lists.view');
        $packingList = $this->findPackingListOrRedirect((int) $id);
        $this->render('packing_lists/view', [
            'title' => 'Packing List ' . $packingList['document_number'],
            'packingList' => $packingList,
            'items' => $this->enrichItems($this->packingLists->getItems((int) $id)),
            'meta' => $this->packingLists->meta($packingList['internal_notes'] ?? null),
            'statuses' => PackingList::statuses(),
            'revisions' => $this->packingLists->revisions((int) $id),
            'history' => $this->packingLists->statusHistory((int) $id),
        ]);
    }

    public function edit(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('packing_lists.update');
        $packingList = $this->findPackingListOrRedirect((int) $id);
        $this->renderForm('Edit Packing List', $packingList, $this->packingLists->meta($packingList['internal_notes'] ?? null), $this->enrichItems($this->packingLists->getItems((int) $id)) ?: [[]], '/packing-lists/' . (int) $id);
    }

    public function update(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('packing_lists.update');
        if (!$this->validateCsrf()) {
            Response::redirect('/packing-lists/' . (int) $id . '/edit', 'Invalid security token.');
        }
        $this->findPackingListOrRedirect((int) $id);
        $data = $this->packingListDataFromRequest();
        $data['updated_by'] = $this->currentUserId();
        $errors = $this->validatePackingList($data);
        if (!empty($errors)) {
            Response::redirect('/packing-lists/' . (int) $id . '/edit', $this->formatValidationErrors($errors));
        }
        $this->packingLists->update((int) $id, $data);
        require_once APP_ROOT . '/classes/UnitConversionEngine.php';
        require_once APP_ROOT . '/classes/ContainerLoadingEngine.php';
        $containerEngine = new ContainerLoadingEngine(Database::getInstance(), new UnitConversionEngine(Database::getInstance()));
        $containerEngine->estimateContainers((int) $id);
        Response::redirect('/packing-lists/' . (int) $id, 'Packing List updated successfully.');
    }

    public function status(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('packing_lists.update');
        if (!$this->validateCsrf()) {
            Response::redirect('/packing-lists/' . (int) $id, 'Invalid security token.');
        }
        $this->findPackingListOrRedirect((int) $id);
        $this->packingLists->updateStatus((int) $id, (int) ($_POST['status'] ?? ProformaInvoice::STATUS_DRAFT), (int) $this->currentUserId(), trim((string) ($_POST['remarks'] ?? '')));
        Response::redirect('/packing-lists/' . (int) $id, 'Packing List status updated.');
    }

    public function revise(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('packing_lists.update');
        if (!$this->validateCsrf()) {
            Response::redirect('/packing-lists/' . (int) $id, 'Invalid security token.');
        }
        $this->findPackingListOrRedirect((int) $id);
        $this->packingLists->revise((int) $id, trim((string) ($_POST['revision_notes'] ?? 'Packing List revision saved')), $this->currentUserId());
        Response::redirect('/packing-lists/' . (int) $id, 'Packing List revision saved.');
    }

    public function print(string $id): void
    {
        $this->show($id);
    }

    public function email(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('packing_lists.view');
        $this->findPackingListOrRedirect((int) $id);
        Response::redirect('/packing-lists/' . (int) $id, 'Packing List is email-ready.');
    }

    private function renderForm(string $title, ?array $packingList, array $meta, array $items, string $action): void
    {
        $this->render('packing_lists/form', [
            'title' => $title,
            'packingList' => $packingList,
            'meta' => $meta,
            'items' => $items,
            'action' => $action,
            'buyers' => $this->packingLists->masterRows('buyers', 'company_name'),
            'buyerContacts' => $this->packingLists->contacts(0),
            'currencies' => $this->packingLists->masterRows('currencies', 'code'),
            'incoterms' => $this->packingLists->masterRows('incoterms', 'code'),
            'paymentTerms' => $this->packingLists->masterRows('payment_terms', 'name'),
            'ports' => $this->packingLists->masterRows('ports', 'name'),
            'products' => $this->packingLists->masterRows('products', 'name'),
            'grades' => $this->packingLists->masterRows('product_grades', 'name'),
            'origins' => $this->packingLists->masterRows('product_origins', 'name'),
            'packingTypes' => $this->packingLists->masterRows('packing_types', 'name'),
            'units' => $this->packingLists->masterRows('units', 'name'),
            'statuses' => PackingList::statuses(),
        ]);
    }

    private function packingListDataFromRequest(): array
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
            'status' => (int) ($_POST['status'] ?? PackingList::STATUS_DRAFT),
            'packing_meta' => [
                'consignee' => trim((string) ($_POST['consignee'] ?? '')),
                'notify_party' => trim((string) ($_POST['notify_party'] ?? '')),
                'marks_numbers' => trim((string) ($_POST['marks_numbers'] ?? '')),
                'container_no' => trim((string) ($_POST['container_no'] ?? '')),
                'seal_no' => trim((string) ($_POST['seal_no'] ?? '')),
                'gross_weight' => trim((string) ($_POST['gross_weight'] ?? '0')),
                'net_weight' => trim((string) ($_POST['net_weight'] ?? '0')),
                'total_packages' => trim((string) ($_POST['total_packages'] ?? '0')),
                'package_type' => trim((string) ($_POST['package_type'] ?? '')),
            ],
            'charges' => ['freight' => trim((string) ($_POST['freight'] ?? '0')), 'insurance' => trim((string) ($_POST['insurance'] ?? '0')), 'other_charges' => trim((string) ($_POST['other_charges'] ?? '0'))],
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
                'no_of_bags' => trim((string) ($_POST['no_of_bags'][$index] ?? '0')),
                'unit_id' => (int) ($_POST['unit_id'][$index] ?? 0),
                'net_weight' => trim((string) ($_POST['net_weight_item'][$index] ?? '0')),
                'gross_weight' => trim((string) ($_POST['gross_weight_item'][$index] ?? '0')),
                'dimensions' => trim((string) ($_POST['dimensions'][$index] ?? '')),
                'remarks' => trim((string) ($_POST['item_remarks'][$index] ?? '')),
            ];
        }
        return $items;
    }

    private function validatePackingList(array $data): array
    {
        return $this->validator->validate($data, ['document_date' => 'required', 'buyer_id' => 'required', 'currency_id' => 'required']);
    }

    private function enrichItems(array $items): array
    {
        foreach ($items as &$item) {
            $quality = json_decode((string) ($item['quality'] ?? ''), true);
            $item['dimensions'] = (string) ($quality['dimensions'] ?? '');
            $item['item_remarks'] = (string) ($quality['remarks'] ?? '');
            $item['no_of_bags'] = $item['quantity'] ?? 0;
        }
        return $items;
    }

    private function findPackingListOrRedirect(int $id): array
    {
        $packingList = $this->packingLists->findById($id);
        if (!$packingList) {
            Response::redirect('/packing-lists', 'Packing List not found.');
        }
        return $packingList;
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