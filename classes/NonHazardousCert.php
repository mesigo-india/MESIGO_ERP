<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

class NonHazardousCert extends CertificateOfOrigin
{
    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    protected function documentCode(): string
    {
        return 'non_hazardous_cert';
    }

    protected function seriesName(): string
    {
        return 'non_hazardous_cert';
    }
}
