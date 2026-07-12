<?php
$certificate = $certificate ?? [];
$meta = $meta ?? [];
$items = $items ?? [];
$statuses = $statuses ?? [];
$revisions = $revisions ?? [];
$history = $history ?? [];
$statusClasses = [0 => 'bg-secondary', 1 => 'bg-warning', 2 => 'bg-success', 3 => 'bg-danger', 4 => 'bg-info', 5 => 'bg-primary', 6 => 'bg-dark', 7 => 'bg-dark'];

$dbInst = App\Core\Database::getInstance();
$engine = new \App\Core\DocumentStatusEngine($dbInst);
$downstream = $engine->getDownstreamDocument((int) $certificate['id']);
$isLocked = !$engine->canEdit((int) $certificate['id'], (int) $certificate['status']);
?>
<style>
.phyto-certificate-box {
    border: 3px double #2e7d32 !important; /* Sage green border */
    padding: 2.5rem !important;
    background-color: #fff;
}
.phyto-grid-header {
    border-bottom: 2px solid #2e7d32;
    padding-bottom: 1rem;
}
</style>

<!-- Action Header Control -->
<div class="page-header d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-0 fw-bold"><i class="fas fa-leaf text-success me-2"></i>Phytosanitary Certificate <?= escapeHtml($certificate['document_number'] ?? '') ?> <span class="badge <?= $statusClasses[(int)($certificate['status'] ?? 0)] ?> fs-6 ms-2"><?= \App\Core\DocumentStatusEngine::getStatusLabel((int)($certificate['status'] ?? 0)) ?></span></h4>
        <span class="text-muted small">Manage document status, revision history, and printable formats.</span>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <a href="/phytosanitary" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Back</a>
        
        <?php if (!$isLocked): ?>
            <a href="/phytosanitary/<?= (int) ($certificate['id'] ?? 0) ?>/edit" class="btn btn-outline-primary btn-sm"><i class="fas fa-edit me-1"></i> Edit</a>
        <?php endif; ?>
        
        <a href="/phytosanitary/<?= (int) ($certificate['id'] ?? 0) ?>/print" target="_blank" class="btn btn-outline-dark btn-sm"><i class="fas fa-print me-1"></i> Print / PDF</a>
        
        <?php if ((int)($certificate['status'] ?? 0) === 0 && !$downstream): ?>
            <form method="post" action="/phytosanitary/<?= (int) ($certificate['id'] ?? 0) ?>/delete" class="d-inline" onsubmit="const reason = prompt('Please enter a reason for deletion (optional):'); if (reason === null) return false; this.querySelector('input[name=delete_reason]').value = reason; return confirm('Are you sure you want to delete this Certificate?');">
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
                This Phytosanitary Certificate is locked because downstream <?= htmlspecialchars($downstream['type_name']) ?> (<a href="/<?= htmlspecialchars($downstream['type_code']) ?>s/<?= $downstream['id'] ?>"><?= htmlspecialchars($downstream['document_number']) ?></a>) has been created.
            <?php else: ?>
                This Phytosanitary Certificate is locked under status <strong><?= \App\Core\DocumentStatusEngine::getStatusLabel((int)$certificate['status']) ?></strong>.
                <?php if ((int)$certificate['status'] === 2): ?>
                    Use the <strong>Save Revision</strong> tool below to create a new draft version.
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<div class="card phyto-certificate-box shadow-sm border-0">
    <div class="card-body p-0">
        <div class="text-center mb-4 phyto-grid-header">
            <h3 class="fw-bold text-success mb-1" style="letter-spacing: 3px;">PHYTOSANITARY CERTIFICATE</h3>
            <span class="text-muted small text-uppercase fw-semibold">Government of India — Department of Agriculture, Cooperation & Farmers Welfare</span>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 border-end">
                <div class="mb-3">
                    <span class="d-block small text-muted text-uppercase fw-bold">1. Name & Address of Exporter:</span>
                    <strong class="d-block text-dark mt-1" style="white-space: pre-wrap; font-size: 0.95rem;"><?= escapeHtml($company['company_name'] ?? '') ?><br><?= escapeHtml($company['address'] ?? '') ?></strong>
                </div>
                <div>
                    <span class="d-block small text-muted text-uppercase fw-bold">2. Declared Name and Address of Consignee:</span>
                    <strong class="d-block text-dark mt-1" style="white-space: pre-wrap; font-size: 0.95rem;"><?= escapeHtml($meta['consignee'] ?? '') ?></strong>
                </div>
            </div>
            <div class="col-6 ps-4">
                <div class="mb-2">
                    <span class="text-muted small text-uppercase fw-bold">Certificate Number:</span>
                    <strong class="text-dark d-block"><?= escapeHtml($certificate['document_number'] ?? '') ?></strong>
                </div>
                <div class="mb-2">
                    <span class="text-muted small text-uppercase fw-bold">Date of Inspection:</span>
                    <strong class="text-dark d-block"><?= escapeHtml($certificate['document_date'] ?? '') ?></strong>
                </div>
                <div class="mb-2">
                    <span class="text-muted small text-uppercase fw-bold">Place of Origin:</span>
                    <strong class="text-success d-block fw-bold" style="font-size: 1.1rem;"><i class="fas fa-flag text-danger me-1"></i> <?= escapeHtml($meta['place_of_origin'] ?? 'India') ?></strong>
                </div>
                <div class="mb-2">
                    <span class="text-muted small text-uppercase fw-bold">Declared Port of Entry:</span>
                    <strong class="text-dark d-block"><?= escapeHtml($meta['port_of_entry'] ?? 'N/A') ?></strong>
                </div>
                <div>
                    <span class="text-muted small text-uppercase fw-bold">Declared Means of Conveyance:</span>
                    <strong class="text-dark d-block"><?= escapeHtml($meta['means_of_conveyance'] ?? 'N/A') ?></strong>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4 border-top border-bottom py-2 bg-light">
            <div class="col-md-6"><span class="d-block text-muted small fw-bold">Botanical Name of Plants:</span><span class="fw-semibold text-success small"><em><?= escapeHtml($meta['botanical_name'] ?? 'N/A') ?></em></span></div>
            <div class="col-md-6"><span class="d-block text-muted small fw-bold">Total Declared Quantity:</span><span class="fw-semibold text-dark small"><?= escapeHtml($meta['declared_quantity'] ?? 'N/A') ?></span></div>
        </div>

        <!-- Disinfestation Treatment Details Panel -->
        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="p-3 border border-success rounded bg-light">
                    <strong class="text-success d-block small text-uppercase mb-2 fw-bold"><i class="fas fa-flask me-1"></i> Disinfestation and/or Disinfection Treatment Details</strong>
                    <div class="row g-3 small text-dark">
                        <div class="col-sm-3 col-6"><span class="text-muted d-block">Date of Treatment:</span><strong class="d-block"><?= escapeHtml($meta['treatment_date'] ?? 'N/A') ?></strong></div>
                        <div class="col-sm-3 col-6"><span class="text-muted d-block">Treatment Chemical:</span><strong class="d-block"><?= escapeHtml($meta['treatment_chemical'] ?? 'N/A') ?></strong></div>
                        <div class="col-sm-3 col-6"><span class="text-muted d-block">Duration & Concentration:</span><strong class="d-block"><?= escapeHtml($meta['treatment_duration'] ?? 'N/A') ?></strong></div>
                        <div class="col-sm-3 col-6"><span class="text-muted d-block">Temperature:</span><strong class="d-block"><?= escapeHtml($meta['treatment_temperature'] ?? 'N/A') ?></strong></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items table -->
        <div class="table-responsive mb-4 border-top border-bottom py-3">
            <span class="d-block small text-muted text-uppercase fw-bold mb-3">3. Description of Packages & Goods:</span>
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width:40px">#</th>
                        <th>Product Details</th>
                        <th>HS Code</th>
                        <th class="text-end">No. of Packages</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $index => $item): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td class="fw-semibold text-dark"><?= escapeHtml($item['product_name'] ?? '') ?></td>
                            <td><?= escapeHtml($item['hsn_code'] ?? '') ?></td>
                            <td class="text-end fw-bold"><?= number_format((float) ($item['quantity'] ?? 0), 0) ?> Packages</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Declaration & Stamp Box -->
        <div class="row g-3 mb-4">
            <div class="col-7">
                <div class="p-3 border rounded bg-light">
                    <strong class="text-success small d-block text-uppercase mb-2 fw-bold">4. Plant Protection Organization Declaration</strong>
                    <p class="small text-muted mb-0" style="font-style: italic; line-height: 1.4;">This is to certify that the plants, plant products or other regulated articles described herein have been inspected and/or tested according to appropriate official procedures and are considered to be free from the quarantine pests specified by the importing contracting party.</p>
                </div>
                <?php if (!empty($certificate['remarks'])): ?>
                    <div class="mt-3">
                        <strong>Remarks:</strong>
                        <div class="small text-muted"><?= nl2br(escapeHtml($certificate['remarks'] ?? '')) ?></div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-5 text-end">
                <div class="p-3 border rounded bg-light text-start d-inline-block" style="min-width: 280px; min-height: 180px;">
                    <span class="d-block small text-muted text-uppercase fw-bold mb-2">5. Signature of Authorized Officer</span>
                    <div style="min-height: 90px;"></div>
                    <span class="d-block border-top pt-1 small text-center text-muted">Plant Protection Quarantine Officer</span>
                </div>
            </div>
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
                
                <?php if ((int)$certificate['status'] === 2): ?>
                    <form method="post" action="/phytosanitary/<?= (int) ($certificate['id'] ?? 0) ?>/revise" class="mt-2">
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
                $currentStatus = (int)$certificate['status'];
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
                    <form method="post" action="/phytosanitary/<?= (int) ($certificate['id'] ?? 0) ?>/status" class="row g-2">
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
