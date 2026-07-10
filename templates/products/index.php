<?php
$h = fn($s) => htmlspecialchars((string)($s ?? ''));
?>

<!-- ── Page Header ─────────────────────────────────────────────────────────── -->
<div class="d-flex align-items-start justify-content-between mb-3 flex-wrap gap-2 d-print-none">
    <div>
        <h4 class="mb-1 fw-bold"><i class="fas fa-box-open me-2 text-primary"></i>Export Product Master</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="/">Home</a></li>
                <li class="breadcrumb-item active">Products</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary shadow-sm" type="button" data-bs-toggle="collapse" data-bs-target="#filterPanel">
            <i class="fas fa-filter text-muted"></i> Filters
        </button>
        <button class="btn btn-outline-secondary shadow-sm"><i class="fas fa-file-import me-1"></i> Import</button>
        <a href="/products?export=csv" class="btn btn-outline-success shadow-sm" target="_blank"><i class="fas fa-file-excel me-1"></i> Excel</a>
        <a href="/products?export=pdf" class="btn btn-outline-danger shadow-sm" target="_blank"><i class="fas fa-file-pdf me-1"></i> PDF</a>
        <a href="/products?export=print" class="btn btn-outline-secondary shadow-sm" target="_blank"><i class="fas fa-print me-1"></i> Print</a>
        <?php if ($this->auth->can('products.create')): ?>
            <a href="/products/create" class="btn btn-primary shadow-sm"><i class="fas fa-plus me-1"></i> Add Product</a>
        <?php endif; ?>
    </div>
</div>

<!-- ── Dashboard Stats ──────────────────────────────────────────────────────── -->
<div class="row g-3 mb-4 d-print-none">
    <div class="col-6 col-md-3 col-xl-3">
        <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #0d6efd !important;">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted small mb-1">Total Products</p>
                        <h3 class="fw-bold mb-0 text-primary"><?= number_format($stats['total'] ?? 0) ?></h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 rounded-circle p-2">
                        <i class="fas fa-boxes text-primary fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 col-xl-3">
        <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #198754 !important;">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted small mb-1">Active</p>
                        <h3 class="fw-bold mb-0 text-success"><?= number_format($stats['active'] ?? 0) ?></h3>
                    </div>
                    <div class="bg-success bg-opacity-10 rounded-circle p-2">
                        <i class="fas fa-check-circle text-success fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 col-xl-3">
        <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #ffc107 !important;">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted small mb-1">Featured</p>
                        <h3 class="fw-bold mb-0 text-warning"><?= number_format($stats['featured'] ?? 0) ?></h3>
                    </div>
                    <div class="bg-warning bg-opacity-10 rounded-circle p-2">
                        <i class="fas fa-star text-warning fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 col-xl-3">
        <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #0dcaf0 !important;">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted small mb-1">Export Ready</p>
                        <h3 class="fw-bold mb-0 text-info"><?= number_format($stats['export_ready'] ?? 0) ?></h3>
                    </div>
                    <div class="bg-info bg-opacity-10 rounded-circle p-2">
                        <i class="fas fa-ship text-info fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ── Filter Panel ─────────────────────────────────────────────────────────── -->
