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

        $this->render('print_templates/designer', [
            'title' => 'Design: ' . $template['name'],
            'template' => $template,
            'settings' => $details['settings'],
            'sections' => $details['sections'],
            'watermark' => $details['watermark'],
            'signatures' => $details['signatures'],
            'qr' => $details['qr'],
            'assets' => $assets
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
            $setStmt = $db->prepare("
                UPDATE print_settings 
                SET paper_size = :size, orientation = :orient, 
                    margin_top = :mt, margin_bottom = :mb, margin_left = :ml, margin_right = :mr,
                    letterhead_mode = :lh_mode
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

        // Generate dummy/mock data for live preview
        $mockData = [
            'company' => [
                'name' => 'MESIGO ENTERPRISE EXPORTS',
                'address' => 'Branding Center, Export Zone, Chennai, India'
            ],
            'header' => [
                'document_number' => 'QTN-2026-PREVIEW',
                'document_date' => date('Y-m-d'),
                'consignee_name' => 'John Consignee Co',
                'incoterm_code' => 'CIF',
                'payment_terms' => '30% Advance, 70% LC',
                'destination_port_name' => 'Port of Rotterdam',
                'subtotal' => 12000.00,
                'freight' => 1500.00,
                'grand_total' => 13500.00,
                'terms' => "1. Price validity: 15 days.\n2. Payment terms as agreed."
            ],
            'buyer' => [
                'company_name' => 'GLOBAL DISTRIBUTORS INC',
                'address' => 'Main Avenue, Sector 4, Rotterdam, Netherlands'
            ],
            'bank' => [
                'bank_name' => 'HDFC BANK INDIA',
                'branch' => 'EXIM Branch Chennai',
                'iban' => 'IN92HDFC0002981928019',
                'swift_code' => 'HDFCINBBA'
            ],
            'items' => [
                [
                    'product_name' => 'Raw Basmati Rice - Premium Grade A',
                    'hs_code' => '10063010',
                    'quantity' => 20.00,
                    'rate' => 600.00,
                    'amount' => 12000.00
                ]
            ]
        ];

        echo $engine->compileHtml($details, $mockData);
    }
}
