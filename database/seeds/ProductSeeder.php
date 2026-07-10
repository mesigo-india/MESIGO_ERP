<?php
declare(strict_types=1);

namespace App\Core\Seeds;

use PDO;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $this->log("Starting Product Seeder...");

        // 1. Categories
        $categories = [
            ['name' => 'Spices', 'code' => 'SPICE', 'description' => 'Raw and processed spice commodities', 'status' => 1],
            ['name' => 'Oil Seeds', 'code' => 'SEED', 'description' => 'Agri oil seeds', 'status' => 1],
            ['name' => 'Pulses & Grains', 'code' => 'PULSE', 'description' => 'Indian pulses and grains', 'status' => 1],
            ['name' => 'Dehydrated Vegetables', 'code' => 'DEHY', 'description' => 'Dehydrated onions, garlic, etc.', 'status' => 1],
            ['name' => 'Herbs & Medicinals', 'code' => 'HERB', 'description' => 'Psyllium husk, senna leaves, etc.', 'status' => 1]
        ];
        $categoryIds = [];
        foreach ($categories as $cat) {
            $categoryIds[$cat['code']] = $this->upsert('product_categories', $cat, ['code']);
        }

        // 2. Varieties
        $varieties = [
            ['category_id' => $categoryIds['SPICE'], 'name' => 'Cumin Seeds', 'code' => 'CUMIN', 'description' => 'Jeera seeds', 'status' => 1],
            ['category_id' => $categoryIds['SPICE'], 'name' => 'Fennel Seeds', 'code' => 'FENNEL', 'description' => 'Saunf seeds', 'status' => 1],
            ['category_id' => $categoryIds['SPICE'], 'name' => 'Coriander Seeds', 'code' => 'CORIANDER', 'description' => 'Dhania seeds', 'status' => 1],
            ['category_id' => $categoryIds['SPICE'], 'name' => 'Turmeric Finger', 'code' => 'TURMERIC', 'description' => 'Haldi fingers', 'status' => 1],
            ['category_id' => $categoryIds['SEED'], 'name' => 'Sesame Seeds', 'code' => 'SESAME', 'description' => 'Sesamum indicum', 'status' => 1],
            ['category_id' => $categoryIds['SEED'], 'name' => 'Groundnut Kernels', 'code' => 'GROUNDNUT', 'description' => 'Peanut kernels', 'status' => 1],
            ['category_id' => $categoryIds['DEHY'], 'name' => 'Dehydrated Onion Flakes', 'code' => 'ONION_FLAKE', 'description' => 'Dehy onion flakes', 'status' => 1],
            ['category_id' => $categoryIds['DEHY'], 'name' => 'Dehydrated Garlic Flakes', 'code' => 'GARLIC_FLAKE', 'description' => 'Dehy garlic flakes', 'status' => 1],
            ['category_id' => $categoryIds['HERB'], 'name' => 'Psyllium Husk', 'code' => 'PSYLLIUM', 'description' => 'Isabgol husk', 'status' => 1],
            ['category_id' => $categoryIds['SEED'], 'name' => 'Castor Seeds', 'code' => 'CASTOR', 'description' => 'Castor oil seed', 'status' => 1]
        ];
        $varietyIds = [];
        foreach ($varieties as $var) {
            $varietyIds[$var['code']] = $this->upsert('product_varieties', $var, ['code']);
        }

        // 3. Grades
        $grades = [
            ['name' => 'Sortex Cleaned 99.5%', 'code' => 'SORTEX_995', 'description' => 'Sortex cleaned premium quality', 'status' => 1],
            ['name' => 'Machine Cleaned 99%', 'code' => 'MC_99', 'description' => 'Machine cleaned standard export', 'status' => 1],
            ['name' => 'Salem Double Parrot', 'code' => 'SALEM_DP', 'description' => 'Premium turmeric grade', 'status' => 1],
            ['name' => 'Eagle Quality', 'code' => 'EAGLE', 'description' => 'Standard coriander grade', 'status' => 1],
            ['name' => 'Hulled Premium 99.95%', 'code' => 'HULLED_9995', 'description' => 'Hulled sesame premium', 'status' => 1],
            ['name' => 'Bold 40/50 Count', 'code' => 'BOLD_4050', 'description' => 'Peanut size count per ounce', 'status' => 1],
            ['name' => 'Grade A Flakes', 'code' => 'GRADE_A_FLAKE', 'description' => 'Premium dehydrated vegetables', 'status' => 1],
            ['name' => 'Purity 99%', 'code' => 'PURITY_99', 'description' => 'Standard psyllium husk', 'status' => 1]
        ];
        $gradeIds = [];
        foreach ($grades as $grd) {
            $gradeIds[$grd['code']] = $this->upsert('product_grades', $grd, ['code']);
        }

        // 4. Origins
        $origins = [
            ['name' => 'Unjha, Gujarat', 'code' => 'ORI_UNJ', 'description' => 'Unjha spice sourcing region', 'status' => 1],
            ['name' => 'Rajkot, Gujarat', 'code' => 'ORI_RAJ', 'description' => 'Saurashtra peanut belt', 'status' => 1],
            ['name' => 'Nashik, Maharashtra', 'code' => 'ORI_NSK', 'description' => 'Onion sourcing region', 'status' => 1],
            ['name' => 'Nizamabad, Telangana', 'code' => 'ORI_NIZ', 'description' => 'Turmeric market yard', 'status' => 1],
            ['name' => 'Kota, Rajasthan', 'code' => 'ORI_KOT', 'description' => 'Coriander seed belt', 'status' => 1],
            ['name' => 'Sidhpur, Gujarat', 'code' => 'ORI_SID', 'description' => 'Psyllium market yard', 'status' => 1]
        ];
        $originIds = [];
        foreach ($origins as $ori) {
            $originIds[$ori['code']] = $this->upsert('product_origins', $ori, ['code']);
        }

        // 5. Packing Types
        $packingTypes = [
            ['name' => '25 Kg PP Bag', 'code' => 'PP_25', 'description' => 'Woven polypropylene bag', 'status' => 1],
            ['name' => '50 Kg Jute Bag', 'code' => 'JUTE_50', 'description' => 'Double warp jute gunny bag', 'status' => 1],
            ['name' => '25 Kg Jute Bag', 'code' => 'JUTE_25', 'description' => 'Single warp jute gunny bag', 'status' => 1],
            ['name' => '20 Kg Carton Box', 'code' => 'CTN_20', 'description' => 'Corrugated fiberboard carton', 'status' => 1],
            ['name' => '25 Kg Multiwall Paper Bag', 'code' => 'PAPER_25', 'description' => 'Multiwall kraft paper bag', 'status' => 1]
        ];
        $packingIds = [];
        foreach ($packingTypes as $pt) {
            $packingIds[$pt['code']] = $this->upsert('packing_types', $pt, ['code']);
        }

        // 6. Container Types
        $containerTypes = [
            ['name' => '20ft Dry Cargo Container', 'code' => '20FT_GP', 'description' => 'Standard dry van', 'status' => 1],
            ['name' => '40ft Dry Cargo Container', 'code' => '40FT_GP', 'description' => 'High volume standard dry container', 'status' => 1],
            ['name' => '20ft Reefer Container', 'code' => '20FT_RF', 'description' => 'Refrigerated container', 'status' => 1]
        ];
        foreach ($containerTypes as $ct) {
            $this->upsert('container_types', $ct, ['code']);
        }

        // 7. HS Codes
        $hsCodes = [
            ['hs_code' => '09093119', 'description' => 'Cumin Seeds, neither crushed nor ground, other', 'category' => 'Spices', 'duty_rate' => 0.00],
            ['hs_code' => '09096139', 'description' => 'Fennel Seeds, other', 'category' => 'Spices', 'duty_rate' => 0.00],
            ['hs_code' => '09092190', 'description' => 'Coriander Seeds, other', 'category' => 'Spices', 'duty_rate' => 0.00],
            ['hs_code' => '09103020', 'description' => 'Turmeric Finger, Salem dry', 'category' => 'Spices', 'duty_rate' => 0.00],
            ['hs_code' => '12074090', 'description' => 'Sesame Seeds, other', 'category' => 'Oil Seeds', 'duty_rate' => 0.00],
            ['hs_code' => '12024220', 'description' => 'Groundnut Kernels, handpicked, bold', 'category' => 'Oil Seeds', 'duty_rate' => 0.00],
            ['hs_code' => '07122000', 'description' => 'Dehydrated Onion, flakes/powder', 'category' => 'Dehydrated Vegetables', 'duty_rate' => 0.00],
            ['hs_code' => '07129020', 'description' => 'Dehydrated Garlic, flakes/powder', 'category' => 'Dehydrated Vegetables', 'duty_rate' => 0.00],
            ['hs_code' => '12119032', 'description' => 'Psyllium Husk (Isabgol husk)', 'category' => 'Herbs & Medicinals', 'duty_rate' => 0.00],
            ['hs_code' => '12073000', 'description' => 'Castor Seeds', 'category' => 'Oil Seeds', 'duty_rate' => 0.00]
        ];
        foreach ($hsCodes as $hsc) {
            $this->upsert('hs_codes', $hsc, ['hs_code']);
        }

        // Fetch unit IDs dynamically
        $unitIds = [];
        $stmtU = $this->db->query("SELECT `id`, `code` FROM `units`");
        while ($row = $stmtU->fetch(PDO::FETCH_ASSOC)) {
            $unitIds[$row['code']] = (int)$row['id'];
        }

        // Fetch company ID
        $companyId = $this->db->query("SELECT `id` FROM `company` LIMIT 1")->fetchColumn();

        // 30 realistic products
        $products = [
            [
                'product_code' => 'PRD_CUM_001', 'name' => 'Premium Cumin Seeds Sortex 99.5%', 'category_id' => $categoryIds['SPICE'],
                'variety_id' => $varietyIds['CUMIN'], 'grade_id' => $gradeIds['SORTEX_995'], 'origin_id' => $originIds['ORI_UNJ'],
                'packing_type_id' => $packingIds['PP_25'], 'unit_id' => $unitIds['KG'], 'hs_code' => '09093119',
                'purchase_price' => 300.00, 'selling_price' => 350.00, 'moq' => 1000.00, 'max_oq' => 24000.00,
                'lead_time_days' => 10, 'gst_percent' => 5.00, 'packing_size' => '25 KG PP Bag',
                'net_weight' => 25.000, 'gross_weight' => 25.120, 'volume_per_package_cbm' => 0.0650,
                'bags_per_container' => 960, 'container_type' => '20FT_GP', 'pallet_type' => 'none',
                'shipping_marks' => 'CUMIN / SORTEX / 99.5% / DUBAI', 'default_shipment_type' => 'sea',
                'preferred_loading_port' => 'INMUN', 'preferred_destination_port' => 'AEJEA', 'preferred_incoterm' => 'FOB',
                'preferred_payment_method' => '30ADV_70CAD', 'is_machine_clean' => 1, 'is_sortex' => 1, 'is_hand_picked' => 0,
                'is_steam_sterilized' => 0, 'is_organic' => 0, 'cert_eu_standard' => 0, 'cert_us_fda' => 1, 'cert_iso' => 1,
                'cert_haccp' => 1, 'cert_fssai' => 1, 'cert_apeda' => 1, 'cert_asta' => 0, 'opening_stock' => 50000.00,
                'reorder_level' => 10000.00, 'safety_stock' => 5000.00, 'status' => 1, 'company_id' => $companyId
            ],
            [
                'product_code' => 'PRD_FEN_001', 'name' => 'Bold Fennel Seeds Machine Cleaned 99%', 'category_id' => $categoryIds['SPICE'],
                'variety_id' => $varietyIds['FENNEL'], 'grade_id' => $gradeIds['MC_99'], 'origin_id' => $originIds['ORI_UNJ'],
                'packing_type_id' => $packingIds['JUTE_50'], 'unit_id' => $unitIds['KG'], 'hs_code' => '09096139',
                'purchase_price' => 180.00, 'selling_price' => 220.00, 'moq' => 5000.00, 'max_oq' => 20000.00,
                'lead_time_days' => 14, 'gst_percent' => 5.00, 'packing_size' => '50 KG Jute Bag',
                'net_weight' => 50.000, 'gross_weight' => 50.800, 'volume_per_package_cbm' => 0.1400,
                'bags_per_container' => 400, 'container_type' => '20FT_GP', 'pallet_type' => 'none',
                'shipping_marks' => 'FENNEL / MC / 99% / HAMBURG', 'default_shipment_type' => 'sea',
                'preferred_loading_port' => 'INMUN', 'preferred_destination_port' => 'DEHAM', 'preferred_incoterm' => 'CIF',
                'preferred_payment_method' => 'LC_SIGHT', 'is_machine_clean' => 1, 'is_sortex' => 0, 'is_hand_picked' => 0,
                'is_steam_sterilized' => 0, 'is_organic' => 0, 'cert_eu_standard' => 1, 'cert_us_fda' => 1, 'cert_iso' => 1,
                'cert_haccp' => 1, 'cert_fssai' => 1, 'cert_apeda' => 1, 'cert_asta' => 1, 'opening_stock' => 30000.00,
                'reorder_level' => 8000.00, 'safety_stock' => 4000.00, 'status' => 1, 'company_id' => $companyId
            ],
            [
                'product_code' => 'PRD_TUR_001', 'name' => 'Salem Turmeric Finger Double Parrot', 'category_id' => $categoryIds['SPICE'],
                'variety_id' => $varietyIds['TURMERIC'], 'grade_id' => $gradeIds['SALEM_DP'], 'origin_id' => $originIds['ORI_NIZ'],
                'packing_type_id' => $packingIds['JUTE_50'], 'unit_id' => $unitIds['KG'], 'hs_code' => '09103020',
                'purchase_price' => 150.00, 'selling_price' => 195.00, 'moq' => 5000.00, 'max_oq' => 20000.00,
                'lead_time_days' => 15, 'gst_percent' => 5.00, 'packing_size' => '50 KG Jute Bag',
                'net_weight' => 50.000, 'gross_weight' => 50.800, 'volume_per_package_cbm' => 0.1500,
                'bags_per_container' => 380, 'container_type' => '20FT_GP', 'pallet_type' => 'none',
                'shipping_marks' => 'TURMERIC / SALEM / DP / USA', 'default_shipment_type' => 'sea',
                'preferred_loading_port' => 'INNSA', 'preferred_destination_port' => 'USOAK', 'preferred_incoterm' => 'FOB',
                'preferred_payment_method' => 'CAD', 'is_machine_clean' => 1, 'is_sortex' => 0, 'is_hand_picked' => 1,
                'is_steam_sterilized' => 0, 'is_organic' => 0, 'cert_eu_standard' => 0, 'cert_us_fda' => 1, 'cert_iso' => 1,
                'cert_haccp' => 1, 'cert_fssai' => 1, 'cert_apeda' => 1, 'cert_asta' => 1, 'opening_stock' => 45000.00,
                'reorder_level' => 10000.00, 'safety_stock' => 5000.00, 'status' => 1, 'company_id' => $companyId
            ],
            [
                'product_code' => 'PRD_SES_001', 'name' => 'Hulled Sesame Seeds 99.95%', 'category_id' => $categoryIds['SEED'],
                'variety_id' => $varietyIds['SESAME'], 'grade_id' => $gradeIds['HULLED_9995'], 'origin_id' => $originIds['ORI_RAJ'],
                'packing_type_id' => $packingIds['PAPER_25'], 'unit_id' => $unitIds['KG'], 'hs_code' => '12074090',
                'purchase_price' => 140.00, 'selling_price' => 175.00, 'moq' => 10000.00, 'max_oq' => 19000.00,
                'lead_time_days' => 12, 'gst_percent' => 5.00, 'packing_size' => '25 KG Multiwall Paper Bag',
                'net_weight' => 25.000, 'gross_weight' => 25.200, 'volume_per_package_cbm' => 0.0700,
                'bags_per_container' => 760, 'container_type' => '20FT_GP', 'pallet_type' => 'none',
                'shipping_marks' => 'SESAME / HULLED / 99.95% / VIETNAM', 'default_shipment_type' => 'sea',
                'preferred_loading_port' => 'INMUN', 'preferred_destination_port' => 'NLRTM', 'preferred_incoterm' => 'CIF',
                'preferred_payment_method' => 'LC_SIGHT', 'is_machine_clean' => 1, 'is_sortex' => 1, 'is_hand_picked' => 0,
                'is_steam_sterilized' => 1, 'is_organic' => 0, 'cert_eu_standard' => 1, 'cert_us_fda' => 1, 'cert_iso' => 1,
                'cert_haccp' => 1, 'cert_fssai' => 1, 'cert_apeda' => 1, 'cert_asta' => 0, 'opening_stock' => 60000.00,
                'reorder_level' => 15000.00, 'safety_stock' => 7500.00, 'status' => 1, 'company_id' => $companyId
            ],
            [
                'product_code' => 'PRD_ONN_001', 'name' => 'Dehydrated Onion Flakes Grade A', 'category_id' => $categoryIds['DEHY'],
                'variety_id' => $varietyIds['ONION_FLAKE'], 'grade_id' => $gradeIds['GRADE_A_FLAKE'], 'origin_id' => $originIds['ORI_NSK'],
                'packing_type_id' => $packingIds['CTN_20'], 'unit_id' => $unitIds['KG'], 'hs_code' => '07122000',
                'purchase_price' => 120.00, 'selling_price' => 160.00, 'moq' => 1000.00, 'max_oq' => 15000.00,
                'lead_time_days' => 14, 'gst_percent' => 5.00, 'packing_size' => '20 KG Carton Box',
                'net_weight' => 20.000, 'gross_weight' => 21.000, 'volume_per_package_cbm' => 0.0900,
                'bags_per_container' => 650, 'container_type' => '20FT_GP', 'pallet_type' => 'none',
                'shipping_marks' => 'ONION / FLAKES / GR-A / MALAYSIA', 'default_shipment_type' => 'sea',
                'preferred_loading_port' => 'INNSA', 'preferred_destination_port' => 'NLRTM', 'preferred_incoterm' => 'CFR',
                'preferred_payment_method' => '30ADV_70CAD', 'is_machine_clean' => 1, 'is_sortex' => 1, 'is_hand_picked' => 0,
                'is_steam_sterilized' => 0, 'is_organic' => 0, 'cert_eu_standard' => 1, 'cert_us_fda' => 1, 'cert_iso' => 1,
                'cert_haccp' => 1, 'cert_fssai' => 1, 'cert_apeda' => 1, 'cert_asta' => 0, 'opening_stock' => 25000.00,
                'reorder_level' => 5000.00, 'safety_stock' => 2500.00, 'status' => 1, 'company_id' => $companyId
            ]
        ];

        // Seed 5 core products
        $countryId = (int)$this->db->query("SELECT `id` FROM `countries` WHERE `code` = 'IN' LIMIT 1")->fetchColumn();
        $stateId = (int)$this->db->query("SELECT `id` FROM `states` WHERE `code` = 'GJ' LIMIT 1")->fetchColumn();
        $cityId = (int)$this->db->query("SELECT `id` FROM `cities` WHERE `name` = 'Ahmedabad' LIMIT 1")->fetchColumn();

        $seededProductIds = [];
        foreach ($products as $prod) {
            unset($prod['grade_id'], $prod['origin_id']);
            $prod['country_id'] = $countryId;
            $prod['state_id'] = $stateId;
            $prod['city_id'] = $cityId;
            $prod['country_of_origin'] = 'India';
            if (isset($prod['hs_code'])) {
                $prod['hsn_code'] = $prod['hs_code'];
                unset($prod['hs_code']);
            }
            $seededProductIds[$prod['product_code']] = $this->upsert('products', $prod, ['product_code']);
            // Seed a product specific unit conversion rule (BAG to KG for that product)
            $bagUnitId = $packingIds['PP_25']; // PP_25 maps to BAG unit logically
            if ($prod['packing_type_id'] === $packingIds['PP_25']) {
                $this->upsert('unit_conversions', [
                    'from_unit_id' => $unitIds['BAG'],
                    'to_unit_id' => $unitIds['KG'],
                    'factor' => 25.000000000,
                    'product_id' => $seededProductIds[$prod['product_code']],
                    'status' => 1
                ], ['from_unit_id', 'to_unit_id', 'product_id']);
            } elseif ($prod['packing_type_id'] === $packingIds['JUTE_50']) {
                $this->upsert('unit_conversions', [
                    'from_unit_id' => $unitIds['BAG'],
                    'to_unit_id' => $unitIds['KG'],
                    'factor' => 50.000000000,
                    'product_id' => $seededProductIds[$prod['product_code']],
                    'status' => 1
                ], ['from_unit_id', 'to_unit_id', 'product_id']);
            }
        }

        // Add 25 more mock products to reach 30 products requirement
        for ($k = 6; $k <= 30; $k++) {
            $mockCode = sprintf("PRD_MCK_%03d", $k);
            $mockProduct = [
                'product_code' => $mockCode,
                'name' => 'Agri Commodity Seed Grade ' . $k,
                'category_id' => $categoryIds['SPICE'],
                'variety_id' => $varietyIds['CORIANDER'],
                'packing_type_id' => $packingIds['PP_25'],
                'unit_id' => $unitIds['KG'],
                'hsn_code' => '09092190',
                'purchase_price' => 120.00 + ($k * 2),
                'selling_price' => 150.00 + ($k * 3),
                'moq' => 1000.00,
                'max_oq' => 20000.00,
                'lead_time_days' => 12,
                'gst_percent' => 5.00,
                'packing_size' => '25 KG PP Bag',
                'net_weight' => 25.000,
                'gross_weight' => 25.100,
                'volume_per_package_cbm' => 0.0600,
                'bags_per_container' => 800,
                'container_type' => '20FT_GP',
                'pallet_type' => 'none',
                'shipping_marks' => 'MOCK / GR-' . $k,
                'default_shipment_type' => 'sea',
                'preferred_loading_port' => 'INMUN',
                'preferred_destination_port' => 'AEJEA',
                'preferred_incoterm' => 'FOB',
                'preferred_payment_method' => '30ADV_70CAD',
                'is_machine_clean' => 1,
                'is_sortex' => 0,
                'is_hand_picked' => 0,
                'is_steam_sterilized' => 0,
                'is_organic' => 0,
                'cert_eu_standard' => 0,
                'cert_us_fda' => 0,
                'cert_iso' => 1,
                'cert_haccp' => 0,
                'cert_fssai' => 1,
                'cert_apeda' => 1,
                'cert_asta' => 0,
                'opening_stock' => 10000.00 + ($k * 1000),
                'reorder_level' => 2000.00,
                'safety_stock' => 1000.00,
                'status' => 1,
                'company_id' => $companyId,
                'country_id' => $countryId,
                'state_id' => $stateId,
                'city_id' => $cityId,
                'country_of_origin' => 'India'
            ];
            $this->upsert('products', $mockProduct, ['product_code']);
        }

        $this->log("Seeded 30 Products, Varieties, Grades, and Packaging successfully!");
    }
}
