<?php
$quotation = $quotation ?? [];
$items = $items ?? [];
$meta = $meta ?? [];
$statuses = $statuses ?? [];
$revisions = $revisions ?? [];
$history = $history ?? [];
$statusClasses = [0 => 'bg-secondary', 1 => 'bg-warning', 2 => 'bg-success', 3 => 'bg-danger', 4 => 'bg-info', 5 => 'bg-primary', 6 => 'bg-dark'];
$totals = $meta['totals'] ?? [];
?>
<style>@media print {.no-print{display:none!important}.card{border:0}.page-header{display:none}}</style>
<div class="page-header no-print">
    <h1><?= escapeHtml($quotation['document_number']) ?></h1>
    <div class="btn-group">
        <a href="/quotations" class="btn btn-outline-secondary">Back</a>
        <a href="/quotations/<?= (int) $quotation['id'] ?>/edit" class="btn btn-outline-primary">Edit</a>
        <button onclick="window.print()" class="btn btn-outline-dark">Print</button>
        <form method="post" action="/quotations/<?= (int) $quotation['id'] ?>/email" class="d-inline"><?= csrfToken() ?><button class="btn btn-outline-info">Email Ready</button></form>
        <form method="post" action="/quotations/<?= (int) $quotation['id'] ?>/convert" class="d-inline" onsubmit="return confirm('Convert quotation to Proforma Invoice?');"><?= csrfToken() ?><button class="btn btn-success">Convert to PI</button></form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="text-center mb-4">
            <h2>EXPORT QUOTATION</h2>
            <div class="text-muted">PDF Ready · Email Ready · Print Ready</div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <strong>Quotation No:</strong> <?= escapeHtml($quotation['document_number']) ?><br>
                <strong>Revision:</strong> <?= (int) ($meta['revision'] ?? 0) ?><br>
                <strong>Date:</strong> <?= escapeHtml($quotation['document_date']) ?><br>
                <strong>Status:</strong> <span class="badge <?= escapeHtml($statusClasses[(int) $quotation['status']] ?? 'bg-secondary') ?>"><?= escapeHtml($statuses[(int) $quotation['status']] ?? 'Unknown') ?></span>
            </div>
            <div class="col-md-6">
                <strong>Buyer:</strong> <?= escapeHtml($quotation['buyer_name'] ?? '') ?><br>
                <strong>Currency:</strong> <?= escapeHtml($quotation['currency_code'] ?? '') ?><br>
                <strong>Valid Until:</strong> <?= escapeHtml($meta['valid_until'] ?? '') ?><br>
                <strong>Incoterm:</strong> <?= escapeHtml($quotation['incoterm_code'] ?? '') ?>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6"><strong>Loading Port:</strong> <?= escapeHtml($quotation['loading_port_name'] ?? '') ?></div>
            <div class="col-md-6"><strong>Delivery Port:</strong> <?= escapeHtml($quotation['delivery_port_name'] ?? '') ?></div>
            <div class="col-md-6"><strong>Payment Term:</strong> <?= escapeHtml($quotation['payment_term_name'] ?? '') ?></div>
            <div class="col-md-6"><strong>Shipment Term:</strong> <?= escapeHtml($meta['shipment_term'] ?? ($quotation['shipment_type'] ?? '')) ?></div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered">
                <thead><tr><th>#</th><th>Product</th><th>HS Code</th><th>Packaging</th><th>Qty</th><th>Unit</th><th>Rate</th><th>Discount</th><th>GST</th><th>Amount</th></tr></thead>
                <tbody>
                    <?php foreach ($items as $index => $item): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= escapeHtml($item['product_name'] ?? '') ?></td>
                            <td><?= escapeHtml($item['hsn_code'] ?? '') ?></td>
                            <td><?= escapeHtml($item['packing_type_name'] ?? '') ?></td>
                            <td class="text-end"><?= number_format((float) $item['quantity'], 3) ?></td>
                            <td><?= escapeHtml($item['unit_code'] ?? '') ?></td>
                            <td class="text-end"><?= number_format((float) $item['rate'], 4) ?></td>
                            <td class="text-end"><?= number_format((float) $item['discount_amount'], 2) ?></td>
                            <td class="text-end"><?= number_format((float) $item['tax_amount'], 2) ?></td>
                            <td class="text-end"><?= number_format((float) $item['net_amount'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="row">
            <div class="col-md-7"><strong>Remarks:</strong><br><?= nl2br(escapeHtml($quotation['remarks'] ?? '')) ?></div>
            <div class="col-md-5">
                <table class="table table-sm">
                    <tr><th>Subtotal</th><td class="text-end"><?= number_format((float) ($totals['subtotal'] ?? 0), 2) ?></td></tr>
                    <tr><th>Discount</th><td class="text-end"><?= number_format((float) ($totals['discount'] ?? 0), 2) ?></td></tr>
                    <tr><th>GST</th><td class="text-end"><?= number_format((float) ($totals['gst'] ?? 0), 2) ?></td></tr>
                    <tr><th>Freight</th><td class="text-end"><?= number_format((float) ($totals['freight'] ?? 0), 2) ?></td></tr>
                    <tr><th>Insurance</th><td class="text-end"><?= number_format((float) ($totals['insurance'] ?? 0), 2) ?></td></tr>
                    <tr><th>Other Charges</th><td class="text-end"><?= number_format((float) ($totals['other'] ?? 0), 2) ?></td></tr>
                    <tr class="fs-5"><th>Grand Total</th><td class="text-end"><strong><?= number_format((float) ($totals['grand'] ?? 0), 2) ?></strong></td></tr>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row mt-3 no-print">
    <div class="col-md-6">
        <div class="card"><div class="card-body"><h5>Revision History</h5><?php foreach ($revisions as $revision): ?><div>Rev <?= (int) $revision['revision_number'] ?> - <?= escapeHtml($revision['created_at']) ?> - <?= escapeHtml($revision['revision_notes'] ?? '') ?></div><?php endforeach; ?><form method="post" action="/quotations/<?= (int) $quotation['id'] ?>/revise" class="mt-2"><?= csrfToken() ?><input type="text" name="revision_notes" class="form-control mb-2" placeholder="Revision notes"><button class="btn btn-sm btn-primary">Save Revision</button></form></div></div>
    </div>
    <div class="col-md-6">
        <div class="card"><div class="card-body"><h5>Status Workflow</h5><form method="post" action="/quotations/<?= (int) $quotation['id'] ?>/status" class="row g-2"><?= csrfToken() ?><div class="col-md-5"><select name="status" class="form-select"><?php foreach ($statuses as $statusId => $statusName): ?><option value="<?= (int) $statusId ?>" <?= (int) $quotation['status'] === (int) $statusId ? 'selected' : '' ?>><?= escapeHtml($statusName) ?></option><?php endforeach; ?></select></div><div class="col-md-5"><input type="text" name="remarks" class="form-control" placeholder="Status remarks"></div><div class="col-md-2"><button class="btn btn-primary">Update</button></div></form><?php foreach ($history as $entry): ?><div class="small text-muted mt-1"><?= escapeHtml($entry['created_at']) ?>: <?= escapeHtml($statuses[(int) $entry['new_status']] ?? 'Unknown') ?> <?= escapeHtml($entry['remarks'] ?? '') ?></div><?php endforeach; ?></div></div>
    </div>
</div>