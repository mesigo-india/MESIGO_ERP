<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use Exception;

/**
 * Buyer Entity
 */
class Buyer
{
    private PDO $db;
    private const TABLE = 'buyers';

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getAll(string $search = '', string $status = '', int $limit = 100, int $offset = 0): array
    {
        $where = ['b.deleted_at IS NULL'];
        $params = [];

        if ($search !== '') {
            $where[] = '(b.buyer_code LIKE :search OR b.company_name LIKE :search OR b.contact_person LIKE :search OR b.email LIKE :search OR b.phone LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        if ($status !== '') {
            $where[] = 'b.status = :status';
            $params['status'] = (int) $status;
        }

        $stmt = $this->db->prepare("
            SELECT b.*, c.name as country_name, s.name as state_name, ci.name as city_name
            FROM " . self::TABLE . " b
            LEFT JOIN countries c ON b.country_id = c.id
            LEFT JOIN states s ON b.state_id = s.id
            LEFT JOIN cities ci ON b.city_id = ci.id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY b.company_name ASC
            LIMIT :limit OFFSET :offset
        ");

        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT b.*, c.name as country_name, s.name as state_name, ci.name as city_name
            FROM " . self::TABLE . " b
            LEFT JOIN countries c ON b.country_id = c.id
            LEFT JOIN states s ON b.state_id = s.id
            LEFT JOIN cities ci ON b.city_id = ci.id
            WHERE b.id = :id AND b.deleted_at IS NULL
        ");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Find by buyer code
     */
    public function findByCode(string $code): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM " . self::TABLE . " 
            WHERE buyer_code = :code AND deleted_at IS NULL
        ");
        $stmt->execute(['code' => $code]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function create(array $data): int
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("
                INSERT INTO " . self::TABLE . " 
                (buyer_code, company_name, contact_person, email, phone, address,
                 country_id, state_id, city_id, gst_number, iec_number, status, 
                 created_by, created_at) 
                VALUES 
                (:buyer_code, :company_name, :contact_person, :email, :phone, :address,
                 :country_id, :state_id, :city_id, :gst_number, :iec_number, :status,
                 :created_by, NOW())
            ");
            $stmt->execute($this->buyerPayload($data, true));

            $buyerId = (int) $this->db->lastInsertId();
            $this->syncContacts($buyerId, $data['contacts'] ?? []);
            $this->syncAddresses($buyerId, $data['addresses'] ?? []);
            $this->db->commit();

            return $buyerId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function update(int $id, array $data): bool
    {
        $this->db->beginTransaction();
        try {
            $payload = $this->buyerPayload($data, false);
            $payload['id'] = $id;

            $stmt = $this->db->prepare("
                UPDATE " . self::TABLE . "
                SET buyer_code = :buyer_code, company_name = :company_name, contact_person = :contact_person,
                    email = :email, phone = :phone, address = :address,
                    country_id = :country_id, state_id = :state_id, city_id = :city_id,
                    gst_number = :gst_number, iec_number = :iec_number, status = :status,
                    updated_by = :updated_by, updated_at = NOW()
                WHERE id = :id AND deleted_at IS NULL
            ");
            $updated = $stmt->execute($payload);

            $this->syncContacts($id, $data['contacts'] ?? []);
            $this->syncAddresses($id, $data['addresses'] ?? []);
            $this->db->commit();

            return $updated;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function delete(int $id, ?int $deletedBy = null): bool
    {
        $stmt = $this->db->prepare("
            UPDATE " . self::TABLE . " 
            SET status = 0, deleted_at = NOW(), deleted_by = :deleted_by
            WHERE id = :id
        ");
        return $stmt->execute([
            'id' => $id,
            'deleted_by' => $deletedBy,
        ]);
    }

    public function getContacts(int $buyerId): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM buyer_contacts
            WHERE buyer_id = :buyer_id AND status = 1
            ORDER BY is_primary DESC, name ASC
        ");
        $stmt->execute(['buyer_id' => $buyerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAddresses(int $buyerId): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM buyer_addresses
            WHERE buyer_id = :buyer_id AND status = 1
            ORDER BY is_default DESC, address_type ASC
        ");
        $stmt->execute(['buyer_id' => $buyerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function count(string $search = '', string $status = ''): int
    {
        $where = ['deleted_at IS NULL'];
        $params = [];

        if ($search !== '') {
            $where[] = '(buyer_code LIKE :search OR company_name LIKE :search OR contact_person LIKE :search OR email LIKE :search OR phone LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        if ($status !== '') {
            $where[] = 'status = :status';
            $params['status'] = (int) $status;
        }

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM " . self::TABLE . " WHERE " . implode(' AND ', $where));
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    private function buyerPayload(array $data, bool $creating): array
    {
        $payload = [
            'buyer_code' => $data['buyer_code'],
            'company_name' => $data['company_name'],
            'contact_person' => $data['contact_person'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => json_encode($data['profile'] ?? []),
            'country_id' => $data['country_id'] ?: null,
            'state_id' => $data['state_id'] ?: null,
            'city_id' => $data['city_id'] ?: null,
            'gst_number' => $data['gst_number'] ?? null,
            'iec_number' => $data['iec_number'] ?? null,
            'status' => (int) ($data['status'] ?? 1),
        ];

        if ($creating) {
            $payload['created_by'] = $data['created_by'] ?? null;
        } else {
            $payload['updated_by'] = $data['updated_by'] ?? null;
        }

        return $payload;
    }

    private function syncContacts(int $buyerId, array $contacts): void
    {
        $this->db->prepare("DELETE FROM buyer_contacts WHERE buyer_id = :buyer_id")->execute(['buyer_id' => $buyerId]);

        $stmt = $this->db->prepare("
            INSERT INTO buyer_contacts (buyer_id, name, designation, email, phone, mobile, is_primary, status, created_at)
            VALUES (:buyer_id, :name, :designation, :email, :phone, :mobile, :is_primary, 1, NOW())
        ");

        foreach ($contacts as $contact) {
            if (trim((string) ($contact['name'] ?? '')) === '') {
                continue;
            }

            $stmt->execute([
                'buyer_id' => $buyerId,
                'name' => trim((string) $contact['name']),
                'designation' => trim((string) ($contact['designation'] ?? '')) ?: null,
                'email' => trim((string) ($contact['email'] ?? '')) ?: null,
                'phone' => trim((string) ($contact['phone'] ?? '')) ?: null,
                'mobile' => trim((string) ($contact['mobile'] ?? '')) ?: null,
                'is_primary' => (int) ($contact['is_primary'] ?? 0),
            ]);
        }
    }

    private function syncAddresses(int $buyerId, array $addresses): void
    {
        $this->db->prepare("DELETE FROM buyer_addresses WHERE buyer_id = :buyer_id")->execute(['buyer_id' => $buyerId]);

        $stmt = $this->db->prepare("
            INSERT INTO buyer_addresses (buyer_id, address_type, address, country_id, state_id, city_id, zip, is_default, status, created_at)
            VALUES (:buyer_id, :address_type, :address, :country_id, :state_id, :city_id, :zip, :is_default, 1, NOW())
        ");

        foreach ($addresses as $address) {
            if (trim((string) ($address['address'] ?? '')) === '') {
                continue;
            }

            $stmt->execute([
                'buyer_id' => $buyerId,
                'address_type' => trim((string) ($address['address_type'] ?? 'billing')) ?: 'billing',
                'address' => trim((string) $address['address']),
                'country_id' => $address['country_id'] ?: null,
                'state_id' => $address['state_id'] ?: null,
                'city_id' => $address['city_id'] ?: null,
                'zip' => trim((string) ($address['zip'] ?? '')) ?: null,
                'is_default' => (int) ($address['is_default'] ?? 0),
            ]);
        }
    }
}