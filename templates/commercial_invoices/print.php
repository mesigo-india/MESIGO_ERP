<?php
$quotation = $invoice ?? $quotation ?? [];
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
                <h4 class="fw-bold tracking-wider text-dark mb-0">COMMERCIAL INVOICE</h4>
                <div class="text-muted small">Official Statutory Export Invoice (Under Customs Regulation)</div>
            </div>
            
            <div class="row g-3 mb-4">
                <div class="col-6">
                    <span class="d-block small text-muted text-uppercase">Invoice Details</span>
                    <strong class="d-block text-dark"><?= escapeHtml($quotation['document_number'] ?? '') ?></strong>
                    <span class="d-block small text-muted">Date: <?= escapeHtml($quotation['document_date'] ?? '') ?></span>
                    <span class="d-block small text-muted">Revision: Rev <?= (int) ($meta['revision'] ?? 0) ?></span>
                    <span class="d-block small text-muted">Status: <span class="badge <?= escapeHtml($statusClasses[(int) ($quotation['status'] ?? 0)] ?? 'bg-secondary') ?>"><?= escapeHtml($statuses[(int) ($quotation['status'] ?? 0)] ?? 'Unknown') ?></span></span>
                </div>
                <div class="col-6 text-end">
                    <span class="d-block small text-muted text-uppercase">Buyer / Client</span>
                    <strong class="d-block text-dark"><?= escapeHtml($quotation['buyer_name'] ?? '') ?></strong>
                    <span class="d-block small text-muted">Buyer PO: <?= escapeHtml($meta['buyer_po'] ?? 'N/A') ?></span>
                    <span class="d-block small text-muted">Currency: <?= escapeHtml($quotation['currency_code'] ?? '') ?></span>
                    <span class="d-block small text-muted">Incoterm: <?= escapeHtml($quotation['incoterm_code'] ?? '') ?></span>
                </div>
            </div>
            
            <div class="row g-3 mb-4 border-top border-bottom py-2 bg-light">
                <div class="col-3"><span class="d-block text-muted small">Loading Port</span><span class="fw-semibold text-dark small"><?= escapeHtml($quotation['loading_port_name'] ?? 'N/A') ?></span></div>
                <div class="col-3"><span class="d-block text-muted small">Destination Port</span><span class="fw-semibold text-dark small"><?= escapeHtml($quotation['delivery_port_name'] ?? 'N/A') ?></span></div>
                <div class="col-3"><span class="d-block text-muted small">Payment Terms</span><span class="fw-semibold text-dark small"><?= escapeHtml($quotation['payment_term_name'] ?? 'N/A') ?></span></div>
                <div class="col-3"><span class="d-block text-muted small">Expected Cargo Type</span><span class="fw-semibold text-dark small"><?= escapeHtml($meta['container_type'] ?? '20FT') ?> Container</span></div>
            </div>

            <!-- Exporter / Consignee Address Section -->
            <div class="row g-3 mb-4">
                <div class="col-6">
                    <div class="p-3 border rounded bg-white" style="min-height: 120px;">
                        <strong class="d-block text-dark small text-uppercase mb-2">Consignee (Delivery Address)</strong>
                        <span class="small text-muted d-block" style="white-space: pre-wrap;"><?= escapeHtml($meta['consignee'] ?? 'Not specified') ?></span>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 border rounded bg-white" style="min-height: 120px;">
                        <strong class="d-block text-dark small text-uppercase mb-2">Notify Party</strong>
                        <span class="small text-muted d-block" style="white-space: pre-wrap;"><?= escapeHtml($meta['notify_party'] ?? 'Same as Consignee') ?></span>
                    </div>
                </div>
            </div>

            <!-- Exporter Statutory Identity Panel -->
            <div class="row g-3 mb-4">
                <div class="col-12">
                    <div class="p-3 border rounded bg-light">
                        <strong class="text-warning d-block small text-uppercase mb-2"><i class="fas fa-gavel me-1"></i> Exporter Statutory Identifiers & Registrations</strong>
                        <div class="row g-3 small text-dark">
                            <div class="col-3"><span class="text-muted d-block">Exporter GSTIN:</span><strong class="d-block"><?= escapeHtml($meta['exporter_gst'] ?? '') ?></strong></div>
                            <div class="col-3"><span class="text-muted d-block">LUT Number:</span><strong class="d-block"><?= escapeHtml($meta['lut_number'] ?? 'N/A') ?></strong></div>
                            <div class="col-3"><span class="text-muted d-block">IEC Code:</span><strong class="d-block"><?= escapeHtml($meta['iec'] ?? '') ?></strong></div>
                            <div class="col-3"><span class="text-muted d-block">PAN Number:</span><strong class="d-block"><?= escapeHtml($meta['pan'] ?? '') ?></strong></div>
                        </div>
                        <div class="row g-3 small text-dark border-top pt-2 mt-2">
                            <div class="col-3"><span class="text-muted d-block">AD Code:</span><strong class="d-block"><?= escapeHtml($meta['ad_code'] ?? '') ?></strong></div>
                            <div class="col-3"><span class="text-muted d-block">Shipping Bill No:</span><strong class="d-block"><?= escapeHtml($meta['shipping_bill_number'] ?? 'N/A') ?></strong></div>
                            <div class="col-3"><span class="text-muted d-block">Shipping Bill Date:</span><strong class="d-block"><?= escapeHtml($meta['shipping_bill_date'] ?? 'N/A') ?></strong></div>
                            <div class="col-3"><span class="text-muted d-block">Country of Origin:</span><strong class="d-block">India</strong></div>
                        </div>
                    </div>
                </div>
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
                                <td class="text-end"><?= number_format((float) ($item['quantity'] ?? 0), 3) ?></td>
                                <td><?= escapeHtml($item['unit_code'] ?? '') ?></td>
                                <td class="text-end"><?= number_format((float) ($item['rate'] ?? 0), 4) ?></td>
                                <td class="text-end"><?= number_format((float) ($item['discount_amount'] ?? 0), 2) ?></td>
                                <td class="text-end"><?= number_format((float) ($item['tax_amount'] ?? 0), 2) ?></td>
                                <td class="text-end fw-semibold text-dark"><?= number_format((float) ($item['net_amount'] ?? 0), 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="row mb-4">
                <!-- Notes & Bank Info Column -->
                <div class="col-7">
                    <div class="mb-3">
                        <strong class="text-dark small">Special Instructions:</strong>
                        <p class="text-muted small mt-1"><?= nl2br(escapeHtml($quotation['remarks'] ?? 'No special instructions.')) ?></p>
                    </div>

                    <?php if (!empty($meta['beneficiary_name'])): ?>
                        <!-- Exporter Bank Box -->
                        <div class="p-3 border border-info rounded bg-light mb-3">
                            <strong class="text-info d-block small text-uppercase mb-2"><i class="fas fa-university me-1"></i> Beneficiary Exporter Bank Details</strong>
                            <div class="row g-2 small text-dark">
                                <div class="col-4 text-muted">Beneficiary:</div><div class="col-8 fw-semibold"><?= escapeHtml($meta['beneficiary_name']) ?></div>
                                <div class="col-4 text-muted">Bank Name:</div><div class="col-8 fw-semibold"><?= escapeHtml($meta['bank_name']) ?></div>
                                <div class="col-4 text-muted">Account No:</div><div class="col-8 fw-semibold"><?= escapeHtml($meta['account_number']) ?></div>
                                <div class="col-4 text-muted">SWIFT Code:</div><div class="col-8 fw-semibold"><?= escapeHtml($meta['swift_code']) ?></div>
                                <?php if (!empty($meta['iban'])): ?>
                                    <div class="col-4 text-muted">IBAN:</div><div class="col-8 fw-semibold"><?= escapeHtml($meta['iban']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($meta['export_declaration'])): ?>
                        <div class="p-3 border rounded bg-light">
                            <strong class="text-dark small d-block text-uppercase mb-1">Declarations & Undertakings</strong>
                            <p class="small text-muted mb-2" style="white-space: pre-wrap; font-style: italic;"><?= escapeHtml($meta['export_declaration']) ?></p>
                            <?php if (!empty($meta['tax_declaration'])): ?>
                                <p class="small text-muted mb-0" style="white-space: pre-wrap; font-style: italic;"><?= escapeHtml($meta['tax_declaration']) ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Totals Column -->
                <div class="col-5">
                    <table class="table table-sm text-nowrap">
                        <tr><th>Subtotal</th><td class="text-end"><?= number_format((float) ($totals['subtotal'] ?? 0), 2) ?></td></tr>
                        <tr><th>Discount</th><td class="text-end"><?= number_format((float) ($totals['discount'] ?? 0), 2) ?></td></tr>
                        <tr><th>GST</th><td class="text-end"><?= number_format((float) ($totals['gst'] ?? 0), 2) ?></td></tr>
                        <tr><th>Freight</th><td class="text-end"><?= number_format((float) ($meta['freight'] ?? ($totals['freight'] ?? 0)), 2) ?></td></tr>
                        <tr><th>Insurance</th><td class="text-end"><?= number_format((float) ($meta['insurance'] ?? ($totals['insurance'] ?? 0)), 2) ?></td></tr>
                        <tr><th>Other Charges</th><td class="text-end"><?= number_format((float) ($totals['other'] ?? 0), 2) ?></td></tr>
                        <tr class="table-primary border-top border-2"><th>Grand Total</th><td class="text-end fw-bold"><strong><?= number_format((float) ($totals['grand'] ?? 0), 2) ?></strong></td></tr>
                    </table>
                </div>
            </div>
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
