
<div class="row" style="border: 1px solid #ddd; padding: 10px; border-radius: 4px; margin-bottom: 10px; font-size: 9pt;">
    <div class="col-12">
        <strong style="color: #0b1c3d;">BANK DETAILS FOR REMITTANCE</strong>
        <table style="width: 100%; margin-top: 5px; font-size: 8.5pt;">
            <tr>
                <td><strong>Bank Name:</strong> <?php echo htmlspecialchars($bank['bank_name'] ?? 'N/A'); ?></td>
                <td><strong>Account Number:</strong> <?php echo htmlspecialchars($bank['account_number'] ?? 'N/A'); ?></td>
                <td><strong>IFSC Code:</strong> <?php echo htmlspecialchars($bank['ifsc_code'] ?? 'N/A'); ?></td>
                <td><strong>SWIFT Code:</strong> <?php echo htmlspecialchars($bank['swift_code'] ?? 'N/A'); ?></td>
            </tr>
        </table>
    </div>
</div>
