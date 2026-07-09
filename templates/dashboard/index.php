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
$cards = [
    ['Total Buyers', $widgets['total_buyers'] ?? 0, 'bg-primary', '/buyers'],
    ['Active Buyers', $widgets['active_buyers'] ?? 0, 'bg-success', '/buyers?status=1'],
    ['Products', $widgets['products'] ?? 0, 'bg-info', '/products'],
    ['Quotations', $widgets['quotations'] ?? 0, 'bg-warning', '/quotations'],
    ['Proforma Invoices', $widgets['proforma_invoices'] ?? 0, 'bg-secondary', '/proforma-invoices'],
    ['Commercial Invoices', $widgets['commercial_invoices'] ?? 0, 'bg-dark', '/commercial-invoices'],
    ['Packing Lists', $widgets['packing_lists'] ?? 0, 'bg-primary', '/packing-lists'],
    ['Shipping Bills', $widgets['shipping_bills'] ?? 0, 'bg-success', '/shipping-bills'],
    ['Bills of Lading', $widgets['bills_of_lading'] ?? 0, 'bg-info', '/bill-of-ladings'],
    ['Pending Documents', $widgets['pending_documents'] ?? 0, 'bg-danger', '/export-documents'],
    ['This Month Exports', $widgets['this_month_exports'] ?? 0, 'bg-warning', '/export-documents'],
    ['Recent Activities', $widgets['recent_activities'] ?? 0, 'bg-secondary', '/export-documents'],
];
?>
<div class="page-header">
    <h1>ERP Dashboard</h1>
</div>

<div class="row">
    <?php foreach ($cards as $card): ?>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <a href="<?= escapeHtml($card[3]) ?>" class="text-decoration-none">
                <div class="card text-white <?= escapeHtml($card[2]) ?> h-100">
                    <div class="card-body">
                        <div class="small"><?= escapeHtml($card[0]) ?></div>
                        <div class="h3 mb-0"><?= (int) $card[1] ?></div>
                    </div>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
</div>

<div class="row">
    <div class="col-lg-6 mb-3">
        <div class="card h-100"><div class="card-body"><h5>Recent Quotations</h5><?php foreach ($recentQuotations as $row): ?><div class="d-flex justify-content-between border-bottom py-2"><span><?= escapeHtml($row['document_number']) ?> - <?= escapeHtml($row['buyer_name'] ?? '') ?></span><a href="/quotations/<?= (int) $row['id'] ?>">Open</a></div><?php endforeach; ?></div></div>
    </div>
    <div class="col-lg-6 mb-3">
        <div class="card h-100"><div class="card-body"><h5>Recent Buyers</h5><?php foreach ($recentBuyers as $buyer): ?><div class="d-flex justify-content-between border-bottom py-2"><span><?= escapeHtml($buyer['company_name']) ?></span><a href="/buyers/<?= (int) $buyer['id'] ?>/edit">Open</a></div><?php endforeach; ?></div></div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mb-3"><div class="card h-100"><div class="card-body"><h5>Pending Follow-ups</h5><?php foreach ($pendingFollowUps as $row): ?><div class="border-bottom py-2"><?= escapeHtml($row['document_type']) ?>: <?= escapeHtml($row['document_number']) ?> <span class="badge bg-warning"><?= escapeHtml($statusLabels[(int) $row['status']] ?? 'Status ' . $row['status']) ?></span></div><?php endforeach; ?></div></div></div>
    <div class="col-lg-6 mb-3"><div class="card h-100"><div class="card-body"><h5>Recent Documents</h5><?php foreach ($recentDocuments as $row): ?><div class="border-bottom py-2"><?= escapeHtml($row['document_type']) ?>: <?= escapeHtml($row['document_number']) ?> - <?= escapeHtml($row['buyer_name'] ?? '') ?></div><?php endforeach; ?></div></div></div>
</div>

<div class="row">
    <div class="col-lg-6 mb-3"><div class="card h-100"><div class="card-body"><h5>Recent Export Orders</h5><?php foreach ($recentExportOrders as $row): ?><div class="border-bottom py-2"><?= escapeHtml($row['document_type']) ?>: <?= escapeHtml($row['document_number']) ?></div><?php endforeach; ?></div></div></div>
    <div class="col-lg-6 mb-3"><div class="card h-100"><div class="card-body"><h5>Document Status Summary</h5><?php foreach ($statusSummary as $row): ?><div class="mb-2"><div class="d-flex justify-content-between"><span><?= escapeHtml($statusLabels[(int) $row['status']] ?? 'Status ' . $row['status']) ?></span><strong><?= (int) $row['total'] ?></strong></div><div class="progress"><div class="progress-bar" style="width: <?= min(100, (int) $row['total'] * 10) ?>%"></div></div></div><?php endforeach; ?></div></div></div>
</div>

<div class="row">
    <div class="col-lg-6 mb-3"><div class="card h-100"><div class="card-body"><h5>Monthly Quotations</h5><?php foreach ($monthlyQuotations as $row): ?><div class="d-flex align-items-center mb-2"><div class="me-2" style="width:80px"><?= escapeHtml($row['month']) ?></div><div class="progress flex-grow-1"><div class="progress-bar bg-warning" style="width: <?= min(100, (int) $row['total'] * 10) ?>%"></div></div><strong class="ms-2"><?= (int) $row['total'] ?></strong></div><?php endforeach; ?></div></div></div>
    <div class="col-lg-6 mb-3"><div class="card h-100"><div class="card-body"><h5>Monthly Export Documents</h5><?php foreach ($monthlyExportDocuments as $row): ?><div class="d-flex align-items-center mb-2"><div class="me-2" style="width:80px"><?= escapeHtml($row['month']) ?></div><div class="progress flex-grow-1"><div class="progress-bar bg-success" style="width: <?= min(100, (int) $row['total'] * 10) ?>%"></div></div><strong class="ms-2"><?= (int) $row['total'] ?></strong></div><?php endforeach; ?></div></div></div>
</div>

<div class="row">
    <div class="col-lg-6 mb-3"><div class="card h-100"><div class="card-body"><h5>Latest Activities</h5><?php foreach ($latestActivities as $activity): ?><div class="small border-bottom py-2"><?= escapeHtml($activity['created_at']) ?> - <?= escapeHtml($activity['document_type']) ?> <?= escapeHtml($activity['document_number']) ?> changed to <?= escapeHtml($statusLabels[(int) $activity['new_status']] ?? 'Status ' . $activity['new_status']) ?></div><?php endforeach; ?></div></div></div>
    <div class="col-lg-6 mb-3"><div class="card h-100"><div class="card-body"><h5>Quick Actions</h5><div class="row g-2"><div class="col-md-6"><a class="btn btn-outline-primary w-100" href="/quotations/create">New Quotation</a></div><div class="col-md-6"><a class="btn btn-outline-primary w-100" href="/buyers/create">New Buyer</a></div><div class="col-md-6"><a class="btn btn-outline-primary w-100" href="/products/create">New Product</a></div><div class="col-md-6"><a class="btn btn-outline-primary w-100" href="/export-documents">Document Vault</a></div></div><h5 class="mt-4">Quick Navigation</h5><div class="row g-2"><div class="col-md-4"><a class="card card-body text-center text-decoration-none" href="/commercial-invoices">Invoices</a></div><div class="col-md-4"><a class="card card-body text-center text-decoration-none" href="/shipping-bills">Shipping</a></div><div class="col-md-4"><a class="card card-body text-center text-decoration-none" href="/certificate-of-origins">Certificates</a></div></div></div></div></div>
</div>