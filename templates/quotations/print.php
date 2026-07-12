<?php
$quotation = $quotation ?? [];
$items = $items ?? [];
$meta = $meta ?? [];
$statuses = $statuses ?? [];
$statusClasses = [0 => 'bg-secondary', 1 => 'bg-warning', 2 => 'bg-success', 3 => 'bg-danger', 4 => 'bg-info', 5 => 'bg-primary', 6 => 'bg-dark'];
$totals = $meta['totals'] ?? [];

$dbInst = App\Core\Database::getInstance();
$company = $dbInst->query("SELECT * FROM company WHERE status = 1 ORDER BY id ASC LIMIT 1")->fetch(\PDO::FETCH_ASSOC) ?: [];

$mode = $_GET['mode'] ?? 'plain';
$letterheadSrc = $_GET['src'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title) ?></title>
    <!-- Include Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #333; background: #fff; }
        .letterhead-print-container { width: 100%; max-width: 800px; margin: 0 auto; padding: 20px; position: relative; }
        
        /* Letterhead Overlay Mode styles */
        .letterhead-print-container.use-letterhead {
            margin-top: 150px; /* Leave space for physical letterhead */
        }
        
        .letterhead-overlay-img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            z-index: -1;
            display: none;
        }
        
        .letterhead-print-container.use-letterhead .letterhead-overlay-img {
            display: block;
        }

        @media print {
            body { background: none; }
            .letterhead-print-container { padding: 0; }
            @page { margin: 1cm; size: portrait; }
        }
    </style>
</head>
<body>

