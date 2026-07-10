<?php
/**
 * MESIGO ERP - Sidebar Template
 */
?>
<nav class="sidebar bg-success text-white d-print-none" style="min-height: 100vh; width: 250px;">
    <div class="sidebar-header p-3">
        <h4 class="mb-0">MESIGO ERP</h4>
    </div>
    
    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="/dashboard" class="nav-link text-white <?= isActive('/dashboard') ? 'active' : '' ?>">
                <i class="fas fa-tachometer-alt me-2"></i>
                <span>Dashboard</span>
            </a>
        </li>
        
        <li class="nav-item">
            <a href="#crm" class="nav-link text-white collapsed" data-bs-toggle="collapse">
                <i class="fas fa-users me-2"></i>
                <span>CRM</span>
                <i class="fas fa-chevron-down ms-auto"></i>
            </a>
            <div class="collapse <?= isActive('/buyers') ? 'show' : '' ?>" id="crm">
                <ul class="nav flex-column ms-4">
                    <li class="nav-item">
                        <a href="/buyers" class="nav-link text-white <?= isActive('/buyers') ? 'active' : '' ?>">Buyer CRM</a>
                    </li>
                </ul>
            </div>
        </li>
        
        <li class="nav-item">
            <a href="#products" class="nav-link text-white collapsed" data-bs-toggle="collapse">
                <i class="fas fa-box me-2"></i>
                <span>Products</span>
                <i class="fas fa-chevron-down ms-auto"></i>
            </a>
            <div class="collapse <?= isActive('/products') ? 'show' : '' ?>" id="products">
                <ul class="nav flex-column ms-4">
                    <li class="nav-item">
                        <a href="/products" class="nav-link text-white <?= isActive('/products') ? 'active' : '' ?>">Product List</a>
                    </li>
                </ul>
            </div>
        </li>
        
        <li class="nav-item">
            <a href="#suppliers" class="nav-link text-white collapsed" data-bs-toggle="collapse">
                <i class="fas fa-truck-loading me-2"></i>
                <span>Suppliers</span>
                <i class="fas fa-chevron-down ms-auto"></i>
            </a>
            <div class="collapse <?= isActive('/suppliers') ? 'show' : '' ?>" id="suppliers">
                <ul class="nav flex-column ms-4">
                    <li class="nav-item">
                        <a href="/suppliers" class="nav-link text-white <?= isActive('/suppliers') ? 'active' : '' ?>">Supplier List</a>
                    </li>
                </ul>
            </div>
        </li>
        
        <li class="nav-item">
            <a href="#documents" class="nav-link text-white collapsed" data-bs-toggle="collapse">
                <i class="fas fa-file-export me-2"></i>
                <span>Export Workflow</span>
                <i class="fas fa-chevron-down ms-auto"></i>
            </a>
            <div class="collapse <?= isActive('/quotations') || isActive('/proforma-invoices') || isActive('/commercial-invoices') || isActive('/packing-lists') || isActive('/shipping-bills') || isActive('/bill-of-ladings') || isActive('/certificate-of-origins') || isActive('/export-documents') ? 'show' : '' ?>" id="documents">
                <ul class="nav flex-column ms-4">
                    <li class="nav-item">
                        <a href="/quotations" class="nav-link text-white <?= isActive('/quotations') ? 'active' : '' ?>">Quotations</a>
                    </li>
                    <li class="nav-item">
                        <a href="/proforma-invoices" class="nav-link text-white <?= isActive('/proforma-invoices') ? 'active' : '' ?>">Proforma Invoices</a>
                    </li>
                    <li class="nav-item">
                        <a href="/commercial-invoices" class="nav-link text-white <?= isActive('/commercial-invoices') ? 'active' : '' ?>">Commercial Invoices</a>
                    </li>
                    <li class="nav-item">
                        <a href="/packing-lists" class="nav-link text-white <?= isActive('/packing-lists') ? 'active' : '' ?>">Packing Lists</a>
                    </li>
                    <li class="nav-item">
                        <a href="/shipping-bills" class="nav-link text-white <?= isActive('/shipping-bills') ? 'active' : '' ?>">Shipping Bills</a>
                    </li>
                    <li class="nav-item">
                        <a href="/bill-of-ladings" class="nav-link text-white <?= isActive('/bill-of-ladings') ? 'active' : '' ?>">Bills of Lading</a>
                    </li>
                    <li class="nav-item">
                        <a href="/certificate-of-origins" class="nav-link text-white <?= isActive('/certificate-of-origins') ? 'active' : '' ?>">Certificates of Origin</a>
                    </li>
                    <li class="nav-item">
                        <a href="/export-documents" class="nav-link text-white <?= isActive('/export-documents') ? 'active' : '' ?>">Document Vault</a>
                    </li>
                    <li class="nav-item">
                        <a href="/reports" class="nav-link text-white <?= isActive('/reports') ? 'active' : '' ?>">Reports</a>
                    </li>
                </ul>
            </div>
        </li>
        
        <?php 
        $isMasterDataActive = (strpos($_SERVER['REQUEST_URI'], '/settings/master-data/') === 0);
        $isSystemSettingsActive = (isActive('/company') || isActive('/users') || isActive('/roles') || isActive('/permissions') || (isActive('/settings') && !$isMasterDataActive));
        ?>
        <li class="nav-item">
            <a href="#master_records" class="nav-link text-white <?= $isMasterDataActive ? '' : 'collapsed' ?>" data-bs-toggle="collapse">
                <i class="fas fa-database me-2"></i>
                <span>Master Records</span>
                <i class="fas fa-chevron-down ms-auto"></i>
            </a>
            <div class="collapse <?= $isMasterDataActive ? 'show' : '' ?>" id="master_records">
                <ul class="nav flex-column ms-4" style="max-height: 250px; overflow-y: auto;">
                    <li class="nav-item">
                        <a href="/settings/master-data/warehouses" class="nav-link text-white <?= isActive('/settings/master-data/warehouses') ? 'active' : '' ?>">Warehouses</a>
                    </li>
                    <li class="nav-item">
                        <a href="/settings/master-data/cost-components" class="nav-link text-white <?= isActive('/settings/master-data/cost-components') ? 'active' : '' ?>">Cost Components</a>
                    </li>
                    <li class="nav-item">
                        <a href="/settings/master-data/cost-templates" class="nav-link text-white <?= isActive('/settings/master-data/cost-templates') ? 'active' : '' ?>">Cost Templates</a>
                    </li>
                    <li class="nav-item">
                        <a href="/settings/master-data/currencies" class="nav-link text-white <?= isActive('/settings/master-data/currencies') ? 'active' : '' ?>">Currencies</a>
                    </li>
                    <li class="nav-item">
                        <a href="/settings/master-data/incoterms" class="nav-link text-white <?= isActive('/settings/master-data/incoterms') ? 'active' : '' ?>">Incoterms</a>
                    </li>
                    <li class="nav-item">
                        <a href="/settings/master-data/ports" class="nav-link text-white <?= isActive('/settings/master-data/ports') ? 'active' : '' ?>">Ports</a>
                    </li>
                    <li class="nav-item">
                        <a href="/settings/master-data/product-categories" class="nav-link text-white <?= isActive('/settings/master-data/product-categories') ? 'active' : '' ?>">Product Categories</a>
                    </li>
                    <li class="nav-item">
                        <a href="/settings/master-data/product-grades" class="nav-link text-white <?= isActive('/settings/master-data/product-grades') ? 'active' : '' ?>">Product Grades</a>
                    </li>
                    <li class="nav-item">
                        <a href="/settings/master-data/product-origins" class="nav-link text-white <?= isActive('/settings/master-data/product-origins') ? 'active' : '' ?>">Product Origins</a>
                    </li>
                    <li class="nav-item">
                        <a href="/settings/master-data/hs-codes" class="nav-link text-white <?= isActive('/settings/master-data/hs-codes') ? 'active' : '' ?>">HS Codes</a>
                    </li>
                    <li class="nav-item">
                        <a href="/settings/master-data/units" class="nav-link text-white <?= isActive('/settings/master-data/units') ? 'active' : '' ?>">Units</a>
                    </li>
                    <li class="nav-item">
                        <a href="/settings/master-data/packing-types" class="nav-link text-white <?= isActive('/settings/master-data/packing-types') ? 'active' : '' ?>">Packing Types</a>
                    </li>
                    <li class="nav-item">
                        <a href="/settings/master-data/countries" class="nav-link text-white <?= isActive('/settings/master-data/countries') ? 'active' : '' ?>">Countries</a>
                    </li>
                    <li class="nav-item">
                        <a href="/settings/master-data/container-types" class="nav-link text-white <?= isActive('/settings/master-data/container-types') ? 'active' : '' ?>">Container Types</a>
                    </li>
                    <li class="nav-item">
                        <a href="/settings/master-data/banks" class="nav-link text-white <?= isActive('/settings/master-data/banks') ? 'active' : '' ?>">Banks</a>
                    </li>
                    <li class="nav-item">
                        <a href="/settings/master-data/payment-terms" class="nav-link text-white <?= isActive('/settings/master-data/payment-terms') ? 'active' : '' ?>">Payment Terms</a>
                    </li>
                    <li class="nav-item">
                        <a href="/settings/master-data/shipping-terms" class="nav-link text-white <?= isActive('/settings/master-data/shipping-terms') ? 'active' : '' ?>">Shipping Terms</a>
                    </li>
                </ul>
            </div>
        </li>

        <li class="nav-item">
            <a href="#system_settings" class="nav-link text-white <?= $isSystemSettingsActive ? '' : 'collapsed' ?>" data-bs-toggle="collapse">
                <i class="fas fa-cogs me-2"></i>
                <span>System Settings</span>
                <i class="fas fa-chevron-down ms-auto"></i>
            </a>
            <div class="collapse <?= $isSystemSettingsActive ? 'show' : '' ?>" id="system_settings">
                <ul class="nav flex-column ms-4">
                    <li class="nav-item">
                        <a href="/company" class="nav-link text-white <?= isActive('/company') ? 'active' : '' ?>">Company Profile</a>
                    </li>
                    <li class="nav-item">
                        <a href="/users" class="nav-link text-white <?= isActive('/users') ? 'active' : '' ?>">Users</a>
                    </li>
                    <li class="nav-item">
                        <a href="/roles" class="nav-link text-white <?= isActive('/roles') ? 'active' : '' ?>">Roles</a>
                    </li>
                    <li class="nav-item">
                        <a href="/permissions" class="nav-link text-white <?= isActive('/permissions') ? 'active' : '' ?>">Permissions</a>
                    </li>
                    <li class="nav-item">
                        <a href="/settings" class="nav-link text-white <?= isActive('/settings') ? 'active' : '' ?>">Global Config</a>
                    </li>
                </ul>
            </div>
        </li>
    </ul>
</nav>