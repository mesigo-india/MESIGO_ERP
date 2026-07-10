<?php
$quotation = $quotation ?? [];
$items = $items ?? [];
$meta = $meta ?? [];
$statuses = $statuses ?? [];
$revisions = $revisions ?? [];
$history = $history ?? [];
$statusClasses = [0 => 'bg-secondary', 1 => 'bg-warning', 2 => 'bg-success', 3 => 'bg-danger', 4 => 'bg-info', 5 => 'bg-primary', 6 => 'bg-dark'];
$totals = $meta['totals'] ?? [];

$dbInst = App\Core\Database::getInstance();
$company = $dbInst->query("SELECT * FROM company WHERE status = 1 ORDER BY id ASC LIMIT 1")->fetch(\PDO::FETCH_ASSOC) ?: [];
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
        <h4 class="mb-0 fw-bold"><i class="fas fa-file-invoice-dollar text-primary me-2"></i>Quotation <?= escapeHtml($quotation['document_number']) ?></h4>
        <span class="text-muted small">Manage document status, revision history, and printable formats.</span>
    </div>
    <div class="d-flex gap-2">
        <a href="/quotations" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Back</a>
        <a href="/quotations/<?= (int) $quotation['id'] ?>/edit" class="btn btn-outline-primary btn-sm"><i class="fas fa-edit me-1"></i> Edit</a>
        
        <!-- Print Selection Dropdown -->
        <div class="dropdown d-inline-block">
            <button class="btn btn-outline-dark btn-sm dropdown-toggle" type="button" id="printDrop" data-bs-toggle="dropdown">
                <i class="fas fa-print me-1"></i> Print / PDF
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-1">
                <li><button onclick="triggerPrint('plain')" class="dropdown-item"><i class="fas fa-file-alt me-2 text-muted"></i> Plain Paper Mode</button></li>
                <?php if (!empty($company['letterhead_path'])): ?>
                    <li><button onclick="triggerPrint('letterhead', '/uploads/<?= htmlspecialchars($company['letterhead_path']) ?>')" class="dropdown-item"><i class="fas fa-file-image me-2 text-muted"></i> Default Letterhead</button></li>
                <?php endif; ?>
                <?php if (!empty($company['letterhead_export_path'])): ?>
                    <li><button onclick="triggerPrint('letterhead', '/uploads/<?= htmlspecialchars($company['letterhead_export_path']) ?>')" class="dropdown-item"><i class="fas fa-ship me-2 text-muted"></i> Export Letterhead</button></li>
                <?php endif; ?>
                <?php if (!empty($company['letterhead_domestic_path'])): ?>
                    <li><button onclick="triggerPrint('letterhead', '/uploads/<?= htmlspecialchars($company['letterhead_domestic_path']) ?>')" class="dropdown-item"><i class="fas fa-building me-2 text-muted"></i> Domestic Letterhead</button></li>
                <?php endif; ?>
            </ul>
        </div>
        
        <form method="post" action="/quotations/<?= (int) $quotation['id'] ?>/email" class="d-inline"><?= csrfToken() ?><button class="btn btn-outline-info btn-sm"><i class="fas fa-envelope me-1"></i> Email</button></form>
        <form method="post" action="/quotations/<?= (int) $quotation['id'] ?>/convert" class="d-inline" onsubmit="return confirm('Convert quotation to Proforma Invoice?');"><?= csrfToken() ?><button class="btn btn-success btn-sm"><i class="fas fa-exchange-alt me-1"></i> Convert to PI</button></form>
        <?php if (\App\Core\Session::get('role_name') === 'admin'): ?>
            <form method="post" action="/quotations/<?= (int) $quotation['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Are you sure you want to permanently delete this Quotation?');">
                <?= csrfToken() ?>
                <button class="btn btn-danger btn-sm"><i class="fas fa-trash-alt me-1"></i> Delete</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<!-- Print Container with Overlay letterheads -->
