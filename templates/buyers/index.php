<?php
$buyers = $buyers ?? [];
$search = $search ?? '';
$status = $status ?? '';
?>
<div class="page-header">
    <h1>Buyers</h1>
    <a href="/buyers/create" class="btn btn-primary">Add Buyer</a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="get" action="/buyers" class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="search">Buyer Search</label>
                <input type="text" id="search" name="search" class="form-control" value="<?= escapeHtml($search ?? '') ?>" placeholder="Code, company, contact, email or phone">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="status">Status</label>
                <select id="status" name="status" class="form-select">
                    <option value="" <?= ($status ?? '') === '' ? 'selected' : '' ?>>All</option>
                    <option value="1" <?= ($status ?? '') === '1' ? 'selected' : '' ?>>Active</option>
                    <option value="0" <?= ($status ?? '') === '0' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">Search</button>
                <a href="/buyers" class="btn btn-outline-secondary">Reset</a>
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
                        <th>Buyer Code</th>
                        <th>Company</th>
                        <th>Contact</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>GST</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($buyers as $buyer): ?>
                        <tr>
                            <td><?= escapeHtml($buyer['buyer_code']) ?></td>
                            <td><?= escapeHtml($buyer['company_name']) ?></td>
                            <td><?= escapeHtml($buyer['contact_person'] ?? '') ?></td>
                            <td><?= escapeHtml($buyer['email'] ?? '') ?></td>
                            <td><?= escapeHtml($buyer['phone'] ?? '') ?></td>
                            <td><?= escapeHtml($buyer['gst_number'] ?? '') ?></td>
                            <td><?= statusBadge((int) $buyer['status']) ?></td>
                            <td>
                                <a href="/buyers/<?= (int) $buyer['id'] ?>/edit" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="post" action="/buyers/<?= (int) $buyer['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Disable this buyer?');">
                                    <?= csrfToken() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Disable</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>