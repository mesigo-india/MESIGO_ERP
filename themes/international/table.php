
<table class="product-grid">
    <thead>
        <tr>
            <th style="width: 8%;">S.No</th>
            <th>Description of Goods</th>
            <th style="width: 15%;" class="text-right">Quantity</th>
            <th style="width: 15%;" class="text-right">Rate</th>
            <th style="width: 15%;" class="text-right">Amount</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $i = 1;
        foreach ($items as $item): ?>
            <tr>
                <td class="text-center"><?php echo $i; ?></td>
                <td>
                    <strong><?php echo htmlspecialchars($item['product_name'] ?? ''); ?></strong>
                    <?php if (!empty($item['hs_code'])): ?>
                        <div style="font-size: 8.5pt; color: #555;">HSN: <?php echo htmlspecialchars($item['hs_code']); ?></div>
                    <?php endif; ?>
                </td>
                <td class="text-right"><?php echo number_format((float)($item['quantity'] ?? 0), 2); ?></td>
                <td class="text-right"><?php echo number_format((float)($item['rate'] ?? 0), 2); ?></td>
                <td class="text-right"><?php echo number_format((float)($item['amount'] ?? 0), 2); ?></td>
            </tr>
        <?php 
        $i++;
        endforeach; ?>
        <?php if (empty($items)): ?>
            <tr>
                <td colspan="5" class="text-center text-muted">No items assigned.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
