<?php
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MESIGO ERP - <?= escapeHtml($title ?? 'Reset Password') ?></title>
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
                            <h1 class="h4 text-primary mb-3">Reset Password</h1>

                            <?php if (!empty($errors)): ?>
                                <div class="alert alert-warning" role="alert">
                                    <?php foreach ($errors as $error): ?>
                                        <div><?= escapeHtml((string) $error) ?></div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <a href="/login" class="btn btn-primary w-100">Back to Login</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>