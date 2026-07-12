<?php
$action = $action ?? '/proforma-invoices';
$title = $title ?? 'Proforma Invoice';
$invoice = $invoice ?? null;
$meta = $meta ?? [];
$items = $items ?? [[]];
$companies = $companies ?? [];
$warehouses = $warehouses ?? [];
$costTemplates = $costTemplates ?? [];
$costComponents = $costComponents ?? [];
$buyers = $buyers ?? [];
$buyerContacts = $buyerContacts ?? [];
$currencies = $currencies ?? [];
$incoterms = $incoterms ?? [];
$paymentTerms = $paymentTerms ?? [];
$ports = $ports ?? [];
$products = $products ?? [];
$grades = $grades ?? [];
$origins = $origins ?? [];
$packingTypes = $packingTypes ?? [];
$units = $units ?? [];
$statuses = $statuses ?? [];

$dbInst = App\Core\Database::getInstance();
$companyBanks = $dbInst->query("SELECT * FROM company_banks WHERE status = 1 ORDER BY is_default DESC")->fetchAll(\PDO::FETCH_ASSOC);
?>
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 text-primary font-weight-bold mb-0"><i class="fas fa-file-invoice text-primary me-2"></i><?= escapeHtml($title) ?></h1>
    <a href="/proforma-invoices" class="btn btn-outline-secondary">Back to List</a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        <form method="post" action="<?= escapeHtml($action) ?>" class="needs-validation" id="piForm" novalidate>
            <?= csrfToken() ?>

            <!-- Segment 1: Organization Context -->
            <div class="bg-light p-3 rounded mb-4">
                <h5 class="text-secondary font-weight-bold mb-3 border-bottom pb-2">1. Organization Context & Header</h5>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label font-weight-bold" for="company_id">Company Entity</label>
                        <select id="company_id" name="company_id" class="form-select select2-init" required>
                            <?php foreach ($companies as $comp): ?>
                                <option value="<?= (int) $comp['id'] ?>" <?= (int) ($invoice['company_id'] ?? $_SESSION['active_company_id'] ?? 1) === (int) $comp['id'] ? 'selected' : '' ?>><?= escapeHtml($comp['company_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label font-weight-bold" for="exchange_rate">Exchange Rate (vs INR Base)</label>
                        <input type="number" step="0.000001" id="exchange_rate" name="exchange_rate" class="form-control" value="<?= (float) ($invoice['exchange_rate'] ?? 1.0) ?>" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label font-weight-bold" for="rate_locked">Currency Rate Lock</label>
                        <select id="rate_locked" name="rate_locked" class="form-select select2-init">
                            <option value="0" <?= (int) ($invoice['rate_locked'] ?? 0) === 0 ? 'selected' : '' ?>>No (Floating Daily Rate)</option>
                            <option value="1" <?= (int) ($invoice['rate_locked'] ?? 0) === 1 ? 'selected' : '' ?>>Yes (Locked Negotiated Rate)</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label font-weight-bold" for="document_date">PI Date</label>
                        <input type="date" id="document_date" name="document_date" class="form-control" value="<?= escapeHtml($invoice['document_date'] ?? date('Y-m-d')) ?>" required>
                    </div>
                </div>
            </div>

            <!-- Segment 2: Proforma Invoice Details -->
            <div class="bg-light p-3 rounded mb-4">
                <h5 class="text-secondary font-weight-bold mb-3 border-bottom pb-2">2. Commercial Details & PO</h5>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label font-weight-bold">PI Number</label>
                        <input type="text" class="form-control bg-white text-muted" value="<?= escapeHtml($invoice['document_number'] ?? 'Generated on Save') ?>" readonly>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label font-weight-bold" for="buyer_id">Buyer (Client)</label>
                        <select id="buyer_id" name="buyer_id" class="form-select select2-init" required>
                            <option value="0">Select Buyer</option>
                            <?php foreach ($buyers as $b): ?>
                                <option value="<?= (int) $b['id'] ?>" <?= (int) ($invoice['buyer_id'] ?? 0) === (int) $b['id'] ? 'selected' : '' ?>><?= escapeHtml($b['company_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label font-weight-bold" for="currency_id">Currency</label>
                        <select id="currency_id" name="currency_id" class="form-select select2-init" required>
                            <?php foreach ($currencies as $curr): ?>
                                <option value="<?= (int) $curr['id'] ?>" <?= (int) ($invoice['currency_id'] ?? 1) === (int) $curr['id'] ? 'selected' : '' ?>><?= escapeHtml($curr['code']) ?> (<?= escapeHtml($curr['symbol']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label font-weight-bold" for="buyer_po">Buyer PO Number</label>
                        <input type="text" id="buyer_po" name="buyer_po" class="form-control" value="<?= escapeHtml($meta['buyer_po'] ?? '') ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label" for="incoterm_id">Incoterm</label>
                        <select id="incoterm_id" name="incoterm_id" class="form-select select2-init">
                            <?php foreach ($incoterms as $inc): ?>
                                <option value="<?= (int) $inc['id'] ?>" <?= (int) ($invoice['incoterm_id'] ?? 0) === (int) $inc['id'] ? 'selected' : '' ?>><?= escapeHtml($inc['code']) ?> - <?= escapeHtml($inc['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label" for="loading_port_id">Port of Loading</label>
                        <select id="loading_port_id" name="loading_port_id" class="form-select select2-init">
                            <option value="0">Select Port</option>
                            <?php foreach ($ports as $port): ?>
                                <option value="<?= (int) $port['id'] ?>" <?= (int) ($invoice['loading_port_id'] ?? 0) === (int) $port['id'] ? 'selected' : '' ?>><?= escapeHtml($port['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label" for="delivery_port_id">Port of Destination</label>
                        <select id="delivery_port_id" name="delivery_port_id" class="form-select select2-init">
                            <option value="0">Select Port</option>
                            <?php foreach ($ports as $port): ?>
                                <option value="<?= (int) $port['id'] ?>" <?= (int) ($invoice['delivery_port_id'] ?? 0) === (int) $port['id'] ? 'selected' : '' ?>><?= escapeHtml($port['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label" for="container_type">Container Type</label>
                        <select id="container_type" name="container_type" class="form-select">
                            <option value="20FT" <?= ($meta['container_type'] ?? '20FT') === '20FT' ? 'selected' : '' ?>>20FT Dry Cargo</option>
                            <option value="40FT" <?= ($meta['container_type'] ?? '') === '40FT' ? 'selected' : '' ?>>40FT Dry Cargo</option>
                            <option value="40HC" <?= ($meta['container_type'] ?? '') === '40HC' ? 'selected' : '' ?>>40FT High Cube</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="consignee">Consignee Address Details</label>
                        <textarea id="consignee" name="consignee" class="form-control" rows="3" placeholder="Name, Address, Registration No of Consignee"><?= escapeHtml($meta['consignee'] ?? '') ?></textarea>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="notify_party">Notify Party Details</label>
                        <textarea id="notify_party" name="notify_party" class="form-control" rows="3" placeholder="Same as Consignee or customs broker details"><?= escapeHtml($meta['notify_party'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Segment 3: Exporter Bank & Payment Schedule -->
            <div class="bg-light p-3 rounded mb-4 border border-info">
                <h5 class="text-info font-weight-bold mb-3 border-bottom pb-2"><i class="fas fa-university me-2"></i>3. Exporter Banking & Payment Schedule</h5>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label font-weight-bold text-dark" for="company_bank_id">Select Company Bank Account</label>
                        <select id="company_bank_id" name="company_bank_id" class="form-select" onchange="autoFillBankDetails(this)">
                            <option value="">-- Choose Preset Bank Account --</option>
                            <?php foreach ($companyBanks as $bank): ?>
                                <option value="<?= $bank['id'] ?>" 
                                    data-beneficiary="<?= escapeHtml($bank['beneficiary_name']) ?>"
                                    data-bank_name="<?= escapeHtml($bank['bank_name']) ?>"
                                    data-account="<?= escapeHtml($bank['account_number']) ?>"
                                    data-swift="<?= escapeHtml($bank['swift_code']) ?>"
                                    data-iban="<?= escapeHtml($bank['iban'] ?? '') ?>"
                                    <?= ((int)($meta['company_bank_id'] ?? 0) === (int)$bank['id']) ? 'selected' : '' ?>>
                                    <?= escapeHtml($bank['bank_name']) ?> (<?= escapeHtml($bank['account_number']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="beneficiary_name">Beneficiary Name</label>
                        <input type="text" id="beneficiary_name" name="beneficiary_name" class="form-control" value="<?= escapeHtml($meta['beneficiary_name'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="bank_name">Bank Name</label>
                        <input type="text" id="bank_name" name="bank_name" class="form-control" value="<?= escapeHtml($meta['bank_name'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="account_number">Account Number</label>
                        <input type="text" id="account_number" name="account_number" class="form-control" value="<?= escapeHtml($meta['account_number'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="swift_code">SWIFT Code</label>
                        <input type="text" id="swift_code" name="swift_code" class="form-control" value="<?= escapeHtml($meta['swift_code'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="iban">IBAN (if applicable)</label>
                        <input type="text" id="iban" name="iban" class="form-control" value="<?= escapeHtml($meta['iban'] ?? '') ?>">
                    </div>
                </div>

                <div class="row border-top pt-3 mt-2">
                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" for="advance_percent">Advance Payment (%)</label>
                        <input type="number" step="0.01" min="0" max="100" id="advance_percent" name="advance_percent" class="form-control" value="<?= (float)($meta['advance_percent'] ?? 30.0) ?>" oninput="syncPaymentSchedule(this, 'balance_percent')">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" for="balance_percent">Balance Against Document Dispatch (%)</label>
                        <input type="number" step="0.01" min="0" max="100" id="balance_percent" name="balance_percent" class="form-control" value="<?= (float)($meta['balance_percent'] ?? 70.0) ?>" oninput="syncPaymentSchedule(this, 'advance_percent')">
                    </div>
                    <div class="col-12">
                        <div id="payment-schedule-alert" class="alert alert-danger d-none p-2 mb-0 small">Payment schedule sum must equal 100%!</div>
                    </div>
                </div>
            </div>

            <!-- Segment 4: Product Grid -->
            <?php require APP_ROOT . '/templates/export_documents/product_grid.php'; ?>

            <!-- Notes & Remarks -->
            <div class="row mb-4 mt-4">
                <div class="col-12">
                    <label class="form-label font-weight-bold" for="remarks">Special Instructions / Remarks</label>
                    <textarea id="remarks" name="remarks" class="form-control" rows="3"><?= escapeHtml($invoice['remarks'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Action Controls -->
            <div class="d-flex justify-content-end gap-2 border-top pt-4">
                <a href="/proforma-invoices" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i> Save Proforma Invoice</button>
            </div>
        </form>
    </div>
</div>

<script>
function autoFillBankDetails(select) {
    const option = select.options[select.selectedIndex];
    if (!option.value) return;
    document.getElementById('beneficiary_name').value = option.getAttribute('data-beneficiary') || '';
    document.getElementById('bank_name').value = option.getAttribute('data-bank_name') || '';
    document.getElementById('account_number').value = option.getAttribute('data-account') || '';
    document.getElementById('swift_code').value = option.getAttribute('data-swift') || '';
    document.getElementById('iban').value = option.getAttribute('data-iban') || '';
}

function syncPaymentSchedule(input, partnerId) {
    const val = parseFloat(input.value) || 0.0;
    const partner = document.getElementById(partnerId);
    partner.value = (100.0 - val).toFixed(2);
    validateScheduleSum();
}

function validateScheduleSum() {
    const adv = parseFloat(document.getElementById('advance_percent').value) || 0.0;
    const bal = parseFloat(document.getElementById('balance_percent').value) || 0.0;
    const alertBox = document.getElementById('payment-schedule-alert');
    if (Math.abs(adv + bal - 100.0) > 0.01) {
        alertBox.classList.remove('d-none');
        return false;
    } else {
        alertBox.classList.add('d-none');
        return true;
    }
}

document.getElementById('piForm').addEventListener('submit', function(e) {
    if (!validateScheduleSum()) {
        e.preventDefault();
        alert('Invalid payment schedule schedule sum must equal 100%!');
        return false;
    }
});
</script>