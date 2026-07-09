<?php
declare(strict_types=1);
namespace App\Core;
use PDO;

class Branch extends MasterDataModel
{
    protected string $table = 'branches';
    protected string $codeField = 'branch_code';
    protected array $fillable = [
        'company_id',
        'branch_code',
        'branch_name',
        'address',
        'city',
        'state',
        'country',
        'zip',
        'email',
        'phone',
        'gst_number',
        'contact_person',
        'status',
        'remarks',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
    protected array $searchable = ['branch_code', 'branch_name', 'city', 'state', 'country'];

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    public function generateBranchCode(): string
    {
        return $this->generateCode('BR');
    }
}
