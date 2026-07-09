<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use Exception;

/**
 * Attachment Manager for Documents
 */
class AttachmentManager
{
    private PDO $db;
    private const TABLE = 'document_attachments';

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Get all attachments for a document
     */
    public function getByDocument(int $documentHeaderId): array
    {
        $stmt = $this->db->prepare("
            SELECT da.*, u.first_name, u.last_name
            FROM " . self::TABLE . " da
            LEFT JOIN users u ON da.uploaded_by = u.id
            WHERE da.document_header_id = :document_header_id
            ORDER BY da.created_at DESC
        ");
        $stmt->execute(['document_header_id' => $documentHeaderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Add attachment to document
     */
    public function add(int $documentHeaderId, string $fileName, string $originalName, string $filePath, ?string $fileType = null, ?int $fileSize = null, ?string $attachmentType = null, ?int $uploadedById = null): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO " . self::TABLE . " 
            (document_header_id, file_name, original_name, file_path, file_type, file_size, attachment_type, uploaded_by, created_at) 
            VALUES 
            (:document_header_id, :file_name, :original_name, :file_path, :file_type, :file_size, :attachment_type, :uploaded_by, NOW())
        ");
        $stmt->execute([
            'document_header_id' => $documentHeaderId,
            'file_name' => $fileName,
            'original_name' => $originalName,
            'file_path' => $filePath,
            'file_type' => $fileType,
            'file_size' => $fileSize,
            'attachment_type' => $attachmentType,
            'uploaded_by' => $uploadedById,
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Delete attachment
     */
    public function delete(int $attachmentId): bool
    {
        $stmt = $this->db->prepare("
            DELETE FROM " . self::TABLE . " 
            WHERE id = :id
        ");
        return $stmt->execute(['id' => $attachmentId]);
    }

    /**
     * Get attachment by ID
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM " . self::TABLE . " 
            WHERE id = :id
        ");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Get attachments by type
     */
    public function getByType(int $documentHeaderId, string $type): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM " . self::TABLE . " 
            WHERE document_header_id = :document_header_id AND attachment_type = :type
            ORDER BY created_at DESC
        ");
        $stmt->execute([
            'document_header_id' => $documentHeaderId,
            'type' => $type,
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Count attachments for a document
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