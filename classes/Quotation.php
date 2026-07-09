<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

class Quotation
{
    public const STATUS_DRAFT = 0;
    public const STATUS_PENDING = 1;
    public const STATUS_APPROVED = 2;
    public const STATUS_REJECTED = 3;
    public const STATUS_SENT = 4;
    public const STATUS_CONVERTED = 5;
    public const STATUS_EXPIRED = 6;

    private DocumentType $documentTypes;
    private NumberGenerator $numbers;
    private RevisionManager $revisions;
    private DocumentStatusEngine $statusEngine;

    public function __construct(private PDO $db)
    {
        $this->documentTypes = new DocumentType($db);
        $this->numbers = new NumberGenerator($db);
        $this->revisions = new RevisionManager($db);
        $this->statusEngine = new DocumentStatusEngine($db);
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_PENDING => 'Pending',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_SENT => 'Sent',
            self::STATUS_CONVERTED => 'Converted',
            self::STATUS_EXPIRED => 'Expired',
        ];
    }

    public static function statusLabel(int $status): string
    {
        return self::statuses()[$status] ?? 'Unknown';
    }

    public static function statusBadgeClass(int $status): string
    {
        return match ($status) {
            self::STATUS_DRAFT => 'bg-secondary',
            self::STATUS_PENDING => 'bg-warning',
            self::STATUS_APPROVED => 'bg-success',
            self::STATUS_REJECTED => 'bg-danger',
            self::STATUS_SENT => 'bg-info',
            self::STATUS_CONVERTED => 'bg-primary',
            self::STATUS_EXPIRED => 'bg-dark',
            default => 'bg-secondary',
        };
    }

    public function getAll(string $search = '', string $status = '', int $limit = 100, int $offset = 0): array
    {
        $type = $this->quotationType();
        $where = ['dh.document_type_id = :type_id', 'dh.deleted_at IS NULL'];
        $params = ['type_id' => (int) $type['id']];

        if ($search !== '') {
            $where[] = '(dh.document_number LIKE :search OR b.company_name LIKE :search OR dh.remarks LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        if ($status !== '') {
            $where[] = 'dh.status = :status';
            $params['status'] = (int) $status;
        }

        $stmt = $this->db->prepare("
            SELECT dh.*, b.company_name AS buyer_name, c.code AS currency_code, c.symbol AS currency_symbol
            FROM document_headers dh
            LEFT JOIN buyers b ON dh.buyer_id = b.id
            LEFT JOIN currencies c ON dh.currency_id = c.id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY dh.document_date DESC, dh.id DESC
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
        $type = $this->quotationType();
        $stmt = $this->db->prepare("
            SELECT dh.*, b.company_name AS buyer_name, c.code AS currency_code, c.symbol AS currency_symbol,
                   i.code AS incoterm_code, pt.name AS payment_term_name, lp.name AS loading_port_name, dp.name AS delivery_port_name
            FROM document_headers dh
            LEFT JOIN buyers b ON dh.buyer_id = b.id
            LEFT JOIN currencies c ON dh.currency_id = c.id
            LEFT JOIN incoterms i ON dh.incoterm_id = i.id
            LEFT JOIN payment_terms pt ON dh.payment_term_id = pt.id
            LEFT JOIN ports lp ON dh.loading_port_id = lp.id
            LEFT JOIN ports dp ON dh.destination_port_id = dp.id
            WHERE dh.id = :id AND dh.document_type_id = :type_id AND dh.deleted_at IS NULL
        ");
        $stmt->execute(['id' => $id, 'type_id' => (int) $type['id']]);
        $quotation = $stmt->fetch(PDO::FETCH_ASSOC);

        return $quotation ?: null;
    }

    public function getItems(int $quotationId): array
    {
        $stmt = $this->db->prepare("
            SELECT di.*, p.name AS product_name, p.product_code, u.code AS unit_code, pk.name AS packing_type_name
            FROM document_items di
            LEFT JOIN products p ON di.product_id = p.id
            LEFT JOIN units u ON di.unit_id = u.id
            LEFT JOIN packing_types pk ON di.packing_type_id = pk.id
            WHERE di.document_header_id = :id
            ORDER BY di.sort_order ASC, di.id ASC
        ");
        $stmt->execute(['id' => $quotationId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $data): int
    {
        $data['document_number'] = $this->numbers->generate($this->seriesName());
        return $this->save(null, $data);
    }

    public function update(int $id, array $data): bool
    {
        $this->save($id, $data);
        return true;
    }

    public function revise(int $id, string $notes, ?int $userId): int
    {
        $quotation = $this->findById($id);
        $items = $this->getItems($id);
        $revision = $this->revisions->getNextRevisionNumber($id);

        return $this->revisions->create($id, $revision, ['header' => $quotation, 'items' => $items], $notes, $userId);
    }

    public function updateStatus(int $id, int $status, int $userId, ?string $remarks = null): bool
    {
        $quotation = $this->findById($id);
        $oldStatus = (int) ($quotation['status'] ?? self::STATUS_DRAFT);
        $stmt = $this->db->prepare("UPDATE document_headers SET status = :status, updated_by = :user_id, updated_at = NOW() WHERE id = :id");
        $updated = $stmt->execute(['id' => $id, 'status' => $status, 'user_id' => $userId]);
        $this->statusEngine->addHistory($id, $oldStatus, $status, $userId, $remarks);

        return $updated;
    }

    public function revisions(int $id): array
    {
        return $this->revisions->getByDocument($id);
    }

    public function statusHistory(int $id): array
    {
        return $this->statusEngine->getHistory($id);
    }

    public function convertToProforma(int $id, int $userId): int
    {
        return $this->convertTo($id, 'proforma_invoice', $userId, ['status' => 0]);
    }

    public function convertTo(int $id, string $targetType, int $userId, array $overrides = []): int
    {
        $converter = new DocumentConversionEngine($this->db);
        return $converter->convert($id, $targetType, $userId, $overrides);
    }

    public function masterRows(string $table, string $orderBy): array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$table} WHERE status != 0 ORDER BY {$orderBy} ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contacts(int $buyerId): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM buyer_contacts
            WHERE status = 1
            ORDER BY buyer_id ASC, is_primary DESC, name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function totals(array $items, array $charges): array
    {
        $subtotal = $discount = $gst = 0.0;
        foreach ($items as $item) {
            $line = (float) ($item['quantity'] ?? 0) * (float) ($item['rate'] ?? 0);
            $lineDiscount = $line * ((float) ($item['discount_percent'] ?? 0) / 100);
            $taxable = $line - $lineDiscount;
            $lineGst = $taxable * ((float) ($item['tax_percent'] ?? 0) / 100);
            $subtotal += $line;
            $discount += $lineDiscount;
            $gst += $lineGst;
        }

        $freight = (float) ($charges['freight'] ?? 0);
        $insurance = (float) ($charges['insurance'] ?? 0);
        $other = (float) ($charges['other_charges'] ?? 0);
        $grand = $subtotal - $discount + $gst + $freight + $insurance + $other;

        return compact('subtotal', 'discount', 'gst', 'freight', 'insurance', 'other', 'grand');
    }

    public function meta(?string $internalNotes): array
    {
        $meta = json_decode((string) $internalNotes, true);
        return is_array($meta) ? $meta : [];
    }

    protected function save(?int $id, array $data): int
    {
        $type = $this->quotationType();
        $totals = $this->totals($data['items'] ?? [], $data['charges'] ?? []);
        $validityDays = !empty($data['valid_until']) ? max(0, (int) floor((strtotime($data['valid_until']) - strtotime($data['document_date'])) / 86400)) : null;
        $meta = [
            'revision' => (int) ($data['revision'] ?? 0),
            'buyer_contact_id' => (int) ($data['buyer_contact_id'] ?? 0),
            'shipment_term' => $data['shipment_term'] ?? '',
            'valid_until' => $data['valid_until'] ?? '',
            'charges' => $data['charges'] ?? [],
            'totals' => $totals,
            'email_ready' => true,
            'pdf_ready' => true,
            'print_ready' => true,
        ];

        $this->db->beginTransaction();
        try {
            if ($id === null) {
                $stmt = $this->db->prepare("
                    INSERT INTO document_headers (document_type_id, document_number, document_date, buyer_id, currency_id, exchange_rate, shipment_type, incoterm_id, loading_port_id, destination_port_id, payment_term_id, validity_days, remarks, internal_notes, status, created_by, created_at)
                    VALUES (:document_type_id, :document_number, :document_date, :buyer_id, :currency_id, 1, :shipment_type, :incoterm_id, :loading_port_id, :destination_port_id, :payment_term_id, :validity_days, :remarks, :internal_notes, :status, :created_by, NOW())
                ");
                $stmt->execute($this->headerPayload($data, $type, $validityDays, $meta, true));
                $id = (int) $this->db->lastInsertId();
            } else {
                $payload = $this->headerPayload($data, $type, $validityDays, $meta, false);
                $payload['id'] = $id;
                $stmt = $this->db->prepare("
                    UPDATE document_headers SET document_date = :document_date, buyer_id = :buyer_id, currency_id = :currency_id, shipment_type = :shipment_type, incoterm_id = :incoterm_id, loading_port_id = :loading_port_id, destination_port_id = :destination_port_id, payment_term_id = :payment_term_id, validity_days = :validity_days, remarks = :remarks, internal_notes = :internal_notes, status = :status, updated_by = :updated_by, updated_at = NOW()
                    WHERE id = :id
                ");
                unset($payload['document_type_id'], $payload['document_number'], $payload['created_by']);
                $stmt->execute($payload);
            }

            $this->syncItems($id, $data['items'] ?? []);
            $this->syncCharges($id, $data['charges'] ?? []);
            $this->db->commit();
            return $id;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    protected function headerPayload(array $data, array $type, ?int $validityDays, array $meta, bool $creating): array
    {
        $payload = [
            'document_type_id' => (int) $type['id'],
            'document_number' => $data['document_number'] ?? '',
            'document_date' => $data['document_date'],
            'buyer_id' => (int) ($data['buyer_id'] ?? 0) ?: null,
            'currency_id' => (int) ($data['currency_id'] ?? 0),
            'shipment_type' => $data['shipment_term'] ?? null,
            'incoterm_id' => (int) ($data['incoterm_id'] ?? 0) ?: null,
            'loading_port_id' => (int) ($data['loading_port_id'] ?? 0) ?: null,
            'destination_port_id' => (int) ($data['delivery_port_id'] ?? 0) ?: null,
            'payment_term_id' => (int) ($data['payment_term_id'] ?? 0) ?: null,
            'validity_days' => $validityDays,
            'remarks' => $data['remarks'] ?? null,
            'internal_notes' => json_encode($meta),
            'status' => (int) ($data['status'] ?? self::STATUS_DRAFT),
        ];

        $payload[$creating ? 'created_by' : 'updated_by'] = $data[$creating ? 'created_by' : 'updated_by'] ?? null;
        return $payload;
    }

    protected function syncItems(int $id, array $items): void
    {
        $this->db->prepare("DELETE FROM document_items WHERE document_header_id = :id")->execute(['id' => $id]);
        $stmt = $this->db->prepare("
            INSERT INTO document_items (document_header_id, product_id, hsn_code, quality, packing_type_id, unit_id, quantity, rate, discount_percent, discount_amount, tax_percent, tax_amount, net_amount, sort_order, created_at)
            VALUES (:document_header_id, :product_id, :hsn_code, :quality, :packing_type_id, :unit_id, :quantity, :rate, :discount_percent, :discount_amount, :tax_percent, :tax_amount, :net_amount, :sort_order, NOW())
        ");
        foreach ($items as $index => $item) {
            if ((int) ($item['product_id'] ?? 0) <= 0) {
                continue;
            }
            $line = (float) ($item['quantity'] ?? 0) * (float) ($item['rate'] ?? 0);
            $discount = $line * ((float) ($item['discount_percent'] ?? 0) / 100);
            $taxable = $line - $discount;
            $tax = $taxable * ((float) ($item['tax_percent'] ?? 0) / 100);
            $stmt->execute([
                'document_header_id' => $id,
                'product_id' => (int) $item['product_id'],
                'hsn_code' => $item['hsn_code'] ?? null,
                'quality' => json_encode(['grade_id' => (int) ($item['grade_id'] ?? 0), 'origin_id' => (int) ($item['origin_id'] ?? 0)]),
                'packing_type_id' => (int) ($item['packing_type_id'] ?? 0) ?: null,
                'unit_id' => (int) ($item['unit_id'] ?? 0) ?: null,
                'quantity' => (float) ($item['quantity'] ?? 0),
                'rate' => (float) ($item['rate'] ?? 0),
                'discount_percent' => (float) ($item['discount_percent'] ?? 0),
                'discount_amount' => $discount,
                'tax_percent' => (float) ($item['tax_percent'] ?? 0),
                'tax_amount' => $tax,
                'net_amount' => $taxable + $tax,
                'sort_order' => $index,
            ]);
        }
    }

    protected function syncCharges(int $id, array $charges): void
    {
        $this->db->prepare("DELETE FROM document_charges WHERE document_header_id = :id")->execute(['id' => $id]);
        $stmt = $this->db->prepare("INSERT INTO document_charges (document_header_id, charge_name, charge_amount, total_amount, sort_order, created_at) VALUES (:id, :name, :amount, :amount, :sort_order, NOW())");
        foreach (['freight' => 'Freight', 'insurance' => 'Insurance', 'other_charges' => 'Other Charges'] as $key => $label) {
            $amount = (float) ($charges[$key] ?? 0);
            if ($amount > 0) {
                $stmt->execute(['id' => $id, 'name' => $label, 'amount' => $amount, 'sort_order' => array_search($key, array_keys($charges), true) ?: 0]);
            }
        }
    }

    protected function quotationType(): array
    {
        $type = $this->documentTypes->findByCode($this->documentCode());
        if (!$type) {
            throw new \RuntimeException($this->documentCode() . ' document type not found.');
        }
        return $type;
    }

    protected function documentCode(): string
    {
        return 'quotation';
    }

    protected function seriesName(): string
    {
        return 'quotation';
    }
}