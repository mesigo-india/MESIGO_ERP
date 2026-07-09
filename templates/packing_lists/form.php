<?php
$title = $title ?? 'Packing List';
$action = $action ?? '/packing-lists';
$packingList = $packingList ?? null;
$meta = $meta ?? [];
$items = $items ?? [[]];
$buyers = $buyers ?? [];
$currencies = $currencies ?? [];
$incoterms = $incoterms ?? [];
$paymentTerms = $paymentTerms ?? [];
$ports = $ports ?? [];
$products = $products ?? [];
$packingTypes = $packingTypes ?? [];
$statuses = $statuses ?? [];
?>
<div class="page-header">
    <h1><?= escapeHtml($title) ?></h1>
    <a href="/packing-lists" class="btn btn-outline-secondary">Back</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="post" action="<?= escapeHtml($action) ?>" class="needs-validation" novalidate>
            <?= csrfToken() ?>
            <h5 class="mb-3">Packing List Header</h5>
            <div class="row">
                <div class="col-md-3 mb-3"><label class="form-label">PL Number</label><input class="form-control" value="<?= escapeHtml($packingList['document_number'] ?? 'Auto') ?>" readonly></div>
                <div class="col-md-3 mb-3"><label class="form-label">Date</label><input type="date" name="document_date" class="form-control" value="<?= escapeHtml($packingList['document_date'] ?? date('Y-m-d')) ?>" required></div>
                <div class="col-md-3 mb-3"><label class="form-label">Revision</label><input type="number" name="revision" class="form-control" value="<?= (int) ($meta['revision'] ?? 0) ?>"></div>
                <div class="col-md-3 mb-3"><label class="form-label">Status</label><select name="status" class="form-select"><?php foreach ($statuses as $statusId => $statusName): ?><option value="<?= (int) $statusId ?>" <?= (int) ($packingList['status'] ?? 0) === (int) $statusId ? 'selected' : '' ?>><?= escapeHtml($statusName) ?></option><?php endforeach; ?></select></div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3"><label class="form-label">Buyer</label><select name="buyer_id" class="form-select" required><option value="0">Select Buyer</option><?php foreach ($buyers as $buyer): ?><option value="<?= (int) $buyer['id'] ?>" <?= (int) ($packingList['buyer_id'] ?? 0) === (int) $buyer['id'] ? 'selected' : '' ?>><?= escapeHtml($buyer['company_name']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-4 mb-3"><label class="form-label">Consignee</label><input name="consignee" class="form-control" value="<?= escapeHtml($meta['consignee'] ?? '') ?>"></div>
                <div class="col-md-4 mb-3"><label class="form-label">Notify Party</label><input name="notify_party" class="form-control" value="<?= escapeHtml($meta['notify_party'] ?? '') ?>"></div>
            </div>
            <div class="row">
                <div class="col-md-3 mb-3"><label class="form-label">Currency</label><select name="currency_id" class="form-select" data-master="currencies" data-master-title="Currency" required><option value="0">Select</option><?php foreach ($currencies as $currency): ?><option value="<?= (int) $currency['id'] ?>" <?= (int) ($packingList['currency_id'] ?? 0) === (int) $currency['id'] ? 'selected' : '' ?>><?= escapeHtml($currency['code']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-3 mb-3"><label class="form-label">Incoterm</label><select name="incoterm_id" class="form-select" data-master="incoterms" data-master-title="Incoterm"><option value="0">Select</option><?php foreach ($incoterms as $incoterm): ?><option value="<?= (int) $incoterm['id'] ?>" <?= (int) ($packingList['incoterm_id'] ?? 0) === (int) $incoterm['id'] ? 'selected' : '' ?>><?= escapeHtml($incoterm['code']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-3 mb-3"><label class="form-label">Payment Term</label><select name="payment_term_id" class="form-select" data-master="payment-terms" data-master-title="Payment Term"><option value="0">Select</option><?php foreach ($paymentTerms as $term): ?><option value="<?= (int) $term['id'] ?>" <?= (int) ($packingList['payment_term_id'] ?? 0) === (int) $term['id'] ? 'selected' : '' ?>><?= escapeHtml($term['name']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-3 mb-3"><label class="form-label">Shipment Terms</label><select name="shipment_term" class="form-select" data-master="shipping-terms" data-master-title="Shipping Term"><option value="<?= escapeHtml($meta['shipment_term'] ?? ($packingList['shipment_type'] ?? '')) ?>"><?= escapeHtml($meta['shipment_term'] ?? ($packingList['shipment_type'] ?? 'Select')) ?></option></select></div>
            </div>
            <div class="row">
                <div class="col-md-3 mb-3"><label class="form-label">Loading Port</label><select name="loading_port_id" class="form-select" data-master="ports" data-master-title="Port"><option value="0">Select</option><?php foreach ($ports as $port): ?><option value="<?= (int) $port['id'] ?>" <?= (int) ($packingList['loading_port_id'] ?? 0) === (int) $port['id'] ? 'selected' : '' ?>><?= escapeHtml($port['name']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-3 mb-3"><label class="form-label">Destination Port</label><select name="delivery_port_id" class="form-select" data-master="ports" data-master-title="Port"><option value="0">Select</option><?php foreach ($ports as $port): ?><option value="<?= (int) $port['id'] ?>" <?= (int) ($packingList['destination_port_id'] ?? 0) === (int) $port['id'] ? 'selected' : '' ?>><?= escapeHtml($port['name']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-3 mb-3"><label class="form-label">Container No</label><input name="container_no" class="form-control" value="<?= escapeHtml($meta['container_no'] ?? '') ?>"></div>
                <div class="col-md-3 mb-3"><label class="form-label">Seal No</label><input name="seal_no" class="form-control" value="<?= escapeHtml($meta['seal_no'] ?? '') ?>"></div>
            </div>
            <div class="row">
                <div class="col-md-3 mb-3"><label class="form-label">Marks & Numbers</label><input name="marks_numbers" class="form-control" value="<?= escapeHtml($meta['marks_numbers'] ?? '') ?>"></div>
                <div class="col-md-2 mb-3"><label class="form-label">Total Packages</label><input id="totalPackages" name="total_packages" class="form-control" value="<?= escapeHtml($meta['total_packages'] ?? '0') ?>" readonly></div>
                <div class="col-md-2 mb-3"><label class="form-label">Package Type</label><input name="package_type" class="form-control" value="<?= escapeHtml($meta['package_type'] ?? '') ?>"></div>
                <div class="col-md-2 mb-3"><label class="form-label">Net Weight</label><input id="totalNet" name="net_weight" class="form-control" value="<?= escapeHtml($meta['net_weight'] ?? '0') ?>" readonly></div>
                <div class="col-md-3 mb-3"><label class="form-label">Gross Weight</label><input id="totalGross" name="gross_weight" class="form-control" value="<?= escapeHtml($meta['gross_weight'] ?? '0') ?>" readonly></div>
            </div>
            <h5 class="mt-4 mb-3">Multi-product Packing Grid</h5>
            <div class="table-responsive"><table class="table table-bordered" id="packing-grid"><thead><tr><th>Product</th><th>HS Code</th><th>Package Type</th><th>No. of Bags</th><th>Net Weight</th><th>Gross Weight</th><th>Dimensions</th><th>Remarks</th></tr></thead><tbody><?php for ($i = 0; $i < 8; $i++): $item = $items[$i] ?? []; ?><tr><td><select name="product_id[]" class="form-select form-select-sm"><option value="0">Product</option><?php foreach ($products as $product): ?><option value="<?= (int) $product['id'] ?>" <?= (int) ($item['product_id'] ?? 0) === (int) $product['id'] ? 'selected' : '' ?>><?= escapeHtml($product['name']) ?></option><?php endforeach; ?></select></td><td><select name="hsn_code[]" class="form-select form-select-sm" data-master="hs-codes" data-master-title="HS Code"><option value="<?= escapeHtml($item['hsn_code'] ?? '') ?>"><?= escapeHtml($item['hsn_code'] ?? 'HS Code') ?></option></select></td><td><select name="packing_type_id[]" class="form-select form-select-sm" data-master="packing-types" data-master-title="Packaging Type"><option value="0">Type</option><?php foreach ($packingTypes as $type): ?><option value="<?= (int) $type['id'] ?>" <?= (int) ($item['packing_type_id'] ?? 0) === (int) $type['id'] ? 'selected' : '' ?>><?= escapeHtml($type['name']) ?></option><?php endforeach; ?></select><input type="hidden" name="unit_id[]" value="<?= (int) ($item['unit_id'] ?? 0) ?>"></td><td><input type="number" step="1" name="no_of_bags[]" class="form-control form-control-sm pack-calc bags" value="<?= escapeHtml($item['no_of_bags'] ?? ($item['quantity'] ?? '')) ?>"></td><td><input type="number" step="0.001" name="net_weight_item[]" class="form-control form-control-sm pack-calc net" value="<?= escapeHtml($item['net_weight'] ?? '') ?>"></td><td><input type="number" step="0.001" name="gross_weight_item[]" class="form-control form-control-sm pack-calc gross" value="<?= escapeHtml($item['gross_weight'] ?? '') ?>"></td><td><input name="dimensions[]" class="form-control form-control-sm" value="<?= escapeHtml($item['dimensions'] ?? '') ?>"></td><td><input name="item_remarks[]" class="form-control form-control-sm" value="<?= escapeHtml($item['item_remarks'] ?? '') ?>"></td></tr><?php endfor; ?></tbody></table></div>
            <div class="mb-3"><label class="form-label">Remarks</label><textarea name="remarks" class="form-control" rows="3"><?= escapeHtml($packingList['remarks'] ?? '') ?></textarea></div>
            <input type="hidden" name="valid_until" value=""><input type="hidden" name="freight" value="0"><input type="hidden" name="insurance" value="0"><input type="hidden" name="other_charges" value="0"><button class="btn btn-primary">Save Packing List</button>
        </form>
    </div>
</div>
<script>
function recalcPacking(){let bags=0,net=0,gross=0;document.querySelectorAll('#packing-grid tbody tr').forEach(r=>{bags+=parseFloat(r.querySelector('.bags')?.value||0);net+=parseFloat(r.querySelector('.net')?.value||0);gross+=parseFloat(r.querySelector('.gross')?.value||0);});document.getElementById('totalPackages').value=bags.toFixed(0);document.getElementById('totalNet').value=net.toFixed(3);document.getElementById('totalGross').value=gross.toFixed(3);}document.querySelectorAll('.pack-calc').forEach(e=>e.addEventListener('input',recalcPacking));recalcPacking();
</script>
