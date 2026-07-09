<?php
$title = $title ?? 'Product';
$action = $action ?? '/products';
$product = $product ?? null;
$meta = $meta ?? [];
$packaging = $packaging ?? [[]];
$categories = $categories ?? [];
$grades = $grades ?? [];
$origins = $origins ?? [];
$hsCodes = $hsCodes ?? [];
$units = $units ?? [];
$packingTypes = $packingTypes ?? [];
?>
<div class="page-header">
    <h1><?= escapeHtml($title) ?></h1>
    <a href="/products" class="btn btn-outline-secondary">Back</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="post" action="<?= escapeHtml($action) ?>" class="needs-validation" novalidate>
            <?= csrfToken() ?>

            <h5 class="mb-3">Product Details</h5>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label" for="product_code">Product Code</label>
                    <input type="text" id="product_code" name="product_code" class="form-control" value="<?= escapeHtml($product['product_code'] ?? '') ?>" required>
                    <div class="invalid-feedback">Product code is required.</div>
                </div>
                <div class="col-md-8 mb-3">
                    <label class="form-label" for="name">Product Name</label>
                    <input type="text" id="name" name="name" class="form-control" value="<?= escapeHtml($product['name'] ?? '') ?>" required>
                    <div class="invalid-feedback">Product name is required.</div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label" for="category_id">Product Category</label>
                    <select id="category_id" name="category_id" class="form-select" data-master="product-categories" data-master-title="Product Category">
                        <option value="0">Select Category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= (int) $category['id'] ?>" <?= (int) ($product['category_id'] ?? 0) === (int) $category['id'] ? 'selected' : '' ?>><?= escapeHtml($category['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label" for="grade_id">Product Grade</label>
                    <select id="grade_id" name="grade_id" class="form-select" data-master="product-grades" data-master-title="Product Grade">
                        <option value="0">Select Grade</option>
                        <?php foreach ($grades as $grade): ?>
                            <option value="<?= (int) $grade['id'] ?>" <?= (int) ($meta['grade_id'] ?? 0) === (int) $grade['id'] ? 'selected' : '' ?>><?= escapeHtml($grade['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label" for="origin_id">Product Origin</label>
                    <select id="origin_id" name="origin_id" class="form-select" data-master="product-origins" data-master-title="Product Origin">
                        <option value="0">Select Origin</option>
                        <?php foreach ($origins as $origin): ?>
                            <option value="<?= (int) $origin['id'] ?>" <?= (int) ($meta['origin_id'] ?? 0) === (int) $origin['id'] ? 'selected' : '' ?>><?= escapeHtml($origin['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label" for="hsn_code">HS Code</label>
                    <select id="hsn_code" name="hsn_code" class="form-select" data-master="hs-codes" data-master-title="HS Code">
                        <option value="">Select HS Code</option>
                        <?php foreach ($hsCodes as $hsCode): ?>
                            <option value="<?= escapeHtml($hsCode['hs_code']) ?>" <?= ($product['hsn_code'] ?? '') === $hsCode['hs_code'] ? 'selected' : '' ?>><?= escapeHtml($hsCode['hs_code'] . ' - ' . ($hsCode['description'] ?? '')) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label" for="unit_id">Unit</label>
                    <select id="unit_id" name="unit_id" class="form-select" data-master="units" data-master-title="Unit">
                        <option value="0">Select Unit</option>
                        <?php foreach ($units as $unit): ?>
                            <option value="<?= (int) $unit['id'] ?>" <?= (int) ($product['unit_id'] ?? 0) === (int) $unit['id'] ? 'selected' : '' ?>><?= escapeHtml(($unit['code'] ?? '') . ' - ' . $unit['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label" for="packing_type_id">Primary Packaging</label>
                    <select id="packing_type_id" name="packing_type_id" class="form-select" data-master="packing-types" data-master-title="Packaging Type">
                        <option value="0">Select Packaging</option>
                        <?php foreach ($packingTypes as $packingType): ?>
                            <option value="<?= (int) $packingType['id'] ?>" <?= (int) ($product['packing_type_id'] ?? 0) === (int) $packingType['id'] ? 'selected' : '' ?>><?= escapeHtml($packingType['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <h5 class="mt-4 mb-3">GST</h5>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label" for="gst_rate">GST Rate (%)</label>
                    <input type="text" id="gst_rate" name="gst_rate" class="form-control" value="<?= escapeHtml($meta['gst_rate'] ?? '') ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label" for="gst_type">GST Type</label>
                    <input type="text" id="gst_type" name="gst_type" class="form-control" value="<?= escapeHtml($meta['gst_type'] ?? '') ?>" placeholder="Export / Domestic / Exempt">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label" for="status">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="1" <?= (int) ($product['status'] ?? 1) === 1 ? 'selected' : '' ?>>Active</option>
                        <option value="0" <?= (int) ($product['status'] ?? 1) === 0 ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
            </div>

            <h5 class="mt-4 mb-3">Packaging</h5>
            <?php for ($i = 0; $i < 3; $i++): $package = $packaging[$i] ?? []; ?>
                <div class="row border rounded p-2 mb-2">
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Packing Type</label>
                        <select name="package_packing_type_id[]" class="form-select" data-master="packing-types" data-master-title="Packaging Type">
                            <option value="0">Select Packaging</option>
                            <?php foreach ($packingTypes as $packingType): ?>
                                <option value="<?= (int) $packingType['id'] ?>" <?= (int) ($package['packing_type_id'] ?? 0) === (int) $packingType['id'] ? 'selected' : '' ?>><?= escapeHtml($packingType['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Unit</label>
                        <select name="package_unit_id[]" class="form-select" data-master="units" data-master-title="Unit">
                            <option value="0">Select Unit</option>
                            <?php foreach ($units as $unit): ?>
                                <option value="<?= (int) $unit['id'] ?>" <?= (int) ($package['unit_id'] ?? 0) === (int) $unit['id'] ? 'selected' : '' ?>><?= escapeHtml(($unit['code'] ?? '') . ' - ' . $unit['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Quantity Per Pack</label>
                        <input type="text" name="quantity_per_pack[]" class="form-control" value="<?= escapeHtml($package['quantity_per_pack'] ?? '') ?>">
                    </div>
                </div>
            <?php endfor; ?>

            <div class="mb-3 mt-4">
                <label class="form-label" for="description_text">Description</label>
                <textarea id="description_text" name="description_text" class="form-control" rows="3"><?= escapeHtml($meta['description_text'] ?? '') ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Save Product</button>
        </form>
    </div>
</div>