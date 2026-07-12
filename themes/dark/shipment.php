
<div class="row" style="margin-bottom: 10px; font-size: 9pt;">
    <div class="col-12" style="border: 1px solid #ddd; padding: 10px; border-radius: 4px;">
        <strong style="color: #0b1c3d; text-transform: uppercase;">Shipment Details</strong>
        <table style="width: 100%; margin-top: 5px; font-size: 8.5pt;">
            <tr>
                <td><strong>Incoterm:</strong> <?php echo htmlspecialchars($header['incoterm_code'] ?? 'N/A'); ?></td>
                <td><strong>Payment:</strong> <?php echo htmlspecialchars($header['payment_terms'] ?? 'N/A'); ?></td>
                <td><strong>Port of Loading:</strong> <?php echo htmlspecialchars($header['loading_port_name'] ?? 'N/A'); ?></td>
                <td><strong>Destination Port:</strong> <?php echo htmlspecialchars($header['destination_port_name'] ?? 'N/A'); ?></td>
            </tr>
        </table>
    </div>
</div>
