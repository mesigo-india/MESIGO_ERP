<?php $assets = $assets ?? []; ?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-0 fw-bold"><i class="fas fa-images text-primary me-2"></i>Company Asset Manager</h4>
        <p class="text-muted small mb-0">Upload and configure corporate logos, seals, digital signatures, certificates, and background watermarks.</p>
    </div>
    <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#uploadAssetModal">
        <i class="fas fa-upload me-1"></i> Upload Asset
    </button>
</div>

<!-- Category Tabs -->
<ul class="nav nav-pills nav-custom mb-4" id="assetTabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" id="all-tab" data-bs-toggle="pill" href="#all-pane" role="tab">All Assets</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="logo-tab" data-bs-toggle="pill" href="#logo-pane" role="tab">Logos</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="signature-tab" data-bs-toggle="pill" href="#signature-pane" role="tab">Signatures</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="stamp-tab" data-bs-toggle="pill" href="#stamp-pane" role="tab">Stamps & Seals</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="bg-tab" data-bs-toggle="pill" href="#bg-pane" role="tab">Backgrounds</a>
    </li>
</ul>

<div class="tab-content" id="assetTabsContent">
    <div class="tab-pane fade show active" id="all-pane" role="tabpanel">
        <div class="row g-4">
            <?php if (empty($assets)): ?>
                <div class="col-12 text-center py-5 text-muted">
                    <i class="fas fa-folder-open fa-3x mb-3 opacity-25 d-block"></i>
                    No corporate assets uploaded yet. Upload your first branding logo or signature.
                </div>
            <?php else: ?>
                <?php foreach ($assets as $asset): 
                    $meta = json_decode((string)$asset['metadata_json'], true) ?: [];
                    $displayPath = !empty($meta['processed_path']) ? '/' . $meta['processed_path'] : '/' . $asset['file_path'];
                ?>
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                        <div class="card border-0 shadow-sm h-100 asset-card">
                            <div class="card-img-top d-flex align-items-center justify-content-center bg-light p-3" style="height: 180px; position: relative; overflow: hidden;">
                                <img src="<?= htmlspecialchars($displayPath) ?>" alt="<?= escapeHtml($asset['name']) ?>" 
                                     style="max-height: 100%; max-width: 100%; object-fit: contain; filter: brightness(<?= 100 + ($meta['brightness'] ?? 0) ?>%) contrast(<?= 100 + ($meta['contrast'] ?? 0) ?>%); transform: rotate(<?= $meta['rotate'] ?? 0 ?>deg); opacity: <?= $meta['opacity'] ?? 1.0 ?>;">
                                <span class="badge bg-secondary position-absolute top-2 start-2 text-uppercase"><?= escapeHtml($asset['asset_type']) ?></span>
                            </div>
                            <div class="card-body p-3">
                                <h6 class="fw-bold text-dark mb-1 text-truncate"><?= escapeHtml($asset['name']) ?></h6>
                                <p class="text-muted small mb-3">Dimensions: <?= $meta['width'] ?? 0 ?>x<?= $meta['height'] ?? 0 ?> px</p>
                                
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-outline-primary flex-grow-1" data-bs-toggle="modal" data-bs-target="#editAssetModal-<?= $asset['id'] ?>">
                                        <i class="fas fa-sliders-h me-1"></i> Edit / Filter
                                    </button>
                                    <form method="post" action="/administration/assets/<?= $asset['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Delete this asset?');">
                                        <?= csrfToken() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Asset">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Modal for each asset -->
                    <div class="modal fade" id="editAssetModal-<?= $asset['id'] ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content border-0 shadow-lg">
                                <div class="modal-header bg-dark text-white border-0">
                                    <h5 class="modal-title fw-bold"><i class="fas fa-magic me-2"></i>Edit Asset: <?= escapeHtml($asset['name']) ?></h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form method="post" action="/administration/assets/<?= $asset['id'] ?>">
                                    <?= csrfToken() ?>
                                    <div class="modal-body p-4">
                                        <div class="row g-4">
                                            <div class="col-md-5 text-center bg-light p-3 rounded d-flex align-items-center justify-content-center" style="height: 300px; overflow: hidden;">
                                                <img src="/<?= htmlspecialchars($asset['file_path']) ?>" id="preview-img-<?= $asset['id'] ?>" style="max-height: 100%; max-width: 100%; object-fit: contain;">
                                            </div>
                                            <div class="col-md-7">
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Rotate Image</label>
                                                    <select name="rotate" class="form-select" onchange="updateLivePreview(<?= $asset['id'] ?>)">
                                                        <option value="0" <?= ($meta['rotate'] ?? 0) === 0 ? 'selected' : '' ?>>0° (Normal)</option>
                                                        <option value="90" <?= ($meta['rotate'] ?? 0) === 90 ? 'selected' : '' ?>>90° Clockwise</option>
                                                        <option value="180" <?= ($meta['rotate'] ?? 0) === 180 ? 'selected' : '' ?>>180°</option>
                                                        <option value="270" <?= ($meta['rotate'] ?? 0) === 270 ? 'selected' : '' ?>>270° Counter-Clockwise</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Brightness</label>
                                                    <input type="range" name="brightness" class="form-range" min="-100" max="100" value="<?= $meta['brightness'] ?? 0 ?>" oninput="updateLivePreview(<?= $asset['id'] ?>)">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Contrast</label>
                                                    <input type="range" name="contrast" class="form-range" min="-100" max="100" value="<?= $meta['contrast'] ?? 0 ?>" oninput="updateLivePreview(<?= $asset['id'] ?>)">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Opacity</label>
                                                    <input type="range" name="opacity" class="form-range" min="0" max="1" step="0.05" value="<?= $meta['opacity'] ?? 1.0 ?>" oninput="updateLivePreview(<?= $asset['id'] ?>)">
                                                </div>
                                                <div class="form-check form-switch mb-3">
                                                    <input class="form-check-input" type="checkbox" name="remove_bg" id="removeBgSwitch-<?= $asset['id'] ?>" value="1" <?= !empty($meta['remove_bg']) ? 'checked' : '' ?>>
                                                    <label class="form-check-label fw-bold" for="removeBgSwitch-<?= $asset['id'] ?>">Remove Background (Transparent PNG filter)</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-0 p-3 bg-light rounded-bottom">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary">Save Changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Upload Asset Modal -->
