<?php
$title = $title ?? 'Permission';
$action = $action ?? '/permissions';
$permission = $permission ?? null;
?>
<div class="page-header">
    <h1><?= escapeHtml($title) ?></h1>
    <a href="/permissions" class="btn btn-outline-secondary">Back</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="post" action="<?= escapeHtml($action) ?>" class="needs-validation" novalidate>
            <?= csrfToken() ?>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="name">Permission Name</label>
                    <input type="text" id="name" name="name" class="form-control" value="<?= escapeHtml($permission['name'] ?? '') ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="display_name">Display Name</label>
                    <input type="text" id="display_name" name="display_name" class="form-control" value="<?= escapeHtml($permission['display_name'] ?? '') ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="module">Module</label>
                    <input type="text" id="module" name="module" class="form-control" value="<?= escapeHtml($permission['module'] ?? '') ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="status">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="1" <?= (int) ($permission['status'] ?? 1) === 1 ? 'selected' : '' ?>>Active</option>
                        <option value="0" <?= (int) ($permission['status'] ?? 1) === 0 ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label" for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="3"><?= escapeHtml($permission['description'] ?? '') ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Save Permission</button>
        </form>
    </div>
</div>