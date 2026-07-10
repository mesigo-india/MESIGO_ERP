<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

class Product
{
    private const TABLE = 'products';

    public function __construct(private PDO $db)
    {
    }

    public function columnList(): array
    {
        return [
            'product_code', 'name', 'category_id', 'variety_id', 'hsn_code', 'unit_id', 'packing_type_id', 'description', 'status',
            'country_of_origin', 'country_id', 'state_id', 'city_id', 'scientific_name', 'crop_year', 'harvest_season', 'shelf_life', 'storage_conditions', 'temperature_req',
            'moisture_percent', 'purity_percent', 'admixture_percent', 'broken_percent', 'color', 'smell',
            'default_currency', 'purchase_price', 'selling_price', 'moq', 'max_oq', 'lead_time_days', 'gst_percent',
            'packing_size', 'net_weight', 'gross_weight', 'bags_per_container', 'container_type', 'pallet_type', 'shipping_marks',
            'default_shipment_type', 'preferred_loading_port', 'preferred_destination_port', 'preferred_incoterm', 'preferred_payment_method',
            'is_machine_clean', 'is_sortex', 'is_hand_picked', 'is_steam_sterilized', 'is_organic',
            'cert_eu_standard', 'cert_us_fda', 'cert_iso', 'cert_haccp', 'cert_fssai', 'cert_apeda', 'cert_asta',
            'opening_stock', 'reorder_level', 'safety_stock', 'warehouse_location', 'rack_location', 'bin_location',
            'is_featured', 'is_priority', 'is_export', 'is_domestic', 'remarks'
        ];
    }

    public function insertData(array $d): array
    {
        $n = fn($k, $arr) => (isset($arr[$k]) && $arr[$k] !== '') ? $arr[$k] : null;
        $b = fn($k, $arr) => !empty($arr[$k]) ? 1 : 0;
        
        return [
            'product_code'               => $n('product_code', $d),
            'name'                       => $n('name', $d),
            'category_id'                => $n('category_id', $d),
            'variety_id'                 => $n('variety_id', $d),
            'hsn_code'                   => $n('hsn_code', $d),
            'unit_id'                    => $n('unit_id', $d),
            'packing_type_id'            => $n('packing_type_id', $d),
            'description'                => $n('description', $d),
            'status'                     => $d['status'] ?? 1,
            
            'country_of_origin'          => $n('country_of_origin', $d),
            'country_id'                 => $n('country_id', $d),
            'state_id'                   => $n('state_id', $d),
            'city_id'                    => $n('city_id', $d),
            'scientific_name'            => $n('scientific_name', $d),
            'crop_year'                  => $n('crop_year', $d),
            'harvest_season'             => $n('harvest_season', $d),
            'shelf_life'                 => $n('shelf_life', $d),
            'storage_conditions'         => $n('storage_conditions', $d),
            'temperature_req'            => $n('temperature_req', $d),
            
            'moisture_percent'           => $n('moisture_percent', $d),
            'purity_percent'             => $n('purity_percent', $d),
            'admixture_percent'          => $n('admixture_percent', $d),
            'broken_percent'             => $n('broken_percent', $d),
            'color'                      => $n('color', $d),
            'smell'                      => $n('smell', $d),
            
            'default_currency'           => $n('default_currency', $d),
            'purchase_price'             => $n('purchase_price', $d),
            'selling_price'              => $n('selling_price', $d),
            'moq'                        => $n('moq', $d),
            'max_oq'                     => $n('max_oq', $d),
            'lead_time_days'             => $n('lead_time_days', $d),
            'gst_percent'                => $n('gst_percent', $d),
            
            'packing_size'               => $n('packing_size', $d),
            'net_weight'                 => $n('net_weight', $d),
            'gross_weight'               => $n('gross_weight', $d),
            'bags_per_container'         => $n('bags_per_container', $d),
            'container_type'             => $n('container_type', $d),
            'pallet_type'                => $n('pallet_type', $d),
            'shipping_marks'             => $n('shipping_marks', $d),
            
            'default_shipment_type'      => $n('default_shipment_type', $d),
            'preferred_loading_port'     => $n('preferred_loading_port', $d),
            'preferred_destination_port' => $n('preferred_destination_port', $d),
            'preferred_incoterm'         => $n('preferred_incoterm', $d),
            'preferred_payment_method'   => $n('preferred_payment_method', $d),
            
            'is_machine_clean'           => $b('is_machine_clean', $d),
            'is_sortex'                  => $b('is_sortex', $d),
            'is_hand_picked'             => $b('is_hand_picked', $d),
            'is_steam_sterilized'        => $b('is_steam_sterilized', $d),
            'is_organic'                 => $b('is_organic', $d),
            
            'cert_eu_standard'           => $b('cert_eu_standard', $d),
            'cert_us_fda'                => $b('cert_us_fda', $d),
            'cert_iso'                   => $b('cert_iso', $d),
            'cert_haccp'                 => $b('cert_haccp', $d),
            'cert_fssai'                 => $b('cert_fssai', $d),
            'cert_apeda'                 => $b('cert_apeda', $d),
            'cert_asta'                  => $b('cert_asta', $d),
            
            'opening_stock'              => $n('opening_stock', $d),
            'reorder_level'              => $n('reorder_level', $d),
            'safety_stock'               => $n('safety_stock', $d),
            'warehouse_location'         => $n('warehouse_location', $d),
            'rack_location'              => $n('rack_location', $d),
            'bin_location'               => $n('bin_location', $d),
            
            'is_featured'                => $b('is_featured', $d),
            'is_priority'                => $b('is_priority', $d),
            'is_export'                  => $b('is_export', $d),
            'is_domestic'                => $b('is_domestic', $d),
            'remarks'                    => $n('remarks', $d)
        ];
    }

