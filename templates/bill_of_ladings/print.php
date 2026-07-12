<?php
$bill = $bill ?? [];
$meta = $meta ?? [];
$items = $items ?? [];
$statuses = $statuses ?? [];
$statusClasses = [0 => 'bg-secondary', 1 => 'bg-info', 2 => 'bg-warning', 3 => 'bg-success'];
$isDraft = (int)($bill['status'] ?? 0) === 0;
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
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #333; background: #fff; padding: 20px; }
        .watermark-container {
            position: relative;
            overflow: hidden;
        }
        <?php if ($isDraft): ?>
        .watermark-container::after {
            content: "DRAFT ONLY - NOT FOR CARRIAGE";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 3.5rem;
            font-weight: 900;
            color: rgba(220, 53, 69, 0.12);
            white-space: nowrap;
            pointer-events: none;
            z-index: 1000;
            letter-spacing: 2px;
        }
        <?php endif; ?>
        @media print {
            body { background: none; padding: 0; }
            @page { margin: 1cm; size: portrait; }
        }
    </style>
</head>
<body>

<div class="card watermark-container border-0">
    <div class="card-body p-0">
        <div class="text-center mb-4">
            <h4 class="fw-bold tracking-wider text-dark mb-0">BILL OF LADING</h4>
            <div class="text-muted small">Liner Shipping Document (Carrier Cargo Contract)</div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6">
                <span class="d-block small text-muted text-uppercase">B/L Details</span>
                <strong class="d-block text-dark"><?= escapeHtml($meta['bl_number'] ?? ($bill['document_number'] ?? '')) ?></strong>
                <span class="d-block small text-muted">Status: <span class="badge <?= escapeHtml($statusClasses[(int) ($bill['status'] ?? 0)] ?? 'bg-secondary') ?>"><?= escapeHtml($statuses[(int) ($bill['status'] ?? 0)] ?? 'Unknown') ?></span></span>
                <span class="d-block small text-muted">Master B/L: <?= escapeHtml($meta['master_bl'] ?? 'N/A') ?></span>
                <span class="d-block small text-muted">House B/L: <?= escapeHtml($meta['house_bl'] ?? 'N/A') ?></span>
            </div>
            <div class="col-6 text-end">
                <span class="d-block small text-muted text-uppercase">Carrier / Liner Agency</span>
                <strong class="d-block text-dark"><?= escapeHtml($meta['carrier'] ?? 'N/A') ?></strong>
                <span class="d-block small text-muted">Vessel: <?= escapeHtml($meta['vessel'] ?? 'N/A') ?></span>
                <span class="d-block small text-muted">Voyage: <?= escapeHtml($meta['voyage'] ?? 'N/A') ?></span>
            </div>
        </div>

        <!-- Exporter / Consignee Address Section -->
        <div class="row g-3 mb-4 border-top pt-3">
            <div class="col-4">
                <div class="p-2 border rounded bg-light" style="min-height: 100px;">
                    <strong class="d-block text-dark small text-uppercase mb-1">Shipper (Exporter)</strong>
                    <span class="small text-muted d-block" style="white-space: pre-wrap;"><?= escapeHtml($meta['exporter_details'] ?? '') ?></span>
                </div>
            </div>
            <div class="col-4">
                <div class="p-2 border rounded bg-light" style="min-height: 100px;">
                    <strong class="d-block text-dark small text-uppercase mb-1">Consignee (Delivery)</strong>
                    <span class="small text-muted d-block" style="white-space: pre-wrap;"><?= escapeHtml($meta['consignee'] ?? '') ?></span>
                </div>
            </div>
            <div class="col-4">
                <div class="p-2 border rounded bg-light" style="min-height: 100px;">
                    <strong class="d-block text-dark small text-uppercase mb-1">Notify Party</strong>
                    <span class="small text-muted d-block" style="white-space: pre-wrap;"><?= escapeHtml($meta['notify_party'] ?? '') ?></span>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4 border-top border-bottom py-2 bg-light">
            <div class="col-3"><span class="d-block text-muted small">Port of Loading</span><span class="fw-semibold text-dark small"><?= escapeHtml($meta['pol'] ?? ($bill['loading_port_name'] ?? 'N/A')) ?></span></div>
            <div class="col-3"><span class="d-block text-muted small">Port of Discharge</span><span class="fw-semibold text-dark small"><?= escapeHtml($meta['pod'] ?? ($bill['delivery_port_name'] ?? 'N/A')) ?></span></div>
            <div class="col-3"><span class="d-block text-muted small">Freight Status</span><span class="fw-semibold text-dark small"><?= escapeHtml($meta['freight'] ?? 'Prepaid') ?></span></div>
            <div class="col-3"><span class="d-block text-muted small">Estimated ETD / ETA</span><span class="fw-semibold text-dark small"><?= escapeHtml($meta['etd'] ?? 'N/A') ?> / <?= escapeHtml($meta['eta'] ?? 'N/A') ?></span></div>
        </div>

        <!-- Items table -->
        <div class="table-responsive mb-4">
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th style="width:40px">#</th>
                        <th>Product Description</th>
                        <th>HS Code</th>
                        <th>Package Type</th>
                        <th class="text-end">No of Bags/Cartons</th>
                        <th class="text-end">Net Weight (KG)</th>
                        <th class="text-end">Gross Weight (KG)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $index => $item): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td class="fw-semibold text-dark"><?= escapeHtml($item['product_name'] ?? '') ?></td>
                            <td><?= escapeHtml($item['hsn_code'] ?? '') ?></td>
                            <td><?= escapeHtml($item['packing_type_name'] ?? '') ?></td>
                            <td class="text-end"><?= number_format((float) ($item['quantity'] ?? 0), 3) ?></td>
                            <td class="text-end"><?= number_format((float) ($item['net_weight'] ?? 0), 3) ?></td>
                            <td class="text-end"><?= number_format((float) ($item['gross_weight'] ?? 0), 3) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="row mb-4 border-top pt-3">
            <div class="col-6 mb-3">
                <span class="d-block text-muted small">Container Details:</span>
                <strong class="d-block text-dark"><?= escapeHtml($meta['container'] ?? 'N/A') ?></strong>
                <span class="d-block text-muted small mt-2">Seal Number:</span>
                <strong class="d-block text-dark"><?= escapeHtml($meta['seal'] ?? 'N/A') ?></strong>
            </div>
            <div class="col-6 text-end">
                <div class="p-3 border rounded bg-light d-inline-block text-start" style="min-width: 250px;">
                    <span class="d-block small text-muted text-uppercase mb-2">Carrier Issuing Office / Agent</span>
                    <div style="min-height: 60px;"></div>
                    <span class="d-block border-top pt-1 small text-center text-muted">Authorized Signature & Stamp</span>
                </div>
            </div>
        </div>

        <div>
            <strong>Remarks:</strong><br>
            <div class="small text-muted"><?= nl2br(escapeHtml($bill['remarks'] ?? '')) ?></div>
        </div>
    </div>
</div>

<script>
window.onload = function() {
    window.print();
};
</script>
</body>
</html>
