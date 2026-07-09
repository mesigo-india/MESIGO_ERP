<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use Exception;

/**
 * Document Header Entity
 */
class DocumentHeader
{
    private PDO $db;
    private const TABLE = 'document_headers';

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Get all documents with pagination
     */
    public function getAll(int $limit = 15, int $offset = 0, array $filters = []): array
    {
        $where = ["dh.status != 0"];
        $params = [];

        if (!empty($filters['document_type_id'])) {
            $where[] = "dh.document_type_id = :document_type_id";
            $params['document_type_id'] = $filters['document_type_id'];
        }

        if (!empty($filters['buyer_id'])) {
            $where[] = "dh.buyer_id = :buyer_id";
            $params['buyer_id'] = $filters['buyer_id'];
        }

        if (!empty($filters['status'])) {
            $where[] = "dh.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['date_from'])) {
            $where[] = "dh.document_date >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = "dh.document_date <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }

        $sql = "
            SELECT dh.*, dt.name as document_type, dt.code as document_type_code,
                   b.company_name as buyer_name, s.company_name as seller_name,
                   c.code as currency_code, c.symbol as currency_symbol,
                   u.first_name as created_by_name, u.last_name as created_by_lastname
            FROM " . self::TABLE . " dh
            LEFT JOIN document_types dt ON dh.document_type_id = dt.id
            LEFT JOIN buyers b ON dh.buyer_id = b.id
            LEFT JOIN suppliers s ON dh.seller_id = s.id
            LEFT JOIN currencies c ON dh.currency_id = c.id
            LEFT JOIN users u ON dh.created_by = u.id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY dh.document_date DESC, dh.id DESC
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Find by ID
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT dh.*, dt.name as document_type, dt.code as document_type_code,
                   b.company_name as buyer_name, s.company_name as seller_name,
                   c.code as currency_code, c.symbol as currency_symbol
            FROM " . self::TABLE . " dh
            LEFT JOIN document_types dt ON dh.document_type_id = dt.id
            LEFT JOIN buyers b ON dh.buyer_id = b.id
            LEFT JOIN suppliers s ON dh.seller_id = s.id
            LEFT JOIN currencies c ON dh.currency_id = c.id
            WHERE dh.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Find by document number
     */
    public function findByNumber(string $number): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM " . self::TABLE . " 
            WHERE document_number = :number
        ");
        $stmt->execute(['number' => $number]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Create document
     */
    public function create(array $data): int
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("
                INSERT INTO " . self::TABLE . " 
                (document_type_id, document_number, document_date, buyer_id, seller_id,
                 currency_id, exchange_rate, shipment_type, incoterm_id,
                 loading_port_id, destination_port_id, payment_term_id,
                 validity_days, expected_shipment, remarks, internal_notes,
                 status, created_by, created_at) 
                VALUES 
                (:document_type_id, :document_number, :document_date, :buyer_id, :seller_id,
                 :currency_id, :exchange_rate, :shipment_type, :incoterm_id,
                 :loading_port_id, :destination_port_id, :payment_term_id,
                 :validity_days, :expected_shipment, :remarks, :internal_notes,
                 :status, :created_by, NOW())
            ");
            $stmt->execute([
                'document_type_id' => $data['document_type_id'],
                'document_number' => $data['document_number'],
                'document_date' => $data['document_date'],
                'buyer_id' => $data['buyer_id'] ?? null,
                'seller_id' => $data['seller_id'] ?? null,
                'currency_id' => $data['currency_id'],
                'exchange_rate' => $data['exchange_rate'] ?? 1.000000,
                'shipment_type' => $data['shipment_type'] ?? null,
                'incoterm_id' => $data['incoterm_id'] ?? null,
                'loading_port_id' => $data['loading_port_id'] ?? null,
                'destination_port_id' => $data['destination_port_id'] ?? null,
                'payment_term_id' => $data['payment_term_id'] ?? null,
                'validity_days' => $data['validity_days'] ?? null,
                'expected_shipment' => $data['expected_shipment'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'internal_notes' => $data['internal_notes'] ?? null,
                'status' => $data['status'] ?? 1,
                'created_by' => $data['created_by'] ?? null,
            ]);
            $id = (int) $this->db->lastInsertId();
            $this->db->commit();
            return $id;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Update document
     */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE " . self::TABLE . " 
            SET document_date = :document_date, buyer_id = :buyer_id, seller_id = :seller_id,
                currency_id = :currency_id, exchange_rate = :exchange_rate,
                shipment_type = :shipment_type, incoterm_id = :incoterm_id,
                loading_port_id = :loading_port_id, destination_port_id = :destination_port_id,
                payment_term_id = :payment_term_id, validity_days = :validity_days,
                expected_shipment = :expected_shipment, remarks = :remarks,
                internal_notes = :internal_notes, updated_by = :updated_by,
                updated_at = NOW()
            WHERE id = :id
        ");
        return $stmt->execute([
            'id' => $id,
            'document_date' => $data['document_date'],
            'buyer_id' => $data['buyer_id'] ?? null,
            'seller_id' => $data['seller_id'] ?? null,
            'currency_id' => $data['currency_id'],
            'exchange_rate' => $data['exchange_rate'] ?? 1.000000,
            'shipment_type' => $data['shipment_type'] ?? null,
            'incoterm_id' => $data['incoterm_id'] ?? null,
            'loading_port_id' => $data['loading_port_id'] ?? null,
            'destination_port_id' => $data['destination_port_id'] ?? null,
            'payment_term_id' => $data['payment_term_id'] ?? null,
            'validity_days' => $data['validity_days'] ?? null,
            'expected_shipment' => $data['expected_shipment'] ?? null,
            'remarks' => $data['remarks'] ?? null,
            'internal_notes' => $data['internal_notes'] ?? null,
            'updated_by' => $data['updated_by'] ?? null,
        ]);
    }

    /**
     * Update status
     */
    public function updateStatus(int $id, int $status, int $changedBy, ?string $remarks = null): bool
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("
                UPDATE " . self::TABLE . " 
                SET status = :status, updated_at = NOW()
                WHERE id = :id
            ");
            $stmt->execute([
                'id' => $id,
                'status' => $status,
            ]);

            // Add to status history
            $historyStmt = $this->db->prepare("
                INSERT INTO document_status_history 
                (document_header_id, new_status, remarks, changed_by, created_at)
                VALUES (:document_header_id, :new_status, :remarks, :changed_by, NOW())
            ");
            $historyStmt->execute([
                'document_header_id' => $id,
                'new_status' => $status,
                'remarks' => $remarks,
                'changed_by' => $changedBy,
            ]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Count total documents
     */
    public function count(array $filters = []): int
    {
        $where = ["status != 0"];
        $params = [];

        if (!empty($filters['document_type_id'])) {
            $where[] = "document_type_id = :document_type_id";
            $params['document_type_id'] = $filters['document_type_id'];
        }

        if (!empty($filters['buyer_id'])) {
            $where[] = "buyer_id = :buyer_id";
            $params['buyer_id'] = $filters['buyer_id'];
        }

        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM " . self::TABLE . " 
            WHERE " . implode(' AND ', $where) . "
        ");
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }
}
