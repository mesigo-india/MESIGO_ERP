<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

/**
 * Incoterm Costing Engine
 * Reusable service mapping logistics cost responsibilities between Buyer and Seller
 */
class IncotermEngine
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Scans document charges and determines cost distribution (Seller Paid vs Buyer Paid)
     */
    public function evaluateCostDistribution(int $documentId, int $incotermId): array
    {
        // 1. Fetch incoterm code
        $stmt = $this->db->prepare("SELECT UPPER(code) FROM incoterms WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $incotermId]);
        $incotermCode = (string) ($stmt->fetchColumn() ?: 'FOB');

        // 2. Fetch document charges
        $stmt = $this->db->prepare("
            SELECT dc.*, cc.category
            FROM document_charges dc
            LEFT JOIN cost_components cc ON dc.cost_component_id = cc.id
            WHERE dc.document_header_id = :id
        ");
        $stmt->execute(['id' => $documentId]);
        $charges = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $distribution = [
            'seller_paid' => [],
            'buyer_paid' => [],
            'totals' => [
                'seller_paid_base' => 0.0,
                'buyer_paid_base' => 0.0,
                'total_base' => 0.0
            ]
        ];

        foreach ($charges as $charge) {
            $category = strtoupper((string) ($charge['category'] ?? ''));
            $name = strtoupper((string) ($charge['charge_name'] ?? ''));
            $baseAmount = (float) ($charge['converted_amount_base'] ?? 0.0);

            $isSeller = $this->isSellerPaid($category, $name, $incotermCode);

            $chargeInfo = [
                'id' => $charge['id'],
                'name' => $charge['charge_name'],
                'amount' => (float) $charge['charge_amount'],
                'converted_amount_base' => $baseAmount,
                'currency_id' => (int) $charge['currency_id'],
                'exchange_rate' => (float) $charge['exchange_rate']
            ];

            if ($isSeller) {
                $distribution['seller_paid'][] = $chargeInfo;
                $distribution['totals']['seller_paid_base'] += $baseAmount;
            } else {
                $distribution['buyer_paid'][] = $chargeInfo;
                $distribution['totals']['buyer_paid_base'] += $baseAmount;
            }

            $distribution['totals']['total_base'] += $baseAmount;
        }

        return $distribution;
    }

    /**
     * Check if a charge category/name is seller-paid under the given Incoterm code
     */
    public function isSellerPaid(string $category, string $name, string $incotermCode): bool
    {
        $incoterm = strtoupper(trim($incotermCode));
        $cat = strtoupper(trim($category));
        $chargeName = strtoupper(trim($name));

        // EXW (Ex Works): Seller only packages goods. All local transport, customs, and freight are Buyer Paid.
        if ($incoterm === 'EXW') {
            return ($cat === 'PROCUREMENT' || $cat === 'PACKING');
        }

        // FOB (Free On Board): Seller pays loading/local charges up to port, Buyer pays ocean freight/insurance.
        if ($incoterm === 'FOB') {
            if ($cat === 'LOGISTICS_INTL' || strpos($chargeName, 'FREIGHT') !== false || strpos($chargeName, 'OCEAN') !== false || strpos($chargeName, 'MARINE') !== false || strpos($chargeName, 'INSURANCE') !== false) {
                return false;
            }
            return true;
        }

        // CFR (Cost and Freight): Seller pays ocean freight. Buyer pays marine insurance.
        if ($incoterm === 'CFR') {
            if (strpos($chargeName, 'INSURANCE') !== false || strpos($chargeName, 'MARINE') !== false) {
                return false;
            }
            return true;
        }

        // CIF (Cost, Insurance and Freight): Seller pays ocean freight and marine insurance.
        if ($incoterm === 'CIF') {
            return true;
        }

        // DDP (Delivered Duty Paid): Seller pays all costs including import customs and duties.
        if ($incoterm === 'DDP') {
            return true;
        }

        // Default: Seller paid
        return true;
    }
}
