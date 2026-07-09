<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

class HSCode extends MasterDataModel
{
    protected string $table = 'hs_codes';
    protected string $codeField = 'hs_code';
    protected array $fillable = ['hs_code', 'description', 'category', 'duty_rate'];
    protected array $searchable = ['hs_code', 'description', 'category', 'status', 'created_at'];

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    public function generateHSCode(): string
    {
        return $this->generateCode('HS');
    }
}