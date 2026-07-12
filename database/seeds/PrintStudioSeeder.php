<?php
declare(strict_types=1);

namespace App\Core\Seeds;

use PDO;

class PrintStudioSeeder extends Seeder
{
    public function run(): void
    {
        $this->log("==================================================");
        $this->log("Executing Print Studio & Default Templates Seeder");
        $this->log("==================================================");

        $docTypes = [
            'quotation' => 'Quotation',
            'proforma_invoice' => 'Proforma Invoice',
            'commercial_invoice' => 'Commercial Invoice',
            'packing_list' => 'Packing List',
            'shipping_bill' => 'Shipping Bill',
            'bill_of_lading_draft' => 'Bill of Lading Draft',
            'bill_of_lading_final' => 'Bill of Lading Final',
            'certificate_of_origin' => 'Certificate of Origin',
            'phytosanitary_certificate' => 'Phytosanitary Certificate',
            'insurance_certificate' => 'Insurance Certificate',
            'weight_certificate' => 'Weight Certificate',
            'inspection_certificate' => 'Inspection Certificate',
            'purchase_order' => 'Purchase Order',
            'payment_advice' => 'Payment Advice',
            'debit_note' => 'Debit Note',
            'credit_note' => 'Credit Note',
            'expense_voucher' => 'Expense Voucher',
            'internal_cost_sheet' => 'Internal Cost Sheet'
        ];

        foreach ($docTypes as $code => $name) {
            $this->log("Seeding print template for: {$name} ({$code})...");

            $templateData = [
                'name' => 'Default ' . $name . ' Template',
                'document_type' => $code,
                'is_active' => 1
            ];
            
            // Check if active template already exists
            $checkStmt = $this->db->prepare("SELECT id FROM print_templates WHERE document_type = :type LIMIT 1");
            $checkStmt->execute(['type' => $code]);
            $templateId = $checkStmt->fetchColumn();

            if ($templateId !== false) {
                $templateId = (int)$templateId;
                $this->log("  Template already exists with ID: {$templateId}. Updating...");
            } else {
                $templateId = $this->upsert('print_templates', $templateData, ['document_type']);
                $this->log("  Created template with ID: {$templateId}.");
            }

            // Seed print_settings
            $settingsData = [
                'template_id' => $templateId,
                'paper_size' => 'A4',
                'orientation' => 'portrait',
                'margin_top' => 15.00,
                'margin_bottom' => 15.00,
                'margin_left' => 15.00,
                'margin_right' => 15.00,
                'header_height' => 0.00,
                'footer_height' => 0.00,
                'show_page_numbers' => 1,
                'page_number_format' => 'Page {page} of {pages}',
                'letterhead_mode' => 'blank'
            ];
            $this->upsert('print_settings', $settingsData, ['template_id']);

            // Seed sections
            $sections = [
                'header' => ['sort' => 0, 'visible' => 1],
                'buyer' => ['sort' => 1, 'visible' => 1],
                'grid' => ['sort' => 2, 'visible' => 1],
                'summary' => ['sort' => 3, 'visible' => 1],
                'bank' => ['sort' => 4, 'visible' => 1],
                'terms' => ['sort' => 5, 'visible' => 1],
                'footer' => ['sort' => 6, 'visible' => 1],
                'signatures' => ['sort' => 7, 'visible' => 1]
            ];

            foreach ($sections as $sectionCode => $meta) {
                $sectionId = $this->upsert('print_template_sections', [
                    'template_id' => $templateId,
                    'section_code' => $sectionCode,
                    'sort_order' => $meta['sort'],
                    'is_visible' => $meta['visible'],
                    'custom_style_json' => json_encode([
                        'padding_top' => 5,
                        'padding_bottom' => 5,
                        'border_bottom' => 'none',
                        'margin_bottom' => 10
                    ])
                ], ['template_id', 'section_code']);

                // Seed default fields per section
                $fields = [];
                if ($sectionCode === 'header') {
                    $fields = [
                        'logo' => ['label' => 'Logo', 'span' => 4, 'align' => 'left', 'order' => 0],
                        'company_name' => ['label' => 'Company Name', 'span' => 8, 'align' => 'right', 'order' => 1],
                        'company_address' => ['label' => 'Address', 'span' => 8, 'align' => 'right', 'order' => 2],
                        'document_number' => ['label' => 'Document No.', 'span' => 6, 'align' => 'left', 'order' => 3],
                        'document_date' => ['label' => 'Date', 'span' => 6, 'align' => 'right', 'order' => 4]
                    ];
                } elseif ($sectionCode === 'buyer') {
                    $fields = [
                        'buyer_name' => ['label' => 'Buyer Name', 'span' => 6, 'align' => 'left', 'order' => 0],
                        'buyer_address' => ['label' => 'Buyer Address', 'span' => 6, 'align' => 'left', 'order' => 1],
                        'consignee' => ['label' => 'Consignee Details', 'span' => 6, 'align' => 'left', 'order' => 2],
                        'incoterm' => ['label' => 'Incoterms', 'span' => 4, 'align' => 'left', 'order' => 3],
                        'payment_terms' => ['label' => 'Payment Terms', 'span' => 4, 'align' => 'left', 'order' => 4],
                        'destination_port' => ['label' => 'Port of Delivery', 'span' => 4, 'align' => 'left', 'order' => 5]
                    ];
                } elseif ($sectionCode === 'grid') {
                    $fields = [
                        'serial_number' => ['label' => '#', 'span' => 1, 'align' => 'center', 'order' => 0],
                        'product_name' => ['label' => 'Product / Description', 'span' => 4, 'align' => 'left', 'order' => 1],
                        'hsn_code' => ['label' => 'HS Code', 'span' => 2, 'align' => 'left', 'order' => 2],
                        'quantity' => ['label' => 'Quantity', 'span' => 1, 'align' => 'right', 'order' => 3],
                        'rate' => ['label' => 'Rate', 'span' => 2, 'align' => 'right', 'order' => 4],
                        'net_amount' => ['label' => 'Amount', 'span' => 2, 'align' => 'right', 'order' => 5]
                    ];
                } elseif ($sectionCode === 'summary') {
                    $fields = [
                        'subtotal' => ['label' => 'Subtotal', 'span' => 6, 'align' => 'right', 'order' => 0],
                        'freight' => ['label' => 'Freight Charges', 'span' => 6, 'align' => 'right', 'order' => 1],
                        'grand_total' => ['label' => 'Grand Total', 'span' => 12, 'align' => 'right', 'order' => 2]
                    ];
                } elseif ($sectionCode === 'bank') {
                    $fields = [
                        'bank_name' => ['label' => 'Bank Name', 'span' => 6, 'align' => 'left', 'order' => 0],
                        'branch' => ['label' => 'Branch Name', 'span' => 6, 'align' => 'left', 'order' => 1],
                        'iban' => ['label' => 'IBAN / A/C No', 'span' => 6, 'align' => 'left', 'order' => 2],
                        'swift_code' => ['label' => 'SWIFT Code', 'span' => 6, 'align' => 'left', 'order' => 3]
                    ];
                } elseif ($sectionCode === 'terms') {
                    $fields = [
                        'terms_text' => ['label' => 'Terms & Conditions', 'span' => 12, 'align' => 'left', 'order' => 0]
                    ];
                }

                foreach ($fields as $key => $f) {
                    $this->upsert('print_fields', [
                        'section_id' => $sectionId,
                        'field_key' => $key,
                        'custom_label' => $f['label'],
                        'is_visible' => 1,
                        'col_span' => $f['span'],
                        'sort_order' => $f['order'],
                        'alignment' => $f['align'],
                        'style_json' => json_encode([
                            'font_weight' => $key === 'grand_total' || $key === 'company_name' ? 'bold' : 'normal',
                            'font_size' => '10pt',
                            'text_transform' => 'none'
                        ])
                    ], ['section_id', 'field_key']);
                }
            }
        }

        $this->log("==================================================");
        $this->log("Print Studio Seeding Complete!");
        $this->log("==================================================");
    }
}
