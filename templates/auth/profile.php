<?php
$user = $user ?? [];
$avatar = !empty($user['photo_path']) ? '/uploads/' . $user['photo_path'] : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($user['email'] ?? ''))) . '?d=mp';
$sig = !empty($user['signature_path']) ? '/uploads/' . $user['signature_path'] : null;
?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-0 fw-bold"><i class="fas fa-id-card text-primary me-2"></i>My Profile</h4>
        <p class="text-muted small mb-0">Configure your personal information, security passwords, document signature images, and local preferences.</p>
    </div>
    <a href="/dashboard" class="btn btn-outline-secondary btn-sm"><i class="fas fa-home me-1"></i> Home</a>
</div>

<div class="row g-4">
    <!-- Left Column: User Card -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm text-center p-4">
            <div class="card-body">
                <img src="<?= htmlspecialchars($avatar) ?>" alt="User Avatar" class="rounded-circle img-thumbnail mb-3" style="width: 120px; height: 120px; object-fit: cover;">
                <h5 class="fw-bold mb-1"><?= escapeHtml(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?></h5>
                <span class="badge bg-light text-primary border mb-3">System Account</span>
                <p class="text-muted small mb-4"><i class="fas fa-envelope me-1"></i> <?= escapeHtml($user['email'] ?? '') ?></p>
                
                <?php if ($sig): ?>
                    <div class="border rounded p-2 bg-light">
                        <span class="d-block text-muted small fw-semibold mb-2">My Saved Signature</span>
                        <img src="<?= htmlspecialchars($sig) ?>" alt="User Signature" class="img-fluid" style="max-height: 60px;">
                    </div>
                <?php else: ?>
                    <div class="border rounded p-3 bg-light text-muted small">
                        <i class="fas fa-file-signature d-block fa-2x mb-2 opacity-50"></i>
                        No document signature uploaded.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right Column: Settings Form -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form method="post" action="/profile" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <?= csrfToken() ?>

                    <!-- Personal Information -->
                    <div class="mb-4">
                        <h6 class="fw-bold border-bottom pb-2 mb-3 text-primary"><i class="fas fa-user me-1"></i> Personal Information</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="first_name">First Name *</label>
                                <input type="text" id="first_name" name="first_name" class="form-control" value="<?= escapeHtml($user['first_name'] ?? '') ?>" required>
                                <div class="invalid-feedback">First name is required.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="last_name">Last Name *</label>
                                <input type="text" id="last_name" name="last_name" class="form-control" value="<?= escapeHtml($user['last_name'] ?? '') ?>" required>
                                <div class="invalid-feedback">Last name is required.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="email">Email Address *</label>
                                <input type="email" id="email" name="email" class="form-control" value="<?= escapeHtml($user['email'] ?? '') ?>" required>
                                <div class="invalid-feedback">Valid email is required.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="phone">Phone / Mobile</label>
                                <input type="text" id="phone" name="phone" class="form-control" value="<?= escapeHtml($user['phone'] ?? '') ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Regional Settings -->
                    <div class="mb-4">
                        <h6 class="fw-bold border-bottom pb-2 mb-3 text-primary"><i class="fas fa-globe me-1"></i> Language & Locale</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="language">Display Language</label>
                                <select id="language" name="language" class="form-select">
                                    <option value="en" <?= ($user['language'] ?? 'en') === 'en' ? 'selected' : '' ?>>English (United States)</option>
                                    <option value="es" <?= ($user['language'] ?? '') === 'es' ? 'selected' : '' ?>>Español (España)</option>
                                    <option value="fr" <?= ($user['language'] ?? '') === 'fr' ? 'selected' : '' ?>>Français (France)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="timezone">Preferred Timezone</label>
                                <select id="timezone" name="timezone" class="form-select">
                                    <option value="Asia/Kolkata" <?= ($user['timezone'] ?? 'Asia/Kolkata') === 'Asia/Kolkata' ? 'selected' : '' ?>>Asia/Kolkata (GMT+05:30)</option>
                                    <option value="UTC" <?= ($user['timezone'] ?? '') === 'UTC' ? 'selected' : '' ?>>UTC / Greenwich (GMT+00:00)</option>
                                    <option value="America/New_York" <?= ($user['timezone'] ?? '') === 'America/New_York' ? 'selected' : '' ?>>Eastern Time (New York - GMT-05:00)</option>
                                    <option value="Europe/London" <?= ($user['timezone'] ?? '') === 'Europe/London' ? 'selected' : '' ?>>GMT / BST (London - GMT+00:00)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Profile Image / Signature Upload -->
                    <div class="mb-4">
                        <h6 class="fw-bold border-bottom pb-2 mb-3 text-primary"><i class="fas fa-images me-1"></i> Avatar & Signature Uploads</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="photo_file">Profile Picture (PNG / JPEG)</label>
                                <input type="file" id="photo_file" name="photo_file" class="form-control" accept="image/*">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="signature_file">Signature Image (PNG Transparent preferred)</label>
                                <input type="file" id="signature_file" name="signature_file" class="form-control" accept="image/*">
                            </div>
                        </div>
                    </div>

                    <!-- Password Update -->
                    <div class="mb-4">
                        <h6 class="fw-bold border-bottom pb-2 mb-3 text-primary"><i class="fas fa-lock me-1"></i> Security Credentials (Optional)</h6>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label" for="password">Change Password</label>
                                <input type="password" id="password" name="password" class="form-control" placeholder="Leave blank to keep current password">
                                <div class="form-text text-muted">Password must be at least 8 characters.</div>
                            </div>
                        </div>
                    </div>

                    <!-- Form submission -->
                    <div class="text-end pt-3 border-top">
                        <button type="submit" class="btn btn-primary px-4"><i class="fas fa-check-circle me-1"></i> Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
