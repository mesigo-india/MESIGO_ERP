<?php $templates = $templates ?? []; ?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-0 fw-bold"><i class="fas fa-sliders-h text-primary me-2"></i>Enterprise Print Studio</h4>
        <p class="text-muted small mb-0">Design, customize, and configure layout templates for Quotations, Invoices, Packing Lists, Certificates, and advices.</p>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-nowrap">
                <thead>
                    <tr>
                        <th class="ps-3">Document Type</th>
                        <th>Template Name</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($templates)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="fas fa-print fa-3x mb-3 opacity-25 d-block"></i>
                                No document print templates found. Run the seeder to populate default configurations.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($templates as $tpl): ?>
                            <tr>
                                <td class="ps-3 fw-bold text-dark text-capitalize">
                                    <i class="fas fa-file-alt text-primary me-2"></i><?= str_replace('_', ' ', escapeHtml($tpl['document_type'])) ?>
                                </td>
                                <td><?= escapeHtml($tpl['name']) ?></td>
                                <td>
                                    <span class="badge badge-<?= (int)$tpl['is_active'] === 1 ? 'active' : 'inactive' ?>">
                                        <?= (int)$tpl['is_active'] === 1 ? 'Active' : 'Inactive' ?>
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <a href="/administration/print-studio/<?= (int) $tpl['id'] ?>/edit" class="btn btn-sm btn-outline-primary" title="Design Layout">
                                        <i class="fas fa-paint-brush me-1"></i> Customize Layout
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
