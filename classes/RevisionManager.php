<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use Exception;

/**
 * Revision Manager for Documents
 */
class RevisionManager
{
    private PDO $db;
    private const TABLE = 'document_revisions';

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Get all revisions for a document
     */
    public function getByDocument(int $documentHeaderId): array
    {
        $stmt = $this->db->prepare("
            SELECT dr.*, u.first_name, u.last_name
            FROM " . self::TABLE . " dr
            LEFT JOIN users u ON dr.created_by = u.id
            WHERE dr.document_header_id = :document_header_id
            ORDER BY dr.revision_number DESC
        ");
        $stmt->execute(['document_header_id' => $documentHeaderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create a revision
     */
    public function create(int $documentHeaderId, int $revisionNumber, array $documentData, ?string $notes = null, ?int $createdBy = null): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO " . self::TABLE . " 
            (document_header_id, revision_number, document_data, revision_notes, created_by, created_at) 
            VALUES 
            (:document_header_id, :revision_number, :document_data, :revision_notes, :created_by, NOW())
        ");
        $stmt->execute([
            'document_header_id' => $documentHeaderId,
            'revision_number' => $revisionNumber,
            'document_data' => json_encode($documentData),
            'revision_notes' => $notes,
            'created_by' => $createdBy,
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Get next revision number
     */
    public function getNextRevisionNumber(int $documentHeaderId): int
    {
        $stmt = $this->db->prepare("
            SELECT MAX(revision_number) as max_revision 
            FROM " . self::TABLE . " 
            WHERE document_header_id = :document_header_id
        ");
        $stmt->execute(['document_header_id' => $documentHeaderId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($result['max_revision'] ?? 0) + 1;
    }

    /**
     * Get revision by number
     */
    public function findByRevision(int $documentHeaderId, int $revisionNumber): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM " . self::TABLE . " 
            WHERE document_header_id = :document_header_id AND revision_number = :revision_number
        ");
        $stmt->execute([
            'document_header_id' => $documentHeaderId,
            'revision_number' => $revisionNumber,
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Delete revision
     */
    public function delete(int $revisionId): bool
    {
        $stmt = $this->db->prepare("
            DELETE FROM " . self::TABLE . " 
            WHERE id = :id
        ");
        return $stmt->execute(['id' => $revisionId]);
    }

    /**
     * Count revisions for a document
     */
    public function count(int $documentHeaderId): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM " . self::TABLE . " 
            WHERE document_header_id = :document_header_id
        ");
        $stmt->execute(['document_header_id' => $documentHeaderId]);
        return (int) $stmt->fetchColumn();
    }
}