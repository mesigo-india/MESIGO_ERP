<?php
$title = $title ?? 'Product Master Data';
$action = $action ?? '';
$row = $row ?? null;
$config = $config ?? [];
$masterKey = $masterKey ?? '';
$fields = $config['fields'] ?? [];
$lookups = $lookups ?? [];
?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-0 fw-bold"><i class="fas fa-edit text-primary me-2"></i><?= escapeHtml((string)$title) ?></h4>
        <p class="text-muted small mb-0">Configure advanced enterprise master specifications with real-time AI assistance.</p>
    </div>
    <div>
        <a href="/settings/master-data/<?= escapeHtml((string)$masterKey) ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Back to List</a>
    </div>
</div>

<div class="row g-4">
    <!-- Main Form Panel -->
    <div class="col-12 col-lg-8">
        <form id="masterForm" method="post" action="<?= escapeHtml((string)$action) ?>" class="needs-validation" novalidate>
            <?= csrfToken() ?>

            <!-- 1. Basic Information Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-dark text-white p-3 fw-bold"><i class="fas fa-info-circle me-2"></i>1. Basic Information</div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <?php foreach ($fields as $field => $meta): ?>
                            <?php 
                            // Render Basic info fields in first card
                            $isBasic = in_array($field, ['code', 'product_code', 'name', 'sku', 'scientific_name', 'trade_name', 'category_id', 'parent_category_id', 'description']);
                            if (!$isBasic) continue; 
                            ?>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold" for="<?= escapeHtml((string)$field) ?>"><?= escapeHtml((string)$meta['label']) ?></label>
                                <?php if (($meta['type'] ?? '') === 'select'): ?>
                                    <select id="<?= escapeHtml((string)$field) ?>" name="<?= escapeHtml((string)$field) ?>" class="form-select form-select-sm" <?= !empty($meta['required']) ? 'required' : '' ?> onchange="handleLookupChange('<?= $field ?>')">
                                        <option value="">-- Select <?= escapeHtml((string)$meta['label']) ?> --</option>
                                        <?php foreach (($lookups[$field] ?? []) as $opt): ?>
                                            <option value="<?= escapeHtml((string)$opt['id']) ?>" <?= (string)($row[$field] ?? '') === (string)$opt['id'] ? 'selected' : '' ?>>
                                                <?= escapeHtml((string)($opt['code'] ?? '')) ?> - <?= escapeHtml((string)$opt['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php elseif (($meta['type'] ?? '') === 'textarea'): ?>
                                    <textarea id="<?= escapeHtml((string)$field) ?>" name="<?= escapeHtml((string)$field) ?>" class="form-control form-control-sm" rows="3"><?= escapeHtml((string)($row[$field] ?? '')) ?></textarea>
                                <?php elseif (($meta['type'] ?? '') === 'checkbox'): ?>
                                    <div class="form-check form-switch mt-2">
                                        <input type="checkbox" id="<?= escapeHtml((string)$field) ?>" name="<?= escapeHtml((string)$field) ?>" class="form-check-input" <?= !empty($row[$field]) ? 'checked' : '' ?>>
                                        <label class="form-check-label small" for="<?= escapeHtml((string)$field) ?>">Allowed</label>
                                    </div>
                                <?php else: ?>
                                    <input type="<?= ($meta['type'] ?? '') === 'number' ? 'number' : 'text' ?>" step="<?= escapeHtml((string)($meta['step'] ?? '1')) ?>" id="<?= escapeHtml((string)$field) ?>" name="<?= escapeHtml((string)$field) ?>" class="form-control form-control-sm" value="<?= escapeHtml((string)($row[$field] ?? '')) ?>" <?= !empty($meta['required']) ? 'required' : '' ?> <?= !empty($meta['max']) ? 'maxlength="' . (int)$meta['max'] . '"' : '' ?>>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- 2. Business & Quality Parameters Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white p-3 fw-bold"><i class="fas fa-cogs me-2"></i>2. Business & Quality Metrics</div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <?php foreach ($fields as $field => $meta): ?>
                            <?php 
                            // Render remaining details/business info
                            $isBasic = in_array($field, ['code', 'product_code', 'name', 'sku', 'scientific_name', 'trade_name', 'category_id', 'parent_category_id', 'description', 'internal_notes', 'tags', 'remarks']);
                            if ($isBasic) continue; 
                            ?>
                            <div class="col-md-6 col-lg-4">
                                <label class="form-label small fw-bold" for="<?= escapeHtml((string)$field) ?>"><?= escapeHtml((string)$meta['label']) ?></label>
                                <?php if (($meta['type'] ?? '') === 'select'): ?>
                                    <select id="<?= escapeHtml((string)$field) ?>" name="<?= escapeHtml((string)$field) ?>" class="form-select form-select-sm" <?= !empty($meta['required']) ? 'required' : '' ?> onchange="handleLookupChange('<?= $field ?>')">
                                        <option value="">-- Select <?= escapeHtml((string)$meta['label']) ?> --</option>
                                        <?php foreach (($lookups[$field] ?? []) as $opt): ?>
                                            <option value="<?= escapeHtml((string)$opt['id']) ?>" <?= (string)($row[$field] ?? '') === (string)$opt['id'] ? 'selected' : '' ?>>
                                                <?= escapeHtml((string)($opt['code'] ?? '')) ?> - <?= escapeHtml((string)$opt['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php elseif (($meta['type'] ?? '') === 'textarea'): ?>
                                    <textarea id="<?= escapeHtml((string)$field) ?>" name="<?= escapeHtml((string)$field) ?>" class="form-control form-control-sm" rows="2"><?= escapeHtml((string)($row[$field] ?? '')) ?></textarea>
                                <?php elseif (($meta['type'] ?? '') === 'checkbox'): ?>
                                    <div class="form-check form-switch mt-2">
                                        <input type="checkbox" id="<?= escapeHtml((string)$field) ?>" name="<?= escapeHtml((string)$field) ?>" class="form-check-input" <?= !empty($row[$field]) ? 'checked' : '' ?>>
                                        <label class="form-check-label small" for="<?= escapeHtml((string)$field) ?>">Active / Enabled</label>
                                    </div>
                                <?php else: ?>
                                    <input type="<?= ($meta['type'] ?? '') === 'number' ? 'number' : 'text' ?>" step="<?= escapeHtml((string)($meta['step'] ?? '0.0001')) ?>" id="<?= escapeHtml((string)$field) ?>" name="<?= escapeHtml((string)$field) ?>" class="form-control form-control-sm" value="<?= escapeHtml((string)($row[$field] ?? '')) ?>" <?= !empty($meta['required']) ? 'required' : '' ?> <?= !empty($meta['max']) ? 'maxlength="' . (int)$meta['max'] . '"' : '' ?>>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- 3. Notes, Meta-tags & Status Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-secondary text-white p-3 fw-bold"><i class="fas fa-tags me-2"></i>3. Administration & Notes</div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold" for="status">Master Record Status</label>
                            <select id="status" name="status" class="form-select form-select-sm">
                                <option value="1" <?= (int)($row['status'] ?? 1) === 1 ? 'selected' : '' ?>>Active / Operational</option>
                                <option value="0" <?= (int)($row['status'] ?? 1) === 0 ? 'selected' : '' ?>>Inactive / Disabled</option>
                            </select>
                        </div>
                        <?php if (isset($fields['tags'])): ?>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold" for="tags">Keywords / Tags</label>
                                <input type="text" id="tags" name="tags" class="form-control form-control-sm" placeholder="e.g. Spices, Export, Premium" value="<?= escapeHtml((string)($row['tags'] ?? '')) ?>">
                            </div>
                        <?php endif; ?>
                        <?php if (isset($fields['remarks'])): ?>
                            <div class="col-12">
                                <label class="form-label small fw-bold" for="remarks">Remarks / Internal Comments</label>
                                <textarea id="remarks" name="remarks" class="form-control form-control-sm" rows="2"><?= escapeHtml((string)($row['remarks'] ?? '')) ?></textarea>
                            </div>
                        <?php endif; ?>
                        <?php if (isset($fields['internal_notes'])): ?>
                            <div class="col-12">
                                <label class="form-label small fw-bold" for="internal_notes">Audit Notes</label>
                                <textarea id="internal_notes" name="internal_notes" class="form-control form-control-sm" rows="2"><?= escapeHtml((string)($row['internal_notes'] ?? '')) ?></textarea>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save me-1"></i> Save <?= escapeHtml((string)($config['singular'] ?? 'Master')) ?></button>
        </form>
    </div>

    <!-- AI Collapsible Assistant Sidebar -->
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm sticky-top" style="top: 20px; z-index: 1020;">
            <div class="card-header bg-dark text-white p-3 fw-bold d-flex align-items-center justify-content-between">
                <span><i class="fas fa-robot me-2 text-warning"></i>AI Assistant Layer</span>
                <span class="badge bg-warning text-dark small">Provider independent</span>
            </div>
            <div class="card-body p-3">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Analyze Product Description / Title</label>
                    <div class="input-group">
                        <input type="text" id="aiQueryInput" class="form-control form-control-sm" placeholder="e.g. Premium Cumin Seeds Sortex 99.5%" value="<?= escapeHtml((string)($row['name'] ?? '')) ?>">
                        <button type="button" class="btn btn-dark btn-sm" onclick="getAiSuggestions()"><i class="fas fa-magic"></i> Suggest</button>
                    </div>
                    <div class="form-text small text-muted">AI reads your text to suggest purity percentages, GST bands, storage recommendations, packaging types, and HS codes.</div>
                </div>

                <!-- Suggestions container -->
                <div id="aiSuggestionsContainer" class="d-none">
                    <h6 class="fw-bold small border-bottom pb-2 text-primary">Suggested Values:</h6>
                    <div id="aiSuggestionsList" class="mb-3" style="max-height: 350px; overflow-y: auto;">
                        <!-- JS generated pills -->
                    </div>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-success btn-sm" onclick="applyAllSuggestions()"><i class="fas fa-check-double"></i> Accept All Suggestions</button>
                    </div>
                </div>

                <div id="aiLoadingIndicator" class="text-center py-4 d-none">
                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                    <span class="small text-muted ms-2">AI Engine analyzing specifications...</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let lastSuggestions = {};

// Fetch suggestions from Provider-independent AI layer
async function getAiSuggestions() {
    const query = document.getElementById('aiQueryInput').value.trim();
    if (query.length < 3) {
        alert("Please enter a longer query description first.");
        return;
    }

    const container = document.getElementById('aiSuggestionsContainer');
    const list = document.getElementById('aiSuggestionsList');
    const loader = document.getElementById('aiLoadingIndicator');

    container.classList.add('d-none');
    loader.classList.remove('d-none');
    list.innerHTML = "";

    try {
        const response = await fetch(`/api/v1/ai/suggest?master=<?= $masterKey ?>&query=${encodeURIComponent(query)}`);
        const result = await response.json();
        loader.classList.add('d-none');

        if (result.success && Object.keys(result.suggestions).length > 0) {
            lastSuggestions = result.suggestions;
            container.classList.remove('d-none');

            for (const [field, val] of Object.entries(lastSuggestions)) {
                const el = document.getElementById(field);
                if (el) {
                    const rowDiv = document.createElement('div');
                    rowDiv.className = "d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded border-start border-4 border-warning";
                    rowDiv.innerHTML = `
                        <div>
                            <span class="small text-muted d-block">${field.replace('_', ' ').toUpperCase()}</span>
                            <strong class="small text-dark">${val}</strong>
                        </div>
                        <button type="button" class="btn btn-xs btn-outline-warning text-dark py-0 px-2" onclick="applySingleSuggestion('${field}', '${val}')">Apply</button>
                    `;
                    list.appendChild(rowDiv);
                }
            }
        } else {
            alert("No suggestions returned from the AI. Check AI Config or try a different description query.");
        }
    } catch (e) {
        loader.classList.add('d-none');
        alert("AI suggestions failed: " . e.message);
    }
}

function applySingleSuggestion(field, val) {
    const el = document.getElementById(field);
    if (el) {
        if (el.type === 'checkbox') {
            el.checked = parseInt(val) === 1;
        } else {
            el.value = val;
        }
        // Highlight success
        el.classList.add('border-warning');
        setTimeout(() => el.classList.remove('border-warning'), 2000);
    }
}

function applyAllSuggestions() {
    for (const [field, val] of Object.entries(lastSuggestions)) {
        applySingleSuggestion(field, val);
    }
}

// Auto-relationship triggers
function handleLookupChange(field) {
    if (field === 'category_id') {
        const categoryId = document.getElementById('category_id').value;
        if (!categoryId) return;
        
        // Changing Category updates default GST and Units
        fetch(`/settings/master-data/product-categories/${categoryId}/details`)
            .then(r => r.json())
            .then(res => {
                if (res.success && res.data) {
                    const data = res.data;
                    if (document.getElementById('gst') && data.default_gst) {
                        document.getElementById('gst').value = data.default_gst;
                    }
                    if (document.getElementById('hsn_code') && data.hs_chapter) {
                        document.getElementById('hsn_code').value = data.hs_chapter + '00';
                    }
                    if (document.getElementById('storage') && data.default_storage) {
                        document.getElementById('storage').value = data.default_storage;
                    }
                }
            });
    }
}
</script>