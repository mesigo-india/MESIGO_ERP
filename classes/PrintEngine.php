<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use Exception;

/**
 * Print Engine for MESIGO ERP
 * Standardizes database-driven A4 PDF and HTML rendering.
 */
class PrintEngine
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Resolve active template for a document type, optionally checking contextual overrides (buyer, country, branch)
     */
    public function resolveTemplate(string $documentType, ?int $branchId = null, ?int $buyerId = null, ?string $countryCode = null): array
    {
        // 1. Try buyer-specific template
        if ($buyerId) {
            $stmt = $this->db->prepare("SELECT * FROM print_templates WHERE document_type = :type AND buyer_id = :buyer AND is_active = 1 LIMIT 1");
            $stmt->execute(['type' => $documentType, 'buyer' => $buyerId]);
            $tpl = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($tpl) return $tpl;
        }

        // 2. Try branch-specific template
        if ($branchId) {
            $stmt = $this->db->prepare("SELECT * FROM print_templates WHERE document_type = :type AND branch_id = :branch AND is_active = 1 LIMIT 1");
            $stmt->execute(['type' => $documentType, 'branch' => $branchId]);
            $tpl = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($tpl) return $tpl;
        }

        // 3. Try country-specific template
        if ($countryCode) {
            $stmt = $this->db->prepare("SELECT * FROM print_templates WHERE document_type = :type AND country_code = :country AND is_active = 1 LIMIT 1");
            $stmt->execute(['type' => $documentType, 'country' => $countryCode]);
            $tpl = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($tpl) return $tpl;
        }

        // 4. Try global active template
        $stmt = $this->db->prepare("SELECT * FROM print_templates WHERE document_type = :type AND is_active = 1 LIMIT 1");
        $stmt->execute(['type' => $documentType]);
        $tpl = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($tpl) return $tpl;

        // Fallback: Grab the first template for this type
        $stmt = $this->db->prepare("SELECT * FROM print_templates WHERE document_type = :type LIMIT 1");
        $stmt->execute(['type' => $documentType]);
        $tpl = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($tpl) return $tpl;

        throw new Exception("No print template configured for document type: {$documentType}");
    }

    /**
     * Compile layout details (sections, fields, settings, styles) for rendering
     */
    public function getTemplateDetails(int $templateId): array
    {
        // 1. Settings
        $stmt = $this->db->prepare("SELECT * FROM print_settings WHERE template_id = :id LIMIT 1");
        $stmt->execute(['id' => $templateId]);
        $settings = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        // 2. Sections (sorted)
        $stmt = $this->db->prepare("SELECT * FROM print_template_sections WHERE template_id = :id AND is_visible = 1 ORDER BY sort_order ASC");
        $stmt->execute(['id' => $templateId]);
        $sections = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 3. Fields per section
        $compiledSections = [];
        foreach ($sections as $sec) {
            $secId = (int)$sec['id'];
            $secCode = $sec['section_code'];
            
            $fStmt = $this->db->prepare("SELECT * FROM print_fields WHERE section_id = :sec_id AND is_visible = 1 ORDER BY sort_order ASC");
            $fStmt->execute(['sec_id' => $secId]);
            $fields = $fStmt->fetchAll(PDO::FETCH_ASSOC);
            
            $sec['fields'] = $fields;
            $compiledSections[$secCode] = $sec;
        }

        // 4. Watermarks
        $stmt = $this->db->prepare("SELECT * FROM print_watermarks WHERE template_id = :id LIMIT 1");
        $stmt->execute(['id' => $templateId]);
        $watermark = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

        // 5. Signatures
        $stmt = $this->db->prepare("
            SELECT ps.*, pca.file_path, pca.name as asset_name 
            FROM print_signature ps 
            JOIN print_company_assets pca ON ps.signature_asset_id = pca.id
            WHERE ps.template_id = :id AND ps.is_visible = 1 
            ORDER BY ps.sort_order ASC
        ");
        $stmt->execute(['id' => $templateId]);
        $signatures = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 6. QR Settings
        $stmt = $this->db->prepare("SELECT * FROM print_qr WHERE template_id = :id AND is_visible = 1 LIMIT 1");
        $stmt->execute(['id' => $templateId]);
        $qr = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

        return [
            'settings' => $settings,
            'sections' => $compiledSections,
            'watermark' => $watermark,
            'signatures' => $signatures,
            'qr' => $qr
        ];
    }

    /**
     * Converts a local image path to base64 for reliable Dompdf rendering
     */
    public static function getBase64Image(string $path): string
    {
        if (!file_exists($path)) {
            return '';
        }
        $data = file_get_contents($path);
        $type = pathinfo($path, PATHINFO_EXTENSION);
        return 'data:image/' . $type . ';base64,' . base64_encode($data);
    }

    /**
     * Main HTML generation compile pipeline combining business data with design templates
     */
    public function compileHtml(array $templateDetails, array $documentData): string
    {
        $settings = $templateDetails['settings'];
        $sections = $templateDetails['sections'];
        $watermark = $templateDetails['watermark'];
        $signatures = $templateDetails['signatures'];
        $qr = $templateDetails['qr'];

        $orientation = $settings['orientation'] ?? 'portrait';
        $paperSize = $settings['paper_size'] ?? 'A4';
        
        $mTop = $settings['margin_top'] ?? 15;
        $mBottom = $settings['margin_bottom'] ?? 15;
        $mLeft = $settings['margin_left'] ?? 15;
        $mRight = $settings['margin_right'] ?? 15;
        $theme = $settings['theme_code'] ?? 'mesigo-professional';

        // Extract metadata custom logo resize values
        $metadata = json_decode($settings['print_metadata_json'] ?? '{}', true) ?: [];
        $logoWidth = (int)($metadata['logo_width'] ?? 120);

        // 1. Fetch Company profile if not provided
        if (empty($documentData['company'])) {
            $stmt = $this->db->prepare("SELECT * FROM company ORDER BY id ASC LIMIT 1");
            $stmt->execute();
            $documentData['company'] = $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];
        }

        // Apply metadata overrides
        if (!empty($metadata['company_name_override'])) {
            $documentData['company']['company_name'] = $metadata['company_name_override'];
        }
        if (!empty($metadata['company_address_override'])) {
            $documentData['company']['address'] = $metadata['company_address_override'];
        }

        // 2. Decode and format company JSON address beautifully
        if (!empty($documentData['company']['address'])) {
            $addr = $documentData['company']['address'];
            $decoded = json_decode($addr, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $parts = [];
                if (!empty($decoded['line1'])) $parts[] = $decoded['line1'];
                if (!empty($decoded['line2'])) $parts[] = $decoded['line2'];
                if (!empty($decoded['city'])) $parts[] = $decoded['city'];
                if (!empty($decoded['state'])) $parts[] = $decoded['state'];
                if (!empty($decoded['country'])) $parts[] = $decoded['country'];
                if (!empty($decoded['zip'])) {
                    $zipStr = $decoded['zip'];
                    if (count($parts) > 0) {
                        $parts[count($parts) - 1] .= ' - ' . $zipStr;
                    } else {
                        $parts[] = $zipStr;
                    }
                }
                $documentData['company']['address'] = implode(', ', $parts);
            }
        }

        // 3. Resolve logo path from asset library if company logo_path is empty
        if (empty($documentData['company']['logo_path'])) {
            $logoAsset = $this->db->query("SELECT file_path FROM print_company_assets WHERE asset_type = 'logo' ORDER BY id DESC LIMIT 1")->fetchColumn();
            if ($logoAsset) {
                $documentData['company']['logo_path'] = $logoAsset;
            }
        }

        // 4. Resolve signatures array from asset library if print_signature is empty
        if (empty($signatures)) {
            $sigAssets = $this->db->query("
                SELECT 'Authorized Signatory' as authorized_person, 'Director' as designation, 100 as scale_percent, 0 as position_x, file_path 
                FROM print_company_assets 
                WHERE asset_type = 'signature'
                ORDER BY id ASC
            ")->fetchAll(\PDO::FETCH_ASSOC);
            if (!empty($sigAssets)) {
                $signatures = $sigAssets;
            }
        }

        // 5. Automatic bank details fallback
        if (empty($documentData['bank'])) {
            $stmt = $this->db->prepare("SELECT * FROM banks LIMIT 1");
            $stmt->execute();
            $documentData['bank'] = $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];
        }

        // Load custom theme CSS
        $themeCss = '';
        $cssPath = APP_ROOT . "/themes/{$theme}/theme.css";
        if (file_exists($cssPath)) {
            $themeCss = file_get_contents($cssPath);
        }

        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <style>
                @page {
                    size: <?php echo $paperSize; ?> <?php echo $orientation; ?>;
                    margin: <?php echo $mTop; ?>mm <?php echo $mRight; ?>mm <?php echo $mBottom; ?>mm <?php echo $mLeft; ?>mm;
                }
                body {
                    margin: 0;
                    padding: 0;
                    background-color: #ffffff;
                }
                
                /* Layout Grids */
                .row {
                    width: 100%;
                    clear: both;
                    content: "";
                    display: table;
                    table-layout: fixed;
                    margin-bottom: 10px;
                }
                .col-1 { width: 8.33%; float: left; }
                .col-2 { width: 16.66%; float: left; }
                .col-3 { width: 25.00%; float: left; }
                .col-4 { width: 33.33%; float: left; }
                .col-5 { width: 41.66%; float: left; }
                .col-6 { width: 50.00%; float: left; }
                .col-7 { width: 58.33%; float: left; }
                .col-8 { width: 66.66%; float: left; }
                .col-9 { width: 75.00%; float: left; }
                .col-10 { width: 83.33%; float: left; }
                .col-11 { width: 91.66%; float: left; }
                .col-12 { width: 100.00%; float: left; }

                .text-left { text-align: left; }
                .text-right { text-align: right; }
                .text-center { text-align: center; }
                .text-justify { text-align: justify; }
                .bold { font-weight: bold; }
                
                /* Screen/Iframe preview margin mapping simulation */
                .preview-page-container {
                    padding-top: <?php echo $mTop; ?>mm;
                    padding-bottom: <?php echo $mBottom; ?>mm;
                    padding-left: <?php echo $mLeft; ?>mm;
                    padding-right: <?php echo $mRight; ?>mm;
                    box-sizing: border-box;
                }

                /* Watermark overlay styling */
                <?php if ($watermark): ?>
                .watermark {
                    position: fixed;
                    top: 40%;
                    left: 20%;
                    transform: rotate(<?php echo $watermark['rotation']; ?>deg);
                    opacity: <?php echo $watermark['opacity']; ?>;
                    font-size: 55pt;
                    color: #ccc;
                    z-index: -1000;
                    text-align: center;
                    width: 100%;
                }
                <?php endif; ?>

                <?php echo $themeCss; ?>
            </style>
        </head>
        <body>
            <?php if ($watermark && $watermark['watermark_type'] === 'text'): ?>
                <div class="watermark"><?php echo htmlspecialchars($watermark['text_value']); ?></div>
            <?php endif; ?>

            <div class="preview-page-container">
                <?php
                // Include dynamic layout blocks sequentially
                foreach ($sections as $secCode => $sec) {
                    if (empty($sec['is_visible'])) {
                        continue;
                    }
                    // Map section codes to file base names
                    $blockName = $secCode;
                    if ($blockName === 'grid') {
                        $blockName = 'table';
                    }
                    if ($blockName === 'signatures') {
                        $blockName = 'signature';
                    }

                    $blockFile = APP_ROOT . "/themes/{$theme}/{$blockName}.php";
                    if (!file_exists($blockFile)) {
                        $blockFile = APP_ROOT . "/themes/mesigo-professional/{$blockName}.php";
                    }

                    if (file_exists($blockFile)) {
                        $company = $documentData['company'] ?? [];
                        $header = $documentData['header'] ?? [];
                        $buyer = $documentData['buyer'] ?? [];
                        $bank = $documentData['bank'] ?? [];
                        $items = $documentData['items'] ?? [];
                        $fields = $sec['fields'] ?? [];
                        $logoWidth = $logoWidth; // expose logoWidth override inside blocks scope

                        include $blockFile;
                    }
                }
                ?>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
}
