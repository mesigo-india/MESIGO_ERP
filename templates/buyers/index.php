<?php include 'includes/header.php'; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between mb-3">
        <h3>Buyers (<?= $total ?>)</h3>
        <div><a href="/buyers/create" class="btn btn-primary">+ Add Buyer</a></div>
    </div>
    <div class="card mb-4"><div class="card-body">
        <form method="get" class="row g-2">
            <div class="col-md-3"><input name="search" class="form-control" placeholder="Search..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"></div>
            <div class="col-md-2"><button class="btn btn-primary">Filter</button></div>
        </form>
    </div></div>
    <table class="table table-bordered bg-white">
        <thead><tr><th>Code</th><th>Company</th><th>Contact</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
            <?php foreach ($buyers as $b): ?>
            <tr><td><?= $b['buyer_code'] ?></td><td><?= $b['company_name'] ?></td><td><?= $b['contact_person'] ?></td>
                <td><?= $b['status'] ? 'Active' : 'Inactive' ?></td>
                <td><a href="/buyers/edit/<?= $b['id'] ?>" class="btn btn-sm btn-info">Edit</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include 'includes/footer.php'; ?>
