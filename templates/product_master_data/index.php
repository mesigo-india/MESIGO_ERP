<?php
$rows = $rows ?? [];
$config = $config ?? [];
$masterKey = $masterKey ?? '';
$search = $search ?? '';
$status = $status ?? '';
$codeField = $config['code_field'] ?? 'code';
$masters = $masters ?? [];
$page = $page ?? 1;
$totalPages = $totalPages ?? 1;
?>
<div class="page-header">
    <h1><?= escapeHtml((string) ($config['title'] ?? 'Master Data')) ?></h1>
    <a href="/settings/master-data/<?= escapeHtml((string) $masterKey) ?>/create" class="btn btn-primary">Add <?= escapeHtml((string) ($config['singular'] ?? 'Master')) ?></a>
</div>

<div class="card mb-3"><div class="card-body"><div class="row g-2">
    <?php foreach ($masters as $key => $master): ?>
        <div class="col-md-2"><a class="btn btn-sm <?= $key === $masterKey ? 'btn-success' : 'btn-outline-success' ?> w-100" href="/settings/master-data/<?= escapeHtml((string) $key) ?>"><?= escapeHtml((string) $master['title']) ?></a></div>
    <?php endforeach; ?>
</div></div></div>

<div class="card mb-3">
    <div class="card-body">
        <form method="get" action="/settings/master-data/<?= escapeHtml((string) $masterKey) ?>" class="row g-2 align-items-end">
            <div class="col-md-6">
                <label class="form-label" for="search">Search</label>
                <input type="text" id="search" name="search" class="form-control" value="<?= escapeHtml((string) $search) ?>" placeholder="Search code, name, description">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="status">Status</label>
                <select id="status" name="status" class="form-select">
                    <option value="" <?= $status === '' ? 'selected' : '' ?>>All</option>
                    <option value="1" <?= $status === '1' ? 'selected' : '' ?>>Active</option>
                    <option value="0" <?= $status === '0' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-outline-primary">Search</button>
                <a href="/settings/master-data/<?= escapeHtml((string) $masterKey) ?>" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Code</th>
                        <?php if ($masterKey !== 'hs-codes'): ?>
                            <th>Name</th>
                        <?php endif; ?>
                        <?php if ($masterKey === 'hs-codes'): ?>
                            <th>Description</th>
                            <th>Category</th>
                            <th>Duty Rate</th>
                        <?php else: ?>
                            <th>Description</th>
                        <?php endif; ?>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <td><?= escapeHtml((string) ($row[$codeField] ?? '')) ?></td>
                            <?php if ($masterKey !== 'hs-codes'): ?>
                                <td><?= escapeHtml((string) ($row['name'] ?? '')) ?></td>
                            <?php endif; ?>
                            <?php if ($masterKey === 'hs-codes'): ?>
                                <td><?= escapeHtml((string) ($row['description'] ?? '')) ?></td>
                                <td><?= escapeHtml((string) ($row['category'] ?? '')) ?></td>
                                <td><?= escapeHtml((string) ($row['duty_rate'] ?? '')) ?></td>
                            <?php else: ?>
                                <td><?= escapeHtml((string) ($row['description'] ?? '')) ?></td>
                            <?php endif; ?>
                            <td><?= statusBadge((int) ($row['status'] ?? 0)) ?></td>
                            <td>
                                <a href="/settings/master-data/<?= escapeHtml((string) $masterKey) ?>/<?= (int) $row['id'] ?>/edit" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="post" action="/settings/master-data/<?= escapeHtml((string) $masterKey) ?>/<?= (int) $row['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Disable this item?');">
                                    <?= csrfToken() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Disable</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if ($rows === []): ?>
                        <tr>
                            <td colspan="<?= $masterKey === 'hs-codes' ? 6 : 5 ?>" class="text-center text-muted">No records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php if ($totalPages > 1): ?>
    <nav class="mt-3"><ul class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?= (int) $page === $i ? 'active' : '' ?>"><a class="page-link" href="?search=<?= urlencode((string) $search) ?>&status=<?= urlencode((string) $status) ?>&page=<?= $i ?>"><?= $i ?></a></li>
        <?php endfor; ?>
    </ul></nav>
<?php endif; ?>