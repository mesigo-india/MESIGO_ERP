<?php
$bill = $bill ?? [];
$meta = $meta ?? [];
$items = $items ?? [];
$statuses = $statuses ?? [];
$revisions = $revisions ?? [];
$history = $history ?? [];
$statusClasses = [0 => 'bg-secondary', 1 => 'bg-warning', 2 => 'bg-success', 3 => 'bg-danger', 4 => 'bg-info', 5 => 'bg-primary', 6 => 'bg-dark', 7 => 'bg-dark'];
$isDraft = (int)($bill['status'] ?? 0) === 0;

$dbInst = App\Core\Database::getInstance();
$engine = new \App\Core\DocumentStatusEngine($dbInst);
$downstream = $engine->getDownstreamDocument((int) $bill['id']);
$isLocked = !$engine->canEdit((int) $bill['id'], (int) $bill['status']);
?>
<style>
.watermark-container {
    position: relative;
    overflow: hidden;
}
<?php if ($isDraft): ?>
.watermark-container::after {
    content: "DRAFT ONLY - NOT FOR CARRIAGE";
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) rotate(-30deg);
    font-size: 3.5rem;
    font-weight: 900;
    color: rgba(220, 53, 69, 0.12);
    white-space: nowrap;
    pointer-events: none;
    z-index: 1000;
    letter-spacing: 2px;
}
<?php endif; ?>
</style>

<!-- Action Header Control -->
<div class="page-header d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-0 fw-bold"><i class="fas fa-ship text-primary me-2"></i>Bill of Lading <?= escapeHtml($bill['document_number'] ?? '') ?> <span class="badge <?= $statusClasses[(int)($bill['status'] ?? 0)] ?> fs-6 ms-2"><?= \App\Core\DocumentStatusEngine::getStatusLabel((int)($bill['status'] ?? 0)) ?></span></h4>
        <span class="text-muted small">Manage document status, revision history, and printable formats.</span>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <a href="/bill-of-ladings" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Back</a>
        
        <?php if (!$isLocked): ?>
            <a href="/bill-of-ladings/<?= (int) ($bill['id'] ?? 0) ?>/edit" class="btn btn-outline-primary btn-sm"><i class="fas fa-edit me-1"></i> Edit</a>
        <?php endif; ?>
        
        <a href="/bill-of-ladings/<?= (int) ($bill['id'] ?? 0) ?>/print" target="_blank" class="btn btn-outline-dark btn-sm"><i class="fas fa-print me-1"></i> Print / PDF</a>
        
        <?php if ((int)($bill['status'] ?? 0) === 0 && !$downstream): ?>
            <form method="post" action="/bill-of-ladings/<?= (int) ($bill['id'] ?? 0) ?>/delete" class="d-inline" onsubmit="const reason = prompt('Please enter a reason for deletion (optional):'); if (reason === null) return false; this.querySelector('input[name=delete_reason]').value = reason; return confirm('Are you sure you want to delete this Bill of Lading?');">
                <?= csrfToken() ?>
                <input type="hidden" name="delete_reason" value="">
                <button class="btn btn-danger btn-sm"><i class="fas fa-trash-alt me-1"></i> Delete</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<!-- Lock Warning Banner -->
