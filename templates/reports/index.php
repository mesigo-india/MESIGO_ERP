<?php
$filters = $filters ?? [];
$buyers = $buyers ?? [];
$products = $products ?? [];
$currencies = $currencies ?? [];
$summary = $summary ?? [];
$profitability = $profitability ?? [];
$exportMode = $exportMode ?? '';
?>
<style>
@media print {
    .no-print { display: none !important; }
    .card { border: 0; }
}
</style>

<div class="page-header no-print d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 text-primary font-weight-bold mb-0">Reports & Analytics</h1>
    <div class="btn-group">
        <a class="btn btn-outline-secondary" href="/reports?<?= http_build_query(array_merge($filters, ['export' => 'pdf'])) ?>">PDF Ready</a>
        <a class="btn btn-outline-success" href="/reports?<?= http_build_query(array_merge($filters, ['export' => 'excel'])) ?>">Excel Ready</a>
        <button onclick="window.print()" class="btn btn-outline-dark">Print Ready</button>
    </div>
</div>

<div class="card mb-4 no-print shadow-sm border-0">
    <div class="card-body p-4 bg-light rounded">
        <form method="get" action="/reports" class="row g-3">
            <div class="col-md-2">
                <label class="form-label font-weight-bold">Date From</label>
                <input type="date" name="date_from" class="form-control" value="<?= escapeHtml($filters['date_from'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label font-weight-bold">Date To</label>
                <input type="date" name="date_to" class="form-control" value="<?= escapeHtml($filters['date_to'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label font-weight-bold">Buyer</label>
                <select name="buyer_id" class="form-select">
                    <option value="0">All Buyers</option>
                    <?php foreach ($buyers as $buyer): ?>
                        <option value="<?= (int) $buyer['id'] ?>" <?= (int) ($filters['buyer_id'] ?? 0) === (int) $buyer['id'] ? 'selected' : '' ?>><?= escapeHtml($buyer['company_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label font-weight-bold">Destination Country</label>
                <input name="country" class="form-control" value="<?= escapeHtml($filters['country'] ?? '') ?>" placeholder="e.g. Netherlands">
            </div>
            <div class="col-md-2">
                <label class="form-label font-weight-bold">Product</label>
                <select name="product_id" class="form-select">
                    <option value="0">All Products</option>
                    <?php foreach ($products as $product): ?>
                        <option value="<?= (int) $product['id'] ?>" <?= (int) ($filters['product_id'] ?? 0) === (int) $product['id'] ? 'selected' : '' ?>><?= escapeHtml($product['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label font-weight-bold">Status</label>
                <input name="status" class="form-control" value="<?= escapeHtml($filters['status'] ?? '') ?>">
            </div>
            <div class="col-md-1">
                <label class="form-label font-weight-bold">Currency</label>
                <select name="currency_id" class="form-select">
                    <option value="0">All</option>
                    <?php foreach ($currencies as $currency): ?>
                        <option value="<?= (int) $currency['id'] ?>" <?= (int) ($filters['currency_id'] ?? 0) === (int) $currency['id'] ? 'selected' : '' ?>><?= escapeHtml($currency['code']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-12 mt-3">
                <button class="btn btn-primary px-4 shadow-sm">Apply Filters</button>
                <a href="/reports" class="btn btn-outline-secondary px-4">Reset</a>
            </div>
        </form>
    </div>
</div>

<?php if ($exportMode !== ''): ?>
    <div class="alert alert-info shadow-sm mb-4">Export mode: <?= escapeHtml(strtoupper($exportMode)) ?> layout preview.</div>
<?php endif; ?>

<!-- Segment 1: Dedicated Enterprise Profitability Margin Ledger -->
<div class="card mb-4 shadow-sm border-0">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <strong class="h5 mb-0"><i class="fa fa-line-chart"></i> Enterprise Profitability & Margin Ledger (Base INR)</strong>
        <span class="badge bg-light text-primary"><?= count($profitability) ?> documents calculated</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Doc Reference</th>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Entity Context</th>
                        <th>Client / Buyer</th>
                        <th class="text-end">Gross Sales (INR)</th>
                        <th class="text-end">Production Cost (INR)</th>
                        <th class="text-end">Charges (INR)</th>
                        <th class="text-end">Net Margin (INR)</th>
                        <th class="text-center">Margin %</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $totSales = 0; $totProd = 0; $totChg = 0; $totProfit = 0;
                    foreach ($profitability as $row): 
                        $totSales += (float) $row['gross_sales_base'];
                        $totProd += (float) $row['production_cost_base'];
                        $totChg += (float) $row['total_charges_base'];
                        $totProfit += (float) $row['net_profit_base'];
                        $profitColor = $row['net_profit_base'] >= 0 ? 'text-success' : 'text-danger';
                        $marginColor = $row['margin_percent'] >= 0 ? 'bg-success' : 'bg-danger';
                    ?>
                        <tr>
                            <td><strong><?= escapeHtml($row['reference_no'] ?? '') ?></strong></td>
                            <td><?= escapeHtml($row['report_date'] ?? '') ?></td>
                            <td><span class="badge bg-secondary"><?= escapeHtml($row['document_type'] ?? '') ?></span></td>
                            <td><?= escapeHtml($row['company_entity'] ?? 'Primary Entity') ?></td>
                            <td><?= escapeHtml($row['buyer_name'] ?? '') ?></td>
                            <td class="text-end"><?= number_format((float) $row['gross_sales_base'], 2) ?></td>
                            <td class="text-end text-muted"><?= number_format((float) $row['production_cost_base'], 2) ?></td>
                            <td class="text-end text-muted"><?= number_format((float) $row['total_charges_base'], 2) ?></td>
                            <td class="text-end font-weight-bold <?= $profitColor ?>"><?= number_format((float) $row['net_profit_base'], 2) ?></td>
                            <td class="text-center"><span class="badge <?= $marginColor ?> px-2 py-1"><?= number_format((float) $row['margin_percent'], 2) ?>%</span></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (count($profitability) > 0): ?>
                        <tr class="table-warning font-weight-bold">
                            <td colspan="5" class="text-end">Aggregate Report Totals:</td>
                            <td class="text-end"><?= number_format($totSales, 2) ?></td>
                            <td class="text-end"><?= number_format($totProd, 2) ?></td>
                            <td class="text-end"><?= number_format($totChg, 2) ?></td>
                            <td class="text-end text-primary"><?= number_format($totProfit, 2) ?></td>
                            <td class="text-center">
                                <span class="badge bg-primary px-2 py-1">
                                    <?= $totSales > 0 ? number_format(($totProfit / $totSales) * 100, 2) : '0.00' ?>%
                                </span>
                            </td>
                        </tr>
                    <?php else: ?>
                        <tr><td colspan="10" class="text-center text-muted py-4">No matching records found for the chosen filters.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Segment 2: Standard Document Reports -->
<?php foreach ($summary as $reportName => $report): ?>
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <strong><?= escapeHtml($reportName) ?></strong>
            <span class="badge bg-light text-dark"><?= (int) $report['count'] ?> records</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-striped table-hover align-middle mb-0">
                    <thead class="table-secondary">
                        <tr>
                            <th>Reference</th>
                            <th>Date</th>
                            <th>Type / Title</th>
                            <th>Buyer / Category</th>
                            <th>Status</th>
                            <th class="text-end">Quantity</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($report['rows'] as $row): ?>
                            <tr>
                                <td><strong><?= escapeHtml($row['reference_no'] ?? '') ?></strong></td>
                                <td><?= escapeHtml((string) ($row['report_date'] ?? $row['created_at'] ?? '')) ?></td>
                                <td><?= escapeHtml($row['document_type'] ?? $row['title'] ?? '') ?></td>
                                <td><?= escapeHtml($row['buyer_name'] ?? $row['category'] ?? '') ?></td>
                                <td><?= escapeHtml((string) ($row['status'] ?? '')) ?></td>
                                <td class="text-end"><?= number_format((float) ($row['quantity'] ?? $row['document_count'] ?? 0), 3) ?></td>
                                <td class="text-end font-weight-bold"><?= number_format((float) ($row['amount'] ?? 0), 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (count($report['rows']) === 0): ?>
                            <tr><td colspan="7" class="text-center text-muted py-3">No matching records found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endforeach; ?>