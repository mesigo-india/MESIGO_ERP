<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

class CommercialInvoice extends ProformaInvoice
{
    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    public function convertToPackingList(int $id, int $userId): int
    {
        return $this->convertTo($id, 'packing_list', $userId, ['status' => ProformaInvoice::STATUS_DRAFT]);
    }

    protected function documentCode(): string
    {
        return 'commercial_invoice';
    }

    protected function seriesName(): string
    {
        return 'commercial_invoice';
    }
}