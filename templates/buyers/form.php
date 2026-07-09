<?php
$title = $title ?? 'Buyer';
$action = $action ?? '/buyers';
$buyer = $buyer ?? null;
$profile = $profile ?? [];
$contacts = $contacts ?? [[]];
$addresses = $addresses ?? [[]];
?>
<div class="page-header">
    <h1><?= escapeHtml($title) ?></h1>
    <a href="/buyers" class="btn btn-outline-secondary">Back</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="post" action="<?= escapeHtml($action) ?>" class="needs-validation" novalidate>
            <?= csrfToken() ?>

            <h5 class="mb-3">Buyer Details</h5>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label" for="buyer_code">Buyer Code</label>
                    <input type="text" id="buyer_code" name="buyer_code" class="form-control" value="<?= escapeHtml($buyer['buyer_code'] ?? '') ?>" required>
                    <div class="invalid-feedback">Buyer code is required.</div>
                </div>
                <div class="col-md-8 mb-3">
                    <label class="form-label" for="company_name">Company Name</label>
                    <input type="text" id="company_name" name="company_name" class="form-control" value="<?= escapeHtml($buyer['company_name'] ?? '') ?>" required>
                    <div class="invalid-feedback">Company name is required.</div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label" for="contact_person">Contact Person</label>
                    <input type="text" id="contact_person" name="contact_person" class="form-control" value="<?= escapeHtml($buyer['contact_person'] ?? '') ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?= escapeHtml($buyer['email'] ?? '') ?>">
                    <div class="invalid-feedback">Enter a valid email address.</div>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label" for="phone">Phone</label>
                    <input type="text" id="phone" name="phone" class="form-control" value="<?= escapeHtml($buyer['phone'] ?? '') ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label" for="gst_number">GST Number</label>
                    <input type="text" id="gst_number" name="gst_number" class="form-control" value="<?= escapeHtml($buyer['gst_number'] ?? '') ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label" for="iec_number">IEC Number</label>
                    <input type="text" id="iec_number" name="iec_number" class="form-control" value="<?= escapeHtml($buyer['iec_number'] ?? '') ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label" for="status">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="1" <?= (int) ($buyer['status'] ?? 1) === 1 ? 'selected' : '' ?>>Active</option>
                        <option value="0" <?= (int) ($buyer['status'] ?? 1) === 0 ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
            </div>

            <h5 class="mt-4 mb-3">Buyer Address</h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="billing_address">Billing Address</label>
                    <textarea id="billing_address" name="billing_address" class="form-control" rows="3"><?= escapeHtml($profile['billing_address'] ?? '') ?></textarea>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="shipping_address">Shipping Address</label>
                    <textarea id="shipping_address" name="shipping_address" class="form-control" rows="3"><?= escapeHtml($profile['shipping_address'] ?? '') ?></textarea>
                </div>
            </div>

            <h5 class="mt-4 mb-3">Buyer Contacts</h5>
            <?php for ($i = 0; $i < 3; $i++): $contact = $contacts[$i] ?? []; ?>
                <div class="row border rounded p-2 mb-2">
                    <div class="col-md-3 mb-2">
                        <label class="form-label">Name</label>
                        <input type="text" name="contact_name[]" class="form-control" value="<?= escapeHtml($contact['name'] ?? '') ?>">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="form-label">Designation</label>
                        <input type="text" name="contact_designation[]" class="form-control" value="<?= escapeHtml($contact['designation'] ?? '') ?>">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label">Email</label>
                        <input type="email" name="contact_email[]" class="form-control" value="<?= escapeHtml($contact['email'] ?? '') ?>">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="form-label">Phone</label>
                        <input type="text" name="contact_phone[]" class="form-control" value="<?= escapeHtml($contact['phone'] ?? '') ?>">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="form-label">Mobile</label>
                        <input type="text" name="contact_mobile[]" class="form-control" value="<?= escapeHtml($contact['mobile'] ?? '') ?>">
                        <input type="hidden" name="contact_primary[]" value="<?= $i === 0 ? 1 : 0 ?>">
                    </div>
                </div>
            <?php endfor; ?>

            <h5 class="mt-4 mb-3">Address Book</h5>
            <?php for ($i = 0; $i < 2; $i++): $address = $addresses[$i] ?? []; ?>
                <div class="row border rounded p-2 mb-2">
                    <div class="col-md-2 mb-2">
                        <label class="form-label">Type</label>
                        <select name="address_type[]" class="form-select">
                            <option value="billing" <?= ($address['address_type'] ?? 'billing') === 'billing' ? 'selected' : '' ?>>Billing</option>
                            <option value="shipping" <?= ($address['address_type'] ?? '') === 'shipping' ? 'selected' : '' ?>>Shipping</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Address</label>
                        <input type="text" name="address_text[]" class="form-control" value="<?= escapeHtml($address['address'] ?? '') ?>">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="form-label">ZIP</label>
                        <input type="text" name="address_zip[]" class="form-control" value="<?= escapeHtml($address['zip'] ?? '') ?>">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="form-label">Default</label>
                        <select name="address_default[]" class="form-select">
                            <option value="0" <?= (int) ($address['is_default'] ?? 0) === 0 ? 'selected' : '' ?>>No</option>
                            <option value="1" <?= (int) ($address['is_default'] ?? 0) === 1 ? 'selected' : '' ?>>Yes</option>
                        </select>
                    </div>
                    <input type="hidden" name="address_country_id[]" value="<?= (int) ($address['country_id'] ?? 0) ?>">
                    <input type="hidden" name="address_state_id[]" value="<?= (int) ($address['state_id'] ?? 0) ?>">
                    <input type="hidden" name="address_city_id[]" value="<?= (int) ($address['city_id'] ?? 0) ?>">
                </div>
            <?php endfor; ?>

            <h5 class="mt-4 mb-3">Banking Details</h5>
            <div class="row">
                <div class="col-md-3 mb-3"><input type="text" name="bank_name" class="form-control" placeholder="Bank Name" value="<?= escapeHtml($profile['bank_name'] ?? '') ?>"></div>
                <div class="col-md-3 mb-3"><input type="text" name="bank_account_name" class="form-control" placeholder="Account Name" value="<?= escapeHtml($profile['bank_account_name'] ?? '') ?>"></div>
                <div class="col-md-3 mb-3"><input type="text" name="bank_account_number" class="form-control" placeholder="Account Number" value="<?= escapeHtml($profile['bank_account_number'] ?? '') ?>"></div>
                <div class="col-md-3 mb-3"><input type="text" name="bank_swift_code" class="form-control" placeholder="SWIFT / IFSC" value="<?= escapeHtml($profile['bank_swift_code'] ?? '') ?>"></div>
            </div>

            <h5 class="mt-4 mb-3">Payment Terms & Shipping Preferences</h5>
            <div class="row">
                <div class="col-md-3 mb-3"><input type="text" name="payment_terms" class="form-control" placeholder="Payment Terms" value="<?= escapeHtml($profile['payment_terms'] ?? '') ?>"></div>
                <div class="col-md-3 mb-3"><input type="text" name="credit_days" class="form-control" placeholder="Credit Days" value="<?= escapeHtml($profile['credit_days'] ?? '') ?>"></div>
                <div class="col-md-3 mb-3"><input type="text" name="preferred_shipping_mode" class="form-control" placeholder="Shipping Mode" value="<?= escapeHtml($profile['preferred_shipping_mode'] ?? '') ?>"></div>
                <div class="col-md-3 mb-3"><input type="text" name="preferred_port" class="form-control" placeholder="Preferred Port" value="<?= escapeHtml($profile['preferred_port'] ?? '') ?>"></div>
            </div>
            <div class="mb-3">
                <label class="form-label" for="shipping_marks">Shipping Marks</label>
                <textarea id="shipping_marks" name="shipping_marks" class="form-control" rows="2"><?= escapeHtml($profile['shipping_marks'] ?? '') ?></textarea>
            </div>

            <input type="hidden" name="country_id" value="<?= (int) ($buyer['country_id'] ?? 0) ?>">
            <input type="hidden" name="state_id" value="<?= (int) ($buyer['state_id'] ?? 0) ?>">
            <input type="hidden" name="city_id" value="<?= (int) ($buyer['city_id'] ?? 0) ?>">

            <button type="submit" class="btn btn-primary">Save Buyer</button>
        </form>
    </div>
</div>