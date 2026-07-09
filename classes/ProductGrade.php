<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

class ProductGrade extends MasterDataModel
{
    protected string $table = 'product_grades';
    protected string $codeField = 'code';
    protected array $fillable = ['name', 'code', 'description'];
    protected array $searchable = ['name', 'code', 'description', 'status', 'created_at'];

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    public function generateGradeCode(): string
    {
        return $this->generateCode('PG');
    }
}
