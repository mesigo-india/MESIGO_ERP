<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

class PackingList extends CommercialInvoice
{
    public const STATUS_DRAFT = 0;
    public const STATUS_APPROVED = 2;
    public const STATUS_ISSUED = 4;
    public const STATUS_REVISED = 5;
    public const STATUS_CANCELLED = 6;

    private PDO $packingDb;

    public function __construct(PDO $db)
    {
        $this->packingDb = $db;
        parent::__construct($db);
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_ISSUED => 'Issued',
            self::STATUS_REVISED => 'Revised',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    protected function headerPayload(array $data, array $type, ?int $validityDays, array $meta, bool $creating): array
    {
        $meta = array_merge($meta, $data['packing_meta'] ?? []);
        return parent::headerPayload($data, $type, $validityDays, $meta, $creating);
    }

    protected function syncItems(int $id, array $items): void
    {
        $this->packingDb->prepare("DELETE FROM document_items WHERE document_header_id = :id")->execute(['id' => $id]);
        $stmt = $this->packingDb->prepare("
            INSERT INTO document_items (document_header_id, product_id, hsn_code, quality, packing_type_id, unit_id, quantity, rate, discount_percent, discount_amount, tax_percent, tax_amount, net_amount, gross_weight, net_weight, sort_order, created_at)
            VALUES (:document_header_id, :product_id, :hsn_code, :quality, :packing_type_id, :unit_id, :quantity, 0, 0, 0, 0, 0, 0, :gross_weight, :net_weight, :sort_order, NOW())
        ");

        foreach ($items as $index => $item) {
            if ((int) ($item['product_id'] ?? 0) <= 0) {
                continue;
            }

            $stmt->execute([
                'document_header_id' => $id,
                'product_id' => (int) $item['product_id'],
                'hsn_code' => $item['hsn_code'] ?? null,
                'quality' => json_encode([
                    'dimensions' => $item['dimensions'] ?? '',
                    'remarks' => $item['remarks'] ?? '',
                ]),
                'packing_type_id' => (int) ($item['packing_type_id'] ?? 0) ?: null,
                'unit_id' => (int) ($item['unit_id'] ?? 0) ?: null,
                'quantity' => (float) ($item['no_of_bags'] ?? 0),
                'gross_weight' => (float) ($item['gross_weight'] ?? 0),
                'net_weight' => (float) ($item['net_weight'] ?? 0),
                'sort_order' => $index,
            ]);
        }
    }

    protected function documentCode(): string
    {
        return 'packing_list';
    }

    protected function seriesName(): string
    {
        return 'packing_list';
    }
}