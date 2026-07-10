<?php
$title = $title ?? 'Company';
$action = $action ?? '/company';
$company = $company ?? null;
$address = $address ?? [];
?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-0 fw-bold"><i class="fas fa-building text-primary me-2"></i><?= escapeHtml($title) ?></h4>
        <p class="text-muted small mb-0">Configure enterprise corporate identities, sourcing certificates, banking details, official seals, and custom letterheads.</p>
    </div>
    <a href="/company" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Back</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form method="post" action="<?= escapeHtml($action) ?>" enctype="multipart/form-data" class="needs-validation" novalidate>
            <?= csrfToken() ?>
            
            <!-- Section 1: General Info -->
            <div class="mb-4">
                <h5 class="fw-bold border-bottom pb-2 text-primary"><i class="fas fa-info-circle me-1"></i> General Information</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="company_name">Company Name *</label>
                        <input type="text" id="company_name" name="company_name" class="form-control" value="<?= escapeHtml($company['company_name'] ?? '') ?>" required>
                        <div class="invalid-feedback">Company name is required.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="contact_person">Contact Person</label>
                        <input type="text" id="contact_person" name="contact_person" class="form-control" value="<?= escapeHtml($company['contact_person'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?= escapeHtml($company['email'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="phone">Phone Number</label>
                        <input type="text" id="phone" name="phone" class="form-control" value="<?= escapeHtml($company['phone'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="website">Website Link</label>
                        <input type="url" id="website" name="website" class="form-control" placeholder="https://..." value="<?= escapeHtml($company['website'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <!-- Section 2: Addresses -->
            <div class="mb-4">
                <h5 class="fw-bold border-bottom pb-2 text-primary"><i class="fas fa-map-marker-alt me-1"></i> Registered Address</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="address_line1">Address Line 1</label>
                        <input type="text" id="address_line1" name="address_line1" class="form-control" value="<?= escapeHtml($address['line1'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="address_line2">Address Line 2</label>
                        <input type="text" id="address_line2" name="address_line2" class="form-control" value="<?= escapeHtml($address['line2'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="city">City</label>
                        <input type="text" id="city" name="city" class="form-control" value="<?= escapeHtml($address['city'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="state">State</label>
                        <input type="text" id="state" name="state" class="form-control" value="<?= escapeHtml($address['state'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="country">Country</label>
                        <input type="text" id="country" name="country" class="form-control" value="<?= escapeHtml($address['country'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="zip">ZIP/Pin Code</label>
                        <input type="text" id="zip" name="zip" class="form-control" value="<?= escapeHtml($address['zip'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <!-- Section 3: Tax & Certifications -->
            <div class="mb-4">
                <h5 class="fw-bold border-bottom pb-2 text-primary"><i class="fas fa-award me-1"></i> Registrations & Sourcing Certifications</h5>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label" for="gst_number">GST IN</label>
                        <input type="text" id="gst_number" name="gst_number" class="form-control" value="<?= escapeHtml($company['gst_number'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="iec_code">IEC Code (Import Export Code)</label>
                        <input type="text" id="iec_code" name="iec_code" class="form-control" value="<?= escapeHtml($company['iec_code'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="pan_number">PAN Number</label>
                        <input type="text" id="pan_number" name="pan_number" class="form-control" value="<?= escapeHtml($company['pan_number'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="cin_number">CIN (Corporate Identification Number)</label>
                        <input type="text" id="cin_number" name="cin_number" class="form-control" value="<?= escapeHtml($company['cin_number'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="apeda_number">APEDA Registration No</label>
                        <input type="text" id="apeda_number" name="apeda_number" class="form-control" value="<?= escapeHtml($company['apeda_number'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="fssai_number">FSSAI License No</label>
                        <input type="text" id="fssai_number" name="fssai_number" class="form-control" value="<?= escapeHtml($company['fssai_number'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="iso_number">ISO Certificate No</label>
                        <input type="text" id="iso_number" name="iso_number" class="form-control" value="<?= escapeHtml($company['iso_number'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="haccp_number">HACCP Certificate No</label>
                        <input type="text" id="haccp_number" name="haccp_number" class="form-control" value="<?= escapeHtml($company['haccp_number'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <!-- Section 4: Banking Information -->
            <div class="mb-4">
                <h5 class="fw-bold border-bottom pb-2 text-primary"><i class="fas fa-university me-1"></i> Default Export Bank Details</h5>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label" for="bank_name">Bank Name</label>
                        <input type="text" id="bank_name" name="bank_name" class="form-control" value="<?= escapeHtml($company['bank_name'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="account_name">Account Beneficiary Name</label>
                        <input type="text" id="account_name" name="account_name" class="form-control" value="<?= escapeHtml($company['account_name'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="account_number">Account Number</label>
                        <input type="text" id="account_number" name="account_number" class="form-control" value="<?= escapeHtml($company['account_number'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="ifsc_code">IFSC Code (Local)</label>
                        <input type="text" id="ifsc_code" name="ifsc_code" class="form-control" value="<?= escapeHtml($company['ifsc_code'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="swift_code">SWIFT / BIC Code (International)</label>
                        <input type="text" id="swift_code" name="swift_code" class="form-control" value="<?= escapeHtml($company['swift_code'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <!-- Section 5: Brand Identity Uploads -->
            <div class="mb-4">
                <h5 class="fw-bold border-bottom pb-2 text-primary"><i class="fas fa-file-image me-1"></i> Signature, Logo & Stamps</h5>
                <div class="row g-3">
                    <!-- Logo -->
                    <div class="col-md-4">
                        <label class="form-label" for="logo_file">Company Logo (Light Background)</label>
                        <input type="file" id="logo_file" name="logo_file" class="form-control" accept="image/*">
                        <?php if (!empty($company['logo_path'])): ?>
                            <div class="mt-2"><img src="/uploads/<?= htmlspecialchars($company['logo_path']) ?>" class="img-thumbnail" style="max-height: 50px;"></div>
                        <?php endif; ?>
                    </div>
                    <!-- Stamp -->
                    <div class="col-md-4">
                        <label class="form-label" for="stamp_file">Company Stamp Image (PNG Transparent)</label>
                        <input type="file" id="stamp_file" name="stamp_file" class="form-control" accept="image/png">
                        <?php if (!empty($company['stamp_path'])): ?>
                            <div class="mt-2"><img src="/uploads/<?= htmlspecialchars($company['stamp_path']) ?>" class="img-thumbnail" style="max-height: 50px;"></div>
                        <?php endif; ?>
                    </div>
                    <!-- Seal -->
                    <div class="col-md-4">
                        <label class="form-label" for="seal_file">Company Seal Image (PNG Transparent)</label>
                        <input type="file" id="seal_file" name="seal_file" class="form-control" accept="image/png">
                        <?php if (!empty($company['seal_path'])): ?>
                            <div class="mt-2"><img src="/uploads/<?= htmlspecialchars($company['seal_path']) ?>" class="img-thumbnail" style="max-height: 50px;"></div>
                        <?php endif; ?>
                    </div>
                    <!-- Captured Signature -->
                    <div class="col-md-6">
                        <label class="form-label" for="signature_file">Captured Written Signature Image</label>
                        <input type="file" id="signature_file" name="signature_file" class="form-control" accept="image/*">
                        <?php if (!empty($company['signature_path'])): ?>
                            <div class="mt-2"><img src="/uploads/<?= htmlspecialchars($company['signature_path']) ?>" class="img-thumbnail" style="max-height: 50px;"></div>
                        <?php endif; ?>
                    </div>
                    <!-- Digital Signature -->
                    <div class="col-md-6">
                        <label class="form-label" for="digital_signature_file">Cryptographic / Digital Signature Image</label>
                        <input type="file" id="digital_signature_file" name="digital_signature_file" class="form-control" accept="image/*">
                        <?php if (!empty($company['digital_signature_path'])): ?>
                            <div class="mt-2"><img src="/uploads/<?= htmlspecialchars($company['digital_signature_path']) ?>" class="img-thumbnail" style="max-height: 50px;"></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Section 6: Letterheads Configuration -->
            <div class="mb-4">
                <h5 class="fw-bold border-bottom pb-2 text-primary"><i class="fas fa-print me-1"></i> Letterhead Configuration</h5>
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label" for="letterhead_type">Default Print Mode</label>
                        <select id="letterhead_type" name="letterhead_type" class="form-select" style="max-width: 300px;">
                            <option value="plain" <?= ($company['letterhead_type'] ?? 'plain') === 'plain' ? 'selected' : '' ?>>Plain Paper (Structured ERP Layout)</option>
                            <option value="image" <?= ($company['letterhead_type'] ?? '') === 'image' ? 'selected' : '' ?>>Company Letterhead Image Background</option>
                        </select>
                    </div>
                    <!-- Base Letterhead -->
                    <div class="col-md-4">
                        <label class="form-label" for="letterhead_file">Global / Base Letterhead Image (A4 dimensions)</label>
                        <input type="file" id="letterhead_file" name="letterhead_file" class="form-control" accept="image/*">
                        <?php if (!empty($company['letterhead_path'])): ?>
                            <div class="mt-2"><a href="/uploads/<?= htmlspecialchars($company['letterhead_path']) ?>" target="_blank" class="btn btn-xs btn-outline-secondary"><i class="fas fa-external-link-alt"></i> View current</a></div>
                        <?php endif; ?>
                    </div>
                    <!-- Export Letterhead -->
                    <div class="col-md-4">
                        <label class="form-label" for="letterhead_export_file">Export-specific Letterhead</label>
                        <input type="file" id="letterhead_export_file" name="letterhead_export_file" class="form-control" accept="image/*">
                        <?php if (!empty($company['letterhead_export_path'])): ?>
                            <div class="mt-2"><a href="/uploads/<?= htmlspecialchars($company['letterhead_export_path']) ?>" target="_blank" class="btn btn-xs btn-outline-secondary"><i class="fas fa-external-link-alt"></i> View current</a></div>
                        <?php endif; ?>
                    </div>
                    <!-- Domestic Letterhead -->
                    <div class="col-md-4">
                        <label class="form-label" for="letterhead_domestic_file">Domestic-specific Letterhead</label>
                        <input type="file" id="letterhead_domestic_file" name="letterhead_domestic_file" class="form-control" accept="image/*">
                        <?php if (!empty($company['letterhead_domestic_path'])): ?>
                            <div class="mt-2"><a href="/uploads/<?= htmlspecialchars($company['letterhead_domestic_path']) ?>" target="_blank" class="btn btn-xs btn-outline-secondary"><i class="fas fa-external-link-alt"></i> View current</a></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Section 7: Declaration / Footer Terms -->
            <div class="mb-4">
                <h5 class="fw-bold border-bottom pb-2 text-primary"><i class="fas fa-file-contract me-1"></i> Document Footers & Declarations</h5>
                <div class="mb-3">
                    <label class="form-label" for="declaration">Terms Declaration (Printed automatically on Invoices / Quotations)</label>
                    <textarea id="declaration" name="declaration" class="form-control" rows="4" placeholder="Enter standard declaration or notes..."><?= escapeHtml($company['declaration'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Status and Save -->
            <div class="row g-3 align-items-center border-top pt-3">
                <div class="col-md-3">
                    <label class="form-label" for="status">Operational Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="1" <?= (int) ($company['status'] ?? 1) === 1 ? 'selected' : '' ?>>Active</option>
                        <option value="0" <?= (int) ($company['status'] ?? 1) === 0 ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <div class="col-md-9 text-end">
                    <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-1"></i> Save Company Profile</button>
                </div>
            </div>
        </form>
    </div>
</div>