<?php
/**
 * Buyer CRM List Template - Export Edition
 */
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>Export Buyer CRM</h5>
            <a href="/buyers/create" class="btn btn-primary btn-sm">Add New Export Buyer</a>
        </div>
        <div class="card-body">
            <table class="table table-hover table-striped">
                <thead class="table-light">
                    <tr>
                        <th>Code</th>
                        <th>Company Name</th>
                        <th>Primary Contact</th>
                        <th>Export Region</th>
                        <th>Payment Terms</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($buyers)): foreach ($buyers as $b): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($b['buyer_code']) ?></strong></td>
                        <td><?= htmlspecialchars($b['company_name']) ?></td>
                        <td>
                            <?= htmlspecialchars($b['primary_contact_name'] ?? 'N/A') ?><br>
                            <small class="text-muted"><?= htmlspecialchars($b['primary_contact_email'] ?? '') ?></small>
                        </td>
                        <td><?= htmlspecialchars($b['export_region'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($b['payment_terms'] ?? 'Standard') ?></td>
                        <td>
                            <?= $b['status'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>' ?>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="/buyers/edit/<?= $b['id'] ?>" class="btn btn-sm btn-outline-info">Edit</a>
                                <a href="/buyers/delete/<?= $b['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this buyer profile?')">Delete</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="7" class="text-center">No export buyers found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
