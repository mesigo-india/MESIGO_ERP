<?php
/**
 * Buyer CRM List Template
 */
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>Buyer CRM</h5>
            <a href="/buyers/create" class="btn btn-primary btn-sm">Add New Buyer</a>
        </div>
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Company Name</th>
                        <th>Primary Contact</th>
                        <th>Region</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($buyers)): foreach ($buyers as $b): ?>
                    <tr>
                        <td><?= htmlspecialchars($b['buyer_code']) ?></td>
                        <td><?= htmlspecialchars($b['company_name']) ?></td>
                        <td><?= htmlspecialchars($b['primary_contact_name'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($b['export_region'] ?? '-') ?></td>
                        <td><?= $b['status'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>' ?></td>
                        <td>
                            <a href="/buyers/edit/<?= $b['id'] ?>" class="btn btn-sm btn-info">Edit</a>
                            <a href="/buyers/delete/<?= $b['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="6" class="text-center">No buyers found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
