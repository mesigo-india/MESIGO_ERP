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
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-0 fw-bold"><i class="fas fa-database text-primary me-2"></i><?= escapeHtml((string)($config['title'] ?? 'Master Data')) ?> Management</h4>
        <p class="text-muted small mb-0">Manage core master lists, seed relationship metadata, and run batch CSV imports/exports.</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#importModal"><i class="fas fa-file-import me-1"></i> Import CSV</button>
        <a href="/settings/master-data/<?= escapeHtml((string)$masterKey) ?>/export" class="btn btn-outline-success btn-sm"><i class="fas fa-file-export me-1"></i> Export CSV</a>
        <a href="/settings/master-data/<?= escapeHtml((string)$masterKey) ?>/create" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i> Add <?= escapeHtml((string)($config['singular'] ?? 'Master')) ?></a>
    </div>
</div>

<!-- Tabs mapping all masters -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-2 bg-light">
        <div class="d-flex flex-wrap gap-2">
            <?php foreach ($masters as $key => $master): ?>
                <a class="btn btn-sm <?= $key === $masterKey ? 'btn-dark text-white' : 'btn-white text-dark shadow-xs' ?> py-2" href="/settings/master-data/<?= escapeHtml((string)$key) ?>">
                    <?= escapeHtml((string)$master['title']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Filters Block -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="get" action="/settings/master-data/<?= escapeHtml((string)$masterKey) ?>" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label small fw-bold text-muted" for="search">Global Search</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="search" name="search" class="form-control" value="<?= escapeHtml((string)$search) ?>" placeholder="Search code, name, description, tags...">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted" for="status">Active Status</label>
                <select id="status" name="status" class="form-select form-select-sm">
                    <option value="" <?= $status === '' ? 'selected' : '' ?>>All Statuses</option>
                    <option value="1" <?= $status === '1' ? 'selected' : '' ?>>Active Records Only</option>
                    <option value="0" <?= $status === '0' ? 'selected' : '' ?>>Inactive Records Only</option>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-dark btn-sm w-50"><i class="fas fa-filter"></i> Apply Filter</button>
                <a href="/settings/master-data/<?= escapeHtml((string)$masterKey) ?>" class="btn btn-outline-secondary btn-sm w-50">Clear Filters</a>
            </div>
        </form>
    </div>
</div>

<!-- Table list -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.9rem;">
                <thead class="table-light">
                    <tr>
                        <th class="p-3">Code</th>
                        <th class="p-3">Name</th>
                        <?php if ($masterKey === 'products'): ?>
                            <th class="p-3">SKU</th>
                            <th class="p-3">HS Code</th>
                            <th class="p-3">GST %</th>
                            <th class="p-3">Buying Price</th>
                        <?php elseif ($masterKey === 'product-grades'): ?>
                            <th class="p-3">Purity %</th>
                            <th class="p-3">Moisture %</th>
                            <th class="p-3">Packing</th>
                        <?php elseif ($masterKey === 'warehouses'): ?>
                            <th class="p-3">Type</th>
                            <th class="p-3">Capacity</th>
                            <th class="p-3">Temp</th>
                        <?php else: ?>
                            <th class="p-3">Description</th>
                        <?php endif; ?>
                        <th class="p-3">Status</th>
                        <th class="p-3 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <td class="p-3 fw-bold text-dark"><?= escapeHtml((string)($row[$codeField] ?? '')) ?></td>
                            <td class="p-3"><?= escapeHtml((string)($row['name'] ?? '')) ?></td>
                            
                            <?php if ($masterKey === 'products'): ?>
                                <td class="p-3"><?= escapeHtml((string)($row['sku'] ?? '')) ?></td>
                                <td class="p-3"><?= escapeHtml((string)($row['hsn_code'] ?? '')) ?></td>
                                <td class="p-3"><?= escapeHtml((string)($row['gst'] ?? '0.00')) ?>%</td>
                                <td class="p-3"><?= escapeHtml((string)($row['buying_price'] ?? '0.00')) ?></td>
                            <?php elseif ($masterKey === 'product-grades'): ?>
                                <td class="p-3"><?= escapeHtml((string)($row['purity'] ?? '0.00')) ?>%</td>
                                <td class="p-3"><?= escapeHtml((string)($row['moisture'] ?? '0.00')) ?>%</td>
                                <td class="p-3"><?= escapeHtml((string)($row['packing'] ?? '')) ?></td>
                            <?php elseif ($masterKey === 'warehouses'): ?>
                                <td class="p-3"><?= escapeHtml((string)($row['warehouse_type'] ?? '')) ?></td>
                                <td class="p-3"><?= escapeHtml((string)($row['capacity'] ?? '0.00')) ?> MT</td>
                                <td class="p-3"><?= escapeHtml((string)($row['temperature'] ?? '')) ?></td>
                            <?php else: ?>
                                <td class="p-3 text-muted"><?= escapeHtml((string)($row['description'] ?? '')) ?></td>
                            <?php endif; ?>
                            
                            <td class="p-3"><?= statusBadge((int)($row['status'] ?? 0)) ?></td>
                            <td class="p-3 text-end">
                                <a href="/settings/master-data/<?= escapeHtml((string)$masterKey) ?>/<?= (int)$row['id'] ?>/edit" class="btn btn-xs btn-outline-primary me-1"><i class="fas fa-edit"></i> Edit</a>
                                <form method="post" action="/settings/master-data/<?= escapeHtml((string)$masterKey) ?>/<?= (int)$row['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Disable this item?');">
                                    <?= csrfToken() ?>
                                    <button type="submit" class="btn btn-xs btn-outline-danger"><i class="fas fa-ban"></i> Disable</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if ($rows === []): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted p-4">No records found matching current query filters.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if ($totalPages > 1): ?>
    <nav class="mt-3">
        <ul class="pagination pagination-sm">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= (int)$page === $i ? 'active' : '' ?>">
                    <a class="page-link" href="?search=<?= urlencode((string)$search) ?>&status=<?= urlencode((string)$status) ?>&page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
<?php endif; ?>

<!-- Import CSV Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="post" action="/settings/master-data/<?= escapeHtml((string)$masterKey) ?>/import" enctype="multipart/form-data">
            <?= csrfToken() ?>
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="importModalLabel"><i class="fas fa-file-import me-2"></i>Batch CSV Import</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Select CSV File</label>
                        <input type="file" name="csv_file" class="form-control" accept=".csv" required>
                    </div>
                    <div class="alert alert-info small mb-0">
                        <i class="fas fa-info-circle me-1"></i> Ensure headers match the database table fields exactly (e.g. <code>code</code>, <code>name</code>, <code>description</code>). Duplicate codes will be automatically detected and skipped.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary btn-sm">Upload & Import</button>
                </div>
            </div>
        </form>
    </div>
</div>