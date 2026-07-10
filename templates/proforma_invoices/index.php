<?php
$invoices = $invoices ?? [];
$statuses = $statuses ?? [];
$search = $search ?? '';
$status = $status ?? '';
$statusClasses = [0 => 'bg-secondary', 2 => 'bg-success', 4 => 'bg-info', 5 => 'bg-primary', 6 => 'bg-danger'];
?>
<div class="page-header">
    <h1>Proforma Invoices</h1>
    <a href="/proforma-invoices/create" class="btn btn-primary">Create Proforma Invoice</a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="get" action="/proforma-invoices" class="row g-3">
            <div class="col-md-6"><label class="form-label" for="search">PI Search</label><input type="text" id="search" name="search" class="form-control" value="<?= escapeHtml($search) ?>" placeholder="PI number, buyer or remarks"></div>
            <div class="col-md-3"><label class="form-label" for="status">Status</label><select id="status" name="status" class="form-select"><option value="" <?= $status === '' ? 'selected' : '' ?>>All</option><?php foreach ($statuses as $statusId => $statusName): ?><option value="<?= (int) $statusId ?>" <?= (string) $status === (string) $statusId ? 'selected' : '' ?>><?= escapeHtml($statusName) ?></option><?php endforeach; ?></select></div>
            <div class="col-md-3 d-flex align-items-end gap-2"><button type="submit" class="btn btn-primary">Search</button><a href="/proforma-invoices" class="btn btn-outline-secondary">Reset</a></div>
        </form>
    </div>
</div>

<div class="card"><div class="card-body"><div class="table-responsive"><table class="table table-striped datatable"><thead><tr><th>PI No.</th><th>Date</th><th>Buyer</th><th>Currency</th><th>Valid Days</th><th>Status</th><th>Actions</th></tr></thead><tbody><?php foreach ($invoices as $invoice): ?><tr><td><?= escapeHtml($invoice['document_number']) ?></td><td><?= escapeHtml($invoice['document_date']) ?></td><td><?= escapeHtml($invoice['buyer_name'] ?? '') ?></td><td><?= escapeHtml($invoice['currency_code'] ?? '') ?></td><td><?= escapeHtml((string) ($invoice['validity_days'] ?? '')) ?></td><td><span class="badge <?= escapeHtml($statusClasses[(int) $invoice['status']] ?? 'bg-secondary') ?>"><?= escapeHtml($statuses[(int) $invoice['status']] ?? 'Unknown') ?></span></td><td><a href="/proforma-invoices/<?= (int) $invoice['id'] ?>" class="btn btn-sm btn-outline-secondary">View</a> <a href="/proforma-invoices/<?= (int) $invoice['id'] ?>/edit" class="btn btn-sm btn-outline-primary">Edit</a> <a href="/proforma-invoices/<?= (int) $invoice['id'] ?>/print" class="btn btn-sm btn-outline-dark">Print</a><?php if (\App\Core\Session::get('role_name') === 'admin'): ?><form method="post" action="/proforma-invoices/<?= (int) $invoice['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Are you sure you want to permanently delete this Proforma Invoice?');"><?= csrfToken() ?><button class="btn btn-sm btn-outline-danger">Delete</button></form><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div></div></div>