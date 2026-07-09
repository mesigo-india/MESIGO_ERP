<?php
declare(strict_types=1);
$title = '404 - Page Not Found';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MESIGO ERP - 404</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .error-page { min-height: 100vh; display: flex; align-items: center; }
        .error-code { font-size: 120px; font-weight: 700; color: #198754; }
    </style>
</head>
<body>
    <div class="error-page">
        <div class="container">
            <div class="row justify-content-center text-center">
                <div class="col-md-6">
                    <div class="error-code">404</div>
                    <h2 class="mb-4">Page Not Found</h2>
                    <p class="text-muted mb-4">The page you are looking for does not exist or has been moved.</p>
                    <a href="/dashboard" class="btn btn-primary">
                        <i class="fas fa-home me-2"></i>Go to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>