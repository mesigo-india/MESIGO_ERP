<?php
declare(strict_types=1);

namespace App\Core;

class NonHazardousCertController extends Controller
{
    private NonHazardousCert $certificates;
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
        require_once APP_ROOT . '/classes/NonHazardousCert.php';
        $this->certificates = new NonHazardousCert(Database::getInstance());
        $this->validator = new Validator();
    }

    public function index(): void
    {
        $this->requireLogin();
        $this->requirePermission('certificate_of_origins.view');
        $search = trim((string) ($_GET['search'] ?? ''));
        $status = trim((string) ($_GET['status'] ?? ''));
        $this->render('non_hazardous_certs/index', [
            'title' => 'Non-Hazardous Certificates',
            'certificates' => $this->certificates->getAll($search, $status),
            'statuses' => NonHazardousCert::statuses(),
            'search' => $search,
            'status' => $status
        ]);
    }

    public function show(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('certificate_of_origins.view');
        $certificate = $this->findCertificateOrRedirect((int) $id);
        $this->render('non_hazardous_certs/view', [
            'title' => 'Non-Hazardous Certificate ' . $certificate['document_number'],
            'certificate' => $certificate,
            'items' => $this->certificates->getItems((int) $id),
            'meta' => $this->certificates->meta($certificate['internal_notes'] ?? null),
            'statuses' => NonHazardousCert::statuses(),
            'revisions' => $this->certificates->revisions((int) $id),
            'history' => $this->certificates->statusHistory((int) $id)
        ]);
    }

    public function edit(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('certificate_of_origins.update');
        $certificate = $this->findCertificateOrRedirect((int) $id);
        $this->renderForm('Edit Non-Hazardous Certificate', $certificate, $this->certificates->meta($certificate['internal_notes'] ?? null), $this->certificates->getItems((int) $id) ?: [[]], '/non-hazardous-certs/' . (int) $id);
    }

    public function update(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('certificate_of_origins.update');
        if (!$this->validateCsrf()) {
            Response::redirect('/non-hazardous-certs/' . (int) $id . '/edit', 'Invalid security token.');
        }
        $this->findCertificateOrRedirect((int) $id);
        $data = $this->certificateDataFromRequest();
        $data['updated_by'] = $this->currentUserId();
        $errors = $this->validateCertificate($data);
        if (!empty($errors)) {
            Response::redirect('/non-hazardous-certs/' . (int) $id . '/edit', $this->formatValidationErrors($errors));
        }
        $this->certificates->update((int) $id, $data);
        Response::redirect('/non-hazardous-certs/' . (int) $id, 'Non-Hazardous Certificate updated successfully.');
    }

    public function status(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('certificate_of_origins.update');
        if (!$this->validateCsrf()) {
            Response::redirect('/non-hazardous-certs/' . (int) $id, 'Invalid security token.');
        }
        $this->findCertificateOrRedirect((int) $id);
        $this->certificates->updateStatus((int) $id, (int) ($_POST['status'] ?? NonHazardousCert::STATUS_DRAFT), (int) $this->currentUserId(), trim((string) ($_POST['remarks'] ?? '')));
        Response::redirect('/non-hazardous-certs/' . (int) $id, 'Non-Hazardous Certificate status updated.');
    }

    public function revise(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('certificate_of_origins.update');
        if (!$this->validateCsrf()) {
            Response::redirect('/non-hazardous-certs/' . (int) $id, 'Invalid security token.');
        }
        $this->findCertificateOrRedirect((int) $id);
        $this->certificates->revise((int) $id, trim((string) ($_POST['revision_notes'] ?? 'Non-Hazardous Certificate revision saved')), $this->currentUserId());
        Response::redirect('/non-hazardous-certs/' . (int) $id, 'Non-Hazardous Certificate revision saved.');
    }

    public function delete(string $id): void
    {
        $this->requireLogin();
        if ($this->auth->user()['role_name'] !== 'admin') {
            Response::redirect('/non-hazardous-certs/' . (int) $id, 'Only administrators can delete transactions.');
        }
        if (!$this->validateCsrf()) {
            Response::redirect('/non-hazardous-certs/' . (int) $id, 'Invalid security token.');
        }
        $this->findCertificateOrRedirect((int) $id);
        $stmt = Database::getInstance()->prepare("UPDATE document_headers SET deleted_at = NOW(), deleted_by = :user_id, status = 0 WHERE id = :id");
        $stmt->execute(['user_id' => $this->currentUserId(), 'id' => (int) $id]);
        Response::redirect('/non-hazardous-certs', 'Non-Hazardous Certificate deleted successfully.');
    }

    public function print(string $id): void
    {
        $this->show($id);
    }

    private function renderForm(string $title, ?array $certificate, array $meta, array $items, string $action): void
    {
        $this->render('non_hazardous_certs/form', [
            'title' => $title,
            'certificate' => $certificate,
            'meta' => $meta,
            'items' => $items,
            'action' => $action,
            'buyers' => $this->certificates->masterRows('buyers', 'company_name'),
            'currencies' => $this->certificates->masterRows('currencies', 'code'),
            'products' => $this->certificates->masterRows('products', 'name'),
            'statuses' => NonHazardousCert::statuses()
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
            'status' => (int) ($_POST['status'] ?? NonHazardousCert::STATUS_DRAFT),
            'co_meta' => [
                'issuing_authority' => trim((string) ($_POST['issuing_authority'] ?? '')),
                'country_of_origin' => trim((string) ($_POST['country_of_origin'] ?? '')),
                'destination_country' => trim((string) ($_POST['destination_country'] ?? '')),
                'hs_code' => trim((string) ($_POST['hs_code'] ?? '')),
                'exporter' => trim((string) ($_POST['exporter'] ?? '')),
                'consignee' => trim((string) ($_POST['consignee'] ?? '')),
                'declaration' => trim((string) ($_POST['declaration'] ?? ''))
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
            Response::redirect('/non-hazardous-certs', 'Non-Hazardous Certificate not found.');
        }
        return $certificate;
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
