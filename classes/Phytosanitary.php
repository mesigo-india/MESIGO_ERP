<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

class Phytosanitary extends CertificateOfOrigin
{
    public const STATUS_DRAFT = 0;
    public const STATUS_SUBMITTED = 1;
    public const STATUS_ISSUED = 2;
    public const STATUS_CANCELLED = 6;

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SUBMITTED => 'Submitted',
            self::STATUS_ISSUED => 'Issued',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    protected function headerPayload(array $data, array $type, ?int $validityDays, array $meta, bool $creating): array
    {
        $data['co_meta'] = array_merge($data['co_meta'] ?? [], $data['phyto_meta'] ?? []);
        return parent::headerPayload($data, $type, $validityDays, $meta, $creating);
    }

    protected function documentCode(): string
    {
        return 'phytosanitary';
    }

    protected function seriesName(): string
    {
        return 'phytosanitary';
    }
}
