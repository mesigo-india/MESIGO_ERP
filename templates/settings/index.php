<?php $settings = $settings ?? []; ?>
<div class="page-header mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-cog me-2"></i>System Settings</h1>
</div>

<form method="post" action="/settings">
    <?= csrfToken() ?>

    <!-- General Settings -->
    <div class="card mb-4">
        <div class="card-header"><strong>General Settings</strong></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Application Name</label>
                    <input type="text" name="settings[app_name]" class="form-control"
                           value="<?= escapeHtml($settings['general']['app_name'] ?? 'MESIGO ERP') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Default Currency</label>
                    <input type="text" name="settings[default_currency]" class="form-control"
                           value="<?= escapeHtml($settings['general']['default_currency'] ?? 'USD') ?>" placeholder="e.g. USD, INR">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Date Format</label>
                    <select name="settings[date_format]" class="form-select">
                        <?php
                        $formats = ['d/m/Y' => 'DD/MM/YYYY', 'm/d/Y' => 'MM/DD/YYYY', 'Y-m-d' => 'YYYY-MM-DD', 'd-m-Y' => 'DD-MM-YYYY'];
                        $current = $settings['general']['date_format'] ?? 'd/m/Y';
                        foreach ($formats as $val => $label): ?>
                            <option value="<?= escapeHtml($val) ?>" <?= $current === $val ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Timezone</label>
                    <input type="text" name="settings[timezone]" class="form-control"
                           value="<?= escapeHtml($settings['general']['timezone'] ?? 'Asia/Kolkata') ?>" placeholder="e.g. Asia/Kolkata">
                </div>
            </div>
        </div>
    </div>

    <!-- Document Settings -->
    <div class="card mb-4">
        <div class="card-header"><strong>Document Numbering</strong></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Quotation Prefix</label>
                    <input type="text" name="settings[quotation_prefix]" class="form-control"
                           value="<?= escapeHtml($settings['general']['quotation_prefix'] ?? 'QT') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Proforma Invoice Prefix</label>
                    <input type="text" name="settings[pi_prefix]" class="form-control"
                           value="<?= escapeHtml($settings['general']['pi_prefix'] ?? 'PI') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Commercial Invoice Prefix</label>
                    <input type="text" name="settings[ci_prefix]" class="form-control"
                           value="<?= escapeHtml($settings['general']['ci_prefix'] ?? 'CI') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Packing List Prefix</label>
                    <input type="text" name="settings[pl_prefix]" class="form-control"
                           value="<?= escapeHtml($settings['general']['pl_prefix'] ?? 'PL') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Shipping Bill Prefix</label>
                    <input type="text" name="settings[sb_prefix]" class="form-control"
                           value="<?= escapeHtml($settings['general']['sb_prefix'] ?? 'SB') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Bill of Lading Prefix</label>
                    <input type="text" name="settings[bol_prefix]" class="form-control"
                           value="<?= escapeHtml($settings['general']['bol_prefix'] ?? 'BL') ?>">
                </div>
            </div>
        </div>
    </div>

    <!-- Save -->
    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Settings</button>
        <a href="/dashboard" class="btn btn-secondary">Cancel</a>
    </div>
</form>
