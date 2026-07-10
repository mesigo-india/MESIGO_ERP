<?php
/**
 * MESIGO ERP — Export Supplier CRM List Page
 */

function sSortUrl(string $col, string $currentSort, string $currentDir): string
{
    $p = $_GET;
    $p['sort'] = $col;
    $p['dir']  = ($currentSort === $col && $currentDir === 'ASC') ? 'DESC' : 'ASC';
    $p['page'] = 1;
    return '/suppliers?' . http_build_query($p);
}

function sSortIcon(string $col, string $currentSort, string $currentDir): string
{
    if ($col !== $currentSort) {
        return '<i class="fas fa-sort text-muted ms-1 small"></i>';
    }
    return $currentDir === 'ASC'
        ? '<i class="fas fa-sort-up text-primary ms-1 small"></i>'
        : '<i class="fas fa-sort-down text-primary ms-1 small"></i>';
}

$stats      = $stats      ?? ['total' => 0, 'active' => 0, 'inactive' => 0, 'approved' => 0, 'blocked' => 0, 'preferred' => 0, 'international' => 0, 'domestic' => 0];
$suppliers  = $suppliers  ?? [];
$total      = $totalRows  ?? 0;
$page       = $page       ?? 1;
$limit      = $limit      ?? 50;
$totalPages = $totalPages ?? 1;
$offset     = $offset     ?? 0;
$sort       = $sort       ?? 'created_at';
$dir        = $dir        ?? 'DESC';

$h = fn($s) => htmlspecialchars((string)($s ?? ''));
?>

<!-- ── Page Header ─────────────────────────────────────────────────────────── -->
<div class="d-flex align-items-start justify-content-between mb-3 flex-wrap gap-2 d-print-none">
    <div>
        <h4 class="mb-1 fw-bold"><i class="fas fa-truck-loading me-2 text-primary"></i>Export Supplier CRM</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="/">Home</a></li>
                <li class="breadcrumb-item active">Suppliers</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary shadow-sm" type="button" data-bs-toggle="collapse" data-bs-target="#filterPanel">
            <i class="fas fa-filter text-muted"></i> Filters
        </button>
        <button class="btn btn-outline-secondary shadow-sm"><i class="fas fa-file-import me-1"></i> Import</button>
        <a href="/suppliers?export=csv" class="btn btn-outline-success shadow-sm" target="_blank"><i class="fas fa-file-excel me-1"></i> Excel</a>
        <a href="/suppliers?export=pdf" class="btn btn-outline-danger shadow-sm" target="_blank"><i class="fas fa-file-pdf me-1"></i> PDF</a>
        <a href="/suppliers?export=print" class="btn btn-outline-secondary shadow-sm" target="_blank"><i class="fas fa-print me-1"></i> Print</a>
        <a href="/suppliers/create" class="btn btn-primary shadow-sm"><i class="fas fa-plus me-1"></i> Add Supplier</a>
    </div>
</div>

<!-- ── Dashboard Stats Cards ──────────────────────────────────────────────── -->
<div class="row g-3 mb-4 d-print-none">
    <div class="col-6 col-md-3 col-xl-3">
        <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #0d6efd !important;">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted small mb-1">Total Suppliers</p>
                        <h3 class="fw-bold mb-0 text-primary"><?= number_format($stats['total']) ?></h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 rounded-circle p-2">
                        <i class="fas fa-users text-primary fa-lg"></i>
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
                        <h3 class="fw-bold mb-0 text-success"><?= number_format($stats['active']) ?></h3>
                    </div>
                    <div class="bg-success bg-opacity-10 rounded-circle p-2">
                        <i class="fas fa-check-circle text-success fa-lg"></i>
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
                        <p class="text-muted small mb-1">Approved</p>
                        <h3 class="fw-bold mb-0 text-info"><?= number_format($stats['approved']) ?></h3>
                    </div>
                    <div class="bg-info bg-opacity-10 rounded-circle p-2">
                        <i class="fas fa-thumbs-up text-info fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 col-xl-3">
        <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #dc3545 !important;">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted small mb-1">Blocked</p>
                        <h3 class="fw-bold mb-0 text-danger"><?= number_format($stats['blocked']) ?></h3>
                    </div>
                    <div class="bg-danger bg-opacity-10 rounded-circle p-2">
                        <i class="fas fa-ban text-danger fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ── Filter Panel ─────────────────────────────────────────────────────────── -->
