<?php
$template = $template ?? [];
$settings = $settings ?? [];
$sections = $sections ?? [];
$watermark = $watermark ?? [];
$signatures = $signatures ?? [];
$assets = $assets ?? [];
?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-0 fw-bold"><i class="fas fa-palette text-primary me-2"></i>Layout Designer: <?= escapeHtml($template['name']) ?></h4>
        <p class="text-muted small mb-0">Drag fields, toggle column parameters, and configure branding elements with real-time A4 visual rendering.</p>
    </div>
    <div>
        <a href="/administration/print-studio" class="btn btn-outline-secondary me-2"><i class="fas fa-arrow-left me-1"></i> Back</a>
    </div>
</div>

<form id="designerForm" method="post" action="/administration/print-studio/<?= $template['id'] ?>">
    <?= csrfToken() ?>
    <div class="row g-4">
        <!-- Settings Panel -->
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-dark text-white p-3 fw-bold">
                    <i class="fas fa-cog me-2"></i>Layout Settings
                </div>
                <div class="card-body p-3">
                    <div class="accordion" id="designerAccordion">
                        <!-- 1. Page Configuration -->
                        <div class="accordion-item border-0 mb-3 shadow-xs">
                            <h2 class="accordion-header" id="pageSetupHeading">
                                <button class="accordion-button fw-bold text-dark rounded" type="button" data-bs-toggle="collapse" data-bs-target="#pageSetupCollapse">
                                    <i class="fas fa-file me-2 text-primary"></i>1. Page Configuration
                                </button>
                            </h2>
                            <div id="pageSetupCollapse" class="accordion-collapse collapse show" data-bs-parent="#designerAccordion">
                                <div class="accordion-body p-3">
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <label class="form-label small fw-bold">Paper Size</label>
                                            <select name="paper_size" class="form-select form-select-sm" onchange="triggerPreviewReload()">
                                                <option value="A4" <?= ($settings['paper_size'] ?? 'A4') === 'A4' ? 'selected' : '' ?>>A4 Standard</option>
                                                <option value="letter" <?= ($settings['paper_size'] ?? 'A4') === 'letter' ? 'selected' : '' ?>>Letter</option>
                                                <option value="legal" <?= ($settings['paper_size'] ?? 'A4') === 'legal' ? 'selected' : '' ?>>Legal</option>
                                            </select>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small fw-bold">Orientation</label>
                                            <select name="orientation" class="form-select form-select-sm" onchange="triggerPreviewReload()">
                                                <option value="portrait" <?= ($settings['orientation'] ?? 'portrait') === 'portrait' ? 'selected' : '' ?>>Portrait</option>
                                                <option value="landscape" <?= ($settings['orientation'] ?? 'portrait') === 'landscape' ? 'selected' : '' ?>>Landscape</option>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label small fw-bold">Paper Mode</label>
                                            <select name="letterhead_mode" class="form-select form-select-sm" onchange="triggerPreviewReload()">
                                                <option value="blank" <?= ($settings['letterhead_mode'] ?? 'blank') === 'blank' ? 'selected' : '' ?>>Blank Paper (Full branding)</option>
                                                <option value="letterhead" <?= ($settings['letterhead_mode'] ?? 'blank') === 'letterhead' ? 'selected' : '' ?>>Pre-printed Letterhead (Hides header/footer)</option>
                                                <option value="logo_only" <?= ($settings['letterhead_mode'] ?? 'blank') === 'logo_only' ? 'selected' : '' ?>>Logo Branding Only</option>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label small fw-bold d-block">Margins (mm)</label>
                                            <div class="d-flex gap-2">
                                                <input type="number" name="margin_left" class="form-control form-control-sm text-center" placeholder="Left" value="<?= $settings['margin_left'] ?? 15 ?>" onchange="triggerPreviewReload()">
                                                <input type="number" name="margin_right" class="form-control form-control-sm text-center" placeholder="Right" value="<?= $settings['margin_right'] ?? 15 ?>" onchange="triggerPreviewReload()">
                                                <input type="number" name="margin_top" class="form-control form-control-sm text-center" placeholder="Top" value="<?= $settings['margin_top'] ?? 15 ?>" onchange="triggerPreviewReload()">
                                                <input type="number" name="margin_bottom" class="form-control form-control-sm text-center" placeholder="Bottom" value="<?= $settings['margin_bottom'] ?? 15 ?>" onchange="triggerPreviewReload()">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Watermark Setup -->
                        <div class="accordion-item border-0 mb-3 shadow-xs">
                            <h2 class="accordion-header" id="watermarkHeading">
                                <button class="accordion-button collapsed fw-bold text-dark rounded" type="button" data-bs-toggle="collapse" data-bs-target="#watermarkCollapse">
                                    <i class="fas fa-ribbon me-2 text-primary"></i>2. Watermark Configuration
                                </button>
                            </h2>
                            <div id="watermarkCollapse" class="accordion-collapse collapse" data-bs-parent="#designerAccordion">
                                <div class="accordion-body p-3">
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Watermark Text</label>
                                        <input type="text" name="watermark_text" class="form-control form-control-sm" value="<?= escapeHtml($watermark['text_value'] ?? '') ?>" onkeyup="triggerPreviewReload()">
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <label class="form-label small fw-bold">Opacity</label>
                                            <input type="number" name="watermark_opacity" step="0.05" min="0" max="1" class="form-control form-control-sm" value="<?= $watermark['opacity'] ?? 0.15 ?>" onchange="triggerPreviewReload()">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small fw-bold">Rotation (deg)</label>
                                            <input type="number" name="watermark_rotation" class="form-control form-control-sm" value="<?= $watermark['rotation'] ?? -30 ?>" onchange="triggerPreviewReload()">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 3. Section Controls -->
                        <div class="accordion-item border-0 mb-3 shadow-xs">
                            <h2 class="accordion-header" id="sectionsHeading">
                                <button class="accordion-button collapsed fw-bold text-dark rounded" type="button" data-bs-toggle="collapse" data-bs-target="#sectionsCollapse">
                                    <i class="fas fa-layer-group me-2 text-primary"></i>3. Section Ordering & Toggle
                                </button>
                            </h2>
                            <div id="sectionsCollapse" class="accordion-collapse collapse" data-bs-parent="#designerAccordion">
                                <div class="accordion-body p-3">
                                    <?php foreach ($sections as $code => $sec): ?>
                                        <div class="card border p-2 mb-2 bg-light">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div>
                                                    <input type="checkbox" name="sections[<?= $sec['id'] ?>][visible]" value="1" <?= $sec['is_visible'] ? 'checked' : '' ?> onchange="triggerPreviewReload()" class="form-check-input me-2">
                                                    <span class="fw-bold text-capitalize text-dark"><?= str_replace('_', ' ', $code) ?> Section</span>
                                                </div>
                                                <input type="number" name="sections[<?= $sec['id'] ?>][sort]" style="width: 60px;" class="form-control form-control-sm text-center" value="<?= $sec['sort_order'] ?>" onchange="triggerPreviewReload()">
                                            </div>
                                            <!-- Section fields customization option -->
                                            <div class="mt-2 border-top pt-2">
                                                <span class="small text-muted fw-bold d-block mb-1">Custom Field Labels:</span>
                                                <?php foreach ($sec['fields'] as $field): ?>
                                                    <div class="row g-2 mb-2 align-items-center">
                                                        <div class="col-6">
                                                            <input type="checkbox" name="fields[<?= $field['id'] ?>][visible]" value="1" <?= $field['is_visible'] ? 'checked' : '' ?> onchange="triggerPreviewReload()" class="form-check-input me-1">
                                                            <span class="small text-muted text-capitalize"><?= str_replace('_', ' ', $field['field_key']) ?></span>
                                                        </div>
                                                        <div class="col-6">
                                                            <input type="text" name="fields[<?= $field['id'] ?>][label]" class="form-control form-control-sm" placeholder="<?= escapeHtml($field['field_key']) ?>" value="<?= escapeHtml($field['custom_label'] ?? '') ?>" onkeyup="triggerPreviewReload()">
                                                            <input type="hidden" name="fields[<?= $field['id'] ?>][span]" value="<?= $field['col_span'] ?>">
                                                            <input type="hidden" name="fields[<?= $field['id'] ?>][sort]" value="<?= $field['sort_order'] ?>">
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary w-100 shadow"><i class="fas fa-save me-1"></i> Save Layout Template</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Live Preview Panel -->
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white p-3 fw-bold d-flex align-items-center justify-content-between">
                    <span><i class="fas fa-eye me-2"></i>A4 Live Preview Canvas</span>
                    <button type="button" class="btn btn-sm btn-light text-primary" onclick="triggerPreviewReload()"><i class="fas fa-sync-alt"></i> Refresh</button>
                </div>
                <div class="card-body p-0 bg-secondary d-flex justify-content-center py-4" style="min-height: 600px;">
                    <!-- A4 Visual Frame Container -->
                    <div style="width: 100%; max-width: 650px; background-color: white; box-shadow: 0 10px 25px rgba(0,0,0,0.15); border-radius: 4px; overflow: hidden;">
                        <iframe id="previewIframe" name="previewIFrameTarget" src="/administration/print-studio/<?= $template['id'] ?>/preview" style="width: 100%; height: 750px; border: none;"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
function triggerPreviewReload() {
    const form = document.getElementById('designerForm');
    if (!form) return;
    
    const originalAction = form.action;
    const originalTarget = form.target;
    
    // Temporarily submit to the preview route targeting the iframe
    form.action = '/administration/print-studio/<?= $template['id'] ?>/preview';
    form.target = 'previewIFrameTarget';
    
    // Submit in background
    form.submit();
    
    // Restore original action and target so regular save still works
    form.action = originalAction;
    form.target = originalTarget;
}

// Initial post preview to display active database configurations on page load
window.addEventListener('load', () => {
    triggerPreviewReload();
});
</script>
