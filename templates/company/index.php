<?php $companies = $companies ?? []; ?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-0 fw-bold"><i class="fas fa-building text-primary me-2"></i>Company Profiles</h4>
        <p class="text-muted small mb-0">Manage registered entities, tax information, billing details, and signature configurations.</p>
    </div>
    <a href="/company/create" class="btn btn-primary shadow-sm"><i class="fas fa-plus me-1"></i> Add Company</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-nowrap">
                <thead>
                    <tr>
                        <th class="ps-3">Company Details</th>
                        <th>Contact Person</th>
                        <th>GST / IEC</th>
                        <th>CIN / PAN</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($companies)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-building fa-3x mb-3 opacity-25 d-block"></i>
                                No companies found. Click "Add Company" to register your first entity.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($companies as $company): 
                            $logo = !empty($company['logo_path']) ? '/uploads/' . $company['logo_path'] : null;
                        ?>
                            <tr>
                                <td class="ps-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <?php if ($logo): ?>
                                            <img src="<?= htmlspecialchars($logo) ?>" alt="Logo" class="rounded border p-1" style="height: 40px; width: 60px; object-fit: contain;">
                                        <?php else: ?>
                                            <div class="bg-light text-muted border rounded d-flex align-items-center justify-content-center" style="height: 40px; width: 60px;">
                                                <i class="fas fa-building"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <span class="d-block fw-bold text-dark mb-0"><?= escapeHtml($company['company_name']) ?></span>
                                            <span class="text-muted small"><?= escapeHtml($company['email'] ?? 'No email') ?> | <?= escapeHtml($company['phone'] ?? 'No phone') ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="d-block text-dark"><?= escapeHtml($company['contact_person'] ?? 'N/A') ?></span>
                                    <span class="text-muted small"><?= escapeHtml($company['website'] ?? '') ?></span>
                                </td>
                                <td>
                                    <span class="d-block small text-dark"><strong>GST:</strong> <?= escapeHtml($company['gst_number'] ?? 'N/A') ?></span>
                                    <span class="text-muted small"><strong>IEC:</strong> <?= escapeHtml($company['iec_code'] ?? 'N/A') ?></span>
                                </td>
                                <td>
                                    <span class="d-block small text-dark"><strong>CIN:</strong> <?= escapeHtml($company['cin_number'] ?? 'N/A') ?></span>
                                    <span class="text-muted small"><strong>PAN:</strong> <?= escapeHtml($company['pan_number'] ?? 'N/A') ?></span>
                                </td>
                                <td>
                                    <span class="badge badge-<?= (int)$company['status'] === 1 ? 'active' : 'inactive' ?>">
                                        <?= (int)$company['status'] === 1 ? 'Active' : 'Inactive' ?>
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="/company/<?= (int) $company['id'] ?>/edit" class="btn btn-sm btn-outline-primary" title="Edit Company Details">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <form method="post" action="/company/<?= (int) $company['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Disable this company?');">
                                            <?= csrfToken() ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Disable Profile">
                                                <i class="fas fa-ban"></i> Disable
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>