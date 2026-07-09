<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

class Company
{
    public function __construct(private PDO $db)
    {
    }

    public function getAll(): array
    {
        $stmt = $this->db->prepare("SELECT * FROM company ORDER BY company_name ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM company WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $company = $stmt->fetch(PDO::FETCH_ASSOC);
        return $company ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO company (company_name, address, contact_person, email, phone, gst_number, iec_code, status, created_at)
            VALUES (:company_name, :address, :contact_person, :email, :phone, :gst_number, :iec_code, :status, NOW())
        ");

        $stmt->execute($this->payload($data));
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $payload = $this->payload($data);
        $payload['id'] = $id;

        $stmt = $this->db->prepare("
            UPDATE company
            SET company_name = :company_name, address = :address, contact_person = :contact_person,
                email = :email, phone = :phone, gst_number = :gst_number, iec_code = :iec_code,
                status = :status, updated_at = NOW()
            WHERE id = :id
        ");

        return $stmt->execute($payload);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE company SET status = 0, updated_at = NOW() WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    private function payload(array $data): array
    {
        $payload = [
            'company_name' => $data['company_name'],
            'address' => json_encode([
                'line1' => $data['address_line1'] ?? '',
                'line2' => $data['address_line2'] ?? '',
                'city' => $data['city'] ?? '',
                'state' => $data['state'] ?? '',
                'country' => $data['country'] ?? '',
                'zip' => $data['zip'] ?? '',
            ]),
            'contact_person' => $data['contact_person'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'gst_number' => $data['gst_number'] ?? null,
            'iec_code' => $data['iec_code'] ?? null,
            'status' => (int) ($data['status'] ?? 1),
        ];

        return $payload;
    }
}