<?php if ($isLocked): ?>
    <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
        <i class="fas fa-lock me-2 fs-5"></i>
        <div>
            <strong>Read Only Mode:</strong> 
            <?php if ($downstream): ?>
                This Bill of Lading is locked because downstream <?= htmlspecialchars($downstream['type_name']) ?> (<a href="/<?= htmlspecialchars($downstream['type_code']) ?>s/<?= $downstream['id'] ?>"><?= htmlspecialchars($downstream['document_number']) ?></a>) has been created.
            <?php else: ?>
                This Bill of Lading is locked under status <strong><?= \App\Core\DocumentStatusEngine::getStatusLabel((int)$bill['status']) ?></strong>.
                <?php if ((int)$bill['status'] === 2): ?>
                    Use the <strong>Save Revision</strong> tool below to create a new draft version.
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<div class="card watermark-container border-0 shadow-sm">
    <div class="card-body p-4">
        <div class="text-center mb-4">
            <h4 class="fw-bold tracking-wider text-dark mb-0">BILL OF LADING</h4>
            <div class="text-muted small">Liner Shipping Document (Carrier Cargo Contract)</div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6">
                <span class="d-block small text-muted text-uppercase">B/L Details</span>
                <strong class="d-block text-dark"><?= escapeHtml($meta['bl_number'] ?? ($bill['document_number'] ?? '')) ?></strong>
                <span class="d-block small text-muted">Status: <span class="badge <?= escapeHtml($statusClasses[(int) ($bill['status'] ?? 0)] ?? 'bg-secondary') ?>"><?= escapeHtml($statuses[(int) ($bill['status'] ?? 0)] ?? 'Unknown') ?></span></span>
                <span class="d-block small text-muted">Master B/L: <?= escapeHtml($meta['master_bl'] ?? 'N/A') ?></span>
                <span class="d-block small text-muted">House B/L: <?= escapeHtml($meta['house_bl'] ?? 'N/A') ?></span>
            </div>
            <div class="col-6 text-end">
                <span class="d-block small text-muted text-uppercase">Carrier / Liner Agency</span>
                <strong class="d-block text-dark"><?= escapeHtml($meta['carrier'] ?? 'N/A') ?></strong>
                <span class="d-block small text-muted">Vessel: <?= escapeHtml($meta['vessel'] ?? 'N/A') ?></span>
                <span class="d-block small text-muted">Voyage: <?= escapeHtml($meta['voyage'] ?? 'N/A') ?></span>
            </div>
        </div>

        <!-- Exporter / Consignee Address Section -->
        <div class="row g-3 mb-4 border-top pt-3">
            <div class="col-4">
                <div class="p-2 border rounded bg-light" style="min-height: 100px;">
                    <strong class="d-block text-dark small text-uppercase mb-1">Shipper (Exporter)</strong>
                    <span class="small text-muted d-block" style="white-space: pre-wrap;"><?= escapeHtml($meta['exporter_details'] ?? '') ?></span>
                </div>
            </div>
            <div class="col-4">
                <div class="p-2 border rounded bg-light" style="min-height: 100px;">
                    <strong class="d-block text-dark small text-uppercase mb-1">Consignee (Delivery)</strong>
                    <span class="small text-muted d-block" style="white-space: pre-wrap;"><?= escapeHtml($meta['consignee'] ?? '') ?></span>
                </div>
            </div>
            <div class="col-4">
                <div class="p-2 border rounded bg-light" style="min-height: 100px;">
                    <strong class="d-block text-dark small text-uppercase mb-1">Notify Party</strong>
                    <span class="small text-muted d-block" style="white-space: pre-wrap;"><?= escapeHtml($meta['notify_party'] ?? '') ?></span>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4 border-top border-bottom py-2 bg-light">
            <div class="col-md-3 col-6"><span class="d-block text-muted small">Port of Loading</span><span class="fw-semibold text-dark small"><?= escapeHtml($meta['pol'] ?? ($bill['loading_port_name'] ?? 'N/A')) ?></span></div>
            <div class="col-md-3 col-6"><span class="d-block text-muted small">Port of Discharge</span><span class="fw-semibold text-dark small"><?= escapeHtml($meta['pod'] ?? ($bill['delivery_port_name'] ?? 'N/A')) ?></span></div>
            <div class="col-md-3 col-6"><span class="d-block text-muted small">Freight Status</span><span class="fw-semibold text-dark small"><?= escapeHtml($meta['freight'] ?? 'Prepaid') ?></span></div>
            <div class="col-md-3 col-6"><span class="d-block text-muted small">Estimated ETD / ETA</span><span class="fw-semibold text-dark small"><?= escapeHtml($meta['etd'] ?? 'N/A') ?> / <?= escapeHtml($meta['eta'] ?? 'N/A') ?></span></div>
        </div>

        <!-- Items table -->
        <div class="table-responsive mb-4">
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th style="width:40px">#</th>
                        <th>Product Description</th>
                        <th>HS Code</th>
                        <th>Package Type</th>
                        <th class="text-end">No of Bags/Cartons</th>
                        <th class="text-end">Net Weight (KG)</th>
                        <th class="text-end">Gross Weight (KG)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $index => $item): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td class="fw-semibold text-dark"><?= escapeHtml($item['product_name'] ?? '') ?></td>
                            <td><?= escapeHtml($item['hsn_code'] ?? '') ?></td>
                            <td><?= escapeHtml($item['packing_type_name'] ?? '') ?></td>
                            <td class="text-end"><?= number_format((float) ($item['quantity'] ?? 0), 3) ?></td>
                            <td class="text-end"><?= number_format((float) ($item['net_weight'] ?? 0), 3) ?></td>
                            <td class="text-end"><?= number_format((float) ($item['gross_weight'] ?? 0), 3) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="row mb-4 border-top pt-3">
            <div class="col-md-6 mb-3">
                <span class="d-block text-muted small">Container Details:</span>
                <strong class="d-block text-dark"><?= escapeHtml($meta['container'] ?? 'N/A') ?></strong>
                <span class="d-block text-muted small mt-2">Seal Number:</span>
                <strong class="d-block text-dark"><?= escapeHtml($meta['seal'] ?? 'N/A') ?></strong>
            </div>
            <div class="col-md-6 text-end">
                <div class="p-3 border rounded bg-light d-inline-block text-start" style="min-width: 250px;">
                    <span class="d-block small text-muted text-uppercase mb-2">Carrier Issuing Office / Agent</span>
                    <div style="min-height: 60px;"></div>
                    <span class="d-block border-top pt-1 small text-center text-muted">Authorized Signature & Stamp</span>
                </div>
            </div>
        </div>

        <div>
            <strong>Remarks:</strong><br>
            <div class="small text-muted"><?= nl2br(escapeHtml($bill['remarks'] ?? '')) ?></div>
        </div>
    </div>
</div>

<div class="row mt-4 no-print border-top pt-4">
    <div class="col-md-6 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5>Revision History</h5>
                <?php foreach ($revisions as $revision): ?>
                    <div class="small mb-1">Rev <?= (int) $revision['revision_number'] ?> - <?= escapeHtml($revision['created_at']) ?> - <?= escapeHtml($revision['revision_notes'] ?? '') ?></div>
                <?php endforeach; ?>
                
                <?php if ((int)$bill['status'] === 2): ?>
                    <form method="post" action="/bill-of-ladings/<?= (int) ($bill['id'] ?? 0) ?>/revise" class="mt-2">
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
                $currentStatus = (int)$bill['status'];
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
                    <form method="post" action="/bill-of-ladings/<?= (int) ($bill['id'] ?? 0) ?>/status" class="row g-2">
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