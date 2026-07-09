<?php
$action = $action ?? '/quotations';
$title = $title ?? 'Quotation';
$quotation = $quotation ?? null;
$meta = $meta ?? [];
$items = $items ?? [[]];
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
<div class="page-header">
    <h1><?= escapeHtml($title) ?></h1>
    <a href="/quotations" class="btn btn-outline-secondary">Back</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="post" action="<?= escapeHtml($action) ?>" class="needs-validation" novalidate>
            <?= csrfToken() ?>

            <h5 class="mb-3">Quotation Header</h5>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Quotation Number</label>
                    <input type="text" class="form-control" value="<?= escapeHtml($quotation['document_number'] ?? 'Auto') ?>" readonly>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label" for="document_date">Quotation Date</label>
                    <input type="date" id="document_date" name="document_date" class="form-control" value="<?= escapeHtml($quotation['document_date'] ?? date('Y-m-d')) ?>" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label" for="revision">Revision</label>
                    <input type="number" id="revision" name="revision" class="form-control" value="<?= (int) ($meta['revision'] ?? 0) ?>" min="0">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label" for="status">Status</label>
                    <select id="status" name="status" class="form-select">
                        <?php foreach ($statuses as $statusId => $statusName): ?>
                            <option value="<?= (int) $statusId ?>" <?= (int) ($quotation['status'] ?? 0) === (int) $statusId ? 'selected' : '' ?>><?= escapeHtml($statusName) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="buyer_id">Buyer</label>
                    <select id="buyer_id" name="buyer_id" class="form-select" required>
                        <option value="0">Select Buyer</option>
                        <?php foreach ($buyers as $buyer): ?>
                            <option value="<?= (int) $buyer['id'] ?>" <?= (int) ($quotation['buyer_id'] ?? 0) === (int) $buyer['id'] ? 'selected' : '' ?>><?= escapeHtml($buyer['company_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback">Buyer is required.</div>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label" for="buyer_contact_id">Buyer Contact</label>
                    <select id="buyer_contact_id" name="buyer_contact_id" class="form-select">
                        <option value="0">Select Contact</option>
                        <?php foreach ($buyerContacts as $contact): ?>
                            <option value="<?= (int) $contact['id'] ?>" data-buyer="<?= (int) $contact['buyer_id'] ?>" <?= (int) ($meta['buyer_contact_id'] ?? 0) === (int) $contact['id'] ? 'selected' : '' ?>><?= escapeHtml($contact['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label" for="currency_id">Currency</label>
                    <select id="currency_id" name="currency_id" class="form-select" data-master="currencies" data-master-title="Currency" required>
                        <option value="0">Select Currency</option>
                        <?php foreach ($currencies as $currency): ?>
                            <option value="<?= (int) $currency['id'] ?>" <?= (int) ($quotation['currency_id'] ?? 0) === (int) $currency['id'] ? 'selected' : '' ?>><?= escapeHtml($currency['code']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback">Currency is required.</div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label" for="incoterm_id">Incoterm</label>
                    <select id="incoterm_id" name="incoterm_id" class="form-select" data-master="incoterms" data-master-title="Incoterm"><option value="0">Select</option><?php foreach ($incoterms as $incoterm): ?><option value="<?= (int) $incoterm['id'] ?>" <?= (int) ($quotation['incoterm_id'] ?? 0) === (int) $incoterm['id'] ? 'selected' : '' ?>><?= escapeHtml($incoterm['code']) ?></option><?php endforeach; ?></select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label" for="payment_term_id">Payment Term</label>
                    <select id="payment_term_id" name="payment_term_id" class="form-select" data-master="payment-terms" data-master-title="Payment Term"><option value="0">Select</option><?php foreach ($paymentTerms as $term): ?><option value="<?= (int) $term['id'] ?>" <?= (int) ($quotation['payment_term_id'] ?? 0) === (int) $term['id'] ? 'selected' : '' ?>><?= escapeHtml($term['name']) ?></option><?php endforeach; ?></select>
                </div>
                <div class="col-md-3 mb-3"><label class="form-label" for="shipment_term">Shipment Term</label><select id="shipment_term" name="shipment_term" class="form-select" data-master="shipping-terms" data-master-title="Shipping Term"><option value="<?= escapeHtml($meta['shipment_term'] ?? ($quotation['shipment_type'] ?? '')) ?>"><?= escapeHtml($meta['shipment_term'] ?? ($quotation['shipment_type'] ?? 'Select')) ?></option></select></div>
                <div class="col-md-3 mb-3"><label class="form-label" for="valid_until">Valid Until</label><input type="date" id="valid_until" name="valid_until" class="form-control" value="<?= escapeHtml($meta['valid_until'] ?? '') ?>"></div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3"><label class="form-label" for="loading_port_id">Loading Port</label><select id="loading_port_id" name="loading_port_id" class="form-select" data-master="ports" data-master-title="Port"><option value="0">Select</option><?php foreach ($ports as $port): ?><option value="<?= (int) $port['id'] ?>" <?= (int) ($quotation['loading_port_id'] ?? 0) === (int) $port['id'] ? 'selected' : '' ?>><?= escapeHtml($port['name']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-6 mb-3"><label class="form-label" for="delivery_port_id">Delivery Port</label><select id="delivery_port_id" name="delivery_port_id" class="form-select" data-master="ports" data-master-title="Port"><option value="0">Select</option><?php foreach ($ports as $port): ?><option value="<?= (int) $port['id'] ?>" <?= (int) ($quotation['destination_port_id'] ?? 0) === (int) $port['id'] ? 'selected' : '' ?>><?= escapeHtml($port['name']) ?></option><?php endforeach; ?></select></div>
            </div>

            <h5 class="mt-4 mb-3">Multi Product Grid</h5>
            <div class="table-responsive">
                <table class="table table-bordered align-middle" id="quotation-items">
                    <thead><tr><th>Product</th><th>Grade</th><th>Origin</th><th>HS Code</th><th>Packaging</th><th>Qty</th><th>Unit</th><th>Rate</th><th>Disc %</th><th>GST %</th><th>Amount</th></tr></thead>
                    <tbody>
                        <?php for ($i = 0; $i < 6; $i++): $item = $items[$i] ?? []; ?>
                            <tr>
                                <td><select name="product_id[]" class="form-select form-select-sm"><option value="0">Product</option><?php foreach ($products as $product): ?><option value="<?= (int) $product['id'] ?>" <?= (int) ($item['product_id'] ?? 0) === (int) $product['id'] ? 'selected' : '' ?>><?= escapeHtml($product['name']) ?></option><?php endforeach; ?></select></td>
                                <td><select name="grade_id[]" class="form-select form-select-sm" data-master="product-grades" data-master-title="Product Grade"><option value="0">Grade</option><?php foreach ($grades as $grade): ?><option value="<?= (int) $grade['id'] ?>" <?= (int) ($item['grade_id'] ?? 0) === (int) $grade['id'] ? 'selected' : '' ?>><?= escapeHtml($grade['name']) ?></option><?php endforeach; ?></select></td>
                                <td><select name="origin_id[]" class="form-select form-select-sm" data-master="product-origins" data-master-title="Product Origin"><option value="0">Origin</option><?php foreach ($origins as $origin): ?><option value="<?= (int) $origin['id'] ?>" <?= (int) ($item['origin_id'] ?? 0) === (int) $origin['id'] ? 'selected' : '' ?>><?= escapeHtml($origin['name']) ?></option><?php endforeach; ?></select></td>
                                <td><select name="hsn_code[]" class="form-select form-select-sm" data-master="hs-codes" data-master-title="HS Code"><option value="<?= escapeHtml($item['hsn_code'] ?? '') ?>"><?= escapeHtml($item['hsn_code'] ?? 'HS Code') ?></option></select></td>
                                <td><select name="packing_type_id[]" class="form-select form-select-sm" data-master="packing-types" data-master-title="Packaging Type"><option value="0">Pack</option><?php foreach ($packingTypes as $packing): ?><option value="<?= (int) $packing['id'] ?>" <?= (int) ($item['packing_type_id'] ?? 0) === (int) $packing['id'] ? 'selected' : '' ?>><?= escapeHtml($packing['name']) ?></option><?php endforeach; ?></select></td>
                                <td><input type="number" step="0.001" name="quantity[]" class="form-control form-control-sm calc qty" value="<?= escapeHtml($item['quantity'] ?? '') ?>"></td>
                                <td><select name="unit_id[]" class="form-select form-select-sm" data-master="units" data-master-title="Unit"><option value="0">Unit</option><?php foreach ($units as $unit): ?><option value="<?= (int) $unit['id'] ?>" <?= (int) ($item['unit_id'] ?? 0) === (int) $unit['id'] ? 'selected' : '' ?>><?= escapeHtml($unit['code'] ?? $unit['name']) ?></option><?php endforeach; ?></select></td>
                                <td><input type="number" step="0.0001" name="rate[]" class="form-control form-control-sm calc rate" value="<?= escapeHtml($item['rate'] ?? '') ?>"></td>
                                <td><input type="number" step="0.01" name="discount_percent[]" class="form-control form-control-sm calc disc" value="<?= escapeHtml($item['discount_percent'] ?? '') ?>"></td>
                                <td><input type="number" step="0.01" name="tax_percent[]" class="form-control form-control-sm calc gst" value="<?= escapeHtml($item['tax_percent'] ?? '') ?>"></td>
                                <td><input type="text" class="form-control form-control-sm amount" value="<?= escapeHtml($item['net_amount'] ?? '') ?>" readonly></td>
                            </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>

            <div class="row mt-3">
                <div class="col-md-8"><label class="form-label" for="remarks">Remarks</label><textarea id="remarks" name="remarks" class="form-control" rows="5"><?= escapeHtml($quotation['remarks'] ?? '') ?></textarea></div>
                <div class="col-md-4">
                    <label class="form-label">Charges & Totals</label>
                    <input type="number" step="0.01" name="freight" class="form-control mb-2 total-charge" placeholder="Freight" value="<?= escapeHtml($charges['freight'] ?? '') ?>">
                    <input type="number" step="0.01" name="insurance" class="form-control mb-2 total-charge" placeholder="Insurance" value="<?= escapeHtml($charges['insurance'] ?? '') ?>">
                    <input type="number" step="0.01" name="other_charges" class="form-control mb-2 total-charge" placeholder="Other Charges" value="<?= escapeHtml($charges['other_charges'] ?? '') ?>">
                    <div class="border rounded p-2 bg-light">
                        <div>Subtotal: <strong id="subtotal"><?= number_format((float) ($metaTotals['subtotal'] ?? 0), 2) ?></strong></div>
                        <div>Discount: <strong id="discountTotal"><?= number_format((float) ($metaTotals['discount'] ?? 0), 2) ?></strong></div>
                        <div>GST: <strong id="gstTotal"><?= number_format((float) ($metaTotals['gst'] ?? 0), 2) ?></strong></div>
                        <div class="fs-5">Grand Total: <strong id="grandTotal"><?= number_format((float) ($metaTotals['grand'] ?? 0), 2) ?></strong></div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary mt-3">Save Quotation</button>
        </form>
    </div>
</div>

<script>
document.querySelectorAll('.calc,.total-charge').forEach(el => el.addEventListener('input', () => {
    let subtotal = 0, discount = 0, gst = 0;
    document.querySelectorAll('#quotation-items tbody tr').forEach(row => {
        const qty = parseFloat(row.querySelector('.qty')?.value || 0);
        const rate = parseFloat(row.querySelector('.rate')?.value || 0);
        const disc = parseFloat(row.querySelector('.disc')?.value || 0);
        const tax = parseFloat(row.querySelector('.gst')?.value || 0);
        const line = qty * rate;
        const lineDisc = line * disc / 100;
        const lineGst = (line - lineDisc) * tax / 100;
        subtotal += line; discount += lineDisc; gst += lineGst;
        const amount = row.querySelector('.amount');
        if (amount) amount.value = (line - lineDisc + lineGst).toFixed(2);
    });
    const charges = Array.from(document.querySelectorAll('.total-charge')).reduce((sum, el) => sum + parseFloat(el.value || 0), 0);
    document.getElementById('subtotal').textContent = subtotal.toFixed(2);
    document.getElementById('discountTotal').textContent = discount.toFixed(2);
    document.getElementById('gstTotal').textContent = gst.toFixed(2);
    document.getElementById('grandTotal').textContent = (subtotal - discount + gst + charges).toFixed(2);
}));

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
</script>