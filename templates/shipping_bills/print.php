<?php
$shippingBill = $shippingBill ?? [];
$meta = $meta ?? [];
$items = $items ?? [];
$statuses = $statuses ?? [];
$statusClasses = [0 => 'bg-secondary', 1 => 'bg-warning', 2 => 'bg-info', 3 => 'bg-primary', 5 => 'bg-success', 6 => 'bg-danger'];
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
        @media print {
            body { background: none; padding: 0; }
            @page { margin: 1cm; size: portrait; }
        }
    </style>
</head>
<body>

<div class="card border-0">
    <div class="card-body p-0">
        <div class="text-center mb-4">
            <h2>SHIPPING BILL</h2>
            <div class="text-muted">Official Export Declaration Manifest</div>
        </div>
        
        <div class="row mb-3">
            <div class="col-6">
                <strong>Shipping Bill No:</strong> <?= escapeHtml($meta['shipping_bill_no'] ?? ($shippingBill['document_number'] ?? '')) ?><br>
                <strong>Shipping Bill Date:</strong> <?= escapeHtml($meta['shipping_bill_date'] ?? ($shippingBill['document_date'] ?? '')) ?><br>
                <strong>Status:</strong> <span class="badge <?= escapeHtml($statusClasses[(int) ($shippingBill['status'] ?? 0)] ?? 'bg-secondary') ?>"><?= escapeHtml($statuses[(int) ($shippingBill['status'] ?? 0)] ?? 'Unknown') ?></span><br>
                <strong>Buyer:</strong> <?= escapeHtml($shippingBill['buyer_name'] ?? '') ?>
            </div>
            <div class="col-6">
                <strong>Port:</strong> <?= escapeHtml($meta['port'] ?? '') ?><br>
                <strong>CHA:</strong> <?= escapeHtml($meta['cha'] ?? '') ?><br>
                <strong>Custom House:</strong> <?= escapeHtml($meta['custom_house'] ?? '') ?><br>
                <strong>Container Details:</strong> <?= escapeHtml($meta['container_details'] ?? '') ?>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-3"><strong>LEO:</strong> <?= escapeHtml($meta['leo'] ?? '') ?></div>
            <div class="col-3"><strong>Drawback:</strong> <?= escapeHtml($meta['drawback'] ?? '') ?></div>
            <div class="col-3"><strong>Scheme:</strong> <?= escapeHtml($meta['scheme'] ?? '') ?></div>
            <div class="col-3"><strong>Exporter:</strong> <?= escapeHtml($meta['exporter_details'] ?? '') ?></div>
        </div>
        
        <div class="table-responsive mb-3">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>HS Code</th>
                        <th>Package</th>
                        <th class="text-end">Qty</th>
                        <th class="text-end">Net Wt</th>
                        <th class="text-end">Gross Wt</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $index => $item): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= escapeHtml($item['product_name'] ?? '') ?></td>
                            <td><?= escapeHtml($item['hsn_code'] ?? '') ?></td>
                            <td><?= escapeHtml($item['packing_type_name'] ?? '') ?></td>
                            <td class="text-end"><?= number_format((float) ($item['quantity'] ?? 0), 3) ?></td>
                            <td class="text-end"><?= number_format((float) ($item['net_weight'] ?? 0), 3) ?></td>
                            <td class="text-end"><?= number_format((float) ($item['gross_weight'] ?? 0), 3) ?></td>
                            <td><?= escapeHtml($item['item_remarks'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div>
            <strong>Remarks:</strong><br>
            <?= nl2br(escapeHtml($shippingBill['remarks'] ?? '')) ?>
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
