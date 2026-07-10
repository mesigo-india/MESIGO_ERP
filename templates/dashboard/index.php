<?php
$widgets = $widgets ?? [];
$recentQuotations = $recentQuotations ?? [];
$recentBuyers = $recentBuyers ?? [];
$pendingFollowUps = $pendingFollowUps ?? [];
$recentDocuments = $recentDocuments ?? [];
$recentExportOrders = $recentExportOrders ?? [];
$statusSummary = $statusSummary ?? [];
$monthlyQuotations = $monthlyQuotations ?? [];
$monthlyExportDocuments = $monthlyExportDocuments ?? [];
$latestActivities = $latestActivities ?? [];
$statusLabels = [0 => 'Draft', 1 => 'Pending / Filed', 2 => 'Approved / Submitted', 3 => 'Rejected / LEO', 4 => 'Sent / Issued', 5 => 'Converted / Completed', 6 => 'Cancelled / Expired'];

// Grouping key widgets for clean display
$widgetConfig = [
    ['Total Buyers', $widgets['total_buyers'] ?? 0, 'fas fa-users-cog', 'text-primary', '/buyers'],
    ['Active Buyers', $widgets['active_buyers'] ?? 0, 'fas fa-user-friends', 'text-success', '/buyers?status=1'],
    ['Products catalog', $widgets['products'] ?? 0, 'fas fa-boxes', 'text-info', '/products'],
    ['Quotations', $widgets['quotations'] ?? 0, 'fas fa-file-invoice-dollar', 'text-warning', '/quotations'],
    ['Proforma Invoices', $widgets['proforma_invoices'] ?? 0, 'fas fa-file-invoice', 'text-secondary', '/proforma-invoices'],
    ['Commercial Invoices', $widgets['commercial_invoices'] ?? 0, 'fas fa-file-signature', 'text-danger', '/commercial-invoices'],
];
?>

<!-- Dashboard Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-0 fw-bold"><i class="fas fa-chart-line text-primary me-2"></i>Executive ERP Dashboard</h4>
        <p class="text-muted small mb-0">Overview of active export operations, procurement statistics, and document statuses.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="/quotations/create" class="btn btn-primary btn-sm shadow-sm"><i class="fas fa-plus me-1"></i> New Quotation</a>
        <a href="/export-documents" class="btn btn-outline-secondary btn-sm"><i class="fas fa-vault me-1"></i> Document Vault</a>
    </div>
</div>

<!-- Widgets Row -->
<div class="row g-3 mb-4">
    <?php foreach ($widgetConfig as $wc): ?>
        <div class="col-6 col-md-4 col-xl-2">
            <a href="<?= htmlspecialchars($wc[4]) ?>" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm hover-translate widget">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted small fw-semibold"><?= htmlspecialchars($wc[0]) ?></span>
                            <div class="widget-icon bg-light <?= htmlspecialchars($wc[3]) ?> p-2 rounded">
                                <i class="<?= htmlspecialchars($wc[2]) ?> fa-lg"></i>
                            </div>
                        </div>
                        <h4 class="fw-bold mb-0 text-dark"><?= number_format((float)$wc[1]) ?></h4>
                    </div>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
</div>

<!-- Detailed Data Grid Row 1 -->
<div class="row g-4 mb-4">
    <!-- Recent Quotations -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header border-0 bg-transparent pb-0">
                <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-receipt text-primary me-2"></i>Recent Quotations</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Quotation No</th>
                                <th>Buyer</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentQuotations as $row): ?>
                                <tr>
                                    <td class="fw-semibold text-dark"><?= escapeHtml($row['document_number']) ?></td>
                                    <td class="small"><?= escapeHtml($row['buyer_name'] ?? '') ?></td>
                                    <td class="text-end"><a href="/quotations/<?= (int) $row['id'] ?>" class="btn btn-sm btn-light border py-1 px-2 small">Open</a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Buyers -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header border-0 bg-transparent pb-0">
                <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-users-cog text-primary me-2"></i>Recently Added Buyers</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Buyer Company</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentBuyers as $buyer): ?>
                                <tr>
                                    <td class="fw-semibold text-dark"><?= escapeHtml($buyer['company_name']) ?></td>
                                    <td class="text-end"><a href="/buyers/<?= (int) $buyer['id'] ?>/edit" class="btn btn-sm btn-light border py-1 px-2 small">Manage</a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Data Grid Row 2 -->
