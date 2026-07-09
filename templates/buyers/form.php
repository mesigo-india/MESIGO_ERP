<?php
include 'includes/header.php';
$b = $buyer;
?>
<div class="container-fluid py-4">
    <div id="validation-alert" class="alert alert-danger d-none">Please complete all required fields.</div>
    <form method="POST" action="" id="buyerForm" class="needs-validation" novalidate>
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        
        <div class="row">
            <div class="col-md-4">
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-primary text-white"><strong>Company Information</strong></div>
                    <div class="card-body">
                        <div class="mb-3"><label class="form-label">Buyer Code *</label><input type="text" name="buyer_code" class="form-control" value="<?= htmlspecialchars($b['buyer_code'] ?? '') ?>" required></div>
                        <div class="mb-3"><label class="form-label">Company Name *</label><input type="text" name="company_name" class="form-control" value="<?= htmlspecialchars($b['company_name'] ?? '') ?>" required></div>
                        <div class="mb-3"><label class="form-label">Buyer Type *</label><select name="buyer_type" class="form-select" required><option value="Domestic">Domestic</option><option value="International">International</option></select></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-primary text-white"><strong>Primary Contact</strong></div>
                    <div class="card-body">
                        <div class="mb-3"><label class="form-label">Contact Person *</label><input type="text" name="contact_person" class="form-control" value="<?= htmlspecialchars($b['contact_person'] ?? '') ?>" required></div>
                        <div class="mb-3"><label class="form-label">Email *</label><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($b['email'] ?? '') ?>" required></div>
                        <div class="mb-3"><label class="form-label">Mobile *</label><input type="text" name="mobile" class="form-control" value="<?= htmlspecialchars($b['mobile'] ?? '') ?>" required></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-primary text-white"><strong>Address</strong></div>
                    <div class="card-body">
                        <div class="mb-3"><label class="form-label">Billing Address *</label><textarea name="billing_address" class="form-control" required><?= htmlspecialchars($b['billing_address'] ?? '') ?></textarea></div>
                        <div class="row">
                            <div class="col-6 mb-3"><label class="form-label">Country *</label><input type="text" name="country" class="form-control" value="<?= htmlspecialchars($b['country'] ?? '') ?>" required></div>
                            <div class="col-6 mb-3"><label class="form-label">City *</label><input type="text" name="city" class="form-control" value="<?= htmlspecialchars($b['city'] ?? '') ?>" required></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="fixed-bottom bg-light p-3 border-top text-end">
            <a href="/buyers" class="btn btn-secondary me-2">Cancel</a>
            <button type="submit" class="btn btn-primary px-5">Save Buyer Profile</button>
        </div>
    </form>
</div>

<script>
(function() {
    'use strict';
    const form = document.getElementById('buyerForm');
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            document.getElementById('validation-alert').classList.remove('d-none');
            form.classList.add('was-validated');
            form.querySelector(':invalid').scrollIntoView({behavior: 'smooth', block: 'center'});
        }
    }, false);
})();
</script>
<?php include 'includes/footer.php'; ?>
