<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

/**
 * Buyer model — Professional Export CRM
 */
class Buyer
{
    private PDO $db;
    private const TABLE = 'buyers';

    /** Whitelist of columns that can be used for ORDER BY */
    private const SORT_WHITELIST = [
        'buyer_code', 'company_name', 'country', 'contact_person',
        'email', 'mobile', 'buyer_type', 'priority', 'lead_status',
        'status', 'assigned_to', 'last_contact', 'next_followup', 'created_at',
    ];

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // =========================================================================
    // READ
    // =========================================================================

    public function getAll(
        string $search      = '',
        string $status      = '',
        string $country     = '',
        string $type        = '',
        string $priority    = '',
        string $lead_source = '',
        string $lead_status = '',
        string $sort        = 'created_at',
        string $dir         = 'DESC',
        int    $limit       = 20,
        int    $offset      = 0
    ): array {
        [$where, $params] = $this->buildWhere(
            $search, $status, $country, $type, $priority, $lead_source, $lead_status
        );

        $sort = in_array($sort, self::SORT_WHITELIST, true) ? $sort : 'created_at';
        $dir  = strtoupper($dir) === 'ASC' ? 'ASC' : 'DESC';

        $sql  = 'SELECT b.*, c.name as country_name FROM `' . self::TABLE . '` b'
              . ' LEFT JOIN countries c ON b.country_id = c.id'
              . ' WHERE ' . implode(' AND ', $where)
              . " ORDER BY b.`{$sort}` {$dir}"
              . ' LIMIT :limit OFFSET :offset';

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue(':' . $key, $val);
        }
        $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function count(
        string $search      = '',
        string $status      = '',
        string $country     = '',
        string $type        = '',
        string $priority    = '',
        string $lead_source = '',
        string $lead_status = ''
    ): int {
        [$where, $params] = $this->buildWhere(
            $search, $status, $country, $type, $priority, $lead_source, $lead_status
        );

        $sql  = 'SELECT COUNT(*) FROM `' . self::TABLE . '` WHERE ' . implode(' AND ', $where);
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue(':' . $key, $val);
        }
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    /**
     * Dashboard stats cards
     */
    public function getStats(): array
    {
        $sql  = "SELECT
                    COUNT(*)                                          AS total,
                    SUM(status = 1)                                   AS active,
                    SUM(status != 1)                                  AS inactive,
                    SUM(priority = 'High')                            AS `high_priority`,
                    SUM(DATE(next_followup) = CURDATE()
                        AND next_followup IS NOT NULL
                        AND deleted_at IS NULL)                       AS today_followups,
                    SUM(next_followup < CURDATE()
                        AND next_followup IS NOT NULL
                        AND deleted_at IS NULL)                       AS overdue_followups
                 FROM `" . self::TABLE . "` WHERE deleted_at IS NULL";

        $row = $this->db->query($sql)->fetch(PDO::FETCH_ASSOC);
        return [
            'total'             => (int) ($row['total']             ?? 0),
            'active'            => (int) ($row['active']            ?? 0),
            'inactive'          => (int) ($row['inactive']          ?? 0),
            'high_priority'     => (int) ($row['high_priority']     ?? 0),
            'today_followups'   => (int) ($row['today_followups']   ?? 0),
            'overdue_followups' => (int) ($row['overdue_followups'] ?? 0),
        ];
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM `' . self::TABLE . '` WHERE id = :id AND deleted_at IS NULL'
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Check if buyer_code already exists (for duplicate validation)
     */
    public function existsByCode(string $code, int $excludeId = 0): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM `' . self::TABLE . '`'
            . ' WHERE buyer_code = :code AND id != :id AND deleted_at IS NULL'
        );
        $stmt->execute(['code' => $code, 'id' => $excludeId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Check if email already exists (for duplicate warning)
     */
    public function existsByEmail(string $email, int $excludeId = 0): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM `' . self::TABLE . '`'
            . ' WHERE email = :email AND id != :id AND deleted_at IS NULL'
        );
        $stmt->execute(['email' => $email, 'id' => $excludeId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Get the next auto-generated buyer code
     */
    public function getNextBuyerCode(): string
    {
        $stmt = $this->db->query("SELECT MAX(CAST(SUBSTRING(buyer_code, 11) AS UNSIGNED)) FROM `" . self::TABLE . "` WHERE buyer_code LIKE 'MIP_Buyer_%'");
        $maxNum = (int)$stmt->fetchColumn();
        return 'MIP_Buyer_' . str_pad((string)($maxNum + 1), 3, '0', STR_PAD_LEFT);
    }

    // =========================================================================
    // WRITE
    // =========================================================================

    public function create(array $d): int
    {
        $cols = $this->columnList();
        $sets = implode(', ', array_map(fn($c) => '`' . $c . '`', $cols));
        $plac = ':' . implode(', :', $cols);

        $sql  = "INSERT INTO `" . self::TABLE . "` ({$sets}, `created_at`) VALUES ({$plac}, NOW())";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($this->insertData($d));
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $d): bool
    {
        $cols = $this->columnList();
        $sets = implode(', ', array_map(fn($c) => "`{$c}` = :{$c}", $cols));

        $sql  = "UPDATE `" . self::TABLE . "` SET {$sets}, `updated_at` = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $data = $this->insertData($d);
        $data['id'] = $id;
        return $stmt->execute($data);
    }

    public function softDelete(int $id): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE `' . self::TABLE . '`'
            . ' SET deleted_at = NOW(), status = 0'
            . ' WHERE id = :id AND deleted_at IS NULL'
        );
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Create a copy of an existing buyer with a modified buyer_code
     */
    public function duplicate(int $id): int
    {
        $src = $this->findById($id);
        if (!$src) {
            return 0;
        }
        $src['buyer_code'] = 'COPY-' . $src['buyer_code'];
        unset($src['id'], $src['created_at'], $src['updated_at'],
              $src['deleted_at'], $src['created_by'], $src['updated_by'], $src['deleted_by']);
        return $this->create($src);
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    private function buildWhere(
        string $search,
        string $status,
        string $country,
        string $type,
        string $priority,
        string $lead_source,
        string $lead_status
    ): array {
        $where  = ['deleted_at IS NULL'];
        $params = [];

        if ($search !== '') {
            $where[]          = '(buyer_code LIKE :search OR company_name LIKE :search'
                              . ' OR email LIKE :search OR mobile LIKE :search'
                              . ' OR contact_person LIKE :search OR country LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }
        if ($status !== '') {
            $where[]          = 'status = :status';
            $params['status'] = $status;
        }
        if ($country !== '') {
            $where[]           = 'country LIKE :country';
            $params['country'] = '%' . $country . '%';
        }
        if ($type !== '') {
            $where[]        = 'buyer_type = :btype';
            $params['btype'] = $type;
        }
        if ($priority !== '') {
            $where[]            = 'priority = :priority';
            $params['priority'] = $priority;
        }
        if ($lead_source !== '') {
            $where[]               = 'lead_source = :lead_source';
            $params['lead_source'] = $lead_source;
        }
        if ($lead_status !== '') {
            $where[]               = 'lead_status = :lead_status';
            $params['lead_status'] = $lead_status;
        }

        return [$where, $params];
    }

    /**
     * Ordered list of columns for INSERT / UPDATE — must match insertData() keys exactly.
     * Never includes: id, created_at, updated_at, deleted_at, created_by, updated_by, deleted_by
     */
    private function columnList(): array
    {
        return [
            'buyer_code', 'company_name', 'buyer_type', 'priority',
            'lead_source', 'lead_status', 'customer_since',
            'contact_person', 'designation', 'email', 'mobile', 'phone',
            'website', 'whatsapp',
            'billing_address', 'shipping_address',
            'country_id', 'state_id', 'city_id', 'zip',
            'gst_number', 'iec_number', 'registration_number', 'tax_number',
            'bank_name', 'account_name', 'account_number', 'swift_ifsc',
            'payment_terms', 'credit_days', 'preferred_currency', 'preferred_incoterm',
            'shipping_mode', 'preferred_port', 'preferred_destination_port',
            'preferred_container', 'preferred_packing',
            'preferred_products', 'import_countries',
            'shipping_marks',
            'assigned_to', 'last_contact', 'next_followup', 'notes',
            'status',
        ];
    }

    /**
     * Map raw input array → clean param array for PDO.
     * Empty strings are converted to NULL for nullable columns.
     */
    private function insertData(array $d): array
    {
        $n = static function (string $key, array $d, mixed $default = null): mixed {
            $v = $d[$key] ?? $default;
            return ($v === '' || $v === null) ? null : $v;
        };

        return [
            'buyer_code'                => $d['buyer_code']                ?? null,
            'company_name'              => $d['company_name']              ?? null,
            'buyer_type'                => $n('buyer_type',                $d),
            'priority'                  => $n('priority',                  $d),
            'lead_source'               => $n('lead_source',               $d),
            'lead_status'               => $n('lead_status',               $d, 'New Lead'),
            'customer_since'            => $n('customer_since',            $d),
            'contact_person'            => $n('contact_person',            $d),
            'designation'               => $n('designation',               $d),
            'email'                     => $n('email',                     $d),
            'mobile'                    => $n('mobile',                    $d),
            'phone'                     => $n('phone',                     $d),
            'website'                   => $n('website',                   $d),
            'whatsapp'                  => $n('whatsapp',                  $d),
            'billing_address'           => $n('billing_address',           $d),
            'shipping_address'          => $n('shipping_address',          $d),
            'country_id'                => $n('country_id',                $d),
            'state_id'                  => $n('state_id',                  $d),
            'city_id'                   => $n('city_id',                   $d),
            'zip'                       => $n('zip',                       $d),
            'gst_number'                => $n('gst_number',                $d),
            'iec_number'                => $n('iec_number',                $d),
            'registration_number'       => $n('registration_number',       $d),
            'tax_number'                => $n('tax_number',                $d),
            'bank_name'                 => $n('bank_name',                 $d),
            'account_name'              => $n('account_name',              $d),
            'account_number'            => $n('account_number',            $d),
            'swift_ifsc'                => $n('swift_ifsc',                $d),
            'payment_terms'             => $n('payment_terms',             $d),
            'credit_days'               => $n('credit_days',               $d),
            'preferred_currency'        => $n('preferred_currency',        $d),
            'preferred_incoterm'        => $n('preferred_incoterm',        $d),
            'shipping_mode'             => $n('shipping_mode',             $d),
            'preferred_port'            => $n('preferred_port',            $d),
            'preferred_destination_port'=> $n('preferred_destination_port',$d),
            'preferred_container'       => $n('preferred_container',       $d),
            'preferred_packing'         => $n('preferred_packing',         $d),
            'preferred_products'        => $n('preferred_products',        $d),
            'import_countries'          => $n('import_countries',          $d),
            'shipping_marks'            => $n('shipping_marks',            $d),
            'assigned_to'               => $n('assigned_to',               $d),
            'last_contact'              => $n('last_contact',              $d),
            'next_followup'             => $n('next_followup',             $d),
            'notes'                     => $n('notes',                     $d),
            'status'                    => isset($d['status']) && $d['status'] !== '' ? (int)$d['status'] : 1,
        ];
    }
}