    public function getAll(string $search = '', string $status = '', int $limit = 100, int $offset = 0, array $filters = []): array
    {
        $where = ['p.deleted_at IS NULL'];
        $params = [];

        if ($search !== '') {
            $where[] = '(p.product_code LIKE :search1 OR p.name LIKE :search2 OR p.hsn_code LIKE :search3)';
            $params['search1'] = '%' . $search . '%';
            $params['search2'] = '%' . $search . '%';
            $params['search3'] = '%' . $search . '%';
        }

        if ($status !== '') {
            $where[] = 'p.status = :status';
            $params['status'] = (int) $status;
        }

        if (!empty($filters['category_id'])) {
            $where[] = 'p.category_id = :category_id';
            $params['category_id'] = (int)$filters['category_id'];
        }
        
        if (!empty($filters['country_of_origin'])) {
            $where[] = 'p.country_of_origin = :country_of_origin';
            $params['country_of_origin'] = $filters['country_of_origin'];
        }

        $stmt = $this->db->prepare("
            SELECT p.*, pc.name AS category_name, u.code AS unit_code, pt.name AS packing_type_name, hs.description AS hs_description, c.name AS country_name
            FROM `" . self::TABLE . "` p
            LEFT JOIN product_categories pc ON p.category_id = pc.id
            LEFT JOIN units u ON p.unit_id = u.id
            LEFT JOIN packing_types pt ON p.packing_type_id = pt.id
            LEFT JOIN hs_codes hs ON p.hsn_code = hs.hs_code
            LEFT JOIN countries c ON p.country_id = c.id
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

    public function getTotalCount(string $search = '', string $status = '', array $filters = []): int
    {
        $where = ['deleted_at IS NULL'];
        $params = [];

        if ($search !== '') {
            $where[] = '(product_code LIKE :search1 OR name LIKE :search2 OR hsn_code LIKE :search3)';
            $params['search1'] = '%' . $search . '%';
            $params['search2'] = '%' . $search . '%';
            $params['search3'] = '%' . $search . '%';
        }
        if ($status !== '') {
            $where[] = 'status = :status';
            $params['status'] = (int) $status;
        }
        if (!empty($filters['category_id'])) {
            $where[] = 'category_id = :category_id';
            $params['category_id'] = (int)$filters['category_id'];
        }
        if (!empty($filters['country_of_origin'])) {
            $where[] = 'country_of_origin = :country_of_origin';
            $params['country_of_origin'] = $filters['country_of_origin'];
        }

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM `" . self::TABLE . "` WHERE " . implode(' AND ', $where));
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function getDashboardStats(): array
    {
        $stmt = $this->db->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) as inactive,
                SUM(CASE WHEN is_featured = 1 AND status = 1 THEN 1 ELSE 0 END) as featured,
                SUM(CASE WHEN is_export = 1 AND status = 1 THEN 1 ELSE 0 END) as export_ready
            FROM `" . self::TABLE . "` 
            WHERE deleted_at IS NULL
        ");
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['total'=>0, 'active'=>0, 'inactive'=>0, 'featured'=>0, 'export_ready'=>0];
    }

    public function getNextProductCode(): string
    {
        $stmt = $this->db->query("SELECT MAX(CAST(SUBSTRING(product_code, 6) AS UNSIGNED)) FROM `" . self::TABLE . "` WHERE product_code LIKE 'PROD-%'");
        $maxNum = (int)$stmt->fetchColumn();
        return 'PROD-' . str_pad((string)($maxNum + 1), 3, '0', STR_PAD_LEFT);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM `" . self::TABLE . "` WHERE id = :id AND deleted_at IS NULL");
        $stmt->execute(['id' => $id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        return $product ?: null;
    }

    public function existsByCode(string $code, int $excludeId = 0): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM `" . self::TABLE . "` WHERE product_code = :code AND id != :id AND deleted_at IS NULL");
        $stmt->execute(['code' => $code, 'id' => $excludeId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function create(array $d): int
    {
        $cols = $this->columnList();
        $sets = implode(', ', array_map(fn($c) => '`' . $c . '`', $cols));
        $plac = ':' . implode(', :', $cols);

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("
                INSERT INTO `" . self::TABLE . "`
                    ($sets, created_by, created_at)
                VALUES
                    ($plac, :created_by, NOW())
            ");
            
            $payload = $this->insertData($d);
            $payload['created_by'] = $d['created_by'] ?? null;
            
            $stmt->execute($payload);
            $productId = (int) $this->db->lastInsertId();
            $this->db->commit();
            return $productId;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function update(int $id, array $d): bool
    {
        $cols = $this->columnList();
        $sets = implode(', ', array_map(fn($c) => "`$c` = :$c", $cols));

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("
                UPDATE `" . self::TABLE . "`
                SET $sets, updated_by = :updated_by, updated_at = NOW()
                WHERE id = :id AND deleted_at IS NULL
            ");

            $payload = $this->insertData($d);
            $payload['id'] = $id;
            $payload['updated_by'] = $d['updated_by'] ?? null;

            $updated = $stmt->execute($payload);
            $this->db->commit();
            return $updated;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function delete(int $id, ?int $deletedBy = null): bool
    {
        $stmt = $this->db->prepare("UPDATE `" . self::TABLE . "` SET status = 0, deleted_at = NOW(), deleted_by = :deleted_by WHERE id = :id");
        return $stmt->execute(['id' => $id, 'deleted_by' => $deletedBy]);
    }

    public function getPackaging(int $productId): array
    {
        return []; // Obsolete, merged directly to Product Export Table as bags_per_container, packing_size etc.
    }

    public function productCategories(): array { return $this->masterRows('product_categories', 'name'); }
    public function productGrades(): array { return $this->masterRows('product_grades', 'name'); }
    public function productOrigins(): array { return $this->masterRows('product_origins', 'name'); }
    public function hsCodes(): array { return $this->masterRows('hs_codes', 'hs_code'); }
    public function packingTypes(): array { return $this->masterRows('packing_types', 'name'); }
    public function units(): array { return $this->masterRows('units', 'name'); }

    private function masterRows(string $table, string $orderBy): array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$table} WHERE status != 0 ORDER BY {$orderBy} ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}