
<div class="row" style="margin-bottom: 10px; font-size: 9pt;">
    <div class="col-12" style="border: 1px solid #ddd; padding: 10px; border-radius: 4px;">
        <strong style="color: #0b1c3d; text-transform: uppercase;">Buyer / Consignee Details</strong>
        <p style="margin: 5px 0 0 0; font-weight: bold;"><?php echo htmlspecialchars($buyer['company_name'] ?? 'N/A'); ?></p>
        <p style="margin: 3px 0; color: #555;"><?php echo nl2br(htmlspecialchars($buyer['address'] ?? '')); ?></p>
    </div>
</div>
