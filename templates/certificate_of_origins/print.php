<?php
$certificate = $certificate ?? [];
$meta = $meta ?? [];
$items = $items ?? [];
$statuses = $statuses ?? [];
$statusClasses = [0 => 'bg-secondary', 1 => 'bg-info', 2 => 'bg-success', 6 => 'bg-danger'];
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
        .co-certificate-box {
            border: 3px double #333 !important;
            padding: 2.5rem !important;
            background-color: #fff;
        }
        .co-grid-header {
            border-bottom: 2px solid #000;
            padding-bottom: 1rem;
        }
        @media print {
            body { background: none; padding: 0; }
            .co-certificate-box { border: 3px double #000 !important; }
            @page { margin: 1cm; size: portrait; }
        }
    </style>
</head>
<body>

<div class="co-certificate-box">
    <div class="text-center mb-4 co-grid-header">
        <h3 class="fw-bold text-dark mb-1" style="letter-spacing: 3px;">CERTIFICATE OF ORIGIN</h3>
        <span class="text-muted small text-uppercase fw-semibold">Chamber of Commerce Industry & Export Promotion Board</span>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 border-end">
            <div class="mb-3">
                <span class="d-block small text-muted text-uppercase fw-bold">1. Exporter Name & Details:</span>
                <strong class="d-block text-dark mt-1" style="white-space: pre-wrap; font-size: 0.95rem;"><?= escapeHtml($meta['exporter'] ?? '') ?></strong>
            </div>
            <div>
                <span class="d-block small text-muted text-uppercase fw-bold">2. Consignee (Name & Address):</span>
                <strong class="d-block text-dark mt-1" style="white-space: pre-wrap; font-size: 0.95rem;"><?= escapeHtml($meta['consignee'] ?? '') ?></strong>
            </div>
        </div>
        <div class="col-6 ps-4">
            <div class="mb-2">
                <span class="text-muted small text-uppercase fw-bold">Certificate Number:</span>
                <strong class="text-dark d-block"><?= escapeHtml($certificate['document_number'] ?? '') ?></strong>
            </div>
            <div class="mb-2">
                <span class="text-muted small text-uppercase fw-bold">Date of Issue:</span>
                <strong class="text-dark d-block"><?= escapeHtml($certificate['document_date'] ?? '') ?></strong>
            </div>
            <div class="mb-2">
                <span class="text-muted small text-uppercase fw-bold">Country of Origin:</span>
                <strong class="text-success d-block fw-bold" style="font-size: 1.1rem;"><i class="fas fa-flag text-danger me-1"></i> <?= escapeHtml($meta['country_of_origin'] ?? 'India') ?></strong>
            </div>
            <div class="mb-2">
                <span class="text-muted small text-uppercase fw-bold">Destination Country:</span>
                <strong class="text-dark d-block"><?= escapeHtml($meta['destination_country'] ?? '') ?></strong>
            </div>
            <div>
                <span class="text-muted small text-uppercase fw-bold">Issuing Authority:</span>
                <strong class="text-dark d-block"><?= escapeHtml($meta['issuing_authority'] ?? 'Chamber of Commerce') ?></strong>
            </div>
        </div>
    </div>

    <!-- Items table -->
    <div class="table-responsive mb-4 border-top border-bottom py-3">
        <span class="d-block small text-muted text-uppercase fw-bold mb-3">3. Description of Consignment:</span>
        <table class="table table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th style="width:40px">#</th>
                    <th>Product Name / Variety Details</th>
                    <th>HS Code</th>
                    <th class="text-end">Declared Quantity</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $index => $item): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td class="fw-semibold text-dark"><?= escapeHtml($item['product_name'] ?? '') ?></td>
                        <td><?= escapeHtml($item['hsn_code'] ?? '') ?></td>
                        <td class="text-end fw-bold"><?= number_format((float) ($item['quantity'] ?? 0), 3) ?> KG</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Declaration & Stamp Box -->
    <div class="row g-3 mb-4">
        <div class="col-7">
            <div class="p-3 border rounded bg-light">
                <strong class="text-dark small d-block text-uppercase mb-2 fw-bold">4. Exporter Statutory Declaration</strong>
                <p class="small text-muted mb-0" style="white-space: pre-wrap; font-style: italic;"><?= escapeHtml($meta['declaration'] ?? 'We hereby declare that the goods described above originate from the country stated in Box Country of Origin and that all particulars are true and correct.') ?></p>
            </div>
            <?php if (!empty($certificate['remarks'])): ?>
                <div class="mt-3">
                    <strong>Remarks:</strong>
                    <div class="small text-muted"><?= nl2br(escapeHtml($certificate['remarks'] ?? '')) ?></div>
                </div>
            <?php endif; ?>
        </div>
        <div class="col-5 text-end">
            <div class="p-3 border rounded bg-light text-start d-inline-block" style="min-width: 280px; min-height: 180px;">
                <span class="d-block small text-muted text-uppercase fw-bold mb-2">5. Chamber Certification Seal</span>
                <div style="min-height: 90px;"></div>
                <span class="d-block border-top pt-1 small text-center text-muted">Certification Signatory Agency</span>
            </div>
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
