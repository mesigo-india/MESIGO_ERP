<?php $user = $user ?? null; $roles = $roles ?? []; $action = $action ?? '/users'; ?>
<div class="page-header mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-user-<?= $user ? 'edit' : 'plus' ?> me-2"></i><?= escapeHtml($title ?? 'User') ?></h1>
</div>

<div class="card">
    <div class="card-body">
        <form method="post" action="<?= escapeHtml($action) ?>">
            <?= csrfToken() ?>
            <div class="row g-3">
                <!-- Account Details -->
                <div class="col-12"><h6 class="text-muted text-uppercase fw-bold small mb-2">Account Details</h6><hr class="mt-0"></div>

                <div class="col-md-6">
                    <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                    <input type="text" id="username" name="username" class="form-control"
                           value="<?= escapeHtml($user['username'] ?? '') ?>" required autocomplete="off">
                </div>
                <div class="col-md-6">
                    <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                    <input type="email" id="email" name="email" class="form-control"
                           value="<?= escapeHtml($user['email'] ?? '') ?>" required autocomplete="off">
                </div>
                <div class="col-md-6">
                    <label for="password" class="form-label">
                        Password <?= $user ? '<span class="text-muted small">(leave blank to keep current)</span>' : '<span class="text-danger">*</span>' ?>
                    </label>
                    <input type="password" id="password" name="password" class="form-control"
                           <?= !$user ? 'required' : '' ?> autocomplete="new-password">
                </div>
                <div class="col-md-6">
                    <label for="role_id" class="form-label">Role</label>
                    <select id="role_id" name="role_id" class="form-select">
                        <option value="">— No Role —</option>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?= (int) $role['id'] ?>"
                                <?= isset($user['role_id']) && (int) $user['role_id'] === (int) $role['id'] ? 'selected' : '' ?>>
                                <?= escapeHtml($role['display_name'] ?: $role['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Personal Info -->
                <div class="col-12 mt-2"><h6 class="text-muted text-uppercase fw-bold small mb-2">Personal Information</h6><hr class="mt-0"></div>

                <div class="col-md-4">
                    <label for="first_name" class="form-label">First Name</label>
                    <input type="text" id="first_name" name="first_name" class="form-control"
                           value="<?= escapeHtml($user['first_name'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label for="last_name" class="form-label">Last Name</label>
                    <input type="text" id="last_name" name="last_name" class="form-control"
                           value="<?= escapeHtml($user['last_name'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="text" id="phone" name="phone" class="form-control"
                           value="<?= escapeHtml($user['phone'] ?? '') ?>">
                </div>

                <!-- Status -->
                <div class="col-md-4">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="1" <?= !$user || (int) ($user['status'] ?? 1) === 1 ? 'selected' : '' ?>>Active</option>
                        <option value="0" <?= $user && (int) ($user['status'] ?? 1) === 0 ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>

                <!-- Actions -->
                <div class="col-12 mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i><?= $user ? 'Update User' : 'Create User' ?>
                    </button>
                    <a href="/users" class="btn btn-secondary ms-2">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
