<?php
$quotation = $invoice ?? [];
$meta = $meta ?? [];
$items = $items ?? [];
$statuses = $statuses ?? [];
$revisions = $revisions ?? [];
$history = $history ?? [];
$totals = $meta['totals'] ?? [];
$statusClasses = [0 => 'bg-secondary', 2 => 'bg-success', 4 => 'bg-info', 5 => 'bg-primary', 6 => 'bg-danger'];
?>
<style>
@media print {
    .no-print { display: none !important; }
    .card { border: 0; }
    .page-header { display: none; }
}
</style>

<div class="page-header no-print d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 text-primary font-weight-bold mb-0"><?= escapeHtml($quotation['document_number'] ?? '') ?></h1>
    <div class="btn-group">
        <a href="/commercial-invoices" class="btn btn-outline-secondary">Back to List</a>
        <a href="/commercial-invoices/<?= (int) ($quotation['id'] ?? 0) ?>/edit" class="btn btn-outline-primary">Edit Invoice</a>
        <button onclick="window.print()" class="btn btn-outline-dark">Print Document</button>
        <form method="post" action="/commercial-invoices/<?= (int) ($quotation['id'] ?? 0) ?>/email" class="d-inline">
            <?= csrfToken() ?>
            <button class="btn btn-outline-info">Email Client</button>
        </form>
        <form method="post" action="/commercial-invoices/<?= (int) ($quotation['id'] ?? 0) ?>/convert" class="d-inline" onsubmit="return confirm('Convert Commercial Invoice to Packing List?');">
            <?= csrfToken() ?>
            <button class="btn btn-success">Convert to PL</button>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-4">
        <div class="text-center mb-4">
            <h2 class="font-weight-bold text-dark">COMMERCIAL INVOICE</h2>
            <div class="text-muted small">PDF Ready · Email Ready · Print Ready</div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <strong>CI Number:</strong> <?= escapeHtml($quotation['document_number'] ?? '') ?><br>
                <strong>Revision Level:</strong> <?= (int) ($meta['revision'] ?? 0) ?><br>
                <strong>Date of Issue:</strong> <?= escapeHtml($quotation['document_date'] ?? '') ?><br>
                <strong>Status:</strong> <span class="badge <?= escapeHtml($statusClasses[(int) ($quotation['status'] ?? 0)] ?? 'bg-secondary') ?>"><?= escapeHtml($statuses[(int) ($quotation['status'] ?? 0)] ?? 'Unknown') ?></span>
            </div>
            <div class="col-md-6 mb-3">
                <strong>Buyer:</strong> <?= escapeHtml($quotation['buyer_name'] ?? '') ?><br>
                <strong>Currency:</strong> <?= escapeHtml($quotation['currency_code'] ?? '') ?><br>
                <strong>Validity Deadline:</strong> <?= escapeHtml($meta['valid_until'] ?? '') ?><br>
                <strong>Incoterm:</strong> <?= escapeHtml($quotation['incoterm_code'] ?? '') ?>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <strong>Loading Port:</strong> <?= escapeHtml($quotation['loading_port_name'] ?? '') ?>
            </div>
            <div class="col-md-6">
                <strong>Destination Port:</strong> <?= escapeHtml($quotation['delivery_port_name'] ?? '') ?>
            </div>
            <div class="col-md-6">
                <strong>Payment Terms:</strong> <?= escapeHtml($quotation['payment_term_name'] ?? '') ?>
            </div>
            <div class="col-md-6">
                <strong>Shipment Terms:</strong> <?= escapeHtml($meta['shipment_term'] ?? ($quotation['shipment_type'] ?? '')) ?>
            </div>
        </div>

        <div class="table-responsive mb-4">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Product Description</th>
                        <th>HS Code</th>
                        <th>Packaging</th>
                        <th class="text-end" style="width: 100px;">Qty</th>
                        <th style="width: 80px;">Unit</th>
                        <th class="text-end" style="width: 120px;">Rate</th>
                        <th class="text-end" style="width: 100px;">Discount</th>
                        <th class="text-end" style="width: 100px;">GST</th>
                        <th class="text-end" style="width: 150px;">Net Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $index => $item): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= escapeHtml($item['product_name'] ?? '') ?></td>
                            <td><?= escapeHtml($item['hsn_code'] ?? '') ?></td>
                            <td><?= escapeHtml($item['packing_type_name'] ?? '') ?></td>
                            <td class="text-end"><?= number_format((float) ($item['quantity'] ?? 0), 3) ?></td>
                            <td><?= escapeHtml($item['unit_code'] ?? '') ?></td>
                            <td class="text-end"><?= number_format((float) ($item['rate'] ?? 0), 4) ?></td>
                            <td class="text-end"><?= number_format((float) ($item['discount_amount'] ?? 0), 2) ?></td>
                            <td class="text-end"><?= number_format((float) ($item['tax_amount'] ?? 0), 2) ?></td>
                            <td class="text-end font-weight-bold"><?= number_format((float) ($item['net_amount'] ?? 0), 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="row">
            <div class="col-md-7">
                <strong>General Remarks & Terms:</strong><br>
                <div class="p-2 border rounded bg-light" style="min-height: 100px;">
                    <?= nl2br(escapeHtml($quotation['remarks'] ?? '')) ?>
                </div>
            </div>
            <div class="col-md-5">
                <table class="table table-sm table-bordered">
                    <tr><th>Subtotal</th><td class="text-end"><?= number_format((float) ($totals['subtotal'] ?? 0), 2) ?></td></tr>
                    <tr><th>Discount</th><td class="text-end text-danger">-<?= number_format((float) ($totals['discount'] ?? 0), 2) ?></td></tr>
                    <tr><th>GST</th><td class="text-end text-info"><?= number_format((float) ($totals['gst'] ?? 0), 2) ?></td></tr>
                    <tr><th>Freight</th><td class="text-end"><?= number_format((float) ($totals['freight'] ?? 0), 2) ?></td></tr>
                    <tr><th>Insurance</th><td class="text-end"><?= number_format((float) ($totals['insurance'] ?? 0), 2) ?></td></tr>
                    <tr><th>Other Charges</th><td class="text-end"><?= number_format((float) ($totals['other'] ?? 0), 2) ?></td></tr>
                    <tr class="table-primary fs-5 font-weight-bold">
                        <th>Grand Total</th>
                        <td class="text-end"><strong><?= number_format((float) ($totals['grand'] ?? 0), 2) ?></strong></td>
                    </tr>
                </table>
            </div>
        </div>

        <?php if (!empty($quotation['estimated_containers_json'])): ?>
            <?php $containers = json_decode($quotation['estimated_containers_json'], true); ?>
            <?php if (!empty($containers)): ?>
                <div class="mt-4 p-3 bg-light rounded border border-warning">
                    <h5 class="text-warning font-weight-bold"><i class="fa fa-ship"></i> Container Loading Recommendation</h5>
                    <div class="row">
                        <?php foreach ($containers as $c): ?>
                            <div class="col-md-4 mb-2">
                                <div class="card shadow-sm border-0 p-3 bg-white">
                                    <strong>Type:</strong> <?= escapeHtml($c['container_type']) ?><br>
                                    <strong>Quantity Recommended:</strong> <span class="badge bg-primary fs-6"><?= (int) $c['container_count'] ?></span><br>
                                    <strong>Estimated Utilization:</strong> <?= number_format($c['utilization_percent'], 1) ?>%<br>
                                    <strong>Total CBM:</strong> <?= number_format($c['total_volume_cbm'], 2) ?> m³ / <strong>Total Weight:</strong> <?= number_format($c['total_weight_kg'] / 1000, 2) ?> MT
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<div class="row mt-3 no-print">
    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h5 class="font-weight-bold text-secondary mb-3">Revision History</h5>
                <div style="max-height: 200px; overflow-y: auto;">
                    <?php foreach ($revisions as $revision): ?>
                        <div class="mb-2 border-bottom pb-1">
                            <strong>Rev <?= (int) $revision['revision_number'] ?></strong> <span class="text-muted text-xs">(<?= escapeHtml($revision['created_at']) ?>)</span><br>
                            <span class="text-sm"><?= escapeHtml($revision['revision_notes'] ?? 'No notes') ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <form method="post" action="/commercial-invoices/<?= (int) ($quotation['id'] ?? 0) ?>/revise" class="mt-3">
                    <?= csrfToken() ?>
                    <input type="text" name="revision_notes" class="form-control mb-2" placeholder="Describe this revision version..." required>
                    <button class="btn btn-sm btn-primary">Create New Snapshot</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h5 class="font-weight-bold text-secondary mb-3">Status Workflow & Approvals</h5>
                <form method="post" action="/commercial-invoices/<?= (int) ($quotation['id'] ?? 0) ?>/status" class="row g-2 align-items-center">
                    <?= csrfToken() ?>
                    <div class="col-md-5">
                        <select name="status" class="form-select select2-init">
                            <?php foreach ($statuses as $statusId => $statusName): ?>
                                <option value="<?= (int) $statusId ?>" <?= (int) ($quotation['status'] ?? 0) === (int) $statusId ? 'selected' : '' ?>><?= escapeHtml($statusName) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <input type="text" name="remarks" class="form-control" placeholder="Audit remarks..." required>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100">Update</button>
                    </div>
                </form>
                <div class="mt-3" style="max-height: 200px; overflow-y: auto;">
                    <?php foreach ($history as $entry): ?>
                        <div class="small text-muted mb-1 border-bottom pb-1">
                            <strong><?= escapeHtml($entry['created_at']) ?></strong>: Changed to 
                            <span class="badge bg-secondary"><?= escapeHtml($statuses[(int) $entry['new_status']] ?? 'Unknown') ?></span>
                            <em>"<?= escapeHtml($entry['remarks'] ?? '') ?>"</em>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>