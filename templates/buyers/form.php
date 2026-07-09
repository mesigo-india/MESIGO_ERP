<?php include 'includes/header.php'; $b = $buyer; ?>
<div class="container-fluid py-4">
    <form method="POST" action="<?= $action ?>" id="buyerForm" class="needs-validation" novalidate>
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        
        <div class="accordion" id="crmAccordion">
            <!-- SECTION 1: Company Information -->
            <div class="accordion-item">
                <h2 class="accordion-header"><button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#s1">Company Information</button></h2>
                <div id="s1" class="accordion-collapse collapse show"><div class="accordion-body">
                    <div class="row">
                        <div class="col-md-4 mb-3"><label>Buyer Code *</label><input name="buyer_code" class="form-control" value="<?= htmlspecialchars($b['buyer_code'] ?? '') ?>" required></div>
                        <div class="col-md-4 mb-3"><label>Company Name *</label><input name="company_name" class="form-control" value="<?= htmlspecialchars($b['company_name'] ?? '') ?>" required></div>
                        <div class="col-md-4 mb-3"><label>Buyer Type *</label><select name="buyer_type" class="form-select" required><option value="Domestic">Domestic</option><option value="International">International</option></select></div>
                    </div>
                </div></div>
            </div>

            <!-- SECTION 2: Primary Contact -->
            <div class="accordion-item">
                <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#s2">Primary Contact</button></h2>
                <div id="s2" class="accordion-collapse collapse"><div class="accordion-body">
                    <div class="row">
                        <div class="col-md-3 mb-3"><label>Contact Person *</label><input name="contact_person" class="form-control" value="<?= htmlspecialchars($b['contact_person'] ?? '') ?>" required></div>
                        <div class="col-md-3 mb-3"><label>Email *</label><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($b['email'] ?? '') ?>" required></div>
                        <div class="col-md-3 mb-3"><label>Mobile *</label><input name="mobile" class="form-control" value="<?= htmlspecialchars($b['mobile'] ?? '') ?>" required></div>
                        <div class="col-md-3 mb-3"><label>WhatsApp</label><input name="whatsapp" class="form-control" value="<?= htmlspecialchars($b['whatsapp'] ?? '') ?>"></div>
                    </div>
                </div></div>
            </div>

            <!-- SECTION 3: Address -->
            <div class="accordion-item">
                <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#s3">Address</button></h2>
                <div id="s3" class="accordion-collapse collapse"><div class="accordion-body">
                    <div class="row">
                        <div class="col-md-6 mb-3"><label>Billing Address *</label><textarea name="billing_address" class="form-control" required><?= htmlspecialchars($b['billing_address'] ?? '') ?></textarea></div>
                        <div class="col-md-6 mb-3"><label>Shipping Address</label><textarea name="shipping_address" class="form-control"><?= htmlspecialchars($b['shipping_address'] ?? '') ?></textarea></div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 mb-3"><label>Country *</label><input name="country" class="form-control" value="<?= htmlspecialchars($b['country'] ?? '') ?>" required></div>
                        <div class="col-md-3 mb-3"><label>State *</label><input name="state" class="form-control" value="<?= htmlspecialchars($b['state'] ?? '') ?>" required></div>
                        <div class="col-md-3 mb-3"><label>City *</label><input name="city" class="form-control" value="<?= htmlspecialchars($b['city'] ?? '') ?>" required></div>
                        <div class="col-md-3 mb-3"><label>ZIP *</label><input name="zip" class="form-control" value="<?= htmlspecialchars($b['zip'] ?? '') ?>" required></div>
                    </div>
                </div></div>
            </div>

            <!-- SECTION 4: Business Details -->
            <div class="accordion-item">
                <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#s4">Business Details</button></h2>
                <div id="s4" class="accordion-collapse collapse"><div class="accordion-body">
                    <div class="row">
                        <div class="col-md-3 mb-3"><label>GST Number</label><input name="gst_number" class="form-control" value="<?= htmlspecialchars($b['gst_number'] ?? '') ?>"></div>
                        <div class="col-md-3 mb-3"><label>IEC Number</label><input name="iec_number" class="form-control" value="<?= htmlspecialchars($b['iec_number'] ?? '') ?>"></div>
                        <div class="col-md-3 mb-3"><label>Reg. Number</label><input name="registration_number" class="form-control" value="<?= htmlspecialchars($b['registration_number'] ?? '') ?>"></div>
                        <div class="col-md-3 mb-3"><label>Tax Number</label><input name="tax_number" class="form-control" value="<?= htmlspecialchars($b['tax_number'] ?? '') ?>"></div>
                    </div>
                </div></div>
            </div>

            <!-- SECTION 5: Bank Details -->
            <div class="accordion-item">
                <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#s5">Bank Details</button></h2>
                <div id="s5" class="accordion-collapse collapse"><div class="accordion-body">
                    <div class="row">
                        <div class="col-md-3 mb-3"><label>Bank Name</label><input name="bank_name" class="form-control" value="<?= htmlspecialchars($b['bank_name'] ?? '') ?>"></div>
                        <div class="col-md-3 mb-3"><label>Account Number</label><input name="account_number" class="form-control" value="<?= htmlspecialchars($b['account_number'] ?? '') ?>"></div>
                        <div class="col-md-3 mb-3"><label>SWIFT / IFSC</label><input name="swift_ifsc" class="form-control" value="<?= htmlspecialchars($b['swift_ifsc'] ?? '') ?>"></div>
                    </div>
                </div></div>
            </div>

            <!-- SECTION 6: Export Preferences -->
            <div class="accordion-item">
                <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#s6">Export Preferences</button></h2>
                <div id="s6" class="accordion-collapse collapse"><div class="accordion-body">
                    <div class="row">
                        <div class="col-md-3 mb-3"><label>Payment Terms</label><input name="payment_terms" class="form-control" value="<?= htmlspecialchars($b['payment_terms'] ?? '') ?>"></div>
                        <div class="col-md-3 mb-3"><label>Shipping Mode</label><input name="shipping_mode" class="form-control" value="<?= htmlspecialchars($b['shipping_mode'] ?? '') ?>"></div>
                        <div class="col-md-3 mb-3"><label>Preferred Port</label><input name="preferred_port" class="form-control" value="<?= htmlspecialchars($b['preferred_port'] ?? '') ?>"></div>
                    </div>
                </div></div>
            </div>

            <!-- SECTION 7: CRM -->
            <div class="accordion-item">
                <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#s7">CRM</button></h2>
                <div id="s7" class="accordion-collapse collapse"><div class="accordion-body">
                    <div class="mb-3"><label>Notes</label><textarea name="notes" class="form-control"><?= htmlspecialchars($b['notes'] ?? '') ?></textarea></div>
                </div></div>
            </div>
        </div>

        <div class="mt-4 text-end">
            <a href="/buyers" class="btn btn-secondary btn-lg px-4">Cancel</a>
            <button type="submit" class="btn btn-primary btn-lg px-5">Save Buyer</button>
        </div>
    </form>
</div>

<script>
(function() {
    'use strict';
    const form = document.getElementById('buyerForm');
    form.addEventListener('submit', function(e) {
        if (!form.checkValidity()) {
            e.preventDefault();
            form.classList.add('was-validated');
            alert('Please complete all required fields.');
            form.querySelector(':invalid').scrollIntoView({behavior: 'smooth', block: 'center'});
        }
    });
})();
</script>
<?php include 'includes/footer.php'; ?>