<div class="letterhead-print-container" id="printContainer">
    
    <!-- Print Header -->
    <?php require_once APP_ROOT . '/includes/document_print_header.php'; ?>

    <div class="letterhead-content-body card border-0">
        <div class="card-body p-0">
            <div class="text-center mb-4">
                <h4 class="fw-bold tracking-wider text-dark mb-0">EXPORT QUOTATION</h4>
                <div class="text-muted small">Provisional Agreement Layout</div>
            </div>
            
            <div class="row g-3 mb-4">
                <div class="col-6 col-sm-6">
                    <span class="d-block small text-muted text-uppercase">Quotation Summary</span>
                    <strong class="d-block text-dark"><?= escapeHtml($quotation['document_number']) ?></strong>
                    <span class="d-block small text-muted">Date: <?= escapeHtml($quotation['document_date']) ?></span>
                    <span class="d-block small text-muted">Revision: Rev <?= (int) ($meta['revision'] ?? 0) ?></span>
                    <span class="d-block small text-muted">Status: <span class="badge <?= escapeHtml($statusClasses[(int) $quotation['status']] ?? 'bg-secondary') ?>"><?= escapeHtml($statuses[(int) $quotation['status']] ?? 'Unknown') ?></span></span>
                </div>
                <div class="col-6 col-sm-6 text-sm-end">
                    <span class="d-block small text-muted text-uppercase">Buyer Details</span>
                    <strong class="d-block text-dark"><?= escapeHtml($quotation['buyer_name'] ?? '') ?></strong>
                    <span class="d-block small text-muted">Valid Until: <?= escapeHtml($meta['valid_until'] ?? '') ?></span>
                    <span class="d-block small text-muted">Currency: <?= escapeHtml($quotation['currency_code'] ?? '') ?></span>
                    <span class="d-block small text-muted">Incoterm: <?= escapeHtml($quotation['incoterm_code'] ?? '') ?></span>
                </div>
            </div>
            
            <div class="row g-3 mb-4 border-top border-bottom py-2 bg-light">
                <div class="col-sm-3 col-6"><span class="d-block text-muted small">Loading Port</span><span class="fw-semibold text-dark small"><?= escapeHtml($quotation['loading_port_name'] ?? 'N/A') ?></span></div>
                <div class="col-sm-3 col-6"><span class="d-block text-muted small">Delivery Port</span><span class="fw-semibold text-dark small"><?= escapeHtml($quotation['delivery_port_name'] ?? 'N/A') ?></span></div>
                <div class="col-sm-3 col-6"><span class="d-block text-muted small">Payment Term</span><span class="fw-semibold text-dark small"><?= escapeHtml($quotation['payment_term_name'] ?? 'N/A') ?></span></div>
                <div class="col-sm-3 col-6"><span class="d-block text-muted small">Shipment Term</span><span class="fw-semibold text-dark small"><?= escapeHtml($meta['shipment_term'] ?? ($quotation['shipment_type'] ?? 'N/A')) ?></span></div>
            </div>

            <!-- Items table -->
            <div class="table-responsive mb-4">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th style="width:40px">#</th>
                            <th>Product</th>
                            <th>HS Code</th>
                            <th>Packaging</th>
                            <th class="text-end">Qty</th>
                            <th>Unit</th>
                            <th class="text-end">Rate</th>
                            <th class="text-end">Discount</th>
                            <th class="text-end">GST</th>
                            <th class="text-end">Net Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $index => $item): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td class="fw-semibold text-dark"><?= escapeHtml($item['product_name'] ?? '') ?></td>
                                <td><?= escapeHtml($item['hsn_code'] ?? '') ?></td>
                                <td><?= escapeHtml($item['packing_type_name'] ?? '') ?></td>
                                <td class="text-end"><?= number_format((float) $item['quantity'], 3) ?></td>
                                <td><?= escapeHtml($item['unit_code'] ?? '') ?></td>
                                <td class="text-end"><?= number_format((float) $item['rate'], 4) ?></td>
                                <td class="text-end"><?= number_format((float) $item['discount_amount'], 2) ?></td>
                                <td class="text-end"><?= number_format((float) $item['tax_amount'], 2) ?></td>
                                <td class="text-end fw-semibold text-dark"><?= number_format((float) $item['net_amount'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-7">
                    <strong class="text-dark small">Notes / Remarks:</strong>
                    <p class="text-muted small mt-1"><?= nl2br(escapeHtml($quotation['remarks'] ?? 'No special instructions.')) ?></p>
                </div>
                <div class="col-md-5">
                    <table class="table table-sm text-nowrap">
                        <tr><th>Subtotal</th><td class="text-end"><?= number_format((float) ($totals['subtotal'] ?? 0), 2) ?></td></tr>
                        <tr><th>Discount</th><td class="text-end"><?= number_format((float) ($totals['discount'] ?? 0), 2) ?></td></tr>
                        <tr><th>GST</th><td class="text-end"><?= number_format((float) ($totals['gst'] ?? 0), 2) ?></td></tr>
                        <tr><th>Freight</th><td class="text-end"><?= number_format((float) ($totals['freight'] ?? 0), 2) ?></td></tr>
                        <tr><th>Insurance</th><td class="text-end"><?= number_format((float) ($totals['insurance'] ?? 0), 2) ?></td></tr>
                        <tr><th>Other Charges</th><td class="text-end"><?= number_format((float) ($totals['other'] ?? 0), 2) ?></td></tr>
                        <tr class="table-primary border-top border-2"><th>Grand Total</th><td class="text-end fw-bold"><strong><?= number_format((float) ($totals['grand'] ?? 0), 2) ?></strong></td></tr>
                    </table>
                </div>
            </div>
            
            <!-- Containers Recomendation -->
            <?php if (!empty($quotation['estimated_containers_json'])): ?>
                <?php $containers = json_decode($quotation['estimated_containers_json'], true); ?>
                <?php if (!empty($containers)): ?>
                    <div class="mt-4 p-3 bg-light rounded border border-warning">
                        <h6 class="text-warning fw-bold"><i class="fa fa-ship"></i> Container Loading Recommendations</h6>
                        <div class="row g-3 mt-1">
                            <?php foreach ($containers as $c): ?>
                                <div class="col-md-4">
                                    <div class="card shadow-sm border-0 p-3 bg-white">
                                        <span class="d-block small text-muted">Container Allocation</span>
                                        <strong class="d-block text-dark"><?= escapeHtml($c['container_type']) ?></strong>
                                        <span class="d-block small">Qty: <span class="badge bg-primary"><?= (int) $c['container_count'] ?></span></span>
                                        <span class="d-block small">Util: <?= number_format($c['utilization_percent'], 1) ?>%</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Print Footer -->
    <?php require_once APP_ROOT . '/includes/document_print_footer.php'; ?>
</div>

<div class="row mt-4 no-print border-top pt-4">
    <div class="col-md-6 mb-3">
        <div class="card border-0 shadow-sm"><div class="card-body"><h5>Revision History</h5><?php foreach ($revisions as $revision): ?><div class="small mb-1">Rev <?= (int) $revision['revision_number'] ?> - <?= escapeHtml($revision['created_at']) ?> - <?= escapeHtml($revision['revision_notes'] ?? '') ?></div><?php endforeach; ?><form method="post" action="/quotations/<?= (int) $quotation['id'] ?>/revise" class="mt-2"><?= csrfToken() ?><input type="text" name="revision_notes" class="form-control mb-2" placeholder="Revision notes"><button class="btn btn-sm btn-primary">Save Revision</button></form></div></div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="card border-0 shadow-sm"><div class="card-body"><h5>Status Workflow</h5><form method="post" action="/quotations/<?= (int) $quotation['id'] ?>/status" class="row g-2"><?= csrfToken() ?><div class="col-md-5"><select name="status" class="form-select"><?php foreach ($statuses as $statusId => $statusName): ?><option value="<?= (int) $statusId ?>" <?= (int) $quotation['status'] === (int) $statusId ? 'selected' : '' ?>><?= escapeHtml($statusName) ?></option><?php endforeach; ?></select></div><div class="col-md-5"><input type="text" name="remarks" class="form-control" placeholder="Status remarks"></div><div class="col-md-2"><button class="btn btn-primary">Update</button></div></form><?php foreach ($history as $entry): ?><div class="small text-muted mt-2"><?= escapeHtml($entry['created_at']) ?>: <?= escapeHtml($statuses[(int) $entry['new_status']] ?? 'Unknown') ?> <?= escapeHtml($entry['remarks'] ?? '') ?></div><?php endforeach; ?></div></div>
    </div>
</div>