<?php
/**
 * MESIGO ERP — Buyer CRM List Page
 * Variables from controller: $buyers, $total, $stats, $page, $limit, $totalPages, $offset, $sort, $dir
 */

// ── Helpers ───────────────────────────────────────────────────────────────────
function bSortUrl(string $col, string $currentSort, string $currentDir): string
{
    $p = $_GET;
    $p['sort'] = $col;
    $p['dir']  = ($currentSort === $col && $currentDir === 'ASC') ? 'DESC' : 'ASC';
    $p['page'] = 1;
    return '/buyers?' . http_build_query($p);
}

function bSortIcon(string $col, string $currentSort, string $currentDir): string
{
    if ($col !== $currentSort) {
        return '<i class="fas fa-sort text-muted ms-1 small"></i>';
    }
    return $currentDir === 'ASC'
        ? '<i class="fas fa-sort-up text-primary ms-1 small"></i>'
        : '<i class="fas fa-sort-down text-primary ms-1 small"></i>';
}

function bPageUrl(int $p): string
{
    $q = $_GET;
    $q['page'] = $p;
    return '/buyers?' . http_build_query($q);
}

function bExportUrl(string $type): string
{
    $q = $_GET;
    unset($q['page']);
    $q['export'] = $type;
    return '/buyers?' . http_build_query($q);
}

$stats      = $stats      ?? ['total' => 0, 'active' => 0, 'inactive' => 0, 'high_priority' => 0, 'today_followups' => 0, 'overdue_followups' => 0];
$buyers     = $buyers     ?? [];
$total      = $total      ?? 0;
$page       = $page       ?? 1;
$limit      = $limit      ?? 20;
$totalPages = $totalPages ?? 1;
$offset     = $offset     ?? 0;
$sort       = $sort       ?? 'created_at';
$dir        = $dir        ?? 'DESC';

$priorityClasses = ['High' => 'danger', 'Medium' => 'warning text-dark', 'Low' => 'secondary'];
$typeClasses     = ['International' => 'primary', 'Domestic' => 'success', 'Trading Company' => 'info text-dark'];
$statusLabels    = [1 => 'Active', 0 => 'Inactive', 2 => 'Prospect', 3 => 'Pending'];
$statusClasses   = [1 => 'success', 0 => 'secondary', 2 => 'info text-dark', 3 => 'warning text-dark'];
$leadStatusClasses = [
    'New Lead' => 'primary', 'Contacted' => 'info text-dark', 'Follow-up Pending' => 'warning text-dark',
    'Qualified' => 'success', 'Proposal Sent' => 'primary', 'Negotiation' => 'warning text-dark',
    'Won - Active Customer' => 'success', 'Cold' => 'secondary', 'Lost' => 'danger',
];
$today = date('Y-m-d');
?>

<!-- ── Page Header ─────────────────────────────────────────────────────────── -->
<div class="d-flex align-items-start justify-content-between mb-3 flex-wrap gap-2 d-print-none">
    <div>
        <h4 class="mb-1 fw-bold"><i class="fas fa-users me-2 text-primary"></i>Buyer CRM</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="/">Home</a></li>
                <li class="breadcrumb-item active">Buyers</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary shadow-sm" type="button" data-bs-toggle="collapse" data-bs-target="#filterPanel">
            <i class="fas fa-filter text-muted"></i> Filters
        </button>
        <button class="btn btn-outline-secondary shadow-sm"><i class="fas fa-file-import me-1"></i> Import</button>
        <a href="<?= bExportUrl('csv') ?>" class="btn btn-outline-success shadow-sm" target="_blank"><i class="fas fa-file-excel me-1"></i> Excel</a>
        <a href="<?= bExportUrl('pdf') ?>" class="btn btn-outline-danger shadow-sm" target="_blank"><i class="fas fa-file-pdf me-1"></i> PDF</a>
        <a href="<?= bExportUrl('print') ?>" class="btn btn-outline-secondary shadow-sm" target="_blank"><i class="fas fa-print me-1"></i> Print</a>
        <?php if ($this->auth->can('buyers.create')): ?>
            <a href="/buyers/create" class="btn btn-primary shadow-sm"><i class="fas fa-plus me-1"></i> Add Buyer</a>
        <?php endif; ?>
    </div>
</div>

