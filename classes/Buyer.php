<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

class Buyer
{
    private PDO $db;
    private const TABLE = 'buyers';

    public function __construct(PDO $db) { $this->db = $db; }

    public function getAll(string $search = '', string $status = '', string $country = '', string $type = '', string $priority = '', int $limit = 20, int $offset = 0): array
    {
        $where = ['deleted_at IS NULL'];
        $params = [];
        if ($search !== '') {
            $where[] = '(buyer_code LIKE :search OR company_name LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }
        if ($status !== '') { $where[] = 'status = :status'; $params['status'] = $status; }
        if ($country !== '') { $where[] = 'country = :country'; $params['country'] = $country; }
        if ($type !== '') { $where[] = 'buyer_type = :type'; $params['type'] = $type; }
        if ($priority !== '') { $where[] = 'priority = :priority'; $params['priority'] = $priority; }

        $stmt = $this->db->prepare("SELECT * FROM " . self::TABLE . " WHERE " . implode(' AND ', $where) . " ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
        foreach ($params as $k => $v) $stmt->bindValue(':' . $k, $v);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function count(string $search = '', string $status = '', string $country = '', string $type = '', string $priority = ''): int
    {
        $where = ['deleted_at IS NULL'];
        $params = [];
        if ($search !== '') { $where[] = '(buyer_code LIKE :search OR company_name LIKE :search)'; $params['search'] = '%' . $search . '%'; }
        if ($status !== '') { $where[] = 'status = :status'; $params['status'] = $status; }
        if ($country !== '') { $where[] = 'country = :country'; $params['country'] = $country; }
        if ($type !== '') { $where[] = 'buyer_type = :type'; $params['type'] = $type; }
        if ($priority !== '') { $where[] = 'priority = :priority'; $params['priority'] = $priority; }
        
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM " . self::TABLE . " WHERE " . implode(' AND ', $where));
        foreach ($params as $k => $v) $stmt->bindValue(':' . $k, $v);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM " . self::TABLE . " WHERE id = :id AND deleted_at IS NULL");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function create(array $d): int
    {
        $stmt = $this->db->prepare("INSERT INTO " . self::TABLE . " (buyer_code, company_name, buyer_type, priority, lead_source, status, contact_person, designation, email, mobile, phone, website, whatsapp, billing_address, shipping_address, country, state, city, zip, gst_number, iec_number, registration_number, tax_number, bank_name, account_name, account_number, swift_ifsc, payment_terms, credit_days, shipping_mode, preferred_port, shipping_marks, assigned_to, last_contact, next_followup, notes, created_at) VALUES (:buyer_code, :company_name, :buyer_type, :priority, :lead_source, :status, :contact_person, :designation, :email, :mobile, :phone, :website, :whatsapp, :billing_address, :shipping_address, :country, :state, :city, :zip, :gst_number, :iec_number, :registration_number, :tax_number, :bank_name, :account_name, :account_number, :swift_ifsc, :payment_terms, :credit_days, :shipping_mode, :preferred_port, :shipping_marks, :assigned_to, :last_contact, :next_followup, :notes, NOW())");
        $stmt->execute($this->mapData($d));
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $d): bool
    {
        $d['id'] = $id;
        $stmt = $this->db->prepare("UPDATE " . self::TABLE . " SET buyer_code=:buyer_code, company_name=:company_name, buyer_type=:buyer_type, priority=:priority, lead_source=:lead_source, status=:status, contact_person=:contact_person, designation=:designation, email=:email, mobile=:mobile, phone=:phone, website=:website, whatsapp=:whatsapp, billing_address=:billing_address, shipping_address=:shipping_address, country=:country, state=:state, city=:city, zip=:zip, gst_number=:gst_number, iec_number=:iec_number, registration_number=:registration_number, tax_number=:tax_number, bank_name=:bank_name, account_name=:account_name, account_number=:account_number, swift_ifsc=:swift_ifsc, payment_terms=:payment_terms, credit_days=:credit_days, shipping_mode=:shipping_mode, preferred_port=:preferred_port, shipping_marks=:shipping_marks, assigned_to=:assigned_to, last_contact=:last_contact, next_followup=:next_followup, notes=:notes, updated_at=NOW() WHERE id=:id");
        return $stmt->execute($this->mapData($d));
    }

    private function mapData(array $d): array
    {
        return [
            'id' => $d['id'] ?? null,
            'buyer_code' => $d['buyer_code'], 'company_name' => $d['company_name'], 'buyer_type' => $d['buyer_type'],
            'priority' => $d['priority'], 'lead_source' => $d['lead_source'], 'status' => $d['status'],
            'contact_person' => $d['contact_person'], 'designation' => $d['designation'] ?? null,
            'email' => $d['email'], 'mobile' => $d['mobile'] ?? null, 'phone' => $d['phone'] ?? null,
            'website' => $d['website'] ?? null, 'whatsapp' => $d['whatsapp'] ?? null,
            'billing_address' => $d['billing_address'], 'shipping_address' => $d['shipping_address'] ?? null,
            'country' => $d['country'], 'state' => $d['state'], 'city' => $d['city'], 'zip' => $d['zip'],
            'gst_number' => $d['gst_number'] ?? null, 'iec_number' => $d['iec_number'] ?? null,
            'registration_number' => $d['registration_number'] ?? null, 'tax_number' => $d['tax_number'] ?? null,
            'bank_name' => $d['bank_name'] ?? null, 'account_name' => $d['account_name'] ?? null,
            'account_number' => $d['account_number'] ?? null, 'swift_ifsc' => $d['swift_ifsc'] ?? null,
            'payment_terms' => $d['payment_terms'] ?? null, 'credit_days' => $d['credit_days'] ?? null,
            'shipping_mode' => $d['shipping_mode'] ?? null, 'preferred_port' => $d['preferred_port'] ?? null,
            'shipping_marks' => $d['shipping_marks'] ?? null, 'assigned_to' => $d['assigned_to'] ?? null,
            'last_contact' => $d['last_contact'] ?? null, 'next_followup' => $d['next_followup'] ?? null,
            'notes' => $d['notes'] ?? null
        ];
    }
}
