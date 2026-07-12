<?php
$quotation = $packingList ?? $quotation ?? [];
$items = $items ?? [];
$meta = $meta ?? [];
$statuses = $statuses ?? [];
$statusClasses = [0 => 'bg-secondary', 1 => 'bg-warning', 2 => 'bg-success', 3 => 'bg-danger', 4 => 'bg-info', 5 => 'bg-primary', 6 => 'bg-dark'];

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

        .packing-totals-box {
            border: 1px solid #ddd;
            padding: 12px;
            border-radius: 4px;
            background-color: #f8f9fa;
        }

        .packing-totals-title {
            font-size: 11px;
            text-transform: uppercase;
            color: #666;
            margin-bottom: 4px;
        }

        .packing-totals-value {
            font-size: 16px;
            font-weight: bold;
            color: #333;
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
                <h4 class="fw-bold tracking-wider text-dark mb-0">PACKING LIST</h4>
                <div class="text-muted small">Official Sourced Manifest & Loading Specification</div>
            </div>
            
            <div class="row g-3 mb-4">
                <div class="col-6">
                    <span class="d-block small text-muted text-uppercase">Manifest Details</span>
                    <strong class="d-block text-dark"><?= escapeHtml($quotation['document_number'] ?? '') ?></strong>
                    <span class="d-block small text-muted">Date: <?= escapeHtml($quotation['document_date'] ?? '') ?></span>
                    <span class="d-block small text-muted">Revision: Rev <?= (int) ($meta['revision'] ?? 0) ?></span>
                    <span class="d-block small text-muted">Status: <span class="badge <?= escapeHtml($statusClasses[(int) ($quotation['status'] ?? 0)] ?? 'bg-secondary') ?>"><?= escapeHtml($statuses[(int) ($quotation['status'] ?? 0)] ?? 'Unknown') ?></span></span>
                </div>
                <div class="col-6 text-end">
                    <span class="d-block small text-muted text-uppercase">Buyer / Client</span>
                    <strong class="d-block text-dark"><?= escapeHtml($quotation['buyer_name'] ?? '') ?></strong>
                    <span class="d-block small text-muted">Loading Port: <?= escapeHtml($quotation['loading_port_name'] ?? 'N/A') ?></span>
                    <span class="d-block small text-muted">Destination Port: <?= escapeHtml($quotation['delivery_port_name'] ?? 'N/A') ?></span>
                    <?php if (!empty($meta['container_no'])): ?>
                        <span class="d-block small text-muted">Container No: <?= escapeHtml($meta['container_no']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($meta['seal_no'])): ?>
                        <span class="d-block small text-muted">Seal No: <?= escapeHtml($meta['seal_no']) ?></span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="row g-3 mb-4 border-top border-bottom py-2 bg-light">
                <div class="col-6">
                    <span class="d-block small text-muted text-uppercase fw-semibold">Consignee Address</span>
                    <p class="text-dark small mb-0"><?= nl2br(escapeHtml($meta['consignee'] ?? 'Same as Buyer')) ?></p>
                </div>
                <div class="col-6 border-start">
                    <span class="d-block small text-muted text-uppercase fw-semibold">Notify Party</span>
                    <p class="text-dark small mb-0"><?= nl2br(escapeHtml($meta['notify_party'] ?? 'Same as Buyer')) ?></p>
                </div>
            </div>

            <!-- Items table -->
            <div class="table-responsive mb-4">
                <table class="table table-bordered align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th style="width:40px">#</th>
                            <th>Description of Goods / Packaging</th>
                            <th>HS Code</th>
                            <th class="text-end">No. of Packages</th>
                            <th class="text-end">Quantity (Pcs)</th>
                            <th class="text-end">Net Weight (KG)</th>
                            <th class="text-end">Gross Weight (KG)</th>
                            <th class="text-end">Measurement (CBM)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $totalPackages = 0;
                        $totalQty = 0;
                        $totalNet = 0.0;
                        $totalGross = 0.0;
                        $totalCbm = 0.0;
                        
                        foreach ($items as $index => $item): 
                            $quality = json_decode((string) ($item['quality'] ?? ''), true) ?: [];
                            
                            $pkgCount = (int) ($item['no_of_bags'] ?? $item['quantity'] ?? 0);
                            $qtyVal = (float) ($quality['total_qty'] ?? ($pkgCount * ($quality['units_per_package'] ?? 1.0)));
                            $netWeight = (float) ($item['net_weight'] ?? 0.0);
                            $grossWeight = (float) ($item['gross_weight'] ?? 0.0);
                            $cbmVal = (float) ($quality['cbm'] ?? 0.0);
                            
                            $totalPackages += $pkgCount;
                            $totalQty += $qtyVal;
                            $totalNet += $netWeight;
                            $totalGross += $grossWeight;
                            $totalCbm += $cbmVal;
                            
                            // Load grade & origin names
                            $gradeName = '';
                            if (!empty($item['grade_id'])) {
                                $stmtG = $dbInst->prepare("SELECT name FROM product_grades WHERE id = :id LIMIT 1");
                                $stmtG->execute(['id' => (int)$item['grade_id']]);
                                $gradeName = $stmtG->fetchColumn() ?: '';
                            }
                            $originName = '';
                            if (!empty($item['origin_id'])) {
                                $stmtO = $dbInst->prepare("SELECT name FROM product_origins WHERE id = :id LIMIT 1");
                                $stmtO->execute(['id' => (int)$item['origin_id']]);
                                $originName = $stmtO->fetchColumn() ?: '';
                            }
                        ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td>
                                    <strong class="d-block text-dark"><?= escapeHtml($item['product_name'] ?? '') ?></strong>
                                    <div class="small text-muted" style="font-size: 0.8rem;">
                                        <?php if (!empty($gradeName)): ?>Grade: <?= escapeHtml($gradeName) ?> | <?php endif; ?>
                                        <?php if (!empty($originName)): ?>Origin: <?= escapeHtml($originName) ?> | <?php endif; ?>
                                        Type: <?= escapeHtml($item['packing_type_name'] ?? 'N/A') ?> 
                                        <?php if (!empty($quality['dimensions'])): ?>| Dim: <?= escapeHtml($quality['dimensions']) ?><?php endif; ?>
                                    </div>
                                    <?php if (!empty($item['item_remarks'])): ?>
                                        <div class="small text-muted italic" style="font-size: 0.75rem;">Remarks: <?= escapeHtml($item['item_remarks']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><?= escapeHtml($item['hsn_code'] ?? '') ?></td>
                                <td class="text-end fw-bold"><?= number_format((float) $pkgCount, 0) ?></td>
                                <td class="text-end"><?= number_format((float) $qtyVal, 3) ?> <?= escapeHtml($item['unit_code'] ?? '') ?></td>
                                <td class="text-end"><?= number_format($netWeight, 3) ?></td>
                                <td class="text-end"><?= number_format($grossWeight, 3) ?></td>
                                <td class="text-end"><?= number_format($cbmVal, 4) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="bg-light fw-bold text-dark">
                        <tr>
                            <td colspan="3">Grand Total</td>
                            <td class="text-end"><?= number_format($totalPackages, 0) ?></td>
                            <td class="text-end"><?= number_format($totalQty, 3) ?></td>
                            <td class="text-end"><?= number_format($totalNet, 3) ?></td>
                            <td class="text-end"><?= number_format($totalGross, 3) ?></td>
                            <td class="text-end"><?= number_format($totalCbm, 4) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Packing Totals Quick Panels -->
            <div class="row g-3 mb-4">
                <div class="col-3">
                    <div class="packing-totals-box">
                        <div class="packing-totals-title">Total Packages</div>
                        <div class="packing-totals-value"><?= number_format($totalPackages, 0) ?> Packages</div>
                    </div>
                </div>
                <div class="col-3">
                    <div class="packing-totals-box">
                        <div class="packing-totals-title">Total Net Weight</div>
                        <div class="packing-totals-value"><?= number_format($totalNet, 3) ?> KG</div>
                    </div>
                </div>
                <div class="col-3">
                    <div class="packing-totals-box">
                        <div class="packing-totals-title">Total Gross Weight</div>
                        <div class="packing-totals-value"><?= number_format($totalGross, 3) ?> KG</div>
                    </div>
                </div>
                <div class="col-3">
                    <div class="packing-totals-box">
                        <div class="packing-totals-title">Total Volume (CBM)</div>
                        <div class="packing-totals-value"><?= number_format($totalCbm, 4) ?> m³</div>
                    </div>
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-12">
                    <strong class="text-dark small">Marks & Numbers Summary:</strong>
                    <p class="text-muted small mt-1"><?= escapeHtml($meta['marks_numbers'] ?? 'No special shipping marks.') ?></p>
                </div>
                <div class="col-12 mt-2">
                    <strong class="text-dark small">Notes / Remarks:</strong>
                    <p class="text-muted small mt-1"><?= nl2br(escapeHtml($quotation['remarks'] ?? 'No special instructions.')) ?></p>
                </div>
            </div>
            
            <!-- Containers Recommendation -->
            <?php if (!empty($quotation['estimated_containers_json'])): ?>
                <?php $containers = json_decode($quotation['estimated_containers_json'], true); ?>
                <?php if (!empty($containers['recommendation'])): ?>
                    <?php $rec = $containers['recommendation']; ?>
                    <div class="mt-4 p-3 bg-light rounded border border-warning">
                        <h6 class="text-warning fw-bold mb-2"><i class="fa fa-ship"></i> Container Loading Recommendation</h6>
                        <div class="row g-3">
                            <div class="col-4">
                                <div class="card shadow-sm border-0 p-3 bg-white">
                                    <span class="d-block small text-muted">Required Containers</span>
                                    <strong class="d-block text-dark"><?= escapeHtml($rec['name']) ?></strong>
                                    <span class="d-block small">Qty: <span class="badge bg-primary"><?= (int) $rec['count'] ?></span></span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="card shadow-sm border-0 p-3 bg-white">
                                    <span class="d-block small text-muted">Weight Stuffing Utilization</span>
                                    <strong class="d-block text-dark"><?= number_format($rec['utilization']['weight_percent'], 2) ?>%</strong>
                                    <div class="progress mt-1" style="height: 6px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?= $rec['utilization']['weight_percent'] ?>%"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="card shadow-sm border-0 p-3 bg-white">
                                    <span class="d-block small text-muted">Volume CBM Utilization</span>
                                    <strong class="d-block text-dark"><?= number_format($rec['utilization']['volume_percent'], 2) ?>%</strong>
                                    <div class="progress mt-1" style="height: 6px;">
                                        <div class="progress-bar bg-info" role="progressbar" style="width: <?= $rec['utilization']['volume_percent'] ?>%"></div>
                                    </div>
                                </div>
                            </div>
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
