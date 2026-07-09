<?php
/**
 * Buyer Form Template
 */
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5><?= $buyer ? 'Edit Buyer' : 'Add New Buyer' ?></h5>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                <h6 class="text-primary mt-3">Company Information</h6>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Company Name</label>
                        <input type="text" name="company_name" class="form-control" value="<?= htmlspecialchars($buyer['company_name'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Buyer Code</label>
                        <input type="text" name="buyer_code" class="form-control" value="<?= htmlspecialchars($buyer['buyer_code'] ?? '') ?>" required>
                    </div>
                </div>

                <h6 class="text-primary mt-3">Primary Contact</h6>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label>Name</label>
                        <input type="text" name="primary_contact_name" class="form-control" value="<?= htmlspecialchars($buyer['primary_contact_name'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Email</label>
                        <input type="email" name="primary_contact_email" class="form-control" value="<?= htmlspecialchars($buyer['primary_contact_email'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Phone</label>
                        <input type="text" name="primary_contact_phone" class="form-control" value="<?= htmlspecialchars($buyer['primary_contact_phone'] ?? '') ?>">
                    </div>
                </div>

                <h6 class="text-primary mt-3">Additional Contacts</h6>
                <div id="contact-list">
                    <?php if (!empty($buyer['contacts'])): foreach ($buyer['contacts'] as $c): ?>
                        <div class="row mb-2">
                            <div class="col-md-3"><input type="text" name="contact_name[]" class="form-control" value="<?= htmlspecialchars($c['name']) ?>"></div>
                            <div class="col-md-3"><input type="text" name="contact_designation[]" class="form-control" value="<?= htmlspecialchars($c['designation'] ?? '') ?>"></div>
                            <div class="col-md-3"><input type="email" name="contact_email[]" class="form-control" value="<?= htmlspecialchars($c['email'] ?? '') ?>"></div>
                            <div class="col-md-3"><input type="text" name="contact_phone[]" class="form-control" value="<?= htmlspecialchars($c['phone'] ?? '') ?>"></div>
                        </div>
                    <?php endforeach; else: ?>
                        <div class="row mb-2">
                            <div class="col-md-3"><input type="text" name="contact_name[]" class="form-control" placeholder="Name"></div>
                            <div class="col-md-3"><input type="text" name="contact_designation[]" class="form-control" placeholder="Designation"></div>
                            <div class="col-md-3"><input type="email" name="contact_email[]" class="form-control" placeholder="Email"></div>
                            <div class="col-md-3"><input type="text" name="contact_phone[]" class="form-control" placeholder="Phone"></div>
                        </div>
                    <?php endif; ?>
                </div>

                <h6 class="text-primary mt-3">Addresses</h6>
                <div id="address-list">
                    <?php if (!empty($buyer['addresses'])): foreach ($buyer['addresses'] as $a): ?>
                        <div class="row mb-2">
                            <div class="col-md-2">
                                <select name="addr_type[]" class="form-control">
                                    <option value="billing" <?= $a['address_type'] == 'billing' ? 'selected' : '' ?>>Billing</option>
                                    <option value="shipping" <?= $a['address_type'] == 'shipping' ? 'selected' : '' ?>>Shipping</option>
                                </select>
                            </div>
                            <div class="col-md-4"><input type="text" name="addr_line1[]" class="form-control" value="<?= htmlspecialchars($a['address_line1']) ?>"></div>
                            <div class="col-md-2"><input type="text" name="addr_city[]" class="form-control" value="<?= htmlspecialchars($a['city']) ?>"></div>
                            <div class="col-md-2"><input type="text" name="addr_country[]" class="form-control" value="<?= htmlspecialchars($a['country']) ?>"></div>
                            <div class="col-md-2"><input type="text" name="addr_zip[]" class="form-control" value="<?= htmlspecialchars($a['postal_code']) ?>"></div>
                        </div>
                    <?php endforeach; endif; ?>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-success">Save Buyer</button>
                    <a href="/buyers" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
