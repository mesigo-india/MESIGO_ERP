<?php
/**
 * MESIGO ERP - Reusable Document Print Header Component
 */
use App\Core\Database;

$dbI = Database::getInstance();
$company = $dbI->query("SELECT * FROM company WHERE status = 1 ORDER BY id ASC LIMIT 1")->fetch(PDO::FETCH_ASSOC) ?: [];

$logoPath = !empty($company['logo_path']) ? '/uploads/' . $company['logo_path'] : null;
$letterheadType = $company['letterhead_type'] ?? 'plain';
$defaultLh = !empty($company['letterhead_path']) ? '/uploads/' . $company['letterhead_path'] : null;
?>
<style>
@media print {
    #printContainer.use-letterhead {
        padding-top: <?= (int)($company['print_margin_top'] ?? 45) ?>mm !important;
        padding-bottom: <?= (int)($company['print_margin_bottom'] ?? 35) ?>mm !important;
        padding-left: <?= (int)($company['print_margin_left'] ?? 20) ?>mm !important;
        padding-right: <?= (int)($company['print_margin_right'] ?? 20) ?>mm !important;
    }
}
</style>
<!-- Print Overlay Background -->
<?php if ($defaultLh): ?>
    <img src="<?= htmlspecialchars($defaultLh) ?>" class="letterhead-overlay-img" id="letterheadOverlayImg">
<?php endif; ?>

<!-- Plain Paper Company Branding Block -->
<div class="company-header-block mb-4 border-bottom pb-3">
    <div class="row align-items-center">
        <div class="col-sm-6 text-center text-sm-start mb-3 mb-sm-0">
            <?php if ($logoPath): ?>
                <img src="<?= htmlspecialchars($logoPath) ?>" alt="Company Logo" class="img-fluid" style="max-height: 70px; width: auto;" onerror="this.style.display='none';">
            <?php else: ?>
                <h3 class="fw-bold text-primary mb-0"><?= htmlspecialchars($company['company_name'] ?? 'MESIGO PRIVATE LIMITED') ?></h3>
            <?php endif; ?>
        </div>
        <div class="col-sm-6 text-center text-sm-end small text-muted">
            <span class="d-block fw-bold text-dark h5 mb-1"><?= htmlspecialchars($company['company_name'] ?? '') ?></span>
            <span class="d-block"><?= htmlspecialchars($company['address_line1'] ?? '') ?>, <?= htmlspecialchars($company['address_line2'] ?? '') ?></span>
            <span class="d-block"><?= htmlspecialchars($company['city'] ?? '') ?>, <?= htmlspecialchars($company['state'] ?? '') ?> - <?= htmlspecialchars($company['zip'] ?? '') ?></span>
            <span class="d-block"><i class="fas fa-envelope me-1"></i> <?= htmlspecialchars($company['email'] ?? '') ?> | <i class="fas fa-phone me-1"></i> <?= htmlspecialchars($company['phone'] ?? '') ?></span>
            <?php if (!empty($company['website'])): ?>
                <span class="d-block"><i class="fas fa-globe me-1"></i> <?= htmlspecialchars($company['website'] ?? '') ?></span>
            <?php endif; ?>
        </div>
    </div>
    <div class="row mt-2 small text-muted">
        <div class="col-sm-6">
            <?php if (!empty($company['gst_number'])): ?>
                <span class="me-3"><strong>GST:</strong> <?= htmlspecialchars($company['gst_number']) ?></span>
            <?php endif; ?>
            <?php if (!empty($company['iec_code'])): ?>
                <span><strong>IEC:</strong> <?= htmlspecialchars($company['iec_code']) ?></span>
            <?php endif; ?>
        </div>
        <div class="col-sm-6 text-sm-end">
            <?php if (!empty($company['pan_number'])): ?>
                <span class="me-3"><strong>PAN:</strong> <?= htmlspecialchars($company['pan_number']) ?></span>
            <?php endif; ?>
            <?php if (!empty($company['cin_number'])): ?>
                <span><strong>CIN:</strong> <?= htmlspecialchars($company['cin_number']) ?></span>
            <?php endif; ?>
        </div>
    </div>
</div>
