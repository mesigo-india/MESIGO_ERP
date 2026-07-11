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
            <style>
                /* Remove HTML5 number input spinners globally within this form context */
                input[type="number"]::-webkit-outer-spin-button,
                input[type="number"]::-webkit-inner-spin-button {
                    -webkit-appearance: none;
                    margin: 0;
                }
                input[type="number"] {
                    -moz-appearance: textfield;
                }

                /* Enterprise Font Standard (Inter / Segoe UI) */
                #quotation-items, 
                .product-line-group, 
                .mesigo-input-common,
                .select2-container--bootstrap-5,
                .btn-grid-action {
                    font-family: 'Inter', 'Segoe UI', -apple-system, sans-serif !important;
                    font-size: 13px !important;
                }

                /* Sticky Header - Dark Navy */
                .mesigo-grid-header th {
                    background-color: #0b1c3d !important;
                    color: #ffffff !important;
                    text-transform: uppercase;
                    font-size: 13px !important;
                    letter-spacing: 0.5px;
                    vertical-align: middle;
                    font-weight: 700;
                    border: none !important;
                    padding: 12px 10px !important;
                }

                /* Product Line Spacing & Hover Styles */
                .product-line-group {
                    border-bottom: 8px solid #f3f4f6 !important;
                }
                .mesigo-grid-row {
                    height: 52px !important;
                }
                .mesigo-grid-row:hover {
                    background-color: #f8fafc !important;
                }

                /* Consistent Input Styles */
                .mesigo-input-common {
                    height: 38px !important;
                    border-radius: 6px !important;
                    border: 1px solid #ced4da !important;
                    box-shadow: inset 0 1px 2px rgba(0,0,0,0.02) !important;
                    padding: 6px 12px !important;
                    vertical-align: middle !important;
                    background-color: #ffffff;
                }
                .mesigo-input-common:focus {
                    outline: none !important;
                    border-color: #3b82f6 !important;
                    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25) !important;
                }

                /* Amount Field (Bold, Readonly, Light Grey) */
                .mesigo-amount-input {
                    background-color: #f3f4f6 !important;
                    color: #1f2937 !important;
                    font-weight: 700 !important;
                    border: 1px solid #ced4da !important;
                    cursor: not-allowed !important;
                }

                /* Select2 Dropdown Consistency */
                .select2-container--bootstrap-5 .select2-selection--single {
                    height: 38px !important;
                    border-radius: 6px !important;
                    border: 1px solid #ced4da !important;
                    display: flex !important;
                    align-items: center !important;
                    padding: 0 12px !important;
                    box-shadow: inset 0 1px 2px rgba(0,0,0,0.02) !important;
                }
                .select2-container--bootstrap-5 .select2-selection__rendered {
                    font-size: 13px !important;
                    color: #495057 !important;
                    padding-left: 0 !important;
                    padding-right: 20px !important;
                    white-space: nowrap !important;
                    overflow: hidden !important;
                    text-overflow: ellipsis !important;
                }
                .select2-container--bootstrap-5 .select2-selection__arrow {
                    height: 36px !important;
                }

                /* System Generated / Read-Only Fields (Grey Background, Not Allowed Cursor) */
                .mesigo-readonly-field {
                    background-color: #f3f4f6 !important;
                    color: #4b5563 !important;
                    cursor: not-allowed !important;
                    pointer-events: none !important;
                    border: 1px solid #ced4da !important;
                    font-weight: 500 !important;
                }
                select.mesigo-readonly-field {
                    appearance: none !important;
                    -webkit-appearance: none !important;
                    -moz-appearance: none !important;
                    background-image: none !important;
                }

                /* Button Styling Consistency */
                .btn-grid-action,
                #btn-add-product,
                #btn-duplicate-product,
                #btn-delete-product,
                #btn-recalculate,
                .btn-toggle-details,
                .btn-duplicate-row,
                .btn-remove-row {
                    height: 38px !important;
                    font-size: 13px !important;
                    border-radius: 6px !important;
                    display: inline-flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                    font-weight: 600 !important;
                    transition: all 0.2s ease !important;
                }
                .btn-toggle-details,
                .btn-duplicate-row,
                .btn-remove-row {
                    width: 38px !important;
                    padding: 0 !important;
                    margin-right: 4px;
                }
                
                /* Details Panel card spacing */
                .details-card {
                    background-color: #ffffff;
                    border: 1px solid #e5e7eb;
                    border-radius: 8px;
                    padding: 16px;
                    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
                }
            </style>
            
            <div class="bg-white p-4 rounded mb-4 shadow-sm border border-light">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="text-secondary font-weight-bold m-0" style="color: #0b1c3d !important;">
                        <i class="fa fa-list-alt me-2 text-primary"></i> 3. Multi Product Sourcing Grid
                    </h5>
                    <button type="button" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm" id="btn-add-product" style="border-width: 2px;">
                        <i class="fa fa-plus me-1"></i> Add Product Line
                    </button>
                </div>
                
                <div class="table-responsive" style="overflow-x: auto; border-radius: 8px; box-shadow: 0 0 15px rgba(0,0,0,0.05);">
                    <table class="table table-bordered mb-0 align-middle" id="quotation-items" style="min-width: 1300px; border-collapse: separate; border-spacing: 0;">
                        <thead class="mesigo-grid-header sticky-top">
                            <tr class="text-center" style="height: 45px;">
                                <th style="width: 30%; padding: 12px 8px;">Product</th>
                                <th style="width: 10%; padding: 12px 8px;">Qty</th>
                                <th style="width: 10%; padding: 12px 8px;">Unit</th>
                                <th style="width: 12%; padding: 12px 8px;">Rate</th>
                                <th style="width: 8%; padding: 12px 8px;">Disc %</th>
                                <th style="width: 8%; padding: 12px 8px;">GST %</th>
                                <th style="width: 14%; padding: 12px 8px;">Amount</th>
                                <th style="width: 8%; padding: 12px 8px;">Actions</th>
                            </tr>
                        </thead>
                        
                            <?php 
                            $count = max(1, count($items));
                            for ($i = 0; $i < $count; $i++): 
                                $item = $items[$i] ?? []; 
                            ?>
                            <tbody class="product-line-group border-top-0 bg-white">
                                <tr class="product-row mesigo-grid-row" style="height: 60px;">
                                    <td class="p-2 border-end-0 border-start-0">
                                        <select name="product_id[]" class="form-select form-select-sm select2-grid mesigo-input-common" required>
                                            <option value="0">Select Product</option>
                                            <?php foreach ($products as $product): ?>
                                                <option value="<?= (int) $product['id'] ?>" <?= (int) ($item['product_id'] ?? 0) === (int) $product['id'] ? 'selected' : '' ?>>
                                                    <?= escapeHtml($product['name']) ?> - <?= escapeHtml($product['product_code'] ?? '') ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback">Product is required</div>
                                    </td>
                                    
                                    <td class="p-2 border-end-0 border-start-0">
                                        <input type="number" step="0.001" name="quantity[]" class="form-control form-control-sm calc qty text-end mesigo-input-common" value="<?= escapeHtml($item['quantity'] ?? '') ?>" placeholder="0.000" required min="0.001">
                                        <div class="invalid-feedback">Qty > 0</div>
                                    </td>
                                    <td class="p-2 border-end-0 border-start-0">
                                        <select name="unit_id[]" class="form-select form-select-sm mesigo-input-common text-center">
                                            <option value="0">Unit</option>
                                            <?php foreach ($units as $unit): ?>
                                                <option value="<?= (int) $unit['id'] ?>" <?= (int) ($item['unit_id'] ?? 0) === (int) $unit['id'] ? 'selected' : '' ?>><?= escapeHtml($unit['code'] ?? $unit['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td class="p-2 border-end-0 border-start-0">
                                        <input type="number" step="0.0001" name="rate[]" class="form-control form-control-sm calc rate text-end mesigo-input-common" value="<?= escapeHtml($item['rate'] ?? '') ?>" placeholder="0.0000" required min="0.0001">
                                        <div class="invalid-feedback">Rate > 0</div>
                                    </td>
                                    <td class="p-2 border-end-0 border-start-0">
                                        <input type="number" step="0.01" name="discount_percent[]" class="form-control form-control-sm calc disc text-center mesigo-input-common" value="<?= escapeHtml($item['discount_percent'] ?? '0.00') ?>" placeholder="0.00">
                                    </td>
                                    <td class="p-2 border-end-0 border-start-0">
                                        <input type="number" step="0.01" name="tax_percent[]" class="form-control form-control-sm calc gst text-center mesigo-input-common" value="<?= escapeHtml($item['tax_percent'] ?? '0.00') ?>" placeholder="0.00">
                                    </td>
                                    <td class="p-2 border-end-0 border-start-0">
                                        <input type="text" class="form-control form-control-sm amount text-end mesigo-input-common mesigo-amount-input" value="<?= escapeHtml($item['net_amount'] ?? '0.00') ?>" readonly tabindex="-1">
                                    </td>
                                    <td class="p-2 text-center border-end-0 border-start-0 text-nowrap">
                                        <button type="button" class="btn btn-outline-info btn-sm btn-toggle-details rounded" style="height: 38px; width: 38px; border-width: 1px;" title="Toggle Details"><i class="fa fa-chevron-down"></i></button>
                                        <button type="button" class="btn btn-outline-primary btn-sm btn-duplicate-row rounded" style="height: 38px; width: 38px; border-width: 1px;" title="Duplicate Line"><i class="fa fa-copy"></i></button>
                                        <button type="button" class="btn btn-outline-danger btn-sm btn-remove-row rounded" style="height: 38px; width: 38px; border-width: 1px;" title="Remove Line"><i class="fa fa-trash"></i></button>
                                    </td>
                                </tr>
                                
                                <tr class="details-row bg-light" style="display: none;">
                                    <td colspan="8" class="p-3 border-bottom shadow-inner" style="box-shadow: inset 0 3px 5px rgba(0,0,0,0.03);">
                                        <div class="details-card">
                                            <div class="row g-3">
                                                <div class="col-md-3">
                                                    <label class="form-label fw-bold text-muted mb-1" style="font-size: 0.75rem;">Source Warehouse</label>
                                                    <select name="warehouse_id[]" class="form-select form-select-sm mesigo-input-common mesigo-readonly-field" tabindex="-1">
                                                        <option value="0">Warehouse</option>
                                                        <?php foreach ($warehouses as $wh): ?>
                                                            <option value="<?= (int) $wh['id'] ?>" <?= (int) ($item['warehouse_id'] ?? 0) === (int) $wh['id'] ? 'selected' : '' ?>><?= escapeHtml($wh['name']) ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label fw-bold text-muted mb-1" style="font-size: 0.75rem;">Grade</label>
                                                    <select name="grade_id[]" class="form-select form-select-sm mesigo-input-common mesigo-readonly-field" tabindex="-1">
                                                        <option value="0">Select Grade</option>
                                                        <?php foreach ($grades as $grade): ?>
                                                            <option value="<?= (int) $grade['id'] ?>" <?= (int) ($item['grade_id'] ?? 0) === (int) $grade['id'] ? 'selected' : '' ?>><?= escapeHtml($grade['name']) ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label fw-bold text-muted mb-1" style="font-size: 0.75rem;">Origin</label>
                                                    <select name="origin_id[]" class="form-select form-select-sm mesigo-input-common mesigo-readonly-field" tabindex="-1">
                                                        <option value="0">Select Origin</option>
                                                        <?php foreach ($origins as $origin): ?>
                                                            <option value="<?= (int) $origin['id'] ?>" <?= (int) ($item['origin_id'] ?? 0) === (int) $origin['id'] ? 'selected' : '' ?>><?= escapeHtml($origin['name']) ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label fw-bold text-muted mb-1" style="font-size: 0.75rem;">HS Code</label>
                                                    <input type="text" name="hsn_code[]" class="form-control form-control-sm text-center font-monospace mesigo-input-common mesigo-readonly-field" value="<?= escapeHtml($item['hsn_code'] ?? '') ?>" tabindex="-1">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label fw-bold text-muted mb-1" style="font-size: 0.75rem;">Packing</label>
                                                    <select name="packing_type_id[]" class="form-select form-select-sm mesigo-input-common mesigo-readonly-field" tabindex="-1">
                                                        <option value="0">Select Pack</option>
                                                        <?php foreach ($packingTypes as $packing): ?>
                                                            <option value="<?= (int) $packing['id'] ?>" <?= (int) ($item['packing_type_id'] ?? 0) === (int) $packing['id'] ? 'selected' : '' ?>><?= escapeHtml($packing['name']) ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold text-muted mb-1" style="font-size: 0.75rem;">Product Description</label>
                                                    <input type="text" name="description[]" class="form-control form-control-sm mesigo-input-common mesigo-readonly-field" value="<?= escapeHtml($item['description'] ?? '') ?>" tabindex="-1">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold text-muted mb-1" style="font-size: 0.75rem;">Specification Notes</label>
                                                    <input type="text" name="specification[]" class="form-control form-control-sm mesigo-input-common mesigo-readonly-field" value="<?= escapeHtml($item['specification'] ?? '') ?>" tabindex="-1">
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                            <?php endfor; ?>
                        
                    </table>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mt-3 p-2 rounded bg-light border">
                    <div class="d-flex gap-2">
                        <button type="button" id="btn-add-row-bottom" class="btn btn-white btn-grid-action shadow-sm border fw-bold text-secondary"><i class="fa fa-plus text-success me-1"></i> Add Row</button>
                        <button type="button" id="btn-duplicate-product" class="btn btn-white btn-grid-action shadow-sm border fw-bold text-secondary"><i class="fa fa-copy text-info me-1"></i> Duplicate Row</button>
                        <button type="button" id="btn-delete-product" class="btn btn-white btn-grid-action shadow-sm border fw-bold text-secondary"><i class="fa fa-trash text-danger me-1"></i> Delete Last</button>
                        <button type="button" id="btn-recalculate" class="btn btn-white btn-grid-action shadow-sm border fw-bold text-secondary"><i class="fa fa-sync text-primary me-1"></i> Recalculate</button>
                    </div>
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
                    <div class="card shadow-sm border-0" style="border-radius: 10px; overflow: hidden;">
                        <div class="card-header bg-white border-bottom-0 pt-4 pb-0 text-center">
                            <h6 class="text-uppercase text-secondary fw-bold letter-spacing-1 mb-0" style="font-size: 0.85rem;">Invoice Summary</h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between mb-3 text-secondary align-items-center">
                                <span class="fw-medium">Subtotal</span>
                                <span class="font-weight-bold fs-5 text-dark" id="subtotal"><?= number_format((float) ($metaTotals['subtotal'] ?? 0), 2) ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-3 align-items-center text-danger">
                                <span class="fw-medium"><i class="fa fa-minus-circle me-1 small"></i> Discount</span>
                                <span class="font-weight-bold" id="discountTotal">-<?= number_format((float) ($metaTotals['discount'] ?? 0), 2) ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-3 align-items-center text-info">
                                <span class="fw-medium"><i class="fa fa-plus-circle me-1 small"></i> Tax (GST)</span>
                                <span class="font-weight-bold" id="gstTotal"><?= number_format((float) ($metaTotals['gst'] ?? 0), 2) ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-4 align-items-center text-warning" style="padding-bottom: 15px; border-bottom: 2px dashed #e9ecef;">
                                <span class="fw-medium"><i class="fa fa-truck me-1 small"></i> Dynamic Charges</span>
                                <span class="font-weight-bold fs-6 text-dark" id="chargesTotal">0.00</span>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center bg-success text-white p-3 rounded shadow-sm" style="margin: -10px; border-radius: 8px !important;">
                                <span class="fw-bold text-uppercase" style="font-size: 0.9rem; letter-spacing: 0.5px;">Grand Total</span>
                                <span class="fw-bolder fs-3" id="grandTotal"><?= number_format((float) ($metaTotals['grand'] ?? 0), 2) ?></span>
                            </div>
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
// 1. Calculations script globally accessible
window.calculate = function() {
    console.log("[DEBUG] calculate() started.");
    let subtotal = 0;
    let discount = 0;
    let gst = 0;

    const rows = document.querySelectorAll('#quotation-items tbody.product-line-group tr.product-row');
    console.log("[DEBUG] calculate() found product rows:", rows.length);

    rows.forEach((row, index) => {
        const qtyEl = row.querySelector('.qty');
        const rateEl = row.querySelector('.rate');
        const discEl = row.querySelector('.disc');
        const gstEl = row.querySelector('.gst');
        
        console.log(`[DEBUG] Row ${index} elements - qtyEl:`, !!qtyEl, 'rateEl:', !!rateEl);
        
        const qty = parseFloat(qtyEl?.value || 0);
        const rate = parseFloat(rateEl?.value || 0);
        const disc = parseFloat(discEl?.value || 0);
        const tax = parseFloat(gstEl?.value || 0);
        
        console.log(`[DEBUG] Row ${index} values - qty: ${qty}, rate: ${rate}, disc: ${disc}, tax: ${tax}`);
        
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

    // Sum costing sheet charges with multi-currency normalization
    const globalRate = parseFloat(document.getElementById('exchange_rate')?.value || 1) || 1;
    let totalCharges = 0;
    document.querySelectorAll('#costing-charges tbody tr.charge-row').forEach(row => {
        const amt = parseFloat(row.querySelector('.charge-amt')?.value || 0);
        const chargeRate = parseFloat(row.querySelector('.charge-rate')?.value || globalRate) || globalRate;
        
        // Normalize charge to Base Currency, then to Document Currency
        const docAmt = amt * (chargeRate / globalRate);
        totalCharges += docAmt;
    });

    const subTotalEl = document.getElementById('subtotal');
    if(subTotalEl) subTotalEl.textContent = subtotal.toFixed(2);
    
    const discTotalEl = document.getElementById('discountTotal');
    if(discTotalEl) discTotalEl.textContent = discount.toFixed(2);
    
    const gstTotalEl = document.getElementById('gstTotal');
    if(gstTotalEl) gstTotalEl.textContent = gst.toFixed(2);
    
    const chargesTotalEl = document.getElementById('chargesTotal');
    if(chargesTotalEl) chargesTotalEl.textContent = totalCharges.toFixed(2);
    
    const grandTotalEl = document.getElementById('grandTotal');
    if(grandTotalEl) grandTotalEl.textContent = (subtotal - discount + gst + totalCharges).toFixed(2);
};

document.addEventListener('DOMContentLoaded', function() {
    console.log("[DEBUG] DOMContentLoaded in form.php");
    // Event listeners delegation
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('calc') || e.target.classList.contains('calc-charge')) {
            console.log("[DEBUG] Form input event triggered on:", e.target.name || e.target.className);
            window.calculate();
        }
    });

    // Handle currency changes to set initial exchange rates automatically
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('charge-curr')) {
            const selectedOpt = e.target.options[e.target.selectedIndex];
            const rate = selectedOpt.dataset.rate || '1.000000';
            const rateInput = e.target.closest('tr').querySelector('.charge-rate');
            if (rateInput) {
                rateInput.value = parseFloat(rate).toFixed(6);
                window.calculate();
            }
        }
        if (e.target.id === 'currency_id') {
            const selectedOpt = e.target.options[e.target.selectedIndex];
            const rate = selectedOpt.dataset.rate || '1.000000';
            const rateInput = document.getElementById('exchange_rate');
            if (rateInput) {
                rateInput.value = parseFloat(rate).toFixed(6);
                window.calculate();
            }
        }
    });

    // Toggle details row
    document.addEventListener('click', function(e) {
        const toggleBtn = e.target.closest('.btn-toggle-details');
        if (toggleBtn) {
            console.log("[DEBUG] Toggle details clicked");
            const tbody = toggleBtn.closest('.product-line-group');
            console.log("[DEBUG] Found tbody parent:", !!tbody);
            const detailsRow = tbody ? tbody.querySelector('.details-row') : null;
            console.log("[DEBUG] Found detailsRow:", !!detailsRow);
            if (detailsRow) {
                const icon = toggleBtn.querySelector('i');
                if (detailsRow.style.display === 'none') {
                    console.log("[DEBUG] Showing detailsRow");
                    detailsRow.style.display = 'table-row';
                    icon.classList.remove('fa-chevron-down');
                    icon.classList.add('fa-chevron-up');
                } else {
                    console.log("[DEBUG] Hiding detailsRow");
                    detailsRow.style.display = 'none';
                    icon.classList.remove('fa-chevron-up');
                    icon.classList.add('fa-chevron-down');
                }
            }
        }
        
        // Handle duplicate row
        const duplicateBtn = e.target.closest('.btn-duplicate-row');
        if (duplicateBtn) {
            const tbody = duplicateBtn.closest('.product-line-group');
            const newGroup = tbody.cloneNode(true);
            
            // Generate a fresh state for the duplicated row
            const detailsRow = newGroup.querySelector('.details-row');
            if (detailsRow) detailsRow.style.display = 'none';
            
            const toggleIcon = newGroup.querySelector('.btn-toggle-details i');
            if (toggleIcon) {
                toggleIcon.classList.remove('fa-chevron-up');
                toggleIcon.classList.add('fa-chevron-down');
            }
            
            // Clean up select2 elements and re-initialize
            newGroup.querySelectorAll('select').forEach(select => {
                if(select.nextElementSibling && select.nextElementSibling.classList.contains('select2')) {
                    select.nextElementSibling.remove();
                }
                select.classList.remove('select2-hidden-accessible');
                select.removeAttribute('data-select2-id');
            });
            
            tbody.after(newGroup);
            
            if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
                jQuery(newGroup).find('.select2-grid').select2({
                    theme: 'bootstrap-5',
                    width: '100%'
                });
            }
            window.calculate();
        }
        
        // Handle remove row
        const removeBtn = e.target.closest('.btn-remove-row');
        if (removeBtn) {
            const tbody = removeBtn.closest('.product-line-group');
            if (document.querySelectorAll('.product-line-group').length > 1) {
                tbody.remove();
                window.calculate();
            } else {
                alert('At least one product line is required.');
            }
        }
    });

    // Global Grid Actions
    document.addEventListener('click', function(e) {
        // Recalculate
        if (e.target.closest('#btn-recalculate')) {
            window.calculate();
            return;
        }
        
        // Add Row
        if (e.target.closest('#btn-add-product') || e.target.closest('#btn-add-row-bottom')) {
            const table = document.querySelector('#quotation-items');
            const firstGroup = table.querySelector('.product-line-group');
            if (!firstGroup) return;

            const newGroup = firstGroup.cloneNode(true);
            newGroup.querySelectorAll('input').forEach(input => {
                input.value = input.classList.contains('amount') ? '0.00' : '';
            });
            newGroup.querySelectorAll('select').forEach(select => {
                select.selectedIndex = 0;
                if(select.nextElementSibling && select.nextElementSibling.classList.contains('select2')) {
                    select.nextElementSibling.remove();
                }
                select.classList.remove('select2-hidden-accessible');
                select.removeAttribute('data-select2-id');
            });
            
            const detailsRow = newGroup.querySelector('.details-row');
            if(detailsRow) detailsRow.style.display = 'none';
            const toggleIcon = newGroup.querySelector('.btn-toggle-details i');
            if(toggleIcon) {
                toggleIcon.classList.remove('fa-chevron-up');
                toggleIcon.classList.add('fa-chevron-down');
            }

            table.appendChild(newGroup);
            
            if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
                jQuery(newGroup).find('.select2-grid').select2({ theme: 'bootstrap-5', width: '100%' });
            }
            window.calculate();
            return;
        }

        // Duplicate Last Row
        if (e.target.closest('#btn-duplicate-product')) {
            const groups = document.querySelectorAll('#quotation-items .product-line-group');
            if (groups.length === 0) return;
            const lastGroup = groups[groups.length - 1];
            
            const newGroup = lastGroup.cloneNode(true);
            
            const detailsRow = newGroup.querySelector('.details-row');
            if (detailsRow) detailsRow.style.display = 'none';
            const toggleIcon = newGroup.querySelector('.btn-toggle-details i');
            if (toggleIcon) {
                toggleIcon.classList.remove('fa-chevron-up');
                toggleIcon.classList.add('fa-chevron-down');
            }
            
            newGroup.querySelectorAll('select').forEach(select => {
                if(select.nextElementSibling && select.nextElementSibling.classList.contains('select2')) {
                    select.nextElementSibling.remove();
                }
                select.classList.remove('select2-hidden-accessible');
                select.removeAttribute('data-select2-id');
            });
            
            lastGroup.after(newGroup);
            
            if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
                jQuery(newGroup).find('.select2-grid').select2({ theme: 'bootstrap-5', width: '100%' });
            }
            window.calculate();
            return;
        }

        // Delete Last Row
        if (e.target.closest('#btn-delete-product')) {
            const groups = document.querySelectorAll('#quotation-items .product-line-group');
            if (groups.length > 1) {
                groups[groups.length - 1].remove();
                window.calculate();
            } else {
                alert('At least one product line is required.');
            }
            return;
        }
    });

    // 3. Add expense charge line dynamically
    document.getElementById('btn-add-charge')?.addEventListener('click', function() {
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