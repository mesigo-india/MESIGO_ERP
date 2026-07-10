<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

/**
 * Supplier model — Professional Export CRM
 */
class Supplier
{
    private PDO $db;
    private const TABLE = 'suppliers';

    /** Whitelist of columns that can be used for ORDER BY */
    private const SORT_WHITELIST = [
        'supplier_code', 'company_name', 'country_id', 'contact_person',
        'email', 'phone', 'supplier_type', 'priority', 'rating',
        'status', 'assigned_executive', 'last_contact_date', 'next_followup_date', 'created_at',
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
        string $sort        = 'created_at',
        string $dir         = 'DESC',
        int    $limit       = 20,
        int    $offset      = 0
    ): array {
        [$where, $params] = $this->buildWhere(
            $search, $status, $country, $type, $priority
        );

        $sort = in_array($sort, self::SORT_WHITELIST, true) ? $sort : 'created_at';
        $dir  = strtoupper($dir) === 'ASC' ? 'ASC' : 'DESC';

        $sql  = 'SELECT s.*, c.name as country_name FROM `' . self::TABLE . '` s'
              . ' LEFT JOIN countries c ON s.country_id = c.id'
              . ' WHERE ' . implode(' AND ', $where)
              . " ORDER BY s.`{$sort}` {$dir}"
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
        string $priority    = ''
    ): int {
        [$where, $params] = $this->buildWhere(
            $search, $status, $country, $type, $priority
        );

        $sql  = 'SELECT COUNT(*) FROM `' . self::TABLE . '` s WHERE ' . implode(' AND ', $where);
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
                    SUM(is_approved = 1)                              AS approved,
                    SUM(is_blocked = 1)                               AS blocked,
                    SUM(is_preferred = 1)                             AS preferred,
                    SUM(supplier_type = 'international')              AS international,
                    SUM(supplier_type = 'domestic')                   AS domestic
                 FROM `" . self::TABLE . "` WHERE deleted_at IS NULL";

        $row = $this->db->query($sql)->fetch(PDO::FETCH_ASSOC);
        return [
            'total'         => (int) ($row['total']         ?? 0),
            'active'        => (int) ($row['active']        ?? 0),
            'inactive'      => (int) ($row['inactive']      ?? 0),
            'approved'      => (int) ($row['approved']      ?? 0),
            'blocked'       => (int) ($row['blocked']       ?? 0),
            'preferred'     => (int) ($row['preferred']     ?? 0),
            'international' => (int) ($row['international'] ?? 0),
            'domestic'      => (int) ($row['domestic']      ?? 0),
        ];
    }

