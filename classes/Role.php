<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

class Role
{
    public function __construct(private PDO $db)
    {
    }

    public function getAll(): array
    {
        $stmt = $this->db->prepare("SELECT * FROM roles ORDER BY name ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM roles WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $role = $stmt->fetch(PDO::FETCH_ASSOC);
        return $role ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO roles (name, display_name, permissions, status, created_at)
            VALUES (:name, :display_name, :permissions, :status, NOW())
        ");
        $stmt->execute([
            'name' => $data['name'],
            'display_name' => $data['display_name'] ?? $data['name'],
            'permissions' => json_encode($data['permissions'] ?? []),
            'status' => (int) ($data['status'] ?? 1),
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE roles
            SET name = :name, display_name = :display_name, permissions = :permissions, status = :status, updated_at = NOW()
            WHERE id = :id
        ");
        return $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'display_name' => $data['display_name'] ?? $data['name'],
            'permissions' => json_encode($data['permissions'] ?? []),
            'status' => (int) ($data['status'] ?? 1),
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE roles SET status = 0, updated_at = NOW() WHERE id = :id AND name != 'admin'");
        return $stmt->execute(['id' => $id]);
    }
}