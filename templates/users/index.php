<?php $users = $users ?? []; $search = $search ?? ''; $status = $status ?? ''; ?>
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-users me-2"></i>User Management</h1>
    <?php if ($this->auth->can('users.create')): ?>
        <a href="/users/create" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Add User</a>
    <?php endif; ?>
</div>

<!-- Filters -->
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="get" action="/users" class="row g-2 align-items-end">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search username, email, name…" value="<?= escapeHtml($search) ?>">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="1" <?= $status === 1 || $status === '1' ? 'selected' : '' ?>>Active</option>
                    <option value="0" <?= $status === 0 || $status === '0' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-secondary w-100"><i class="fas fa-search me-1"></i>Filter</button>
            </div>
            <div class="col-md-2">
                <a href="/users" class="btn btn-sm btn-outline-secondary w-100">Clear</a>
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
                        <th>#</th>
                        <th>Username</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Last Login</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= (int) $u['id'] ?></td>
                        <td><strong><?= escapeHtml($u['username']) ?></strong></td>
                        <td><?= escapeHtml(trim($u['first_name'] . ' ' . $u['last_name'])) ?></td>
                        <td><?= escapeHtml($u['email']) ?></td>
                        <td><?= escapeHtml($u['role_name'] ?? '—') ?></td>
                        <td><?= $u['last_login_at'] ? date('d M Y H:i', strtotime($u['last_login_at'])) : '—' ?></td>
                        <td><?= statusBadge((int) $u['status']) ?></td>
                        <td>
                            <?php if ($this->auth->can('users.update')): ?>
                                <a href="/users/<?= (int) $u['id'] ?>/edit" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                            <?php endif; ?>
                            <?php if ($this->auth->can('users.delete')): ?>
                                <form method="post" action="/users/<?= (int) $u['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Delete this user? This cannot be undone.');">
                                    <?= csrfToken() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($users)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">No users found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
