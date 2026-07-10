<?php
declare(strict_types=1);
$flash = flashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MESIGO ERP - <?= htmlspecialchars($title ?? 'Login') ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="/assets/css/style.css" rel="stylesheet">
    <link href="/assets/css/theme.css" rel="stylesheet">
    <style>
        .password-toggle {
            cursor: pointer;
            z-index: 10;
        }
    </style>
</head>
<body>
    <div class="container-fluid p-0">
        <div class="row g-0 login-split-container">
            <!-- Left Panel (Brand Panel) -->
            <div class="col-lg-7 d-none d-lg-flex login-brand-sidebar">
                <div class="max-w-md mx-auto" style="max-width: 500px;">
                    <span class="badge bg-light text-primary border py-2 px-3 mb-3 fw-bold text-uppercase tracking-wider" style="font-size: 0.8rem;">Enterprise Edition</span>
                    <h1 class="display-4 fw-bold mb-3">MESIGO ERP</h1>
                    <p class="h4 fw-normal opacity-75 mb-4 text-white">Sourcing, Costing, and Shipping Agri Commodities Globally.</p>
                    <hr class="w-25 my-4 border-2 border-light">
                    <ul class="list-unstyled d-flex flex-column gap-3 small opacity-90">
                        <li><i class="fas fa-check-circle me-2 text-success"></i> Integrated Cross-Currency Sourcing Engine</li>
                        <li><i class="fas fa-check-circle me-2 text-success"></i> Incoterms Liability Mapping & Profitability Metrics</li>
                        <li><i class="fas fa-check-circle me-2 text-success"></i> Container Loading & Packing Optimization</li>
                    </ul>
                </div>
            </div>
            
            <!-- Right Panel (Login Form Panel) -->
            <div class="col-lg-5 login-form-area">
                <div class="w-100" style="max-width: 420px;">
                    <div class="text-center text-lg-start mb-4">
                        <!-- Logo Placeholder -->
                        <div class="mb-3 d-inline-block d-lg-none">
                            <i class="fas fa-cubes text-primary fa-3x"></i>
                        </div>
                        <h3 class="fw-bold mb-1">Corporate Sign In</h3>
                        <p class="text-muted small">Access your secure workspace using your official credentials.</p>
                    </div>

                    <?php if ($flash): ?>
                        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($flash['message']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php foreach ($errors as $error): ?>
                                <div class="small fw-semibold"><?= htmlspecialchars((string) $error) ?></div>
                            <?php endforeach; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="/login" class="needs-validation" novalidate>
                        <?= csrfToken() ?>
                        
                        <div class="mb-3">
                            <label for="username" class="form-label">Username or Corporate Email</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-user text-muted"></i></span>
                                <input type="text" class="form-control border-start-0 ps-0" id="username" name="username" value="<?= htmlspecialchars($username ?? '') ?>" required autofocus placeholder="username@company.com">
                            </div>
                            <div class="invalid-feedback">Username or email is required.</div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label for="password" class="form-label mb-0">Password</label>
                                <a href="/forgot-password" class="small text-decoration-none">Forgot password?</a>
                            </div>
                            <div class="input-group position-relative">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-muted"></i></span>
                                <input type="password" class="form-control border-start-0 border-end-0 ps-0" id="password" name="password" required placeholder="••••••••">
                                <span class="input-group-text bg-light border-start-0 password-toggle" id="togglePasswordBtn">
                                    <i class="fas fa-eye text-muted" id="togglePasswordIcon"></i>
                                </span>
                            </div>
                            <div class="invalid-feedback">Password is required.</div>
                        </div>

                        <!-- Remember Me -->
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="remember" name="remember">
                            <label class="form-check-label text-muted small" for="remember">
                                Keep me signed in on this computer
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2.5 fw-bold shadow-sm">
                            <i class="fas fa-sign-in-alt me-2"></i>Sign In
                        </button>
                    </form>
                    
                    <div class="mt-5 text-center text-muted small border-top pt-3">
                        <span class="d-block">&copy; <?= date('Y') ?> MESIGO INDIA PRIVATE LIMITED.</span>
                        <span class="d-block mt-1">Authorized access only. All activities are audited.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Password Show/Hide Toggle
            $('#togglePasswordBtn').on('click', function() {
                var passwordField = $('#password');
                var fieldType = passwordField.attr('type');
                if (fieldType === 'password') {
                    passwordField.attr('type', 'text');
                    $('#togglePasswordIcon').removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    passwordField.attr('type', 'password');
                    $('#togglePasswordIcon').removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });

            // Form Validation helper
            (function () {
                'use strict'
                var forms = document.querySelectorAll('.needs-validation')
                Array.prototype.slice.call(forms)
                    .forEach(function (forms) {
                        forms.addEventListener('submit', function (event) {
                            if (!forms.checkValidity()) {
                                event.preventDefault()
                                event.stopPropagation()
                            }
                            forms.classList.add('was-validated')
                        }, false)
                    })
            })()
        });
    </script>
</body>
</html>