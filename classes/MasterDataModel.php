<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

abstract class MasterDataModel
{
    protected PDO $db;
    protected string $table;
    protected string $codeField = 'code';
    protected array $fillable = [];
    protected array $searchable = [];

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getAll(int $limit = 100, int $offset = 0, string $search = ''): array
    {
        $where = ['status != 0', 'deleted_at IS NULL'];
        $params = [];

        if ($search !== '' && $this->searchable !== []) {
            $parts = [];
            foreach ($this->searchable as $field) {
                $parts[] = "{$field} LIKE :search";
            }
            $where[] = '(' . implode(' OR ', $parts) . ')';
            $params['search'] = '%' . $search . '%';
        }

        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE " . implode(' AND ', $where) .
            " ORDER BY created_at DESC LIMIT :limit OFFSET :offset"
        );
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id AND deleted_at IS NULL");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function create(array $data): int
    {
        $data = $this->onlyFillable($data);
        $columns = array_keys($data);
        $placeholders = array_map(fn(string $column): string => ':' . $column, $columns);

        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table} (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")"
        );
        $stmt->execute($data);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $data = $this->onlyFillable($data);
        unset($data[$this->codeField]);

        if ($data === []) {
            return false;
        }

        $sets = [];
        foreach (array_keys($data) as $column) {
            $sets[] = "{$column} = :{$column}";
        }
        $data['id'] = $id;

        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET " . implode(', ', $sets) . ", updated_at = NOW() WHERE id = :id"
        );

        return $stmt->execute($data);
    }

    public function delete(int $id, ?int $deletedBy = null): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET status = 0, deleted_at = NOW(), deleted_by = :deleted_by WHERE id = :id"
        );

        return $stmt->execute(['id' => $id, 'deleted_by' => $deletedBy]);
    }

    public function count(): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE status != 0 AND deleted_at IS NULL");
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    protected function generateCode(string $prefix): string
    {
        $stmt = $this->db->prepare("SELECT COALESCE(MAX(id), 0) + 1 FROM {$this->table}");
        $stmt->execute();

        return $prefix . '-' . date('Ymd') . '-' . str_pad((string) $stmt->fetchColumn(), 4, '0', STR_PAD_LEFT);
    }

    private function onlyFillable(array $data): array
    {
        return array_intersect_key($data, array_flip($this->fillable));
    }
}
