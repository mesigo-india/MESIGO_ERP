<?php
$quotation = $invoice ?? [];
$meta = $meta ?? [];
$items = $items ?? [];
$statuses = $statuses ?? [];
$revisions = $revisions ?? [];
$history = $history ?? [];
$totals = $meta['totals'] ?? [];
$statusClasses = [0 => 'bg-secondary', 1 => 'bg-warning', 2 => 'bg-success', 3 => 'bg-danger', 4 => 'bg-info', 5 => 'bg-primary', 6 => 'bg-dark', 7 => 'bg-dark'];

$dbInst = App\Core\Database::getInstance();
$company = $dbInst->query("SELECT * FROM company WHERE status = 1 ORDER BY id ASC LIMIT 1")->fetch(\PDO::FETCH_ASSOC) ?: [];

$engine = new \App\Core\DocumentStatusEngine($dbInst);
$downstream = $engine->getDownstreamDocument((int) $quotation['id']);
$isLocked = !$engine->canEdit((int) $quotation['id'], (int) $quotation['status']);
?>
<style>
@media print {
    .no-print { display: none !important; }
    .card { border: 0 !important; box-shadow: none !important; }
    .page-header { display: none !important; }
}
</style>

<!-- Action Header Control -->
<div class="page-header no-print d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-0 fw-bold"><i class="fas fa-file-invoice-dollar text-primary me-2"></i>Commercial Invoice <?= escapeHtml($quotation['document_number'] ?? '') ?> <span class="badge <?= $statusClasses[(int)($quotation['status'] ?? 0)] ?> fs-6 ms-2"><?= \App\Core\DocumentStatusEngine::getStatusLabel((int)($quotation['status'] ?? 0)) ?></span></h4>
        <span class="text-muted small">Manage document status, revision history, and printable formats.</span>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <a href="/commercial-invoices" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Back</a>
        
        <?php if (!$isLocked): ?>
            <a href="/commercial-invoices/<?= (int) ($quotation['id'] ?? 0) ?>/edit" class="btn btn-outline-primary btn-sm"><i class="fas fa-edit me-1"></i> Edit</a>
        <?php endif; ?>
        
        <!-- Print Selection Dropdown -->
        <div class="dropdown d-inline-block">
            <button class="btn btn-outline-dark btn-sm dropdown-toggle" type="button" id="printDrop" data-bs-toggle="dropdown">
                <i class="fas fa-print me-1"></i> Print / PDF
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-1">
                <li><a href="/commercial-invoices/<?= (int) ($quotation['id'] ?? 0) ?>/print?mode=plain" target="_blank" class="dropdown-item"><i class="fas fa-file-alt me-2 text-muted"></i> Plain Paper Mode</a></li>
                <?php if (!empty($company['letterhead_path'])): ?>
                    <li><a href="/commercial-invoices/<?= (int) ($quotation['id'] ?? 0) ?>/print?mode=letterhead&src=<?= urlencode('/uploads/' . $company['letterhead_path']) ?>" target="_blank" class="dropdown-item"><i class="fas fa-file-image me-2 text-muted"></i> Default Letterhead</a></li>
                <?php endif; ?>
                <?php if (!empty($company['letterhead_export_path'])): ?>
                    <li><a href="/commercial-invoices/<?= (int) ($quotation['id'] ?? 0) ?>/print?mode=letterhead&src=<?= urlencode('/uploads/' . $company['letterhead_export_path']) ?>" target="_blank" class="dropdown-item"><i class="fas fa-ship me-2 text-muted"></i> Export Letterhead</a></li>
                <?php endif; ?>
                <?php if (!empty($company['letterhead_domestic_path'])): ?>
                    <li><a href="/commercial-invoices/<?= (int) ($quotation['id'] ?? 0) ?>/print?mode=letterhead&src=<?= urlencode('/uploads/' . $company['letterhead_domestic_path']) ?>" target="_blank" class="dropdown-item"><i class="fas fa-building me-2 text-muted"></i> Domestic Letterhead</a></li>
                <?php endif; ?>
            </ul>
        </div>
        
        <form method="post" action="/commercial-invoices/<?= (int) ($quotation['id'] ?? 0) ?>/email" class="d-inline"><?= csrfToken() ?><button class="btn btn-outline-info btn-sm"><i class="fas fa-envelope me-1"></i> Email</button></form>
        
        <?php if ((int)$quotation['status'] === 2 && !$downstream): ?>
            <form method="post" action="/commercial-invoices/<?= (int) ($quotation['id'] ?? 0) ?>/convert" class="d-inline" onsubmit="return confirm('Convert Commercial Invoice to Packing List?');"><?= csrfToken() ?><button class="btn btn-success btn-sm"><i class="fas fa-exchange-alt me-1"></i> Convert to PL</button></form>
        <?php endif; ?>
        
        <?php if ((int)($quotation['status'] ?? 0) === 0 && !$downstream): ?>
            <form method="post" action="/commercial-invoices/<?= (int) ($quotation['id'] ?? 0) ?>/delete" class="d-inline" onsubmit="const reason = prompt('Please enter a reason for deletion (optional):'); if (reason === null) return false; this.querySelector('input[name=delete_reason]').value = reason; return confirm('Are you sure you want to delete this Commercial Invoice?');">
                <?= csrfToken() ?>
                <input type="hidden" name="delete_reason" value="">
                <button class="btn btn-danger btn-sm"><i class="fas fa-trash-alt me-1"></i> Delete</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<!-- Lock Warning Banner -->
