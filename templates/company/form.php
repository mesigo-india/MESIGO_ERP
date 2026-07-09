<?php
$title = $title ?? 'Company';
$action = $action ?? '/company';
$company = $company ?? null;
$address = $address ?? [];
?>
<div class="page-header">
    <h1><?= escapeHtml($title) ?></h1>
    <a href="/company" class="btn btn-outline-secondary">Back</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="post" action="<?= escapeHtml($action) ?>" class="needs-validation" novalidate>
            <?= csrfToken() ?>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="company_name">Company Name</label>
                    <input type="text" id="company_name" name="company_name" class="form-control" value="<?= escapeHtml($company['company_name'] ?? '') ?>" required>
                    <div class="invalid-feedback">Company name is required.</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="contact_person">Contact Person</label>
                    <input type="text" id="contact_person" name="contact_person" class="form-control" value="<?= escapeHtml($company['contact_person'] ?? '') ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?= escapeHtml($company['email'] ?? '') ?>">
                    <div class="invalid-feedback">Enter a valid email address.</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="phone">Phone</label>
                    <input type="text" id="phone" name="phone" class="form-control" value="<?= escapeHtml($company['phone'] ?? '') ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="gst_number">GST Number</label>
                    <input type="text" id="gst_number" name="gst_number" class="form-control" value="<?= escapeHtml($company['gst_number'] ?? '') ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="iec_code">IEC Code</label>
                    <input type="text" id="iec_code" name="iec_code" class="form-control" value="<?= escapeHtml($company['iec_code'] ?? '') ?>">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label" for="address_line1">Address Line 1</label>
                <input type="text" id="address_line1" name="address_line1" class="form-control" value="<?= escapeHtml($address['line1'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label" for="address_line2">Address Line 2</label>
                <input type="text" id="address_line2" name="address_line2" class="form-control" value="<?= escapeHtml($address['line2'] ?? '') ?>">
            </div>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label" for="city">City</label>
                    <input type="text" id="city" name="city" class="form-control" value="<?= escapeHtml($address['city'] ?? '') ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label" for="state">State</label>
                    <input type="text" id="state" name="state" class="form-control" value="<?= escapeHtml($address['state'] ?? '') ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label" for="country">Country</label>
                    <input type="text" id="country" name="country" class="form-control" value="<?= escapeHtml($address['country'] ?? '') ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label" for="zip">ZIP</label>
                    <input type="text" id="zip" name="zip" class="form-control" value="<?= escapeHtml($address['zip'] ?? '') ?>">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label" for="status">Status</label>
                <select id="status" name="status" class="form-select">
                    <option value="1" <?= (int) ($company['status'] ?? 1) === 1 ? 'selected' : '' ?>>Active</option>
                    <option value="0" <?= (int) ($company['status'] ?? 1) === 0 ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Save Company</button>
        </form>
    </div>
</div>