<!-- ── Dashboard Stats Cards ──────────────────────────────────────────────── -->
<div class="row g-3 mb-4 d-print-none">
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #0d6efd !important;">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted small mb-1">Total Buyers</p>
                        <h3 class="fw-bold mb-0 text-primary"><?= number_format($stats['total']) ?></h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 rounded-circle p-2">
                        <i class="fas fa-users text-primary fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
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
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #6c757d !important;">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted small mb-1">Inactive</p>
                        <h3 class="fw-bold mb-0 text-secondary"><?= number_format($stats['inactive']) ?></h3>
                    </div>
                    <div class="bg-secondary bg-opacity-10 rounded-circle p-2">
                        <i class="fas fa-user-slash text-secondary fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #dc3545 !important;">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted small mb-1">High Priority</p>
                        <h3 class="fw-bold mb-0 text-danger"><?= number_format($stats['high_priority']) ?></h3>
                    </div>
                    <div class="bg-danger bg-opacity-10 rounded-circle p-2">
                        <i class="fas fa-fire text-danger fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #fd7e14 !important;">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted small mb-1">Today's Follow-ups</p>
                        <h3 class="fw-bold mb-0" style="color:#fd7e14"><?= number_format($stats['today_followups']) ?></h3>
                    </div>
                    <div class="rounded-circle p-2" style="background:rgba(253,126,20,.12)">
                        <i class="fas fa-calendar-day fa-lg" style="color:#fd7e14"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #6f42c1 !important;">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted small mb-1">Overdue</p>
                        <h3 class="fw-bold mb-0 text-<?= $stats['overdue_followups'] > 0 ? 'danger' : 'secondary' ?>">
                            <?= number_format($stats['overdue_followups']) ?>
                        </h3>
                    </div>
                    <div class="bg-warning bg-opacity-10 rounded-circle p-2">
                        <i class="fas fa-clock text-warning fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ── Toolbar ─────────────────────────────────────────────────────────────── -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2 px-3">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="/buyers/create" class="btn btn-primary btn-sm" id="btn-add-buyer">
                <i class="fas fa-plus me-1"></i>Add Buyer
            </a>
            <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#importModal" id="btn-import">
                <i class="fas fa-file-upload me-1"></i>Import
            </button>
            <a href="<?= bExportUrl('csv') ?>" class="btn btn-outline-success btn-sm" id="btn-export-excel">
                <i class="fas fa-file-excel me-1"></i>Export Excel
            </a>
            <a href="<?= bExportUrl('print') ?>" target="_blank" class="btn btn-outline-danger btn-sm" id="btn-export-pdf">
                <i class="fas fa-file-pdf me-1"></i>Export PDF
            </a>
            <a href="/buyers" class="btn btn-outline-secondary btn-sm" id="btn-refresh">
                <i class="fas fa-sync-alt me-1"></i>Refresh
            </a>

            <!-- Search -->
            <form method="GET" action="/buyers" class="ms-auto d-flex align-items-center gap-2" id="searchForm">
                <?php foreach ($_GET as $k => $v): ?>
                    <?php if ($k !== 'search' && $k !== 'page'): ?>
                    <input type="hidden" name="<?= htmlspecialchars($k) ?>" value="<?= htmlspecialchars($v) ?>">
                    <?php endif; ?>
                <?php endforeach; ?>
                <div class="input-group input-group-sm" style="min-width:220px">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search buyers…"
                           value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" id="searchInput">
                    <?php if (!empty($_GET['search'])): ?>
                    <a href="<?php $q = $_GET; unset($q['search'],$q['page']); echo '/buyers?' . http_build_query($q); ?>"
                       class="btn btn-outline-secondary" title="Clear search"><i class="fas fa-times"></i></a>
                    <?php endif; ?>
                </div>
                <button class="btn btn-primary btn-sm" type="submit">Search</button>
            </form>

            <!-- Filters toggle -->
            <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse"
                    data-bs-target="#filterPanel" id="btn-filters">
                <i class="fas fa-filter me-1"></i>Filters
                <?php
                $activeFilters = array_filter([
                    $_GET['status'] ?? '', $_GET['country'] ?? '', $_GET['type'] ?? '',
                    $_GET['priority'] ?? '', $_GET['lead_source'] ?? '', $_GET['lead_status'] ?? '',
                ]);
                if (!empty($activeFilters)):
                ?>
                <span class="badge bg-primary ms-1"><?= count($activeFilters) ?></span>
                <?php endif; ?>
            </button>
        </div>
    </div>
