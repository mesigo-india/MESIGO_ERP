<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

/**
 * Document Type Entity
 * 
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string|null $description
 * @property bool $is_active
 */
class DocumentType
{
    private PDO $db;
    private const TABLE = 'document_types';

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Get all active document types
     */
    public function getAll(): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM " . self::TABLE . " 
            WHERE is_active = 1 
            ORDER BY name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Find by code
     */
    public function findByCode(string $code): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM " . self::TABLE . " 
            WHERE code = :code AND is_active = 1
        ");
        $stmt->execute(['code' => $code]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Find by ID
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM " . self::TABLE . " 
            WHERE id = :id AND is_active = 1
        ");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }
}