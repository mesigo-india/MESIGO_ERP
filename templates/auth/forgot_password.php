<?php
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MESIGO ERP - <?= escapeHtml($title ?? 'Forgot Password') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
                            <h1 class="h4 text-primary mb-3">Forgot Password</h1>
                            <p class="text-muted">Enter your email address. An administrator will assist with password recovery.</p>

                            <?php if (!empty($errors)): ?>
                                <div class="alert alert-danger" role="alert">
                                    <?php foreach ($errors as $error): ?>
                                        <div><?= escapeHtml((string) $error) ?></div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <form method="post" action="/forgot-password" class="needs-validation" novalidate>
                                <?= csrfToken() ?>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?= escapeHtml($email ?? '') ?>" required>
                                    <div class="invalid-feedback">Valid email is required.</div>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Request Help</button>
                            </form>

                            <div class="text-center mt-3">
                                <a href="/login" class="text-decoration-none">Back to login</a>
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