</div>

<!-- ── Filter Panel ────────────────────────────────────────────────────────── -->
<div class="collapse <?= !empty($activeFilters) ? 'show' : '' ?>" id="filterPanel">
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-3">
            <form method="GET" action="/buyers" id="filterForm">
                <?php if (!empty($_GET['search'])): ?>
                <input type="hidden" name="search" value="<?= htmlspecialchars($_GET['search']) ?>">
                <?php endif; ?>
                <?php if (!empty($_GET['sort'])): ?>
                <input type="hidden" name="sort" value="<?= htmlspecialchars($_GET['sort']) ?>">
                <input type="hidden" name="dir"  value="<?= htmlspecialchars($_GET['dir'] ?? 'DESC') ?>">
                <?php endif; ?>
                <div class="row g-2 align-items-end">
                    <div class="col-6 col-md-4 col-lg-2">
                        <label class="form-label small fw-semibold mb-1">Country</label>
                        <input type="text" name="country" class="form-control form-control-sm"
                               placeholder="Any country" value="<?= htmlspecialchars($_GET['country'] ?? '') ?>">
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <label class="form-label small fw-semibold mb-1">Buyer Type</label>
                        <select name="type" class="form-select form-select-sm">
                            <option value="">All Types</option>
                            <?php foreach (['International','Domestic','Trading Company','Manufacturer','Distributor','Agent','End User'] as $opt): ?>
                            <option value="<?= $opt ?>" <?= ($_GET['type'] ?? '') === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <label class="form-label small fw-semibold mb-1">Priority</label>
                        <select name="priority" class="form-select form-select-sm">
                            <option value="">All Priorities</option>
                            <?php foreach (['High','Medium','Low'] as $opt): ?>
                            <option value="<?= $opt ?>" <?= ($_GET['priority'] ?? '') === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <label class="form-label small fw-semibold mb-1">Lead Source</label>
                        <select name="lead_source" class="form-select form-select-sm">
                            <option value="">All Sources</option>
                            <?php foreach (['Direct','Referral','Trade Fair / Exhibition','Online / Website','Agent','Cold Call','Import Data','Other'] as $opt): ?>
                            <option value="<?= $opt ?>" <?= ($_GET['lead_source'] ?? '') === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <label class="form-label small fw-semibold mb-1">Lead Status</label>
                        <select name="lead_status" class="form-select form-select-sm">
                            <option value="">All Statuses</option>
                            <?php foreach (['New Lead','Contacted','Follow-up Pending','Qualified','Proposal Sent','Negotiation','Won - Active Customer','Cold','Lost'] as $opt): ?>
                            <option value="<?= $opt ?>" <?= ($_GET['lead_status'] ?? '') === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <label class="form-label small fw-semibold mb-1">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All</option>
                            <option value="1" <?= ($_GET['status'] ?? '') === '1' ? 'selected' : '' ?>>Active</option>
                            <option value="0" <?= ($_GET['status'] ?? '') === '0' ? 'selected' : '' ?>>Inactive</option>
                            <option value="2" <?= ($_GET['status'] ?? '') === '2' ? 'selected' : '' ?>>Prospect</option>
                        </select>
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-filter me-1"></i>Apply Filters
                        </button>
                        <a href="/buyers<?= !empty($_GET['search']) ? '?search='.urlencode($_GET['search']) : '' ?>"
                           class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-times me-1"></i>Clear Filters
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── Results Info ────────────────────────────────────────────────────────── -->
<div class="d-flex justify-content-between align-items-center mb-2 px-1">
    <span class="text-muted">
        Showing <strong><?= $offset + 1 ?>–<?= min($offset + $limit, $total) ?></strong>
        of <strong><?= number_format($total) ?></strong> buyers
        <?php if (!empty($activeFilters)): ?>
        <span class="badge bg-light text-dark border ms-1"><?= count($activeFilters) ?> filter<?= count($activeFilters) > 1 ? 's' : '' ?> active</span>
        <?php endif; ?>
    </span>
    <span class="text-muted">
        Page <strong><?= $page ?></strong> of <strong><?= $totalPages ?></strong>
    </span>
</div>

