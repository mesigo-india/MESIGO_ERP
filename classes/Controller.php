<?php
declare(strict_types=1);

namespace App\Core;

use Exception;

/**
 * Base Controller class
 */
abstract class Controller
{
    protected Auth $auth;
    protected Logger $logger;
    
    public function __construct()
    {
        $this->auth = new Auth(Database::getInstance());
        $this->logger = new Logger();
        if ($this->auth->isLoggedIn()) {
            $this->auth->user();
        }
    }
    
    /**
     * Render view
     */
    protected function render(string $view, array $data = []): void
    {
        $viewFile = APP_ROOT . '/templates/' . $view . '.php';
        
        if (!file_exists($viewFile)) {
            http_response_code(404);
            require_once APP_ROOT . '/404.php';
            exit;
        }
        
        extract($data);
        require APP_ROOT . '/includes/header.php';
        require $viewFile;
        require APP_ROOT . '/includes/footer.php';
    }
    
    /**
     * Redirect
     */
    protected function redirect(string $url, string $message = ''): void
    {
        Response::redirect($url, $message);
    }
    
    /**
     * Require permission
     */
    protected function requirePermission(string $permission): void
    {
        if (!$this->auth->can($permission)) {
            http_response_code(403);
            require_once APP_ROOT . '/403.php';
            exit;
        }
    }
    
    /**
     * Require login
     */
    protected function requireLogin(): void
    {
        if (!$this->auth->isLoggedIn()) {
            $this->redirect('/login');
        }
    }
    
    /**
     * Get POST data
     */
    protected function getPostData(): array
    {
        return $_POST;
    }
    
    /**
     * Get GET data
     */
    protected function getGetData(): array
    {
        return $_GET;
    }
    
    /**
     * Validate CSRF token
     */
    protected function validateCsrf(): bool
    {
        $token = $_POST['csrf_token'] ?? '';
        return Session::validateCsrfToken($token);
    }
    
    /**
     * Unified Export Architecture for CSV, PDF, and Print
     */
    protected function handleExportData(string $exportType, string $title, string $filenameBase, array $data, array $columns): void
    {
        if ($exportType === 'csv') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filenameBase . '_' . date('Ymd_His') . '.csv"');
            $f = fopen('php://output', 'w');
            fwrite($f, "\xEF\xBB\xBF"); // UTF-8 BOM
            
            // Headers
            fputcsv($f, array_keys($columns));
            
