<?php
$h = fn($s) => htmlspecialchars((string)($s ?? ''));
$e = fn($k, $arr) => isset($arr[$k]) ? $h($arr[$k]) : '';
$c = fn($k, $v, $arr) => (isset($arr[$k]) && (string)$arr[$k] === (string)$v) ? 'checked' : '';
$s = fn($k, $v, $arr) => (isset($arr[$k]) && (string)$arr[$k] === (string)$v) ? 'selected' : '';
$err = fn($k, $errors) => isset($errors[$k]) ? '<div class="invalid-feedback d-block">' . $h($errors[$k]) . '</div>' : '';
$cls = fn($k, $errors) => isset($errors[$k]) ? ' is-invalid' : '';

$p = $product ?? [];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 text-dark fw-bold"><i class="fas fa-box-open me-2 text-primary"></i><?= $title ?></h4>
    <a href="/products" class="btn btn-outline-secondary shadow-sm"><i class="fas fa-arrow-left me-1"></i> Back to List</a>
</div>

<form method="POST" action="<?= $action ?>" class="needs-validation" novalidate id="productForm">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

    <div class="row g-4">
    <div class="col-lg-12">

        <!-- 1. Basic Information -->
        <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
            <h5 class="fw-bold text-primary mb-0"><i class="fas fa-info-circle me-2 me-2"></i>Basic Information</h5>
        </div>
        <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Product Code <span class="text-danger">*</span></label>
                            <input type="text" name="product_code" class="form-control<?= $cls('product_code', $errors) ?>" value="<?= $e('product_code', $p) ?>" required readonly>
                            <?= $err('product_code', $errors) ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Product Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control<?= $cls('name', $errors) ?>" value="<?= $e('name', $p) ?>" required>
                            <?= $err('name', $errors) ?>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">HS Code <span class="text-danger">*</span></label>
                            <input type="text" name="hsn_code" class="form-control<?= $cls('hsn_code', $errors) ?>" value="<?= $e('hsn_code', $p) ?>" required>
                            <?= $err('hsn_code', $errors) ?>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select<?= $cls('category_id', $errors) ?>" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= $s('category_id', $cat['id'], $p) ?>><?= $h($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?= $err('category_id', $errors) ?>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Primary Unit <span class="text-danger">*</span></label>
                            <select name="unit_id" class="form-select<?= $cls('unit_id', $errors) ?>" required>
                                <option value="">Select Unit</option>
                                <?php foreach ($units as $u): ?>
                                    <option value="<?= $u['id'] ?>" <?= $s('unit_id', $u['id'], $p) ?>><?= $h($u['name']) ?> (<?= $h($u['code']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                            <?= $err('unit_id', $errors) ?>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">GST %</label>
                            <input type="number" step="0.01" name="gst_percent" class="form-control" value="<?= $e('gst_percent', $p) ?>">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" class="form-control" rows="3"><?= $e('description', $p) ?></textarea>
                        </div>
                    </div>
                </div>
        </div>

        <!-- 2. Export Information -->
        <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
            <h5 class="fw-bold text-primary mb-0"><i class="fas fa-globe-americas me-2 me-2"></i>Export Information</h5>
        </div>
        <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Country of Origin <span class="text-danger">*</span></label>
                            <select name="country_id" id="country_id" class="form-select select2<?= $cls('country_id', $errors) ?>" required>
                                <option value="" disabled selected>Select Country...</option>
                                <?php foreach ($countries as $country_row): ?>
                                    <option value="<?= $country_row['id'] ?>" <?= ($p['country_id'] ?? '') == $country_row['id'] ? 'selected' : '' ?>><?= htmlspecialchars($country_row['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?= $err('country_id', $errors) ?>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">State/Region</label>
                            <select name="state_id" id="state_id" class="form-select select2">
                                <option value="" disabled selected>Select State...</option>
                                <?php foreach ($states ?? [] as $st): ?>
                                    <option value="<?= $st['id'] ?>" data-country="<?= $st['country_id'] ?>" <?= ($p['state_id'] ?? '') == $st['id'] ? 'selected' : '' ?>><?= htmlspecialchars($st['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold w-100 d-flex justify-content-between align-items-center"><span>City</span><a href="#" class="text-primary small text-decoration-none openCityModalBtn"><i class="fas fa-plus"></i> Add</a></label>
                            <select name="city_id" class="form-select select2">
                                <option value="" disabled selected>Select City...</option>
                                <?php foreach ($cities ?? [] as $city): ?>
                                    <option value="<?= $city['id'] ?>" <?= ($p['city_id'] ?? '') == $city['id'] ? 'selected' : '' ?>><?= htmlspecialchars($city['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Legacy field for compatibility -->
                        <input type="hidden" name="country_of_origin" id="country_of_origin" value="<?= $e('country_of_origin', $p) ?>">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Scientific Name</label>
                            <input type="text" name="scientific_name" class="form-control fst-italic" value="<?= $e('scientific_name', $p) ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Crop Year</label>
                            <input type="text" name="crop_year" class="form-control" value="<?= $e('crop_year', $p) ?>" placeholder="e.g. 2025">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Harvest Season</label>
                            <input type="text" name="harvest_season" class="form-control" value="<?= $e('harvest_season', $p) ?>">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Shelf Life</label>
                            <input type="text" name="shelf_life" class="form-control" value="<?= $e('shelf_life', $p) ?>" placeholder="e.g. 24 Months">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Storage Conditions</label>
                            <input type="text" name="storage_conditions" class="form-control" value="<?= $e('storage_conditions', $p) ?>" placeholder="e.g. Cool & Dry Place">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Temperature Req.</label>
                            <input type="text" name="temperature_req" class="form-control" value="<?= $e('temperature_req', $p) ?>" placeholder="e.g. 15°C to 20°C">
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Moisture (%)</label>
                            <input type="number" step="0.01" name="moisture_percent" class="form-control" value="<?= $e('moisture_percent', $p) ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Purity (%)</label>
                            <input type="number" step="0.01" name="purity_percent" class="form-control" value="<?= $e('purity_percent', $p) ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Admixture (%)</label>
                            <input type="number" step="0.01" name="admixture_percent" class="form-control" value="<?= $e('admixture_percent', $p) ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Broken (%)</label>
                            <input type="number" step="0.01" name="broken_percent" class="form-control" value="<?= $e('broken_percent', $p) ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Color</label>
                            <input type="text" name="color" class="form-control" value="<?= $e('color', $p) ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Smell</label>
                            <input type="text" name="smell" class="form-control" value="<?= $e('smell', $p) ?>">
                        </div>
                    </div>
                </div>
        </div>

        <!-- 3. Commercial Information -->
        <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
            <h5 class="fw-bold text-primary mb-0"><i class="fas fa-dollar-sign me-2 me-2"></i>Commercial Information</h5>
        </div>
        <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Default Currency</label>
                            <select name="default_currency" class="form-select">
                                <option value="">Select Currency</option>
                                <?php foreach ($currencies as $curr): ?>
                                    <option value="<?= $curr['code'] ?>" <?= $s('default_currency', $curr['code'], $p) ?>><?= $h($curr['code']) ?> - <?= $h($curr['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Purchase Price</label>
                            <input type="number" step="0.01" name="purchase_price" class="form-control<?= $cls('purchase_price', $errors) ?>" value="<?= $e('purchase_price', $p) ?>">
                            <?= $err('purchase_price', $errors) ?>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Selling Price</label>
                            <input type="number" step="0.01" name="selling_price" class="form-control<?= $cls('selling_price', $errors) ?>" value="<?= $e('selling_price', $p) ?>">
                            <?= $err('selling_price', $errors) ?>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Lead Time (Days)</label>
                            <input type="number" name="lead_time_days" class="form-control" value="<?= $e('lead_time_days', $p) ?>">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">MOQ (Min Order Qty)</label>
                            <input type="number" step="0.01" name="moq" class="form-control" value="<?= $e('moq', $p) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Max Order Qty</label>
                            <input type="number" step="0.01" name="max_oq" class="form-control" value="<?= $e('max_oq', $p) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Preferred Payment Method</label>
                            <input type="text" name="preferred_payment_method" class="form-control" value="<?= $e('preferred_payment_method', $p) ?>" placeholder="e.g. 100% LC at Sight">
                        </div>
                    </div>
                </div>
        </div>

        <!-- 4. Packing Information -->
        <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
            <h5 class="fw-bold text-primary mb-0"><i class="fas fa-box me-2 me-2"></i>Packing Information</h5>
        </div>
        <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Packing Type</label>
                            <select name="packing_type_id" class="form-select">
                                <option value="">Select Packing Type</option>
                                <?php foreach ($packingTypes as $pt): ?>
                                    <option value="<?= $pt['id'] ?>" <?= $s('packing_type_id', $pt['id'], $p) ?>><?= $h($pt['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Packing Size</label>
                            <input type="text" name="packing_size" class="form-control" value="<?= $e('packing_size', $p) ?>" placeholder="e.g. 25 KG">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Net Weight (KG)</label>
                            <input type="number" step="0.001" name="net_weight" class="form-control" value="<?= $e('net_weight', $p) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Gross Weight (KG)</label>
                            <input type="number" step="0.001" name="gross_weight" class="form-control" value="<?= $e('gross_weight', $p) ?>">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Container Type</label>
                            <input type="text" name="container_type" class="form-control" value="<?= $e('container_type', $p) ?>" placeholder="e.g. 20ft FCL">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Bags per Container</label>
                            <input type="number" name="bags_per_container" class="form-control" value="<?= $e('bags_per_container', $p) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Pallet Type</label>
                            <input type="text" name="pallet_type" class="form-control" value="<?= $e('pallet_type', $p) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Shipping Marks</label>
                            <input type="text" name="shipping_marks" class="form-control" value="<?= $e('shipping_marks', $p) ?>">
                        </div>

                        <!-- Enterprise Packing Intelligence specifications -->
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Units Per Package</label>
                            <input type="number" step="0.0001" name="units_per_package" class="form-control" value="<?= $e('units_per_package', $p) !== '' ? $e('units_per_package', $p) : '1.0000' ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Empty Package Weight (KG)</label>
                            <input type="number" step="0.001" name="empty_package_weight" class="form-control" value="<?= $e('empty_package_weight', $p) !== '' ? $e('empty_package_weight', $p) : '0.000' ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Gross Weight Formula</label>
                            <input type="text" name="gross_weight_formula" class="form-control" value="<?= $e('gross_weight_formula', $p) ?>" placeholder="e.g. [net_weight]+[empty_package_weight]">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Package Length (cm)</label>
                            <input type="number" step="0.1" name="package_length" class="form-control" value="<?= $e('package_length', $p) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Package Width (cm)</label>
                            <input type="number" step="0.1" name="package_width" class="form-control" value="<?= $e('package_width', $p) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Package Height (cm)</label>
                            <input type="number" step="0.1" name="package_height" class="form-control" value="<?= $e('package_height', $p) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Package Material</label>
                            <input type="text" name="package_material" class="form-control" value="<?= $e('package_material', $p) ?>" placeholder="e.g. HDPE Bag">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Stack Limit</label>
                            <input type="number" name="stack_limit" class="form-control" value="<?= $e('stack_limit', $p) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Pallet Configuration</label>
                            <input type="text" name="pallet_configuration" class="form-control" value="<?= $e('pallet_configuration', $p) ?>" placeholder="e.g. 10 layers of 5 bags">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Storage Type</label>
                            <input type="text" name="storage_type" class="form-control" value="<?= $e('storage_type', $p) ?>" placeholder="e.g. Ambient / Dry">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Loading Method</label>
                            <input type="text" name="loading_method" class="form-control" value="<?= $e('loading_method', $p) ?>" placeholder="e.g. Loose stuffing">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Container Compatibility</label>
                            <input type="text" name="container_compatibility" class="form-control" value="<?= $e('container_compatibility', $p) ?>" placeholder="e.g. 20FT, 40FT, 40HC">
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Default Shipment Type</label>
                            <select name="default_shipment_type" class="form-select">
                                <option value="">Select Type</option>
                                <option value="Sea" <?= $s('default_shipment_type', 'Sea', $p) ?>>Sea Freight</option>
                                <option value="Air" <?= $s('default_shipment_type', 'Air', $p) ?>>Air Freight</option>
                                <option value="Road" <?= $s('default_shipment_type', 'Road', $p) ?>>Road Transport</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Preferred Loading Port</label>
                            <input type="text" name="preferred_loading_port" class="form-control" value="<?= $e('preferred_loading_port', $p) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Preferred Incoterm</label>
                            <select name="preferred_incoterm" class="form-select">
                                <option value="">Select Incoterm</option>
                                <?php foreach ($incoterms as $inco): ?>
                                    <option value="<?= $inco['code'] ?>" <?= $s('preferred_incoterm', $inco['code'], $p) ?>><?= $h($inco['code']) ?> - <?= $h($inco['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
        </div>

        <!-- 5. Quality Parameters -->
        <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
            <h5 class="fw-bold text-primary mb-0"><i class="fas fa-check-double me-2 me-2"></i>Quality Parameters & Certificates</h5>
        </div>
        <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3 border-bottom pb-2">Processing Standards</h6>
                            <div class="d-flex flex-wrap gap-4">
                                <div class="form-check">
                                    <input type="checkbox" name="is_machine_clean" id="q_mc" class="form-check-input" value="1" <?= $c('is_machine_clean', 1, $p) ?>>
                                    <label class="form-check-label fw-semibold" for="q_mc">Machine Clean</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" name="is_sortex" id="q_sx" class="form-check-input" value="1" <?= $c('is_sortex', 1, $p) ?>>
                                    <label class="form-check-label fw-semibold" for="q_sx">Sortex Clean</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" name="is_hand_picked" id="q_hp" class="form-check-input" value="1" <?= $c('is_hand_picked', 1, $p) ?>>
                                    <label class="form-check-label fw-semibold" for="q_hp">Hand Picked</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" name="is_steam_sterilized" id="q_ss" class="form-check-input" value="1" <?= $c('is_steam_sterilized', 1, $p) ?>>
                                    <label class="form-check-label fw-semibold" for="q_ss">Steam Sterilized</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" name="is_organic" id="q_org" class="form-check-input" value="1" <?= $c('is_organic', 1, $p) ?>>
                                    <label class="form-check-label fw-semibold text-success" for="q_org"><i class="fas fa-leaf"></i> Organic</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3 border-bottom pb-2">Required Certificates</h6>
                            <div class="d-flex flex-wrap gap-4">
                                <div class="form-check">
                                    <input type="checkbox" name="cert_eu_standard" id="c_eu" class="form-check-input" value="1" <?= $c('cert_eu_standard', 1, $p) ?>>
                                    <label class="form-check-label fw-semibold" for="c_eu">EU Standard</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" name="cert_us_fda" id="c_fda" class="form-check-input" value="1" <?= $c('cert_us_fda', 1, $p) ?>>
                                    <label class="form-check-label fw-semibold" for="c_fda">US FDA</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" name="cert_iso" id="c_iso" class="form-check-input" value="1" <?= $c('cert_iso', 1, $p) ?>>
                                    <label class="form-check-label fw-semibold" for="c_iso">ISO</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" name="cert_haccp" id="c_haccp" class="form-check-input" value="1" <?= $c('cert_haccp', 1, $p) ?>>
                                    <label class="form-check-label fw-semibold" for="c_haccp">HACCP</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" name="cert_fssai" id="c_fssai" class="form-check-input" value="1" <?= $c('cert_fssai', 1, $p) ?>>
                                    <label class="form-check-label fw-semibold" for="c_fssai">FSSAI</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" name="cert_apeda" id="c_apeda" class="form-check-input" value="1" <?= $c('cert_apeda', 1, $p) ?>>
                                    <label class="form-check-label fw-semibold" for="c_apeda">APEDA</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" name="cert_asta" id="c_asta" class="form-check-input" value="1" <?= $c('cert_asta', 1, $p) ?>>
                                    <label class="form-check-label fw-semibold" for="c_asta">ASTA</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        </div>

        <!-- 6. Inventory Information -->
        <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
            <h5 class="fw-bold text-primary mb-0"><i class="fas fa-boxes me-2 me-2"></i>Inventory Information</h5>
        </div>
        <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Opening Stock</label>
                            <input type="number" step="0.01" name="opening_stock" class="form-control<?= $cls('opening_stock', $errors) ?>" value="<?= $e('opening_stock', $p) ?>">
                            <?= $err('opening_stock', $errors) ?>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Reorder Level</label>
                            <input type="number" step="0.01" name="reorder_level" class="form-control" value="<?= $e('reorder_level', $p) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Safety Stock</label>
                            <input type="number" step="0.01" name="safety_stock" class="form-control" value="<?= $e('safety_stock', $p) ?>">
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Warehouse Location</label>
                            <input type="text" name="warehouse_location" class="form-control" value="<?= $e('warehouse_location', $p) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Rack Location</label>
                            <input type="text" name="rack_location" class="form-control" value="<?= $e('rack_location', $p) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Bin Location</label>
                            <input type="text" name="bin_location" class="form-control" value="<?= $e('bin_location', $p) ?>">
                        </div>
                    </div>
                </div>
        </div>

        <!-- 7. CRM & Status -->
        <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
            <h5 class="fw-bold text-primary mb-0"><i class="fas fa-chart-line me-2 me-2"></i>CRM & Settings</h5>
        </div>
        <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <div class="d-flex flex-wrap gap-4 pt-3">
                                <div class="form-check form-switch fs-5">
                                    <input class="form-check-input" type="checkbox" name="status" id="status" value="1" <?= (!isset($p['status']) || $p['status'] == 1) ? 'checked' : '' ?>>
                                    <label class="form-check-label fw-bold text-success ms-2" for="status">Active Product</label>
                                </div>
                                <div class="form-check form-switch fs-5">
                                    <input class="form-check-input" type="checkbox" name="is_export" id="is_export" value="1" <?= $c('is_export', 1, $p) ?>>
                                    <label class="form-check-label fw-bold text-primary ms-2" for="is_export">Export Product</label>
                                </div>
                                <div class="form-check form-switch fs-5">
                                    <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured" value="1" <?= $c('is_featured', 1, $p) ?>>
                                    <label class="form-check-label fw-bold text-warning ms-2" for="is_featured">Featured Product</label>
                                </div>
                                <div class="form-check form-switch fs-5">
                                    <input class="form-check-input" type="checkbox" name="is_domestic" id="is_domestic" value="1" <?= $c('is_domestic', 1, $p) ?>>
                                    <label class="form-check-label fw-bold text-info ms-2" for="is_domestic">Domestic Product</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Internal Remarks / Notes</label>
                            <textarea name="remarks" class="form-control" rows="3"><?= $e('remarks', $p) ?></textarea>
                        </div>
                    </div>
                </div>
        </div>
</div>

    <!-- Sticky Footer Actions -->
    <div class="card border-0 shadow-sm sticky-bottom z-3 mb-4" style="bottom: 1rem;">
        <div class="card-body bg-light rounded d-flex justify-content-between align-items-center">
            <div class="text-muted small">
                <i class="fas fa-info-circle me-1"></i> Ensure all mandatory fields (<span class="text-danger">*</span>) are filled correctly.
            </div>
            <div class="d-flex gap-2">
                <a href="/products" class="btn btn-outline-secondary shadow-sm px-4 fw-semibold">Cancel</a>
                <button type="submit" class="btn btn-primary shadow-sm px-4 fw-bold"><i class="fas fa-save me-2"></i> Save Product</button>
            </div>
        </div>
    </div>
</form>

<style>
.select2-results__options {
    max-height: 250px !important;
    overflow-y: auto !important;
}
.select2-results__option--highlighted[aria-selected],
.select2-results__option:hover {
    background-color: #0d6efd !important;
    color: white !important;
}
</style>

<div id="noCitiesAlert" class="mt-2 text-danger fw-bold" style="display: none;">
    No cities found. 
    <button type="button" class="btn btn-sm btn-outline-primary ms-2 openCityModalBtn">
        <i class="fas fa-plus"></i> Add New City
    </button>
</div>

<!-- Modal for Adding New City -->
<div class="modal fade" id="addCityModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-city me-2 text-primary"></i>Add New City</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
            <label class="form-label fw-semibold">Country</label>
            <input type="text" id="modalCountryName" class="form-control bg-light" readonly>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">State/Region</label>
            <input type="text" id="modalStateName" class="form-control bg-light" readonly>
            <input type="hidden" id="modalStateId">
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">City Name <span class="text-danger">*</span></label>
            <input type="text" id="modalCityName" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="saveCityBtn">
            <i class="fas fa-save me-1"></i> Save
        </button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const countrySelect = document.getElementById('country_id');
    const stateSelect = document.getElementById('state_id');
    const citySelect = document.querySelector('select[name="city_id"]');
    const noCitiesAlert = document.getElementById('noCitiesAlert');
    const countryOfOriginInput = document.getElementById('country_of_origin');
    const citySelectContainer = citySelect.parentElement;
    
    // Setup alert inside container
    citySelectContainer.appendChild(noCitiesAlert);

    // Initial disable states if empty
    if (!countrySelect.value) {
        stateSelect.disabled = true;
        citySelect.disabled = true;
    } else if (!stateSelect.value) {
        citySelect.disabled = true;
    }

    if (countrySelect && stateSelect && citySelect) {
        // Cache all states internally
        const allStates = Array.from(stateSelect.options).filter(opt => opt.value !== '').map(opt => ({
            value: opt.value,
            text: opt.text,
            countryId: opt.getAttribute('data-country'),
            selected: opt.selected
        }));
        
        function updateSelect2(el) {
            if (typeof jQuery !== 'undefined' && jQuery(el).hasClass('select2-hidden-accessible')) {
                jQuery(el).select2('destroy');
            }
            if (typeof jQuery !== 'undefined') {
                jQuery(el).select2({
                    theme: 'bootstrap-5',
                    width: '100%'
                });
            }
        }

        function bindStateSelect2Events() {
            if (typeof jQuery !== 'undefined') {
                jQuery(stateSelect).off('select2:select').on('select2:select', function() {
                    citySelect.value = '';
                    loadCities(stateSelect.value);
                });
            }
        }

        function filterStates() {
            const selectedCountry = countrySelect.value;
            const currentState = stateSelect.value;
            
            // Sync country_of_origin hidden input
            if (countrySelect.selectedIndex >= 0 && countrySelect.options[countrySelect.selectedIndex].value !== "") {
                countryOfOriginInput.value = countrySelect.options[countrySelect.selectedIndex].text;
            } else {
                countryOfOriginInput.value = '';
            }

            // Clear current options except first
            while (stateSelect.options.length > 1) {
                stateSelect.remove(1);
            }
            
            if (!selectedCountry) {
                stateSelect.disabled = true;
                citySelect.disabled = true;
                citySelect.value = '';
                updateSelect2(stateSelect);
                bindStateSelect2Events();
                updateSelect2(citySelect);
                noCitiesAlert.style.display = 'none';
                return;
            }

            stateSelect.disabled = false;
            
            // Add matching states
            allStates.forEach(state => {
                if (state.countryId === selectedCountry) {
                    const option = new Option(state.text, state.value, false, state.value === currentState);
                    option.setAttribute('data-country', state.countryId);
                    stateSelect.add(option);
                }
            });
            updateSelect2(stateSelect);
            bindStateSelect2Events();
            
            // If country changed manually and no state selected, disable city
            if (!stateSelect.value) {
                citySelect.disabled = true;
                citySelect.value = '';
                updateSelect2(citySelect);
                noCitiesAlert.style.display = 'none';
            } else {
                // Fetch cities for currently selected state
                loadCities(stateSelect.value, citySelect.value);
            }
        }

        function loadCities(stateId, selectedCityId = null) {
            if (!stateId) {
                citySelect.disabled = true;
                citySelect.value = '';
                while (citySelect.options.length > 1) {
                    citySelect.remove(1);
                }
                updateSelect2(citySelect);
                noCitiesAlert.style.display = 'none';
                return;
            }

            // AJAX fetch cities
            const formData = new FormData();
            formData.append('ajax_action', 'get_cities');
            formData.append('state_id', stateId);
            formData.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);

            fetch(window.location.href, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    while (citySelect.options.length > 1) {
                        citySelect.remove(1);
                    }
                    
                    if (data.cities.length === 0) {
                        citySelect.disabled = true;
                        noCitiesAlert.style.display = 'block';
                    } else {
                        citySelect.disabled = false;
                        noCitiesAlert.style.display = 'none';
                        data.cities.forEach(city => {
                            const option = new Option(city.name, city.id, false, city.id == selectedCityId);
                            citySelect.add(option);
                        });
                    }
                    updateSelect2(citySelect);
                }
            });
        }
        
        // Listen to native change and select2 change for Country
        countrySelect.addEventListener('change', function() {
            stateSelect.value = ''; // clear state
            filterStates();
        });
        if (typeof jQuery !== 'undefined') {
            jQuery(countrySelect).on('select2:select', function() {
                stateSelect.value = '';
                filterStates();
            });
        }
        
        // Listen to native change for State
        stateSelect.addEventListener('change', function() {
            citySelect.value = ''; // clear city
            loadCities(stateSelect.value);
        });
        
        // Initial Select2 binding for state
        bindStateSelect2Events();
        
        // Enable select2 for city
        if (typeof jQuery !== 'undefined') {
            jQuery(citySelect).select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: "Select City..."
            });
        }
        
        // Initial setup
        filterStates();

        // Modal Logic
        const addCityModal = new bootstrap.Modal(document.getElementById('addCityModal'));
        document.querySelectorAll('.openCityModalBtn').forEach(btn => btn.addEventListener('click', function(e) {
            e.preventDefault();
            if (!stateSelect.value) {
                alert('Please select a State first before adding a city.');
                return;
            }
            document.getElementById('modalCountryName').value = countrySelect.options[countrySelect.selectedIndex].text;
            document.getElementById('modalStateName').value = stateSelect.options[stateSelect.selectedIndex].text;
            document.getElementById('modalStateId').value = stateSelect.value;
            document.getElementById('modalCityName').value = '';
            addCityModal.show();
        }));

        document.getElementById('saveCityBtn').addEventListener('click', function() {
            const cityName = document.getElementById('modalCityName').value.trim();
            const stateId = document.getElementById('modalStateId').value;
            
            if (!cityName) return alert("City name is required");
            
            const formData = new FormData();
            formData.append('ajax_action', 'add_city');
            formData.append('state_id', stateId);
            formData.append('city_name', cityName);
            formData.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    addCityModal.hide();
                    loadCities(stateId, data.city.id);
                } else {
                    alert(data.message || 'Error saving city');
                }
            });
        });
    }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Scroll to first validation error if any
    const invalidInputs = document.querySelectorAll('.is-invalid');
    if (invalidInputs.length > 0) {
        invalidInputs[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
});
</script>