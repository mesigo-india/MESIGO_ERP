<?php include 'includes/header.php'; $b = $buyer; ?>
<div class="container-fluid py-4">
    <form method="POST" action="<?= $action ?>" id="buyerForm" class="needs-validation" novalidate>
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <div class="accordion" id="crmAccordion">
            <div class="accordion-item">
                <h2 class="accordion-header"><button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#s1">Company & Primary Contact</button></h2>
                <div id="s1" class="accordion-collapse collapse show"><div class="accordion-body">
                    <div class="row">
                        <div class="col-md-3 mb-3"><label>Buyer Code *</label><input name="buyer_code" class="form-control" value="<?= htmlspecialchars($b['buyer_code'] ?? '') ?>" required></div>
                        <div class="col-md-3 mb-3"><label>Company Name *</label><input name="company_name" class="form-control" value="<?= htmlspecialchars($b['company_name'] ?? '') ?>" required></div>
                        <div class="col-md-3 mb-3"><label>Contact Person *</label><input name="contact_person" class="form-control" value="<?= htmlspecialchars($b['contact_person'] ?? '') ?>" required></div>
                        <div class="col-md-3 mb-3"><label>Email *</label><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($b['email'] ?? '') ?>" required></div>
                    </div>
                </div></div>
            </div>
        </div>
        <div class="mt-4 text-end"><button type="submit" class="btn btn-primary btn-lg px-5">Save Buyer</button></div>
    </form>
</div>
<?php include 'includes/footer.php'; ?>
