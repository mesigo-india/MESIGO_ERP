<?php
$h = fn($s) => htmlspecialchars((string)($s ?? ''));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $h($title) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 11px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .header h2 {
            margin: 0 0 5px 0;
            color: #000;
        }
        .header p {
            margin: 0;
            color: #555;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #000;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .badge {
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }
        .bg-success { background-color: #d1e7dd; color: #0f5132; }
        .bg-danger { background-color: #f8d7da; color: #842029; }
        .bg-warning { background-color: #fff3cd; color: #664d03; }
        .bg-info { background-color: #cff4fc; color: #055160; }
        
        @media print {
            body { margin: 0; padding: 15px; }
            button { display: none; }
            @page { size: A3 landscape; margin: 10mm; }
        }
    </style>
</head>
<body>

    <button onclick="window.print()" style="position:fixed; top:20px; right:20px; padding:10px 20px; background:#0d6efd; color:#fff; border:none; border-radius:5px; cursor:pointer;">
        Print PDF
    </button>

    <div class="header">
        <h2>MESIGO ERP - Export Product Master</h2>
        <p>Generated on <?= date('d M Y, h:i A') ?></p>
        <p>Total Products: <?= count($products) ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:30px">#</th>
                <th style="width:100px">Code</th>
                <th>Product Name</th>
                <th>HS Code</th>
                <th>Category</th>
                <th>Origin</th>
                <th>Packing</th>
                <th class="text-right">Pur. Price</th>
                <th class="text-right">Sell Price</th>
                <th class="text-right">Stock</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($products)): ?>
                <tr><td colspan="11" class="text-center">No products found.</td></tr>
            <?php else: ?>
                <?php foreach ($products as $i => $p): ?>
                <tr>
                    <td class="text-center"><?= $i + 1 ?></td>
                    <td>
                        <b><?= $h($p['product_code']) ?></b><br>
                        <?php if ($p['is_featured']): ?>
                            <span class="badge bg-warning">Featured</span>
                        <?php endif; ?>
                        <?php if ($p['is_export']): ?>
                            <span class="badge bg-info">Export</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <strong><?= $h($p['name']) ?></strong>
                        <?php if (!empty($p['scientific_name'])): ?>
                            <br><i style="color:#666"><?= $h($p['scientific_name']) ?></i>
                        <?php endif; ?>
                    </td>
                    <td><?= $h($p['hsn_code'] ?: '-') ?></td>
                    <td><?= $h($p['category_name'] ?: '-') ?></td>
                    <td><?= $h($p['country_of_origin'] ?: '-') ?></td>
                    <td>
                        <?= $h($p['packing_size'] ?: '-') ?><br>
                        <span style="font-size:10px;color:#666"><?= $h($p['container_type']) ?></span>
                    </td>
                    <td class="text-right">
                        <?= !empty($p['purchase_price']) ? number_format((float)$p['purchase_price'], 2) : '-' ?> 
                        <?= $h($p['default_currency'] ?? '') ?>
                    </td>
                    <td class="text-right">
                        <?= !empty($p['selling_price']) ? number_format((float)$p['selling_price'], 2) : '-' ?> 
                        <?= $h($p['default_currency'] ?? '') ?>
                    </td>
                    <td class="text-right">
                        <?= !empty($p['opening_stock']) ? number_format((float)$p['opening_stock'], 2) : '-' ?>
                    </td>
                    <td class="text-center">
                        <?php if ($p['status'] == 1): ?>
                            <span class="badge bg-success">Active</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Inactive</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html>
