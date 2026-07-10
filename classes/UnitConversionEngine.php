<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use Throwable;

/**
 * Unit Conversion Engine
 * Decoupled, reusable service for all unit calculations in MESIGO ERP
 */
class UnitConversionEngine
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Convert a quantity from one unit to another
     * Supports direct conversions, indirect (via KG base), and product packaging fallbacks
     */
    public function convert(
        float $quantity,
        int $fromUnitId,
        int $toUnitId,
        ?int $productId = null,
        ?float $customCapacity = null
    ): float {
        if ($fromUnitId === $toUnitId || $quantity === 0.0) {
            return $quantity;
        }

        // 1. Check custom capacity override (e.g. user manually input 25 Kg for this line's Bag unit)
        if ($customCapacity !== null && $customCapacity > 0.0) {
            $fromCode = $this->getUnitCode($fromUnitId);
            $toCode = $this->getUnitCode($toUnitId);
            
            // If converting from packaging (bag/box) to base weight (KG)
            if ($this->isPackagingUnit($fromCode) && $this->isWeightUnit($toCode)) {
                return $quantity * $customCapacity;
            }
            // If converting from base weight (KG) to packaging (bag/box)
            if ($this->isWeightUnit($fromCode) && $this->isPackagingUnit($toCode)) {
                return $quantity / $customCapacity;
            }
        }

        // 2. Check for direct conversion in database
        $directFactor = $this->getDirectFactor($fromUnitId, $toUnitId, $productId);
        if ($directFactor !== null) {
            return $quantity * $directFactor;
        }

        // 3. Check for reverse direct conversion
        $reverseFactor = $this->getDirectFactor($toUnitId, $fromUnitId, $productId);
        if ($reverseFactor !== null && $reverseFactor > 0.0) {
            return $quantity / $reverseFactor;
        }

        // 4. Try indirect conversion via KG (base weight unit)
        $kgUnitId = $this->getUnitIdByCode('KG');
        if ($kgUnitId !== null && $fromUnitId !== $kgUnitId && $toUnitId !== $kgUnitId) {
            try {
                $qtyInKg = $this->convert($quantity, $fromUnitId, $kgUnitId, $productId);
                return $this->convert($qtyInKg, $kgUnitId, $toUnitId, $productId);
            } catch (Throwable) {
                // Fail silent to allow fallback
            }
        }

        // 5. Fallback: check product master packaging specifications directly
        if ($productId !== null) {
            $product = $this->getProductPackagingSpecs($productId);
            if ($product !== null) {
                $fromCode = $this->getUnitCode($fromUnitId);
                $toCode = $this->getUnitCode($toUnitId);

                // If converting from Bag/Box to Weight (KG)
                if ($this->isPackagingUnit($fromCode) && $this->isWeightUnit($toCode)) {
                    $packageWeight = (float) ($product['net_weight'] ?? 0.0);
                    if ($packageWeight > 0.0) {
                        return $quantity * $packageWeight;
                    }
                }
                // If converting from Weight (KG) to Bag/Box
                if ($this->isWeightUnit($fromCode) && $this->isPackagingUnit($toCode)) {
                    $packageWeight = (float) ($product['net_weight'] ?? 0.0);
                    if ($packageWeight > 0.0) {
                        return $quantity / $packageWeight;
                    }
                }
            }
        }

        // If no conversion path is found, return the original quantity
        return $quantity;
    }

    /**
     * Get equivalent values for a product quantity (e.g. quantity in Bags, KG, and MT)
     */
    public function getEquivalents(float $quantity, int $unitId, int $productId): array
    {
        $equivalents = [
            'original_quantity' => $quantity,
            'original_unit_code' => $this->getUnitCode($unitId),
            'kg_quantity' => $quantity,
            'mt_quantity' => $quantity / 1000.0,
            'bags_quantity' => null
        ];

        $kgUnitId = $this->getUnitIdByCode('KG');
        $mtUnitId = $this->getUnitIdByCode('MT');
        $bagUnitId = $this->getUnitIdByCode('BAG');

        if ($kgUnitId !== null) {
            $equivalents['kg_quantity'] = $this->convert($quantity, $unitId, $kgUnitId, $productId);
            $equivalents['mt_quantity'] = $equivalents['kg_quantity'] / 1000.0;
        }

        if ($bagUnitId !== null && $kgUnitId !== null) {
            $equivalents['bags_quantity'] = $this->convert($equivalents['kg_quantity'], $kgUnitId, $bagUnitId, $productId);
        }

        return $equivalents;
    }

    /**
     * Get direct factor from database
     */
    private function getDirectFactor(int $from, int $to, ?int $productId): ?float
    {
        // Try product-specific conversion first
        if ($productId !== null) {
            $stmt = $this->db->prepare("
                SELECT factor FROM unit_conversions 
                WHERE from_unit_id = :from AND to_unit_id = :to AND product_id = :product_id AND status = 1
                LIMIT 1
            ");
            $stmt->execute(['from' => $from, 'to' => $to, 'product_id' => $productId]);
            $val = $stmt->fetchColumn();
            if ($val !== false) {
                return (float) $val;
            }
        }

        // Try global conversion (product_id IS NULL)
        $stmt = $this->db->prepare("
            SELECT factor FROM unit_conversions 
            WHERE from_unit_id = :from AND to_unit_id = :to AND product_id IS NULL AND status = 1
            LIMIT 1
        ");
        $stmt->execute(['from' => $from, 'to' => $to]);
        $val = $stmt->fetchColumn();
        if ($val !== false) {
            return (float) $val;
        }

        return null;
    }

    /**
     * Helper to check if unit code represents a weight unit
     */
    private function isWeightUnit(string $code): bool
    {
        return in_array(strtoupper($code), ['KG', 'KGS', 'KILOGRAM', 'KILOGRAMS', 'MT', 'TON', 'TONS', 'METRIC TON', 'METRIC TONS', 'G', 'GR', 'GRAM', 'GRAMS'], true);
    }

    /**
     * Helper to check if unit code represents a packaging unit
     */
    private function isPackagingUnit(string $code): bool
    {
        return in_array(strtoupper($code), ['BAG', 'BAGS', 'BOX', 'BOXES', 'CTN', 'CARTON', 'CARTONS', 'PKT', 'PACKET', 'PACKETS', 'PLT', 'PALLET', 'PALLETS'], true);
    }

    /**
     * Get unit code by ID
     */
    private function getUnitCode(int $unitId): string
    {
        $stmt = $this->db->prepare("SELECT code FROM units WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $unitId]);
        return (string) ($stmt->fetchColumn() ?: '');
    }

    /**
     * Get unit ID by code
     */
    private function getUnitIdByCode(string $code): ?int
    {
        $stmt = $this->db->prepare("SELECT id FROM units WHERE UPPER(code) = UPPER(:code) LIMIT 1");
        $stmt->execute(['code' => $code]);
        $id = $stmt->fetchColumn();
        return $id !== false ? (int) $id : null;
    }

    /**
     * Fetch product packaging Net/Gross weight specifications
     */
    private function getProductPackagingSpecs(int $productId): ?array
    {
        $stmt = $this->db->prepare("SELECT net_weight, gross_weight, packing_size FROM products WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $productId]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }
}