<div class="row g-4 mb-4">
    <!-- Pending Follow-ups -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header border-0 bg-transparent pb-0">
                <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-clock text-primary me-2"></i>Pending Operations & Follow-ups</h6>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <?php foreach ($pendingFollowUps as $row): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-bottom">
                            <div>
                                <span class="d-block text-dark small fw-semibold"><?= escapeHtml($row['document_type']) ?></span>
                                <span class="text-muted small"><?= escapeHtml($row['document_number']) ?></span>
                            </div>
                            <span class="badge <?= ($row['status'] == 1) ? 'badge-pending' : 'badge-draft' ?>">
                                <?= escapeHtml($statusLabels[(int) $row['status']] ?? 'Status ' . $row['status']) ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

    <!-- Document Status Summary -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header border-0 bg-transparent pb-0">
                <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-check-circle text-primary me-2"></i>Document Status Summary</h6>
            </div>
            <div class="card-body">
                <?php foreach ($statusSummary as $row): 
                    $pct = min(100, (int) $row['total'] * 8);
                ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small text-muted fw-semibold"><?= escapeHtml($statusLabels[(int) $row['status']] ?? 'Status ' . $row['status']) ?></span>
                            <strong class="small"><?= (int) $row['total'] ?> docs</strong>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-primary" style="width: <?= $pct ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Data Grid Row 3 -->
<div class="row g-4">
    <!-- Latest Audit Log Activity -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header border-0 bg-transparent pb-0">
                <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-history text-primary me-2"></i>Latest System Activities</h6>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush" style="max-height: 250px; overflow-y: auto;">
                    <?php foreach ($latestActivities as $activity): ?>
                        <li class="list-group-item px-0 py-2 border-bottom">
                            <div class="d-flex justify-content-between">
                                <span class="fw-semibold text-dark small"><?= escapeHtml($activity['document_type']) ?> (<?= escapeHtml($activity['document_number']) ?>)</span>
                                <span class="text-muted small" style="font-size: 0.75rem;"><?= escapeHtml($activity['created_at']) ?></span>
                            </div>
                            <span class="d-block text-muted small mt-1">Status changed to <span class="badge bg-light text-dark border"><?= escapeHtml($statusLabels[(int) $activity['new_status']] ?? 'Status ' . $activity['new_status']) ?></span></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

    <!-- Quick Operations Navigation -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header border-0 bg-transparent pb-0">
                <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-directions text-primary me-2"></i>Quick Navigation Shortcuts</h6>
            </div>
            <div class="card-body">
                <div class="row g-2 mb-3">
                    <div class="col-md-6"><a class="btn btn-outline-primary btn-sm w-100" href="/quotations/create"><i class="fas fa-file-invoice-dollar me-1"></i> New Quotation</a></div>
                    <div class="col-md-6"><a class="btn btn-outline-primary btn-sm w-100" href="/buyers/create"><i class="fas fa-user-plus me-1"></i> New Buyer</a></div>
                    <div class="col-md-6"><a class="btn btn-outline-primary btn-sm w-100" href="/products/create"><i class="fas fa-box me-1"></i> New Product</a></div>
                    <div class="col-md-6"><a class="btn btn-outline-primary btn-sm w-100" href="/export-documents"><i class="fas fa-vault me-1"></i> Vault Vault</a></div>
                </div>
                <h6 class="fw-bold text-dark mb-2">Shortcuts</h6>
                <div class="row g-2">
                    <div class="col-md-4"><a class="card card-body p-2 text-center text-decoration-none bg-light border text-dark hover-translate" href="/commercial-invoices"><i class="fas fa-file-signature d-block mb-1"></i> Invoices</a></div>
                    <div class="col-md-4"><a class="card card-body p-2 text-center text-decoration-none bg-light border text-dark hover-translate" href="/shipping-bills"><i class="fas fa-passport d-block mb-1"></i> Shipping</a></div>
                    <div class="col-md-4"><a class="card card-body p-2 text-center text-decoration-none bg-light border text-dark hover-translate" href="/certificate-of-origins"><i class="fas fa-certificate d-block mb-1"></i> Certificates</a></div>
                </div>
            </div>
        </div>
    </div>
</div>