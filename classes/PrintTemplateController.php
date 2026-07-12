<?php
declare(strict_types=1);

namespace App\Core;

use Exception;

/**
 * Controller for Enterprise Print Studio Template Designer
 */
class PrintTemplateController extends Controller
{
    /**
     * List all print templates
     */
    public function index(): void
    {
        $this->requireLogin();
        
        $db = Database::getInstance();
        $stmt = $db->query("SELECT * FROM print_templates ORDER BY document_type ASC, is_active DESC");
        $templates = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->render('print_templates/index', [
            'title' => 'Print Studio - Template Manager',
            'templates' => $templates
        ]);
    }

    /**
     * Interactive drag-and-drop designer canvas
     */
    public function edit(string $id): void
    {
        $this->requireLogin();
        $db = Database::getInstance();

        $stmt = $db->prepare("SELECT * FROM print_templates WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $template = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$template) {
            $this->redirect('/administration/print-studio', 'Template not found.');
        }

        require_once APP_ROOT . '/classes/PrintEngine.php';
        $engine = new PrintEngine($db);
        $details = $engine->getTemplateDetails((int)$id);

        // Fetch company assets for stamp/signature/logo select fields
        $assetsStmt = $db->query("SELECT * FROM print_company_assets ORDER BY name ASC");
        $assets = $assetsStmt->fetchAll(\PDO::FETCH_ASSOC);

        // Fetch recent documents for Live Preview selectors
        $typeStmt = $db->prepare("SELECT id FROM document_types WHERE code = :code LIMIT 1");
        $typeStmt->execute(['code' => $template['document_type']]);
        $typeId = (int)$typeStmt->fetchColumn();

        $previewDocs = [];
        if ($typeId > 0) {
            $docStmt = $db->prepare("
                SELECT id, document_number, document_date 
                FROM document_headers 
                WHERE document_type_id = :type_id AND deleted_at IS NULL
                ORDER BY id DESC 
                LIMIT 15
            ");
            $docStmt->execute(['type_id' => $typeId]);
            $previewDocs = $docStmt->fetchAll(\PDO::FETCH_ASSOC);
        }
        $company = $db->query("SELECT * FROM company ORDER BY id ASC LIMIT 1")->fetch(\PDO::FETCH_ASSOC) ?: [];

        $this->render('print_templates/designer', [
            'title' => 'Design: ' . $template['name'],
            'template' => $template,
            'settings' => $details['settings'],
            'sections' => $details['sections'],
            'watermark' => $details['watermark'],
            'signatures' => $details['signatures'],
            'qr' => $details['qr'],
            'assets' => $assets,
            'previewDocs' => $previewDocs,
            'company' => $company
        ]);
    }

    /**
     * Save designer workspace layout configurations
     */
    public function update(string $id): void
    {
        $this->requireLogin();
        $db = Database::getInstance();

        $stmt = $db->prepare("SELECT * FROM print_templates WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $template = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$template) {
            $this->redirect('/administration/print-studio', 'Template not found.');
        }

        $db->beginTransaction();
        try {
            // 1. Update settings
            $metaData = [
                'logo_width' => (int)($_POST['logo_width'] ?? 120),
                'company_name_override' => trim((string)($_POST['company_name_override'] ?? '')),
                'company_address_override' => trim((string)($_POST['company_address_override'] ?? ''))
            ];

            $setStmt = $db->prepare("
                UPDATE print_settings 
                SET paper_size = :size, orientation = :orient, 
                    margin_top = :mt, margin_bottom = :mb, margin_left = :ml, margin_right = :mr,
                    letterhead_mode = :lh_mode, theme_code = :theme, print_metadata_json = :meta
                WHERE template_id = :tpl_id
            ");
            $setStmt->execute([
                'size' => $_POST['paper_size'] ?? 'A4',
                'orient' => $_POST['orientation'] ?? 'portrait',
                'mt' => (float)($_POST['margin_top'] ?? 15.0),
                'mb' => (float)($_POST['margin_bottom'] ?? 15.0),
                'ml' => (float)($_POST['margin_left'] ?? 15.0),
                'mr' => (float)($_POST['margin_right'] ?? 15.0),
                'lh_mode' => $_POST['letterhead_mode'] ?? 'blank',
                'theme' => $_POST['theme_code'] ?? 'mesigo-professional',
                'meta' => json_encode($metaData),
                'tpl_id' => $id
            ]);

            // 2. Update Watermark settings
            $wmStmt = $db->prepare("
                INSERT INTO print_watermarks (template_id, watermark_type, text_value, opacity, rotation, scale, position_x, position_y)
                VALUES (:tpl_id, :type, :text, :opacity, :rot, 100.00, 'center', 'center')
                ON DUPLICATE KEY UPDATE 
                    watermark_type = VALUES(watermark_type),
                    text_value = VALUES(text_value),
                    opacity = VALUES(opacity),
                    rotation = VALUES(rotation)
            ");
            $wmStmt->execute([
                'tpl_id' => $id,
                'type' => $_POST['watermark_type'] ?? 'text',
                'text' => $_POST['watermark_text'] ?? '',
                'opacity' => (float)($_POST['watermark_opacity'] ?? 0.15),
                'rot' => (int)($_POST['watermark_rotation'] ?? -30)
            ]);

            // 3. Update field visibility & custom labels
            if (isset($_POST['fields']) && is_array($_POST['fields'])) {
                foreach ($_POST['fields'] as $fId => $fData) {
                    $fStmt = $db->prepare("
                        UPDATE print_fields 
                        SET custom_label = :label, 
                            is_visible = :vis, 
                            col_span = :span, 
                            sort_order = :sort
                        WHERE id = :fid
                    ");
                    $fStmt->execute([
                        'label' => $fData['label'] ?? null,
                        'vis' => !empty($fData['visible']) ? 1 : 0,
                        'span' => (int)($fData['span'] ?? 6),
                        'sort' => (int)($fData['sort'] ?? 0),
                        'fid' => $fId
                    ]);
                }
            }

            // 4. Update section visibility & order
            if (isset($_POST['sections']) && is_array($_POST['sections'])) {
                foreach ($_POST['sections'] as $secId => $secData) {
                    $sStmt = $db->prepare("
                        UPDATE print_template_sections 
                        SET is_visible = :vis, 
                            sort_order = :sort
                        WHERE id = :sid
                    ");
                    $sStmt->execute([
                        'vis' => !empty($secData['visible']) ? 1 : 0,
                        'sort' => (int)($secData['sort'] ?? 0),
                        'sid' => $secId
                    ]);
                }
            }

            $db->commit();
            $this->redirect('/administration/print-studio/' . $id . '/edit', 'Template configurations saved.');
        } catch (Exception $e) {
            $db->rollBack();
            $this->redirect('/administration/print-studio/' . $id . '/edit', 'Save failed: ' . $e->getMessage());
        }
    }

    /**
     * Real-time compiled live HTML preview
     */
    public function preview(string $id): void
    {
        $this->requireLogin();
        $db = Database::getInstance();

        $stmt = $db->prepare("SELECT * FROM print_templates WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $template = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$template) {
            echo "Template not found.";
            return;
        }

        require_once APP_ROOT . '/classes/PrintEngine.php';
        $engine = new PrintEngine($db);
        $details = $engine->getTemplateDetails((int)$id);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Override settings
            $details['settings']['paper_size'] = $_POST['paper_size'] ?? $details['settings']['paper_size'];
            $details['settings']['orientation'] = $_POST['orientation'] ?? $details['settings']['orientation'];
            $details['settings']['letterhead_mode'] = $_POST['letterhead_mode'] ?? $details['settings']['letterhead_mode'];
            $details['settings']['margin_top'] = $_POST['margin_top'] ?? $details['settings']['margin_top'];
            $details['settings']['margin_bottom'] = $_POST['margin_bottom'] ?? $details['settings']['margin_bottom'];
            $details['settings']['margin_left'] = $_POST['margin_left'] ?? $details['settings']['margin_left'];
            $details['settings']['margin_right'] = $_POST['margin_right'] ?? $details['settings']['margin_right'];
            $details['settings']['theme_code'] = $_POST['theme_code'] ?? ($details['settings']['theme_code'] ?? 'mesigo-professional');

            // Override watermark
            if (!$details['watermark']) {
                $details['watermark'] = [
                    'watermark_type' => 'text',
                    'text_value' => '',
                    'opacity' => 0.15,
                    'rotation' => -30
                ];
            }
            $details['watermark']['watermark_type'] = $_POST['watermark_type'] ?? $details['watermark']['watermark_type'];
            $details['watermark']['text_value'] = $_POST['watermark_text'] ?? $details['watermark']['text_value'];
            $details['watermark']['opacity'] = $_POST['watermark_opacity'] ?? $details['watermark']['opacity'];
            $details['watermark']['rotation'] = $_POST['watermark_rotation'] ?? $details['watermark']['rotation'];

            // Override logo width & company overrides
            $details['settings']['print_metadata_json'] = json_encode([
                'logo_width' => (int)($_POST['logo_width'] ?? 120),
                'company_name_override' => trim((string)($_POST['company_name_override'] ?? '')),
                'company_address_override' => trim((string)($_POST['company_address_override'] ?? ''))
            ]);

            // Override sections and fields
            $postSecs = $_POST['sections'] ?? [];
            $postFields = $_POST['fields'] ?? [];

            foreach ($details['sections'] as $secCode => &$sec) {
                $secId = (string)$sec['id'];
                if (isset($postSecs[$secId])) {
                    $sec['is_visible'] = !empty($postSecs[$secId]['visible']) ? 1 : 0;
                    $sec['sort_order'] = isset($postSecs[$secId]['sort']) ? (int)$postSecs[$secId]['sort'] : $sec['sort_order'];
                }
                
                // Override fields in this section
                foreach ($sec['fields'] as &$field) {
                    $fieldId = (string)$field['id'];
                    if (isset($postFields[$fieldId])) {
                        $field['custom_label'] = $postFields[$fieldId]['label'] ?? $field['custom_label'];
                        $field['is_visible'] = !empty($postFields[$fieldId]['visible']) ? 1 : 0;
                        $field['col_span'] = isset($postFields[$fieldId]['span']) ? (int)$postFields[$fieldId]['span'] : $field['col_span'];
                        $field['sort_order'] = isset($postFields[$fieldId]['sort']) ? (int)$postFields[$fieldId]['sort'] : $field['sort_order'];
                    }
                }
                
                // Sort fields by their (potentially updated) sort_order
                usort($sec['fields'], fn($a, $b) => (int)$a['sort_order'] <=> (int)$b['sort_order']);
            }
            unset($sec); // break reference

            // Sort sections by their (potentially updated) sort_order
            uasort($details['sections'], fn($a, $b) => (int)$a['sort_order'] <=> (int)$b['sort_order']);
        }

        // Live database records rendering
        $previewDocId = (int)($_POST['preview_document_id'] ?? $_GET['preview_document_id'] ?? 0);
        $documentData = [];

        if ($previewDocId > 0) {
            $headerStmt = $db->prepare("
                SELECT dh.*, lp.name AS loading_port_name, dp.name AS destination_port_name,
                       i.code AS incoterm_code, pt.name AS payment_terms
                FROM document_headers dh
                LEFT JOIN ports lp ON dh.loading_port_id = lp.id
                LEFT JOIN ports dp ON dh.destination_port_id = dp.id
                LEFT JOIN incoterms i ON dh.incoterm_id = i.id
                LEFT JOIN payment_terms pt ON dh.payment_term_id = pt.id
                WHERE dh.id = :id AND dh.deleted_at IS NULL
            ");
            $headerStmt->execute(['id' => $previewDocId]);
            $header = $headerStmt->fetch(\PDO::FETCH_ASSOC);

            if ($header) {
                // Resolve document type label
                $typeId = (int)($header['document_type_id'] ?? 0);
                $typeLabel = $db->query("SELECT name FROM document_types WHERE id = {$typeId}")->fetchColumn() ?: 'DOCUMENT';
                $header['document_type_label'] = $typeLabel;

                $buyerId = (int)($header['buyer_id'] ?? 0);
                $buyerStmt = $db->prepare("SELECT * FROM buyers WHERE id = :id LIMIT 1");
                $buyerStmt->execute(['id' => $buyerId]);
                $buyer = $buyerStmt->fetch(\PDO::FETCH_ASSOC) ?: [];

                $bank = $db->query("SELECT * FROM banks LIMIT 1")->fetch(\PDO::FETCH_ASSOC) ?: [];
                $company = $db->query("SELECT * FROM company WHERE status = 1 ORDER BY id ASC LIMIT 1")->fetch(\PDO::FETCH_ASSOC) ?: [];

                $itemsStmt = $db->prepare("
                    SELECT di.*, p.name AS product_name, p.product_code 
                    FROM document_items di
                    LEFT JOIN products p ON di.product_id = p.id
                    WHERE di.document_header_id = :id
                    ORDER BY di.sort_order ASC, di.id ASC
                ");
                $itemsStmt->execute(['id' => $previewDocId]);
                $items = $itemsStmt->fetchAll(\PDO::FETCH_ASSOC);

                $documentData = [
                    'company' => $company,
                    'header' => $header,
                    'buyer' => $buyer,
                    'bank' => $bank,
                    'items' => $items
                ];
            }
        }

        if (empty($documentData)) {
            // Fallback: Query the latest document of this type
            $typeStmt = $db->prepare("SELECT id FROM document_types WHERE code = :code LIMIT 1");
            $typeStmt->execute(['code' => $template['document_type']]);
            $typeId = (int)$typeStmt->fetchColumn();

            if ($typeId > 0) {
                $latestStmt = $db->prepare("
                    SELECT id FROM document_headers 
                    WHERE document_type_id = :type_id AND deleted_at IS NULL
                    ORDER BY id DESC LIMIT 1
                ");
                $latestStmt->execute(['type_id' => $typeId]);
                $latestId = (int)$latestStmt->fetchColumn();

                if ($latestId > 0) {
                    $headerStmt = $db->prepare("
                        SELECT dh.*, lp.name AS loading_port_name, dp.name AS destination_port_name,
                               i.code AS incoterm_code, pt.name AS payment_terms
                        FROM document_headers dh
                        LEFT JOIN ports lp ON dh.loading_port_id = lp.id
                        LEFT JOIN ports dp ON dh.destination_port_id = dp.id
                        LEFT JOIN incoterms i ON dh.incoterm_id = i.id
                        LEFT JOIN payment_terms pt ON dh.payment_term_id = pt.id
                        WHERE dh.id = :id
                    ");
                    $headerStmt->execute(['id' => $latestId]);
                    $header = $headerStmt->fetch(\PDO::FETCH_ASSOC);

                    if ($header) {
                        // Resolve document type label
                        $typeId = (int)($header['document_type_id'] ?? 0);
                        $typeLabel = $db->query("SELECT name FROM document_types WHERE id = {$typeId}")->fetchColumn() ?: 'DOCUMENT';
                        $header['document_type_label'] = $typeLabel;

                        $buyerStmt = $db->prepare("SELECT * FROM buyers WHERE id = :id LIMIT 1");
                        $buyerStmt->execute(['id' => (int)$header['buyer_id']]);
                        $buyer = $buyerStmt->fetch(\PDO::FETCH_ASSOC) ?: [];
                        $bank = $db->query("SELECT * FROM banks LIMIT 1")->fetch(\PDO::FETCH_ASSOC) ?: [];
                        $company = $db->query("SELECT * FROM company WHERE status = 1 ORDER BY id ASC LIMIT 1")->fetch(\PDO::FETCH_ASSOC) ?: [];
                        
                        $itemsStmt = $db->prepare("
                            SELECT di.*, p.name AS product_name, p.product_code 
                            FROM document_items di
                            LEFT JOIN products p ON di.product_id = p.id
                            WHERE di.document_header_id = :id
                            ORDER BY di.sort_order ASC, di.id ASC
                        ");
                        $itemsStmt->execute(['id' => $latestId]);
                        $items = $itemsStmt->fetchAll(\PDO::FETCH_ASSOC);

                        $documentData = [
                            'company' => $company,
                            'header' => $header,
                            'buyer' => $buyer,
                            'bank' => $bank,
                            'items' => $items
                        ];
                    }
                }
            }
        }

        // Mock data baseline fallback
        if (empty($documentData)) {
            $company = $db->query("SELECT * FROM company ORDER BY id ASC LIMIT 1")->fetch(\PDO::FETCH_ASSOC) ?: [];
            
            $docType = $template['document_type'];
            $docTypesMap = [
                'quotation' => 'QUOTATION',
                'proforma_invoice' => 'PROFORMA INVOICE',
                'commercial_invoice' => 'COMMERCIAL INVOICE',
                'packing_list' => 'PACKING LIST',
                'shipping_bill' => 'SHIPPING BILL',
                'bill_of_lading_draft' => 'BILL OF LADING (DRAFT)',
                'bill_of_lading_final' => 'BILL OF LADING',
                'certificate_of_origin' => 'CERTIFICATE OF ORIGIN',
                'phytosanitary_certificate' => 'PHYTOSANITARY CERTIFICATE',
                'insurance_certificate' => 'INSURANCE CERTIFICATE',
                'weight_certificate' => 'WEIGHT CERTIFICATE',
                'inspection_certificate' => 'INSPECTION CERTIFICATE',
                'purchase_order' => 'PURCHASE ORDER',
                'payment_advice' => 'PAYMENT ADVICE',
                'debit_note' => 'DEBIT NOTE',
                'credit_note' => 'CREDIT NOTE',
                'expense_voucher' => 'EXPENSE VOUCHER',
                'internal_cost_sheet' => 'INTERNAL COST SHEET'
            ];
            $label = $docTypesMap[$docType] ?? 'DOCUMENT';

            $documentData = [
                'company' => $company,
                'header' => [
                    'document_number' => strtoupper(substr($docType, 0, 3)) . '-2026-0001',
                    'document_date' => date('Y-m-d'),
                    'document_type_label' => $label,
                    'consignee_name' => 'GLOBAL EXPORTS & TRADING L.L.C.',
                    'incoterm_code' => 'FOB',
                    'payment_terms' => '30% Advance / 70% LC at sight',
                    'loading_port_name' => 'Port of Mundra, India',
                    'destination_port_name' => 'Port of Rotterdam, Netherlands',
                    'subtotal' => 12000.00,
                    'freight' => 1500.00,
                    'grand_total' => 13500.00,
                    'terms' => "1. Price validity: 30 days.\n2. Shipment: 15-20 days."
                ],
                'buyer' => [
                    'company_name' => 'GLOBAL EXPORTS & TRADING L.L.C.',
                    'address' => 'Avenue 4, Block 12, Business Bay, Dubai, UAE'
                ],
                'bank' => [
                    'bank_name' => 'STATE BANK OF INDIA',
                    'branch' => 'EXIM Branch, Ahmedabad, India',
                    'account_number' => '40918290192',
                    'ifsc_code' => 'SBIN0000600',
                    'swift_code' => 'SBININBBAHM'
                ],
                'items' => [
                    [
                        'product_name' => 'Raw Basmati Rice - Premium Grade A (1121)',
                        'hs_code' => '10063010',
                        'quantity' => 20.00,
                        'rate' => 600.00,
                        'amount' => 12000.00
                    ]
                ]
            ];

            if ($docType === 'packing_list') {
                $documentData['header']['subtotal'] = 0;
                $documentData['header']['freight'] = 0;
                $documentData['header']['grand_total'] = 0;
                $documentData['items'] = [
                    [
                        'product_name' => 'Raw Basmati Rice - Premium Grade A (800 Bags x 25 Kgs)',
                        'hs_code' => '10063010',
                        'quantity' => 800.00,
                        'rate' => 0.00,
                        'amount' => 0.00
                    ]
                ];
                $documentData['header']['terms'] = "1. Net Weight: 20,000.00 Kgs.\n2. Gross Weight: 20,240.00 Kgs.";
            }
        }

        echo $engine->compileHtml($details, $documentData);
    }
}
