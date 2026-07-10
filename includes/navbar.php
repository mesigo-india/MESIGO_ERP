<?php
/**
 * MESIGO ERP - Enterprise Navbar Template
 */
use App\Core\Database;
use App\Core\Session;

$dbInstance = Database::getInstance();

// Retrieve corporate settings
$companyInfo = $dbInstance->query("SELECT * FROM company WHERE status = 1 ORDER BY id ASC LIMIT 1")->fetch();
$activeBranch = $dbInstance->query("SELECT * FROM branches WHERE status = 1 ORDER BY id ASC LIMIT 1")->fetch();
$activeFY = $dbInstance->query("SELECT * FROM financial_years WHERE status = 1 ORDER BY id ASC LIMIT 1")->fetch();

// Current User Photo
$currentUserPhoto = null;
if (Session::has('user_id')) {
    $stmtUser = $dbInstance->prepare("SELECT photo_path FROM users WHERE id = ?");
    $stmtUser->execute([Session::get('user_id')]);
    $currentUserPhoto = $stmtUser->fetchColumn();
}
$userAvatar = $currentUserPhoto ? '/uploads/' . $currentUserPhoto : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim(Session::get('email') ?? ''))) . '?d=mp';

$companyLogo = ($companyInfo && !empty($companyInfo['logo_path'])) ? '/uploads/' . $companyInfo['logo_path'] : '/assets/img/logo-placeholder.png';
?>
<header class="navbar navbar-expand navbar-light bg-white border-bottom px-4 d-print-none">
    <div class="container-fluid p-0">
        <!-- Sidebar Toggle -->
        <button class="btn btn-outline-secondary border-0 me-3" type="button" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        
        <!-- Corporate Context -->
        <div class="d-none d-md-flex align-items-center gap-3">
            <img src="<?= htmlspecialchars($companyLogo) ?>" alt="Logo" class="img-fluid rounded" style="max-height: 40px; width: auto;" onerror="this.src='https://placehold.co/120x40/1b4d3e/ffffff?text=MESIGO';">
            <div class="border-start ps-3 py-1">
                <span class="d-block fw-bold text-dark mb-0" style="font-size: 0.95rem; line-height: 1.2;"><?= htmlspecialchars($companyInfo['company_name'] ?? 'MESIGO PRIVATE LIMITED') ?></span>
                <div class="d-flex align-items-center gap-2 text-muted small" style="font-size: 0.75rem;">
                    <span class="badge bg-secondary py-1"><?= htmlspecialchars($activeBranch['name'] ?? 'Head Office') ?></span>
                    <span>•</span>
                    <span>FY: <?= htmlspecialchars($activeFY['code'] ?? date('Y')) ?></span>
                </div>
            </div>
        </div>

        <!-- Right Side Nav -->
        <div class="collapse navbar-collapse" id="navbarControls">
            <ul class="navbar-nav ms-auto align-items-center gap-2">
                <!-- Search bar placeholder -->
                <li class="nav-item d-none d-lg-block me-2">
                    <form class="position-relative">
                        <input type="search" class="form-control form-control-sm bg-light border-0 ps-3 pe-4 py-2" placeholder="Search transactions..." style="border-radius: 20px; width: 220px;">
                        <i class="fas fa-search position-absolute text-muted" style="right: 12px; top: 12px; font-size: 0.85rem;"></i>
                    </form>
                </li>

                <!-- Quick Add Dropdown -->
                <li class="nav-item dropdown me-1">
                    <button class="btn btn-outline-primary btn-sm rounded-circle p-2 d-flex align-items-center justify-content-center" style="width:36px; height:36px;" data-bs-toggle="dropdown" title="Quick Add">
                        <i class="fas fa-plus"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                        <li class="dropdown-header fw-bold text-uppercase small text-muted">Quick Actions</li>
                        <li><a class="dropdown-item" href="/quotations/create"><i class="fas fa-file-invoice-dollar me-2 text-primary"></i>New Quotation</a></li>
                        <li><a class="dropdown-item" href="/proforma-invoices/create"><i class="fas fa-file-invoice me-2 text-primary"></i>New Proforma Invoice</a></li>
                        <li><a class="dropdown-item" href="/commercial-invoices/create"><i class="fas fa-file-signature me-2 text-primary"></i>New Commercial Invoice</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/buyers/create"><i class="fas fa-user-plus me-2 text-success"></i>Add Buyer</a></li>
                        <li><a class="dropdown-item" href="/suppliers/create"><i class="fas fa-truck me-2 text-success"></i>Add Supplier</a></li>
                        <li><a class="dropdown-item" href="/products/create"><i class="fas fa-cube me-2 text-success"></i>Add Product</a></li>
                    </ul>
                </li>

                <!-- Notifications Dropdown -->
                <li class="nav-item dropdown me-1">
                    <button class="btn btn-light btn-sm rounded-circle p-2 d-flex align-items-center justify-content-center position-relative" style="width:36px; height:36px;" data-bs-toggle="dropdown" title="Notifications">
                        <i class="fas fa-bell text-muted"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light" style="padding: 3px 6px; font-size: 0.65rem;">3</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" style="width: 280px;">
                        <li class="dropdown-header d-flex justify-content-between align-items-center fw-bold text-uppercase small text-muted">
                            <span>Notifications</span>
                            <a href="#" class="text-decoration-none small text-primary">Clear all</a>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <a class="dropdown-item py-2" href="/quotations">
                                <div class="d-flex align-items-start gap-2">
                                    <div class="bg-warning bg-opacity-10 text-warning rounded-circle p-1"><i class="fas fa-file-invoice small"></i></div>
                                    <div class="text-wrap">
                                        <p class="mb-0 small fw-semibold text-dark">Quotation QTN-26-0003 is pending approval</p>
                                        <span class="text-muted small" style="font-size: 0.75rem;">10 mins ago</span>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-2" href="/shipments">
                                <div class="d-flex align-items-start gap-2">
                                    <div class="bg-success bg-opacity-10 text-success rounded-circle p-1"><i class="fas fa-ship small"></i></div>
                                    <div class="text-wrap">
                                        <p class="mb-0 small fw-semibold text-dark">Shipment SHP-26-0002 has departed</p>
                                        <span class="text-muted small" style="font-size: 0.75rem;">1 hour ago</span>
                                    </div>
                                </div>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- User Dropdown -->
                <li class="nav-item dropdown">
                    <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle py-1 px-2 rounded-pill hover-bg-light" data-bs-toggle="dropdown">
                        <img src="<?= htmlspecialchars($userAvatar) ?>" alt="User avatar" class="rounded-circle border" style="width:34px; height:34px; object-fit: cover;">
                        <span class="d-none d-md-inline ms-2 fw-semibold text-dark" style="font-size: 0.875rem;"><?= htmlspecialchars($_SESSION['username'] ?? 'ERP User') ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                        <li class="dropdown-header">
                            <span class="d-block fw-bold text-dark"><?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></span>
                            <span class="d-block text-muted small"><?= htmlspecialchars($_SESSION['email'] ?? '') ?></span>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a href="/profile" class="dropdown-item"><i class="fas fa-id-card me-2 text-muted"></i>My Profile</a></li>
                        <li><a href="/settings" class="dropdown-item"><i class="fas fa-sliders-h me-2 text-muted"></i>System Configuration</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a href="/logout" class="dropdown-item text-danger"><i class="fas fa-sign-out-alt me-2"></i>Sign Out</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</header>