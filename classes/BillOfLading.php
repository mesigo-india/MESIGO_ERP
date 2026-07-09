<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

class BillOfLading extends ShippingBill
{
    public const STATUS_DRAFT = 0;
    public const STATUS_ISSUED = 1;
    public const STATUS_ORIGINAL_RECEIVED = 2;
    public const STATUS_RELEASED = 3;

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    public function convertToCertificateOfOrigin(int $id, int $userId): int
    {
        return $this->convertTo($id, 'certificate_of_origin', $userId, ['status' => self::STATUS_DRAFT]);
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_ISSUED => 'Issued',
            self::STATUS_ORIGINAL_RECEIVED => 'Original Received',
            self::STATUS_RELEASED => 'Released',
        ];
    }

    protected function headerPayload(array $data, array $type, ?int $validityDays, array $meta, bool $creating): array
    {
        $data['shipping_meta'] = array_merge($data['shipping_meta'] ?? [], $data['bl_meta'] ?? []);
        return parent::headerPayload($data, $type, $validityDays, $meta, $creating);
    }

    protected function documentCode(): string
    {
        return 'bill_of_lading';
    }

    protected function seriesName(): string
    {
        return 'bill_of_lading';
    }
}