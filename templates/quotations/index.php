<?php
$quotations = $quotations ?? [];
$statuses = $statuses ?? [];
$search = $search ?? '';
$status = $status ?? '';
$statusClasses = [0 => 'bg-secondary', 1 => 'bg-warning', 2 => 'bg-success', 3 => 'bg-danger', 4 => 'bg-info', 5 => 'bg-primary', 6 => 'bg-dark'];
?>
<div class="page-header">
    <h1>Quotations</h1>
    <a href="/quotations/create" class="btn btn-primary">Create Quotation</a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="get" action="/quotations" class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="search">Quotation Search</label>
                <input type="text" id="search" name="search" class="form-control" value="<?= escapeHtml($search ?? '') ?>" placeholder="Quotation number, buyer or remarks">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="status">Status</label>
                <select id="status" name="status" class="form-select">
                    <option value="" <?= ($status ?? '') === '' ? 'selected' : '' ?>>All</option>
                    <?php foreach ($statuses as $statusId => $statusName): ?>
                        <option value="<?= (int) $statusId ?>" <?= (string) ($status ?? '') === (string) $statusId ? 'selected' : '' ?>><?= escapeHtml($statusName) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">Search</button>
                <a href="/quotations" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped datatable">
                <thead>
                    <tr>
                        <th>Quotation No.</th>
                        <th>Date</th>
                        <th>Buyer</th>
                        <th>Currency</th>
                        <th>Valid Days</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($quotations as $quotation): ?>
                        <tr>
                            <td><?= escapeHtml($quotation['document_number']) ?></td>
                            <td><?= escapeHtml($quotation['document_date']) ?></td>
                            <td><?= escapeHtml($quotation['buyer_name'] ?? '') ?></td>
                            <td><?= escapeHtml($quotation['currency_code'] ?? '') ?></td>
                            <td><?= escapeHtml((string) ($quotation['validity_days'] ?? '')) ?></td>
                            <td><span class="badge <?= escapeHtml($statusClasses[(int) $quotation['status']] ?? 'bg-secondary') ?>"><?= escapeHtml($statuses[(int) $quotation['status']] ?? 'Unknown') ?></span></td>
                            <td>
                                <a href="/quotations/<?= (int) $quotation['id'] ?>" class="btn btn-sm btn-outline-secondary">View</a>
                                <a href="/quotations/<?= (int) $quotation['id'] ?>/edit" class="btn btn-sm btn-outline-primary">Edit</a>
                                <a href="/quotations/<?= (int) $quotation['id'] ?>/print" class="btn btn-sm btn-outline-dark">Print</a>
                                <?php if (\App\Core\Session::get('role_name') === 'admin'): ?>
                                    <form method="post" action="/quotations/<?= (int) $quotation['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Are you sure you want to permanently delete this Quotation?');">
                                        <?= csrfToken() ?>
                                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>