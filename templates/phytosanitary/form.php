<?php
$action = $action ?? '/phytosanitary';
$title = $title ?? 'Phytosanitary Certificate';
$certificate = $certificate ?? null;
$meta = $meta ?? [];
$items = $items ?? [[]];
$buyers = $buyers ?? [];
$currencies = $currencies ?? [];
$products = $products ?? [];
$packingTypes = $packingTypes ?? [];
$statuses = $statuses ?? [];
?>
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 text-primary font-weight-bold mb-0"><i class="fas fa-leaf text-success me-2"></i><?= escapeHtml($title) ?></h1>
    <a href="/phytosanitary" class="btn btn-outline-secondary">Back to List</a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        <form method="post" action="<?= escapeHtml($action) ?>" class="needs-validation" novalidate>
            <?= csrfToken() ?>

            <!-- Segment 1: Header Context -->
            <div class="bg-light p-3 rounded mb-4">
                <h5 class="text-secondary font-weight-bold mb-3 border-bottom pb-2">1. Quarantine Document Context</h5>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label font-weight-bold">Certificate Number</label>
                        <input type="text" class="form-control bg-white text-muted" value="<?= escapeHtml($certificate['document_number'] ?? 'Generated on Save') ?>" readonly>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label font-weight-bold" for="document_date">Certificate Date</label>
                        <input type="date" id="document_date" name="document_date" class="form-control" value="<?= escapeHtml($certificate['document_date'] ?? date('Y-m-d')) ?>" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label font-weight-bold" for="buyer_id">Buyer / Consignment Client</label>
                        <select id="buyer_id" name="buyer_id" class="form-select" required>
                            <option value="0">Select Buyer</option>
                            <?php foreach ($buyers as $b): ?>
                                <option value="<?= (int) $b['id'] ?>" <?= (int) ($certificate['buyer_id'] ?? 0) === (int) $b['id'] ? 'selected' : '' ?>><?= escapeHtml($b['company_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label font-weight-bold" for="currency_id">Currency (For reference)</label>
                        <select id="currency_id" name="currency_id" class="form-select" required>
                            <?php foreach ($currencies as $curr): ?>
                                <option value="<?= (int) $curr['id'] ?>" <?= (int) ($certificate['currency_id'] ?? 1) === (int) $curr['id'] ? 'selected' : '' ?>><?= escapeHtml($curr['code']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Segment 2: Consignment Details -->
            <div class="bg-light p-3 rounded mb-4">
                <h5 class="text-secondary font-weight-bold mb-3 border-bottom pb-2">2. Consignment Details</h5>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label font-weight-bold" for="botanical_name">Botanical Name (Scientific Name)</label>
                        <input type="text" id="botanical_name" name="botanical_name" class="form-control" value="<?= escapeHtml($meta['botanical_name'] ?? '') ?>" placeholder="e.g., Sesamum Indicum" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label font-weight-bold" for="place_of_origin">Place of Origin</label>
                        <input type="text" id="place_of_origin" name="place_of_origin" class="form-control" value="<?= escapeHtml($meta['place_of_origin'] ?? 'India') ?>" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label font-weight-bold" for="means_of_conveyance">Declared Means of Conveyance</label>
                        <input type="text" id="means_of_conveyance" name="means_of_conveyance" class="form-control" value="<?= escapeHtml($meta['means_of_conveyance'] ?? '') ?>" placeholder="e.g., Sea Freight / Vessel">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label font-weight-bold" for="port_of_entry">Declared Port of Entry</label>
                        <input type="text" id="port_of_entry" name="port_of_entry" class="form-control" value="<?= escapeHtml($meta['port_of_entry'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label font-weight-bold" for="declared_quantity">Declared Quantity</label>
                        <input type="text" id="declared_quantity" name="declared_quantity" class="form-control" value="<?= escapeHtml($meta['declared_quantity'] ?? '') ?>" placeholder="e.g., 20.000 Metric Tons">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label font-weight-bold" for="status">Status</label>
                        <select id="status" name="status" class="form-select">
                            <?php foreach ($statuses as $statusId => $statusName): ?>
                                <option value="<?= (int) $statusId ?>" <?= (int) ($certificate['status'] ?? 0) === (int) $statusId ? 'selected' : '' ?>><?= escapeHtml($statusName) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label font-weight-bold" for="consignee">Declared Name and Address of Consignee</label>
                        <textarea id="consignee" name="consignee" class="form-control" rows="3"><?= escapeHtml($meta['consignee'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Segment 3: Chemical Active Treatment -->
            <div class="bg-light p-3 rounded mb-4 border border-success">
                <h5 class="text-success font-weight-bold mb-3 border-bottom pb-2"><i class="fas fa-flask me-2"></i>3. Disinfestation and/or Disinfection Treatment</h5>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label" for="treatment_date">Date of Treatment</label>
                        <input type="date" id="treatment_date" name="treatment_date" class="form-control" value="<?= escapeHtml($meta['treatment_date'] ?? '') ?>">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label" for="treatment_chemical">Chemical Active Substance</label>
                        <input type="text" id="treatment_chemical" name="treatment_chemical" class="form-control" value="<?= escapeHtml($meta['treatment_chemical'] ?? '') ?>" placeholder="e.g., Methyl Bromide (CH3Br)">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label" for="treatment_duration">Duration & Concentration</label>
                        <input type="text" id="treatment_duration" name="treatment_duration" class="form-control" value="<?= escapeHtml($meta['treatment_duration'] ?? '') ?>" placeholder="e.g., 24 Hours (32 g/m3)">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label" for="treatment_temperature">Temperature</label>
                        <input type="text" id="treatment_temperature" name="treatment_temperature" class="form-control" value="<?= escapeHtml($meta['treatment_temperature'] ?? '') ?>" placeholder="e.g., 21°C or above">
                    </div>
                </div>
            </div>

            <!-- Segment 4: Cargo Product List -->
            <div class="bg-light p-3 rounded mb-4">
                <h5 class="text-secondary font-weight-bold mb-3 border-bottom pb-2">4. Product Description</h5>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>Product</th>
                                <th>HS Code</th>
                                <th>Quantity (Packages)</th>
                                <th>Item Remarks / Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for ($i = 0; $i < 3; $i++): $item = $items[$i] ?? []; ?>
                                <tr>
                                    <td>
                                        <select name="product_id[]" class="form-select form-select-sm">
                                            <option value="0">Select Product</option>
                                            <?php foreach ($products as $prod): ?>
                                                <option value="<?= (int) $prod['id'] ?>" <?= (int) ($item['product_id'] ?? 0) === (int) $prod['id'] ? 'selected' : '' ?>><?= escapeHtml($prod['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="hsn_code[]" class="form-control form-control-sm" value="<?= escapeHtml($item['hsn_code'] ?? '') ?>" placeholder="HS Code">
                                    </td>
                                    <td>
                                        <input type="text" name="quantity[]" class="form-control form-control-sm" value="<?= escapeHtml($item['quantity'] ?? '') ?>" placeholder="Bags / Cartons">
                                    </td>
                                    <td>
                                        <input type="text" name="item_remarks[]" class="form-control form-control-sm" value="<?= escapeHtml($item['item_remarks'] ?? '') ?>">
                                    </td>
                                </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Notes & Remarks -->
            <div class="row mb-4">
                <div class="col-12">
                    <label class="form-label font-weight-bold" for="remarks">Special Instructions / Remarks</label>
                    <textarea id="remarks" name="remarks" class="form-control" rows="3"><?= escapeHtml($certificate['remarks'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Action Controls -->
            <div class="d-flex justify-content-end gap-2 border-top pt-4">
                <a href="/phytosanitary" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i> Save Phytosanitary Certificate</button>
            </div>
        </form>
    </div>
</div>
