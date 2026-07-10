<?php
declare(strict_types=1);

namespace App\Core\Seeds;

use PDO;

class BuyerSeeder extends Seeder
{
    public function run(): void
    {
        $this->log("Starting Buyer Seeder...");

        // Get country, state, city details dynamically
        $countryIds = [];
        $stmtC = $this->db->query("SELECT `id`, `code` FROM `countries`");
        while ($row = $stmtC->fetch(PDO::FETCH_ASSOC)) {
            $countryIds[$row['code']] = (int)$row['id'];
        }

        $stateIds = [];
        $stmtS = $this->db->query("SELECT `id`, `code` FROM `states`");
        while ($row = $stmtS->fetch(PDO::FETCH_ASSOC)) {
            $stateIds[$row['code']] = (int)$row['id'];
        }

        $cityIds = [];
        $stmtCity = $this->db->query("SELECT `id`, `name` FROM `cities`");
        while ($row = $stmtCity->fetch(PDO::FETCH_ASSOC)) {
            $cityIds[$row['name']] = (int)$row['id'];
        }

        $buyersData = [
            [
                'buyer_code' => 'BY_DXB_001', 'company_name' => 'Al-Sadiq Spices Trading LLC',
                'buyer_type' => 'importer', 'priority' => 'high', 'lead_source' => 'website',
                'contact_person' => 'Tariq Al-Sadiq', 'designation' => 'Managing Director',
                'email' => 'tariq@alsadiqspices.ae', 'mobile' => '+971-50-1234567', 'phone' => '+971-4-2223344',
                'website' => 'www.alsadiqspices.ae', 'whatsapp' => '+971-50-1234567',
                'billing_address' => 'Warehouse 12, Al Ras Spice Market, Deira, Dubai',
                'shipping_address' => 'Warehouse 12, Al Ras Spice Market, Deira, Dubai',
                'country' => 'United Arab Emirates', 'state' => 'Dubai', 'city' => 'Dubai', 'zip' => '00000',
                'country_id' => $countryIds['AE'] ?? null, 'state_id' => $stateIds['DU'] ?? null, 'city_id' => $cityIds['Dubai'] ?? null,
                'tax_number' => 'TRN10023456700003', 'payment_terms' => '30ADV_70CAD', 'credit_days' => 30,
                'shipping_mode' => 'sea', 'preferred_port' => 'Jebel Ali Port', 'shipping_marks' => 'ALSADIQ / DUBAI',
                'assigned_to' => 'System Admin', 'status' => 1
            ],
            [
                'buyer_code' => 'BY_DXB_002', 'company_name' => 'Gulf Agro Foods',
                'buyer_type' => 'distributor', 'priority' => 'medium', 'lead_source' => 'referral',
                'contact_person' => 'Faisal Hassan', 'designation' => 'Procurement Manager',
                'email' => 'procurement@gulfagro.ae', 'mobile' => '+971-52-9876543', 'phone' => '+971-2-5556677',
                'website' => 'www.gulfagro.ae', 'whatsapp' => '+971-52-9876543',
                'billing_address' => 'Plot 45, Industrial City Abu Dhabi (ICAD) 1, Abu Dhabi',
                'shipping_address' => 'Plot 45, Industrial City Abu Dhabi (ICAD) 1, Abu Dhabi',
                'country' => 'United Arab Emirates', 'state' => 'Dubai', 'city' => 'Abu Dhabi', 'zip' => '00000',
                'country_id' => $countryIds['AE'] ?? null, 'state_id' => $stateIds['DU'] ?? null, 'city_id' => $cityIds['Abu Dhabi'] ?? null,
                'tax_number' => 'TRN10098765400003', 'payment_terms' => 'LC_SIGHT', 'credit_days' => 0,
                'shipping_mode' => 'sea', 'preferred_port' => 'Jebel Ali Port', 'shipping_marks' => 'GULFAGRO / DUBAI',
                'assigned_to' => 'System Admin', 'status' => 1
            ],
            [
                'buyer_code' => 'BY_RUH_001', 'company_name' => 'Arabian Agricultural Est.',
                'buyer_type' => 'importer', 'priority' => 'high', 'lead_source' => 'trade_show',
                'contact_person' => 'Abdul Latif', 'designation' => 'General Manager',
                'email' => 'latif@arabianagro.com.sa', 'mobile' => '+966-50-3334444', 'phone' => '+966-11-4778899',
                'website' => 'www.arabianagro.com.sa', 'whatsapp' => '+966-50-3334444',
                'billing_address' => 'King Abdulaziz Road, Al Malaz District, Riyadh',
                'shipping_address' => 'King Abdulaziz Road, Al Malaz District, Riyadh',
                'country' => 'Saudi Arabia', 'state' => 'Riyadh', 'city' => 'Riyadh', 'zip' => '11564',
                'country_id' => $countryIds['SA'] ?? null, 'state_id' => $stateIds['RI'] ?? null, 'city_id' => $cityIds['Riyadh'] ?? null,
                'tax_number' => 'VAT300123456700003', 'payment_terms' => '30ADV_70CAD', 'credit_days' => 30,
                'shipping_mode' => 'sea', 'preferred_port' => 'Jeddah Port', 'shipping_marks' => 'AAE / RIYADH VIA JEDDAH',
                'assigned_to' => 'Export Manager', 'status' => 1
            ],
            [
                'buyer_code' => 'BY_JED_001', 'company_name' => 'Red Sea Food Imports',
                'buyer_type' => 'wholesaler', 'priority' => 'medium', 'lead_source' => 'website',
                'contact_person' => 'Khaled Al-Harbi', 'designation' => 'Purchasing Manager',
                'email' => 'khaled@redseafood.com.sa', 'mobile' => '+966-54-7778888', 'phone' => '+966-12-6601122',
                'website' => 'www.redseafood.com.sa', 'whatsapp' => '+966-54-7778888',
                'billing_address' => 'Al Mina Street, Al Hindawiyyah District, Jeddah',
                'shipping_address' => 'Al Mina Street, Al Hindawiyyah District, Jeddah',
                'country' => 'Saudi Arabia', 'state' => 'Riyadh', 'city' => 'Jeddah', 'zip' => '21432',
                'country_id' => $countryIds['SA'] ?? null, 'state_id' => $stateIds['RI'] ?? null, 'city_id' => $cityIds['Jeddah'] ?? null,
                'tax_number' => 'VAT300987654300003', 'payment_terms' => 'CAD', 'credit_days' => 10,
                'shipping_mode' => 'sea', 'preferred_port' => 'Jeddah Port', 'shipping_marks' => 'RSFI / JEDDAH',
                'assigned_to' => 'Export Manager', 'status' => 1
            ],
            [
                'buyer_code' => 'BY_HAM_001', 'company_name' => 'Hanseatic Spice Importers GmbH',
                'buyer_type' => 'importer', 'priority' => 'high', 'lead_source' => 'referral',
                'contact_person' => 'Dieter Kaufmann', 'designation' => 'Head of Sourcing',
                'email' => 'kaufmann@hanseaticspice.de', 'mobile' => '+49-170-1234567', 'phone' => '+49-40-369870',
                'website' => 'www.hanseaticspice.de', 'whatsapp' => '+49-170-1234567',
                'billing_address' => 'Am Sandtorkai 37, Speicherstadt, Hamburg',
                'shipping_address' => 'Am Sandtorkai 37, Speicherstadt, Hamburg',
                'country' => 'Germany', 'state' => 'Hamburg', 'city' => 'Hamburg', 'zip' => '20457',
                'country_id' => $countryIds['DE'] ?? null, 'state_id' => $stateIds['HH'] ?? null, 'city_id' => $cityIds['Hamburg'] ?? null,
                'tax_number' => 'DE123456789', 'payment_terms' => 'CAD', 'credit_days' => 14,
                'shipping_mode' => 'sea', 'preferred_port' => 'Hamburg Port', 'shipping_marks' => 'HSI / HAMBURG',
                'assigned_to' => 'Export Manager', 'status' => 1
            ],
            [
                'buyer_code' => 'BY_RTM_001', 'company_name' => 'Rotterdam Agro Trade BV',
                'buyer_type' => 'distributor', 'priority' => 'high', 'lead_source' => 'trade_show',
                'contact_person' => 'Jan de Boer', 'designation' => 'Director',
                'email' => 'jan@rotterdamagro.nl', 'mobile' => '+31-61-1234567', 'phone' => '+31-10-4402233',
                'website' => 'www.rotterdamagro.nl', 'whatsapp' => '+31-61-1234567',
                'billing_address' => 'Albert Plesmanweg 61, Rotterdam Port Area, Rotterdam',
                'shipping_address' => 'Albert Plesmanweg 61, Rotterdam Port Area, Rotterdam',
                'country' => 'Netherlands', 'state' => 'Zuid-Holland', 'city' => 'Rotterdam', 'zip' => '3088 GB',
                'country_id' => $countryIds['NL'] ?? null, 'state_id' => $stateIds['ZH'] ?? null, 'city_id' => $cityIds['Rotterdam'] ?? null,
                'tax_number' => 'NL800123456B01', 'payment_terms' => 'LC_SIGHT', 'credit_days' => 0,
                'shipping_mode' => 'sea', 'preferred_port' => 'Rotterdam Port', 'shipping_marks' => 'RAT / ROTTERDAM',
                'assigned_to' => 'System Admin', 'status' => 1
            ],
            [
                'buyer_code' => 'BY_LON_001', 'company_name' => 'London Spice Merchants Ltd',
                'buyer_type' => 'importer', 'priority' => 'medium', 'lead_source' => 'website',
                'contact_person' => 'Robert Green', 'designation' => 'Supply Chain Manager',
                'email' => 'r.green@londonspicemerchants.co.uk', 'mobile' => '+44-7700-900077', 'phone' => '+44-20-79460011',
                'website' => 'www.londonspicemerchants.co.uk', 'whatsapp' => '+44-7700-900077',
                'billing_address' => 'Unit 8, Docklands Business Park, London',
                'shipping_address' => 'Unit 8, Docklands Business Park, London',
                'country' => 'United Kingdom', 'state' => 'England', 'city' => 'London', 'zip' => 'E14 8PX',
                'country_id' => $countryIds['GB'] ?? null, 'state_id' => $stateIds['ENG'] ?? null, 'city_id' => $cityIds['London'] ?? null,
                'tax_number' => 'GB987654321', 'payment_terms' => '30ADV_70CAD', 'credit_days' => 30,
                'shipping_mode' => 'sea', 'preferred_port' => 'London Gateway', 'shipping_marks' => 'LSM / LONDON',
                'assigned_to' => 'Export Manager', 'status' => 1
            ],
            [
                'buyer_code' => 'BY_SFO_001', 'company_name' => 'Pacific Spices & Herbs Inc',
                'buyer_type' => 'distributor', 'priority' => 'high', 'lead_source' => 'trade_show',
                'contact_person' => 'Sarah Jenkins', 'designation' => 'VP Procurement',
                'email' => 'sarah@pacificspices.com', 'mobile' => '+1-415-5550199', 'phone' => '+1-415-5550122',
                'website' => 'www.pacificspices.com', 'whatsapp' => '+1-415-5550199',
                'billing_address' => '100 Terminal Court, South San Francisco, CA',
                'shipping_address' => '100 Terminal Court, South San Francisco, CA',
                'country' => 'United States', 'state' => 'California', 'city' => 'San Francisco', 'zip' => '94080',
                'country_id' => $countryIds['US'] ?? null, 'state_id' => $stateIds['CA'] ?? null, 'city_id' => $cityIds['San Francisco'] ?? null,
                'tax_number' => 'US95-1234567', 'payment_terms' => 'CAD', 'credit_days' => 15,
                'shipping_mode' => 'sea', 'preferred_port' => 'Oakland Port', 'shipping_marks' => 'PSH / OAKLAND',
                'assigned_to' => 'System Admin', 'status' => 1
            ],
            [
                'buyer_code' => 'BY_NYC_001', 'company_name' => 'Atlantic Food Distributors',
                'buyer_type' => 'wholesaler', 'priority' => 'medium', 'lead_source' => 'website',
                'contact_person' => 'Michael Chang', 'designation' => 'Director Sourcing',
                'email' => 'mchang@atlanticfood.com', 'mobile' => '+1-646-5550144', 'phone' => '+1-212-5550110',
                'website' => 'www.atlanticfood.com', 'whatsapp' => '+1-646-5550144',
                'billing_address' => '450 Food Center Drive, Bronx, NY',
                'shipping_address' => '450 Food Center Drive, Bronx, NY',
                'country' => 'United States', 'state' => 'California', 'city' => 'New York', 'zip' => '10474',
                'country_id' => $countryIds['US'] ?? null, 'state_id' => $stateIds['CA'] ?? null, 'city_id' => $cityIds['New York'] ?? null,
                'tax_number' => 'US13-9876543', 'payment_terms' => 'LC_SIGHT', 'credit_days' => 0,
                'shipping_mode' => 'sea', 'preferred_port' => 'Newark Port', 'shipping_marks' => 'AFD / NEWARK',
                'assigned_to' => 'Export Manager', 'status' => 1
            ],
            [
                'buyer_code' => 'BY_TOR_001', 'company_name' => 'Great Lakes Spice Co.',
                'buyer_type' => 'importer', 'priority' => 'medium', 'lead_source' => 'referral',
                'contact_person' => 'David Miller', 'designation' => 'President',
                'email' => 'dmiller@greatlakesspice.ca', 'mobile' => '+1-416-5550188', 'phone' => '+1-416-5550155',
                'website' => 'www.greatlakesspice.ca', 'whatsapp' => '+1-416-5550188',
                'billing_address' => '220 Queens Quay West, Toronto, ON',
                'shipping_address' => '220 Queens Quay West, Toronto, ON',
                'country' => 'Canada', 'state' => 'Ontario', 'city' => 'Toronto', 'zip' => 'M5J 2Y5',
                'country_id' => $countryIds['CA'] ?? null, 'state_id' => $stateIds['ON'] ?? null, 'city_id' => $cityIds['Toronto'] ?? null,
                'tax_number' => 'CA123456789RT0001', 'payment_terms' => '30ADV_70CAD', 'credit_days' => 30,
                'shipping_mode' => 'sea', 'preferred_port' => 'Montreal Port', 'shipping_marks' => 'GLS / MONTREAL',
                'assigned_to' => 'Export Manager', 'status' => 1
            ],
            [
                'buyer_code' => 'BY_MEL_001', 'company_name' => 'Oceania Spices Pty Ltd',
                'buyer_type' => 'importer', 'priority' => 'high', 'lead_source' => 'trade_show',
                'contact_person' => 'Bruce Kelly', 'designation' => 'Procurement Lead',
                'email' => 'bruce@oceaniaspices.com.au', 'mobile' => '+61-491-570156', 'phone' => '+61-3-90001234',
                'website' => 'www.oceaniaspices.com.au', 'whatsapp' => '+61-491-570156',
                'billing_address' => '45 Logistics Boulevard, Altona, Melbourne, VIC',
                'shipping_address' => '45 Logistics Boulevard, Altona, Melbourne, VIC',
                'country' => 'Australia', 'state' => 'Victoria', 'city' => 'Melbourne', 'zip' => '3018',
                'country_id' => $countryIds['AU'] ?? null, 'state_id' => $stateIds['VIC'] ?? null, 'city_id' => $cityIds['Melbourne'] ?? null,
                'tax_number' => 'ABN98765432100', 'payment_terms' => 'CAD', 'credit_days' => 14,
                'shipping_mode' => 'sea', 'preferred_port' => 'Melbourne Port', 'shipping_marks' => 'OSPL / MELBOURNE',
                'assigned_to' => 'System Admin', 'status' => 1
            ],
            [
                'buyer_code' => 'BY_PEN_001', 'company_name' => 'Penang Agro Distributors Sdn Bhd',
                'buyer_type' => 'distributor', 'priority' => 'high', 'lead_source' => 'website',
                'contact_person' => 'Lim Cheng', 'designation' => 'Director Sourcing',
                'email' => 'lim@penangagro.com.my', 'mobile' => '+60-12-3456789', 'phone' => '+60-4-3901234',
                'website' => 'www.penangagro.com.my', 'whatsapp' => '+60-12-3456789',
                'billing_address' => 'Lot 12A, Jalan Perusahaan, Prai Industrial Estate, Penang',
                'shipping_address' => 'Lot 12A, Jalan Perusahaan, Prai Industrial Estate, Penang',
                'country' => 'Malaysia', 'state' => 'Penang', 'city' => 'Penang', 'zip' => '13600',
                'country_id' => $countryIds['MY'] ?? null, 'state_id' => $stateIds['PEN'] ?? null, 'city_id' => $cityIds['Penang'] ?? null,
                'tax_number' => 'W10-1234-56789012', 'payment_terms' => '30ADV_70CAD', 'credit_days' => 30,
                'shipping_mode' => 'sea', 'preferred_port' => 'Penang Port', 'shipping_marks' => 'PAD / PENANG',
                'assigned_to' => 'Export Manager', 'status' => 1
            ],
            [
                'buyer_code' => 'BY_KUL_001', 'company_name' => 'Nanyang Imports',
                'buyer_type' => 'wholesaler', 'priority' => 'medium', 'lead_source' => 'referral',
                'contact_person' => 'Tan Kok Seng', 'designation' => 'General Manager',
                'email' => 'tan@nanyangimports.com.my', 'mobile' => '+60-16-9876543', 'phone' => '+60-3-22824567',
                'website' => 'www.nanyangimports.com.my', 'whatsapp' => '+60-16-9876543',
                'billing_address' => '12 Jalan Sultan Ismail, Kuala Lumpur',
                'shipping_address' => '12 Jalan Sultan Ismail, Kuala Lumpur',
                'country' => 'Malaysia', 'state' => 'Penang', 'city' => 'Penang', 'zip' => '50250',
                'country_id' => $countryIds['MY'] ?? null, 'state_id' => $stateIds['PEN'] ?? null, 'city_id' => $cityIds['Penang'] ?? null,
                'tax_number' => 'W10-9876-54321098', 'payment_terms' => 'LC_SIGHT', 'credit_days' => 0,
                'shipping_mode' => 'sea', 'preferred_port' => 'Klang Port', 'shipping_marks' => 'NYI / KLANG',
                'assigned_to' => 'Export Manager', 'status' => 1
            ],
            [
                'buyer_code' => 'BY_SGN_001', 'company_name' => 'Mekong Delta Spices JSC',
                'buyer_type' => 'importer', 'priority' => 'high', 'lead_source' => 'trade_show',
                'contact_person' => 'Nguyen Van Hung', 'designation' => 'Director',
                'email' => 'hung@mekongspices.vn', 'mobile' => '+84-90-3456789', 'phone' => '+84-28-38290000',
                'website' => 'www.mekongspices.vn', 'whatsapp' => '+84-90-3456789',
                'billing_address' => '120 Nguyen Hue Boulevard, District 1, Ho Chi Minh City',
                'shipping_address' => '120 Nguyen Hue Boulevard, District 1, Ho Chi Minh City',
                'country' => 'Vietnam', 'state' => 'Ho Chi Minh', 'city' => 'Ho Chi Minh City', 'zip' => '70000',
                'country_id' => $countryIds['VN'] ?? null, 'state_id' => $stateIds['HC'] ?? null, 'city_id' => $cityIds['Ho Chi Minh City'] ?? null,
                'tax_number' => 'MST0301234567', 'payment_terms' => '30ADV_70CAD', 'credit_days' => 30,
                'shipping_mode' => 'sea', 'preferred_port' => 'Cat Lai Port', 'shipping_marks' => 'MDS / CATLAI',
                'assigned_to' => 'Export Manager', 'status' => 1
            ],
            [
                'buyer_code' => 'BY_HAN_001', 'company_name' => 'Hanoi Spices Trading',
                'buyer_type' => 'wholesaler', 'priority' => 'medium', 'lead_source' => 'website',
                'contact_person' => 'Tran Thi Mai', 'designation' => 'Procurement Officer',
                'email' => 'mai@hanoispices.vn', 'mobile' => '+84-91-9876543', 'phone' => '+84-24-37560000',
                'website' => 'www.hanoispices.vn', 'whatsapp' => '+84-91-9876543',
                'billing_address' => '45 Ly Thuong Kiet Street, Hoan Kiem District, Hanoi',
                'shipping_address' => '45 Ly Thuong Kiet Street, Hoan Kiem District, Hanoi',
                'country' => 'Vietnam', 'state' => 'Ho Chi Minh', 'city' => 'Ho Chi Minh City', 'zip' => '10000',
                'country_id' => $countryIds['VN'] ?? null, 'state_id' => $stateIds['HC'] ?? null, 'city_id' => $cityIds['Ho Chi Minh City'] ?? null,
                'tax_number' => 'MST0309876543', 'payment_terms' => 'CAD', 'credit_days' => 10,
                'shipping_mode' => 'sea', 'preferred_port' => 'Hai Phong Port', 'shipping_marks' => 'HST / HAIPHONG',
                'assigned_to' => 'System Admin', 'status' => 1
            ],
            [
                'buyer_code' => 'BY_DXB_003', 'company_name' => 'Falcon Agri Trading LLC',
                'buyer_type' => 'importer', 'priority' => 'high', 'lead_source' => 'website',
                'contact_person' => 'Imran Khan', 'designation' => 'CEO',
                'email' => 'imran@falconagri.ae', 'mobile' => '+971-55-1234500', 'phone' => '+971-4-3221144',
                'website' => 'www.falconagri.ae', 'whatsapp' => '+971-55-1234500',
                'billing_address' => 'Shop 3, Ganj Spice Market, Deira, Dubai',
                'shipping_address' => 'Shop 3, Ganj Spice Market, Deira, Dubai',
                'country' => 'United Arab Emirates', 'state' => 'Dubai', 'city' => 'Dubai', 'zip' => '00000',
                'country_id' => $countryIds['AE'] ?? null, 'state_id' => $stateIds['DU'] ?? null, 'city_id' => $cityIds['Dubai'] ?? null,
                'tax_number' => 'TRN10077654300003', 'payment_terms' => '30ADV_70CAD', 'credit_days' => 30,
                'shipping_mode' => 'sea', 'preferred_port' => 'Jebel Ali Port', 'shipping_marks' => 'FALCON / DUBAI',
                'assigned_to' => 'System Admin', 'status' => 1
            ],
            [
                'buyer_code' => 'BY_RUH_002', 'company_name' => 'Saud Spice House',
                'buyer_type' => 'distributor', 'priority' => 'high', 'lead_source' => 'trade_show',
                'contact_person' => 'Saud Bin Fahd', 'designation' => 'Director',
                'email' => 'saud@saudspices.sa', 'mobile' => '+966-55-2223333', 'phone' => '+966-11-2009988',
                'website' => 'www.saudspices.sa', 'whatsapp' => '+966-55-2223333',
                'billing_address' => 'Olaya Main Street, Riyadh',
                'shipping_address' => 'Olaya Main Street, Riyadh',
                'country' => 'Saudi Arabia', 'state' => 'Riyadh', 'city' => 'Riyadh', 'zip' => '11421',
                'country_id' => $countryIds['SA'] ?? null, 'state_id' => $stateIds['RI'] ?? null, 'city_id' => $cityIds['Riyadh'] ?? null,
                'tax_number' => 'VAT300887654000003', 'payment_terms' => 'LC_SIGHT', 'credit_days' => 0,
                'shipping_mode' => 'sea', 'preferred_port' => 'Jeddah Port', 'shipping_marks' => 'SSH / RIYADH',
                'assigned_to' => 'Export Manager', 'status' => 1
            ],
            [
                'buyer_code' => 'BY_DE_002', 'company_name' => 'Euro-Masala Import GmbH',
                'buyer_type' => 'wholesaler', 'priority' => 'medium', 'lead_source' => 'website',
                'contact_person' => 'Markus Weber', 'designation' => 'Procurement Officer',
                'email' => 'weber@euromasala.de', 'mobile' => '+49-160-9876543', 'phone' => '+49-89-450099',
                'website' => 'www.euromasala.de', 'whatsapp' => '+49-160-9876543',
                'billing_address' => 'Werner-von-Siemens-Str. 15, Munich',
                'shipping_address' => 'Am Sandtorkai Quay, Hamburg Port, Hamburg',
                'country' => 'Germany', 'state' => 'Hamburg', 'city' => 'Hamburg', 'zip' => '80333',
                'country_id' => $countryIds['DE'] ?? null, 'state_id' => $stateIds['HH'] ?? null, 'city_id' => $cityIds['Hamburg'] ?? null,
                'tax_number' => 'DE987654321', 'payment_terms' => 'CAD', 'credit_days' => 30,
                'shipping_mode' => 'sea', 'preferred_port' => 'Hamburg Port', 'shipping_marks' => 'EM / HAMBURG',
                'assigned_to' => 'Export Manager', 'status' => 1
            ],
            [
                'buyer_code' => 'BY_NL_002', 'company_name' => 'Zuid Trading Holland',
                'buyer_type' => 'importer', 'priority' => 'high', 'lead_source' => 'referral',
                'contact_person' => 'Dirk Kuyt', 'designation' => 'Supply Manager',
                'email' => 'kuyt@zuidtrading.nl', 'mobile' => '+31-62-9876543', 'phone' => '+31-20-6609900',
                'website' => 'www.zuidtrading.nl', 'whatsapp' => '+31-62-9876543',
                'billing_address' => 'Keizersgracht 450, Amsterdam',
                'shipping_address' => 'Albert Plesmanweg Container Yard, Rotterdam Port, Rotterdam',
                'country' => 'Netherlands', 'state' => 'Zuid-Holland', 'city' => 'Rotterdam', 'zip' => '1016 GD',
                'country_id' => $countryIds['NL'] ?? null, 'state_id' => $stateIds['ZH'] ?? null, 'city_id' => $cityIds['Rotterdam'] ?? null,
                'tax_number' => 'NL900987654B01', 'payment_terms' => '30ADV_70CAD', 'credit_days' => 30,
                'shipping_mode' => 'sea', 'preferred_port' => 'Rotterdam Port', 'shipping_marks' => 'ZTH / ROTTERDAM',
                'assigned_to' => 'System Admin', 'status' => 1
            ],
            [
                'buyer_code' => 'BY_AUS_002', 'company_name' => 'Southern Cross Imports',
                'buyer_type' => 'distributor', 'priority' => 'medium', 'lead_source' => 'website',
                'contact_person' => 'Mark Taylor', 'designation' => 'Purchasing Manager',
                'email' => 'mtaylor@southerncross.com.au', 'mobile' => '+61-491-570990', 'phone' => '+61-2-92004455',
                'website' => 'www.southerncross.com.au', 'whatsapp' => '+61-491-570990',
                'billing_address' => '12 George Street, Sydney, NSW',
                'shipping_address' => '45 Logistics Blvd, Altona, Melbourne Port, Melbourne',
                'country' => 'Australia', 'state' => 'Victoria', 'city' => 'Melbourne', 'zip' => '2000',
                'country_id' => $countryIds['AU'] ?? null, 'state_id' => $stateIds['VIC'] ?? null, 'city_id' => $cityIds['Melbourne'] ?? null,
                'tax_number' => 'ABN12345678900', 'payment_terms' => 'CAD', 'credit_days' => 14,
                'shipping_mode' => 'sea', 'preferred_port' => 'Melbourne Port', 'shipping_marks' => 'SCI / MELBOURNE',
                'assigned_to' => 'Export Manager', 'status' => 1
            ]
        ];

        foreach ($buyersData as $buyer) {
            $buyerId = $this->upsert('buyers', $buyer, ['buyer_code']);
            
            // Add buyer contact
            $contact = [
                'buyer_id' => $buyerId,
                'name' => $buyer['contact_person'],
                'designation' => $buyer['designation'],
                'email' => $buyer['email'],
                'mobile' => $buyer['mobile'],
                'phone' => $buyer['phone'],
                'is_primary' => 1,
                'status' => 1
            ];
            $this->upsert('buyer_contacts', $contact, ['buyer_id', 'email']);

            // Add buyer address
            $address = [
                'buyer_id' => $buyerId,
                'address_type' => 'billing',
                'address' => $buyer['billing_address'],
                'country_id' => $buyer['country_id'],
                'state_id' => $buyer['state_id'],
                'city_id' => $buyer['city_id'],
                'zip' => $buyer['zip'],
                'status' => 1
            ];
            $this->upsert('buyer_addresses', $address, ['buyer_id', 'address_type']);

            // Shipping address if identical
            $address['address_type'] = 'shipping';
            $address['address'] = $buyer['shipping_address'];
            $this->upsert('buyer_addresses', $address, ['buyer_id', 'address_type']);
        }

        $this->log("Seeded 20 Buyers, Contacts, and Addresses successfully!");
    }
}
