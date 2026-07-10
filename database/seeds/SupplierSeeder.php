<?php
declare(strict_types=1);

namespace App\Core\Seeds;

use PDO;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $this->log("Starting Supplier Seeder...");

        // Get country, state, city details dynamically
        $countryIds = [];
        $stmtC = $this->db->query("SELECT `id`, `code` FROM `countries`");
        while ($row = $stmtC->fetch(PDO::FETCH_ASSOC)) {
            $countryIds[$row['code']] = (int)$row['id'];
        }
        $stateGjId = (int)$this->db->query("SELECT `id` FROM `states` WHERE `code` = 'GJ' LIMIT 1")->fetchColumn();
        $cityAmdId = (int)$this->db->query("SELECT `id` FROM `cities` WHERE `name` = 'Ahmedabad' LIMIT 1")->fetchColumn();

        $suppliersData = [
            [
                'supplier_code' => 'SU_UNJ_001', 'company_name' => 'Unjha Spice Mandi Sourcing Ltd',
                'contact_person' => 'Ramesh Patel', 'designation' => 'Managing Partner',
                'email' => 'ramesh@unjhaspices.com', 'mobile' => '+91-9825012345', 'phone' => '+91-2767-222333',
                'address' => 'Ganj Bazar Yard, Shop No 102, Unjha, Gujarat, India',
                'gst_number' => '24AABCU1234E1ZA', 'status' => 1
            ],
            [
                'supplier_code' => 'SU_RAJ_001', 'company_name' => 'Saurashtra Peanut Sourcing',
                'contact_person' => 'Hardik Savaliya', 'designation' => 'Sales Director',
                'email' => 'hardik@saurashtrapeanut.com', 'mobile' => '+91-9979012345', 'phone' => '+91-281-2445566',
                'address' => 'Marketing Yard Road, GIDC Area, Rajkot, Gujarat, India',
                'gst_number' => '24AABCS4567F1ZB', 'status' => 1
            ],
            [
                'supplier_code' => 'SU_NSK_001', 'company_name' => 'Nashik Onion & Garlic Association',
                'contact_person' => 'Milind Kakade', 'designation' => 'Operations Head',
                'email' => 'milind@nashikonions.co.in', 'mobile' => '+91-9422012345', 'phone' => '+91-253-2331122',
                'address' => 'Pimpalgaon Baswant Mandi Office, Nashik, Maharashtra, India',
                'gst_number' => '27AABCN8901D1ZC', 'status' => 1
            ],
            [
                'supplier_code' => 'SU_IND_001', 'company_name' => 'Indore Turmeric & Masala Mills',
                'contact_person' => 'Anil Chaurasia', 'designation' => 'Partner',
                'email' => 'anil@indoreturmeric.com', 'mobile' => '+91-9893012345', 'phone' => '+91-731-2559900',
                'address' => '204, Anaj Mandi, Indore, Madhya Pradesh, India',
                'gst_number' => '23AABCI2345C1ZD', 'status' => 1
            ],
            [
                'supplier_code' => 'SU_MUN_001', 'company_name' => 'Kutch Psyllium Industries',
                'contact_person' => 'Devji Maheshwari', 'designation' => 'Proprietor',
                'email' => 'devji@kutchpsyllium.com', 'mobile' => '+91-9879012345', 'phone' => '+91-2838-224455',
                'address' => 'GIDC Estate, Phase II, Mundra, Kutch, Gujarat, India',
                'gst_number' => '24AABCK7890B1ZE', 'status' => 1
            ],
            [
                'supplier_code' => 'SU_UNJ_002', 'company_name' => 'Gujarat Seed Cleaners & Processors',
                'contact_person' => 'Jayesh Shah', 'designation' => 'Director',
                'email' => 'jayesh@gujaratseeds.com', 'mobile' => '+91-9824054321', 'phone' => '+91-2767-233445',
                'address' => 'SIDCO Industrial Zone, Unjha, Gujarat, India',
                'gst_number' => '24AABCG5432E1ZF', 'status' => 1
            ],
            [
                'supplier_code' => 'SU_RAJ_002', 'company_name' => 'Maruti Oil & Pulse Mills',
                'contact_person' => 'Ketan Vaghasia', 'designation' => 'Manager',
                'email' => 'ketan@marutioils.com', 'mobile' => '+91-9724012345', 'phone' => '+91-2825-244222',
                'address' => 'Gondal Highway Mandi Yard, Rajkot, Gujarat, India',
                'gst_number' => '24AABCM9901M1ZG', 'status' => 1
            ],
            [
                'supplier_code' => 'SU_NSK_002', 'company_name' => 'Sahyadri Agri Processing Pvt Ltd',
                'contact_person' => 'Sachin Gawande', 'designation' => 'Manager Procurement',
                'email' => 'sachin@sahyadriagri.com', 'mobile' => '+91-9822012345', 'phone' => '+91-253-2445566',
                'address' => 'Gat No 312, Adgaon, Nashik, Maharashtra, India',
                'gst_number' => '27AABCS5544N1ZH', 'status' => 1
            ],
            [
                'supplier_code' => 'SU_IND_002', 'company_name' => 'Narmada Valley Agro Traders',
                'contact_person' => 'Vikram Singh', 'designation' => 'Proprietor',
                'email' => 'vikram@narmadaagro.com', 'mobile' => '+91-9425012345', 'phone' => '+91-731-2884400',
                'address' => 'Kalyan Ganj Market, Indore, Madhya Pradesh, India',
                'gst_number' => '23AABCV1122C1ZI', 'status' => 1
            ],
            [
                'supplier_code' => 'SU_MUN_002', 'company_name' => 'Mundra Marine & Transit Logistics',
                'contact_person' => 'Pradeep Singh', 'designation' => 'Partner',
                'email' => 'pradeep@mundralogistics.co.in', 'mobile' => '+91-9909012345', 'phone' => '+91-2838-233445',
                'address' => 'Custom House Road, Mundra Port Area, Gujarat, India',
                'gst_number' => '24AABCM3344D1ZJ', 'status' => 1
            ],
            [
                'supplier_code' => 'SU_AMD_001', 'company_name' => 'Agro-Tech Processing Ind',
                'contact_person' => 'Manish Vaghela', 'designation' => 'Proprietor',
                'email' => 'manish@agrotechind.com', 'mobile' => '+91-9825123456', 'phone' => '+91-79-25831234',
                'address' => 'Phase IV, GIDC Vatva, Ahmedabad, Gujarat, India',
                'gst_number' => '24AABCA5566V1ZK', 'status' => 1
            ],
            [
                'supplier_code' => 'SU_AMD_002', 'company_name' => 'Gujarat Spices & Pulses Sourcing',
                'contact_person' => 'Kamlesh Shah', 'designation' => 'MD',
                'email' => 'kamlesh@gujaratspices.com', 'mobile' => '+91-9824012345', 'phone' => '+91-79-26851234',
                'address' => 'Satellite Center, Ahmedabad, Gujarat, India',
                'gst_number' => '24AABCG9988S1ZL', 'status' => 1
            ],
            [
                'supplier_code' => 'SU_UNJ_003', 'company_name' => 'Patel Crop Trading Company',
                'contact_person' => 'Amrut Patel', 'designation' => 'MD',
                'email' => 'amrut@patelcrops.com', 'mobile' => '+91-9825045678', 'phone' => '+91-2767-224466',
                'address' => 'Station Road, Unjha, Gujarat, India',
                'gst_number' => '24AABCP8899P1ZM', 'status' => 1
            ],
            [
                'supplier_code' => 'SU_RAJ_003', 'company_name' => 'Saurashtra Agri Logistics',
                'contact_person' => 'Bipin Savalia', 'designation' => 'MD',
                'email' => 'bipin@saurashtraagri.co.in', 'mobile' => '+91-9879054321', 'phone' => '+91-281-2339900',
                'address' => 'Marketing Yard, Block 12, Rajkot, Gujarat, India',
                'gst_number' => '24AABCS1122F1ZN', 'status' => 1
            ],
            [
                'supplier_code' => 'SU_NSK_003', 'company_name' => 'Godavari Dehy Foods',
                'contact_person' => 'Prashant Patil', 'designation' => 'Director',
                'email' => 'prashant@godavaridehy.com', 'mobile' => '+91-9860012345', 'phone' => '+91-253-2456700',
                'address' => 'Sinnar Industrial Zone, Nashik, Maharashtra, India',
                'gst_number' => '27AABCG3322P1ZO', 'status' => 1
            ]
        ];

        foreach ($suppliersData as $sup) {
            $supplierRecord = [
                'supplier_code' => $sup['supplier_code'],
                'company_name' => $sup['company_name'],
                'contact_person' => $sup['contact_person'],
                'email' => $sup['email'],
                'phone' => $sup['phone'],
                'address' => json_encode(['street' => $sup['address']]),
                'gst_number' => $sup['gst_number'],
                'status' => $sup['status']
            ];
            $supId = $this->upsert('suppliers', $supplierRecord, ['supplier_code']);

            // Seed Contact
            $contact = [
                'supplier_id' => $supId,
                'name' => $sup['contact_person'],
                'designation' => $sup['designation'],
                'email' => $sup['email'],
                'mobile' => $sup['mobile'],
                'phone' => $sup['phone'],
                'is_primary' => 1,
                'status' => 1
            ];
            $this->upsert('supplier_contacts', $contact, ['supplier_id', 'email']);

            // Seed Address
            $address = [
                'supplier_id' => $supId,
                'address_type' => 'billing',
                'address' => $sup['address'],
                'country_id' => $countryIds['IN'],
                'state_id' => $stateGjId,
                'city_id' => $cityAmdId,
                'zip' => '380001',
                'status' => 1
            ];
            $this->upsert('supplier_addresses', $address, ['supplier_id', 'address_type']);

            $address['address_type'] = 'shipping';
            $this->upsert('supplier_addresses', $address, ['supplier_id', 'address_type']);

            // Seed Bank Account
            $bank = [
                'supplier_id' => $supId,
                'bank_name' => 'State Bank of India',
                'account_name' => $sup['company_name'],
                'account_number' => '300012345' . $supId,
                'ifsc_code' => 'SBIN0001234',
                'swift_code' => 'SBININBBXXX',
                'currency' => 'INR',
                'is_primary' => 1
            ];
            $this->upsert('supplier_bank_details', $bank, ['supplier_id', 'account_number']);
        }

        $this->log("Seeded 15 Suppliers, Contacts, and Addresses successfully!");
    }
}
