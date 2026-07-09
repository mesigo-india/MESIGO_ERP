<?php $roles = $roles ?? []; ?>
<div class="page-header">
    <h1>Roles</h1>
    <a href="/roles/create" class="btn btn-primary">Create Role</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped datatable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Display Name</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($roles as $role): ?>
                        <tr>
                            <td><?= escapeHtml($role['name']) ?></td>
                            <td><?= escapeHtml($role['display_name'] ?? '') ?></td>
                            <td><?= statusBadge((int) $role['status']) ?></td>
                            <td>
                                <a href="/roles/<?= (int) $role['id'] ?>/edit" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="post" action="/roles/<?= (int) $role['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Disable this role?');">
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