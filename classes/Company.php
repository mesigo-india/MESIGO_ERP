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
            INSERT INTO company (
                company_name, address, contact_person, email, phone, website,
                gst_number, iec_code, pan_number, cin_number, apeda_number, fssai_number, iso_number, haccp_number,
                bank_name, account_name, account_number, ifsc_code, swift_code,
                logo_path, stamp_path, seal_path, signature_path, digital_signature_path,
                letterhead_type, letterhead_path, letterhead_export_path, letterhead_domestic_path,
                declaration, status, created_at,
                print_margin_top, print_margin_bottom, print_margin_left, print_margin_right,
                signature_print_width, seal_print_width, stamp_print_width
            ) VALUES (
                :company_name, :address, :contact_person, :email, :phone, :website,
                :gst_number, :iec_code, :pan_number, :cin_number, :apeda_number, :fssai_number, :iso_number, :haccp_number,
                :bank_name, :account_name, :account_number, :ifsc_code, :swift_code,
                :logo_path, :stamp_path, :seal_path, :signature_path, :digital_signature_path,
                :letterhead_type, :letterhead_path, :letterhead_export_path, :letterhead_domestic_path,
                :declaration, :status, NOW(),
                :print_margin_top, :print_margin_bottom, :print_margin_left, :print_margin_right,
                :signature_print_width, :seal_print_width, :stamp_print_width
            )
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
                email = :email, phone = :phone, website = :website,
                gst_number = :gst_number, iec_code = :iec_code, pan_number = :pan_number, cin_number = :cin_number,
                apeda_number = :apeda_number, fssai_number = :fssai_number, iso_number = :iso_number, haccp_number = :haccp_number,
                bank_name = :bank_name, account_name = :account_name, account_number = :account_number, ifsc_code = :ifsc_code, swift_code = :swift_code,
                logo_path = :logo_path, stamp_path = :stamp_path, seal_path = :seal_path, signature_path = :signature_path, digital_signature_path = :digital_signature_path,
                letterhead_type = :letterhead_type, letterhead_path = :letterhead_path, letterhead_export_path = :letterhead_export_path, letterhead_domestic_path = :letterhead_domestic_path,
                declaration = :declaration, status = :status, updated_at = NOW(),
                print_margin_top = :print_margin_top, print_margin_bottom = :print_margin_bottom,
                print_margin_left = :print_margin_left, print_margin_right = :print_margin_right,
                signature_print_width = :signature_print_width, seal_print_width = :seal_print_width,
                stamp_print_width = :stamp_print_width
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
        return [
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
            'website' => $data['website'] ?? null,
            
            'gst_number' => $data['gst_number'] ?? null,
            'iec_code' => $data['iec_code'] ?? null,
            'pan_number' => $data['pan_number'] ?? null,
            'cin_number' => $data['cin_number'] ?? null,
            'apeda_number' => $data['apeda_number'] ?? null,
            'fssai_number' => $data['fssai_number'] ?? null,
            'iso_number' => $data['iso_number'] ?? null,
            'haccp_number' => $data['haccp_number'] ?? null,
            
            'bank_name' => $data['bank_name'] ?? null,
            'account_name' => $data['account_name'] ?? null,
            'account_number' => $data['account_number'] ?? null,
            'ifsc_code' => $data['ifsc_code'] ?? null,
            'swift_code' => $data['swift_code'] ?? null,
            
            'logo_path' => $data['logo_path'] ?? null,
            'stamp_path' => $data['stamp_path'] ?? null,
            'seal_path' => $data['seal_path'] ?? null,
            'signature_path' => $data['signature_path'] ?? null,
            'digital_signature_path' => $data['digital_signature_path'] ?? null,
            
            'letterhead_type' => $data['letterhead_type'] ?? 'plain',
            'letterhead_path' => $data['letterhead_path'] ?? null,
            'letterhead_export_path' => $data['letterhead_export_path'] ?? null,
            'letterhead_domestic_path' => $data['letterhead_domestic_path'] ?? null,
            
            'declaration' => $data['declaration'] ?? null,
            'status' => (int) ($data['status'] ?? 1),
            'print_margin_top' => (int) ($data['print_margin_top'] ?? 45),
            'print_margin_bottom' => (int) ($data['print_margin_bottom'] ?? 35),
            'print_margin_left' => (int) ($data['print_margin_left'] ?? 20),
            'print_margin_right' => (int) ($data['print_margin_right'] ?? 20),
            'signature_print_width' => (int) ($data['signature_print_width'] ?? 120),
            'seal_print_width' => (int) ($data['seal_print_width'] ?? 100),
            'stamp_print_width' => (int) ($data['stamp_print_width'] ?? 100),
        ];
    }
}