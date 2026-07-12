<?php
$shippingBill = $shippingBill ?? [];
$meta = $meta ?? [];
$items = $items ?? [];
$statuses = $statuses ?? [];
$revisions = $revisions ?? [];
$history = $history ?? [];
$statusClasses = [0 => 'bg-secondary', 1 => 'bg-warning', 2 => 'bg-success', 3 => 'bg-danger', 4 => 'bg-info', 5 => 'bg-primary', 6 => 'bg-dark', 7 => 'bg-dark'];

$dbInst = App\Core\Database::getInstance();
$engine = new \App\Core\DocumentStatusEngine($dbInst);
$downstream = $engine->getDownstreamDocument((int) $shippingBill['id']);
$isLocked = !$engine->canEdit((int) $shippingBill['id'], (int) $shippingBill['status']);
?>

<div class="page-header d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-0 fw-bold"><i class="fas fa-file-contract text-primary me-2"></i>Shipping Bill <?= escapeHtml($shippingBill['document_number'] ?? '') ?> <span class="badge <?= $statusClasses[(int)($shippingBill['status'] ?? 0)] ?> fs-6 ms-2"><?= \App\Core\DocumentStatusEngine::getStatusLabel((int)($shippingBill['status'] ?? 0)) ?></span></h4>
        <span class="text-muted small">Manage document status, revision history, and printable formats.</span>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <a href="/shipping-bills" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Back</a>
        
        <?php if (!$isLocked): ?>
            <a href="/shipping-bills/<?= (int) ($shippingBill['id'] ?? 0) ?>/edit" class="btn btn-outline-primary btn-sm"><i class="fas fa-edit me-1"></i> Edit</a>
        <?php endif; ?>
        
        <a href="/shipping-bills/<?= (int) ($shippingBill['id'] ?? 0) ?>/print" target="_blank" class="btn btn-outline-dark btn-sm"><i class="fas fa-print me-1"></i> Print</a>
        <form method="post" action="/shipping-bills/<?= (int) ($shippingBill['id'] ?? 0) ?>/email" class="d-inline"><?= csrfToken() ?><button class="btn btn-outline-info btn-sm"><i class="fas fa-envelope me-1"></i> Email Ready</button></form>
        
        <?php if ((int)$shippingBill['status'] === 2 && !$downstream): ?>
            <form method="post" action="/shipping-bills/<?= (int) ($shippingBill['id'] ?? 0) ?>/convert" class="d-inline" onsubmit="return confirm('Convert Shipping Bill to Bill of Lading?');"><?= csrfToken() ?><button class="btn btn-success btn-sm"><i class="fas fa-exchange-alt me-1"></i> Convert to BL</button></form>
            <form method="post" action="/shipping-bills/<?= (int) ($shippingBill['id'] ?? 0) ?>/convert-co" class="d-inline" onsubmit="return confirm('Convert Shipping Bill to Certificate of Origin?');"><?= csrfToken() ?><button class="btn btn-outline-success btn-sm"><i class="fas fa-certificate me-1"></i> Convert to COO</button></form>
            <form method="post" action="/shipping-bills/<?= (int) ($shippingBill['id'] ?? 0) ?>/convert-phyto" class="d-inline" onsubmit="return confirm('Convert Shipping Bill to Phytosanitary Certificate?');"><?= csrfToken() ?><button class="btn btn-outline-primary btn-sm"><i class="fas fa-leaf me-1"></i> Convert to Phyto</button></form>
        <?php endif; ?>
        
        <?php if ((int)($shippingBill['status'] ?? 0) === 0 && !$downstream): ?>
            <form method="post" action="/shipping-bills/<?= (int) ($shippingBill['id'] ?? 0) ?>/delete" class="d-inline" onsubmit="const reason = prompt('Please enter a reason for deletion (optional):'); if (reason === null) return false; this.querySelector('input[name=delete_reason]').value = reason; return confirm('Are you sure you want to delete this Shipping Bill?');">
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
                This Shipping Bill is locked because downstream <?= htmlspecialchars($downstream['type_name']) ?> (<a href="/<?= htmlspecialchars($downstream['type_code']) ?>s/<?= $downstream['id'] ?>"><?= htmlspecialchars($downstream['document_number']) ?></a>) has been created.
            <?php else: ?>
                This Shipping Bill is locked under status <strong><?= \App\Core\DocumentStatusEngine::getStatusLabel((int)$shippingBill['status']) ?></strong>.
                <?php if ((int)$shippingBill['status'] === 2): ?>
                    Use the <strong>Save Revision</strong> tool below to create a new draft version.
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <div class="text-center mb-4 border-bottom pb-3">
            <h5 class="fw-bold tracking-wider text-dark mb-0">SHIPPING BILL</h5>
            <div class="text-muted small">Official Statutory Export Declaration Details</div>
        </div>
        
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <strong>Shipping Bill No:</strong> <?= escapeHtml($meta['shipping_bill_no'] ?? ($shippingBill['document_number'] ?? '')) ?><br>
                <strong>Shipping Bill Date:</strong> <?= escapeHtml($meta['shipping_bill_date'] ?? ($shippingBill['document_date'] ?? '')) ?><br>
                <strong>Status:</strong> <span class="badge <?= escapeHtml($statusClasses[(int) ($shippingBill['status'] ?? 0)] ?? 'bg-secondary') ?>"><?= escapeHtml($statuses[(int) ($shippingBill['status'] ?? 0)] ?? 'Unknown') ?></span><br>
                <strong>Buyer:</strong> <?= escapeHtml($shippingBill['buyer_name'] ?? '') ?>
            </div>
            <div class="col-md-6">
                <strong>Port:</strong> <?= escapeHtml($meta['port'] ?? 'N/A') ?><br>
                <strong>CHA:</strong> <?= escapeHtml($meta['cha'] ?? 'N/A') ?><br>
                <strong>Custom House:</strong> <?= escapeHtml($meta['custom_house'] ?? 'N/A') ?><br>
                <strong>Container Details:</strong> <?= escapeHtml($meta['container_details'] ?? 'N/A') ?>
            </div>
        </div>
        
        <div class="row g-3 mb-4 border-top border-bottom py-2 bg-light">
            <div class="col-sm-3 col-6"><strong>LEO Code:</strong> <span class="fw-semibold text-dark"><?= escapeHtml($meta['leo'] ?? 'N/A') ?></span></div>
            <div class="col-sm-3 col-6"><strong>Drawback:</strong> <span class="fw-semibold text-dark"><?= escapeHtml($meta['drawback'] ?? 'N/A') ?></span></div>
            <div class="col-sm-3 col-6"><strong>Scheme Code:</strong> <span class="fw-semibold text-dark"><?= escapeHtml($meta['scheme'] ?? 'N/A') ?></span></div>
            <div class="col-sm-3 col-6"><strong>Exporter ID:</strong> <span class="fw-semibold text-dark"><?= escapeHtml($meta['exporter_details'] ?? 'N/A') ?></span></div>
        </div>
        
        <div class="table-responsive mb-4">
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th style="width:40px">#</th>
                        <th>Product</th>
                        <th>HS Code</th>
                        <th>Packaging Type</th>
                        <th class="text-end">Quantity</th>
                        <th class="text-end">Net Weight (KG)</th>
                        <th class="text-end">Gross Weight (KG)</th>
                        <th>Remarks</th>
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
                            <td class="text-muted small"><?= escapeHtml($item['item_remarks'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div>
            <strong class="text-dark small">Notes / Remarks:</strong>
            <p class="text-muted small mt-1"><?= nl2br(escapeHtml($shippingBill['remarks'] ?? 'No special notes.')) ?></p>
        </div>
    </div>
</div>

<div class="row mt-4 border-top pt-4">
    <div class="col-md-6 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5>Revision History</h5>
                <?php foreach ($revisions as $revision): ?>
                    <div class="small mb-1">Rev <?= (int) $revision['revision_number'] ?> - <?= escapeHtml($revision['created_at']) ?> - <?= escapeHtml($revision['revision_notes'] ?? '') ?></div>
                <?php endforeach; ?>
                
                <?php if ((int)$shippingBill['status'] === 2): ?>
                    <form method="post" action="/shipping-bills/<?= (int) ($shippingBill['id'] ?? 0) ?>/revise" class="mt-2">
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
                $currentStatus = (int)$shippingBill['status'];
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
                    <form method="post" action="/shipping-bills/<?= (int) ($shippingBill['id'] ?? 0) ?>/status" class="row g-2">
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