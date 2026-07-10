<?php
/**
 * MESIGO ERP - Reusable Document Print Footer Component
 */
use App\Core\Database;

$dbF = Database::getInstance();
$company = $dbF->query("SELECT * FROM company WHERE status = 1 ORDER BY id ASC LIMIT 1")->fetch(PDO::FETCH_ASSOC) ?: [];

$stamp = !empty($company['stamp_path']) ? '/uploads/' . $company['stamp_path'] : null;
$seal = !empty($company['seal_path']) ? '/uploads/' . $company['seal_path'] : null;
$signature = !empty($company['signature_path']) ? '/uploads/' . $company['signature_path'] : null;
$digSig = !empty($company['digital_signature_path']) ? '/uploads/' . $company['digital_signature_path'] : null;

// Dynamic verification QR
$currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$qrApiUrl = "https://api.qrserver.com/v1/create-qr-code/?size=90x90&data=" . urlencode($currentUrl);
?>
<div class="document-footer-block mt-5 pt-3 border-top">
    <div class="row g-3">
        <!-- Declarations & Notes -->
        <div class="col-md-7">
            <h6 class="fw-bold small text-uppercase text-dark mb-1">Declaration & Terms</h6>
            <p class="text-muted small" style="font-size: 0.75rem; line-height: 1.4;">
                <?= !empty($company['declaration']) ? nl2br(htmlspecialchars($company['declaration'])) : "We declare that this document shows the actual price of the goods described and that all particulars are true and correct." ?>
            </p>
            
            <!-- Default Bank Details -->
            <?php if (!empty($company['bank_name'])): ?>
                <div class="mt-3 bg-light p-2 rounded border border-light" style="font-size: 0.75rem;">
                    <span class="d-block fw-bold text-dark mb-1"><i class="fas fa-university me-1"></i> Default Sourcing / Payment Bank Details</span>
                    <div class="row g-1">
                        <div class="col-sm-6"><strong>Bank:</strong> <?= htmlspecialchars($company['bank_name']) ?></div>
                        <div class="col-sm-6"><strong>Beneficiary:</strong> <?= htmlspecialchars($company['account_name'] ?? $company['company_name']) ?></div>
                        <div class="col-sm-6"><strong>Account:</strong> <?= htmlspecialchars($company['account_number']) ?></div>
                        <div class="col-sm-3"><strong>IFSC:</strong> <?= htmlspecialchars($company['ifsc_code'] ?? 'N/A') ?></div>
                        <div class="col-sm-3"><strong>SWIFT:</strong> <?= htmlspecialchars($company['swift_code'] ?? 'N/A') ?></div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- QR Validation & Authorized Signatures -->
        <div class="col-md-5 text-end d-flex flex-column justify-content-between align-items-end">
            <div class="d-flex align-items-center gap-3">
                <!-- QR Code representation -->
                <div class="text-center">
                    <img src="<?= $qrApiUrl ?>" alt="QR Verification" class="img-fluid border p-1 rounded" style="width: 75px; height: 75px;">
                    <span class="d-block text-muted" style="font-size: 0.6rem;">Scan to Verify</span>
                </div>
                
                <!-- Seal -->
                <?php if ($seal): ?>
                    <img src="<?= htmlspecialchars($seal) ?>" alt="Seal" class="img-fluid" style="max-width: <?= (int)($company['seal_print_width'] ?? 100) ?>px; height: auto;">
                <?php endif; ?>
            </div>

            <!-- Signatures -->
            <div class="mt-3 w-100 text-end">
                <div class="d-inline-block position-relative text-center border-bottom pb-1" style="min-width: 180px;">
                    <div class="d-flex align-items-center justify-content-center gap-2 mb-1">
                        <?php if ($signature): ?>
                            <img src="<?= htmlspecialchars($signature) ?>" alt="Authorized Signature" class="img-fluid" style="max-width: <?= (int)($company['signature_print_width'] ?? 120) ?>px; height: auto;">
                        <?php endif; ?>
                        <?php if ($stamp): ?>
                            <img src="<?= htmlspecialchars($stamp) ?>" alt="Company Stamp" class="img-fluid" style="max-width: <?= (int)($company['stamp_print_width'] ?? 100) ?>px; height: auto;">
                        <?php endif; ?>
                    </div>
                    <?php if ($digSig): ?>
                        <div class="text-center opacity-75 mb-1"><img src="<?= htmlspecialchars($digSig) ?>" alt="Digitally Signed" class="img-fluid" style="max-height: 20px;"></div>
                    <?php endif; ?>
                    <span class="d-block fw-bold text-dark small">Authorized Signatory</span>
                </div>
                <span class="d-block text-muted small" style="font-size: 0.7rem;">For <?= htmlspecialchars($company['company_name'] ?? '') ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Print Controller Trigger dropdown layout helper -->
<script>
function triggerPrint(mode, src) {
    var container = $('#printContainer');
    var overlay = $('#letterheadOverlayImg');
    if (mode === 'letterhead' && src) {
        container.addClass('use-letterhead');
        if (overlay.length > 0) {
            overlay.attr('src', src).show();
        }
    } else {
        container.removeClass('use-letterhead');
        if (overlay.length > 0) {
            overlay.hide();
        }
    }
    setTimeout(function() {
        window.print();
    }, 150);
}
</script>
