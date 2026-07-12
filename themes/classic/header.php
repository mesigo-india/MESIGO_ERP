<?php
$logoUrl = "";
if (!empty($company['logo_path'])) {
    $logoUrl = \App\Core\PrintEngine::getBase64Image(APP_ROOT . '/' . $company['logo_path']);
}
?>
<div class="row" style="border-bottom: 2px solid #0b1c3d; padding-bottom: 15px; margin-bottom: 20px; font-family: sans-serif;">
    <div class="col-8">
        <table style="width: 100%; border: none; margin: 0; padding: 0;">
            <tr style="border: none;">
                <?php if (!empty($logoUrl)): ?>
                <td style="width: <?php echo $logoWidth ?? 120; ?>px; vertical-align: top; padding-right: 15px; border: none; background: none;">
                    <img src="<?php echo $logoUrl; ?>" style="width: 100%; max-height: 80px; object-fit: contain; display: block;" />
                </td>
                <?php endif; ?>
                <td style="vertical-align: top; border: none; background: none; text-align: left;">
                    <h3 style="margin: 0; font-size: 13pt; color: #0b1c3d; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; line-height: 1.2;">
                        <?php echo htmlspecialchars($company['company_name'] ?? 'MESIGO EXPORTS'); ?>
                    </h3>
                    <div style="margin-top: 5px; font-size: 8.5pt; color: #475569; line-height: 1.4;">
                        <?php echo nl2br(htmlspecialchars($company['address'] ?? '')); ?>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <div class="col-4 text-right" style="vertical-align: top;">
        <h2 style="margin: 0 0 8px 0; font-size: 16pt; color: #0b1c3d; font-weight: 800; text-transform: uppercase; letter-spacing: 1px;">
            <?php echo htmlspecialchars($header['document_type_label'] ?? 'DOCUMENT'); ?>
        </h2>
        <table style="width: 100%; border-collapse: collapse; font-size: 8.5pt;" align="right">
            <tr style="border: none;">
                <td class="text-right" style="color: #64748b; padding: 2px 5px; border: none; background: none; font-weight: bold;">Doc No:</td>
                <td class="text-right" style="font-weight: bold; color: #0f172a; padding: 2px 0 2px 5px; border: none; background: none;"><?php echo htmlspecialchars($header['document_number'] ?? 'N/A'); ?></td>
            </tr>
            <tr style="border: none;">
                <td class="text-right" style="color: #64748b; padding: 2px 5px; border: none; background: none; font-weight: bold;">Date:</td>
                <td class="text-right" style="font-weight: bold; color: #0f172a; padding: 2px 0 2px 5px; border: none; background: none;"><?php echo htmlspecialchars($header['document_date'] ?? 'N/A'); ?></td>
            </tr>
        </table>
    </div>
</div>
