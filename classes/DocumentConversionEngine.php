<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use Exception;

/**
 * Document Conversion Engine
 * Handles conversion between document types
 */
class DocumentConversionEngine
{
    private PDO $db;
    private DocumentHeader $documentHeader;
    private DocumentItem $documentItem;
    private NumberGenerator $numberGenerator;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->documentHeader = new DocumentHeader($db);
        $this->documentItem = new DocumentItem($db);
        $this->numberGenerator = new NumberGenerator($db);
    }

    /**
     * Convert document to another type
     */
    public function convert(int $sourceId, string $targetType, int $convertedBy, array $overrides = []): int
    {
        try {
            // Get source document
            $source = $this->documentHeader->findById($sourceId);
            if (!$source) {
                throw new Exception("Source document not found");
            }

            // Get target document type
            $docType = (new DocumentType($this->db))->findByCode($targetType);
            if (!$docType) {
                throw new Exception("Target document type not found");
            }

            // Generate new document number
            $newNumber = $this->numberGenerator->generate($targetType);

            // Create new document with source data
            $newData = [
                'document_type_id' => $docType['id'],
                'document_number' => $newNumber,
                'document_date' => date('Y-m-d'),
                'company_id' => $source['company_id'] ?? 1,
                'buyer_id' => $source['buyer_id'],
                'seller_id' => $source['seller_id'],
                'currency_id' => $source['currency_id'],
                'exchange_rate' => $source['exchange_rate'],
                'rate_locked' => $source['rate_locked'] ?? 0,
                'lut_active' => $source['lut_active'] ?? 0,
                'tax_basis' => $source['tax_basis'] ?? 'lut',
                'estimated_containers_json' => $source['estimated_containers_json'] ?? null,
                'shipment_type' => $source['shipment_type'],
                'incoterm_id' => $source['incoterm_id'],
                'loading_port_id' => $source['loading_port_id'],
                'destination_port_id' => $source['destination_port_id'],
                'payment_term_id' => $source['payment_term_id'],
                'validity_days' => $source['validity_days'],
                'expected_shipment' => $source['expected_shipment'],
                'remarks' => $source['remarks'],
                'internal_notes' => $source['internal_notes'],
                'created_by' => $convertedBy,
                'converted_from_id' => $sourceId,
            ];

            // Apply overrides
            foreach ($overrides as $key => $value) {
                $newData[$key] = $value;
            }

            $newId = $this->documentHeader->create($newData);

            // Copy items
            require_once APP_ROOT . '/classes/Product.php';
            $productModel = new Product($this->db);
            $sourceItems = $this->documentItem->getByDocument($sourceId);
            foreach ($sourceItems as $index => $item) {
                $item['document_header_id'] = $newId;
                $item['sort_order'] = $index;

                if ($targetType === 'packing_list') {
                    $prod = $productModel->findById((int) $item['product_id']);
                    if ($prod) {
                        $unitsPerPkg = (float) ($prod['units_per_package'] ?? 1.0);
                        if ($unitsPerPkg <= 0.0) {
                            $unitsPerPkg = 1.0;
                        }

                        $sourceQty = (float) $item['quantity'];

                        $unitStmt = $this->db->prepare("SELECT code FROM units WHERE id = :id LIMIT 1");
                        $unitStmt->execute(['id' => (int) $item['unit_id']]);
                        $unitCode = strtoupper((string) ($unitStmt->fetchColumn() ?: ''));
                        
                        $isMt = in_array($unitCode, ['MT', 'TONS', 'TON'], true);
                        if ($isMt) {
                            $sourceQty = $sourceQty * 1000.0;
                            $kgUnitStmt = $this->db->prepare("SELECT id FROM units WHERE UPPER(code) = 'KG' LIMIT 1");
                            $kgUnitStmt->execute();
                            $kgUnitId = $kgUnitStmt->fetchColumn();
                            if ($kgUnitId !== false) {
                                $item['unit_id'] = (int) $kgUnitId;
                            }
                        }

                        $packages = ceil($sourceQty / $unitsPerPkg);
                        
                        $netWtPkg = (float) ($prod['net_weight'] ?? 0.0);
                        $netWeight = $packages * $netWtPkg;
                        if ($netWeight <= 0.0) {
                            $netWeight = $sourceQty;
                        }

                        $emptyPkgWt = (float) ($prod['empty_package_weight'] ?? 0.0);
                        $grossWeight = $netWeight + ($packages * $emptyPkgWt);
                        
                        $cbmPkg = (float) ($prod['volume_per_package_cbm'] ?? 0.0);
                        $cbm = $packages * $cbmPkg;

                        $dims = '';
                        if (!empty($prod['package_length'])) {
                            $dims = $prod['package_length'] . 'x' . $prod['package_width'] . 'x' . $prod['package_height'] . ' cm';
                        }

                        $item['quantity'] = $packages; 
                        $item['net_weight'] = $netWeight;
                        $item['gross_weight'] = $grossWeight;
                        
                        $item['quality'] = json_encode([
                            'dimensions' => $dims,
                            'remarks' => '',
                            'units_per_package' => $unitsPerPkg,
                            'cbm' => $cbm,
                            'empty_package_weight' => $emptyPkgWt,
                            'total_qty' => $sourceQty,
                            'pallet_count' => 0.0,
                            'net_weight_per_package' => $netWtPkg,
                            'gross_weight_formula' => $prod['gross_weight_formula'] ?? ''
                        ]);
                    }
                }

                $this->documentItem->create($item);
            }

            // Copy charges (costs sheet)
            $chargesStmt = $this->db->prepare("SELECT * FROM document_charges WHERE document_header_id = :id");
            $chargesStmt->execute(['id' => $sourceId]);
            $charges = $chargesStmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($charges as $charge) {
                $insertCharge = $this->db->prepare("
                    INSERT INTO document_charges 
                    (document_header_id, cost_component_id, charge_name, charge_amount, currency_id, exchange_rate, converted_amount_base, payment_method, remarks, sort_order)
                    VALUES 
                    (:doc_id, :comp_id, :name, :amount, :curr_id, :rate, :base_amt, :method, :remarks, :sort)
                ");
                $insertCharge->execute([
                    'doc_id' => $newId,
                    'comp_id' => $charge['cost_component_id'],
                    'name' => $charge['charge_name'],
                    'amount' => $charge['charge_amount'],
                    'curr_id' => $charge['currency_id'],
                    'rate' => $charge['exchange_rate'],
                    'base_amt' => $charge['converted_amount_base'],
                    'method' => $charge['payment_method'],
                    'remarks' => $charge['remarks'],
                    'sort' => $charge['sort_order'],
                ]);
            }

            // Update source document with converted_to_id
            $this->db->prepare("
                UPDATE document_headers 
                SET converted_to_id = :converted_to_id, status = 5, updated_at = NOW()
                WHERE id = :id
            ")->execute([
                'id' => $sourceId,
                'converted_to_id' => $newId,
            ]);

            return $newId;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get conversion path
     */
    public static function getConversionPath(string $fromType, string $toType): array
    {
        $paths = [
            'inquiry' => ['quotation'],
            'quotation' => ['proforma_invoice'],
            'proforma_invoice' => ['commercial_invoice'],
            'commercial_invoice' => ['packing_list'],
            'packing_list' => ['shipping_bill'],
            'shipping_bill' => ['bill_of_lading', 'certificate_of_origin', 'phytosanitary', 'insurance', 'inspection'],
        ];

        $path = [];
        $current = $fromType;
        
        while ($current && $current !== $toType) {
            if (!isset($paths[$current])) {
                break;
            }
            $next = $paths[$current];
            $path = array_merge($path, $next);
            $current = $next[0] ?? null;
        }

        return $path;
    }

    /**
     * Check if conversion is allowed
     */
    public static function isConversionAllowed(string $fromType, string $toType): bool
    {
        $allowedConversions = [
            'inquiry' => ['quotation'],
            'quotation' => ['proforma_invoice', 'quotation'],
            'proforma_invoice' => ['commercial_invoice'],
            'commercial_invoice' => ['packing_list'],
            'packing_list' => ['shipping_bill'],
            'shipping_bill' => ['bill_of_lading', 'certificate_of_origin', 'phytosanitary', 'insurance', 'inspection'],
        ];

        if (!isset($allowedConversions[$fromType])) {
            return false;
        }

        return in_array($toType, $allowedConversions[$fromType]);
    }
}