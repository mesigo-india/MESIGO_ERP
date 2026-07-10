<?php
declare(strict_types=1);

namespace App\Core;

class ExportDocumentController extends Controller
{
    private ExportDocumentManager $documents;

    public function __construct()
    {
        parent::__construct();
        require_once APP_ROOT . '/classes/AttachmentManager.php';
        require_once APP_ROOT . '/classes/DocumentStatusEngine.php';
        require_once APP_ROOT . '/classes/ExportDocumentManager.php';
        $this->documents = new ExportDocumentManager(Database::getInstance());
    }

    public function index(): void
    {
        $this->requireLogin();
        $this->requirePermission('export_documents.view');
        $search = trim((string) ($_GET['search'] ?? ''));
        $status = trim((string) ($_GET['status'] ?? ''));
        $this->render('export_documents/index', [
            'title' => 'Export Documentation Center',
            'documents' => $this->documents->getOrders($search, $status),
            'search' => $search,
            'status' => $status,
        ]);
    }

    public function show(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('export_documents.view');
        $document = $this->findDocumentOrRedirect((int) $id);
        $this->render('export_documents/show', [
            'title' => 'Document Vault - ' . $document['document_number'],
            'document' => $document,
            'vault' => $this->documents->getVault((int) $id),
            'history' => $this->documents->statusHistory((int) $id),
            'documentTypes' => ExportDocumentManager::documentTypes(),
        ]);
    }

    public function upload(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('export_documents.upload');
        if (!$this->validateCsrf()) {
            Response::redirect('/export-documents/' . (int) $id, 'Invalid security token.');
        }
        $this->findDocumentOrRedirect((int) $id);
        $type = trim((string) ($_POST['attachment_type'] ?? 'custom_documents'));
        if (!isset($_FILES['document_file']) || (int) ($_FILES['document_file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            Response::redirect('/export-documents/' . (int) $id, 'Please select a valid file.');
        }
        $this->documents->upload((int) $id, $_FILES['document_file'], $type, $this->currentUserId());
        Response::redirect('/export-documents/' . (int) $id, 'Document uploaded successfully.');
    }

    public function download(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('export_documents.view');
        $attachment = $this->findAttachmentOrRedirect((int) $id);
        $path = APP_ROOT . '/' . $attachment['file_path'];
        if (!is_file($path)) {
            Response::redirect('/export-documents', 'File not found.');
        }
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($attachment['original_name']) . '"');
        readfile($path);
        exit;
    }

    public function preview(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('export_documents.view');
        $attachment = $this->findAttachmentOrRedirect((int) $id);
        $path = APP_ROOT . '/' . $attachment['file_path'];
        if (!is_file($path)) {
            Response::redirect('/export-documents', 'File not found.');
        }
        header('Content-Type: ' . ($attachment['file_type'] ?: 'application/octet-stream'));
        readfile($path);
        exit;
    }

    public function status(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('export_documents.update');
        if (!$this->validateCsrf()) {
            Response::redirect('/export-documents/' . (int) $id, 'Invalid security token.');
        }
        $this->findDocumentOrRedirect((int) $id);
        $this->documents->updateStatus((int) $id, (int) ($_POST['status'] ?? 0), (int) $this->currentUserId(), trim((string) ($_POST['remarks'] ?? '')));
        Response::redirect('/export-documents/' . (int) $id, 'Document status updated.');
    }

    private function findDocumentOrRedirect(int $id): array
    {
        $document = $this->documents->findDocument($id);
        if (!$document) {
            Response::redirect('/export-documents', 'Document not found.');
        }
        return $document;
    }

    private function findAttachmentOrRedirect(int $id): array
    {
        $attachment = $this->documents->findAttachment($id);
        if (!$attachment) {
            Response::redirect('/export-documents', 'Attachment not found.');
        }
        return $attachment;
    }

}