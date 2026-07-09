<?php
$title = $title ?? 'Role';
$action = $action ?? '/roles';
$role = $role ?? null;
$permissions = $permissions ?? [];
$selectedPermissions = $selectedPermissions ?? [];
$rolePermissions = $selectedPermissions;
?>
<div class="page-header">
    <h1><?= escapeHtml($title) ?></h1>
    <a href="/roles" class="btn btn-outline-secondary">Back</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="post" action="<?= escapeHtml($action) ?>" class="needs-validation" novalidate>
            <?= csrfToken() ?>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="name">Role Name</label>
                    <input type="text" id="name" name="name" class="form-control" value="<?= escapeHtml($role['name'] ?? '') ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="display_name">Display Name</label>
                    <input type="text" id="display_name" name="display_name" class="form-control" value="<?= escapeHtml($role['display_name'] ?? '') ?>">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label" for="status">Status</label>
                <select id="status" name="status" class="form-select">
                    <option value="1" <?= (int) ($role['status'] ?? 1) === 1 ? 'selected' : '' ?>>Active</option>
                    <option value="0" <?= (int) ($role['status'] ?? 1) === 0 ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Permissions</label>
                <div class="row">
                    <?php foreach ($permissions as $permission): ?>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="<?= escapeHtml($permission['name']) ?>" id="perm_<?= (int) $permission['id'] ?>" <?= in_array($permission['name'], $rolePermissions, true) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="perm_<?= (int) $permission['id'] ?>"><?= escapeHtml($permission['display_name'] ?? $permission['name']) ?></label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Save Role</button>
        </form>
    </div>
</div>