<?php if ($isLocked): ?>
    <div class="alert alert-warning d-flex align-items-center mb-4 no-print" role="alert">
        <i class="fas fa-lock me-2 fs-5"></i>
        <div>
            <strong>Read Only Mode:</strong> 
            <?php if ($downstream): ?>
                This Commercial Invoice is locked because downstream <?= htmlspecialchars($downstream['type_name']) ?> (<a href="/<?= htmlspecialchars($downstream['type_code']) ?>s/<?= $downstream['id'] ?>"><?= htmlspecialchars($downstream['document_number']) ?></a>) has been created.
            <?php else: ?>
                This Commercial Invoice is locked under status <strong><?= \App\Core\DocumentStatusEngine::getStatusLabel((int)$quotation['status']) ?></strong>.
                <?php if ((int)$quotation['status'] === 2): ?>
                    Use the <strong>Save Revision</strong> tool below to create a new draft version.
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<div class="row mt-4 border-top pt-4">
    <div class="col-md-6 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5>Revision History</h5>
                <?php foreach ($revisions as $revision): ?>
                    <div class="small mb-1">Rev <?= (int) $revision['revision_number'] ?> - <?= escapeHtml($revision['created_at']) ?> - <?= escapeHtml($revision['revision_notes'] ?? '') ?></div>
                <?php endforeach; ?>
                
                <?php if ((int)$quotation['status'] === 2): ?>
                    <form method="post" action="/commercial-invoices/<?= (int) ($quotation['id'] ?? 0) ?>/revise" class="mt-2">
                        <?= csrfToken() ?>
                        <input type="text" name="revision_notes" class="form-control mb-2" placeholder="Revision notes" required>
                        <button class="btn btn-sm btn-primary">Save Revision</button>
                    </form>
                <?php else: ?>
                    <div class="small text-muted mt-2">Revisions can only be created from Approved documents.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5>Status Workflow</h5>
                <?php
                $currentStatus = (int)$quotation['status'];
                $allowedTransitions = [];
                foreach ($statuses as $sid => $sname) {
                    if ($sid == $currentStatus || \App\Core\DocumentStatusEngine::isValidTransition($currentStatus, (int)$sid)) {
                        if ($currentStatus === 2 && $sid === 6 && $downstream) {
                            continue;
                        }
                        $allowedTransitions[$sid] = $sname;
                    }
                }
                ?>
                <?php if (count($allowedTransitions) > 1): ?>
                    <form method="post" action="/commercial-invoices/<?= (int) ($quotation['id'] ?? 0) ?>/status" class="row g-2">
                        <?= csrfToken() ?>
                        <div class="col-md-5">
                            <select name="status" class="form-select">
                                <?php foreach ($allowedTransitions as $statusId => $statusName): ?>
                                    <option value="<?= (int) $statusId ?>" <?= $currentStatus === (int) $statusId ? 'selected' : '' ?>><?= escapeHtml($statusName) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <input type="text" name="remarks" class="form-control" placeholder="Status remarks">
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-primary w-100">Update</button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="alert alert-light py-2 text-muted small mb-2">No further status transitions are allowed.</div>
                <?php endif; ?>
                
                <?php foreach ($history as $entry): ?>
                    <div class="small text-muted mt-2"><?= escapeHtml($entry['created_at']) ?>: <?= escapeHtml($statuses[(int) $entry['new_status']] ?? 'Unknown') ?> <?= escapeHtml($entry['remarks'] ?? '') ?></div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>