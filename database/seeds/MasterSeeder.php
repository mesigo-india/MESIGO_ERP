<?php
declare(strict_types=1);

namespace App\Core\Seeds;

use PDO;

class MasterSeeder extends Seeder
{
    public function run(): void
    {
        $this->log("Starting Master Seeder...");

        // 1. Roles
        $roles = [
            ['name' => 'admin', 'display_name' => 'Administrator', 'permissions' => json_encode(['all']), 'status' => 1],
            ['name' => 'manager', 'display_name' => 'Export Manager', 'permissions' => json_encode(['view', 'create', 'update']), 'status' => 1],
            ['name' => 'executive', 'display_name' => 'Documentation Executive', 'permissions' => json_encode(['view', 'create']), 'status' => 1]
        ];
        $roleIds = [];
        foreach ($roles as $role) {
            $roleIds[$role['name']] = $this->upsert('roles', $role, ['name']);
        }
        $this->log("Seeded Roles.");

        // 2. Permissions
        $permissions = [
            ['name' => 'settings.view', 'display_name' => 'View Settings', 'description' => 'View settings', 'module' => 'settings', 'status' => 1],
            ['name' => 'settings.create', 'display_name' => 'Create Settings', 'description' => 'Create settings', 'module' => 'settings', 'status' => 1],
            ['name' => 'settings.update', 'display_name' => 'Update Settings', 'description' => 'Update settings', 'module' => 'settings', 'status' => 1],
            ['name' => 'settings.delete', 'display_name' => 'Delete Settings', 'description' => 'Delete settings', 'module' => 'settings', 'status' => 1],
            ['name' => 'users.view', 'display_name' => 'View Users', 'description' => 'View users', 'module' => 'users', 'status' => 1],
            ['name' => 'users.create', 'display_name' => 'Create Users', 'description' => 'Create users', 'module' => 'users', 'status' => 1],
            ['name' => 'users.update', 'display_name' => 'Update Users', 'description' => 'Update users', 'module' => 'users', 'status' => 1],
            ['name' => 'users.delete', 'display_name' => 'Delete Users', 'description' => 'Delete users', 'module' => 'users', 'status' => 1],
            ['name' => 'products.view', 'display_name' => 'View Products', 'description' => 'View products', 'module' => 'products', 'status' => 1],
            ['name' => 'products.create', 'display_name' => 'Create Products', 'description' => 'Create products', 'module' => 'products', 'status' => 1],
            ['name' => 'products.update', 'display_name' => 'Update Products', 'description' => 'Update products', 'module' => 'products', 'status' => 1],
            ['name' => 'products.delete', 'display_name' => 'Delete Products', 'description' => 'Delete products', 'module' => 'products', 'status' => 1],
            ['name' => 'buyers.view', 'display_name' => 'View Buyers', 'description' => 'View buyers', 'module' => 'buyers', 'status' => 1],
            ['name' => 'buyers.create', 'display_name' => 'Create Buyers', 'description' => 'Create buyers', 'module' => 'buyers', 'status' => 1],
            ['name' => 'buyers.update', 'display_name' => 'Update Buyers', 'description' => 'Update buyers', 'module' => 'buyers', 'status' => 1],
            ['name' => 'buyers.delete', 'display_name' => 'Delete Buyers', 'description' => 'Delete buyers', 'module' => 'buyers', 'status' => 1],
            ['name' => 'suppliers.view', 'display_name' => 'View Suppliers', 'description' => 'View suppliers', 'module' => 'suppliers', 'status' => 1],
            ['name' => 'suppliers.create', 'display_name' => 'Create Suppliers', 'description' => 'Create suppliers', 'module' => 'suppliers', 'status' => 1],
            ['name' => 'suppliers.update', 'display_name' => 'Update Suppliers', 'description' => 'Update suppliers', 'module' => 'suppliers', 'status' => 1],
            ['name' => 'suppliers.delete', 'display_name' => 'Delete Suppliers', 'description' => 'Delete suppliers', 'module' => 'suppliers', 'status' => 1]
        ];
        foreach ($permissions as $perm) {
            $this->upsert('permissions', $perm, ['name']);
        }
        $this->log("Seeded Permissions.");

        // 3. Users
        $passwordHash = password_hash('password123', PASSWORD_BCRYPT);
        $users = [
            ['username' => 'admin', 'first_name' => 'System', 'last_name' => 'Admin', 'email' => 'admin@mesigo.com', 'password' => $passwordHash, 'role_id' => $roleIds['admin'], 'status' => 1],
            ['username' => 'manager', 'first_name' => 'Export', 'last_name' => 'Manager', 'email' => 'manager@mesigo.com', 'password' => $passwordHash, 'role_id' => $roleIds['manager'], 'status' => 1],
            ['username' => 'executive', 'first_name' => 'Doc', 'last_name' => 'Executive', 'email' => 'executive@mesigo.com', 'password' => $passwordHash, 'role_id' => $roleIds['executive'], 'status' => 1]
        ];
        $userIds = [];
        foreach ($users as $user) {
            $userIds[$user['username']] = $this->upsert('users', $user, ['username']);
            // Add user_roles connection
            $roleName = $user['username'] === 'admin' ? 'admin' : ($user['username'] === 'manager' ? 'manager' : 'executive');
            $roleId = $roleIds[$roleName];
            $this->db->exec("INSERT IGNORE INTO `user_roles` (`user_id`, `role_id`) VALUES ({$userIds[$user['username']]}, {$roleId})");
        }
        $this->log("Seeded Users and Roles mapping.");

        // 4. Countries, States, Cities
        $countries = [
            'IN' => ['name' => 'India', 'code' => 'IN', 'status' => 1],
            'AE' => ['name' => 'United Arab Emirates', 'code' => 'AE', 'status' => 1],
            'SA' => ['name' => 'Saudi Arabia', 'code' => 'SA', 'status' => 1],
            'DE' => ['name' => 'Germany', 'code' => 'DE', 'status' => 1],
            'NL' => ['name' => 'Netherlands', 'code' => 'NL', 'status' => 1],
            'GB' => ['name' => 'United Kingdom', 'code' => 'GB', 'status' => 1],
            'US' => ['name' => 'United States', 'code' => 'US', 'status' => 1],
            'CA' => ['name' => 'Canada', 'code' => 'CA', 'status' => 1],
            'AU' => ['name' => 'Australia', 'code' => 'AU', 'status' => 1],
            'MY' => ['name' => 'Malaysia', 'code' => 'MY', 'status' => 1],
            'VN' => ['name' => 'Vietnam', 'code' => 'VN', 'status' => 1]
        ];
        $countryIds = [];
        foreach ($countries as $code => $country) {
            $countryIds[$code] = $this->upsert('countries', $country, ['code']);
        }
        $this->log("Seeded Countries.");

        // States
        $states = [
            'IN_GJ' => ['country_id' => $countryIds['IN'], 'name' => 'Gujarat', 'code' => 'GJ', 'status' => 1],
            'IN_MH' => ['country_id' => $countryIds['IN'], 'name' => 'Maharashtra', 'code' => 'MH', 'status' => 1],
            'IN_MP' => ['country_id' => $countryIds['IN'], 'name' => 'Madhya Pradesh', 'code' => 'MP', 'status' => 1],
            'AE_DU' => ['country_id' => $countryIds['AE'], 'name' => 'Dubai', 'code' => 'DU', 'status' => 1],
            'SA_RI' => ['country_id' => $countryIds['SA'], 'name' => 'Riyadh', 'code' => 'RI', 'status' => 1],
            'DE_HH' => ['country_id' => $countryIds['DE'], 'name' => 'Hamburg', 'code' => 'HH', 'status' => 1],
            'NL_ZH' => ['country_id' => $countryIds['NL'], 'name' => 'Zuid-Holland', 'code' => 'ZH', 'status' => 1],
            'GB_ENG'=> ['country_id' => $countryIds['GB'], 'name' => 'England', 'code' => 'ENG', 'status' => 1],
            'US_CA' => ['country_id' => $countryIds['US'], 'name' => 'California', 'code' => 'CA', 'status' => 1],
            'CA_ON' => ['country_id' => $countryIds['CA'], 'name' => 'Ontario', 'code' => 'ON', 'status' => 1],
            'AU_VIC' => ['country_id' => $countryIds['AU'], 'name' => 'Victoria', 'code' => 'VIC', 'status' => 1],
            'MY_PEN' => ['country_id' => $countryIds['MY'], 'name' => 'Penang', 'code' => 'PEN', 'status' => 1],
            'VN_HC'  => ['country_id' => $countryIds['VN'], 'name' => 'Ho Chi Minh', 'code' => 'HC', 'status' => 1]
        ];
        $stateIds = [];
        foreach ($states as $key => $state) {
            $stateIds[$key] = $this->upsert('states', $state, ['country_id', 'code']);
        }
        $this->log("Seeded States.");

        // Cities
        $cities = [
            ['state_id' => $stateIds['IN_GJ'], 'name' => 'Ahmedabad', 'status' => 1],
            ['state_id' => $stateIds['IN_GJ'], 'name' => 'Unjha', 'status' => 1],
            ['state_id' => $stateIds['IN_GJ'], 'name' => 'Rajkot', 'status' => 1],
            ['state_id' => $stateIds['IN_GJ'], 'name' => 'Mundra', 'status' => 1],
            ['state_id' => $stateIds['IN_MH'], 'name' => 'Nashik', 'status' => 1],
            ['state_id' => $stateIds['IN_MP'], 'name' => 'Indore', 'status' => 1],
            ['state_id' => $stateIds['AE_DU'], 'name' => 'Dubai', 'status' => 1],
            ['state_id' => $stateIds['SA_RI'], 'name' => 'Riyadh', 'status' => 1],
            ['state_id' => $stateIds['DE_HH'], 'name' => 'Hamburg', 'status' => 1],
            ['state_id' => $stateIds['NL_ZH'], 'name' => 'Rotterdam', 'status' => 1],
            ['state_id' => $stateIds['GB_ENG'], 'name' => 'London', 'status' => 1],
            ['state_id' => $stateIds['US_CA'], 'name' => 'San Francisco', 'status' => 1],
            ['state_id' => $stateIds['CA_ON'], 'name' => 'Toronto', 'status' => 1],
            ['state_id' => $stateIds['AU_VIC'], 'name' => 'Melbourne', 'status' => 1],
            ['state_id' => $stateIds['MY_PEN'], 'name' => 'Penang', 'status' => 1],
            ['state_id' => $stateIds['VN_HC'], 'name' => 'Ho Chi Minh City', 'status' => 1]
        ];
        foreach ($cities as $city) {
            $this->upsert('cities', $city, ['state_id', 'name']);
        }
        $this->log("Seeded Cities.");

        // 5. Ports
        $ports = [
            ['name' => 'Mundra Port', 'code' => 'INMUN', 'type' => 'sea', 'status' => 1],
            ['name' => 'Nhava Sheva Port', 'code' => 'INNSA', 'type' => 'sea', 'status' => 1],
            ['name' => 'Jebel Ali Port', 'code' => 'AEJEA', 'type' => 'sea', 'status' => 1],
            ['name' => 'Hamburg Port', 'code' => 'DEHAM', 'type' => 'sea', 'status' => 1],
            ['name' => 'Rotterdam Port', 'code' => 'NLRTM', 'type' => 'sea', 'status' => 1],
            ['name' => 'Oakland Port', 'code' => 'USOAK', 'type' => 'sea', 'status' => 1]
        ];
        $portIds = [];
        foreach ($ports as $port) {
            $portIds[$port['code']] = $this->upsert('ports', $port, ['code']);
        }
        $this->log("Seeded Ports.");

        // 6. Companies & Warehousing
        $company = [
            'company_name' => 'MESIGO India Agricultural Exports Pvt Ltd',
            'address' => json_encode(['street' => '501-505, Agro Business Hub, SG Highway', 'city' => 'Ahmedabad', 'state' => 'Gujarat', 'country' => 'India', 'zip' => '380001']),
            'contact_person' => 'Kamlesh Shah',
            'gst_number' => '24AAACM1234F1Z0',
            'iec_code' => 'U01100GJ2026PTC123456',
            'email' => 'exports@mesigo.com',
            'phone' => '+91-79-40012345',
            'status' => 1
        ];
        $companyId = $this->upsert('company', $company, ['company_name']);

        // Link User-Companies
        foreach ($userIds as $uname => $uid) {
            $this->db->exec("INSERT IGNORE INTO `user_companies` (`user_id`, `company_id`) VALUES ({$uid}, {$companyId})");
        }

        $branches = [
            ['company_id' => $companyId, 'branch_name' => 'Ahmedabad Head Office', 'branch_code' => 'BR_AMD', 'status' => 1],
            ['company_id' => $companyId, 'branch_name' => 'Unjha Processing Unit', 'branch_code' => 'BR_UNJ', 'status' => 1],
            ['company_id' => $companyId, 'branch_name' => 'Mundra Port Depot', 'branch_code' => 'BR_MUN', 'status' => 1]
        ];
        $branchIds = [];
        foreach ($branches as $branch) {
            $branchIds[$branch['branch_code']] = $this->upsert('branches', $branch, ['branch_code']);
        }

        $warehouses = [
            ['branch_id' => $branchIds['BR_AMD'], 'name' => 'Ahmedabad Central Warehouse', 'code' => 'WH_AMD', 'status' => 1],
            ['branch_id' => $branchIds['BR_UNJ'], 'name' => 'Unjha Processing Warehouse', 'code' => 'WH_UNJ', 'status' => 1],
            ['branch_id' => $branchIds['BR_MUN'], 'name' => 'Mundra Port Transit Depot', 'code' => 'WH_MUN', 'status' => 1]
        ];
        $warehouseIds = [];
        foreach ($warehouses as $wh) {
            $warehouseIds[$wh['code']] = $this->upsert('warehouses', $wh, ['code']);
        }

        // Warehouse Locations
        $locations = [
            ['warehouse_id' => $warehouseIds['WH_AMD'], 'name' => 'General Raw Area A', 'code' => 'AMD-RAW-A', 'status' => 1],
            ['warehouse_id' => $warehouseIds['WH_UNJ'], 'name' => 'Processing Sorting Bin 1', 'code' => 'UNJ-BIN-1', 'status' => 1],
            ['warehouse_id' => $warehouseIds['WH_UNJ'], 'name' => 'Cold Section Storage B', 'code' => 'UNJ-COLD-B', 'status' => 1],
            ['warehouse_id' => $warehouseIds['WH_MUN'], 'name' => 'Transit Loading Dock 1', 'code' => 'MUN-DOCK-1', 'status' => 1]
        ];
        foreach ($locations as $loc) {
            $this->upsert('warehouse_locations', $loc, ['code']);
        }
        $this->log("Seeded Companies, Branches, and Warehousing.");

        // 7. Banks
        $banks = [
            ['name' => 'State Bank of India', 'code' => 'SBI', 'branch' => 'Commercial Branch, Ahmedabad', 'ifsc_code' => 'SBIN0001234', 'address' => 'SBI Towers, Lal Darwaja, Ahmedabad', 'status' => 1],
            ['name' => 'HDFC Bank', 'code' => 'HDFC', 'branch' => 'Agro Center, Unjha', 'ifsc_code' => 'HDFC0004567', 'address' => 'Ganj Bazar Road, Unjha', 'status' => 1],
            ['name' => 'ICICI Bank', 'code' => 'ICICI', 'branch' => 'Overseas Branch, Mumbai', 'ifsc_code' => 'ICIC0007890', 'address' => 'BKC, Bandra East, Mumbai', 'status' => 1]
        ];
        foreach ($banks as $bank) {
            $this->upsert('banks', $bank, ['code']);
        }
        $this->log("Seeded Banks.");

        // 8. Payment Terms & Incoterms & Shipping lines
        $paymentTerms = [
            ['name' => 'Advance 30% / Balance 70% against BL scan', 'code' => '30ADV_70CAD', 'days' => 30, 'description' => '30% advance deposit, remaining 70% CAD against presentation of BL scan.', 'status' => 1],
            ['name' => 'Irrevocable LC at Sight', 'code' => 'LC_SIGHT', 'days' => 0, 'description' => 'Irrevocable letter of credit payable at sight.', 'status' => 1],
            ['name' => 'Cash Against Documents (CAD)', 'code' => 'CAD', 'days' => 10, 'description' => 'Payment upon presentation of original documents to buyer bank.', 'status' => 1]
        ];
        foreach ($paymentTerms as $term) {
            $this->upsert('payment_terms', $term, ['code']);
        }

        $incoterms = [
            ['code' => 'FOB', 'name' => 'Free On Board', 'description' => 'Seller pays for local logistics to port; Buyer pays ocean freight and insurance.', 'status' => 1],
            ['code' => 'CIF', 'name' => 'Cost, Insurance and Freight', 'description' => 'Seller pays freight and maritime insurance to destination port.', 'status' => 1],
            ['code' => 'CFR', 'name' => 'Cost and Freight', 'description' => 'Seller pays ocean freight to destination; Buyer covers insurance.', 'status' => 1]
        ];
        $incotermIds = [];
        foreach ($incoterms as $ico) {
            $incotermIds[$ico['code']] = $this->upsert('incoterms', $ico, ['code']);
        }

        $shippingLines = [
            ['name' => 'Maersk Line', 'code' => 'MAERSK', 'status' => 1],
            ['name' => 'Mediterranean Shipping Company', 'code' => 'MSC', 'status' => 1],
            ['name' => 'CMA CGM', 'code' => 'CMACGM', 'status' => 1]
        ];
        foreach ($shippingLines as $line) {
            $this->upsert('shipping_lines', $line, ['code']);
        }
        $this->log("Seeded Sourcing, Payment, and Incoterms.");

        // 9. Number Series configurations
        $numberSeries = [
            ['name' => 'Quotation Series', 'prefix' => 'QTN-26-', 'next_number' => 1, 'suffix' => '', 'padding' => 4, 'status' => 1],
            ['name' => 'Proforma Invoice Series', 'prefix' => 'PI-26-', 'next_number' => 1, 'suffix' => '', 'padding' => 4, 'status' => 1],
            ['name' => 'Commercial Invoice Series', 'prefix' => 'INV-26-', 'next_number' => 1, 'suffix' => '', 'padding' => 4, 'status' => 1],
            ['name' => 'Packing List Series', 'prefix' => 'PKG-26-', 'next_number' => 1, 'suffix' => '', 'padding' => 4, 'status' => 1]
        ];
        foreach ($numberSeries as $ns) {
            $this->upsert('number_series', $ns, ['name']);
        }

        // 10. Expense Categories & Cost Components
        $expenseCategories = [
            ['name' => 'Procurement Outlays', 'code' => 'EXP_PROC', 'status' => 1],
            ['name' => 'Local Freight & Transport', 'code' => 'EXP_FOB_LOC', 'status' => 1],
            ['name' => 'Ocean Freight', 'code' => 'EXP_OCEAN', 'status' => 1],
            ['name' => 'CHA Handling & Customs Clearance', 'code' => 'EXP_CHA', 'status' => 1],
            ['name' => 'Quality Certs & Tests', 'code' => 'EXP_TEST', 'status' => 1],
            ['name' => 'Financial Fees', 'code' => 'EXP_FINANCE', 'status' => 1]
        ];
        foreach ($expenseCategories as $ec) {
            $this->upsert('expense_categories', $ec, ['code']);
        }

        $costComponents = [
            ['code' => 'PRODUCT_COST', 'name' => 'Base Product Cost', 'category' => 'procurement', 'calculation_type' => 'per_unit_qty', 'default_value' => 0.00, 'default_currency_id' => 1, 'status' => 1],
            ['code' => 'OCEAN_FREIGHT', 'name' => 'Ocean Freight Charges', 'category' => 'logistics_intl', 'calculation_type' => 'per_container', 'default_value' => 1200.00, 'default_currency_id' => 2, 'status' => 1], // USD defaults
            ['code' => 'CHA_CHARGES', 'name' => 'Customs Handling Agency Fee', 'category' => 'logistics_local', 'calculation_type' => 'flat', 'default_value' => 15000.00, 'default_currency_id' => 1, 'status' => 1], // INR defaults
            ['code' => 'FUMIGATION_FEE', 'name' => 'Fumigation & Chemical Spray', 'category' => 'documentation', 'calculation_type' => 'per_container', 'default_value' => 6000.00, 'default_currency_id' => 1, 'status' => 1],
            ['code' => 'DOCUMENTATION_FEE', 'name' => 'Certificate & Courier Fee', 'category' => 'documentation', 'calculation_type' => 'flat', 'default_value' => 5000.00, 'default_currency_id' => 1, 'status' => 1]
        ];
        $componentIds = [];
        foreach ($costComponents as $cc) {
            $componentIds[$cc['code']] = $this->upsert('cost_components', $cc, ['code']);
        }

        // Cost Templates & Items
        $templates = [
            ['company_id' => $companyId, 'name' => 'FOB Mundra Standard Spices', 'description' => 'FOB template including local CHA, loading, and documentation.', 'incoterm_id' => $incotermIds['FOB'], 'destination_port_id' => $portIds['AEJEA'], 'status' => 1],
            ['company_id' => $companyId, 'name' => 'CIF Hamburg Premium Seeds', 'description' => 'CIF template including ocean freight, transit insurance, and local charges.', 'incoterm_id' => $incotermIds['CIF'], 'destination_port_id' => $portIds['DEHAM'], 'status' => 1]
        ];
        foreach ($templates as $temp) {
            $tempId = $this->upsert('cost_templates', $temp, ['name']);
            // Add costing lines
            if ($temp['name'] === 'FOB Mundra Standard Spices') {
                $this->upsert('cost_template_items', ['cost_template_id' => $tempId, 'cost_component_id' => $componentIds['CHA_CHARGES'], 'amount' => 18000.00, 'currency_id' => 1], ['cost_template_id', 'cost_component_id']);
                $this->upsert('cost_template_items', ['cost_template_id' => $tempId, 'cost_component_id' => $componentIds['DOCUMENTATION_FEE'], 'amount' => 4500.00, 'currency_id' => 1], ['cost_template_id', 'cost_component_id']);
            } else {
                $this->upsert('cost_template_items', ['cost_template_id' => $tempId, 'cost_component_id' => $componentIds['OCEAN_FREIGHT'], 'amount' => 1500.00, 'currency_id' => 2], ['cost_template_id', 'cost_component_id']);
                $this->upsert('cost_template_items', ['cost_template_id' => $tempId, 'cost_component_id' => $componentIds['CHA_CHARGES'], 'amount' => 20000.00, 'currency_id' => 1], ['cost_template_id', 'cost_component_id']);
            }
        }
        $this->log("Seeded Cost components and Cost templates.");

        // 11. Inspection Agencies
        $inspectionAgencies = [
            ['agency_name' => 'SGS India Pvt Ltd', 'agency_code' => 'SGS', 'status' => 1],
            ['agency_name' => 'Geo-Chem Laboratories', 'agency_code' => 'GEOCHEM', 'status' => 1],
            ['agency_name' => 'QSS Inspection Services', 'agency_code' => 'QSS', 'status' => 1]
        ];
        foreach ($inspectionAgencies as $agency) {
            $this->upsert('inspection_agencies', $agency, ['agency_code']);
        }
        $this->log("Seeded Inspection Agencies.");

        // 12. Settings Setup
        $settings = [
            ['key' => 'company.default_id', 'value' => (string)$companyId, 'type' => 'string', 'group' => 'general', 'status' => 1],
            ['key' => 'currency.base_code', 'value' => 'INR', 'type' => 'string', 'group' => 'currency', 'status' => 1],
            ['key' => 'tax.default_lut_active', 'value' => '1', 'type' => 'string', 'group' => 'tax', 'status' => 1]
        ];
        foreach ($settings as $set) {
            $this->upsert('settings', $set, ['key']);
        }
        $this->log("Seeded Settings.");

        $this->log("Master Seeder completed successfully!");
    }
}
