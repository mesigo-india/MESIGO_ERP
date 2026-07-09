<?php $companies = $companies ?? []; ?>
<div class="page-header">
    <h1>Companies</h1>
    <a href="/company/create" class="btn btn-primary">Add Company</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped datatable">
                <thead>
                    <tr>
                        <th>Company Name</th>
                        <th>Contact Person</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>GST Number</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($companies as $company): ?>
                        <tr>
                            <td><?= escapeHtml($company['company_name']) ?></td>
                            <td><?= escapeHtml($company['contact_person'] ?? '') ?></td>
                            <td><?= escapeHtml($company['email'] ?? '') ?></td>
                            <td><?= escapeHtml($company['phone'] ?? '') ?></td>
                            <td><?= escapeHtml($company['gst_number'] ?? '') ?></td>
                            <td><?= statusBadge((int) $company['status']) ?></td>
                            <td>
                                <a href="/company/<?= (int) $company['id'] ?>/edit" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="post" action="/company/<?= (int) $company['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Disable this company?');">
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