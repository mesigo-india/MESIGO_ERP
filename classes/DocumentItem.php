<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use Exception;

/**
 * Document Item Entity
 */
class DocumentItem
{
    private PDO $db;
    private const TABLE = 'document_items';

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Get all items for a document
     */
    public function getByDocument(int $documentHeaderId): array
    {
        $stmt = $this->db->prepare("
            SELECT di.*, p.name as product_name, p.product_code,
                   pv.name as variety_name, u.code as unit_code,
                   pt.name as packing_type_name, c.name as origin_country
            FROM " . self::TABLE . " di
            LEFT JOIN products p ON di.product_id = p.id
            LEFT JOIN product_varieties pv ON di.variety_id = pv.id
            LEFT JOIN units u ON di.unit_id = u.id
            LEFT JOIN packing_types pt ON di.packing_type_id = pt.id
            LEFT JOIN countries c ON di.origin_country_id = c.id
            WHERE di.document_header_id = :document_header_id
            ORDER BY di.sort_order ASC, di.id ASC
        ");
        $stmt->execute(['document_header_id' => $documentHeaderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create document item
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO " . self::TABLE . " 
            (document_header_id, product_id, variety_id, hsn_code, origin_country_id,
             quality, packing_type_id, unit_id, quantity, rate,
             discount_percent, discount_amount, tax_percent, tax_amount,
             net_amount, gross_weight, net_weight, sort_order, created_at) 
            VALUES 
            (:document_header_id, :product_id, :variety_id, :hsn_code, :origin_country_id,
             :quality, :packing_type_id, :unit_id, :quantity, :rate,
             :discount_percent, :discount_amount, :tax_percent, :tax_amount,
             :net_amount, :gross_weight, :net_weight, :sort_order, NOW())
        ");
        $stmt->execute([
            'document_header_id' => $data['document_header_id'],
            'product_id' => $data['product_id'],
            'variety_id' => $data['variety_id'] ?? null,
            'hsn_code' => $data['hsn_code'] ?? null,
            'origin_country_id' => $data['origin_country_id'] ?? null,
            'quality' => $data['quality'] ?? null,
            'packing_type_id' => $data['packing_type_id'] ?? null,
            'unit_id' => $data['unit_id'] ?? null,
            'quantity' => $data['quantity'] ?? 0,
            'rate' => $data['rate'] ?? 0,
            'discount_percent' => $data['discount_percent'] ?? 0,
            'discount_amount' => $data['discount_amount'] ?? 0,
            'tax_percent' => $data['tax_percent'] ?? 0,
            'tax_amount' => $data['tax_amount'] ?? 0,
            'net_amount' => $data['net_amount'] ?? 0,
            'gross_weight' => $data['gross_weight'] ?? null,
            'net_weight' => $data['net_weight'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Create multiple items for a document
     */
    public function createBatch(int $documentHeaderId, array $items): bool
    {
        $this->db->beginTransaction();
        try {
            foreach ($items as $index => $item) {
                $item['document_header_id'] = $documentHeaderId;
                $item['sort_order'] = $index;
                $this->create($item);
            }
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Delete all items for a document
     */
    public function deleteByDocument(int $documentHeaderId): bool
    {
        $stmt = $this->db->prepare("
            DELETE FROM " . self::TABLE . " 
            WHERE document_header_id = :document_header_id
        ");
        return $stmt->execute(['document_header_id' => $documentHeaderId]);
    }

    /**
     * Calculate totals for a document
     */
    public function calculateTotals(int $documentHeaderId): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                SUM(quantity) as total_quantity,
                SUM(net_amount) as total_net_amount,
                SUM(tax_amount) as total_tax_amount,
                SUM(discount_amount) as total_discount,
                SUM(gross_weight) as total_gross_weight,
                SUM(net_weight) as total_net_weight
            FROM " . self::TABLE . " 
            WHERE document_header_id = :document_header_id
        ");
        $stmt->execute(['document_header_id' => $documentHeaderId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}