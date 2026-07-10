<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use Exception;

/**
 * Role-Based Document Approval Workflow Engine
 * Manages multi-stage role sign-offs and status transition triggers
 */
class ApprovalWorkflowEngine
{
    private PDO $db;
    private DocumentStatusEngine $statusEngine;

    public function __construct(PDO $db, DocumentStatusEngine $statusEngine)
    {
        $this->db = $db;
        $this->statusEngine = $statusEngine;
    }

    /**
     * Submit a document for approval routing to one or more roles
     */
    public function submitForApproval(int $documentId, array $roleApprovers, int $userId): bool
    {
        $stmt = $this->db->prepare("SELECT status FROM document_headers WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $documentId]);
        $currentStatus = $stmt->fetchColumn();

        if ($currentStatus === false) {
            throw new Exception("Document header not found: ID={$documentId}");
        }

        $currentStatus = (int) $currentStatus;
        if ($currentStatus !== DocumentStatusEngine::STATUS_DRAFT && $currentStatus !== DocumentStatusEngine::STATUS_REJECTED) {
            throw new Exception("Document cannot be submitted for approval in its current state.");
        }

        $this->db->beginTransaction();
        try {
            // Clear past approval logs
            $stmt = $this->db->prepare("DELETE FROM document_approvals WHERE document_header_id = :id");
            $stmt->execute(['id' => $documentId]);

            // Insert pending approvals for target roles
            foreach ($roleApprovers as $roleId) {
                $stmt = $this->db->prepare("
                    INSERT INTO document_approvals 
                    (document_header_id, assigned_role_id, approval_status, created_at)
                    VALUES (:doc_id, :role_id, 'pending', NOW())
                ");
                $stmt->execute([
                    'doc_id' => $documentId,
                    'role_id' => (int) $roleId
                ]);
            }

            // Update document status to Pending Manager Review (1)
            $stmt = $this->db->prepare("
                UPDATE document_headers 
                SET status = :status, updated_by = :user, updated_at = NOW() 
                WHERE id = :id
            ");
            $stmt->execute([
                'status' => DocumentStatusEngine::STATUS_PENDING,
                'user' => $userId,
                'id' => $documentId
            ]);

            // Add history event
            $this->statusEngine->addHistory(
                $documentId, 
                $currentStatus, 
                DocumentStatusEngine::STATUS_PENDING, 
                $userId, 
                'Submitted for workflow routing'
            );

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Records an approval/rejection decision by a user of a specific role
     */
    public function recordDecision(
        int $documentId, 
        int $userId, 
        int $userRoleId, 
        string $decision, 
        ?string $remarks = null
    ): bool {
        $decision = strtolower(trim($decision));
        if ($decision !== 'approved' && $decision !== 'rejected') {
            throw new Exception("Invalid decision value. Must be 'approved' or 'rejected'.");
        }

        // Find the pending approval record for this document and role
        $stmt = $this->db->prepare("
            SELECT id FROM document_approvals 
            WHERE document_header_id = :doc_id AND assigned_role_id = :role_id AND approval_status = 'pending'
            LIMIT 1
        ");
        $stmt->execute(['doc_id' => $documentId, 'role_id' => $userRoleId]);
        $approvalId = $stmt->fetchColumn();

        if ($approvalId === false) {
            // Check if user is an administrator (can bypass or override)
            if ($userRoleId === 1) { // Admin role ID = 1
                $stmt = $this->db->prepare("
                    SELECT id FROM document_approvals 
                    WHERE document_header_id = :doc_id AND approval_status = 'pending'
                    LIMIT 1
                ");
                $stmt->execute(['doc_id' => $documentId]);
                $approvalId = $stmt->fetchColumn();
            }
            
            if ($approvalId === false) {
                throw new Exception("No active pending approval step found for this document and role context.");
            }
        }

        $approvalId = (int) $approvalId;

        $this->db->beginTransaction();
        try {
            if ($decision === 'approved') {
                // Mark this step approved
                $stmt = $this->db->prepare("
                    UPDATE document_approvals 
                    SET approver_id = :user, approval_status = 'approved', remarks = :remarks, actioned_at = NOW()
                    WHERE id = :id
                ");
                $stmt->execute(['user' => $userId, 'remarks' => $remarks, 'id' => $approvalId]);

                // Check for remaining pending steps
                $stmt = $this->db->prepare("
                    SELECT COUNT(*) FROM document_approvals 
                    WHERE document_header_id = :doc_id AND approval_status = 'pending'
                ");
                $stmt->execute(['doc_id' => $documentId]);
                $pendingCount = (int) $stmt->fetchColumn();

                if ($pendingCount === 0) {
                    // All workflow steps approved -> Update header to STATUS_APPROVED (2)
                    $stmt = $this->db->prepare("
                        UPDATE document_headers 
                        SET status = :status, updated_by = :user, updated_at = NOW() 
                        WHERE id = :id
                    ");
                    $stmt->execute([
                        'status' => DocumentStatusEngine::STATUS_APPROVED,
                        'user' => $userId,
                        'id' => $documentId
                    ]);

                    $this->statusEngine->addHistory(
                        $documentId,
                        DocumentStatusEngine::STATUS_PENDING,
                        DocumentStatusEngine::STATUS_APPROVED,
                        $userId,
                        'All stages approved: Release final.'
                    );
                } else {
                    // Log current status update
                    $this->statusEngine->addHistory(
                        $documentId,
                        DocumentStatusEngine::STATUS_PENDING,
                        DocumentStatusEngine::STATUS_PENDING,
                        $userId,
                        "Approved step (Role ID: {$userRoleId}): " . ($remarks ?: '')
                    );
                }
            } else {
                // Rejection: Mark this step rejected
                $stmt = $this->db->prepare("
                    UPDATE document_approvals 
                    SET approver_id = :user, approval_status = 'rejected', remarks = :remarks, actioned_at = NOW()
                    WHERE id = :id
                ");
                $stmt->execute(['user' => $userId, 'remarks' => $remarks, 'id' => $approvalId]);

                // Cancel all remaining pending steps
                $stmt = $this->db->prepare("
                    UPDATE document_approvals 
                    SET approval_status = 'rejected', remarks = 'Workflow cancelled due to rejection'
                    WHERE document_header_id = :doc_id AND approval_status = 'pending'
                ");
                $stmt->execute(['doc_id' => $documentId]);

                // Update document header to STATUS_REJECTED (3)
                $stmt = $this->db->prepare("
                    UPDATE document_headers 
                    SET status = :status, updated_by = :user, updated_at = NOW() 
                    WHERE id = :id
                ");
                $stmt->execute([
                    'status' => DocumentStatusEngine::STATUS_REJECTED,
                    'user' => $userId,
                    'id' => $documentId
                ]);

                $this->statusEngine->addHistory(
                    $documentId,
                    DocumentStatusEngine::STATUS_PENDING,
                    DocumentStatusEngine::STATUS_REJECTED,
                    $userId,
                    'Workflow rejected: ' . ($remarks ?: '')
                );
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
