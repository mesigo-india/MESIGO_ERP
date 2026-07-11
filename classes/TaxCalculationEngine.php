<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

/**
 * Tax Calculation Engine
 * Governs all sales tax, domestic CGST/SGST/IGST, and export LUT zero-rate validations
 */
class TaxCalculationEngine
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Compute taxes dynamically on item lines and header aggregates
     */
    public function calculateTax(int $documentId): array
    {
        // 1. Load document header
        $stmt = $this->db->prepare("SELECT * FROM document_headers WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $documentId]);
        $header = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$header) {
            return [];
        }

        $taxBasis = (string) ($header['tax_basis'] ?? 'lut'); // 'lut', 'igst_paid', 'domestic'
        $companyId = (int) ($header['company_id'] ?? 1);
        $buyerId = (int) ($header['buyer_id'] ?? 0);

        // 2. Fetch document items with product tax classifications
        $stmt = $this->db->prepare("
            SELECT di.*, p.gst_percent as prod_gst
            FROM document_items di
            JOIN products p ON di.product_id = p.id
            WHERE di.document_header_id = :id
        ");
        $stmt->execute(['id' => $documentId]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $totalTax = 0.0;
        $totalNetValue = 0.0;
        $taxSummary = [
            'basis' => $taxBasis,
            'cgst' => 0.0,
            'sgst' => 0.0,
            'igst' => 0.0,
            'total' => 0.0,
            'details' => []
        ];

        // 3. Determine if sale is Intrastate vs Interstate (applicable only to domestic tax basis)
        $isSameState = false;
        if ($taxBasis === 'domestic') {
            $isSameState = $this->isIntrastateSale($companyId, $buyerId);
        }

        // 4. Calculate tax line-by-line
        foreach ($items as $item) {
            $itemId = (int) $item['id'];
            $amount = (float)($item['quantity'] ?? 0) * (float)($item['rate'] ?? 0) - (float)($item['discount_amount'] ?? 0);
            
            // Resolve active tax rate slab
            $rate = 0.0;
            if ($taxBasis === 'igst_paid' || $taxBasis === 'domestic') {
                $rate = (float) ($item['prod_gst'] ?? 0.0);
            }

            $lineTax = 0.0;
            $lineCgst = 0.0;
            $lineSgst = 0.0;
            $lineIgst = 0.0;

            if ($rate > 0.0) {
                $lineTax = $amount * ($rate / 100.0);

                if ($taxBasis === 'domestic' && $isSameState) {
                    // CGST & SGST split equally (50% each)
                    $lineCgst = $lineTax / 2.0;
                    $lineSgst = $lineTax / 2.0;
                } else {
                    // Export IGST or Interstate Domestic IGST (100%)
                    $lineIgst = $lineTax;
                }
            }

            $totalTax += $lineTax;
            $totalNetValue += ($amount + $lineTax);

            $taxSummary['cgst'] += $lineCgst;
            $taxSummary['sgst'] += $lineSgst;
            $taxSummary['igst'] += $lineIgst;

            // Update item record with computed values
            $updateStmt = $this->db->prepare("
                UPDATE document_items 
                SET tax_slab_percent = :slab, 
                    tax_percent = :rate, 
                    tax_amount = :tax,
                    net_amount = :net
                WHERE id = :item_id
            ");
            $updateStmt->execute([
                'slab' => $rate,
                'rate' => $rate,
                'tax' => $lineTax,
                'net' => ($amount + $lineTax),
                'item_id' => $itemId
            ]);

            $taxSummary['details'][] = [
                'item_id' => $itemId,
                'amount' => $amount,
                'tax_rate' => $rate,
                'tax_amount' => $lineTax,
                'cgst' => $lineCgst,
                'sgst' => $lineSgst,
                'igst' => $lineIgst
            ];
        }

        $taxSummary['total'] = $totalTax;

        return $taxSummary;
    }

    /**
     * Compare Company state and Buyer state to detect Intrastate vs Interstate transactions
     */
    private function isIntrastateSale(int $companyId, int $buyerId): bool
    {
        if ($buyerId === 0) {
            return false;
        }

        // Fetch company state
        $stmt = $this->db->prepare("SELECT address FROM company WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $companyId]);
        $compAddrJson = (string) ($stmt->fetchColumn() ?: '');
        $compAddr = json_decode($compAddrJson, true) ?: [];
        $compState = strtoupper(trim((string) ($compAddr['state'] ?? '')));

        if ($compState === '') {
            return false;
        }

        // Fetch buyer primary state
        $stmt = $this->db->prepare("
            SELECT s.name 
            FROM buyer_addresses ba
            JOIN states s ON ba.state_id = s.id
            WHERE ba.buyer_id = :buyer_id AND ba.address_type = 'billing' 
            LIMIT 1
        ");
        $stmt->execute(['buyer_id' => $buyerId]);
        $buyerState = strtoupper(trim((string) ($stmt->fetchColumn() ?: '')));

        if ($buyerState === '') {
            // Try general state_id column in buyers table if exists
            $stmt = $this->db->prepare("
                SELECT s.name 
                FROM buyers b
                JOIN states s ON b.state_id = s.id
                WHERE b.id = :id 
                LIMIT 1
            ");
            $stmt->execute(['id' => $buyerId]);
            $buyerState = strtoupper(trim((string) ($stmt->fetchColumn() ?: '')));
        }

        return ($compState === $buyerState);
    }
}
