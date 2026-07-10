<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MESIGO ERP — Buyer List Export</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 10px; color: #222; background: #fff; }
        .print-header { display: flex; justify-content: space-between; align-items: center; padding: 12px 20px; border-bottom: 2px solid #0d6efd; margin-bottom: 12px; }
        .print-header h1 { font-size: 16px; color: #0d6efd; font-weight: bold; }
        .print-header .meta { text-align: right; color: #555; font-size: 9px; }
        .print-summary { display: flex; gap: 20px; padding: 0 20px 10px; font-size: 9px; color: #555; }
        .print-summary span b { color: #222; }
        table { width: 100%; border-collapse: collapse; font-size: 9px; }
        thead { background: #0d6efd; color: #fff; }
        thead th { padding: 5px 6px; text-align: left; font-weight: 600; border: 1px solid #0856d6; white-space: nowrap; }
        tbody tr { border-bottom: 1px solid #e5e7eb; }
        tbody tr:nth-child(even) { background: #f9fafb; }
        tbody td { padding: 4px 6px; vertical-align: top; }
        .badge { display: inline-block; padding: 1px 5px; border-radius: 3px; font-size: 8px; font-weight: 600; }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-danger  { background: #fee2e2; color: #991b1b; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-secondary { background: #f3f4f6; color: #374151; border: 1px solid #d1d5db; }
        .badge-primary { background: #dbeafe; color: #1e40af; }
        .text-muted { color: #9ca3af; }
        .print-footer { margin-top: 16px; padding: 10px 20px; border-top: 1px solid #e5e7eb; display: flex; justify-content: space-between; color: #9ca3af; font-size: 8px; }
        @media print {
            @page { size: A3 landscape; margin: 10mm; }
            .no-print { display: none !important; }
            body { font-size: 9px; }
        }
    </style>
</head>
<body>
<?php
$buyers    = $buyers ?? [];
$total     = $total  ?? count($buyers);
$today     = date('d M Y');
$time      = date('H:i');
$statusLabels = [1 => 'Active', 0 => 'Inactive', 2 => 'Prospect', 3 => 'Pending'];
$priorityBadge = [
    'High'   => 'badge-danger',
    'Medium' => 'badge-warning',
    'Low'    => 'badge-secondary',
];
$statusBadge = [
    1 => 'badge-success', 0 => 'badge-secondary', 2 => 'badge-primary', 3 => 'badge-warning',
];
?>

<!-- Print toolbar -->
<div class="no-print" style="position:sticky;top:0;z-index:100;background:#1e293b;padding:8px 20px;display:flex;align-items:center;gap:12px;">
    <span style="color:#fff;font-size:13px;font-weight:bold;">MESIGO ERP — Buyer List Export</span>
    <button onclick="window.print()" style="margin-left:auto;background:#0d6efd;color:#fff;border:none;padding:6px 18px;border-radius:4px;cursor:pointer;font-size:12px;">
        🖨️ Print / Save as PDF
    </button>
    <button onclick="window.close()" style="background:#6c757d;color:#fff;border:none;padding:6px 14px;border-radius:4px;cursor:pointer;font-size:12px;">
        ✕ Close
    </button>
</div>

<div style="padding: 0 20px;">

    <!-- Header -->
    <div class="print-header" style="margin-top: 8px;">
        <div>
            <h1>MESIGO INDIA PRIVATE LIMITED</h1>
            <div style="font-size:10px;color:#555;">Buyer CRM — Complete List</div>
        </div>
        <div class="meta">
            <div><b>Date:</b> <?= $today ?> <?= $time ?></div>
            <div><b>Total Records:</b> <?= number_format($total) ?></div>
            <div style="color:#888;margin-top:4px;">Confidential — For Internal Use Only</div>
        </div>
    </div>

    <!-- Table -->
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Buyer Code</th>
                <th>Company Name</th>
                <th>Country</th>
                <th>Contact Person</th>
                <th>Email</th>
                <th>Mobile</th>
                <th>Type</th>
                <th>Priority</th>
                <th>Lead Status</th>
                <th>Payment Terms</th>
                <th>Incoterm</th>
                <th>Assigned To</th>
                <th>Last Contact</th>
                <th>Next Follow-up</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($buyers)): ?>
            <tr><td colspan="16" style="text-align:center;padding:20px;color:#9ca3af;">No buyers found.</td></tr>
            <?php else: ?>
            <?php foreach ($buyers as $i => $b): ?>
            <?php
                $statusInt = (int)($b['status'] ?? 1);
                $today2    = date('Y-m-d');
                $nf        = $b['next_followup'] ?? '';
                $rowBg     = ($nf && $nf < $today2) ? '#fef2f2' : (($nf === $today2) ? '#fefce8' : '');
            ?>
            <tr <?= $rowBg ? 'style="background:' . $rowBg . '"' : '' ?>>
                <td class="text-muted"><?= $i + 1 ?></td>
                <td><b><?= htmlspecialchars($b['buyer_code'] ?? '') ?></b></td>
                <td><?= htmlspecialchars($b['company_name'] ?? '') ?></td>
                <td><?= htmlspecialchars($b['country'] ?? '—') ?></td>
                <td>
                    <?= htmlspecialchars($b['contact_person'] ?? '') ?>
                    <?php if (!empty($b['designation'])): ?>
                    <br><span class="text-muted"><?= htmlspecialchars($b['designation']) ?></span>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($b['email'] ?? '—') ?></td>
                <td><?= htmlspecialchars($b['mobile'] ?? '—') ?></td>
                <td><?= htmlspecialchars($b['buyer_type'] ?? '—') ?></td>
                <td>
                    <?php $pc = $priorityBadge[$b['priority'] ?? ''] ?? 'badge-secondary'; ?>
                    <span class="badge <?= $pc ?>"><?= htmlspecialchars($b['priority'] ?? '—') ?></span>
                </td>
                <td><?= htmlspecialchars($b['lead_status'] ?? 'New Lead') ?></td>
                <td><?= htmlspecialchars($b['payment_terms'] ?? '—') ?></td>
                <td><?= htmlspecialchars($b['preferred_incoterm'] ?? '—') ?></td>
                <td><?= htmlspecialchars($b['assigned_to'] ?? '—') ?></td>
                <td><?= !empty($b['last_contact'])  ? date('d M Y', strtotime($b['last_contact']))  : '—' ?></td>
                <td>
                    <?php if ($nf): ?>
                    <span style="<?= $nf < $today2 ? 'color:#dc3545;font-weight:bold' : ($nf === $today2 ? 'color:#d97706;font-weight:bold' : '') ?>">
                        <?= date('d M Y', strtotime($nf)) ?>
                    </span>
                    <?php else: ?>—<?php endif; ?>
                </td>
                <td>
                    <?php $sc = $statusBadge[$statusInt] ?? 'badge-secondary'; ?>
                    <span class="badge <?= $sc ?>"><?= $statusLabels[$statusInt] ?? 'Unknown' ?></span>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Footer -->
    <div class="print-footer">
        <span>MESIGO ERP v1.0 — © <?= date('Y') ?> MESIGO INDIA PRIVATE LIMITED. All rights reserved.</span>
        <span>Printed: <?= $today ?> <?= $time ?> | Total: <?= number_format($total) ?> Buyers</span>
    </div>

</div>

<script>
// Auto-trigger print dialog after page loads
window.addEventListener('load', function () {
    setTimeout(function () { window.print(); }, 500);
});
</script>
</body>
</html>
