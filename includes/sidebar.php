<?php
/**
 * MESIGO ERP - Reorganized Collapsible Sidebar Template
 */
use App\Core\Database;

$dbInst = Database::getInstance();
$comp = $dbInst->query("SELECT company_name FROM company WHERE status = 1 ORDER BY id ASC LIMIT 1")->fetchColumn();
$compName = $comp ? $comp : 'MESIGO ERP';
?>
<nav class="sidebar d-print-none min-vh-100 d-flex flex-column">
    <!-- Header -->
    <div class="sidebar-header px-4 py-3 d-flex align-items-center">
        <span class="text-white fw-bold h5 mb-0 tracking-wide"><i class="fas fa-cubes text-accent me-2"></i><?= htmlspecialchars($compName) ?></span>
    </div>
    
    <!-- Navigation Links -->
    <div class="flex-grow-1 overflow-y-auto py-3">
        <ul class="nav flex-column gap-1">
            
            <!-- Dashboard -->
            <li class="nav-item">
                <a href="/dashboard" class="nav-link <?= isActive('/dashboard') ? 'active' : '' ?>">
                    <i class="fas fa-chart-line"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            
            <!-- CRM Group -->
            <li class="nav-item">
                <a href="#crmGroup" class="nav-link <?= isActive('/buyers') ? '' : 'collapsed' ?>" data-bs-toggle="collapse">
                    <i class="fas fa-users-cog"></i>
                    <span>CRM Modules</span>
                    <i class="fas fa-chevron-down collapse-icon"></i>
                </a>
                <div class="collapse <?= isActive('/buyers') ? 'show' : '' ?>" id="crmGroup">
                    <ul class="submenu">
                        <li><a href="/buyers" class="nav-link <?= isActive('/buyers') ? 'active' : '' ?>"><i class="fas fa-user-friends small"></i> Buyer CRM</a></li>
                    </ul>
                </div>
            </li>
            
            <!-- Suppliers Group -->
            <li class="nav-item">
                <a href="#suppliersGroup" class="nav-link <?= isActive('/suppliers') ? '' : 'collapsed' ?>" data-bs-toggle="collapse">
                    <i class="fas fa-handshake"></i>
                    <span>Sourcing & Suppliers</span>
                    <i class="fas fa-chevron-down collapse-icon"></i>
                </a>
                <div class="collapse <?= isActive('/suppliers') ? 'show' : '' ?>" id="suppliersGroup">
                    <ul class="submenu">
                        <li><a href="/suppliers" class="nav-link <?= isActive('/suppliers') ? 'active' : '' ?>"><i class="fas fa-people-carry small"></i> Sourcing CRM</a></li>
                    </ul>
                </div>
            </li>
            
            <!-- Products Catalog -->
            <li class="nav-item">
                <a href="#productsCatalog" class="nav-link <?= isActive('/products') ? '' : 'collapsed' ?>" data-bs-toggle="collapse">
                    <i class="fas fa-boxes"></i>
                    <span>Product Catalog</span>
                    <i class="fas fa-chevron-down collapse-icon"></i>
                </a>
                <div class="collapse <?= isActive('/products') ? 'show' : '' ?>" id="productsCatalog">
                    <ul class="submenu">
                        <li><a href="/products" class="nav-link <?= isActive('/products') ? 'active' : '' ?>"><i class="fas fa-box-open small"></i> Products List</a></li>
                    </ul>
                </div>
            </li>
            
            <!-- Export Sales Workflow -->
            <?php 
            $isSalesActive = isActive('/quotations') || isActive('/proforma-invoices') || isActive('/commercial-invoices') || isActive('/packing-lists');
            ?>
            <li class="nav-item">
                <a href="#salesWorkflow" class="nav-link <?= $isSalesActive ? '' : 'collapsed' ?>" data-bs-toggle="collapse">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <span>Sales & Invoices</span>
                    <i class="fas fa-chevron-down collapse-icon"></i>
                </a>
                <div class="collapse <?= $isSalesActive ? 'show' : '' ?>" id="salesWorkflow">
                    <ul class="submenu">
                        <li><a href="/quotations" class="nav-link <?= isActive('/quotations') ? 'active' : '' ?>"><i class="fas fa-receipt small"></i> Quotations</a></li>
                        <li><a href="/proforma-invoices" class="nav-link <?= isActive('/proforma-invoices') ? 'active' : '' ?>"><i class="fas fa-file-invoice small"></i> Proforma Invoices</a></li>
                        <li><a href="/commercial-invoices" class="nav-link <?= isActive('/commercial-invoices') ? 'active' : '' ?>"><i class="fas fa-file-signature small"></i> Commercial Invoices</a></li>
                        <li><a href="/packing-lists" class="nav-link <?= isActive('/packing-lists') ? 'active' : '' ?>"><i class="fas fa-pallet small"></i> Packing Lists</a></li>
                    </ul>
                </div>
            </li>
            
            <!-- Shipping Documentation -->
            <?php 
            $isShippingActive = isActive('/shipping-bills') || isActive('/bill-of-ladings') || isActive('/certificate-of-origins') || isActive('/export-documents');
            ?>
            <li class="nav-item">
                <a href="#shippingDocs" class="nav-link <?= $isShippingActive ? '' : 'collapsed' ?>" data-bs-toggle="collapse">
                    <i class="fas fa-ship"></i>
                    <span>Logistics & Shipping</span>
                    <i class="fas fa-chevron-down collapse-icon"></i>
                </a>
                <div class="collapse <?= $isShippingActive ? 'show' : '' ?>" id="shippingDocs">
                    <ul class="submenu">
                        <li><a href="/shipping-bills" class="nav-link <?= isActive('/shipping-bills') ? 'active' : '' ?>"><i class="fas fa-passport small"></i> Shipping Bills</a></li>
                        <li><a href="/bill-of-ladings" class="nav-link <?= isActive('/bill-of-ladings') ? 'active' : '' ?>"><i class="fas fa-file-contract small"></i> Bills of Lading</a></li>
                        <li><a href="/certificate-of-origins" class="nav-link <?= isActive('/certificate-of-origins') ? 'active' : '' ?>"><i class="fas fa-certificate small"></i> Certificates of Origin</a></li>
                        <li><a href="/export-documents" class="nav-link <?= isActive('/export-documents') ? 'active' : '' ?>"><i class="fas fa-vault small"></i> Document Vault</a></li>
                    </ul>
                </div>
            </li>
            
            <!-- Reports -->
            <li class="nav-item">
                <a href="/reports" class="nav-link <?= isActive('/reports') ? 'active' : '' ?>">
                    <i class="fas fa-chart-pie"></i>
                    <span>Profitability Reports</span>
                </a>
            </li>

            <!-- Master Data Tables -->
            <?php 
            $isMasterActive = (strpos($_SERVER['REQUEST_URI'], '/settings/master-data/') === 0);
            ?>
            <li class="nav-item">
                <a href="#masterRecords" class="nav-link <?= $isMasterActive ? '' : 'collapsed' ?>" data-bs-toggle="collapse">
                    <i class="fas fa-database"></i>
                    <span>Master Records</span>
                    <i class="fas fa-chevron-down collapse-icon"></i>
                </a>
                <div class="collapse <?= $isMasterActive ? 'show' : '' ?>" id="masterRecords">
                    <ul class="submenu" style="max-height: 250px; overflow-y: auto;">
                        <li><a href="/settings/master-data/warehouses" class="nav-link <?= isActive('/settings/master-data/warehouses') ? 'active' : '' ?>"><i class="fas fa-warehouse small"></i> Warehouses</a></li>
                        <li><a href="/settings/master-data/cost-components" class="nav-link <?= isActive('/settings/master-data/cost-components') ? 'active' : '' ?>"><i class="fas fa-calculator small"></i> Cost Components</a></li>
                        <li><a href="/settings/master-data/cost-templates" class="nav-link <?= isActive('/settings/master-data/cost-templates') ? 'active' : '' ?>"><i class="fas fa-file-code small"></i> Cost Templates</a></li>
                        <li><a href="/settings/master-data/currencies" class="nav-link <?= isActive('/settings/master-data/currencies') ? 'active' : '' ?>"><i class="fas fa-coins small"></i> Currencies</a></li>
                        <li><a href="/settings/master-data/incoterms" class="nav-link <?= isActive('/settings/master-data/incoterms') ? 'active' : '' ?>"><i class="fas fa-handshake-alt small"></i> Incoterms</a></li>
                        <li><a href="/settings/master-data/ports" class="nav-link <?= isActive('/settings/master-data/ports') ? 'active' : '' ?>"><i class="fas fa-anchor small"></i> Ports</a></li>
                        <li><a href="/settings/master-data/product-categories" class="nav-link <?= isActive('/settings/master-data/product-categories') ? 'active' : '' ?>"><i class="fas fa-tags small"></i> Product Categories</a></li>
                        <li><a href="/settings/master-data/product-grades" class="nav-link <?= isActive('/settings/master-data/product-grades') ? 'active' : '' ?>"><i class="fas fa-layer-group small"></i> Product Grades</a></li>
                        <li><a href="/settings/master-data/product-origins" class="nav-link <?= isActive('/settings/master-data/product-origins') ? 'active' : '' ?>"><i class="fas fa-globe small"></i> Product Origins</a></li>
                        <li><a href="/settings/master-data/hs-codes" class="nav-link <?= isActive('/settings/master-data/hs-codes') ? 'active' : '' ?>"><i class="fas fa-barcode small"></i> HS Codes</a></li>
                        <li><a href="/settings/master-data/units" class="nav-link <?= isActive('/settings/master-data/units') ? 'active' : '' ?>"><i class="fas fa-weight-hanging small"></i> Units</a></li>
                        <li><a href="/settings/master-data/packing-types" class="nav-link <?= isActive('/settings/master-data/packing-types') ? 'active' : '' ?>"><i class="fas fa-box small"></i> Packing Types</a></li>
                        <li><a href="/settings/master-data/countries" class="nav-link <?= isActive('/settings/master-data/countries') ? 'active' : '' ?>"><i class="fas fa-flag small"></i> Countries</a></li>
                        <li><a href="/settings/master-data/container-types" class="nav-link <?= isActive('/settings/master-data/container-types') ? 'active' : '' ?>"><i class="fas fa-truck-container small"></i> Container Types</a></li>
                        <li><a href="/settings/master-data/banks" class="nav-link <?= isActive('/settings/master-data/banks') ? 'active' : '' ?>"><i class="fas fa-piggy-bank small"></i> Banks</a></li>
                        <li><a href="/settings/master-data/payment-terms" class="nav-link <?= isActive('/settings/master-data/payment-terms') ? 'active' : '' ?>"><i class="fas fa-credit-card small"></i> Payment Terms</a></li>
                        <li><a href="/settings/master-data/shipping-terms" class="nav-link <?= isActive('/settings/master-data/shipping-terms') ? 'active' : '' ?>"><i class="fas fa-shipping-fast small"></i> Shipping Terms</a></li>
                    </ul>
                </div>
            </li>

            <!-- System Administration -->
            <?php 
            $isAdminActive = isActive('/company') || isActive('/users') || isActive('/roles') || isActive('/permissions') || (isActive('/settings') && !$isMasterActive);
            ?>
            <li class="nav-item">
                <a href="#adminGroup" class="nav-link <?= $isAdminActive ? '' : 'collapsed' ?>" data-bs-toggle="collapse">
                    <i class="fas fa-cogs"></i>
                    <span>Administration</span>
                    <i class="fas fa-chevron-down collapse-icon"></i>
                </a>
                <div class="collapse <?= $isAdminActive ? 'show' : '' ?>" id="adminGroup">
                    <ul class="submenu">
                        <li><a href="/company" class="nav-link <?= isActive('/company') ? 'active' : '' ?>"><i class="fas fa-building small"></i> Company Profile</a></li>
                        <li><a href="/users" class="nav-link <?= isActive('/users') ? 'active' : '' ?>"><i class="fas fa-users-cog small"></i> User Accounts</a></li>
                        <li><a href="/roles" class="nav-link <?= isActive('/roles') ? 'active' : '' ?>"><i class="fas fa-shield-alt small"></i> Security Roles</a></li>
                        <li><a href="/permissions" class="nav-link <?= isActive('/permissions') ? 'active' : '' ?>"><i class="fas fa-key small"></i> Permissions matrix</a></li>
                        <li><a href="/settings" class="nav-link <?= (isActive('/settings') && !$isMasterActive) ? 'active' : '' ?>"><i class="fas fa-sliders-h small"></i> Global Config</a></li>
                    </ul>
                </div>
            </li>
        </ul>
    </div>
</nav>