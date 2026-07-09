<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

class ProformaInvoice extends Quotation
{
    public const STATUS_DRAFT = 0;
    public const STATUS_APPROVED = 2;
    public const STATUS_SENT = 4;
    public const STATUS_CONVERTED = 5;
    public const STATUS_CANCELLED = 6;

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_SENT => 'Sent',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_CONVERTED => 'Converted',
        ];
    }

    public static function statusBadgeClass(int $status): string
    {
        return match ($status) {
            self::STATUS_DRAFT => 'bg-secondary',
            self::STATUS_APPROVED => 'bg-success',
            self::STATUS_SENT => 'bg-info',
            self::STATUS_CANCELLED => 'bg-danger',
            self::STATUS_CONVERTED => 'bg-primary',
            default => 'bg-secondary',
        };
    }

    public function convertToCommercialInvoice(int $id, int $userId): int
    {
        return $this->convertTo($id, 'commercial_invoice', $userId, ['status' => self::STATUS_DRAFT]);
    }

    protected function documentCode(): string
    {
        return 'proforma_invoice';
    }

    protected function seriesName(): string
    {
        return 'proforma_invoice';
    }
}