<div class="collapse <?= ($search || $status || !empty($filters['category_id']) || !empty($filters['country_of_origin'])) ? 'show' : '' ?> mb-4" id="filterPanel">
    <div class="card border-0 shadow-sm">
        <div class="card-body bg-light">
            <form method="GET" action="/products" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-muted">Search Code / Name / HS</label>
                    <input type="text" name="search" class="form-control bg-white" value="<?= $h($search) ?>" placeholder="Type to search...">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold text-muted">Status</label>
                    <select name="status" class="form-select bg-white">
                        <option value="">All Statuses</option>
                        <option value="1" <?= $status === '1' ? 'selected' : '' ?>>Active</option>
                        <option value="0" <?= $status === '0' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-muted">Category</label>
                    <select name="category_id" class="form-select bg-white">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= (string)($filters['category_id'] ?? '') === (string)$cat['id'] ? 'selected' : '' ?>>
                                <?= $h($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold text-muted">Origin</label>
                    <input type="text" name="country_of_origin" class="form-control bg-white" value="<?= $h($filters['country_of_origin'] ?? '') ?>" placeholder="Country Name">
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i> Search</button>
                    <a href="/products" class="btn btn-light border"><i class="fas fa-undo"></i></a>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── Product Grid ─────────────────────────────────────────────────────────── -->
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover table-bordered align-middle mb-0 w-100 text-nowrap" id="productsTable">
            <thead class="table-dark sticky-top border-bottom">
                <tr>
                    <th class="ps-3 text-white fw-bold" style="width:40px">#</th>
                    <th class="text-white fw-bold" style="min-width:130px">Product Code</th>
                    <th class="text-white fw-bold" style="min-width:250px">Product Name</th>
                    <th class="text-white fw-bold" style="min-width:120px">HS Code</th>
                    <th class="text-white fw-bold" style="min-width:150px">Category</th>
                    <th class="text-white fw-bold" style="min-width:130px">Origin</th>
                    <th class="text-white fw-bold text-end" style="min-width:120px">Pur. Price</th>
                    <th class="text-white fw-bold text-end" style="min-width:120px">Sell Price</th>
                    <th class="text-white fw-bold text-end" style="min-width:120px">Stock</th>
                    <th class="text-white fw-bold text-center" style="min-width:100px">Status</th>
                    <th class="text-white fw-bold text-center pe-3 d-print-none" style="min-width:80px">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                <tr>
                    <td colspan="11" class="text-center py-5 text-muted">
                        <i class="fas fa-box-open fa-3x mb-3 opacity-25 d-block"></i>
                        <strong>No products found.</strong><br>
                        <span>Try adjusting your search or filters.</span>
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($products as $i => $p): ?>
                    <tr>
                        <td class="ps-3 text-muted"><?= ($page - 1) * 50 + $i + 1 ?></td>
                        <td>
                            <a href="/products/<?= (int)$p['id'] ?>/edit" class="fw-semibold text-decoration-none text-primary fs-6">
                                <?= $h($p['product_code']) ?>
                            </a>
                            <div class="mt-1">
                                <?php if ($p['is_featured']): ?>
                                    <span class="badge bg-warning text-dark">Featured</span>
                                <?php endif; ?>
                                <?php if ($p['is_export']): ?>
                                    <span class="badge bg-info text-dark">Export</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <div class="fw-bold text-dark fs-6"><?= $h($p['name']) ?></div>
                            <?php if (!empty($p['scientific_name'])): ?>
                                <div class="text-muted fst-italic mt-1"><?= $h($p['scientific_name']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-secondary"><?= $h($p['hsn_code'] ?: 'N/A') ?></span>
                        </td>
                        <td class="text-muted">
                            <?= $h($p['category_name'] ?? '—') ?>
                        </td>
                        <td>
                            <?= $h($p['country_of_origin'] ?? '—') ?>
                        </td>
                        <td class="text-end fw-semibold text-danger">
                            <?= !empty($p['purchase_price']) ? number_format((float)$p['purchase_price'], 2) : '—' ?>
                            <small class="text-muted"><?= !empty($p['default_currency']) ? $h($p['default_currency']) : '' ?></small>
                        </td>
                        <td class="text-end fw-semibold text-success">
                            <?= !empty($p['selling_price']) ? number_format((float)$p['selling_price'], 2) : '—' ?>
                            <small class="text-muted"><?= !empty($p['default_currency']) ? $h($p['default_currency']) : '' ?></small>
                        </td>
                        <td class="text-end fw-bold <?= ((float)($p['opening_stock'] ?? 0) < (float)($p['reorder_level'] ?? 0)) ? 'text-danger' : 'text-dark' ?>">
                            <?= !empty($p['opening_stock']) ? number_format((float)$p['opening_stock'], 2) : '0.00' ?>
                        </td>
                        <td class="text-center">
                            <?php if ($p['status'] == 1): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center pe-3 d-print-none position-static">
                            <div class="dropdown position-static">
                                <button class="btn btn-sm btn-light border" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                    <?php if ($this->auth->can('products.update')): ?>
                                        <li><a class="dropdown-item fw-semibold" href="/products/<?= $p['id'] ?>/edit"><i class="fas fa-edit me-2 text-primary"></i> Edit Product</a></li>
                                        <li><a class="dropdown-item fw-semibold" href="/products/create?duplicate_from=<?= $p['id'] ?>"><i class="fas fa-copy me-2 text-secondary"></i> Duplicate</a></li>
                                    <?php endif; ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item fw-semibold text-success" href="#"><i class="fas fa-file-invoice-dollar me-2"></i> Create Quotation</a></li>
                                    <?php if ($this->auth->can('products.delete')): ?>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form method="POST" action="/products/<?= $p['id'] ?>/delete" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                                <button type="submit" class="dropdown-item text-danger fw-semibold"><i class="fas fa-trash-alt me-2"></i> Delete</button>
                                            </form>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- ── Pagination ───────────────────────────────────────────────────────── -->
    <?php if ($totalPages > 1): ?>
    <div class="card-footer bg-white border-top py-3 d-flex justify-content-between align-items-center">
        <div class="text-muted">
            Showing <?= count($products) ?> of <?= $totalRows ?> products
        </div>
        <nav>
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>">Previous</a>
                </li>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>">Next</a>
                </li>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>