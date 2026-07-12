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
                    font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
                    font-size: 10pt;
                    line-height: 1.4;
                    color: #333;
                    margin: 0;
                    padding: 0;
                }
                .container {
                    width: 100%;
                    position: relative;
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

                /* Standard styling elements */
                .text-left { text-align: left; }
                .text-right { text-align: right; }
                .text-center { text-align: center; }
                .text-justify { text-align: justify; }
                
                .bold { font-weight: bold; }
                
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

                /* Product Table styling */
                table.product-grid {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 15px;
                    margin-bottom: 15px;
                }
                table.product-grid th, table.product-grid td {
                    border: 1px solid #ddd;
                    padding: 8px;
                    font-size: 9.5pt;
                }
                table.product-grid th {
                    background-color: #0b1c3d;
                    color: #ffffff;
                    font-weight: bold;
                }
                
                /* Letterhead hide elements if configured */
                <?php if (in_array($settings['letterhead_mode'], ['letterhead', 'logo_only'])): ?>
                .digital-header {
                    visibility: hidden;
                    height: <?php echo $settings['header_height']; ?>mm;
                }
                <?php endif; ?>
            </style>
        </head>
        <body>
            <?php if ($watermark && $watermark['watermark_type'] === 'text'): ?>
                <div class="watermark"><?php echo htmlspecialchars($watermark['text_value']); ?></div>
            <?php endif; ?>

            <div class="container">
                <!-- 1. Header Section -->
                <?php if (isset($sections['header'])): 
                    $hSec = $sections['header']; ?>
                    <div class="row" style="border-bottom: 2px solid #0b1c3d; padding-bottom: 10px;">
                        <?php foreach ($hSec['fields'] as $field): 
                            $val = '';
                            if ($field['field_key'] === 'company_name') $val = $documentData['company']['name'] ?? 'MESIGO EXPORTS';
                            elseif ($field['field_key'] === 'company_address') $val = $documentData['company']['address'] ?? '';
                            elseif ($field['field_key'] === 'document_number') $val = $documentData['header']['document_number'] ?? '';
                            elseif ($field['field_key'] === 'document_date') $val = $documentData['header']['document_date'] ?? '';
                            ?>
                            <div class="col-<?php echo $field['col_span']; ?> text-<?php echo $field['alignment']; ?>">
                                <?php if ($field['field_key'] === 'logo'): ?>
                                    <!-- Render Logo Placeholder / Real logo base64 if configured -->
                                    <div style="font-weight: bold; color: #0b1c3d; font-size: 16pt;">LOGO</div>
                                <?php else: ?>
                                    <div class="<?php echo $field['style_json'] ? (strpos($field['style_json'], 'bold') !== false ? 'bold' : '') : ''; ?>">
                                        <?php echo htmlspecialchars($val); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- 2. Buyer/Consignee Info Section -->
                <?php if (isset($sections['buyer'])): 
                    $bSec = $sections['buyer']; ?>
                    <div class="row" style="margin-top: 15px;">
                        <?php foreach ($bSec['fields'] as $field): 
                            $val = '';
                            if ($field['field_key'] === 'buyer_name') $val = $documentData['buyer']['company_name'] ?? '';
                            elseif ($field['field_key'] === 'buyer_address') $val = $documentData['buyer']['address'] ?? '';
                            elseif ($field['field_key'] === 'consignee') $val = $documentData['header']['consignee_name'] ?? '';
                            elseif ($field['field_key'] === 'incoterm') $val = $documentData['header']['incoterm_code'] ?? '';
                            elseif ($field['field_key'] === 'payment_terms') $val = $documentData['header']['payment_terms'] ?? '';
                            elseif ($field['field_key'] === 'destination_port') $val = $documentData['header']['destination_port_name'] ?? '';
                            ?>
                            <div class="col-<?php echo $field['col_span']; ?> text-<?php echo $field['alignment']; ?>" style="margin-bottom: 5px;">
                                <strong><?php echo htmlspecialchars($field['custom_label'] ?: $field['field_key']); ?>:</strong>
                                <div><?php echo nl2br(htmlspecialchars((string)$val)); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- 3. Product Grid Designer Section -->
                <?php if (isset($sections['grid'])): 
                    $gSec = $sections['grid']; ?>
                    <table class="product-grid">
                        <thead>
                            <tr>
                                <?php foreach ($gSec['fields'] as $field): ?>
                                    <th class="text-<?php echo $field['alignment']; ?>" style="width: <?php echo ($field['col_span'] / 12) * 100; ?>%;">
                                        <?php echo htmlspecialchars($field['custom_label'] ?: $field['field_key']); ?>
                                    </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $i = 1;
                            foreach ($documentData['items'] as $item): ?>
                                <tr>
                                    <?php foreach ($gSec['fields'] as $field): 
                                        $val = '';
                                        if ($field['field_key'] === 'serial_number') $val = (string)$i;
                                        elseif ($field['field_key'] === 'product_name') $val = $item['product_name'] ?? '';
                                        elseif ($field['field_key'] === 'hsn_code') $val = $item['hs_code'] ?? '';
                                        elseif ($field['field_key'] === 'quantity') $val = number_format((float)($item['quantity'] ?? 0), 2);
                                        elseif ($field['field_key'] === 'rate') $val = number_format((float)($item['rate'] ?? 0), 2);
                                        elseif ($field['field_key'] === 'net_amount') $val = number_format((float)($item['amount'] ?? 0), 2);
                                        ?>
                                        <td class="text-<?php echo $field['alignment']; ?>">
                                            <?php echo htmlspecialchars((string)$val); ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php 
                            $i++;
                            endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>

                <!-- 4. Summary & Totals -->
                <?php if (isset($sections['summary'])): 
                    $sSec = $sections['summary']; ?>
                    <div class="row">
                        <div class="col-6">
                            <!-- Left space / placeholder -->
                        </div>
                        <div class="col-6">
                            <?php foreach ($sSec['fields'] as $field): 
                                $val = '';
                                if ($field['field_key'] === 'subtotal') $val = number_format((float)($documentData['header']['subtotal'] ?? 0), 2);
                                elseif ($field['field_key'] === 'freight') $val = number_format((float)($documentData['header']['freight'] ?? 0), 2);
                                elseif ($field['field_key'] === 'grand_total') $val = number_format((float)($documentData['header']['grand_total'] ?? 0), 2);
                                ?>
                                <div class="row">
                                    <div class="col-6 text-right">
                                        <strong><?php echo htmlspecialchars($field['custom_label'] ?: $field['field_key']); ?>:</strong>
                                    </div>
                                    <div class="col-6 text-right <?php echo $field['field_key'] === 'grand_total' ? 'bold' : ''; ?>">
                                        <?php echo htmlspecialchars((string)$val); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- 5. Bank Details Section -->
                <?php if (isset($sections['bank'])): 
                    $bkSec = $sections['bank']; ?>
                    <div class="row" style="border-top: 1px solid #ddd; margin-top: 15px; padding-top: 10px;">
                        <div class="col-12"><strong style="color: #0b1c3d;">BANK DETAILS FOR REMITTANCE</strong></div>
                        <?php foreach ($bkSec['fields'] as $field): 
                            $val = '';
                            if ($field['field_key'] === 'bank_name') $val = $documentData['bank']['bank_name'] ?? '';
                            elseif ($field['field_key'] === 'branch') $val = $documentData['bank']['branch'] ?? '';
                            elseif ($field['field_key'] === 'iban') $val = $documentData['bank']['iban'] ?? '';
                            elseif ($field['field_key'] === 'swift_code') $val = $documentData['bank']['swift_code'] ?? '';
                            ?>
                            <div class="col-<?php echo $field['col_span']; ?> text-<?php echo $field['alignment']; ?>">
                                <strong><?php echo htmlspecialchars($field['custom_label'] ?: $field['field_key']); ?>:</strong>
                                <span><?php echo htmlspecialchars((string)$val); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- 6. Terms and Conditions -->
                <?php if (isset($sections['terms'])): 
                    $tSec = $sections['terms']; ?>
                    <div class="row" style="margin-top: 15px;">
                        <?php foreach ($tSec['fields'] as $field): 
                            $val = $documentData['header']['terms'] ?? '1. Standard export rules apply.';
                            ?>
                            <div class="col-<?php echo $field['col_span']; ?> text-<?php echo $field['alignment']; ?>">
                                <strong><?php echo htmlspecialchars($field['custom_label'] ?: $field['field_key']); ?>:</strong>
                                <div style="font-size: 8.5pt; color: #555;"><?php echo nl2br(htmlspecialchars((string)$val)); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- 7. Signatures and Seals -->
                <?php if ($signatures && count($signatures) > 0): ?>
                    <div class="row" style="margin-top: 30px;">
                        <div class="col-6"></div>
                        <div class="col-6 text-right" style="position: relative;">
                            <?php foreach ($signatures as $sig): ?>
                                <div style="display: inline-block; text-align: center; margin-left: 20px;">
                                    <?php if (!empty($sig['file_path'])): ?>
                                        <img src="<?php echo self::getBase64Image(APP_ROOT . '/' . $sig['file_path']); ?>" 
                                             style="width: <?php echo $sig['scale_percent']; ?>px; transform: rotate(<?php echo $sig['position_x']; ?>deg);" /><br/>
                                    <?php endif; ?>
                                    <strong><?php echo htmlspecialchars($sig['authorized_person']); ?></strong><br/>
                                    <span style="font-size: 8pt; color: #666;"><?php echo htmlspecialchars($sig['designation']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
}
