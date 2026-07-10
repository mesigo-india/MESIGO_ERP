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
}