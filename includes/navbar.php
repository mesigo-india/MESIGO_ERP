<?php
/**
 * MESIGO ERP - Navbar Template
 */
?>
<header class="navbar navbar-expand navbar-light bg-white border-bottom">
    <div class="container-fluid">
        <button class="btn btn-outline-secondary d-md-none" type="button" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-2"></i>
                        <?= escapeHtml($_SESSION['username'] ?? 'User') ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a href="/profile" class="dropdown-item">
                                <i class="fas fa-user me-2"></i>Profile
                            </a>
                        </li>
                        <li>
                            <a href="/settings" class="dropdown-item">
                                <i class="fas fa-cog me-2"></i>Settings
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a href="/logout" class="dropdown-item">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</header>