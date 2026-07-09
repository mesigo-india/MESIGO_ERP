<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

class Permission
{
    public function __construct(private PDO $db)
    {
    }

    public function getAll(): array
    {
        $stmt = $this->db->prepare("SELECT * FROM permissions ORDER BY module ASC, name ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM permissions WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $permission = $stmt->fetch(PDO::FETCH_ASSOC);
        return $permission ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO permissions (name, display_name, description, module, status, created_at)
            VALUES (:name, :display_name, :description, :module, :status, NOW())
        ");
        $stmt->execute([
            'name' => $data['name'],
            'display_name' => $data['display_name'] ?? $data['name'],
            'description' => $data['description'] ?? null,
            'module' => $data['module'] ?? null,
            'status' => (int) ($data['status'] ?? 1),
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE permissions
            SET name = :name, display_name = :display_name, description = :description, module = :module, status = :status, updated_at = NOW()
            WHERE id = :id
        ");
        return $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'display_name' => $data['display_name'] ?? $data['name'],
            'description' => $data['description'] ?? null,
            'module' => $data['module'] ?? null,
            'status' => (int) ($data['status'] ?? 1),
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE permissions SET status = 0, updated_at = NOW() WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}