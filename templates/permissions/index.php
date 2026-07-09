<?php $permissions = $permissions ?? []; ?>
<div class="page-header">
    <h1>Permissions</h1>
    <a href="/permissions/create" class="btn btn-primary">Create Permission</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped datatable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Display Name</th>
                        <th>Module</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($permissions as $permission): ?>
                        <tr>
                            <td><?= escapeHtml($permission['name']) ?></td>
                            <td><?= escapeHtml($permission['display_name'] ?? '') ?></td>
                            <td><?= escapeHtml($permission['module'] ?? '') ?></td>
                            <td><?= statusBadge((int) $permission['status']) ?></td>
                            <td>
                                <a href="/permissions/<?= (int) $permission['id'] ?>/edit" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="post" action="/permissions/<?= (int) $permission['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Disable this permission?');">
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