<div class="collapse <?= ($search || $status || $country || $type || $priority) ? 'show' : '' ?> mb-4 d-print-none" id="filterPanel">
    <div class="card border-0 shadow-sm">
        <div class="card-body bg-light">
            <form method="GET" action="/suppliers" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-muted">Search</label>
                    <input type="text" name="search" class="form-control bg-white" value="<?= $h($search) ?>" placeholder="Code, Name, Email, Contact">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold text-muted">Status</label>
                    <select name="status" class="form-select bg-white">
                        <option value="">All Statuses</option>
                        <option value="1" <?= $status === '1' ? 'selected' : '' ?>>Active</option>
                        <option value="0" <?= $status === '0' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold text-muted">Supplier Type</label>
                    <select name="type" class="form-select bg-white">
                        <option value="">All Types</option>
                        <option value="domestic" <?= $type === 'domestic' ? 'selected' : '' ?>>Domestic</option>
                        <option value="international" <?= $type === 'international' ? 'selected' : '' ?>>International</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold text-muted">Priority</label>
                    <select name="priority" class="form-select bg-white">
                        <option value="">All Priorities</option>
                        <option value="low" <?= $priority === 'low' ? 'selected' : '' ?>>Low</option>
                        <option value="medium" <?= $priority === 'medium' ? 'selected' : '' ?>>Medium</option>
                        <option value="high" <?= $priority === 'high' ? 'selected' : '' ?>>High</option>
                        <option value="critical" <?= $priority === 'critical' ? 'selected' : '' ?>>Critical</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold text-muted">Country</label>
                    <select name="country" class="form-select bg-white">
                        <option value="">All Countries</option>
                        <?php foreach ($countries as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= (string)$country === (string)$c['id'] ? 'selected' : '' ?>><?= $h($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-1 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i></button>
                    <a href="/suppliers" class="btn btn-light border"><i class="fas fa-undo"></i></a>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── Supplier Grid ─────────────────────────────────────────────────────────── -->
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover table-bordered align-middle mb-0 w-100 text-nowrap" id="suppliersTable">
            <thead class="table-dark sticky-top border-bottom">
                <tr>
                    <th class="ps-3 text-white fw-bold" style="width:40px">#</th>
                    <th class="text-white fw-bold" style="min-width:130px"><a href="<?= sSortUrl('supplier_code', $sort, $dir) ?>" class="text-white text-decoration-none">Supplier Code <?= sSortIcon('supplier_code', $sort, $dir) ?></a></th>
                    <th class="text-white fw-bold" style="min-width:250px"><a href="<?= sSortUrl('company_name', $sort, $dir) ?>" class="text-white text-decoration-none">Company Name <?= sSortIcon('company_name', $sort, $dir) ?></a></th>
                    <th class="text-white fw-bold" style="min-width:150px"><a href="<?= sSortUrl('contact_person', $sort, $dir) ?>" class="text-white text-decoration-none">Contact Person <?= sSortIcon('contact_person', $sort, $dir) ?></a></th>
                    <th class="text-white fw-bold" style="min-width:130px"><a href="<?= sSortUrl('country_id', $sort, $dir) ?>" class="text-white text-decoration-none">Country <?= sSortIcon('country_id', $sort, $dir) ?></a></th>
                    <th class="text-white fw-bold" style="min-width:120px">Phone</th>
                    <th class="text-white fw-bold" style="min-width:180px">Email</th>
                    <th class="text-white fw-bold" style="min-width:100px">Rating</th>
                    <th class="text-white fw-bold text-center" style="min-width:100px">Status</th>
                    <th class="text-white fw-bold text-center pe-3 d-print-none" style="min-width:80px">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($suppliers)): ?>
                <tr>
                    <td colspan="10" class="text-center py-5 text-muted">
                        <i class="fas fa-truck-loading fa-3x mb-3 opacity-25 d-block"></i>
                        <strong>No suppliers found.</strong><br>
                        <span>Try adjusting your search or filters.</span>
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($suppliers as $i => $s): ?>
                    <tr>
                        <td class="ps-3 text-muted"><?= $offset + $i + 1 ?></td>
                        <td>
                            <a href="/suppliers/<?= (int)$s['id'] ?>/edit" class="fw-semibold text-decoration-none text-primary fs-6">
                                <?= $h($s['supplier_code']) ?>
                            </a>
                            <div class="mt-1">
                                <?php if ($s['supplier_type'] == 'international'): ?>
                                    <span class="badge bg-info text-dark">International</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Domestic</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <div class="fw-bold text-dark fs-6"><?= $h($s['company_name']) ?></div>
                            <div class="mt-1">
                                <?php if ($s['is_preferred']): ?>
                                    <span class="badge bg-warning text-dark"><i class="fas fa-star text-dark"></i> Preferred</span>
                                <?php endif; ?>
                                <?php if ($s['is_approved']): ?>
                                    <span class="badge bg-success"><i class="fas fa-check"></i> Approved</span>
                                <?php endif; ?>
                                <?php if ($s['is_blocked']): ?>
                                    <span class="badge bg-danger"><i class="fas fa-ban"></i> Blocked</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td><?= $h($s['contact_person']) ?></td>
                        <td class="text-muted"><?= $h($s['country_name'] ?? '—') ?></td>
                        <td><?= $h($s['phone'] ?: '—') ?></td>
                        <td><?= $h($s['email'] ?: '—') ?></td>
                        <td>
                            <?php 
                                $rating = (float) $s['rating'];
                                $fullStars = floor($rating);
                                $halfStar = ($rating - $fullStars) >= 0.5;
                                $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
                            ?>
                            <div class="text-warning">
                                <?php for($j=0; $j<$fullStars; $j++): ?><i class="fas fa-star"></i><?php endfor; ?>
                                <?php if($halfStar): ?><i class="fas fa-star-half-alt"></i><?php endif; ?>
                                <?php for($j=0; $j<$emptyStars; $j++): ?><i class="far fa-star"></i><?php endfor; ?>
                                <span class="text-dark small ms-1">(<?= number_format($rating, 1) ?>)</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <?php if ($s['status'] == 1): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center pe-3 d-print-none position-static">
                            <div class="dropdown position-static">
                                <button class="btn btn-sm btn-light border" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                    <li><a class="dropdown-item fw-semibold" href="/suppliers/<?= $s['id'] ?>/view"><i class="fas fa-eye me-2 text-info"></i> View</a></li>
                                    <li><a class="dropdown-item fw-semibold" href="/suppliers/<?= $s['id'] ?>/edit"><i class="fas fa-edit me-2 text-primary"></i> Edit</a></li>
                                    <li><a class="dropdown-item fw-semibold" href="/suppliers/create?duplicate_from=<?= $s['id'] ?>"><i class="fas fa-copy me-2 text-secondary"></i> Duplicate</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item fw-semibold" href="#"><i class="fas fa-shopping-cart me-2 text-success"></i> Purchase Orders</a></li>
                                    <li><a class="dropdown-item fw-semibold" href="#"><i class="fas fa-book me-2 text-dark"></i> Ledger</a></li>
                                    <li><a class="dropdown-item fw-semibold" href="#"><i class="fas fa-history me-2 text-muted"></i> History</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="POST" action="/suppliers/<?= $s['id'] ?>/delete" onsubmit="return confirm('Are you sure you want to delete this supplier?');">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                            <button type="submit" class="dropdown-item text-danger fw-semibold"><i class="fas fa-trash-alt me-2"></i> Delete</button>
                                        </form>
                                    </li>
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
    <div class="card-footer bg-white border-top py-3 d-flex justify-content-between align-items-center d-print-none">
        <div class="text-muted">
            Showing <?= count($suppliers) ?> of <?= $total ?> suppliers
        </div>
        <nav>
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= sPageUrl($page - 1) ?>">Previous</a>
                </li>
                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="<?= sPageUrl($i) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= sPageUrl($page + 1) ?>">Next</a>
                </li>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>

<?php 
// Helper page url inside template so it works with the pagination
if (!function_exists('sPageUrl')) {
    function sPageUrl(int $p): string {
        $q = $_GET;
        $q['page'] = $p;
        return '/suppliers?' . http_build_query($q);
    }
}
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fix dropdown clipping inside table-responsive using Popper.js fixed strategy
    var dropdowns = document.querySelectorAll('.table-responsive [data-bs-toggle="dropdown"]');
    dropdowns.forEach(function(el) {
        new bootstrap.Dropdown(el, {
            popperConfig: function(defaultBsPopperConfig) {
                return Object.assign({}, defaultBsPopperConfig, {
                    strategy: 'fixed'
                });
            }
        });
    });
});
</script>
