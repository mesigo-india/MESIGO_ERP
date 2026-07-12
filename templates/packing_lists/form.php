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
$grades = $grades ?? [];
$origins = $origins ?? [];
$packingTypes = $packingTypes ?? [];
$units = $units ?? [];
$statuses = $statuses ?? [];

// Selected container type defaults
$selectedContainerType = $meta['selected_container_type'] ?? '20FT';
?>
<div class="page-header d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 fw-bold mb-0 text-dark"><i class="fas fa-pallet text-primary me-2"></i><?= escapeHtml($title) ?></h1>
        <span class="text-muted small">Configure shipment packaging, weights, volumes, and container stuffing optimization.</span>
    </div>
    <a href="/packing-lists" class="btn btn-outline-secondary btn-sm"><i class="fa fa-arrow-left me-1"></i> Back</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form method="post" action="<?= escapeHtml($action) ?>" class="needs-validation" novalidate id="packing-list-form">
            <?= csrfToken() ?>
            
            <h5 class="fw-bold mb-3 text-primary border-bottom pb-2"><i class="fas fa-file-invoice me-2"></i>Packing List Header</h5>
            
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">PL Number</label>
                    <input class="form-control" value="<?= escapeHtml($packingList['document_number'] ?? 'Auto') ?>" readonly tabindex="-1" style="background-color: #f8f9fa;">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Date</label>
                    <input type="date" name="document_date" class="form-control" value="<?= escapeHtml($packingList['document_date'] ?? date('Y-m-d')) ?>" required>
                    <div class="invalid-feedback">Please choose a valid date.</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Revision</label>
                    <input type="number" name="revision" class="form-control" value="<?= (int) ($meta['revision'] ?? 0) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Status</label>
                    <select name="status" class="form-select">
                        <?php foreach ($statuses as $statusId => $statusName): ?>
                            <option value="<?= (int) $statusId ?>" <?= (int) ($packingList['status'] ?? 0) === (int) $statusId ? 'selected' : '' ?>><?= escapeHtml($statusName) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Buyer</label>
                    <select name="buyer_id" class="form-select select2-init" required>
                        <option value="0">Select Buyer</option>
                        <?php foreach ($buyers as $buyer): ?>
                            <option value="<?= (int) $buyer['id'] ?>" <?= (int) ($packingList['buyer_id'] ?? 0) === (int) $buyer['id'] ? 'selected' : '' ?>><?= escapeHtml($buyer['company_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback">Buyer selection is required.</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Consignee</label>
                    <textarea name="consignee" class="form-control" rows="1" placeholder="Consignee Address"><?= escapeHtml($meta['consignee'] ?? '') ?></textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Notify Party</label>
                    <textarea name="notify_party" class="form-control" rows="1" placeholder="Notify Party Details"><?= escapeHtml($meta['notify_party'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Currency</label>
                    <select name="currency_id" class="form-select select2-init" required>
                        <option value="0">Select Currency</option>
                        <?php foreach ($currencies as $currency): ?>
                            <option value="<?= (int) $currency['id'] ?>" <?= (int) ($packingList['currency_id'] ?? 0) === (int) $currency['id'] ? 'selected' : '' ?>><?= escapeHtml($currency['code']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Incoterm</label>
                    <select name="incoterm_id" class="form-select">
                        <option value="0">Select</option>
                        <?php foreach ($incoterms as $incoterm): ?>
                            <option value="<?= (int) $incoterm['id'] ?>" <?= (int) ($packingList['incoterm_id'] ?? 0) === (int) $incoterm['id'] ? 'selected' : '' ?>><?= escapeHtml($incoterm['code']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Payment Term</label>
                    <select name="payment_term_id" class="form-select">
                        <option value="0">Select</option>
                        <?php foreach ($paymentTerms as $term): ?>
                            <option value="<?= (int) $term['id'] ?>" <?= (int) ($packingList['payment_term_id'] ?? 0) === (int) $term['id'] ? 'selected' : '' ?>><?= escapeHtml($term['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Shipment Terms</label>
                    <select name="shipment_term" class="form-select">
                        <option value="Sea" <?= ($meta['shipment_term'] ?? $packingList['shipment_type'] ?? '') === 'Sea' ? 'selected' : '' ?>>Sea Freight</option>
                        <option value="Air" <?= ($meta['shipment_term'] ?? $packingList['shipment_type'] ?? '') === 'Air' ? 'selected' : '' ?>>Air Freight</option>
                        <option value="Road" <?= ($meta['shipment_term'] ?? $packingList['shipment_type'] ?? '') === 'Road' ? 'selected' : '' ?>>Road Transport</option>
                    </select>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Loading Port</label>
                    <select name="loading_port_id" class="form-select select2-init">
                        <option value="0">Select Port</option>
                        <?php foreach ($ports as $port): ?>
                            <option value="<?= (int) $port['id'] ?>" <?= (int) ($packingList['loading_port_id'] ?? 0) === (int) $port['id'] ? 'selected' : '' ?>><?= escapeHtml($port['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Destination Port</label>
                    <select name="delivery_port_id" class="form-select select2-init">
                        <option value="0">Select Port</option>
                        <?php foreach ($ports as $port): ?>
                            <option value="<?= (int) $port['id'] ?>" <?= (int) ($packingList['destination_port_id'] ?? 0) === (int) $port['id'] ? 'selected' : '' ?>><?= escapeHtml($port['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Container No</label>
                    <input name="container_no" class="form-control" value="<?= escapeHtml($meta['container_no'] ?? '') ?>" placeholder="e.g. MSKU1234567">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Seal No</label>
                    <input name="seal_no" class="form-control" value="<?= escapeHtml($meta['seal_no'] ?? '') ?>" placeholder="e.g. SL987654">
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Marks & Numbers</label>
                    <input name="marks_numbers" class="form-control" value="<?= escapeHtml($meta['marks_numbers'] ?? '') ?>" placeholder="Shipping marks & packaging identification numbers">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Standard Package Type</label>
                    <input name="package_type" class="form-control" value="<?= escapeHtml($meta['package_type'] ?? '') ?>" placeholder="e.g. Bags / Boxes / Cartons">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Selected Container For Stuffing</label>
                    <select name="selected_container_type" id="selectedContainerType" class="form-select">
                        <option value="20FT" <?= $selectedContainerType === '20FT' ? 'selected' : '' ?>>20FT Dry Container (24,000 kg / 33 CBM)</option>
                        <option value="40FT" <?= $selectedContainerType === '40FT' ? 'selected' : '' ?>>40FT Dry Container (26,500 kg / 67 CBM)</option>
                        <option value="40HC" <?= $selectedContainerType === '40HC' ? 'selected' : '' ?>>40FT High Cube (26,500 kg / 76 CBM)</option>
                    </select>
                </div>
            </div>

            <h5 class="fw-bold mt-4 mb-3 text-primary border-bottom pb-2"><i class="fas fa-boxes me-2"></i>Multi-product Packing Grid</h5>
            
            <div class="table-responsive mb-3 rounded shadow-sm">
                <table class="table align-middle" id="packing-grid" style="border-collapse: separate; border-spacing: 0;">
                    <thead style="background-color: #0b1c3d; color: #ffffff;">
                        <tr>
                            <th style="width: 30%; padding: 12px 10px;">Product</th>
                            <th style="width: 12%; padding: 12px 10px;" class="text-end">Packages (Bags/Crt)</th>
                            <th style="width: 12%; padding: 12px 10px;" class="text-end">Quantity (Pcs/Weight)</th>
                            <th style="width: 10%; padding: 12px 10px;">Unit</th>
                            <th style="width: 13%; padding: 12px 10px;" class="text-end">Net Weight (KG)</th>
                            <th style="width: 13%; padding: 12px 10px;" class="text-end">Gross Weight (KG)</th>
                            <th style="width: 10%; padding: 12px 10px;" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    
                    <?php 
                    $count = max(1, count($items));
                    for ($i = 0; $i < $count; $i++): 
                        $item = $items[$i] ?? []; 
                    ?>
                    <tbody class="product-line-group border-bottom bg-white">
                        <tr class="product-row" style="height: 58px;">
                            <td class="p-2 border-end-0 border-start-0">
                                <select name="product_id[]" class="form-select form-select-sm select2-grid product-select" required>
                                    <option value="0">Select Product</option>
                                    <?php foreach ($products as $product): ?>
                                        <option value="<?= (int) $product['id'] ?>" <?= (int) ($item['product_id'] ?? 0) === (int) $product['id'] ? 'selected' : '' ?>>
                                            <?= escapeHtml($product['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">Product selection is required</div>
                            </td>
                            <td class="p-2 border-end-0 border-start-0">
                                <input type="number" step="1" name="no_of_bags[]" class="form-control form-control-sm text-end pack-calc bags" value="<?= escapeHtml($item['no_of_bags'] ?? '') ?>" placeholder="0" min="1" required>
                            </td>
                            <td class="p-2 border-end-0 border-start-0">
                                <input type="number" step="0.001" name="total_qty[]" class="form-control form-control-sm text-end pack-calc total-qty" value="<?= escapeHtml($item['total_qty'] ?? '') ?>" placeholder="0.000" min="0.001" required>
                            </td>
                            <td class="p-2 border-end-0 border-start-0">
                                <select name="unit_id[]" class="form-select form-select-sm unit-select">
                                    <option value="0">Unit</option>
                                    <?php foreach ($units as $unit): ?>
                                        <option value="<?= (int) $unit['id'] ?>" <?= (int) ($item['unit_id'] ?? 0) === (int) $unit['id'] ? 'selected' : '' ?>><?= escapeHtml($unit['code']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td class="p-2 border-end-0 border-start-0">
                                <input type="number" step="0.001" name="net_weight_item[]" class="form-control form-control-sm text-end pack-calc net-wt" value="<?= escapeHtml($item['net_weight'] ?? '') ?>" placeholder="0.000" required>
                            </td>
                            <td class="p-2 border-end-0 border-start-0">
                                <input type="number" step="0.001" name="gross_weight_item[]" class="form-control form-control-sm text-end pack-calc gross-wt" value="<?= escapeHtml($item['gross_weight'] ?? '') ?>" placeholder="0.000" required>
                            </td>
                            <td class="p-2 text-center border-end-0 border-start-0 text-nowrap">
                                <button type="button" class="btn btn-outline-info btn-sm btn-toggle-details" style="height: 34px; width: 34px; border-width: 1px;" title="Toggle Specifications"><i class="fa fa-chevron-down"></i></button>
                                <button type="button" class="btn btn-outline-primary btn-sm btn-duplicate-row" style="height: 34px; width: 34px; border-width: 1px;" title="Duplicate Line"><i class="fa fa-copy"></i></button>
                                <button type="button" class="btn btn-outline-danger btn-sm btn-remove-row" style="height: 34px; width: 34px; border-width: 1px;" title="Remove Line"><i class="fa fa-trash"></i></button>
                            </td>
                        </tr>
                        
                        <tr class="details-row bg-light" style="display: none;">
                            <td colspan="7" class="p-3 border-bottom shadow-inner">
                                <div class="details-card card border-0 bg-white p-3 rounded shadow-sm">
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold text-muted mb-1 small">HS Code</label>
                                            <input type="text" name="hsn_code[]" class="form-control form-control-sm hsn-code" value="<?= escapeHtml($item['hsn_code'] ?? '') ?>" placeholder="HS Code">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold text-muted mb-1 small">Grade</label>
                                            <select name="grade_id[]" class="form-select form-select-sm grade-select">
                                                <option value="0">Grade</option>
                                                <?php foreach ($grades as $grade): ?>
                                                    <option value="<?= (int) $grade['id'] ?>" <?= (int) ($item['grade_id'] ?? 0) === (int) $grade['id'] ? 'selected' : '' ?>><?= escapeHtml($grade['name']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold text-muted mb-1 small">Country of Origin</label>
                                            <select name="origin_id[]" class="form-select form-select-sm origin-select">
                                                <option value="0">Origin</option>
                                                <?php foreach ($origins as $origin): ?>
                                                    <option value="<?= (int) $origin['id'] ?>" <?= (int) ($item['origin_id'] ?? 0) === (int) $origin['id'] ? 'selected' : '' ?>><?= escapeHtml($origin['name']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold text-muted mb-1 small">Packing Type</label>
                                            <select name="packing_type_id[]" class="form-select form-select-sm packing-type-select">
                                                <option value="0">Select Type</option>
                                                <?php foreach ($packingTypes as $type): ?>
                                                    <option value="<?= (int) $type['id'] ?>" <?= (int) ($item['packing_type_id'] ?? 0) === (int) $type['id'] ? 'selected' : '' ?>><?= escapeHtml($type['name']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="row g-3 mt-1">
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold text-muted mb-1 small">Packing Size</label>
                                            <input type="text" name="packing_size[]" class="form-control form-control-sm packing-size" value="<?= escapeHtml($item['packing_size'] ?? '') ?>" placeholder="e.g. 25 KG">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold text-muted mb-1 small">Empty Pkg Weight (KG)</label>
                                            <input type="number" step="0.001" name="empty_package_weight[]" class="form-control form-control-sm empty-pkg-wt" value="<?= escapeHtml($item['empty_package_weight'] ?? '0.000') ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold text-muted mb-1 small">Units per Package</label>
                                            <input type="number" step="0.0001" name="units_per_package[]" class="form-control form-control-sm units-per-pkg" value="<?= escapeHtml($item['units_per_package'] ?? '1.0000') ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold text-muted mb-1 small">CBM Per Package</label>
                                            <input type="number" step="0.0001" name="cbm_per_package[]" class="form-control form-control-sm cbm-per-pkg" value="<?= escapeHtml($item['cbm_per_package'] ?? '0.0000') ?>">
                                        </div>
                                    </div>

                                    <div class="row g-3 mt-1">
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold text-muted mb-1 small">Net Weight/Pkg (KG)</label>
                                            <input type="number" step="0.001" name="net_weight_per_package[]" class="form-control form-control-sm net-wt-per-pkg" value="<?= escapeHtml($item['net_weight_per_package'] ?? '0.000') ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold text-muted mb-1 small">Gross Weight Formula</label>
                                            <input type="text" name="gross_weight_formula[]" class="form-control form-control-sm gross-formula" value="<?= escapeHtml($item['gross_weight_formula'] ?? '') ?>" placeholder="e.g. [net_weight]+[empty_package_weight]">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold text-muted mb-1 small">Line Volume (CBM)</label>
                                            <input type="number" step="0.0001" name="cbm[]" class="form-control form-control-sm line-cbm" value="<?= escapeHtml($item['cbm'] ?? '0.0000') ?>" readonly tabindex="-1" style="background-color: #f8f9fa;">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-bold text-muted mb-1 small">Line Pallets Count</label>
                                            <input type="number" step="0.1" name="pallet_count[]" class="form-control form-control-sm line-pallet" value="<?= escapeHtml($item['pallet_count'] ?? '0.0') ?>">
                                        </div>
                                    </div>

                                    <div class="row g-3 mt-1">
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold text-muted mb-1 small">Package Dimensions (cm)</label>
                                            <input type="text" name="dimensions[]" class="form-control form-control-sm package-dimensions" value="<?= escapeHtml($item['dimensions'] ?? '') ?>" placeholder="e.g. 50x30x20">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold text-muted mb-1 small">Storage & Loading Specifications</label>
                                            <input type="text" name="item_loading_specs" class="form-control form-control-sm loading-specs" placeholder="Storage/Palletization guidelines" readonly tabindex="-1" style="background-color: #f8f9fa;">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold text-muted mb-1 small">Line Item Remarks</label>
                                            <input type="text" name="item_remarks[]" class="form-control form-control-sm remarks" value="<?= escapeHtml($item['item_remarks'] ?? '') ?>" placeholder="Line remarks">
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                    <?php endfor; ?>
                </table>
            </div>

            <!-- Action buttons under grid -->
            <div class="d-flex align-items-center justify-content-between mb-4 bg-light p-2 rounded border">
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-primary btn-sm rounded btn-add-row" style="height: 38px;"><i class="fa fa-plus-circle me-1"></i> Add Product Row</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded btn-recalc-all" style="height: 38px;"><i class="fa fa-sync me-1"></i> Recalculate Grid</button>
                </div>
                <div class="text-muted small">Enter either Quantity or Packages count to auto-compute all packing stats.</div>
            </div>

            <!-- Summary Panel -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm bg-light h-100 p-3">
                        <h6 class="fw-bold text-primary mb-3"><i class="fas fa-ship me-2"></i>Container Loading & Stuffing Analytics</h6>
                        <div class="mb-3">
                            <span class="d-block small text-muted mb-1">Weight stuffing capacity:</span>
                            <div class="progress" style="height: 18px; border-radius: 4px;">
                                <div id="weightProgressBar" class="progress-bar bg-success" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0.00%</div>
                            </div>
                            <span class="small text-muted d-block mt-1" id="weightProgressLabel">0.000 / 0 kg utilization</span>
                        </div>
                        <div class="mb-3">
                            <span class="d-block small text-muted mb-1">Volume/CBM stuffing capacity:</span>
                            <div class="progress" style="height: 18px; border-radius: 4px;">
                                <div id="volumeProgressBar" class="progress-bar bg-info" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0.00%</div>
                            </div>
                            <span class="small text-muted d-block mt-1" id="volumeProgressLabel">0.0000 / 0 CBM utilization</span>
                        </div>
                        <div>
                            <span class="small text-muted d-block" id="containerRecommendationText">Container Allocation Recommendation: <strong>N/A</strong></span>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm bg-light p-3">
                        <h6 class="fw-bold text-primary mb-3"><i class="fas fa-chart-pie me-2"></i>Shipment Packing Totals</h6>
                        <div class="row g-2">
                            <div class="col-6">
                                <span class="d-block small text-muted">Total Packages</span>
                                <input id="totalPackages" name="total_packages" class="form-control form-control-sm fw-bold border-0 bg-white" value="<?= escapeHtml($meta['total_packages'] ?? '0') ?>" readonly tabindex="-1">
                            </div>
                            <div class="col-6">
                                <span class="d-block small text-muted">Total Volume (CBM)</span>
                                <input id="totalCbm" class="form-control form-control-sm fw-bold border-0 bg-white" value="0.0000" readonly tabindex="-1">
                            </div>
                            <div class="col-6">
                                <span class="d-block small text-muted">Total Net Weight (KG)</span>
                                <input id="totalNet" name="net_weight" class="form-control form-control-sm fw-bold border-0 bg-white" value="<?= escapeHtml($meta['net_weight'] ?? '0') ?>" readonly tabindex="-1">
                            </div>
                            <div class="col-6">
                                <span class="d-block small text-muted">Total Gross Weight (KG)</span>
                                <input id="totalGross" name="gross_weight" class="form-control form-control-sm fw-bold border-0 bg-white" value="<?= escapeHtml($meta['gross_weight'] ?? '0') ?>" readonly tabindex="-1">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Remarks</label>
                <textarea name="remarks" class="form-control" rows="3" placeholder="Enter special remarks or shipment guidelines"><?= escapeHtml($packingList['remarks'] ?? '') ?></textarea>
            </div>
            
            <input type="hidden" name="valid_until" value="">
            <input type="hidden" name="freight" value="0">
            <input type="hidden" name="insurance" value="0">
            <input type="hidden" name="other_charges" value="0">
            
            <button class="btn btn-primary px-4 py-2" id="btn-save-packing-list"><i class="fa fa-save me-1"></i> Save Packing List</button>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    // Keep reference to template tbody row for adding rows
    const rowTemplate = $('#packing-grid tbody:first').clone();
    
    // Clear template inputs for clean addition
    rowTemplate.find('input').val('');
    rowTemplate.find('.empty-pkg-wt').val('0.000');
    rowTemplate.find('.units-per-pkg').val('1.0000');
    rowTemplate.find('.cbm-per-pkg').val('0.0000');
    rowTemplate.find('.net-wt-per-pkg').val('0.000');
    rowTemplate.find('.line-cbm').val('0.0000');
    rowTemplate.find('.line-pallet').val('0.0');
    rowTemplate.find('select').val('0');
    rowTemplate.find('.details-row').hide();
    rowTemplate.find('.btn-toggle-details i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
    
    // Initialize standard select2 elements
    function initSelect2OnRow(row) {
        row.find('.select2-grid').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#packing-grid')
        });
    }

    $('.select2-init').select2({
        theme: 'bootstrap-5'
    });
    
    $('.product-select').each(function() {
        $(this).select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#packing-grid')
        });
    });

    // Toggle details delegation
    $(document).on('click', '.btn-toggle-details', function() {
        const tbody = $(this).closest('tbody');
        const details = tbody.find('.details-row');
        const icon = $(this).find('i');
        
        if (details.is(':visible')) {
            details.hide();
            icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
        } else {
            details.show();
            icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
        }
    });

    // Add product row
    $('.btn-add-row').on('click', function() {
        const newGroup = rowTemplate.clone();
        // Reset select2 instance
        newGroup.find('.select2-container').remove();
        newGroup.find('.product-select').removeClass('select2-hidden-accessible');
        
        $('#packing-grid').append(newGroup);
        initSelect2OnRow(newGroup);
        recalcAll();
    });

    // Remove row
    $(document).on('click', '.btn-remove-row', function() {
        const groups = $('#packing-grid tbody');
        if (groups.length > 1) {
            $(this).closest('tbody').remove();
            recalcAll();
        } else {
            alert('At least one product line item is required.');
        }
    });

    // Duplicate row
    $(document).on('click', '.btn-duplicate-row', function() {
        const sourceTbody = $(this).closest('tbody');
        const newGroup = sourceTbody.clone();
        
        // Handle select values manually as clone doesn't copy current selected index state
        newGroup.find('select').each(function(index) {
            const sourceSelect = sourceTbody.find('select').eq(index);
            $(this).val(sourceSelect.val());
        });
        
        // Reset select2 wrapper
        newGroup.find('.select2-container').remove();
        newGroup.find('.product-select').removeClass('select2-hidden-accessible');
        
        sourceTbody.after(newGroup);
        initSelect2OnRow(newGroup);
        recalcAll();
    });

    // Product Auto Fetch details
    $(document).on('change', '.product-select', function() {
        const productId = $(this).val();
        if (!productId || productId === '0') return;
        
        const tbody = $(this).closest('tbody');
        
        $.ajax({
            url: '/products/' + productId + '/details',
            method: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.success && res.product) {
                    const p = res.product;
                    
                    // Basic fields
                    if (p.hsn_code) tbody.find('.hsn-code').val(p.hsn_code);
                    if (p.packing_type_id) tbody.find('.packing-type-select').val(p.packing_type_id);
                    if (p.unit_id) tbody.find('.unit-select').val(p.unit_id);
                    if (p.packing_size) tbody.find('.packing-size').val(p.packing_size);
                    
                    // Packaging specs
                    tbody.find('.units-per-pkg').val(p.units_per_package || '1.0000');
                    tbody.find('.empty-pkg-wt').val(p.empty_package_weight || '0.000');
                    tbody.find('.net-wt-per-pkg').val(p.net_weight || '0.000');
                    tbody.find('.gross-formula').val(p.gross_weight_formula || '');
                    tbody.find('.cbm-per-pkg').val(p.volume_per_package_cbm || '0.0000');
                    
                    // Dimensions
                    let dims = '';
                    if (p.package_length) {
                        dims = p.package_length + 'x' + p.package_width + 'x' + p.package_height + ' cm';
                    }
                    tbody.find('.package-dimensions').val(dims);
                    
                    // Loading specs display
                    let loadingStr = '';
                    if (p.package_material) loadingStr += p.package_material + ' ';
                    if (p.stack_limit) loadingStr += '(Stack max: ' + p.stack_limit + ') ';
                    if (p.pallet_configuration) loadingStr += 'Pallet: [' + p.pallet_configuration + ']';
                    tbody.find('.loading-specs').val(loadingStr.trim());
                    
                    // Trigger line calculations
                    runLineCalculations(tbody, 'bags');
                }
            }
        });
    });

    // Run Line calculations on keyup or focus change
    function runLineCalculations(tbody, triggerField) {
        const unitsPerPkg = parseFloat(tbody.find('.units-per-pkg').val() || 1.0) || 1.0;
        const emptyPkgWt = parseFloat(tbody.find('.empty-pkg-wt').val() || 0.0) || 0.0;
        const netWtPerPkg = parseFloat(tbody.find('.net-wt-per-pkg').val() || 0.0) || 0.0;
        const cbmPerPkg = parseFloat(tbody.find('.cbm-per-pkg').val() || 0.0) || 0.0;
        const formulaStr = tbody.find('.gross-formula').val() || '';
        
        let bags = parseFloat(tbody.find('.bags').val() || 0.0);
        let totalQty = parseFloat(tbody.find('.total-qty').val() || 0.0);
        
        if (triggerField === 'bags') {
            totalQty = bags * unitsPerPkg;
            tbody.find('.total-qty').val(totalQty.toFixed(3));
        } else if (triggerField === 'qty') {
            bags = Math.ceil(totalQty / unitsPerPkg);
            tbody.find('.bags').val(bags.toFixed(0));
        }
        
        // Calculate Net Weight
        let netWeight = bags * netWtPerPkg;
        if (netWeight <= 0.0) {
            netWeight = totalQty;
        }
        tbody.find('.net-wt').val(netWeight.toFixed(3));
        
        // Calculate Gross Weight
        let grossWeight = netWeight + (bags * emptyPkgWt);
        if (formulaStr) {
            try {
                // Safely evaluate simple formula e.g. [net_weight] + [empty_package_weight] * [packages]
                let expr = formulaStr
                    .replace(/\[net_weight\]/g, netWeight.toString())
                    .replace(/\[empty_package_weight\]/g, emptyPkgWt.toString())
                    .replace(/\[packages\]/g, bags.toString())
                    .replace(/\[qty\]/g, totalQty.toString());
                
                // Sanitization to prevent arbitrary JS execution
                if (/^[0-9.+\-*\/()\s]+$/.test(expr)) {
                    grossWeight = eval(expr);
                }
            } catch(e) {
                console.log("[Formula Error]", e);
            }
        }
        tbody.find('.gross-wt').val(grossWeight.toFixed(3));
        
        // Calculate CBM
        let cbm = bags * cbmPerPkg;
        tbody.find('.line-cbm').val(cbm.toFixed(4));
        
        // Estimate Pallet count
        let palletCount = 0;
        const palletConfig = tbody.find('.loading-specs').val() || '';
        // If pallet configuration mentions a number per pallet, we can estimate
        let match = palletConfig.match(/(\d+)\s*(?:bags|packages|units)\s*(?:per|limit|layer)/i);
        if (match) {
            const limit = parseInt(match[1]);
            if (limit > 0) palletCount = bags / limit;
        }
        tbody.find('.line-pallet').val(palletCount.toFixed(1));
        
        recalcAll();
    }

    // Grid input listeners
    $(document).on('input', '.bags', function() {
        runLineCalculations($(this).closest('tbody'), 'bags');
    });
    
    $(document).on('input', '.total-qty', function() {
        runLineCalculations($(this).closest('tbody'), 'qty');
    });
    
    $(document).on('input', '.net-wt, .gross-wt, .units-per-pkg, .empty-pkg-wt, .cbm-per-pkg, .gross-formula', function() {
        runLineCalculations($(this).closest('tbody'), 'custom');
    });

    $('#selectedContainerType').on('change', function() {
        recalcAll();
    });

    $('.btn-recalc-all').on('click', function() {
        recalcAll();
    });

    // Grand calculations and container stuffing recommendations
    function recalcAll() {
        let totalBags = 0;
        let totalQty = 0;
        let totalNet = 0;
        let totalGross = 0;
        let totalCbm = 0;
        let totalPallets = 0;
        
        $('#packing-grid tbody').each(function() {
            totalBags += parseFloat($(this).find('.bags').val() || 0.0);
            totalQty += parseFloat($(this).find('.total-qty').val() || 0.0);
            totalNet += parseFloat($(this).find('.net-wt').val() || 0.0);
            totalGross += parseFloat($(this).find('.gross-wt').val() || 0.0);
            totalCbm += parseFloat($(this).find('.line-cbm').val() || 0.0);
            totalPallets += parseFloat($(this).find('.line-pallet').val() || 0.0);
        });
        
        // Update inputs
        $('#totalPackages').val(totalBags.toFixed(0));
        $('#totalNet').val(totalNet.toFixed(3));
        $('#totalGross').val(totalGross.toFixed(3));
        $('#totalCbm').val(totalCbm.toFixed(4));
        
        // Container limits
        const containerType = $('#selectedContainerType').val();
        let maxWeight = 24000.0;
        let maxVolume = 33.0;
        let containerName = "20FT Dry Container";
        
        if (containerType === '40FT') {
            maxWeight = 26500.0;
            maxVolume = 67.0;
            containerName = "40FT Dry Container";
        } else if (containerType === '40HC') {
            maxWeight = 26500.0;
            maxVolume = 76.0;
            containerName = "40FT High Cube Container";
        }
        
        // Calculations
        const weightUtil = Math.min(100, (totalGross / maxWeight) * 100);
        const volumeUtil = Math.min(100, (totalCbm / maxVolume) * 100);
        
        // Render Weight Utilization Bar
        const weightBar = $('#weightProgressBar');
        weightBar.css('width', weightUtil.toFixed(2) + '%');
        weightBar.attr('aria-valuenow', weightUtil.toFixed(2));
        weightBar.text(weightUtil.toFixed(2) + '%');
        weightBar.removeClass('bg-success bg-warning bg-danger');
        if (weightUtil < 70) weightBar.addClass('bg-success');
        else if (weightUtil < 95) weightBar.addClass('bg-warning');
        else weightBar.addClass('bg-danger');
        
        $('#weightProgressLabel').text(totalGross.toFixed(3) + ' / ' + maxWeight + ' kg (' + weightUtil.toFixed(2) + '% utilization)');
        
        // Render CBM/Volume Utilization Bar
        const volumeBar = $('#volumeProgressBar');
        volumeBar.css('width', volumeUtil.toFixed(2) + '%');
        volumeBar.attr('aria-valuenow', volumeUtil.toFixed(2));
        volumeBar.text(volumeUtil.toFixed(2) + '%');
        volumeBar.removeClass('bg-info bg-warning bg-danger');
        if (volumeUtil < 70) volumeBar.addClass('bg-info');
        else if (volumeUtil < 95) volumeBar.addClass('bg-warning');
        else volumeBar.addClass('bg-danger');
        
        $('#volumeProgressLabel').text(totalCbm.toFixed(4) + ' / ' + maxVolume + ' CBM (' + volumeUtil.toFixed(2) + '% utilization)');
        
        // Recommendations Count
        const countByWeight = Math.ceil(totalGross / maxWeight) || 0;
        const countByVolume = Math.ceil(totalCbm / maxVolume) || 0;
        const suggestedCount = Math.max(countByWeight, countByVolume) || 1;
        
        $('#containerRecommendationText').html(
            'Container Allocation Recommendation: <strong>' + suggestedCount + ' x ' + containerName + '</strong> ' +
            '(Estimated ' + totalPallets.toFixed(1) + ' pallets required)'
        );
    }
    
    // Initial run
    recalcAll();
});
</script>