            // Rows
            foreach ($data as $row) {
                $csvRow = [];
                foreach ($columns as $header => $keyOrCallback) {
                    if (is_callable($keyOrCallback)) {
                        $csvRow[] = $keyOrCallback($row);
                    } else {
                        $csvRow[] = $row[$keyOrCallback] ?? '';
                    }
                }
                fputcsv($f, $csvRow);
            }
            fclose($f);
            exit;
        }

        if ($exportType === 'pdf' || $exportType === 'print') {
            $html = '<html><head><title>' . htmlspecialchars($title) . ' - Data Export</title><style>
                body { font-family: Helvetica, sans-serif; font-size: 10px; }
                h2 { text-align: center; color: #333; margin-bottom: 20px; }
                table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f8f9fa; font-weight: bold; color: #333; }
                tr:nth-child(even) { background-color: #fcfcfc; }
                @media print {
                    @page { margin: 0.5cm; size: landscape; }
                }
            </style></head><body>';
            
            $html .= '<h2>' . htmlspecialchars($title) . ' - Data Export</h2>';
            $html .= '<table><tr>';
            foreach (array_keys($columns) as $header) {
                $html .= '<th>' . htmlspecialchars($header) . '</th>';
            }
            $html .= '</tr>';
            
            foreach ($data as $row) {
                $html .= '<tr>';
                foreach ($columns as $header => $keyOrCallback) {
                    if (is_callable($keyOrCallback)) {
                        $val = $keyOrCallback($row);
                    } else {
                        $val = $row[$keyOrCallback] ?? '';
                    }
                    $html .= '<td>' . htmlspecialchars((string)$val) . '</td>';
                }
                $html .= '</tr>';
            }
            $html .= '</table>';
            
            if ($exportType === 'print') {
                $html .= '<script>window.onload = function() { window.print(); };</script>';
            }
            
            $html .= '</body></html>';

            if ($exportType === 'pdf') {
                if (!class_exists('\Dompdf\Dompdf')) {
                    $autoloadFile = APP_ROOT . '/vendor/autoload.php';
                    if (file_exists($autoloadFile)) {
                        require_once $autoloadFile;
                    }
                }
                $dompdf = new \Dompdf\Dompdf();
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'landscape');
                $dompdf->render();

                $filename = $filenameBase . '_List.pdf';
                // Dynamic naming logic if there's exactly 1 record
                if (count($data) === 1) {
                    $firstKey = reset($columns);
                    $firstVal = '';
                    if (!is_callable($firstKey) && isset($data[0][$firstKey])) {
                        $firstVal = preg_replace('/[^a-z0-9_\-\.]/i', '_', $data[0][$firstKey]);
                    }
                    
                    $secondKey = next($columns);
                    $secondVal = '';
                    if (!is_callable($secondKey) && isset($data[0][$secondKey])) {
                        $secondVal = preg_replace('/[^a-z0-9_\-\.]/i', '_', $data[0][$secondKey]);
                    }
                    
                    if ($firstVal && $secondVal) {
                        $filename = $firstVal . '_' . $secondVal . '.pdf';
                    }
                }

                $dompdf->stream($filename, ["Attachment" => true]);
            } else {
                echo $html;
            }
            exit;
        }
    }

    /**
     * Shared helper to extract structured document form data from the POST request
     */
    protected function extractDocumentDataFromRequest(int $defaultStatus = 0): array
    {
        $charges = [];
        if (isset($_POST['charge_amount']) && is_array($_POST['charge_amount'])) {
            foreach ($_POST['charge_amount'] as $index => $amount) {
                if (trim((string) $amount) === '' || (float) $amount <= 0.0) {
                    continue;
                }
                $charges[] = [
                    'cost_component_id' => (int) ($_POST['charge_cost_component_id'][$index] ?? 0),
                    'charge_name' => trim((string) ($_POST['charge_name'][$index] ?? 'Charge')),
                    'charge_amount' => (float) $amount,
                    'currency_id' => (int) ($_POST['charge_currency_id'][$index] ?? 1),
                    'exchange_rate' => (float) ($_POST['charge_exchange_rate'][$index] ?? 1.0),
                    'payment_method' => trim((string) ($_POST['charge_payment_method'][$index] ?? '')),
                    'remarks' => trim((string) ($_POST['charge_remarks'][$index] ?? '')),
                ];
            }
        } else {
            $charges = [
                'freight' => trim((string) ($_POST['freight'] ?? '0')),
                'insurance' => trim((string) ($_POST['insurance'] ?? '0')),
                'other_charges' => trim((string) ($_POST['other_charges'] ?? '0')),
            ];
        }

        $items = [];
        foreach ($_POST['product_id'] ?? [] as $index => $productId) {
            $items[] = [
                'product_id' => (int) $productId,
                'warehouse_id' => (int) ($_POST['warehouse_id'][$index] ?? 0),
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

        return [
            'document_date' => trim((string) ($_POST['document_date'] ?? date('Y-m-d'))),
            'company_id' => (int) ($_POST['company_id'] ?? $_SESSION['active_company_id'] ?? 1),
            'revision' => (int) ($_POST['revision'] ?? 0),
            'buyer_id' => (int) ($_POST['buyer_id'] ?? 0),
            'buyer_contact_id' => (int) ($_POST['buyer_contact_id'] ?? 0),
            'currency_id' => (int) ($_POST['currency_id'] ?? 0),
            'exchange_rate' => (float) ($_POST['exchange_rate'] ?? 1.0),
            'rate_locked' => (int) ($_POST['rate_locked'] ?? 0),
            'lut_active' => (int) ($_POST['lut_active'] ?? 0),
            'tax_basis' => trim((string) ($_POST['tax_basis'] ?? 'lut')),
            'incoterm_id' => (int) ($_POST['incoterm_id'] ?? 0),
            'payment_term_id' => (int) ($_POST['payment_term_id'] ?? 0),
            'shipment_term' => trim((string) ($_POST['shipment_term'] ?? '')),
            'delivery_port_id' => (int) ($_POST['delivery_port_id'] ?? 0),
            'loading_port_id' => (int) ($_POST['loading_port_id'] ?? 0),
            'valid_until' => trim((string) ($_POST['valid_until'] ?? '')),
            'remarks' => trim((string) ($_POST['remarks'] ?? '')),
            'status' => (int) ($_POST['status'] ?? $defaultStatus),
            'charges' => $charges,
            'items' => $items,
        ];
    }

    /**
     * Get active logged in User ID
     */
    protected function currentUserId(): ?int
    {
        return $this->auth->isLoggedIn() ? (int) ($_SESSION['user_id'] ?? 0) : null;
    }

    /**
     * Get active logged in User Role ID
     */
    protected function currentUserRoleId(): int
    {
        return $this->auth->isLoggedIn() ? (int) ($_SESSION['role_id'] ?? 0) : 0;
    }

    /**
     * Centralized Lifecycle Audit Logging
     */
    protected function logLifecycleAudit(
        string $action,
        string $module,
        int $recordId,
        string $docNo,
        ?int $oldStatus,
        ?int $newStatus,
        ?string $reason = null,
        ?array $oldData = null,
        ?array $newData = null
    ): void {
        $db = Database::getInstance();
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $userId = $this->currentUserId() ?: null;
        $roleName = $_SESSION['role_name'] ?? 'Guest';
        
        $oldPayload = [
            'document_number' => $docNo,
            'module' => $module,
            'user' => $userId,
            'role' => $roleName,
            'date' => date('Y-m-d'),
            'time' => date('H:i:s'),
            'status' => $oldStatus,
            'data' => $oldData
        ];
        
        $newPayload = [
            'document_number' => $docNo,
            'module' => $module,
            'user' => $userId,
            'role' => $roleName,
            'date' => date('Y-m-d'),
            'time' => date('H:i:s'),
            'status' => $newStatus,
            'reason' => $reason,
            'data' => $newData
        ];
        
        $stmt = $db->prepare("
            INSERT INTO audit_logs (user_id, action, table_name, record_id, old_values, new_values, ip_address, user_agent, created_at)
            VALUES (:user_id, :action, 'document_headers', :record_id, :old_values, :new_values, :ip, :ua, NOW())
        ");
        $stmt->execute([
            'user_id' => $userId,
            'action' => $action,
            'record_id' => $recordId,
            'old_values' => json_encode($oldPayload),
            'new_values' => json_encode($newPayload),
            'ip' => $ip,
            'ua' => $ua
        ]);
    }

    /**
     * Centralized validation: Check if document is editable
     */
    protected function verifyCanEdit(array $document, string $redirectUrl, string $typeCode): void
    {
        $status = (int) ($document['status'] ?? 0);
        if ($status !== 0) {
            $this->redirect($redirectUrl, 'This document cannot be modified because it is not in Draft status.');
        }

        $engine = new DocumentStatusEngine(Database::getInstance());
        $downstream = $engine->getDownstreamDocument((int) $document['id']);
        if ($downstream) {
            $this->redirect($redirectUrl, "This document cannot be modified because a downstream {$downstream['type_name']} ({$downstream['document_number']}) exists.");
        }
    }

    /**
     * Centralized validation: Check if status change transition is allowed
     */
    protected function verifyCanChangeStatus(int $id, int $fromStatus, int $toStatus, string $redirectUrl): void
    {
        $engine = new DocumentStatusEngine(Database::getInstance());
        if (!$engine->canChangeStatus($id, $fromStatus, $toStatus)) {
            $fromLabel = DocumentStatusEngine::getStatusLabel($fromStatus);
            $toLabel = DocumentStatusEngine::getStatusLabel($toStatus);
            
            $downstream = $engine->getDownstreamDocument($id);
            if ($fromStatus === DocumentStatusEngine::STATUS_APPROVED && $toStatus === DocumentStatusEngine::STATUS_CANCELLED && $downstream) {
                $this->redirect($redirectUrl . '/' . $id, "Cannot cancel this document because downstream {$downstream['type_name']} ({$downstream['document_number']}) exists.");
            }
            
            $this->redirect($redirectUrl . '/' . $id, "Status transition from {$fromLabel} to {$toLabel} is not allowed.");
        }
    }

    /**
     * Centralized helper to clone a document to create a new active revision
     */
    protected function handleDocumentRevision(int $id, string $redirectUrl, string $typeCode, object $model): int
    {
        $db = Database::getInstance();
        $document = $model->findById($id);
        if (!$document) {
            $this->redirect($redirectUrl, 'Document not found.');
        }

        $status = (int) ($document['status'] ?? 0);
        if ($status !== DocumentStatusEngine::STATUS_APPROVED) {
            $this->redirect($redirectUrl . '/' . $id, 'Only approved documents can be revised.');
        }

        // Generate next revision number & suffix the doc number
        $baseNumber = preg_replace('/-R\d+$/i', '', $document['document_number']);
        
        $revStmt = $db->prepare("SELECT COUNT(*) FROM document_headers WHERE document_number LIKE :pattern AND deleted_at IS NULL");
        $revStmt->execute(['pattern' => $baseNumber . '-R%']);
        $revCount = (int)$revStmt->fetchColumn() + 1;
        
        $newNumber = $baseNumber . '-R' . $revCount;

        // Clone header
        $meta = json_decode((string) ($document['internal_notes'] ?? null), true) ?: [];
        $meta['revision'] = $revCount;
        $meta['revised_from_id'] = $id;

        $db->beginTransaction();
        try {
            $stmt = $db->prepare("
                INSERT INTO document_headers (
                    document_type_id, document_number, document_date, company_id, buyer_id, seller_id,
                    currency_id, exchange_rate, shipment_type, incoterm_id, loading_port_id, destination_port_id,
                    payment_term_id, validity_days, expected_shipment, remarks, internal_notes, status,
                    created_by, created_at, updated_at
                ) VALUES (
                    :type_id, :doc_no, NOW(), :company_id, :buyer_id, :seller_id,
                    :currency_id, :exchange_rate, :shipment_type, :incoterm_id, :loading_port_id, :destination_port_id,
                    :payment_term_id, :validity_days, :expected_shipment, :remarks, :internal_notes, 0,
                    :user_id, NOW(), NOW()
                )
            ");
            
            $stmt->execute([
                'type_id' => $document['document_type_id'],
                'doc_no' => $newNumber,
                'company_id' => $document['company_id'] ?? 1,
                'buyer_id' => $document['buyer_id'],
                'seller_id' => $document['seller_id'],
                'currency_id' => $document['currency_id'],
                'exchange_rate' => $document['exchange_rate'],
                'shipment_type' => $document['shipment_type'],
                'incoterm_id' => $document['incoterm_id'],
                'loading_port_id' => $document['loading_port_id'],
                'destination_port_id' => $document['destination_port_id'],
                'payment_term_id' => $document['payment_term_id'],
                'validity_days' => $document['validity_days'],
                'expected_shipment' => $document['expected_shipment'],
                'remarks' => $document['remarks'],
                'internal_notes' => json_encode($meta),
                'user_id' => $this->currentUserId()
            ]);
            
            $newId = (int)$db->lastInsertId();

            // Clone items dynamically to prevent hardcoding columns
            $itemsStmt = $db->prepare("SELECT * FROM document_items WHERE document_header_id = :id");
            $itemsStmt->execute(['id' => $id]);
            $items = $itemsStmt->fetchAll(\PDO::FETCH_ASSOC);

            if (!empty($items)) {
                $cols = array_keys($items[0]);
                $insertCols = array_filter($cols, fn($c) => $c !== 'id');
                
                $placeholders = array_map(fn($c) => ":" . $c, $insertCols);
                $sql = "INSERT INTO document_items (" . implode(', ', $insertCols) . ") VALUES (" . implode(', ', $placeholders) . ")";
                $itemStmt = $db->prepare($sql);
                
                foreach ($items as $item) {
                    $item['document_header_id'] = $newId;
                    $execData = [];
                    foreach ($insertCols as $col) {
                        $execData[$col] = $item[$col];
                    }
                    $itemStmt->execute($execData);
                }
            }

            // Save revision history snapshot record for original document
            require_once APP_ROOT . '/classes/RevisionManager.php';
            $revisionManager = new RevisionManager($db);
            $notes = trim((string) ($_POST['revision_notes'] ?? 'Revision created'));
            $revisionManager->create($id, $revCount, ['header' => $document, 'items' => $items], $notes, (int)$this->currentUserId());

            // Log status history for new draft revision
            $statusEngine = new DocumentStatusEngine($db);
            $statusEngine->addHistory($newId, 0, 0, (int)$this->currentUserId(), "Revised from {$document['document_number']}");

            // Log lifecycle audit
            $this->logLifecycleAudit('Revision', $typeCode, $newId, $newNumber, null, 0, $notes, $document, null);

            $db->commit();
            return $newId;
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * Shared helper to handle document deletion validations, dependency checks, soft delete, and auditing
     */
    protected function handleDocumentDelete(int $id, string $redirectUrl, string $permissionPrefix): void
    {
        $this->requireLogin();
        $this->requirePermission($permissionPrefix . '.delete');

        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM document_headers WHERE id = :id AND deleted_at IS NULL");
        $stmt->execute(['id' => $id]);
        $document = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$document) {
            $this->redirect($redirectUrl, 'Document not found.');
        }

        if ((int)$document['status'] !== 0) {
            $this->redirect($redirectUrl . '/' . $id, 'This document cannot be deleted because it is no longer in Draft status.');
        }

        // Downstream dependency check
        $depStmt = $db->prepare("
            SELECT id, document_number, document_type_id 
            FROM document_headers 
            WHERE (converted_from_id = :id1 OR id = :converted_to_id) 
              AND id != :id2 
              AND deleted_at IS NULL 
            LIMIT 1
        ");
        $depStmt->execute([
            'id1' => $id,
            'id2' => $id,
            'converted_to_id' => $document['converted_to_id'] ?? 0
        ]);
        $child = $depStmt->fetch(\PDO::FETCH_ASSOC);

        if ($child) {
            $typeStmt = $db->prepare("SELECT name FROM document_types WHERE id = :type_id LIMIT 1");
            $typeStmt->execute(['type_id' => $child['document_type_id']]);
            $typeName = $typeStmt->fetchColumn() ?: 'downstream document';
            
            $sourceTypeStmt = $db->prepare("SELECT name FROM document_types WHERE id = :type_id LIMIT 1");
            $sourceTypeStmt->execute(['type_id' => $document['document_type_id']]);
            $sourceTypeName = $sourceTypeStmt->fetchColumn() ?: 'document';

            $this->redirect($redirectUrl . '/' . $id, "This {$sourceTypeName} has already been converted to {$typeName} {$child['document_number']} and cannot be deleted.");
        }

        $reason = trim((string)($_POST['delete_reason'] ?? ''));
        if ($reason === '') {
            $reason = 'No reason specified';
        }

        // Soft Delete
        $delStmt = $db->prepare("
            UPDATE document_headers 
            SET deleted_at = NOW(), 
                deleted_by = :user_id, 
                delete_reason = :reason
            WHERE id = :id
        ");
        $delStmt->execute([
            'user_id' => $this->currentUserId(),
            'reason' => $reason,
            'id' => $id
        ]);

        // Audit Logging using centralized lifecycle audit helper
        $typeCodeStmt = $db->prepare("SELECT code FROM document_types WHERE id = :type_id LIMIT 1");
        $typeCodeStmt->execute(['type_id' => $document['document_type_id']]);
        $typeCode = $typeCodeStmt->fetchColumn() ?: 'unknown';

        $this->logLifecycleAudit(
            'DELETE',
            $typeCode,
            $id,
            $document['document_number'],
            (int)$document['status'],
            null,
            $reason,
            $document,
            null
        );

        $this->redirect($redirectUrl, 'Document deleted successfully.');
    }

    /**
     * Render print view without standard layout header and footer
     */
    protected function renderPrint(string $view, array $data = []): void
    {
        $viewFile = APP_ROOT . '/templates/' . $view . '.php';
        
        if (!file_exists($viewFile)) {
            http_response_code(404);
            require_once APP_ROOT . '/404.php';
            exit;
        }
        
        extract($data);
        require $viewFile;
    }

    /**
     * Shared helper to print any document using the database-driven Print Studio layouts
     */
    protected function handleDocumentPrint(int $id, string $docTypeCode, array $header, array $items): void
    {
        $db = Database::getInstance();
        
        // Fetch company
        $comp = $db->query("SELECT * FROM company WHERE status = 1 ORDER BY id ASC LIMIT 1")->fetch(\PDO::FETCH_ASSOC) ?: [];
        
        // Fetch buyer
        $buyerId = (int)($header['buyer_id'] ?? 0);
        $buyerStmt = $db->prepare("SELECT * FROM buyers WHERE id = :id LIMIT 1");
        $buyerStmt->execute(['id' => $buyerId]);
        $buyer = $buyerStmt->fetch(\PDO::FETCH_ASSOC) ?: [];

        // Fetch bank
        $bank = $db->query("SELECT * FROM banks LIMIT 1")->fetch(\PDO::FETCH_ASSOC) ?: [];

        $documentData = [
            'company' => $comp,
            'header' => $header,
            'buyer' => $buyer,
            'bank' => $bank,
            'items' => $items
        ];

        require_once APP_ROOT . '/classes/PrintEngine.php';
        $printEngine = new \App\Core\PrintEngine($db);
        $tpl = $printEngine->resolveTemplate($docTypeCode, (int)($comp['id'] ?? 1), $buyerId);
        $details = $printEngine->getTemplateDetails((int)$tpl['id']);

        if (isset($_GET['export']) && $_GET['export'] === 'pdf') {
            if (!class_exists('\Dompdf\Dompdf')) {
                $autoloadFile = APP_ROOT . '/vendor/autoload.php';
                if (file_exists($autoloadFile)) {
                    require_once $autoloadFile;
                }
            }
            $html = $printEngine->compileHtml($details, $documentData);
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper($details['settings']['paper_size'] ?? 'A4', $details['settings']['orientation'] ?? 'portrait');
            $dompdf->render();
            $filename = ucfirst($docTypeCode) . '_' . ($header['document_number'] ?? (string)$id) . '.pdf';
            $dompdf->stream($filename, ["Attachment" => false]);
            exit;
        }

        $html = $printEngine->compileHtml($details, $documentData);
        echo $html;
        exit;
    }
}