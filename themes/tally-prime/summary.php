
<div class="row" style="margin-top: 15px;">
    <div class="col-6">
        <strong style="color: #0b1c3d;">Amount in Words:</strong>
        <p style="margin: 5px 0; font-size: 8.5pt; color: #555; font-style: italic;">
            <?php echo htmlspecialchars($header['amount_in_words'] ?? 'N/A'); ?>
        </p>
    </div>
    <div class="col-6">
        <table style="width: 100%; font-size: 9.5pt;">
            <tr>
                <td class="text-right"><strong>Subtotal:</strong></td>
                <td class="text-right"><?php echo number_format((float)($header['subtotal'] ?? 0), 2); ?></td>
            </tr>
            <?php if (!empty($header['freight'])): ?>
            <tr>
                <td class="text-right"><strong>Freight Charges:</strong></td>
                <td class="text-right"><?php echo number_format((float)($header['freight'] ?? 0), 2); ?></td>
            </tr>
            <?php endif; ?>
            <tr style="border-top: 1.5px solid #000; font-weight: bold; font-size: 11pt;">
                <td class="text-right"><strong>Grand Total:</strong></td>
                <td class="text-right" style="color: #0b1c3d;"><?php echo number_format((float)($header['grand_total'] ?? 0), 2); ?></td>
            </tr>
        </table>
    </div>
</div>
