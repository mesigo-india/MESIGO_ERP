<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

class Product
{
    public function __construct(private PDO $db)
    {
    }

    public function getAll(string $search = '', string $status = '', int $limit = 100, int $offset = 0): array
    {
        $where = ['p.deleted_at IS NULL'];
        $params = [];

        if ($search !== '') {
            $where[] = '(p.product_code LIKE :search OR p.name LIKE :search OR p.hsn_code LIKE :search OR p.description LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        if ($status !== '') {
            $where[] = 'p.status = :status';
            $params['status'] = (int) $status;
        }

        $stmt = $this->db->prepare("
            SELECT p.*, pc.name AS category_name, u.code AS unit_code, pt.name AS packing_type_name, hs.description AS hs_description
            FROM products p
            LEFT JOIN product_categories pc ON p.category_id = pc.id
            LEFT JOIN units u ON p.unit_id = u.id
            LEFT JOIN packing_types pt ON p.packing_type_id = pt.id
            LEFT JOIN hs_codes hs ON p.hsn_code = hs.hs_code
            WHERE " . implode(' AND ', $where) . "
            ORDER BY p.name ASC
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
        $stmt = $this->db->prepare("SELECT * FROM products WHERE id = :id AND deleted_at IS NULL");
        $stmt->execute(['id' => $id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        return $product ?: null;
    }

    public function findByCode(string $code): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM products WHERE product_code = :product_code AND deleted_at IS NULL");
        $stmt->execute(['product_code' => $code]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        return $product ?: null;
    }

    public function create(array $data): int
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("
                INSERT INTO products
                    (product_code, name, category_id, variety_id, hsn_code, unit_id, packing_type_id, description, status, created_by, created_at)
                VALUES
                    (:product_code, :name, :category_id, :variety_id, :hsn_code, :unit_id, :packing_type_id, :description, :status, :created_by, NOW())
            ");
            $stmt->execute($this->productPayload($data, true));

            $productId = (int) $this->db->lastInsertId();
            $this->syncPackaging($productId, $data['packaging'] ?? []);
            $this->db->commit();

            return $productId;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function update(int $id, array $data): bool
    {
        $this->db->beginTransaction();
        try {
            $payload = $this->productPayload($data, false);
            $payload['id'] = $id;

            $stmt = $this->db->prepare("
                UPDATE products
                SET product_code = :product_code, name = :name, category_id = :category_id, variety_id = :variety_id,
                    hsn_code = :hsn_code, unit_id = :unit_id, packing_type_id = :packing_type_id,
                    description = :description, status = :status, updated_by = :updated_by, updated_at = NOW()
                WHERE id = :id AND deleted_at IS NULL
            ");
            $updated = $stmt->execute($payload);

            $this->syncPackaging($id, $data['packaging'] ?? []);
            $this->db->commit();

            return $updated;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function delete(int $id, ?int $deletedBy = null): bool
    {
        $stmt = $this->db->prepare("UPDATE products SET status = 0, deleted_at = NOW(), deleted_by = :deleted_by WHERE id = :id");
        return $stmt->execute(['id' => $id, 'deleted_by' => $deletedBy]);
    }

    public function getPackaging(int $productId): array
    {
        $stmt = $this->db->prepare("
            SELECT pp.*, pt.name AS packing_type_name, u.code AS unit_code
            FROM product_packaging pp
            LEFT JOIN packing_types pt ON pp.packing_type_id = pt.id
            LEFT JOIN units u ON pp.unit_id = u.id
            WHERE pp.product_id = :product_id AND pp.status = 1
            ORDER BY pp.id ASC
        ");
        $stmt->execute(['product_id' => $productId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function productCategories(): array
    {
        return $this->masterRows('product_categories', 'name');
    }

    public function productGrades(): array
    {
        return $this->masterRows('product_grades', 'name');
    }

    public function productOrigins(): array
    {
        return $this->masterRows('product_origins', 'name');
    }

    public function hsCodes(): array
    {
        return $this->masterRows('hs_codes', 'hs_code');
    }

    public function packingTypes(): array
    {
        return $this->masterRows('packing_types', 'name');
    }

    public function units(): array
    {
        return $this->masterRows('units', 'name');
    }

    public function decodeMeta(?string $description): array
    {
        if (!$description) {
            return [];
        }

        $meta = json_decode($description, true);
        if (is_array($meta)) {
            return $meta;
        }

        return ['description_text' => $description];
    }

    private function productPayload(array $data, bool $creating): array
    {
        $payload = [
            'product_code' => $data['product_code'],
            'name' => $data['name'],
            'category_id' => $data['category_id'] ?: null,
            'variety_id' => null,
            'hsn_code' => $data['hsn_code'] ?: null,
            'unit_id' => $data['unit_id'] ?: null,
            'packing_type_id' => $data['packing_type_id'] ?: null,
            'description' => json_encode($data['meta'] ?? []),
            'status' => (int) ($data['status'] ?? 1),
        ];

        if ($creating) {
            $payload['created_by'] = $data['created_by'] ?? null;
        } else {
            $payload['updated_by'] = $data['updated_by'] ?? null;
        }

        return $payload;
    }

    private function syncPackaging(int $productId, array $packaging): void
    {
        $this->db->prepare("DELETE FROM product_packaging WHERE product_id = :product_id")->execute(['product_id' => $productId]);

        $stmt = $this->db->prepare("
            INSERT INTO product_packaging (product_id, packing_type_id, unit_id, quantity_per_pack, status, created_at)
            VALUES (:product_id, :packing_type_id, :unit_id, :quantity_per_pack, 1, NOW())
        ");

        foreach ($packaging as $row) {
            if ((int) ($row['packing_type_id'] ?? 0) === 0 && (int) ($row['unit_id'] ?? 0) === 0) {
                continue;
            }

            $stmt->execute([
                'product_id' => $productId,
                'packing_type_id' => (int) ($row['packing_type_id'] ?? 0) ?: null,
                'unit_id' => (int) ($row['unit_id'] ?? 0) ?: null,
                'quantity_per_pack' => (float) ($row['quantity_per_pack'] ?? 0),
            ]);
        }
    }

    private function masterRows(string $table, string $orderBy): array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$table} WHERE status != 0 ORDER BY {$orderBy} ASC");
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}