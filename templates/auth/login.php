<?php
declare(strict_types=1);
$flash = flashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MESIGO ERP - <?= escapeHtml($title ?? 'Login') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
    <link href="/assets/css/theme.css" rel="stylesheet">
</head>
<body class="bg-light">
    <main class="min-vh-100 d-flex align-items-center py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="card shadow-sm">
                        <div class="card-body p-4">
                            <div class="text-center mb-4">
                                <h1 class="h3 mb-1 text-primary">MESIGO ERP</h1>
                                <p class="text-muted mb-0">Sign in to continue</p>
                            </div>

                            <?php if ($flash): ?>
                                <div class="alert alert-<?= escapeHtml($flash['type']) ?>" role="alert">
                                    <?= escapeHtml($flash['message']) ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($errors)): ?>
                                <div class="alert alert-danger" role="alert">
                                    <?php foreach ($errors as $error): ?>
                                        <div><?= escapeHtml((string) $error) ?></div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <form method="post" action="/login" class="needs-validation" novalidate>
                                <?= csrfToken() ?>
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username or Email</label>
                                    <input type="text" class="form-control" id="username" name="username" value="<?= escapeHtml($username ?? '') ?>" required autofocus>
                                    <div class="invalid-feedback">Username or email is required.</div>
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                    <div class="invalid-feedback">Password is required.</div>
                                </div>

                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-sign-in-alt me-2"></i>Login
                                </button>
                            </form>

                            <div class="text-center mt-3">
                                <a href="/forgot-password" class="text-decoration-none">Forgot password?</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/app.js"></script>
</body>
</html>