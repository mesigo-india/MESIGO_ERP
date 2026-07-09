<?php
$products = $products ?? [];
$search = $search ?? '';
$status = $status ?? '';
?>
<div class="page-header">
    <h1>Products</h1>
    <a href="/products/create" class="btn btn-primary">Add Product</a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="get" action="/products" class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="search">Product Search</label>
                <input type="text" id="search" name="search" class="form-control" value="<?= escapeHtml($search ?? '') ?>" placeholder="Code, name, HS code or description">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="status">Status</label>
                <select id="status" name="status" class="form-select">
                    <option value="" <?= ($status ?? '') === '' ? 'selected' : '' ?>>All</option>
                    <option value="1" <?= ($status ?? '') === '1' ? 'selected' : '' ?>>Active</option>
                    <option value="0" <?= ($status ?? '') === '0' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">Search</button>
                <a href="/products" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped datatable">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Product</th>
                        <th>Category</th>
                        <th>HS Code</th>
                        <th>Unit</th>
                        <th>Packaging</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?= escapeHtml($product['product_code']) ?></td>
                            <td><?= escapeHtml($product['name']) ?></td>
                            <td><?= escapeHtml($product['category_name'] ?? '') ?></td>
                            <td><?= escapeHtml($product['hsn_code'] ?? '') ?></td>
                            <td><?= escapeHtml($product['unit_code'] ?? '') ?></td>
                            <td><?= escapeHtml($product['packing_type_name'] ?? '') ?></td>
                            <td><?= statusBadge((int) $product['status']) ?></td>
                            <td>
                                <a href="/products/<?= (int) $product['id'] ?>/edit" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="post" action="/products/<?= (int) $product['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Disable this product?');">
                                    <?= csrfToken() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Disable</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>