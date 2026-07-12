<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

class ShippingBill extends PackingList
{
    public const STATUS_DRAFT = 0;
    public const STATUS_FILED = 1;
    public const STATUS_SUBMITTED = 2;
    public const STATUS_LEO_RECEIVED = 3;
    public const STATUS_COMPLETED = 5;
    public const STATUS_CANCELLED = 6;

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    public function convertToBillOfLading(int $id, int $userId): int
    {
        return $this->convertTo($id, 'bill_of_lading', $userId, ['status' => BillOfLading::STATUS_DRAFT]);
    }

    public function convertToCertificateOfOrigin(int $id, int $userId): int
    {
        return $this->convertTo($id, 'certificate_of_origin', $userId, ['status' => CertificateOfOrigin::STATUS_DRAFT]);
    }

    public function convertToPhytosanitary(int $id, int $userId): int
    {
        return $this->convertTo($id, 'phytosanitary', $userId, ['status' => Phytosanitary::STATUS_DRAFT]);
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_FILED => 'Filed',
            self::STATUS_SUBMITTED => 'Submitted',
            self::STATUS_LEO_RECEIVED => 'LEO Received',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    protected function headerPayload(array $data, array $type, ?int $validityDays, array $meta, bool $creating): array
    {
        $data['packing_meta'] = array_merge($data['packing_meta'] ?? [], $data['shipping_meta'] ?? []);
        return parent::headerPayload($data, $type, $validityDays, $meta, $creating);
    }

    protected function documentCode(): string
    {
        return 'shipping_bill';
    }

    protected function seriesName(): string
    {
        return 'shipping_bill';
    }
}