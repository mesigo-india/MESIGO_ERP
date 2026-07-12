<?php
$template = $template ?? [];
$settings = $settings ?? [];
$sections = $sections ?? [];
$watermark = $watermark ?? [];
$signatures = $signatures ?? [];
$assets = $assets ?? [];
$previewDocs = $previewDocs ?? [];

$metadata = json_decode($settings['print_metadata_json'] ?? '{}', true) ?: [];
$logoWidth = (int)($metadata['logo_width'] ?? 120);
$companyNameOverride = $metadata['company_name_override'] ?? '';
$companyAddressOverride = $metadata['company_address_override'] ?? '';


// Fetch all templates to populate Left Sidebar (Explorer)
$db = \App\Core\Database::getInstance();
$allTemplates = $db->query("SELECT * FROM print_templates ORDER BY document_type ASC")->fetchAll(PDO::FETCH_ASSOC);

// Map document types to friendly labels
$docTypesMap = [
    'quotation' => 'Quotation',
    'proforma_invoice' => 'Proforma Invoice',
    'commercial_invoice' => 'Commercial Invoice',
    'packing_list' => 'Packing List',
    'shipping_bill' => 'Shipping Bill',
    'bill_of_lading_draft' => 'Bill Of Lading (Draft)',
    'bill_of_lading_final' => 'Bill Of Lading (Final)',
    'certificate_of_origin' => 'Certificate of Origin',
    'phytosanitary_certificate' => 'Phytosanitary',
    'payment_advice' => 'Payment Advice',
    'purchase_order' => 'Purchase Order',
    'credit_note' => 'Credit Note',
    'debit_note' => 'Debit Note'
];
?>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=Outfit:wght@400;600;800&family=Roboto+Mono&display=swap" rel="stylesheet">
<style>
    /* Adobe InDesign / Canva Premium Style layout */
    body {
        font-family: 'Inter', sans-serif;
        background-color: #0f172a; /* Sleek Dark Theme Outer Canvas */
        margin: 0;
        padding: 0;
        overflow: hidden;
    }
    .workspace-container {
        display: flex;
        flex-direction: column;
        height: 100vh;
        overflow: hidden;
    }
    /* Dynamic Dark Toolbar */
    .top-toolbar {
        background-color: #1e293b;
        color: #f8fafc;
        padding: 8px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid #334155;
        z-index: 100;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
    }
    .toolbar-group {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    /* 4-Panel Main Layout */
    .main-workspace {
        display: flex;
        flex: 1;
        overflow: hidden;
        position: relative;
    }
    /* Left Sidebar: Document Explorer */
    .explorer-panel {
        width: 220px;
        background-color: #0f172a;
        color: #cbd5e1;
        border-right: 1px solid #1e293b;
        display: flex;
        flex-direction: column;
        overflow-y: auto;
    }
    .panel-header-title {
        padding: 12px 15px;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.7rem;
        letter-spacing: 0.08em;
        border-bottom: 1px solid #1e293b;
        color: #64748b;
    }
    .explorer-item {
        padding: 10px 15px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 0.8rem;
        color: #94a3b8;
        text-decoration: none;
        border-left: 3px solid transparent;
        transition: all 0.2s;
    }
    .explorer-item:hover {
        background-color: #1e293b;
        color: #f8fafc;
    }
    .explorer-item.active {
        background-color: #1e293b;
        color: #38bdf8;
        border-left-color: #38bdf8;
        font-weight: 600;
    }
    /* Left Panel 2: Toolbox Element Library */
    .toolbox-panel {
        width: 250px;
        background-color: #1e293b;
        border-right: 1px solid #334155;
        color: #f8fafc;
        display: flex;
        flex-direction: column;
        overflow-y: auto;
        padding: 15px;
    }
    .draggable-block {
        background-color: #334155;
        border: 1px dashed #475569;
        border-radius: 6px;
        padding: 8px 12px;
        margin-bottom: 8px;
        cursor: grab;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 0.75rem;
        font-weight: 600;
        color: #e2e8f0;
        transition: all 0.2s;
    }
    .draggable-block:hover {
        background-color: #475569;
        border-color: #64748b;
    }
    /* Center Page Preview Canvas */
    .canvas-panel {
        flex: 1;
        background-color: #334155; /* Soft Gray Desk Backdrop */
        overflow: auto;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        padding: 40px;
        position: relative;
    }
    .canvas-viewport-wrapper {
        perspective: 1000px;
        transform-style: preserve-3d;
    }
    .a4-page-frame {
        width: 210mm;
        min-height: 297mm;
        background-color: #ffffff;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
        position: relative;
        transition: transform 0.3s ease;
        transform-origin: top center;
        border: 1px solid #475569;
    }
    /* Right Panel: Properties Inspector */
    .properties-panel {
        width: 320px;
        background-color: #1e293b;
        border-left: 1px solid #334155;
        color: #f8fafc;
        display: flex;
        flex-direction: column;
        overflow-y: auto;
        padding: 15px;
    }
    .property-group-header {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        color: #94a3b8;
        margin-top: 15px;
        margin-bottom: 10px;
        padding-bottom: 5px;
        border-bottom: 1px solid #334155;
        letter-spacing: 0.05em;
    }
    .form-label-sm {
        font-size: 0.75rem;
        font-weight: 600;
        color: #cbd5e1;
        margin-bottom: 4px;
    }
    .form-control-dark {
        background-color: #0f172a;
        border: 1px solid #334155;
        color: #fff;
        font-size: 0.8rem;
    }
    .form-control-dark:focus {
        background-color: #0f172a;
        border-color: #38bdf8;
        color: #fff;
        box-shadow: none;
    }
    .form-select-dark {
        background-color: #0f172a;
        border: 1px solid #334155;
        color: #fff;
        font-size: 0.8rem;
    }
    .accordion-dark .accordion-item {
        background-color: #334155;
        border: 1px solid #475569;
        color: #fff;
    }
    .accordion-dark .accordion-button {
        background-color: #334155;
        color: #fff;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 8px 12px;
    }
    .accordion-dark .accordion-button:not(.collapsed) {
        background-color: #475569;
    }
</style>

<form id="designerForm" method="post" action="/administration/print-studio/<?= $template['id'] ?>">
    <?= csrfToken() ?>

    <div class="workspace-container">
        <!-- Top Toolbar -->
        <div class="top-toolbar">
            <div class="toolbar-group">
                <span class="fw-bold small text-info"><i class="fas fa-magic me-2"></i>PRINT STUDIO PRO</span>
                <div class="vr bg-secondary"></div>
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save me-1"></i> Save Layout</button>
                <div class="vr bg-secondary"></div>
                
                <!-- Live Database Document Selector -->
                <label class="small text-muted mb-0 me-1">Preview Record:</label>
                <select name="preview_document_id" class="form-select form-select-sm form-select-dark" style="width: 200px;" onchange="triggerPreviewReload()">
                    <option value="0">Latest Document (Default)</option>
                    <?php foreach ($previewDocs as $doc): ?>
                        <option value="<?= $doc['id'] ?>"><?= escapeHtml($doc['document_number']) ?> (<?= $doc['document_date'] ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="toolbar-group">
                <!-- Theme selection -->
                <label class="small text-muted mb-0 me-1">Theme:</label>
                <select name="theme_code" class="form-select form-select-sm form-select-dark" style="width: 180px;" onchange="triggerPreviewReload()">
                    <option value="mesigo-professional" <?= ($settings['theme_code'] ?? '') === 'mesigo-professional' ? 'selected' : '' ?>>MESIGO Professional</option>
                    <option value="tally-prime" <?= ($settings['theme_code'] ?? '') === 'tally-prime' ? 'selected' : '' ?>>Tally Prime (Ledger Style)</option>
                    <option value="international" <?= ($settings['theme_code'] ?? '') === 'international' ? 'selected' : '' ?>>International Export</option>
                    <option value="minimal" <?= ($settings['theme_code'] ?? '') === 'minimal' ? 'selected' : '' ?>>Minimal Classic</option>
                    <option value="classic" <?= ($settings['theme_code'] ?? '') === 'classic' ? 'selected' : '' ?>>Classic Serif</option>
                    <option value="modern" <?= ($settings['theme_code'] ?? '') === 'modern' ? 'selected' : '' ?>>Modern Inter</option>
                    <option value="dark" <?= ($settings['theme_code'] ?? '') === 'dark' ? 'selected' : '' ?>>Dark Digital</option>
                </select>

                <div class="vr bg-secondary"></div>

                <!-- Zoom selection -->
                <label class="small text-muted mb-0 me-1">Zoom:</label>
                <select id="zoomSelect" class="form-select form-select-sm form-select-dark" style="width: 90px;" onchange="applyZoom(this.value)">
                    <option value="0.5">50%</option>
                    <option value="0.75">75%</option>
                    <option value="1.0" selected>100%</option>
                    <option value="1.25">125%</option>
                    <option value="1.5">150%</option>
                </select>
            </div>
        </div>

        <!-- Main workspace grid -->
        <div class="main-workspace">
            <!-- 1. Document Explorer (Left Panel) -->
            <div class="explorer-panel">
                <div class="panel-header-title">Document Explorer</div>
                <?php foreach ($allTemplates as $tpl): ?>
                    <a href="/administration/print-studio/<?= $tpl['id'] ?>/edit" class="explorer-item <?= (int)$tpl['id'] === (int)$template['id'] ? 'active' : '' ?>">
                        <i class="fas fa-file-invoice small"></i>
                        <span><?= escapeHtml($docTypesMap[$tpl['document_type']] ?? $tpl['name']) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- 2. Toolbox Element Library -->
            <div class="toolbox-panel">
                <div class="panel-header-title" style="padding:0; margin-bottom:12px;">Layout Blocks</div>
                <div id="blocksList">
                    <?php foreach ($sections as $code => $sec): ?>
                        <div class="draggable-block" draggable="true" data-section-id="<?= $sec['id'] ?>">
                            <span>
                                <input type="checkbox" name="sections[<?= $sec['id'] ?>][visible]" value="1" <?= $sec['is_visible'] ? 'checked' : '' ?> onchange="triggerPreviewReload()" class="form-check-input me-2">
                                <span class="text-capitalize"><?= str_replace('_', ' ', $code) ?></span>
                            </span>
                            <i class="fas fa-grip-vertical text-muted"></i>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- 3. Center Canvas (Virtual A4 Page) -->
            <div class="canvas-panel">
                <div class="canvas-viewport-wrapper">
                    <div class="a4-page-frame" id="a4Frame">
                        <iframe id="previewIframe" name="previewIFrameTarget" src="/administration/print-studio/<?= $template['id'] ?>/preview" style="width: 100%; height: 297mm; border: none;"></iframe>
                    </div>
                </div>
            </div>

            <!-- 4. Properties Panel (Right Panel) -->
            <div class="properties-panel">
                <div class="property-group-header" style="margin-top:0;">Page Setup</div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label form-label-sm">Paper Size</label>
                        <select name="paper_size" class="form-select form-select-sm form-select-dark" onchange="triggerPreviewReload()">
                            <option value="A4" <?= ($settings['paper_size'] ?? 'A4') === 'A4' ? 'selected' : '' ?>>A4 Standard</option>
                            <option value="letter" <?= ($settings['paper_size'] ?? 'A4') === 'letter' ? 'selected' : '' ?>>Letter</option>
                            <option value="legal" <?= ($settings['paper_size'] ?? 'A4') === 'legal' ? 'selected' : '' ?>>Legal</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label form-label-sm">Orientation</label>
                        <select name="orientation" class="form-select form-select-sm form-select-dark" onchange="triggerPreviewReload()">
                            <option value="portrait" <?= ($settings['orientation'] ?? 'portrait') === 'portrait' ? 'selected' : '' ?>>Portrait</option>
                            <option value="landscape" <?= ($settings['orientation'] ?? 'portrait') === 'landscape' ? 'selected' : '' ?>>Landscape</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label form-label-sm">Letterhead Mode</label>
                        <select name="letterhead_mode" class="form-select form-select-sm form-select-dark" onchange="triggerPreviewReload()">
                            <option value="blank" <?= ($settings['letterhead_mode'] ?? 'blank') === 'blank' ? 'selected' : '' ?>>Blank Paper (Full branding)</option>
                            <option value="letterhead" <?= ($settings['letterhead_mode'] ?? 'blank') === 'letterhead' ? 'selected' : '' ?>>Pre-printed Letterhead</option>
                            <option value="logo_only" <?= ($settings['letterhead_mode'] ?? 'blank') === 'logo_only' ? 'selected' : '' ?>>Logo Branding Only</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label form-label-sm">Margins (mm)</label>
                        <div class="d-flex gap-1">
                            <input type="number" name="margin_left" class="form-control form-control-sm form-control-dark text-center" placeholder="L" value="<?= $settings['margin_left'] ?? 15 ?>" onchange="triggerPreviewReload()">
                            <input type="number" name="margin_right" class="form-control form-control-sm form-control-dark text-center" placeholder="R" value="<?= $settings['margin_right'] ?? 15 ?>" onchange="triggerPreviewReload()">
                            <input type="number" name="margin_top" class="form-control form-control-sm form-control-dark text-center" placeholder="T" value="<?= $settings['margin_top'] ?? 15 ?>" onchange="triggerPreviewReload()">
                            <input type="number" name="margin_bottom" class="form-control form-control-sm form-control-dark text-center" placeholder="B" value="<?= $settings['margin_bottom'] ?? 15 ?>" onchange="triggerPreviewReload()">
                        </div>
                    </div>
                </div>

                <div class="property-group-header">Watermark</div>
                <div class="mb-3">
                    <label class="form-label form-label-sm">Text Value</label>
                    <input type="text" name="watermark_text" class="form-control form-control-sm form-control-dark" value="<?= escapeHtml($watermark['text_value'] ?? '') ?>" onkeyup="triggerPreviewReloadDebounced()">
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label form-label-sm">Opacity</label>
                        <input type="number" name="watermark_opacity" step="0.05" min="0" max="1" class="form-control form-control-sm form-control-dark" value="<?= $watermark['opacity'] ?? 0.15 ?>" onchange="triggerPreviewReload()">
                    </div>
                    <div class="col-6">
                        <label class="form-label form-label-sm">Rotation</label>
                        <input type="number" name="watermark_rotation" class="form-control form-control-sm form-control-dark" value="<?= $watermark['rotation'] ?? -30 ?>" onchange="triggerPreviewReload()">
                    </div>
                </div>

                <div class="property-group-header">Header & Logo Setup</div>
                <div class="row g-2 mb-3">
                    <div class="col-12">
                        <label class="form-label form-label-sm">Logo Width (px)</label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="range" name="logo_width" min="50" max="300" step="5" class="form-range" value="<?= $logoWidth ?>" oninput="this.nextElementSibling.value = this.value; triggerPreviewReloadDebounced()">
                            <output class="text-light small"><?= $logoWidth ?></output>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label form-label-sm">Company Name Override</label>
                        <input type="text" name="company_name_override" class="form-control form-control-sm form-control-dark" placeholder="Mesigo India Pvt Ltd" value="<?= escapeHtml($companyNameOverride) ?>" onkeyup="triggerPreviewReloadDebounced()">
                    </div>
                    <div class="col-12">
                        <label class="form-label form-label-sm">Company Address Override</label>
                        <textarea name="company_address_override" rows="2" class="form-control form-control-sm form-control-dark" placeholder="Line 1, Line 2, City..." onkeyup="triggerPreviewReloadDebounced()"><?= escapeHtml($companyAddressOverride) ?></textarea>
                    </div>
                </div>

                <div class="property-group-header">Field Label Customizer</div>
                <div class="accordion accordion-dark" id="fieldsAccordion" style="max-height: 320px; overflow-y: auto;">
                    <?php foreach ($sections as $code => $sec): ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed py-2 px-3" type="button" data-bs-toggle="collapse" data-bs-target="#secFields_<?= $sec['id'] ?>">
                                    <?= str_replace('_', ' ', $code) ?>
                                </button>
                            </h2>
                            <div id="secFields_<?= $sec['id'] ?>" class="accordion-collapse collapse" data-bs-parent="#fieldsAccordion">
                                <div class="accordion-body p-2 bg-dark">
                                    <?php foreach ($sec['fields'] as $field): ?>
                                        <div class="mb-2 p-2 border border-secondary rounded bg-dark">
                                            <div class="d-flex align-items-center justify-content-between mb-1">
                                                <span class="small fw-bold text-light text-capitalize"><?= str_replace('_', ' ', $field['field_key']) ?></span>
                                                <input type="checkbox" name="fields[<?= $field['id'] ?>][visible]" value="1" <?= $field['is_visible'] ? 'checked' : '' ?> onchange="triggerPreviewReload()" class="form-check-input">
                                            </div>
                                            <input type="text" name="fields[<?= $field['id'] ?>][label]" class="form-control form-control-sm form-control-dark" placeholder="<?= escapeHtml($field['field_key']) ?>" value="<?= escapeHtml($field['custom_label'] ?? '') ?>" onkeyup="triggerPreviewReloadDebounced()">
                                            <input type="hidden" name="fields[<?= $field['id'] ?>][span]" value="<?= $field['col_span'] ?>">
                                            <input type="hidden" name="fields[<?= $field['id'] ?>][sort]" value="<?= $field['sort_order'] ?>" class="field-sort-input">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
let reloadTimeout = null;

function triggerPreviewReload() {
    const form = document.getElementById('designerForm');
    if (!form) return;
    
    const originalAction = form.action;
    const originalTarget = form.target;
    
    // Submit to target preview route targeting the iframe
    form.action = '/administration/print-studio/<?= $template['id'] ?>/preview';
    form.target = 'previewIFrameTarget';
    
    form.submit();
    
    form.action = originalAction;
    form.target = originalTarget;
}

function triggerPreviewReloadDebounced() {
    clearTimeout(reloadTimeout);
    reloadTimeout = setTimeout(triggerPreviewReload, 250);
}

function applyZoom(scale) {
    const frame = document.getElementById('a4Frame');
    if (frame) {
        frame.style.transform = `scale(${scale})`;
    }
}

// Drag & Drop Handling logic
let dragSrcEl = null;

function handleDragStart(e) {
    this.style.opacity = '0.4';
    dragSrcEl = this;
    e.dataTransfer.effectAllowed = 'move';
}

function handleDragOver(e) {
    if (e.preventDefault) {
        e.preventDefault();
    }
    e.dataTransfer.dropEffect = 'move';
    return false;
}

function handleDragEnter(e) {
    this.classList.add('over');
}

function handleDragLeave(e) {
    this.classList.remove('over');
}

function handleDrop(e) {
    if (e.stopPropagation) {
        e.stopPropagation();
    }
    
    if (dragSrcEl !== this) {
        const list = document.getElementById('blocksList');
        const children = Array.from(list.children);
        const indexSrc = children.indexOf(dragSrcEl);
        const indexTarget = children.indexOf(this);
        
        if (indexSrc < indexTarget) {
            list.insertBefore(dragSrcEl, this.nextSibling);
        } else {
            list.insertBefore(dragSrcEl, this);
        }
        reorderSortInputs();
    }
    return false;
}

function handleDragEnd(e) {
    this.style.opacity = '1.0';
    document.querySelectorAll('.draggable-block').forEach(item => {
        item.classList.remove('over');
    });
}

function reorderSortInputs() {
    const list = document.getElementById('blocksList');
    const items = Array.from(list.children);
    items.forEach((item, index) => {
        const secId = item.getAttribute('data-section-id');
        let sortInput = document.querySelector(`input[name="sections[${secId}][sort]"]`);
        if (!sortInput) {
            sortInput = document.createElement('input');
            sortInput.type = 'hidden';
            sortInput.name = `sections[${secId}][sort]`;
            document.getElementById('designerForm').appendChild(sortInput);
        }
        sortInput.value = index;
    });
    triggerPreviewReload();
}

function initDragAndDrop() {
    const items = document.querySelectorAll('.draggable-block');
    items.forEach(item => {
        item.addEventListener('dragstart', handleDragStart, false);
        item.addEventListener('dragenter', handleDragEnter, false);
        item.addEventListener('dragover', handleDragOver, false);
        item.addEventListener('dragleave', handleDragLeave, false);
        item.addEventListener('drop', handleDrop, false);
        item.addEventListener('dragend', handleDragEnd, false);
    });
}

window.addEventListener('load', () => {
    initDragAndDrop();
    triggerPreviewReload();
});
</script>
