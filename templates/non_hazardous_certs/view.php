<?php 
$certificate = $certificate ?? []; 
$meta = $meta ?? []; 
$items = $items ?? []; 
$statuses = $statuses ?? []; 
$revisions = $revisions ?? []; 
$history = $history ?? []; 
$statusClasses = [0 => 'bg-secondary', 1 => 'bg-info', 2 => 'bg-success', 6 => 'bg-danger']; 
?>
<style>
@media print {
    .no-print { display: none !important; }
    .card { border: 0; }
    .page-header { display: none; }
}
</style>
<div class="page-header no-print">
    <h1><?= escapeHtml($certificate['document_number'] ?? '') ?></h1>
    <div class="btn-group">
        <a href="/export-documents" class="btn btn-outline-secondary">Back to Vault</a>
        <button onclick="window.print()" class="btn btn-outline-dark">Print</button>
        <?php if (\App\Core\Session::get('role_name') === 'admin'): ?>
            <form method="post" action="/non-hazardous-certs/<?= (int) ($certificate['id'] ?? 0) ?>/delete" class="d-inline" onsubmit="return confirm('Are you sure you want to permanently delete this Non-Hazardous Certificate?');">
                <?= csrfToken() ?>
                <button class="btn btn-danger">Delete</button>
            </form>
        <?php endif; ?>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <div class="text-center mb-4">
            <h2 class="fw-bold text-primary">NON-HAZARDOUS CARGO DECLARATION CERTIFICATE</h2>
            <p class="text-muted">Issued in accordance with international maritime transport regulations</p>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <strong>Certificate No:</strong> <?= escapeHtml($certificate['document_number'] ?? '') ?><br>
                <strong>Date:</strong> <?= escapeHtml($certificate['document_date'] ?? '') ?><br>
                <strong>Status:</strong> <span class="badge <?= escapeHtml($statusClasses[(int) ($certificate['status'] ?? 0)] ?? 'bg-secondary') ?>"><?= escapeHtml($statuses[(int) ($certificate['status'] ?? 0)] ?? 'Unknown') ?></span><br>
                <strong>Exporter (Shipper):</strong> <?= escapeHtml($meta['exporter'] ?: 'MESIGO India Agricultural Exports Pvt Ltd') ?>
            </div>
            <div class="col-md-6">
                <strong>Consignee:</strong> <?= escapeHtml($meta['consignee'] ?: ($certificate['buyer_name'] ?? '')) ?><br>
                <strong>Country of Origin:</strong> <?= escapeHtml($meta['country_of_origin'] ?: 'India') ?><br>
                <strong>Destination Country:</strong> <?= escapeHtml($meta['destination_country'] ?? '') ?>
            </div>
        </div>
        
        <div class="border p-3 my-4 bg-light">
            <h5 class="fw-bold">Declaration Statement</h5>
            <p class="mb-0">
                <?= !empty($meta['declaration']) ? nl2br(escapeHtml($meta['declaration'])) : 'We hereby declare and certify that the consignment detailed below consists of non-hazardous, non-combustible, and non-dangerous goods. The cargo does not contain any restricted, poisonous, or harmful substances, and is safe and fit for international sea, road, and air transit under IMDG / IATA / ADR regulations.' ?>
            </p>
        </div>

        <div class="table-responsive mt-3">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product Description</th>
                        <th>HS Code</th>
                        <th>Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $index => $item): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= escapeHtml($item['product_name'] ?? '') ?></td>
                            <td><?= escapeHtml($item['hsn_code'] ?? '') ?></td>
                            <td><?= number_format((float) ($item['quantity'] ?? 0), 3) ?> Bags</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div>
            <strong>Remarks:</strong><br>
            <?= nl2br(escapeHtml($certificate['remarks'] ?? 'No special remarks.')) ?>
        </div>
        <div class="mt-5 row">
            <div class="col-6"></div>
            <div class="col-6 text-center">
                <div class="border-bottom d-inline-block" style="width: 200px; height: 50px;"></div>
                <div class="small text-muted mt-2">Authorized Signatory & Stamp</div>
            </div>
        </div>
    </div>
</div>
