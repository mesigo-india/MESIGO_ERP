<?php
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MESIGO ERP - <?= htmlspecialchars($title ?? 'Forgot Password') ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="/assets/css/style.css" rel="stylesheet">
    <link href="/assets/css/theme.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid p-0">
        <div class="row g-0 login-split-container">
            <!-- Left Panel (Brand Panel) -->
            <div class="col-lg-7 d-none d-lg-flex login-brand-sidebar">
                <div class="max-w-md mx-auto" style="max-width: 500px;">
                    <span class="badge bg-light text-primary border py-2 px-3 mb-3 fw-bold text-uppercase tracking-wider" style="font-size: 0.8rem;">Security Operations</span>
                    <h1 class="display-4 fw-bold mb-3">Self Service Password Recovery</h1>
                    <p class="h4 fw-normal opacity-75 mb-4 text-white">Reset credentials or request administrative credential assistance.</p>
                    <hr class="w-25 my-4 border-2 border-light">
                    <p class="small text-white opacity-75">All requests generate system event notifications for audit logging.</p>
                </div>
            </div>
            
            <!-- Right Panel (Recovery Form Panel) -->
            <div class="col-lg-5 login-form-area">
                <div class="w-100" style="max-width: 420px;">
                    <div class="text-center text-lg-start mb-4">
                        <div class="mb-3 d-inline-block d-lg-none">
                            <i class="fas fa-shield-alt text-primary fa-3x"></i>
                        </div>
                        <h3 class="fw-bold mb-1">Recover Password</h3>
                        <p class="text-muted small">Enter your registered email address. Password reset instructions will be generated.</p>
                    </div>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php foreach ($errors as $error): ?>
                                <div class="small fw-semibold"><?= htmlspecialchars((string) $error) ?></div>
                            <?php endforeach; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="/forgot-password" class="needs-validation" novalidate>
                        <?= csrfToken() ?>
                        
                        <div class="mb-4">
                            <label for="email" class="form-label">Corporate Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-envelope text-muted"></i></span>
                                <input type="email" class="form-control border-start-0 ps-0" id="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>" required placeholder="username@company.com" autofocus>
                            </div>
                            <div class="invalid-feedback">A valid corporate email address is required.</div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2.5 fw-bold shadow-sm mb-3">
                            <i class="fas fa-paper-plane me-2"></i>Send Request
                        </button>
                    </form>
                    
                    <div class="text-center">
                        <a href="/login" class="small text-decoration-none fw-semibold"><i class="fas fa-arrow-left me-1"></i> Back to sign in</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>