<div class="modal fade" id="uploadAssetModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-upload me-2"></i>Upload Corporate Asset</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="/administration/assets" enctype="multipart/form-data">
                <?= csrfToken() ?>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Asset Type</label>
                        <select name="asset_type" class="form-select" required>
                            <option value="logo">Corporate Logo</option>
                            <option value="signature">Authorized Signature</option>
                            <option value="seal">Corporate Seal</option>
                            <option value="stamp">Rubber Stamp</option>
                            <option value="letterhead_bg">Letterhead Background / Backdrop</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Asset Title / Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Primary Logo, CEO Signature" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Select File</label>
                        <input type="file" name="asset_file" class="form-control" accept="image/*" required>
                        <small class="text-muted">Supports transparent PNG, JPG, GIF up to 5MB.</small>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 bg-light rounded-bottom">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Start Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function updateLivePreview(id) {
    const rotate = document.querySelector(`#editAssetModal-${id} select[name="rotate"]`).value;
    const brightness = document.querySelector(`#editAssetModal-${id} input[name="brightness"]`).value;
    const contrast = document.querySelector(`#editAssetModal-${id} input[name="contrast"]`).value;
    const opacity = document.querySelector(`#editAssetModal-${id} input[name="opacity"]`).value;
    
    const img = document.querySelector(`#preview-img-${id}`);
    if (img) {
        img.style.transform = `rotate(${rotate}deg)`;
        img.style.filter = `brightness(${100 + parseInt(brightness)}%) contrast(${100 + parseInt(contrast)}%)`;
        img.style.opacity = opacity;
    }
}
</script>