<!-- ── Buyer Table ─────────────────────────────────────────────────────────── -->
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover table-bordered align-middle mb-0 w-100 text-nowrap" id="buyersTable">
            <thead class="table-dark sticky-top border-bottom">
                <tr>
                    <th class="ps-3 text-white fw-bold" style="width:40px">#</th>
                    <th class="text-white fw-bold" style="min-width:110px"><a href="<?= bSortUrl('buyer_code', $sort, $dir) ?>" class="text-white text-decoration-none">Code <?= bSortIcon('buyer_code', $sort, $dir) ?></a></th>
                    <th class="text-white fw-bold" style="min-width:180px"><a href="<?= bSortUrl('company_name', $sort, $dir) ?>" class="text-white text-decoration-none">Company <?= bSortIcon('company_name', $sort, $dir) ?></a></th>
                    <th class="text-white fw-bold" style="min-width:110px"><a href="<?= bSortUrl('country', $sort, $dir) ?>" class="text-white text-decoration-none">Country <?= bSortIcon('country', $sort, $dir) ?></a></th>
                    <th class="text-white fw-bold" style="min-width:130px"><a href="<?= bSortUrl('contact_person', $sort, $dir) ?>" class="text-white text-decoration-none">Contact <?= bSortIcon('contact_person', $sort, $dir) ?></a></th>
                    <th class="text-white fw-bold" style="min-width:160px"><a href="<?= bSortUrl('email', $sort, $dir) ?>" class="text-white text-decoration-none">Email <?= bSortIcon('email', $sort, $dir) ?></a></th>
                    <th class="text-white fw-bold" style="min-width:120px"><a href="<?= bSortUrl('mobile', $sort, $dir) ?>" class="text-white text-decoration-none">Mobile <?= bSortIcon('mobile', $sort, $dir) ?></a></th>
                    <th class="text-white fw-bold" style="min-width:110px"><a href="<?= bSortUrl('buyer_type', $sort, $dir) ?>" class="text-white text-decoration-none">Type <?= bSortIcon('buyer_type', $sort, $dir) ?></a></th>
                    <th class="text-white fw-bold" style="min-width:90px"><a href="<?= bSortUrl('priority', $sort, $dir) ?>" class="text-white text-decoration-none">Priority <?= bSortIcon('priority', $sort, $dir) ?></a></th>
                    <th class="text-white fw-bold" style="min-width:130px"><a href="<?= bSortUrl('lead_status', $sort, $dir) ?>" class="text-white text-decoration-none">Lead Status <?= bSortIcon('lead_status', $sort, $dir) ?></a></th>
                    <th class="text-white fw-bold" style="min-width:80px"><a href="<?= bSortUrl('status', $sort, $dir) ?>" class="text-white text-decoration-none">Status <?= bSortIcon('status', $sort, $dir) ?></a></th>
                    <th class="text-white fw-bold" style="min-width:120px"><a href="<?= bSortUrl('assigned_to', $sort, $dir) ?>" class="text-white text-decoration-none">Assigned <?= bSortIcon('assigned_to', $sort, $dir) ?></a></th>
                    <th class="text-white fw-bold" style="min-width:100px"><a href="<?= bSortUrl('last_contact', $sort, $dir) ?>" class="text-white text-decoration-none">Last Contact <?= bSortIcon('last_contact', $sort, $dir) ?></a></th>
                    <th class="text-white fw-bold" style="min-width:100px"><a href="<?= bSortUrl('next_followup', $sort, $dir) ?>" class="text-white text-decoration-none">Follow-up <?= bSortIcon('next_followup', $sort, $dir) ?></a></th>
                    <th class="text-white fw-bold text-center pe-3 d-print-none" style="min-width:90px">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($buyers)): ?>
                <tr>
                    <td colspan="15" class="text-center py-5 text-muted">
                        <i class="fas fa-users fa-3x mb-3 d-block opacity-25"></i>
                        <strong>No buyers found.</strong><br>
                        <span>Try adjusting your search or filters, or
                            <a href="/buyers/create" class="text-decoration-none">add a new buyer</a>.
                        </span>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($buyers as $i => $b): ?>
                <?php
                    $rowClass   = '';
                    $followup   = $b['next_followup'] ?? '';
                    $isOverdue  = ($followup && $followup < $today);
                    $isToday    = ($followup === $today);
                    if ($isOverdue) $rowClass = 'table-danger';
                    elseif ($isToday) $rowClass = 'table-warning';
                    $statusInt = (int)($b['status'] ?? 1);
                    $lsClass   = $leadStatusClasses[$b['lead_status'] ?? 'New Lead'] ?? 'primary';
                ?>
                <tr class="<?= $rowClass ?>">
                    <td class="ps-3 text-muted"><?= $offset + $i + 1 ?></td>
                    <td>
                        <a href="/buyers/<?= (int)$b['id'] ?>/edit" class="fw-semibold text-decoration-none text-primary">
                            <?= htmlspecialchars($b['buyer_code'] ?? '') ?>
                        </a>
                    </td>
                    <td>
                        <div class="fw-semibold"><?= htmlspecialchars($b['company_name'] ?? '') ?></div>
                        <?php if (!empty($b['website'])): ?>
                        <a href="<?= htmlspecialchars($b['website']) ?>" target="_blank" class="text-muted text-decoration-none" style="font-size:1rem">
                            <i class="fas fa-external-link-alt"></i> <?= htmlspecialchars(preg_replace('#^https?://#', '', $b['website'])) ?>
                        </a>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($b['country'] ?? '—') ?></td>
                    <td>
                        <div class="fw-semibold"><?= htmlspecialchars($b['contact_person'] ?? '') ?></div>
                        <?php if (!empty($b['designation'])): ?>
                        <div class="text-muted" style="font-size:.95rem"><?= htmlspecialchars($b['designation']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!empty($b['email'])): ?>
                        <a href="mailto:<?= htmlspecialchars($b['email']) ?>" class="text-decoration-none">
                            <?= htmlspecialchars($b['email']) ?>
                        </a>
                        <?php else: ?>
                        <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!empty($b['mobile'])): ?>
                        <a href="tel:<?= htmlspecialchars($b['mobile']) ?>" class="text-decoration-none">
                            <?= htmlspecialchars($b['mobile']) ?>
                        </a>
                        <?php if (!empty($b['whatsapp'])): ?>
                        <a href="https://wa.me/<?= preg_replace('/\D/','',$b['mobile']) ?>" target="_blank" class="ms-1 text-success" title="WhatsApp">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                        <?php endif; ?>
                        <?php else: ?>
                        <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php $tc = $typeClasses[$b['buyer_type'] ?? ''] ?? 'secondary'; ?>
                        <span class="badge bg-<?= $tc ?> badge-sm" style="font-size:.9rem">
                            <?= htmlspecialchars($b['buyer_type'] ?? '—') ?>
                        </span>
                    </td>
                    <td>
                        <?php $pc = $priorityClasses[$b['priority'] ?? ''] ?? 'secondary'; ?>
                        <?php if (!empty($b['priority'])): ?>
                        <span class="badge bg-<?= $pc ?>" style="font-size:.9rem">
                            <?php if ($b['priority'] === 'High'): ?><i class="fas fa-circle-dot me-1"></i><?php endif; ?>
                            <?= htmlspecialchars($b['priority']) ?>
                        </span>
                        <?php else: ?>
                        <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge bg-<?= $lsClass ?>" style="font-size:.9rem">
                            <?= htmlspecialchars($b['lead_status'] ?? 'New Lead') ?>
                        </span>
                    </td>
                    <td>
                        <?php $sc = $statusClasses[$statusInt] ?? 'secondary'; ?>
                        <span class="badge bg-<?= $sc ?>" style="font-size:.9rem">
                            <?= htmlspecialchars($statusLabels[$statusInt] ?? 'Unknown') ?>
                        </span>
                    </td>
                    <td class="text-muted"><?= htmlspecialchars($b['assigned_to'] ?? '—') ?></td>
                    <td class="text-muted">
                        <?= !empty($b['last_contact']) ? date('d M Y', strtotime($b['last_contact'])) : '—' ?>
                    </td>
                    <td>
                        <?php if (!empty($followup)): ?>
                        <span class="fw-semibold <?= $isOverdue ? 'text-danger' : ($isToday ? 'text-warning' : 'text-muted') ?>">
                            <?php if ($isOverdue): ?><i class="fas fa-exclamation-triangle me-1"></i><?php endif; ?>
                            <?php if ($isToday): ?><i class="fas fa-bell me-1 text-warning"></i><?php endif; ?>
                            <?= date('d M Y', strtotime($followup)) ?>
                        </span>
                        <?php else: ?>
                        <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center pe-3 position-static">
                        <div class="dropdown position-static">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle py-0 px-2"
                                    type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                    style="font-size:1rem">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                <li>
                                    <a class="dropdown-item" href="/buyers/<?= (int)$b['id'] ?>/edit">
                                        <i class="fas fa-edit me-2 text-primary"></i>View / Edit
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="/buyers/create?duplicate_from=<?= (int)$b['id'] ?>">
                                        <i class="fas fa-copy me-2 text-secondary"></i>Duplicate
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="/quotations/create?buyer_id=<?= (int)$b['id'] ?>">
                                        <i class="fas fa-file-invoice me-2 text-success"></i>Create Quotation
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="/buyers/<?= (int)$b['id'] ?>/edit#history">
                                        <i class="fas fa-history me-2 text-info"></i>History
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider my-1"></li>
                                <li>
                                    <form method="POST" action="/buyers/<?= (int)$b['id'] ?>/delete"
                                          onsubmit="return confirm('Delete <?= htmlspecialchars(addslashes($b['company_name'] ?? 'this buyer')) ?>? This cannot be undone.')">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="fas fa-trash-alt me-2"></i>Delete
                                        </button>
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

    <!-- ── Pagination ──────────────────────────────────────────────────────── -->
    <?php if ($totalPages > 1): ?>
    <div class="card-footer bg-white border-top py-2">
        <nav aria-label="Buyer pagination">
            <ul class="pagination pagination-sm mb-0 justify-content-center flex-wrap gap-1">
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= bPageUrl(1) ?>"><i class="fas fa-angle-double-left"></i></a>
                </li>
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= bPageUrl($page - 1) ?>"><i class="fas fa-angle-left"></i></a>
                </li>

                <?php
                $window = 2;
                $start  = max(1, $page - $window);
                $end    = min($totalPages, $page + $window);
                if ($start > 1): ?>
                <li class="page-item"><a class="page-link" href="<?= bPageUrl(1) ?>">1</a></li>
                <?php if ($start > 2): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif; ?>
                <?php endif; ?>

                <?php for ($p = $start; $p <= $end; $p++): ?>
                <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                    <a class="page-link" href="<?= bPageUrl($p) ?>"><?= $p ?></a>
                </li>
                <?php endfor; ?>

                <?php if ($end < $totalPages): ?>
                <?php if ($end < $totalPages - 1): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif; ?>
                <li class="page-item"><a class="page-link" href="<?= bPageUrl($totalPages) ?>"><?= $totalPages ?></a></li>
                <?php endif; ?>

                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= bPageUrl($page + 1) ?>"><i class="fas fa-angle-right"></i></a>
                </li>
                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= bPageUrl($totalPages) ?>"><i class="fas fa-angle-double-right"></i></a>
                </li>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>

