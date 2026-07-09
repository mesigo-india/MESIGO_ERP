<?php
/**
 * Buyer Form Template - Export CRM Edition
 */
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5><?= $buyer ? 'Edit Buyer CRM' : 'Add New Export Buyer' ?></h5>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                <h6 class="text-primary mt-3 border-bottom">Company Information</h6>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Company Name</label>
                        <input type="text" name="company_name" class="form-control" value="<?= htmlspecialchars($buyer['company_name'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Company Website</label>
                        <input type="url" name="company_website" class="form-control" value="<?= htmlspecialchars($buyer['company_website'] ?? '') ?>">
                    </div>
                </div>

                <h6 class="text-primary mt-3 border-bottom">Primary Contact</h6>
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

                <h6 class="text-primary mt-3 border-bottom">Bank Details</h6>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label>Bank Name</label>
                        <input type="text" name="bank_name" class="form-control" value="<?= htmlspecialchars($buyer['bank_name'] ?? '') ?>">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>Account Number</label>
                        <input type="text" name="bank_account_number" class="form-control" value="<?= htmlspecialchars($buyer['bank_account_number'] ?? '') ?>">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>SWIFT Code</label>
                        <input type="text" name="bank_swift_code" class="form-control" value="<?= htmlspecialchars($buyer['bank_swift_code'] ?? '') ?>">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>Payment Terms</label>
                        <input type="text" name="payment_terms" class="form-control" value="<?= htmlspecialchars($buyer['payment_terms'] ?? '') ?>">
                    </div>
                </div>

                <h6 class="text-primary mt-3 border-bottom">CRM & Preferences</h6>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Export Region</label>
                        <input type="text" name="export_region" class="form-control" value="<?= htmlspecialchars($buyer['export_region'] ?? '') ?>">
                    </div>
                    <div class="col-md-12 mb-3">
                        <label>CRM Notes</label>
                        <textarea name="crm_notes" class="form-control" rows="3"><?= htmlspecialchars($buyer['crm_notes'] ?? '') ?></textarea>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-success">Save CRM Profile</button>
                    <a href="/buyers" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
