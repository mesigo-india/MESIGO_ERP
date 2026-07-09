<?php
include 'includes/header.php';
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$country = $_GET['country'] ?? '';
$type = $_GET['type'] ?? '';
$priority = $_GET['priority'] ?? '';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Buyers (<?= $total ?>)</h3>
        <div>
            <a href="/buyers/import" class="btn btn-outline-secondary">Import</a>
            <a href="/buyers/export" class="btn btn-outline-secondary">Export</a>
            <a href="/buyers/create" class="btn btn-primary">+ Add Buyer</a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-3">
                    <input name="search" class="form-control" placeholder="Search by name/code..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">Status</option>
                        <option value="1" <?= $status == '1' ? 'selected' : '' ?>>Active</option>
                        <option value="0" <?= $status == '0' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="type" class="form-select">
                        <option value="">Buyer Type</option>
                        <option value="Domestic" <?= $type == 'Domestic' ? 'selected' : '' ?>>Domestic</option>
                        <option value="International" <?= $type == 'International' ? 'selected' : '' ?>>International</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="priority" class="form-select">
                        <option value="">Priority</option>
                        <option value="High" <?= $priority == 'High' ? 'selected' : '' ?>>High</option>
                        <option value="Medium" <?= $priority == 'Medium' ? 'selected' : '' ?>>Medium</option>
                        <option value="Low" <?= $priority == 'Low' ? 'selected' : '' ?>>Low</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Code</th>
                        <th>Company Name</th>
                        <th>Type</th>
                        <th>Priority</th>
                        <th>Contact</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($buyers)): ?>
                        <tr><td colspan="7" class="text-center py-4">No export buyers found.</td></tr>
                    <?php else: foreach ($buyers as $b): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($b['buyer_code']) ?></strong></td>
                        <td><?= htmlspecialchars($b['company_name']) ?></td>
                        <td><?= htmlspecialchars($b['buyer_type']) ?></td>
                        <td><?= htmlspecialchars($b['priority']) ?></td>
                        <td><?= htmlspecialchars($b['contact_person']) ?><br><small class="text-muted"><?= htmlspecialchars($b['email']) ?></small></td>
                        <td><?= (int)$b['status'] === 1 ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' ?></td>
                        <td class="text-end">
                            <a href="/buyers/edit/<?= $b['id'] ?>" class="btn btn-sm btn-info">Edit</a>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
