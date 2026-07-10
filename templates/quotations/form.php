<?php
$action = $action ?? '/quotations';
$title = $title ?? 'Quotation';
$quotation = $quotation ?? null;
$meta = $meta ?? [];
$items = $items ?? [[]];
$companies = $companies ?? [];
$warehouses = $warehouses ?? [];
$costTemplates = $costTemplates ?? [];
$costComponents = $costComponents ?? [];
$buyers = $buyers ?? [];
$buyerContacts = $buyerContacts ?? [];
$currencies = $currencies ?? [];
$incoterms = $incoterms ?? [];
$paymentTerms = $paymentTerms ?? [];
$ports = $ports ?? [];
$products = $products ?? [];
$grades = $grades ?? [];
$origins = $origins ?? [];
$packingTypes = $packingTypes ?? [];
$units = $units ?? [];
$statuses = $statuses ?? [];
$metaTotals = $meta['totals'] ?? [];
$charges = $meta['charges'] ?? [];
?>
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 text-primary font-weight-bold mb-0"><?= escapeHtml($title) ?></h1>
    <a href="/quotations" class="btn btn-outline-secondary">Back to List</a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        <form method="post" action="<?= escapeHtml($action) ?>" class="needs-validation" novalidate>
            <?= csrfToken() ?>

            <!-- Segment 1: Company Context & Document Meta -->
            <div class="bg-light p-3 rounded mb-4">
                <h5 class="text-secondary font-weight-bold mb-3 border-bottom pb-2">1. Organization Context & Document Scope</h5>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label font-weight-bold" for="company_id">Company Entity</label>
                        <select id="company_id" name="company_id" class="form-select select2-init" required>
                            <?php foreach ($companies as $comp): ?>
                                <option value="<?= (int) $comp['id'] ?>" <?= (int) ($quotation['company_id'] ?? $_SESSION['active_company_id'] ?? 1) === (int) $comp['id'] ? 'selected' : '' ?>><?= escapeHtml($comp['company_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label font-weight-bold" for="tax_basis">Tax Basis (Regulation)</label>
                        <select id="tax_basis" name="tax_basis" class="form-select select2-init" required>
                            <option value="lut" <?= ($quotation['tax_basis'] ?? 'lut') === 'lut' ? 'selected' : '' ?>>Zero-Rated Export (LUT 0% GST)</option>
                            <option value="igst_paid" <?= ($quotation['tax_basis'] ?? '') === 'igst_paid' ? 'selected' : '' ?>>Export IGST Refund Claim (Paid)</option>
                            <option value="domestic" <?= ($quotation['tax_basis'] ?? '') === 'domestic' ? 'selected' : '' ?>>Domestic Invoice (CGST/SGST/IGST)</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label font-weight-bold" for="exchange_rate">Exchange Rate (vs INR Base)</label>
                        <input type="number" step="0.000001" id="exchange_rate" name="exchange_rate" class="form-control" value="<?= (float) ($quotation['exchange_rate'] ?? 1.0) ?>" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label font-weight-bold" for="rate_locked">Currency Rate Lock</label>
                        <select id="rate_locked" name="rate_locked" class="form-select select2-init">
                            <option value="0" <?= (int) ($quotation['rate_locked'] ?? 0) === 0 ? 'selected' : '' ?>>No (Floating Daily Rate)</option>
                            <option value="1" <?= (int) ($quotation['rate_locked'] ?? 0) === 1 ? 'selected' : '' ?>>Yes (Locked Negotiated Rate)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Segment 2: Standard Header Fields -->
            <div class="bg-light p-3 rounded mb-4">
                <h5 class="text-secondary font-weight-bold mb-3 border-bottom pb-2">2. Sales Negotiation Header</h5>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Quotation Number</label>
                        <input type="text" class="form-control bg-white" value="<?= escapeHtml($quotation['document_number'] ?? 'Generated on Save') ?>" readonly>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label" for="document_date">Quotation Date</label>
                        <input type="date" id="document_date" name="document_date" class="form-control" value="<?= escapeHtml($quotation['document_date'] ?? date('Y-m-d')) ?>" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label" for="revision">Revision Level</label>
                        <input type="number" id="revision" name="revision" class="form-control" value="<?= (int) ($meta['revision'] ?? 0) ?>" min="0">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label" for="status">Document Status</label>
                        <select id="status" name="status" class="form-select select2-init">
                            <?php foreach ($statuses as $statusId => $statusName): ?>
                                <option value="<?= (int) $statusId ?>" <?= (int) ($quotation['status'] ?? 0) === (int) $statusId ? 'selected' : '' ?>><?= escapeHtml($statusName) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="buyer_id">Buyer (Exporter Client)</label>
                        <select id="buyer_id" name="buyer_id" class="form-select select2-init" required>
                            <option value="0">Select Buyer</option>
                            <?php foreach ($buyers as $buyer): ?>
                                <option value="<?= (int) $buyer['id'] ?>" <?= (int) ($quotation['buyer_id'] ?? 0) === (int) $buyer['id'] ? 'selected' : '' ?>><?= escapeHtml($buyer['company_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label" for="buyer_contact_id">Contact Person</label>
                        <select id="buyer_contact_id" name="buyer_contact_id" class="form-select select2-init">
                            <option value="0">Select Contact</option>
                            <?php foreach ($buyerContacts as $contact): ?>
                                <option value="<?= (int) $contact['id'] ?>" data-buyer="<?= (int) $contact['buyer_id'] ?>" <?= (int) ($meta['buyer_contact_id'] ?? 0) === (int) $contact['id'] ? 'selected' : '' ?>><?= escapeHtml($contact['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label" for="currency_id">Transaction Currency</label>
                        <select id="currency_id" name="currency_id" class="form-select select2-init" required>
                            <option value="0">Select Currency</option>
                            <?php foreach ($currencies as $currency): ?>
                                <option value="<?= (int) $currency['id'] ?>" <?= (int) ($quotation['currency_id'] ?? 0) === (int) $currency['id'] ? 'selected' : '' ?>><?= escapeHtml($currency['code']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label" for="incoterm_id">Incoterm Rule</label>
                        <select id="incoterm_id" name="incoterm_id" class="form-select select2-init">
                            <option value="0">Select Incoterm</option>
                            <?php foreach ($incoterms as $incoterm): ?>
                                <option value="<?= (int) $incoterm['id'] ?>" <?= (int) ($quotation['incoterm_id'] ?? 0) === (int) $incoterm['id'] ? 'selected' : '' ?>><?= escapeHtml($incoterm['code']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label" for="payment_term_id">Payment Mode</label>
                        <select id="payment_term_id" name="payment_term_id" class="form-select select2-init">
                            <option value="0">Select Payment Term</option>
                            <?php foreach ($paymentTerms as $term): ?>
                                <option value="<?= (int) $term['id'] ?>" <?= (int) ($quotation['payment_term_id'] ?? 0) === (int) $term['id'] ? 'selected' : '' ?>><?= escapeHtml($term['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label" for="shipment_term">Shipment Term</label>
                        <input type="text" id="shipment_term" name="shipment_term" class="form-control" value="<?= escapeHtml($meta['shipment_term'] ?? ($quotation['shipment_type'] ?? '')) ?>" placeholder="e.g. 20ft Container / LCL">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label" for="valid_until">Validity Deadline</label>
                        <input type="date" id="valid_until" name="valid_until" class="form-control" value="<?= escapeHtml($meta['valid_until'] ?? '') ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="loading_port_id">Port of Loading (Origin)</label>
                        <select id="loading_port_id" name="loading_port_id" class="form-select select2-init">
                            <option value="0">Select Loading Port</option>
                            <?php foreach ($ports as $port): ?>
                                <option value="<?= (int) $port['id'] ?>" <?= (int) ($quotation['loading_port_id'] ?? 0) === (int) $port['id'] ? 'selected' : '' ?>><?= escapeHtml($port['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="delivery_port_id">Port of Destination (Delivery)</label>
                        <select id="delivery_port_id" name="delivery_port_id" class="form-select select2-init">
                            <option value="0">Select Destination Port</option>
                            <?php foreach ($ports as $port): ?>
                                <option value="<?= (int) $port['id'] ?>" <?= (int) ($quotation['destination_port_id'] ?? 0) === (int) $port['id'] ? 'selected' : '' ?>><?= escapeHtml($port['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Segment 3: Multi Product Grid -->
            <div class="bg-light p-3 rounded mb-4">
                <h5 class="text-secondary font-weight-bold mb-3 border-bottom pb-2 d-flex justify-content-between align-items-center">
                    <span>3. Multi Product Sourcing Grid</span>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="btn-add-product">Add Product Line</button>
                </h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle" id="quotation-items">
                        <thead class="table-dark">
                            <tr>
                                <th style="min-width: 180px;">Product</th>
                                <th style="min-width: 140px;">Source Warehouse</th>
                                <th style="min-width: 100px;">Grade</th>
                                <th style="min-width: 100px;">Origin</th>
                                <th>HS Code</th>
                                <th style="min-width: 90px;">Packing</th>
                                <th style="width: 100px;">Qty</th>
                                <th style="min-width: 90px;">Unit</th>
                                <th style="width: 100px;">Rate</th>
                                <th style="width: 80px;">Disc %</th>
                                <th style="width: 80px;">GST %</th>
                                <th style="width: 120px;">Amount</th>
                                <th style="width: 50px;">Remove</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $count = max(4, count($items));
                            for ($i = 0; $i < $count; $i++): 
                                $item = $items[$i] ?? []; 
                            ?>
                                <tr class="product-row">
                                    <td>
                                        <select name="product_id[]" class="form-select form-select-sm select2-grid">
                                            <option value="0">Select Product</option>
                                            <?php foreach ($products as $product): ?>
                                                <option value="<?= (int) $product['id'] ?>" <?= (int) ($item['product_id'] ?? 0) === (int) $product['id'] ? 'selected' : '' ?>><?= escapeHtml($product['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="warehouse_id[]" class="form-select form-select-sm select2-grid">
                                            <option value="0">Select Warehouse</option>
                                            <?php foreach ($warehouses as $wh): ?>
                                                <option value="<?= (int) $wh['id'] ?>" <?= (int) ($item['warehouse_id'] ?? 0) === (int) $wh['id'] ? 'selected' : '' ?>><?= escapeHtml($wh['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="grade_id[]" class="form-select form-select-sm">
                                            <option value="0">Select Grade</option>
                                            <?php foreach ($grades as $grade): ?>
                                                <option value="<?= (int) $grade['id'] ?>" <?= (int) ($item['grade_id'] ?? 0) === (int) $grade['id'] ? 'selected' : '' ?>><?= escapeHtml($grade['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="origin_id[]" class="form-select form-select-sm">
                                            <option value="0">Select Origin</option>
                                            <?php foreach ($origins as $origin): ?>
                                                <option value="<?= (int) $origin['id'] ?>" <?= (int) ($item['origin_id'] ?? 0) === (int) $origin['id'] ? 'selected' : '' ?>><?= escapeHtml($origin['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="hsn_code[]" class="form-control form-control-sm" value="<?= escapeHtml($item['hsn_code'] ?? '') ?>" placeholder="HS Code">
                                    </td>
                                    <td>
                                        <select name="packing_type_id[]" class="form-select form-select-sm">
                                            <option value="0">Select Pack</option>
                                            <?php foreach ($packingTypes as $packing): ?>
                                                <option value="<?= (int) $packing['id'] ?>" <?= (int) ($item['packing_type_id'] ?? 0) === (int) $packing['id'] ? 'selected' : '' ?>><?= escapeHtml($packing['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" step="0.001" name="quantity[]" class="form-control form-control-sm calc qty" value="<?= escapeHtml($item['quantity'] ?? '') ?>">
                                    </td>
                                    <td>
                                        <select name="unit_id[]" class="form-select form-select-sm">
                                            <option value="0">Select Unit</option>
                                            <?php foreach ($units as $unit): ?>
                                                <option value="<?= (int) $unit['id'] ?>" <?= (int) ($item['unit_id'] ?? 0) === (int) $unit['id'] ? 'selected' : '' ?>><?= escapeHtml($unit['code'] ?? $unit['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" step="0.0001" name="rate[]" class="form-control form-control-sm calc rate" value="<?= escapeHtml($item['rate'] ?? '') ?>">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="discount_percent[]" class="form-control form-control-sm calc disc" value="<?= escapeHtml($item['discount_percent'] ?? '0.00') ?>">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="tax_percent[]" class="form-control form-control-sm calc gst" value="<?= escapeHtml($item['tax_percent'] ?? '0.00') ?>">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm amount bg-light font-weight-bold" value="<?= escapeHtml($item['net_amount'] ?? '0.00') ?>" readonly>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-outline-danger btn-sm btn-remove-row"><i class="fa fa-trash"></i></button>
                                    </td>
                                </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Segment 4: Dynamic Expenses Sheet -->
            <div class="bg-light p-3 rounded mb-4">
                <h5 class="text-secondary font-weight-bold mb-3 border-bottom pb-2 d-flex justify-content-between align-items-center">
                    <span>4. Dynamic Costing Sheet Ledger</span>
                    <div class="d-flex align-items-center">
                        <select id="cost_template_id" class="form-select form-select-sm mr-2" style="width: 200px;">
                            <option value="0">Select Template</option>
                            <?php foreach ($costTemplates as $temp): ?>
                                <option value="<?= (int) $temp['id'] ?>"><?= escapeHtml($temp['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" class="btn btn-outline-secondary btn-sm mr-3" id="btn-apply-template">Load Template</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="btn-add-charge">Add Expense Line</button>
                    </div>
                </h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle" id="costing-charges">
                        <thead class="table-dark">
                            <tr>
                                <th>Cost Component Type</th>
                                <th>Description / Item Name</th>
                                <th style="width: 140px;">Amount</th>
                                <th style="width: 110px;">Currency</th>
                                <th style="width: 140px;">Exchange Rate</th>
                                <th>Method / Bank</th>
                                <th>Remarks</th>
                                <th style="width: 50px;">Remove</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // Fetch existing charges if dynamic array format; if not, translate old properties
                            $existingCharges = [];
                            if (isset($charges[0]) && is_array($charges[0])) {
                                $existingCharges = $charges;
                            } else {
                                // Convert old static array structure into sequential lines
                                $idx = 0;
                                foreach (['freight' => 'Freight', 'insurance' => 'Insurance', 'other_charges' => 'Other Charges'] as $key => $lbl) {
                                    $val = (float) ($charges[$key] ?? 0.0);
                                    if ($val > 0.0) {
                                        $existingCharges[] = [
                                            'cost_component_id' => 0,
                                            'charge_name' => $lbl,
                                            'charge_amount' => $val,
                                            'currency_id' => $quotation['currency_id'] ?? 1,
                                            'exchange_rate' => $quotation['exchange_rate'] ?? 1.0,
                                            'payment_method' => '',
                                            'remarks' => ''
                                        ];
                                    }
                                }
                            }

                            // Render charge rows
                            foreach ($existingCharges as $charge):
                            ?>
                                <tr class="charge-row">
                                    <td>
                                        <select name="charge_cost_component_id[]" class="form-select form-select-sm">
                                            <option value="0">Select Component</option>
                                            <?php foreach ($costComponents as $comp): ?>
                                                <option value="<?= (int) $comp['id'] ?>" <?= (int) ($charge['cost_component_id'] ?? 0) === (int) $comp['id'] ? 'selected' : '' ?>><?= escapeHtml($comp['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="charge_name[]" class="form-control form-control-sm" value="<?= escapeHtml($charge['charge_name'] ?? '') ?>" required>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="charge_amount[]" class="form-control form-control-sm calc-charge charge-amt" value="<?= escapeHtml($charge['charge_amount'] ?? '') ?>" required>
                                    </td>
                                    <td>
                                        <select name="charge_currency_id[]" class="form-select form-select-sm charge-curr">
                                            <?php foreach ($currencies as $curr): ?>
                                                <option value="<?= (int) $curr['id'] ?>" data-rate="<?= (float) $curr['exchange_rate'] ?>" <?= (int) ($charge['currency_id'] ?? 1) === (int) $curr['id'] ? 'selected' : '' ?>><?= escapeHtml($curr['code']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" step="0.000001" name="charge_exchange_rate[]" class="form-control form-control-sm calc-charge charge-rate" value="<?= (float) ($charge['exchange_rate'] ?? 1.0) ?>" required>
                                    </td>
                                    <td>
                                        <input type="text" name="charge_payment_method[]" class="form-control form-control-sm" value="<?= escapeHtml($charge['payment_method'] ?? '') ?>" placeholder="Method">
                                    </td>
                                    <td>
                                        <input type="text" name="charge_remarks[]" class="form-control form-control-sm" value="<?= escapeHtml($charge['remarks'] ?? '') ?>" placeholder="Remarks">
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-outline-danger btn-sm btn-remove-charge-row"><i class="fa fa-trash"></i></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Segment 5: Totals Summary -->
            <div class="row">
                <div class="col-md-8">
                    <label class="form-label font-weight-bold" for="remarks">General Negotiation Remarks / Terms</label>
                    <textarea id="remarks" name="remarks" class="form-control bg-white" rows="6"><?= escapeHtml($quotation['remarks'] ?? '') ?></textarea>
                </div>
                <div class="col-md-4">
                    <div class="border rounded p-4 bg-light shadow-sm">
                        <h6 class="text-secondary font-weight-bold border-bottom pb-2 mb-3">Invoice Totals Summary</h6>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal (Base Value):</span>
                            <span class="font-weight-bold" id="subtotal"><?= number_format((float) ($metaTotals['subtotal'] ?? 0), 2) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 text-danger">
                            <span>Discount Total:</span>
                            <span class="font-weight-bold" id="discountTotal">-<?= number_format((float) ($metaTotals['discount'] ?? 0), 2) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 text-info">
                            <span>Tax Aggregate:</span>
                            <span class="font-weight-bold" id="gstTotal"><?= number_format((float) ($metaTotals['gst'] ?? 0), 2) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3 text-warning">
                            <span>Dynamic Charges Total:</span>
                            <span class="font-weight-bold" id="chargesTotal">0.00</span>
                        </div>
                        <div class="d-flex justify-content-between border-top pt-2 fs-5 text-primary">
                            <strong>Invoice Grand Total:</strong>
                            <strong id="grandTotal"><?= number_format((float) ($metaTotals['grand'] ?? 0), 2) ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="border-top pt-3 mt-4 text-right">
                <button type="submit" class="btn btn-primary btn-lg shadow-sm px-5">Save Quotation Record</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Calculations script
    function calculate() {
        let subtotal = 0;
        let discount = 0;
        let gst = 0;

        document.querySelectorAll('#quotation-items tbody tr.product-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.qty')?.value || 0);
            const rate = parseFloat(row.querySelector('.rate')?.value || 0);
            const disc = parseFloat(row.querySelector('.disc')?.value || 0);
            const tax = parseFloat(row.querySelector('.gst')?.value || 0);
            
            const line = qty * rate;
            const lineDisc = line * (disc / 100);
            const lineGst = (line - lineDisc) * (tax / 100);
            
            subtotal += line;
            discount += lineDisc;
            gst += lineGst;

            const amountInput = row.querySelector('.amount');
            if (amountInput) {
                amountInput.value = (line - lineDisc + lineGst).toFixed(2);
            }
        });

        // Sum costing sheet charges
        let totalCharges = 0;
        document.querySelectorAll('#costing-charges tbody tr.charge-row').forEach(row => {
            const amt = parseFloat(row.querySelector('.charge-amt')?.value || 0);
            totalCharges += amt;
        });

        document.getElementById('subtotal').textContent = subtotal.toFixed(2);
        document.getElementById('discountTotal').textContent = discount.toFixed(2);
        document.getElementById('gstTotal').textContent = gst.toFixed(2);
        document.getElementById('chargesTotal').textContent = totalCharges.toFixed(2);
        document.getElementById('grandTotal').textContent = (subtotal - discount + gst + totalCharges).toFixed(2);
    }

    // Event listeners delegation
    document.querySelector('form').addEventListener('input', function(e) {
        if (e.target.classList.contains('calc') || e.target.classList.contains('calc-charge')) {
            calculate();
        }
    });

    // Handle currency changes to set initial exchange rates automatically
    document.querySelector('form').addEventListener('change', function(e) {
        if (e.target.classList.contains('charge-curr')) {
            const selectedOpt = e.target.options[e.target.selectedIndex];
            const rate = selectedOpt.dataset.rate || '1.000000';
            const rateInput = e.target.closest('tr').querySelector('.charge-rate');
            if (rateInput) {
                rateInput.value = parseFloat(rate).toFixed(6);
                calculate();
            }
        }
        if (e.target.id === 'currency_id') {
            const selectedOpt = e.target.options[e.target.selectedIndex];
            const rate = selectedOpt.dataset.rate || '1.000000';
            const rateInput = document.getElementById('exchange_rate');
            if (rateInput) {
                rateInput.value = parseFloat(rate).toFixed(6);
                calculate();
            }
        }
    });

    // 2. Add product line dynamically
    document.getElementById('btn-add-product').addEventListener('click', function() {
        const tbody = document.querySelector('#quotation-items tbody');
        const firstRow = tbody.querySelector('tr.product-row');
        if (!firstRow) return;

        const newRow = firstRow.cloneNode(true);
        // Clear input values
        newRow.querySelectorAll('input').forEach(input => {
            input.value = input.classList.contains('amount') ? '0.00' : '';
        });
        newRow.querySelectorAll('select').forEach(select => {
            select.selectedIndex = 0;
        });
        tbody.appendChild(newRow);
        calculate();
    });

    // 3. Add expense charge line dynamically
    document.getElementById('btn-add-charge').addEventListener('click', function() {
        const tbody = document.querySelector('#costing-charges tbody');
        const firstRow = tbody.querySelector('tr.charge-row');
        
        let newRow;
        if (firstRow) {
            newRow = firstRow.cloneNode(true);
            newRow.querySelectorAll('input').forEach(input => {
                input.value = input.classList.contains('charge-rate') ? '1.000000' : '';
            });
            newRow.querySelectorAll('select').forEach(select => {
                select.selectedIndex = 0;
            });
        } else {
            // If charges sheet was completely empty, construct a skeleton row
            newRow = document.createElement('tr');
            newRow.className = 'charge-row';
            newRow.innerHTML = `
                <td>
                    <select name="charge_cost_component_id[]" class="form-select form-select-sm">
                        <option value="0">Select Component</option>
                        <?php foreach ($costComponents as $comp): ?>
                            <option value="<?= (int) $comp['id'] ?>"><?= escapeHtml($comp['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td><input type="text" name="charge_name[]" class="form-control form-control-sm" required></td>
                <td><input type="number" step="0.01" name="charge_amount[]" class="form-control form-control-sm calc-charge charge-amt" required></td>
                <td>
                    <select name="charge_currency_id[]" class="form-select form-select-sm charge-curr">
                        <?php foreach ($currencies as $curr): ?>
                            <option value="<?= (int) $curr['id'] ?>" data-rate="<?= (float) $curr['exchange_rate'] ?>"><?= escapeHtml($curr['code']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td><input type="number" step="0.000001" name="charge_exchange_rate[]" class="form-control form-control-sm calc-charge charge-rate" value="1.000000" required></td>
                <td><input type="text" name="charge_payment_method[]" class="form-control form-control-sm" placeholder="Method"></td>
                <td><input type="text" name="charge_remarks[]" class="form-control form-control-sm" placeholder="Remarks"></td>
                <td><button type="button" class="btn btn-outline-danger btn-sm btn-remove-charge-row"><i class="fa fa-trash"></i></button></td>
            `;
        }
        tbody.appendChild(newRow);
        calculate();
    });

    // 4. Remove rows delegation
    document.querySelector('#quotation-items').addEventListener('click', function(e) {
        if (e.target.closest('.btn-remove-row')) {
            const rows = document.querySelectorAll('#quotation-items tbody tr.product-row');
            if (rows.length > 1) {
                e.target.closest('tr.product-row').remove();
                calculate();
            } else {
                alert('Every document must have at least one product item.');
            }
        }
    });

    document.querySelector('#costing-charges').addEventListener('click', function(e) {
        if (e.target.closest('.btn-remove-charge-row')) {
            e.target.closest('tr.charge-row').remove();
            calculate();
        }
    });

    // 5. Apply Cost Templates via AJAX
    document.getElementById('btn-apply-template').addEventListener('click', function() {
        const templateId = document.getElementById('cost_template_id').value;
        if (templateId === '0') return;

        if (!confirm('Are you sure you want to apply this costing template? It will overwrite current charges lines.')) {
            return;
        }

        // We fetch template line details via a quick admin JSON helper
        // Since we are creating a dynamic AJAX request, let's fetch components details:
        fetch('/settings/cost-templates/' + templateId + '/items')
            .then(res => {
                if (!res.ok) throw new Error('Failed to load costing template details.');
                return res.json();
            })
            .then(data => {
                const tbody = document.querySelector('#costing-charges tbody');
                tbody.innerHTML = ''; // clear current

                data.forEach(item => {
                    const row = document.createElement('tr');
                    row.className = 'charge-row';
                    row.innerHTML = `
                        <td>
                            <select name="charge_cost_component_id[]" class="form-select form-select-sm">
                                <option value="0">Select Component</option>
                                <?php foreach ($costComponents as $comp): ?>
                                    <option value="<?= (int) $comp['id'] ?>" \${item.cost_component_id == <?= (int) $comp['id'] ?> ? 'selected' : ''}><?= escapeHtml($comp['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td><input type="text" name="charge_name[]" class="form-control form-control-sm" value="\${item.component_name || 'Charge'}" required></td>
                        <td><input type="number" step="0.01" name="charge_amount[]" class="form-control form-control-sm calc-charge charge-amt" value="\${item.amount}" required></td>
                        <td>
                            <select name="charge_currency_id[]" class="form-select form-select-sm charge-curr">
                                <?php foreach ($currencies as $curr): ?>
                                    <option value="<?= (int) $curr['id'] ?>" data-rate="<?= (float) $curr['exchange_rate'] ?>" \${item.currency_id == <?= (int) $curr['id'] ?> ? 'selected' : ''}><?= escapeHtml($curr['code']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td><input type="number" step="0.000001" name="charge_exchange_rate[]" class="form-control form-control-sm calc-charge charge-rate" value="\${parseFloat(item.exchange_rate || 1).toFixed(6)}" required></td>
                        <td><input type="text" name="charge_payment_method[]" class="form-control form-control-sm" placeholder="Method"></td>
                        <td><input type="text" name="charge_remarks[]" class="form-control form-control-sm" placeholder="Remarks"></td>
                        <td><button type="button" class="btn btn-outline-danger btn-sm btn-remove-charge-row"><i class="fa fa-trash"></i></button></td>
                    `;
                    tbody.appendChild(row);
                });
                calculate();
            })
            .catch(err => {
                alert(err.message);
            });
    });

    // 6. Filter contacts
    const buyerSelect = document.getElementById('buyer_id');
    const contactSelect = document.getElementById('buyer_contact_id');
    function filterBuyerContacts() {
        if (!buyerSelect || !contactSelect) return;
        const buyerId = buyerSelect.value;
        Array.from(contactSelect.options).forEach(option => {
            if (option.value === '0') return;
            option.hidden = option.dataset.buyer !== buyerId;
        });
        const selected = contactSelect.options[contactSelect.selectedIndex];
        if (selected && selected.hidden) contactSelect.value = '0';
    }
    buyerSelect?.addEventListener('change', filterBuyerContacts);
    filterBuyerContacts();
    calculate();
});
</script>