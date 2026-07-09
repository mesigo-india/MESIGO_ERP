<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use Exception;

/**
 * Buyer Entity - Export CRM Edition
 */
class Buyer
{
    private PDO $db;
    private const TABLE = 'buyers';

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // ... [Existing getAll, findById, count methods remain compatible]

    public function create(array $data): int
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("
                INSERT INTO " . self::TABLE . " 
                (buyer_code, company_name, company_website, primary_contact_name, 
                 primary_contact_email, primary_contact_phone, bank_name, bank_branch, 
                 bank_account_number, bank_swift_code, payment_terms, export_region, 
                 crm_notes, status, created_at) 
                VALUES 
                (:buyer_code, :company_name, :company_website, :primary_contact_name, 
                 :primary_contact_email, :primary_contact_phone, :bank_name, :bank_branch, 
                 :bank_account_number, :bank_swift_code, :payment_terms, :export_region, 
                 :crm_notes, :status, NOW())
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
        $this->db->prepare("DELETE FROM buyer_contacts WHERE buyer_id = :id")->execute(['id' => $buyerId]);
        $stmt = $this->db->prepare("INSERT INTO buyer_contacts (buyer_id, name, designation, email, phone) VALUES (?, ?, ?, ?, ?)");
        foreach ($contacts as $c) {
            $stmt->execute([$buyerId, $c['name'], $c['designation'] ?? null, $c['email'] ?? null, $c['phone'] ?? null]);
        }
    }

    private function syncAddresses(int $buyerId, array $addresses): void
    {
        $this->db->prepare("DELETE FROM buyer_addresses WHERE buyer_id = :id")->execute(['id' => $buyerId]);
        $stmt = $this->db->prepare("INSERT INTO buyer_addresses (buyer_id, address_type, address_line1, city, country, postal_code) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($addresses as $a) {
            $stmt->execute([$buyerId, $a['type'], $a['line1'], $a['city'], $a['country'], $a['zip']]);
        }
    }
}
