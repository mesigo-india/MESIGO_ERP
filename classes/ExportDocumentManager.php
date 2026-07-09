<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

class ExportDocumentManager
{
    private AttachmentManager $attachments;
    private DocumentStatusEngine $statusEngine;

    public function __construct(private PDO $db)
    {
        $this->attachments = new AttachmentManager($db);
        $this->statusEngine = new DocumentStatusEngine($db);
    }

    public function getOrders(string $search = '', string $status = ''): array
    {
        $where = ['dh.deleted_at IS NULL'];
        $params = [];

        if ($search !== '') {
            $where[] = '(dh.document_number LIKE :search OR b.company_name LIKE :search OR dt.name LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        if ($status !== '') {
            $where[] = 'dh.status = :status';
            $params['status'] = (int) $status;
        }

        $stmt = $this->db->prepare("
            SELECT dh.*, dt.name AS document_type, dt.code AS document_type_code, b.company_name AS buyer_name,
                   COUNT(da.id) AS attachment_count
            FROM document_headers dh
            LEFT JOIN document_types dt ON dh.document_type_id = dt.id
            LEFT JOIN buyers b ON dh.buyer_id = b.id
            LEFT JOIN document_attachments da ON da.document_header_id = dh.id
            WHERE " . implode(' AND ', $where) . "
            GROUP BY dh.id
            ORDER BY dh.document_date DESC, dh.id DESC
            LIMIT 100
        ");

        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findDocument(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT dh.*, dt.name AS document_type, dt.code AS document_type_code, b.company_name AS buyer_name
            FROM document_headers dh
            LEFT JOIN document_types dt ON dh.document_type_id = dt.id
            LEFT JOIN buyers b ON dh.buyer_id = b.id
            WHERE dh.id = :id AND dh.deleted_at IS NULL
        ");
        $stmt->execute(['id' => $id]);
        $document = $stmt->fetch(PDO::FETCH_ASSOC);
        return $document ?: null;
    }

    public function getVault(int $documentId): array
    {
        $attachments = $this->attachments->getByDocument($documentId);
        $vault = [];
        foreach (self::documentTypes() as $type => $label) {
            $vault[$type] = ['label' => $label, 'files' => []];
        }
        foreach ($attachments as $attachment) {
            $type = $attachment['attachment_type'] ?: 'custom_documents';
            if (!isset($vault[$type])) {
                $vault[$type] = ['label' => ucwords(str_replace('_', ' ', $type)), 'files' => []];
            }
            $vault[$type]['files'][] = $attachment;
        }
        return $vault;
    }

    public function upload(int $documentId, array $file, string $type, ?int $userId): int
    {
        $uploadDir = APP_ROOT . '/uploads/documents/' . $documentId;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $originalName = basename((string) ($file['name'] ?? 'document'));
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $fileName = uniqid($type . '_', true) . ($extension ? '.' . $extension : '');
        $targetPath = $uploadDir . '/' . $fileName;

        if (!move_uploaded_file((string) $file['tmp_name'], $targetPath)) {
            throw new \RuntimeException('Unable to upload document.');
        }

        return $this->attachments->add(
            $documentId,
            $fileName,
            $originalName,
            'uploads/documents/' . $documentId . '/' . $fileName,
            $file['type'] ?? null,
            isset($file['size']) ? (int) $file['size'] : null,
            $type,
            $userId
        );
    }

    public function findAttachment(int $id): ?array
    {
        return $this->attachments->findById($id);
    }

    public function updateStatus(int $documentId, int $status, int $userId, string $remarks = ''): bool
    {
        $document = $this->findDocument($documentId);
        $oldStatus = (int) ($document['status'] ?? 0);
        $stmt = $this->db->prepare('UPDATE document_headers SET status = :status, updated_by = :user_id, updated_at = NOW() WHERE id = :id');
        $updated = $stmt->execute(['id' => $documentId, 'status' => $status, 'user_id' => $userId]);
        $this->statusEngine->addHistory($documentId, $oldStatus, $status, $userId, $remarks);
        return $updated;
    }

    public function statusHistory(int $documentId): array
    {
        return $this->statusEngine->getHistory($documentId);
    }

    public static function documentTypes(): array
    {
        return [
            'quotation' => 'Quotation',
            'proforma_invoice' => 'Proforma Invoice',
            'commercial_invoice' => 'Commercial Invoice',
            'packing_list' => 'Packing List',
            'shipping_bill' => 'Shipping Bill',
            'bill_of_lading' => 'Bill of Lading',
            'certificate_of_origin' => 'Certificate of Origin',
            'phytosanitary' => 'Phytosanitary',
            'fumigation' => 'Fumigation',
            'insurance' => 'Insurance',
            'sgs' => 'SGS',
            'weight_certificate' => 'Weight Certificate',
            'inspection_certificate' => 'Inspection Certificate',
            'shipping_instructions' => 'Shipping Instructions',
            'courier_tracking' => 'Courier Tracking',
            'custom_documents' => 'Custom Documents',
        ];
    }
}