<!-- ── Import Modal ────────────────────────────────────────────────────────── -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow border-0">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="importModalLabel">
                    <i class="fas fa-file-upload me-2"></i>Import Buyers from CSV
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="/buyers" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <input type="hidden" name="_action" value="import">
                <div class="modal-body">
                    <div class="alert alert-info small mb-3">
                        <i class="fas fa-info-circle me-1"></i>
                        <strong>CSV Format:</strong> Buyer Code, Company Name, Country, Contact Person,
                        Email, Mobile, Buyer Type, Priority<br>
                        <span class="text-muted">First row should be headers. Duplicate buyer codes will be skipped.</span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Select CSV File <span class="text-danger">*</span></label>
                        <input type="file" name="csv_file" class="form-control" accept=".csv,.txt" required>
                    </div>
                    <div class="text-end">
                        <a href="#" class="text-muted" id="downloadTemplate">
                            <i class="fas fa-download me-1"></i>Download template CSV
                        </a>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload me-1"></i>Import Now
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Download template CSV
document.getElementById('downloadTemplate').addEventListener('click', function(e) {
    e.preventDefault();
    const csv  = 'Buyer Code,Company Name,Country,Contact Person,Email,Mobile,Buyer Type,Priority\nBUY-001,Sample Company Ltd,India,John Doe,john@example.com,9876543210,International,High';
    const blob = new Blob([csv], {type:'text/csv'});
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement('a');
    a.href     = url;
    a.download = 'buyers_import_template.csv';
    a.click();
    URL.revokeObjectURL(url);
});
</script>
