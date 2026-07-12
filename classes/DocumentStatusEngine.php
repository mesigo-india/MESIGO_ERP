<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use Exception;

/**
 * Document Status & Lifecycle Rule Engine
 * Manages document status workflow, validation, locks, and action buttons.
 */
class DocumentStatusEngine
{
    private PDO $db;
    
    // Standard Lifecycle Status Codes
    public const STATUS_DRAFT = 0;
    public const STATUS_PENDING = 1;
    public const STATUS_APPROVED = 2;
    public const STATUS_REJECTED = 3;
    public const STATUS_CONVERTED = 4;
    public const STATUS_COMPLETED = 5;
    public const STATUS_CANCELLED = 6;
    public const STATUS_ARCHIVED = 7;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Get status label
     */
    public static function getStatusLabel(int $status): string
     {
         $labels = [
             self::STATUS_DRAFT => 'Draft',
             self::STATUS_PENDING => 'Pending Approval',
             self::STATUS_APPROVED => 'Approved',
             self::STATUS_REJECTED => 'Rejected',
             self::STATUS_CONVERTED => 'Converted',
             self::STATUS_COMPLETED => 'Completed',
             self::STATUS_CANCELLED => 'Cancelled',
             self::STATUS_ARCHIVED => 'Archived',
         ];
         return $labels[$status] ?? 'Unknown';
     }

     /**
      * Get status badge class
      */
     public static function getStatusBadgeClass(int $status): string
     {
         $classes = [
             self::STATUS_DRAFT => 'bg-secondary',
             self::STATUS_PENDING => 'bg-warning text-dark',
             self::STATUS_APPROVED => 'bg-success',
             self::STATUS_REJECTED => 'bg-danger',
             self::STATUS_CONVERTED => 'bg-primary',
             self::STATUS_COMPLETED => 'bg-info text-dark',
             self::STATUS_CANCELLED => 'bg-dark',
             self::STATUS_ARCHIVED => 'bg-dark',
         ];
         return $classes[$status] ?? 'bg-secondary';
     }

     /**
      * Check if status transition is valid (static structural check)
      */
     public static function isValidTransition(int $fromStatus, int $toStatus): bool
     {
         $validTransitions = [
             self::STATUS_DRAFT => [self::STATUS_PENDING, self::STATUS_CANCELLED],
             self::STATUS_PENDING => [self::STATUS_APPROVED, self::STATUS_REJECTED],
             self::STATUS_APPROVED => [self::STATUS_CONVERTED, self::STATUS_CANCELLED],
             self::STATUS_REJECTED => [self::STATUS_DRAFT],
             self::STATUS_CONVERTED => [self::STATUS_COMPLETED],
             self::STATUS_COMPLETED => [self::STATUS_ARCHIVED],
         ];

         if (!isset($validTransitions[$fromStatus])) {
             return false;
         }

         return in_array($toStatus, $validTransitions[$fromStatus]);
     }

     /**
      * Get timeline history for a document
      */
     public function getHistory(int $documentHeaderId): array
     {
         $stmt = $this->db->prepare("
             SELECT dsh.*, u.first_name, u.last_name
             FROM document_status_history dsh
             LEFT JOIN users u ON dsh.changed_by = u.id
             WHERE dsh.document_header_id = :document_header_id
             ORDER BY dsh.created_at DESC
         ");
         $stmt->execute(['document_header_id' => $documentHeaderId]);
         return $stmt->fetchAll(PDO::FETCH_ASSOC);
     }

     /**
      * Add status history entry
      */
     public function addHistory(int $documentHeaderId, int $oldStatus, int $newStatus, int $changedBy, ?string $remarks = null): bool
     {
         $stmt = $this->db->prepare("
             INSERT INTO document_status_history 
             (document_header_id, old_status, new_status, remarks, changed_by, created_at)
             VALUES (:document_header_id, :old_status, :new_status, :remarks, :changed_by, NOW())
         ");
         return $stmt->execute([
             'document_header_id' => $documentHeaderId,
             'old_status' => $oldStatus,
             'new_status' => $newStatus,
             'remarks' => $remarks,
             'changed_by' => $changedBy,
         ]);
     }

     /**
      * Get timeline for a document
      */
     public function getTimeline(int $documentHeaderId): array
     {
         $stmt = $this->db->prepare("
             SELECT dsh.*, u.first_name, u.last_name
             FROM document_status_history dsh
             LEFT JOIN users u ON dsh.changed_by = u.id
             WHERE dsh.document_header_id = :document_header_id
             ORDER BY dsh.created_at ASC
         ");
         $stmt->execute(['document_header_id' => $documentHeaderId]);
         $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

         $headerStmt = $this->db->prepare("
             SELECT created_by, created_at FROM document_headers
             WHERE id = :id
         ");
         $headerStmt->execute(['id' => $documentHeaderId]);
         $header = $headerStmt->fetch(PDO::FETCH_ASSOC);

         if ($header) {
             array_unshift($history, [
                 'old_status' => null,
                 'new_status' => 0,
                 'remarks' => 'Document created',
                 'changed_by' => $header['created_by'],
                 'created_at' => $header['created_at'],
             ]);
         }

         return $history;
     }

     /**
      * Check if a downstream document exists for the given document
      */
     public function getDownstreamDocument(int $id): ?array
     {
         $stmt = $this->db->prepare("
             SELECT dh.id, dh.document_number, dt.code AS type_code, dt.name AS type_name, dh.status
             FROM document_headers dh
             JOIN document_types dt ON dh.document_type_id = dt.id
             WHERE dh.converted_from_id = :id AND dh.deleted_at IS NULL
             LIMIT 1
         ");
         $stmt->execute(['id' => $id]);
         return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
     }

     /**
      * Check if editing is allowed
      */
     public function canEdit(int $id, int $status): bool
     {
         if ($status !== self::STATUS_DRAFT) {
             return false;
         }
         return $this->getDownstreamDocument($id) === null;
     }

     /**
      * Check if deleting is allowed
      */
     public function canDelete(int $id, int $status): bool
     {
         if ($status !== self::STATUS_DRAFT) {
             return false;
         }
         return $this->getDownstreamDocument($id) === null;
     }

     /**
      * Check if status transition is allowed (incorporating downstream locks)
      */
     public function canChangeStatus(int $id, int $fromStatus, int $toStatus): bool
     {
         if (!self::isValidTransition($fromStatus, $toStatus)) {
             return false;
         }
         // Approved to Cancelled requires no downstream docs
         if ($fromStatus === self::STATUS_APPROVED && $toStatus === self::STATUS_CANCELLED) {
             return $this->getDownstreamDocument($id) === null;
         }
         return true;
     }
}