<div class="letterhead-print-container <?= $mode === 'letterhead' ? 'use-letterhead' : '' ?>" id="printContainer">
    <?php if ($mode === 'letterhead' && !empty($letterheadSrc)): ?>
        <img src="<?= htmlspecialchars($letterheadSrc) ?>" class="letterhead-overlay-img" id="letterheadOverlayImg" alt="Letterhead">
    <?php endif; ?>

    <!-- Print Header -->
    <?php require_once APP_ROOT . '/includes/document_print_header.php'; ?>

    <div class="letterhead-content-body card border-0">
        <div class="card-body p-0">
            <div class="text-center mb-4">
                <h4 class="fw-bold tracking-wider text-dark mb-0">EXPORT QUOTATION</h4>
                <div class="text-muted small">Provisional Agreement Layout</div>
            </div>
            
            <div class="row g-3 mb-4">
                <div class="col-6">
                    <span class="d-block small text-muted text-uppercase">Quotation Summary</span>
                    <strong class="d-block text-dark"><?= escapeHtml($quotation['document_number']) ?></strong>
                    <span class="d-block small text-muted">Date: <?= escapeHtml($quotation['document_date']) ?></span>
                    <span class="d-block small text-muted">Revision: Rev <?= (int) ($meta['revision'] ?? 0) ?></span>
                    <span class="d-block small text-muted">Status: <span class="badge <?= escapeHtml($statusClasses[(int) $quotation['status']] ?? 'bg-secondary') ?>"><?= escapeHtml($statuses[(int) $quotation['status']] ?? 'Unknown') ?></span></span>
                </div>
                <div class="col-6 text-end">
                    <span class="d-block small text-muted text-uppercase">Buyer Details</span>
                    <strong class="d-block text-dark"><?= escapeHtml($quotation['buyer_name'] ?? '') ?></strong>
                    <span class="d-block small text-muted">Valid Until: <?= escapeHtml($meta['valid_until'] ?? '') ?></span>
                    <span class="d-block small text-muted">Currency: <?= escapeHtml($quotation['currency_code'] ?? '') ?></span>
                    <span class="d-block small text-muted">Incoterm: <?= escapeHtml($quotation['incoterm_code'] ?? '') ?></span>
                </div>
            </div>
            
            <div class="row g-3 mb-4 border-top border-bottom py-2 bg-light">
                <div class="col-3"><span class="d-block text-muted small">Loading Port</span><span class="fw-semibold text-dark small"><?= escapeHtml($quotation['loading_port_name'] ?? 'N/A') ?></span></div>
                <div class="col-3"><span class="d-block text-muted small">Delivery Port</span><span class="fw-semibold text-dark small"><?= escapeHtml($quotation['delivery_port_name'] ?? 'N/A') ?></span></div>
                <div class="col-3"><span class="d-block text-muted small">Payment Term</span><span class="fw-semibold text-dark small"><?= escapeHtml($quotation['payment_term_name'] ?? 'N/A') ?></span></div>
                <div class="col-3"><span class="d-block text-muted small">Shipment Term</span><span class="fw-semibold text-dark small"><?= escapeHtml($meta['shipment_term'] ?? ($quotation['shipment_type'] ?? 'N/A')) ?></span></div>
            </div>

            <!-- Items table -->
            <div class="table-responsive mb-4">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th style="width:40px">#</th>
                            <th>Product</th>
                            <th>HS Code</th>
                            <th>Packaging</th>
                            <th class="text-end">Qty</th>
                            <th>Unit</th>
                            <th class="text-end">Rate</th>
                            <th class="text-end">Discount</th>
                            <th class="text-end">GST</th>
                            <th class="text-end">Net Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $index => $item): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td class="fw-semibold text-dark"><?= escapeHtml($item['product_name'] ?? '') ?></td>
                                <td><?= escapeHtml($item['hsn_code'] ?? '') ?></td>
                                <td><?= escapeHtml($item['packing_type_name'] ?? '') ?></td>
                                <td class="text-end"><?= number_format((float) $item['quantity'], 3) ?></td>
                                <td><?= escapeHtml($item['unit_code'] ?? '') ?></td>
                                <td class="text-end"><?= number_format((float) $item['rate'], 4) ?></td>
                                <td class="text-end"><?= number_format((float) $item['discount_amount'], 2) ?></td>
                                <td class="text-end"><?= number_format((float) $item['tax_amount'], 2) ?></td>
                                <td class="text-end fw-semibold text-dark"><?= number_format((float) $item['net_amount'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="row mb-4">
                <div class="col-7">
                    <strong class="text-dark small">Notes / Remarks:</strong>
                    <p class="text-muted small mt-1"><?= nl2br(escapeHtml($quotation['remarks'] ?? 'No special instructions.')) ?></p>
                </div>
                <div class="col-5">
                    <table class="table table-sm text-nowrap">
                        <tr><th>Subtotal</th><td class="text-end"><?= number_format((float) ($totals['subtotal'] ?? 0), 2) ?></td></tr>
                        <tr><th>Discount</th><td class="text-end"><?= number_format((float) ($totals['discount'] ?? 0), 2) ?></td></tr>
                        <tr class="table-primary border-top border-2"><th>Grand Total</th><td class="text-end fw-bold"><strong><?= number_format((float) (($totals['subtotal'] ?? 0) - ($totals['discount'] ?? 0)), 2) ?></strong></td></tr>
                    </table>
                </div>
            </div>
            
            <!-- Containers Recommendation -->
            <?php if (!empty($quotation['estimated_containers_json'])): ?>
                <?php $containers = json_decode($quotation['estimated_containers_json'], true); ?>
                <?php if (!empty($containers)): ?>
                    <div class="mt-4 p-3 bg-light rounded border border-warning">
                        <h6 class="text-warning fw-bold"><i class="fa fa-ship"></i> Container Loading Recommendations</h6>
                        <div class="row g-3 mt-1">
                            <?php foreach ($containers as $c): ?>
                                <div class="col-4">
                                    <div class="card shadow-sm border-0 p-3 bg-white">
                                        <span class="d-block small text-muted">Container Allocation</span>
                                        <strong class="d-block text-dark"><?= escapeHtml($c['container_type']) ?></strong>
                                        <span class="d-block small">Qty: <span class="badge bg-primary"><?= (int) $c['container_count'] ?></span></span>
                                        <span class="d-block small">Util: <?= number_format($c['utilization_percent'], 1) ?>%</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Print Footer -->
    <?php require_once APP_ROOT . '/includes/document_print_footer.php'; ?>
</div>

<script>
window.onload = function() {
    window.print();
};
</script>
</body>
</html>
