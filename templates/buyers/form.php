<?php
/**
 * MESIGO ERP — Professional Export Buyer CRM Form
 * Variables: $buyer (array), $action (string), $errors (array), $title (string)
 */
$b      = $buyer  ?? [];
$errors = $errors ?? [];
$e      = static function (string $k, array $b): string {
    return htmlspecialchars($b[$k] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
};
$err    = static function (string $k, array $errors): string {
    return !empty($errors[$k])
        ? '<div class="invalid-feedback d-block mt-1"><i class="fas fa-exclamation-circle me-1"></i>' . htmlspecialchars($errors[$k]) . '</div>'
        : '';
};
$cls    = static function (string $k, array $errors): string {
    return !empty($errors[$k]) ? ' is-invalid' : '';
};
$isNew  = empty($b['id']);
?>

<!-- ── Page Header ─────────────────────────────────────────────────────────── -->
<div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold">
            <i class="fas fa-<?= $isNew ? 'user-plus' : 'user-edit' ?> me-2 text-primary"></i>
            <?= $isNew ? 'New Buyer' : htmlspecialchars('Edit: ' . ($b['company_name'] ?? '')) ?>
        </h4>
        <nav aria-label="breadcrumb" class="mt-1">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="/">Home</a></li>
                <li class="breadcrumb-item"><a href="/buyers">Buyers</a></li>
                <li class="breadcrumb-item active"><?= $isNew ? 'New Buyer' : 'Edit' ?></li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <?php if (!$isNew): ?>
        <a href="/buyers/create?duplicate_from=<?= (int)($b['id'] ?? 0) ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-copy me-1"></i>Duplicate
        </a>
        <?php endif; ?>
        <a href="/buyers" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-times me-1"></i>Cancel
        </a>
        <button type="submit" form="buyerForm" class="btn btn-primary btn-sm">
            <i class="fas fa-save me-1"></i>Save Buyer
        </button>
    </div>
</div>

<?php if (!empty($errors['db'])): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-database me-2"></i><strong>Database Error:</strong>
    <?= htmlspecialchars($errors['db']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if (!empty($errors['csrf'])): ?>
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <i class="fas fa-shield-alt me-2"></i><?= htmlspecialchars($errors['csrf']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if (!empty($errors['email_warning'])): ?>
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($errors['email_warning']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- ── Main Form ────────────────────────────────────────────────────────────── -->
<form method="POST" action="<?= htmlspecialchars($action ?? '/buyers') ?>" id="buyerForm" novalidate>
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

    <div class="row g-4">
    <div class="col-lg-12">

        <!-- ================================================================
             SECTION 1 — COMPANY INFORMATION
             ================================================================ -->
        <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
            <h5 class="fw-bold text-primary mb-0"><i class="fas fa-building me-2"></i>Company Information</h5>
        </div>
        <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Buyer Code <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-barcode text-muted"></i></span>
                                <input type="text" name="buyer_code" id="buyer_code"
                                       class="form-control<?= $cls('buyer_code', $errors) ?>"
                                       value="<?= $e('buyer_code', $b) ?>"
                                       placeholder="e.g. MIP_Buyer_001" required maxlength="50" readonly>
                            </div>
                            <?= $err('buyer_code', $errors) ?>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-semibold">Company Name <span class="text-danger">*</span></label>
                            <input type="text" name="company_name" id="company_name"
                                   class="form-control<?= $cls('company_name', $errors) ?>"
                                   value="<?= $e('company_name', $b) ?>"
                                   placeholder="Full legal company name" required maxlength="200">
                            <?= $err('company_name', $errors) ?>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Buyer Type</label>
                            <select name="buyer_type" class="form-select">
                                <option value="">— Select —</option>
                                <?php foreach (['International','Domestic','Trading Company','Manufacturer','Distributor','Agent','End User'] as $opt): ?>
                                <option value="<?= $opt ?>" <?= ($b['buyer_type'] ?? '') === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="status" class="form-select">
                                <option value="1" <?= (($b['status'] ?? 1) == 1) ? 'selected' : '' ?>>🟢 Active</option>
                                <option value="2" <?= (($b['status'] ?? 1) == 2) ? 'selected' : '' ?>>🔵 Prospect</option>
                                <option value="0" <?= (($b['status'] ?? 1) == 0) ? 'selected' : '' ?>>⚪ Inactive</option>
                                <option value="3" <?= (($b['status'] ?? 1) == 3) ? 'selected' : '' ?>>🟡 Pending</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Customer Since</label>
                            <input type="date" name="customer_since" class="form-control"
                                   value="<?= $e('customer_since', $b) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Website</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-globe text-muted"></i></span>
                                <input type="text" name="website" class="form-control"
                                       value="<?= $e('website', $b) ?>"
                                       placeholder="example.com" maxlength="255">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">GST / VAT Number</label>
                            <input type="text" name="gst_number" class="form-control"
                                   value="<?= $e('gst_number', $b) ?>" placeholder="GSTIN or VAT No." maxlength="30">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">IEC Number</label>
                            <input type="text" name="iec_number" class="form-control"
                                   value="<?= $e('iec_number', $b) ?>" placeholder="Import Export Code" maxlength="20">
                        </div>
                    </div>
                </div>
        </div>

        <!-- ================================================================
             SECTION 2 — PRIMARY CONTACT
             ================================================================ -->
        <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
            <h5 class="fw-bold text-primary mb-0"><i class="fas fa-user me-2"></i>Primary Contact</h5>
        </div>
        <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Contact Person <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user text-muted"></i></span>
                                <input type="text" name="contact_person" id="contact_person"
                                       class="form-control<?= $cls('contact_person', $errors) ?>"
                                       value="<?= $e('contact_person', $b) ?>"
                                       placeholder="Full name" required maxlength="150">
                            </div>
                            <?= $err('contact_person', $errors) ?>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Designation / Title</label>
                            <input type="text" name="designation" class="form-control"
                                   value="<?= $e('designation', $b) ?>"
                                   placeholder="e.g. Director, Procurement Manager" maxlength="100">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope text-muted"></i></span>
                                <input type="email" name="email" id="email"
                                       class="form-control<?= $cls('email', $errors) ?>"
                                       value="<?= $e('email', $b) ?>"
                                       placeholder="contact@company.com" required maxlength="255">
                            </div>
                            <?= $err('email', $errors) ?>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Mobile <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-mobile-alt text-muted"></i></span>
                                <input type="tel" name="mobile" id="mobile"
                                       class="form-control<?= $cls('mobile', $errors) ?>"
                                       value="<?= $e('mobile', $b) ?>"
                                       placeholder="+1 234 567 8900" required maxlength="30">
                            </div>
                            <?= $err('mobile', $errors) ?>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Phone / Landline</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-phone text-muted"></i></span>
                                <input type="tel" name="phone" class="form-control"
                                       value="<?= $e('phone', $b) ?>" placeholder="+1 234 567 8901" maxlength="30">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">WhatsApp Number</label>
                            <div class="input-group">
                                <span class="input-group-text text-success"><i class="fab fa-whatsapp"></i></span>
                                <input type="tel" name="whatsapp" class="form-control"
                                       value="<?= $e('whatsapp', $b) ?>" placeholder="+1 234 567 8900" maxlength="30">
                            </div>
                        </div>
                    </div>
                </div>
        </div>

        <!-- ================================================================
             SECTION 3 — ADDRESS
             ================================================================ -->
        <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
            <h5 class="fw-bold text-primary mb-0"><i class="fas fa-map-marker-alt me-2"></i>Address</h5>
        </div>
        <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Billing Address <span class="text-danger">*</span></label>
                            <textarea name="billing_address" rows="3"
                                      class="form-control<?= $cls('billing_address', $errors) ?>"
                                      placeholder="Street, building, floor…" required><?= $e('billing_address', $b) ?></textarea>
                            <?= $err('billing_address', $errors) ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Shipping Address</label>
                            <textarea name="shipping_address" rows="3"
                                      class="form-control"
                                      placeholder="Same as billing or different…"><?= $e('shipping_address', $b) ?></textarea>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Country</label>
                            <select name="country_id" id="country_id" class="form-select select2">
                                <option value="" disabled selected>Select Country...</option>
                                <?php foreach ($countries as $country_row): ?>
                                    <option value="<?= $country_row['id'] ?>" <?= ($b['country_id'] ?? '') == $country_row['id'] ? 'selected' : '' ?>><?= htmlspecialchars($country_row['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">State / Province</label>
                            <select name="state_id" id="state_id" class="form-select select2">
                                <option value="" disabled selected>Select State...</option>
                                <?php foreach ($states ?? [] as $st): ?>
                                    <option value="<?= $st['id'] ?>" data-country="<?= $st['country_id'] ?>" <?= ($b['state_id'] ?? '') == $st['id'] ? 'selected' : '' ?>><?= htmlspecialchars($st['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold w-100 d-flex justify-content-between align-items-center"><span>City</span><a href="#" class="text-primary small text-decoration-none openCityModalBtn"><i class="fas fa-plus"></i> Add</a></label>
                            <select name="city_id" class="form-select select2">
                                <option value="" disabled selected>Select City...</option>
                                <?php foreach ($cities ?? [] as $city): ?>
                                    <option value="<?= $city['id'] ?>" <?= ($b['city_id'] ?? '') == $city['id'] ? 'selected' : '' ?>><?= htmlspecialchars($city['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">ZIP / Postal Code</label>
                            <input type="text" name="zip" class="form-control"
                                   value="<?= $e('zip', $b) ?>" placeholder="ZIP" maxlength="20">
                        </div>
                    </div>
                </div>
        </div>

        <!-- ================================================================
             SECTION 4 — BANK DETAILS
             ================================================================ -->
        <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
            <h5 class="fw-bold text-primary mb-0"><i class="fas fa-university me-2"></i>Bank Details</h5>
        </div>
        <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Bank Name</label>
                            <input type="text" name="bank_name" class="form-control"
                                   value="<?= $e('bank_name', $b) ?>" placeholder="Bank of America" maxlength="150">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Account Holder Name</label>
                            <input type="text" name="account_name" class="form-control"
                                   value="<?= $e('account_name', $b) ?>" placeholder="As on account" maxlength="150">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Account Number / IBAN</label>
                            <input type="text" name="account_number" class="form-control"
                                   value="<?= $e('account_number', $b) ?>" placeholder="Account No." maxlength="50">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">SWIFT / IFSC Code</label>
                            <input type="text" name="swift_ifsc" class="form-control"
                                   value="<?= $e('swift_ifsc', $b) ?>" placeholder="SWIFT or IFSC" maxlength="20">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Tax / PAN Number</label>
                            <input type="text" name="tax_number" class="form-control"
                                   value="<?= $e('tax_number', $b) ?>" placeholder="Tax ID" maxlength="30">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Registration Number</label>
                            <input type="text" name="registration_number" class="form-control"
                                   value="<?= $e('registration_number', $b) ?>" placeholder="Company Reg. No." maxlength="50">
                        </div>
                    </div>
                </div>
        </div>

        <!-- ================================================================
             SECTION 5 — PAYMENT & TERMS
             ================================================================ -->
        <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
            <h5 class="fw-bold text-primary mb-0"><i class="fas fa-money-bill-wave me-2"></i>Payment &amp; Terms</h5>
        </div>
        <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Payment Terms</label>
                            <select name="payment_terms" class="form-select">
                                <option value="">— Select —</option>
                                <?php foreach (['LC at Sight','LC 30 Days','LC 60 Days','LC 90 Days','TT in Advance','TT Against Documents','CAD / Documents Against Payment','Open Account 30 Days','Open Account 60 Days','Open Account 90 Days','Cash on Delivery','Other'] as $opt): ?>
                                <option value="<?= $opt ?>" <?= ($b['payment_terms'] ?? '') === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Credit Days</label>
                            <div class="input-group">
                                <input type="number" name="credit_days" class="form-control"
                                       value="<?= $e('credit_days', $b) ?>" placeholder="0" min="0" max="365">
                                <span class="input-group-text">days</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Preferred Currency</label>
                            <select name="preferred_currency" class="form-select">
                                <option value="">— Select —</option>
                                <?php foreach (['USD - US Dollar','EUR - Euro','GBP - British Pound','INR - Indian Rupee','AED - UAE Dirham','SGD - Singapore Dollar','JPY - Japanese Yen','CNY - Chinese Yuan','AUD - Australian Dollar','Other'] as $opt): ?>
                                <?php $val = explode(' - ', $opt)[0]; ?>
                                <option value="<?= $val ?>" <?= ($b['preferred_currency'] ?? '') === $val ? 'selected' : '' ?>><?= $opt ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Preferred Incoterm</label>
                            <select name="preferred_incoterm" class="form-select">
                                <option value="">— Select —</option>
                                <?php foreach (['EXW','FCA','FAS','FOB','CFR','CIF','CPT','CIP','DAP','DPU','DDP'] as $opt): ?>
                                <option value="<?= $opt ?>" <?= ($b['preferred_incoterm'] ?? '') === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
        </div>

        <!-- ================================================================
             SECTION 6 — SHIPPING PREFERENCES
             ================================================================ -->
        <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
            <h5 class="fw-bold text-primary mb-0"><i class="fas fa-ship me-2"></i>Shipping Preferences</h5>
        </div>
        <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Shipping Mode</label>
                            <select name="shipping_mode" class="form-select">
                                <option value="">— Select —</option>
                                <?php foreach (['Sea Freight','Air Freight','Road Transport','Rail Transport','Sea + Air','Courier','As per Buyer Request'] as $opt): ?>
                                <option value="<?= $opt ?>" <?= ($b['shipping_mode'] ?? '') === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Preferred Loading Port</label>
                            <input type="text" name="preferred_port" class="form-control"
                                   value="<?= $e('preferred_port', $b) ?>" placeholder="e.g. Nhava Sheva, JNPT" maxlength="100">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Preferred Destination Port</label>
                            <input type="text" name="preferred_destination_port" class="form-control"
                                   value="<?= $e('preferred_destination_port', $b) ?>" placeholder="e.g. Port of Rotterdam" maxlength="100">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Preferred Container</label>
                            <select name="preferred_container" class="form-select">
                                <option value="">— Select —</option>
                                <?php foreach (['20 FT Standard','40 FT Standard','40 FT High Cube','40 FT Refrigerated (Reefer)','LCL (Less than Container Load)','Bulk','Flexi Tank','As per Order'] as $opt): ?>
                                <option value="<?= $opt ?>" <?= ($b['preferred_container'] ?? '') === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Preferred Packing</label>
                            <input type="text" name="preferred_packing" class="form-control"
                                   value="<?= $e('preferred_packing', $b) ?>" placeholder="e.g. Jute Bag, HDPE Bag, Cartons" maxlength="100">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Shipping Marks</label>
                            <input type="text" name="shipping_marks" class="form-control"
                                   value="<?= $e('shipping_marks', $b) ?>" placeholder="Custom shipping marks" maxlength="100">
                        </div>
                    </div>
                </div>
        </div>

        <!-- ================================================================
             SECTION 7 — EXPORT BUSINESS
             ================================================================ -->
        <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
            <h5 class="fw-bold text-primary mb-0"><i class="fas fa-globe-americas me-2"></i>Export Business</h5>
        </div>
        <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Preferred Products / Commodities</label>
                            <textarea name="preferred_products" rows="3" class="form-control"
                                      placeholder="List of products this buyer is interested in (e.g. Rice, Spices, Textile)"><?= $e('preferred_products', $b) ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Import Countries</label>
                            <textarea name="import_countries" rows="3" class="form-control"
                                      placeholder="Countries this buyer imports from (e.g. India, Vietnam, China)"><?= $e('import_countries', $b) ?></textarea>
                        </div>
                    </div>
                    <div class="mt-3 p-3 bg-light rounded-2 small text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        <strong>Future Integration:</strong> This buyer will be linkable to
                        <span class="badge bg-secondary">Quotations</span>
                        <span class="badge bg-secondary">Proforma Invoices</span>
                        <span class="badge bg-secondary">Commercial Invoices</span>
                        <span class="badge bg-secondary">Packing Lists</span>
                        <span class="badge bg-secondary">Bills of Lading</span>
                        <span class="badge bg-secondary">Certificates of Origin</span>
                    </div>
                </div>
        </div>

        <!-- ================================================================
             SECTION 8 — CRM INFORMATION
             ================================================================ -->
        <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
            <h5 class="fw-bold text-primary mb-0"><i class="fas fa-chart-line me-2"></i>CRM Information</h5>
        </div>
        <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Lead Source</label>
                            <select name="lead_source" class="form-select">
                                <option value="">— Select —</option>
                                <?php foreach (['Direct','Referral','Trade Fair / Exhibition','Online / Website','Agent','Cold Call','Import Data','LinkedIn','Other'] as $opt): ?>
                                <option value="<?= $opt ?>" <?= ($b['lead_source'] ?? '') === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Lead / Pipeline Status</label>
                            <select name="lead_status" class="form-select">
                                <option value="">— Select —</option>
                                <?php foreach (['New Lead','Contacted','Follow-up Pending','Qualified','Proposal Sent','Negotiation','Won - Active Customer','Cold','Lost'] as $opt): ?>
                                <option value="<?= $opt ?>" <?= ($b['lead_status'] ?? 'New Lead') === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Priority</label>
                            <div class="d-flex gap-2 mt-1">
                                <?php foreach (['High' => 'danger', 'Medium' => 'warning', 'Low' => 'secondary'] as $pVal => $pCls): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="priority"
                                           id="prio_<?= strtolower($pVal) ?>" value="<?= $pVal ?>"
                                           <?= ($b['priority'] ?? '') === $pVal ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="prio_<?= strtolower($pVal) ?>">
                                        <span class="badge bg-<?= $pCls ?>"><?= $pVal ?></span>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Assigned Executive</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user-tie text-muted"></i></span>
                                <input type="text" name="assigned_to" class="form-control"
                                       value="<?= $e('assigned_to', $b) ?>" placeholder="Sales executive name" maxlength="100">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Last Contact Date</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-calendar-check text-muted"></i></span>
                                <input type="date" name="last_contact" class="form-control"
                                       value="<?= $e('last_contact', $b) ?>"
                                       max="<?= date('Y-m-d') ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Next Follow-up Date</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-calendar-day text-muted"></i></span>
                                <input type="date" name="next_followup" class="form-control"
                                       value="<?= $e('next_followup', $b) ?>">
                            </div>
                            <?php
                                $nf = $b['next_followup'] ?? '';
                                if ($nf && $nf < date('Y-m-d')):
                            ?>
                            <div class="text-danger mt-1 small"><i class="fas fa-exclamation-triangle me-1"></i>Follow-up is overdue!</div>
                            <?php elseif ($nf && $nf === date('Y-m-d')): ?>
                            <div class="text-warning mt-1 small"><i class="fas fa-bell me-1"></i>Follow-up is today!</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
        </div>

        <!-- ================================================================
             SECTION 9 — NOTES
             ================================================================ -->
        <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
            <h5 class="fw-bold text-primary mb-0"><i class="fas fa-sticky-note me-2"></i>Notes &amp; Remarks</h5>
        </div>
        <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Internal Notes / Remarks</label>
                            <textarea name="notes" rows="4" class="form-control"
                                      placeholder="Any internal notes, special instructions, or remarks about this buyer…"><?= $e('notes', $b) ?></textarea>
                            <div class="form-text">Visible to internal team only. Not printed on documents.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!$isNew): ?>
        <!-- ================================================================
             SECTION 10 — HISTORY (Audit Info)
             ================================================================ -->
        <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
            <h5 class="fw-bold text-primary mb-0"><i class="fas fa-history me-2"></i>Record History</h5>
        </div>
        <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label small text-muted">Buyer ID</label>
                            <div class="fw-bold"><?= (int)($b['id'] ?? 0) ?></div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted">Created At</label>
                            <div><?= !empty($b['created_at']) ? date('d M Y, H:i', strtotime($b['created_at'])) : '—' ?></div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted">Last Updated</label>
                            <div><?= !empty($b['updated_at']) ? date('d M Y, H:i', strtotime($b['updated_at'])) : '—' ?></div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted">Quick Links</label>
                            <div class="d-flex gap-2 flex-wrap">
                                <a href="/quotations/create?buyer_id=<?= (int)($b['id'] ?? 0) ?>" class="btn btn-sm btn-outline-success">
                                    <i class="fas fa-file-invoice me-1"></i>New Quotation
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div><!-- /accordion -->

    <!-- ── Bottom Save Bar ─────────────────────────────────────────────────── -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body py-3 px-4 d-flex justify-content-between align-items-center">
            <div class="text-muted small">
                <i class="fas fa-shield-alt me-1 text-success"></i>
                All data is encrypted and stored securely.
                Fields marked <span class="text-danger fw-bold">*</span> are required.
            </div>
            <div class="d-flex gap-2">
                <a href="/buyers" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>Cancel
                </a>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-save me-1"></i>Save Buyer
                </button>
            </div>
        </div>
    </div>

</form>

<style>
.select2-results__options {
    max-height: 250px !important;
    overflow-y: auto !important;
}
.select2-results__option--highlighted[aria-selected],
.select2-results__option:hover {
    background-color: #0d6efd !important;
    color: white !important;
}
</style>

<div id="noCitiesAlert" class="mt-2 text-danger fw-bold" style="display: none;">
    No cities found. 
    <button type="button" class="btn btn-sm btn-outline-primary ms-2 openCityModalBtn">
        <i class="fas fa-plus"></i> Add New City
    </button>
</div>

<!-- Modal for Adding New City -->
<div class="modal fade" id="addCityModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-city me-2 text-primary"></i>Add New City</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
            <label class="form-label fw-semibold">Country</label>
            <input type="text" id="modalCountryName" class="form-control bg-light" readonly>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">State/Region</label>
            <input type="text" id="modalStateName" class="form-control bg-light" readonly>
            <input type="hidden" id="modalStateId">
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">City Name <span class="text-danger">*</span></label>
            <input type="text" id="modalCityName" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="saveCityBtn">
            <i class="fas fa-save me-1"></i> Save
        </button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const countrySelect = document.getElementById('country_id');
    const stateSelect = document.getElementById('state_id');
    const citySelect = document.querySelector('select[name="city_id"]');
    const noCitiesAlert = document.getElementById('noCitiesAlert');
    const citySelectContainer = citySelect.parentElement;
    
    // Setup alert inside container
    citySelectContainer.appendChild(noCitiesAlert);

    // Initial disable states if empty
    if (!countrySelect.value) {
        stateSelect.disabled = true;
        citySelect.disabled = true;
    } else if (!stateSelect.value) {
        citySelect.disabled = true;
    }

    if (countrySelect && stateSelect && citySelect) {
        // Cache all states internally
        const allStates = Array.from(stateSelect.options).filter(opt => opt.value !== '').map(opt => ({
            value: opt.value,
            text: opt.text,
            countryId: opt.getAttribute('data-country'),
            selected: opt.selected
        }));
        
        function updateSelect2(el) {
            if (typeof jQuery !== 'undefined' && jQuery(el).hasClass('select2-hidden-accessible')) {
                jQuery(el).select2('destroy');
            }
            if (typeof jQuery !== 'undefined') {
                jQuery(el).select2({
                    theme: 'bootstrap-5',
                    width: '100%'
                });
            }
        }

        function bindStateSelect2Events() {
            if (typeof jQuery !== 'undefined') {
                jQuery(stateSelect).off('select2:select').on('select2:select', function() {
                    citySelect.value = '';
                    loadCities(stateSelect.value);
                });
            }
        }

        function filterStates() {
            const selectedCountry = countrySelect.value;
            const currentState = stateSelect.value;
            
            // Clear current options except first
            while (stateSelect.options.length > 1) {
                stateSelect.remove(1);
            }
            
            if (!selectedCountry) {
                stateSelect.disabled = true;
                citySelect.disabled = true;
                citySelect.value = '';
                updateSelect2(stateSelect);
                bindStateSelect2Events();
                updateSelect2(citySelect);
                noCitiesAlert.style.display = 'none';
                return;
            }

            stateSelect.disabled = false;
            
            // Add matching states
            allStates.forEach(state => {
                if (state.countryId === selectedCountry) {
                    const option = new Option(state.text, state.value, false, state.value === currentState);
                    option.setAttribute('data-country', state.countryId);
                    stateSelect.add(option);
                }
            });
            updateSelect2(stateSelect);
            bindStateSelect2Events();
            
            // If country changed manually and no state selected, disable city
            if (!stateSelect.value) {
                citySelect.disabled = true;
                citySelect.value = '';
                updateSelect2(citySelect);
                noCitiesAlert.style.display = 'none';
            } else {
                // Fetch cities for currently selected state
                loadCities(stateSelect.value, citySelect.value);
            }
        }

        function loadCities(stateId, selectedCityId = null) {
            if (!stateId) {
                citySelect.disabled = true;
                citySelect.value = '';
                while (citySelect.options.length > 1) {
                    citySelect.remove(1);
                }
                updateSelect2(citySelect);
                noCitiesAlert.style.display = 'none';
                return;
            }

            // AJAX fetch cities
            const formData = new FormData();
            formData.append('ajax_action', 'get_cities');
            formData.append('state_id', stateId);
            formData.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);

            fetch(window.location.href, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    while (citySelect.options.length > 1) {
                        citySelect.remove(1);
                    }
                    
                    if (data.cities.length === 0) {
                        citySelect.disabled = true;
                        noCitiesAlert.style.display = 'block';
                    } else {
                        citySelect.disabled = false;
                        noCitiesAlert.style.display = 'none';
                        data.cities.forEach(city => {
                            const option = new Option(city.name, city.id, false, city.id == selectedCityId);
                            citySelect.add(option);
                        });
                    }
                    updateSelect2(citySelect);
                }
            });
        }
        
        // Listen to native change and select2 change for Country
        countrySelect.addEventListener('change', function() {
            stateSelect.value = ''; // clear state
            filterStates();
        });
        if (typeof jQuery !== 'undefined') {
            jQuery(countrySelect).on('select2:select', function() {
                stateSelect.value = '';
                filterStates();
            });
        }
        
        // Listen to native change for State
        stateSelect.addEventListener('change', function() {
            citySelect.value = ''; // clear city
            loadCities(stateSelect.value);
        });
        
        // Initial Select2 binding for state
        bindStateSelect2Events();
        
        // Enable select2 for city
        if (typeof jQuery !== 'undefined') {
            jQuery(citySelect).select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: "Select City..."
            });
        }
        
        // Initial setup
        filterStates();

        // Modal Logic
        const addCityModal = new bootstrap.Modal(document.getElementById('addCityModal'));
        document.querySelectorAll('.openCityModalBtn').forEach(btn => btn.addEventListener('click', function(e) {
            e.preventDefault();
            if (!stateSelect.value) return;
            document.getElementById('modalCountryName').value = countrySelect.options[countrySelect.selectedIndex].text;
            document.getElementById('modalStateName').value = stateSelect.options[stateSelect.selectedIndex].text;
            document.getElementById('modalStateId').value = stateSelect.value;
            document.getElementById('modalCityName').value = '';
            addCityModal.show();
        }));

        document.getElementById('saveCityBtn').addEventListener('click', function() {
            const cityName = document.getElementById('modalCityName').value.trim();
            const stateId = document.getElementById('modalStateId').value;
            
            if (!cityName) return alert("City name is required");
            
            const formData = new FormData();
            formData.append('ajax_action', 'add_city');
            formData.append('state_id', stateId);
            formData.append('city_name', cityName);
            formData.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    addCityModal.hide();
                    loadCities(stateId, data.city.id);
                } else {
                    alert(data.message || 'Error saving city');
                }
            });
        });
    }
});
</script>
<!-- ── JS: Validation + Section Chevron ─────────────────────────────────────── -->
<script>
(function () {
    'use strict';

    const form = document.getElementById('buyerForm');

    // Client-side Bootstrap validation
    form.addEventListener('submit', function (e) {
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
            // Open the first section that has an invalid field
            const firstInvalid = form.querySelector(':invalid');
            if (firstInvalid) {
                const pane = firstInvalid.closest('.collapse');
                if (pane && !pane.classList.contains('show')) {
                    bootstrap.Collapse.getOrCreateInstance(pane).show();
                    setTimeout(function () {
                        firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        firstInvalid.focus();
                    }, 350);
                } else if (firstInvalid) {
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        }
        form.classList.add('was-validated');
    });

    // If server returned errors, open affected sections
    <?php if (!empty($errors)): ?>
    (function () {
        form.classList.add('was-validated');
        const invalids = form.querySelectorAll('.is-invalid');
        invalids.forEach(function (el) {
            const pane = el.closest('.collapse');
            if (pane && !pane.classList.contains('show')) {
                bootstrap.Collapse.getOrCreateInstance(pane).show();
            }
        });
    })();
    <?php endif; ?>

    // Rotate chevron icons on accordion toggle
    document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(function (btn) {
        const targetId = btn.getAttribute('data-bs-target');
        const target   = targetId ? document.querySelector(targetId) : null;
        const icon     = btn.querySelector('.sec-icon');
        if (!target || !icon) return;

        target.addEventListener('show.bs.collapse', function () {
            icon.style.transform = 'rotate(180deg)';
            icon.style.transition = 'transform .3s';
        });
        target.addEventListener('hide.bs.collapse', function () {
            icon.style.transform = 'rotate(0deg)';
        });
        // Set initial state
        if (target.classList.contains('show')) {
            icon.style.transform = 'rotate(180deg)';
        }
    });

    // WhatsApp quick-fill: if mobile is typed and whatsapp is empty, offer to copy
    const mobileInput = document.getElementById('mobile');
    const waInput     = document.querySelector('input[name="whatsapp"]');
    if (mobileInput && waInput) {
        mobileInput.addEventListener('blur', function () {
            if (mobileInput.value && !waInput.value) {
                waInput.value = mobileInput.value;
            }
        });
    }
})();
</script>
