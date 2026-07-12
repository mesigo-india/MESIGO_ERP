<?php
$certificate = $certificate ?? [];
$meta = $meta ?? [];
$items = $items ?? [];
$statuses = $statuses ?? [];
$statusClasses = [0 => 'bg-secondary', 1 => 'bg-info', 2 => 'bg-success', 6 => 'bg-danger'];

$dbInst = App\Core\Database::getInstance();
$company = $dbInst->query("SELECT * FROM company WHERE status = 1 ORDER BY id ASC LIMIT 1")->fetch(\PDO::FETCH_ASSOC) ?: [];
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
        .phyto-certificate-box {
            border: 3px double #2e7d32 !important; /* Sage green border */
            padding: 2.5rem !important;
            background-color: #fff;
        }
        .phyto-grid-header {
            border-bottom: 2px solid #2e7d32;
            padding-bottom: 1rem;
        }
        @media print {
            body { background: none; padding: 0; }
            .phyto-certificate-box { border: 3px double #2e7d32 !important; }
            @page { margin: 1cm; size: portrait; }
        }
    </style>
</head>
<body>

<div class="phyto-certificate-box">
    <div class="text-center mb-4 phyto-grid-header">
        <h3 class="fw-bold text-success mb-1" style="letter-spacing: 3px;">PHYTOSANITARY CERTIFICATE</h3>
        <span class="text-muted small text-uppercase fw-semibold">Government of India — Department of Agriculture, Cooperation & Farmers Welfare</span>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 border-end">
            <div class="mb-3">
                <span class="d-block small text-muted text-uppercase fw-bold">1. Name & Address of Exporter:</span>
                <strong class="d-block text-dark mt-1" style="white-space: pre-wrap; font-size: 0.95rem;"><?= escapeHtml($company['company_name'] ?? '') ?><br><?= escapeHtml($company['address'] ?? '') ?></strong>
            </div>
            <div>
                <span class="d-block small text-muted text-uppercase fw-bold">2. Declared Name and Address of Consignee:</span>
                <strong class="d-block text-dark mt-1" style="white-space: pre-wrap; font-size: 0.95rem;"><?= escapeHtml($meta['consignee'] ?? '') ?></strong>
            </div>
        </div>
        <div class="col-6 ps-4">
            <div class="mb-2">
                <span class="text-muted small text-uppercase fw-bold">Certificate Number:</span>
                <strong class="text-dark d-block"><?= escapeHtml($certificate['document_number'] ?? '') ?></strong>
            </div>
            <div class="mb-2">
                <span class="text-muted small text-uppercase fw-bold">Date of Inspection:</span>
                <strong class="text-dark d-block"><?= escapeHtml($certificate['document_date'] ?? '') ?></strong>
            </div>
            <div class="mb-2">
                <span class="text-muted small text-uppercase fw-bold">Place of Origin:</span>
                <strong class="text-success d-block fw-bold" style="font-size: 1.1rem;"><i class="fas fa-flag text-danger me-1"></i> <?= escapeHtml($meta['place_of_origin'] ?? 'India') ?></strong>
            </div>
            <div class="mb-2">
                <span class="text-muted small text-uppercase fw-bold">Declared Port of Entry:</span>
                <strong class="text-dark d-block"><?= escapeHtml($meta['port_of_entry'] ?? 'N/A') ?></strong>
            </div>
            <div>
                <span class="text-muted small text-uppercase fw-bold">Declared Means of Conveyance:</span>
                <strong class="text-dark d-block"><?= escapeHtml($meta['means_of_conveyance'] ?? 'N/A') ?></strong>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4 border-top border-bottom py-2 bg-light">
        <div class="col-6"><span class="d-block text-muted small fw-bold">Botanical Name of Plants:</span><span class="fw-semibold text-success small"><em><?= escapeHtml($meta['botanical_name'] ?? 'N/A') ?></em></span></div>
        <div class="col-6"><span class="d-block text-muted small fw-bold">Total Declared Quantity:</span><span class="fw-semibold text-dark small"><?= escapeHtml($meta['declared_quantity'] ?? 'N/A') ?></span></div>
    </div>

    <!-- Disinfestation Treatment Details Panel -->
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="p-3 border border-success rounded bg-light">
                <strong class="text-success d-block small text-uppercase mb-2 fw-bold"><i class="fas fa-flask me-1"></i> Disinfestation and/or Disinfection Treatment Details</strong>
                <div class="row g-3 small text-dark">
                    <div class="col-3"><span class="text-muted d-block">Date of Treatment:</span><strong class="d-block"><?= escapeHtml($meta['treatment_date'] ?? 'N/A') ?></strong></div>
                    <div class="col-3"><span class="text-muted d-block">Treatment Chemical:</span><strong class="d-block"><?= escapeHtml($meta['treatment_chemical'] ?? 'N/A') ?></strong></div>
                    <div class="col-3"><span class="text-muted d-block">Duration & Concentration:</span><strong class="d-block"><?= escapeHtml($meta['treatment_duration'] ?? 'N/A') ?></strong></div>
                    <div class="col-3"><span class="text-muted d-block">Temperature:</span><strong class="d-block"><?= escapeHtml($meta['treatment_temperature'] ?? 'N/A') ?></strong></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Items table -->
    <div class="table-responsive mb-4 border-top border-bottom py-3">
        <span class="d-block small text-muted text-uppercase fw-bold mb-3">3. Description of Packages & Goods:</span>
        <table class="table table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th style="width:40px">#</th>
                    <th>Product Details</th>
                    <th>HS Code</th>
                    <th class="text-end">No. of Packages</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $index => $item): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td class="fw-semibold text-dark"><?= escapeHtml($item['product_name'] ?? '') ?></td>
                        <td><?= escapeHtml($item['hsn_code'] ?? '') ?></td>
                        <td class="text-end fw-bold"><?= number_format((float) ($item['quantity'] ?? 0), 0) ?> Packages</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Declaration & Stamp Box -->
    <div class="row g-3 mb-4">
        <div class="col-7">
            <div class="p-3 border rounded bg-light">
                <strong class="text-success d-block small text-uppercase mb-2 fw-bold">4. Plant Protection Organization Declaration</strong>
                <p class="small text-muted mb-0" style="font-style: italic; line-height: 1.4;">This is to certify that the plants, plant products or other regulated articles described herein have been inspected and/or tested according to appropriate official procedures and are considered to be free from the quarantine pests specified by the importing contracting party.</p>
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
                <span class="d-block small text-muted text-uppercase fw-bold mb-2">5. Signature of Authorized Officer</span>
                <div style="min-height: 90px;"></div>
                <span class="d-block border-top pt-1 small text-center text-muted">Plant Protection Quarantine Officer</span>
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
