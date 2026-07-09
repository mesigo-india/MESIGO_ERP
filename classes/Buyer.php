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
            $where[] = '(b.buyer_code LIKE :search OR b.company_name LIKE :search OR b.primary_contact_name LIKE :search OR b.email LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        if ($status !== '') {
            $where[] = 'b.status = :status';
            $params['status'] = (int) $status;
        }

        $stmt = $this->db->prepare("
            SELECT b.*
            FROM " . self::TABLE . " b
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
        $stmt = $this->db->prepare("SELECT * FROM " . self::TABLE . " WHERE id = :id AND deleted_at IS NULL");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            $result['contacts'] = $this->getContacts($id);
            $result['addresses'] = $this->getAddresses($id);
        }
        
        return $result ?: null;
    }

    public function create(array $data): int
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("
                INSERT INTO " . self::TABLE . " 
                (buyer_code, company_name, company_website, primary_contact_name, primary_contact_email, primary_contact_phone,
                 bank_name, bank_branch, bank_account_number, bank_swift_code, payment_terms, export_region, crm_notes,
                 status, created_at) 
                VALUES 
                (:buyer_code, :company_name, :company_website, :primary_contact_name, :primary_contact_email, :primary_contact_phone,
                 :bank_name, :bank_branch, :bank_account_number, :bank_swift_code, :payment_terms, :export_region, :crm_notes,
                 :status, NOW())
            ");
            $stmt->execute($this->buyerPayload($data));

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
            $payload = $this->buyerPayload($data);
            $payload['id'] = $id;

            $stmt = $this->db->prepare("
                UPDATE " . self::TABLE . "
                SET buyer_code = :buyer_code, company_name = :company_name, company_website = :company_website,
                    primary_contact_name = :primary_contact_name, primary_contact_email = :primary_contact_email, 
                    primary_contact_phone = :primary_contact_phone, bank_name = :bank_name, bank_branch = :bank_branch, 
                    bank_account_number = :bank_account_number, bank_swift_code = :bank_swift_code, 
                    payment_terms = :payment_terms, export_region = :export_region, crm_notes = :crm_notes,
                    status = :status, updated_at = NOW()
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
        $stmt = $this->db->prepare("UPDATE " . self::TABLE . " SET status = 0, deleted_at = NOW() WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function getContacts(int $buyerId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM buyer_contacts WHERE buyer_id = :buyer_id");
        $stmt->execute(['buyer_id' => $buyerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAddresses(int $buyerId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM buyer_addresses WHERE buyer_id = :buyer_id");
        $stmt->execute(['buyer_id' => $buyerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function buyerPayload(array $data): array
    {
        return [
            'buyer_code' => $data['buyer_code'],
            'company_name' => $data['company_name'],
            'company_website' => $data['company_website'] ?? null,
            'primary_contact_name' => $data['primary_contact_name'] ?? null,
            'primary_contact_email' => $data['primary_contact_email'] ?? null,
            'primary_contact_phone' => $data['primary_contact_phone'] ?? null,
            'bank_name' => $data['bank_name'] ?? null,
            'bank_branch' => $data['bank_branch'] ?? null,
            'bank_account_number' => $data['bank_account_number'] ?? null,
            'bank_swift_code' => $data['bank_swift_code'] ?? null,
            'payment_terms' => $data['payment_terms'] ?? null,
            'export_region' => $data['export_region'] ?? null,
            'crm_notes' => $data['crm_notes'] ?? null,
            'status' => (int) ($data['status'] ?? 1),
        ];
    }

    private function syncContacts(int $buyerId, array $contacts): void
    {
        $this->db->prepare("DELETE FROM buyer_contacts WHERE buyer_id = :buyer_id")->execute(['buyer_id' => $buyerId]);
        $stmt = $this->db->prepare("INSERT INTO buyer_contacts (buyer_id, name, designation, email, phone) VALUES (?, ?, ?, ?, ?)");
        foreach ($contacts as $c) {
            $stmt->execute([$buyerId, $c['name'], $c['designation'] ?? null, $c['email'] ?? null, $c['phone'] ?? null]);
        }
    }

    private function syncAddresses(int $buyerId, array $addresses): void
    {
        $this->db->prepare("DELETE FROM buyer_addresses WHERE buyer_id = :buyer_id")->execute(['buyer_id' => $buyerId]);
        $stmt = $this->db->prepare("INSERT INTO buyer_addresses (buyer_id, address_type, address_line1, city, country, postal_code) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($addresses as $a) {
            $stmt->execute([$buyerId, $a['type'], $a['line1'], $a['city'], $a['country'], $a['zip']]);
        }
    }
}