    public function getNextSupplierCode(): string
    {
        $stmt = $this->db->query("SELECT MAX(CAST(SUBSTRING(supplier_code, 14) AS UNSIGNED)) FROM `" . self::TABLE . "` WHERE supplier_code LIKE 'MIP_Supplier_%'");
        $maxNum = (int)$stmt->fetchColumn();
        return 'MIP_Supplier_' . str_pad((string)($maxNum + 1), 3, '0', STR_PAD_LEFT);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT s.* FROM `' . self::TABLE . '` s WHERE s.id = :id AND s.deleted_at IS NULL'
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Check if supplier_code already exists
     */
    public function existsByCode(string $code, int $excludeId = 0): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM `' . self::TABLE . '`'
            . ' WHERE supplier_code = :code AND id != :id AND deleted_at IS NULL'
        );
        $stmt->execute(['code' => $code, 'id' => $excludeId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Check if gst_number already exists
     */
    public function existsByGst(string $gst, int $excludeId = 0): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM `' . self::TABLE . '`'
            . ' WHERE gst_number = :gst AND id != :id AND deleted_at IS NULL'
        );
        $stmt->execute(['gst' => $gst, 'id' => $excludeId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Check if email already exists
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

    // =========================================================================
    // WRITE
    // =========================================================================

    public function create(array $data): int
    {
        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ':' . $col, $columns);

        $sql = 'INSERT INTO `' . self::TABLE . '` (' . implode(', ', $columns) . ')'
             . ' VALUES (' . implode(', ', $placeholders) . ')';

        $stmt = $this->db->prepare($sql);
        foreach ($data as $key => $val) {
            $stmt->bindValue(':' . $key, $val);
        }
        $stmt->execute();
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $set = [];
        foreach ($data as $key => $val) {
            $set[] = "`{$key}` = :{$key}";
        }

        $sql = 'UPDATE `' . self::TABLE . '` SET ' . implode(', ', $set)
             . ' WHERE id = :id AND deleted_at IS NULL';

        $stmt = $this->db->prepare($sql);
        foreach ($data as $key => $val) {
            $stmt->bindValue(':' . $key, $val);
        }
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function softDelete(int $id, int $userId): bool
    {
        $sql = 'UPDATE `' . self::TABLE . '`'
             . ' SET deleted_at = CURRENT_TIMESTAMP, deleted_by = :user_id'
             . ' WHERE id = :id AND deleted_at IS NULL';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'id'      => $id,
            'user_id' => $userId
        ]);
        return $stmt->rowCount() > 0;
    }

    // =========================================================================
    // RELATED ENTITIES
    // =========================================================================

    public function getContacts(int $supplierId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM supplier_contacts WHERE supplier_id = :id ORDER BY is_primary DESC, name ASC');
        $stmt->execute(['id' => $supplierId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function saveContacts(int $supplierId, array $contacts): void
    {
        // First delete existing contacts (simplified strategy for this demo)
        $stmt = $this->db->prepare('DELETE FROM supplier_contacts WHERE supplier_id = :id');
        $stmt->execute(['id' => $supplierId]);

        if (empty($contacts)) return;

        $sql = 'INSERT INTO supplier_contacts (supplier_id, name, designation, email, phone, mobile, is_primary) VALUES (?, ?, ?, ?, ?, ?, ?)';
        $stmt = $this->db->prepare($sql);

        foreach ($contacts as $c) {
            if (empty($c['name'])) continue; // skip blank names
            $stmt->execute([
                $supplierId,
                $c['name'],
                $c['designation'] ?? null,
                $c['email'] ?? null,
                $c['phone'] ?? null,
                $c['mobile'] ?? null,
                !empty($c['is_primary']) ? 1 : 0
            ]);
        }
    }

    public function getAddresses(int $supplierId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM supplier_addresses WHERE supplier_id = :id ORDER BY is_primary DESC, address_type ASC');
        $stmt->execute(['id' => $supplierId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function saveAddresses(int $supplierId, array $addresses): void
    {
        $stmt = $this->db->prepare('DELETE FROM supplier_addresses WHERE supplier_id = :id');
        $stmt->execute(['id' => $supplierId]);

        if (empty($addresses)) return;

        $sql = 'INSERT INTO supplier_addresses (supplier_id, address_type, address_line1, address_line2, city_id, state_id, country_id, pin_code, is_primary) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)';
        $stmt = $this->db->prepare($sql);

        foreach ($addresses as $a) {
            if (empty($a['address_line1'])) continue;
            $stmt->execute([
                $supplierId,
                $a['address_type'] ?? 'billing',
                $a['address_line1'],
                $a['address_line2'] ?? null,
                !empty($a['city_id']) ? (int)$a['city_id'] : null,
                !empty($a['state_id']) ? (int)$a['state_id'] : null,
                !empty($a['country_id']) ? (int)$a['country_id'] : null,
                $a['pin_code'] ?? null,
                !empty($a['is_primary']) ? 1 : 0
            ]);
        }
    }

    public function getBankDetails(int $supplierId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM supplier_bank_details WHERE supplier_id = :id ORDER BY is_primary DESC, bank_name ASC');
        $stmt->execute(['id' => $supplierId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function saveBankDetails(int $supplierId, array $banks): void
    {
        $stmt = $this->db->prepare('DELETE FROM supplier_bank_details WHERE supplier_id = :id');
        $stmt->execute(['id' => $supplierId]);

        if (empty($banks)) return;

        $sql = 'INSERT INTO supplier_bank_details (supplier_id, bank_name, account_name, account_number, ifsc_code, swift_code, currency, is_primary) VALUES (?, ?, ?, ?, ?, ?, ?, ?)';
        $stmt = $this->db->prepare($sql);

        foreach ($banks as $b) {
            if (empty($b['bank_name']) || empty($b['account_number'])) continue;
            $stmt->execute([
                $supplierId,
                $b['bank_name'],
                $b['account_name'] ?? '',
                $b['account_number'],
                $b['ifsc_code'] ?? null,
                $b['swift_code'] ?? null,
                $b['currency'] ?? null,
                !empty($b['is_primary']) ? 1 : 0
            ]);
        }
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    /**
     * Builds WHERE clause and bound params for filters
     */
    private function buildWhere(
        string $search, string $status, string $country, string $type, string $priority
    ): array {
        $where = ['s.deleted_at IS NULL'];
        $params = [];

        if ($search !== '') {
            $where[] = '(s.supplier_code LIKE :search OR s.company_name LIKE :search OR s.email LIKE :search OR s.contact_person LIKE :search)';
            $params['search'] = "%{$search}%";
        }
        if ($status !== '') {
            $where[] = 's.status = :status';
            $params['status'] = (int) $status;
        }
        if ($country !== '') {
            $where[] = 's.country_id = :country';
            $params['country'] = (int) $country;
        }
        if ($type !== '') {
            $where[] = 's.supplier_type = :type';
            $params['type'] = $type;
        }
        if ($priority !== '') {
            $where[] = 's.priority = :priority';
            $params['priority'] = $priority;
        }

        return [$where, $params];
    }
}
