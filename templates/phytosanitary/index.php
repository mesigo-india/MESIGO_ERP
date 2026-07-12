<?php
$certificates = $certificates ?? [];
$statuses = $statuses ?? [];
$search = $search ?? '';
$status = $status ?? '';
$statusClasses = [0 => 'bg-secondary', 1 => 'bg-info', 2 => 'bg-success', 6 => 'bg-danger'];
?>
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 text-primary font-weight-bold mb-0"><i class="fas fa-leaf text-success me-2"></i>Phytosanitary Certificates</h1>
    <a href="/phytosanitary/create" class="btn btn-primary">Create Certificate</a>
</div>

<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body">
        <form method="get" action="/phytosanitary" class="row g-3">
            <div class="col-md-6">
                <label class="form-label font-weight-bold">Search</label>
                <input name="search" class="form-control" value="<?= escapeHtml($search) ?>" placeholder="Certificate number, buyer or remarks">
            </div>
            <div class="col-md-3">
                <label class="form-label font-weight-bold">Status</label>
                <select name="status" class="form-select">
                    <option value="" <?= $status === '' ? 'selected' : '' ?>>All</option>
                    <?php foreach ($statuses as $statusId => $statusName): ?>
                        <option value="<?= (int) $statusId ?>" <?= (string) $status === (string) $statusId ? 'selected' : '' ?>><?= escapeHtml($statusName) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button class="btn btn-primary">Search</button>
                <a href="/phytosanitary" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Certificate No</th>
                        <th>Date</th>
                        <th>Buyer</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($certificates as $certificate): ?>
                        <tr>
                            <td><?= escapeHtml($certificate['document_number']) ?></td>
                            <td><?= escapeHtml($certificate['document_date']) ?></td>
                            <td><?= escapeHtml($certificate['buyer_name'] ?? '') ?></td>
                            <td>
                                <span class="badge <?= escapeHtml($statusClasses[(int) $certificate['status']] ?? 'bg-secondary') ?>"><?= escapeHtml($statuses[(int) $certificate['status']] ?? 'Unknown') ?></span>
                            </td>
                            <td>
                                <a href="/phytosanitary/<?= (int) $certificate['id'] ?>" class="btn btn-sm btn-outline-secondary">View</a>
                                <a href="/phytosanitary/<?= (int) $certificate['id'] ?>/edit" class="btn btn-sm btn-outline-primary">Edit</a>
                                <a href="/phytosanitary/<?= (int) $certificate['id'] ?>/print" class="btn btn-sm btn-outline-dark">Print</a>
                                <?php if (\App\Core\Session::get('role_name') === 'admin'): ?>
                                    <form method="post" action="/phytosanitary/<?= (int) $certificate['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Are you sure you want to permanently delete this Certificate?');">
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
