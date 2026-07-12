
<?php
$signatures = $signatures ?? [];
?>
<div class="row" style="margin-top: 20px; font-size: 9pt;">
    <div class="col-6"></div>
    <div class="col-6 text-right" style="position: relative;">
        <strong style="color: #0b1c3d;">FOR <?php echo htmlspecialchars($company['company_name'] ?? 'MESIGO EXPORTS'); ?></strong>
        <div style="margin: 10px 0;">
            <?php foreach ($signatures as $sig): ?>
                <?php if (!empty($sig['file_path'])): ?>
                    <img src="<?php echo \App\Core\PrintEngine::getBase64Image(APP_ROOT . '/' . $sig['file_path']); ?>" 
                         style="width: <?php echo $sig['scale_percent'] ?? 100; ?>px; display: inline-block; margin-left: 20px;" /><br/>
                <?php endif; ?>
                <strong><?php echo htmlspecialchars($sig['authorized_person'] ?? 'Authorized Signatory'); ?></strong><br/>
                <span style="font-size: 8pt; color: #666;"><?php echo htmlspecialchars($sig['designation'] ?? 'Director'); ?></span>
            <?php endforeach; ?>
        </div>
    </div>
</div>
