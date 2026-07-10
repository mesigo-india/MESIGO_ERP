<?php
declare(strict_types=1);

namespace App\Core\Seeds;

use PDO;
use DateTime;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $this->log("Starting Transaction Seeder...");

        // Fetch dynamic IDs
        $companyId = (int)$this->db->query("SELECT `id` FROM `company` LIMIT 1")->fetchColumn();
        
        $buyers = [];
        $stmtB = $this->db->query("SELECT `id`, `buyer_code`, `company_name` FROM `buyers`");
        while ($row = $stmtB->fetch(PDO::FETCH_ASSOC)) {
            $buyers[$row['buyer_code']] = $row;
        }

        $suppliers = [];
        $stmtSup = $this->db->query("SELECT `id`, `supplier_code`, `company_name` FROM `suppliers`");
        while ($row = $stmtSup->fetch(PDO::FETCH_ASSOC)) {
            $suppliers[$row['supplier_code']] = $row;
        }

        $products = [];
        $stmtP = $this->db->query("SELECT `id`, `product_code`, `unit_id`, `net_weight`, `gross_weight`, `volume_per_package_cbm`, `gst_percent` FROM `products`");
        while ($row = $stmtP->fetch(PDO::FETCH_ASSOC)) {
            $products[$row['product_code']] = $row;
        }

        $currencies = [];
        $stmtCur = $this->db->query("SELECT `id`, `code` FROM `currencies`");
        while ($row = $stmtCur->fetch(PDO::FETCH_ASSOC)) {
            $currencies[$row['code']] = (int)$row['id'];
        }

        $ports = [];
        $stmtPort = $this->db->query("SELECT `id`, `code` FROM `ports`");
        while ($row = $stmtPort->fetch(PDO::FETCH_ASSOC)) {
            $ports[$row['code']] = (int)$row['id'];
        }

        $incoterms = [];
        $stmtIco = $this->db->query("SELECT `id`, `code` FROM `incoterms`");
        while ($row = $stmtIco->fetch(PDO::FETCH_ASSOC)) {
            $incoterms[$row['code']] = (int)$row['id'];
        }

        $paymentTerms = [];
        $stmtPt = $this->db->query("SELECT `id`, `code` FROM `payment_terms`");
        while ($row = $stmtPt->fetch(PDO::FETCH_ASSOC)) {
            $paymentTerms[$row['code']] = (int)$row['id'];
        }

        $warehouses = [];
        $stmtWh = $this->db->query("SELECT `id`, `code` FROM `warehouses`");
        while ($row = $stmtWh->fetch(PDO::FETCH_ASSOC)) {
            $warehouses[$row['code']] = (int)$row['id'];
        }

        $costComponents = [];
        $stmtCc = $this->db->query("SELECT `id`, `code` FROM `cost_components`");
        while ($row = $stmtCc->fetch(PDO::FETCH_ASSOC)) {
            $costComponents[$row['code']] = (int)$row['id'];
        }

        $adminUserId = (int)$this->db->query("SELECT `id` FROM `users` WHERE `username` = 'admin' LIMIT 1")->fetchColumn();
        $managerRoleId = (int)$this->db->query("SELECT `id` FROM `roles` WHERE `name` = 'manager' LIMIT 1")->fetchColumn();

        // 1. Placeholder Files Generation
        $this->generatePlaceholders();

        // 2. Scenario 1: Cumin ➔ Dubai, FOB, Completed
        $this->log("Seeding Scenario 1 (Cumin to Dubai - FOB Completed)...");
        $docId1 = $this->createDocumentChain(
            $companyId,
            (int)$buyers['BY_DXB_001']['id'],
            $currencies['USD'],
            83.500000,
            $incoterms['FOB'],
            $ports['INMUN'],
            $ports['AEJEA'],
            $paymentTerms['30ADV_70CAD'],
            'QTN-26-0001',
            'PI-26-0001',
            'INV-26-0001',
            'PKG-26-0001',
            $products['PRD_CUM_001'],
            24000.00, // 24 MT
            4.5000,   // Rate USD
            $warehouses['WH_UNJ'],
            $costComponents,
            $currencies,
            $adminUserId,
            $managerRoleId,
            'completed' // Final status
        );

        // Seed Shipment & Payment for Scenario 1
        $shipId1 = $this->upsert('shipments', [
            'shipment_number' => 'SHP-26-0001', 'shipment_date' => '2026-05-15', 'buyer_id' => $buyers['BY_DXB_001']['id'],
            'shipping_line_id' => 1, 'freight_forwarder_id' => 1, 'loading_port_id' => $ports['INMUN'],
            'destination_port_id' => $ports['AEJEA'], 'status' => 3, 'created_by' => $adminUserId // 3 = Delivered / Completed
        ], ['shipment_number']);

        $this->upsert('shipment_items', [
            'shipment_id' => $shipId1, 'product_id' => $products['PRD_CUM_001']['id'], 'quantity' => 24000.00
        ], ['shipment_id', 'product_id']);

        $this->upsert('shipment_milestones', [
            'shipment_id' => $shipId1, 'milestone' => 'Vessel Departed Mundra', 'milestone_at' => '2026-05-18 10:00:00',
            'remarks' => 'Maersk Honam Voy 2601', 'created_by' => $adminUserId
        ], ['shipment_id', 'milestone']);
        
        $this->upsert('shipment_milestones', [
            'shipment_id' => $shipId1, 'milestone' => 'Container Arrived Jebel Ali', 'milestone_at' => '2026-05-24 15:30:00',
            'remarks' => 'Completed customs clearance', 'created_by' => $adminUserId
        ], ['shipment_id', 'milestone']);

        // Payment (Received & Fully Allocated)
        $payId1 = $this->upsert('payments', [
            'payment_number' => 'REC-26-0001', 'payment_date' => '2026-05-28', 'party_type' => 'buyer',
            'party_id' => $buyers['BY_DXB_001']['id'], 'bank_id' => 1, 'currency_id' => $currencies['USD'],
            'amount' => 108000.00, 'status' => 2, 'created_by' => $adminUserId // 2 = Approved/Posted
        ], ['payment_number']);

        $this->upsert('payment_allocations', [
            'payment_id' => $payId1, 'document_header_id' => $docId1, 'amount' => 108000.00
        ], ['payment_id', 'document_header_id']);

        // Update Receivables
        $this->upsert('receivables', [
            'buyer_id' => $buyers['BY_DXB_001']['id'], 'document_header_id' => $docId1,
            'amount' => 108000.00 * 83.50, 'paid_amount' => 108000.00 * 83.50, 'status' => 2 // Paid
        ], ['document_header_id']);

        // Stock Ledger movement out
        $this->upsert('stock_ledgers', [
            'product_id' => $products['PRD_CUM_001']['id'], 'warehouse_id' => $warehouses['WH_UNJ'],
            'movement_type' => 'out', 'quantity' => 24000.00, 'source_type' => 'commercial_invoice',
            'source_id' => $docId1, 'created_by' => $adminUserId, 'lot_number' => 'LOT-CUM-2601', 'moisture_percent' => 8.20
        ], ['source_type', 'source_id', 'product_id']);


        // 3. Scenario 2: Fennel ➔ Germany, CIF, In Transit
        $this->log("Seeding Scenario 2 (Fennel to Germany - CIF In Transit)...");
        $docId2 = $this->createDocumentChain(
            $companyId,
            (int)$buyers['BY_HAM_001']['id'],
            $currencies['USD'],
            83.600000,
            $incoterms['CIF'],
            $ports['INMUN'],
            $ports['DEHAM'],
            $paymentTerms['LC_SIGHT'],
            'QTN-26-0002',
            'PI-26-0002',
            'INV-26-0002',
            'PKG-26-0002',
            $products['PRD_FEN_001'],
            20000.00, // 20 MT
            2.8000,
            $warehouses['WH_UNJ'],
            $costComponents,
            $currencies,
            $adminUserId,
            $managerRoleId,
            'approved' // Released / Active
        );

        $shipId2 = $this->upsert('shipments', [
            'shipment_number' => 'SHP-26-0002', 'shipment_date' => '2026-06-25', 'buyer_id' => $buyers['BY_HAM_001']['id'],
            'shipping_line_id' => 1, 'freight_forwarder_id' => 1, 'loading_port_id' => $ports['INMUN'],
            'destination_port_id' => $ports['DEHAM'], 'status' => 2, 'created_by' => $adminUserId // 2 = In Transit
        ], ['shipment_number']);

        $this->upsert('shipment_items', [
            'shipment_id' => $shipId2, 'product_id' => $products['PRD_FEN_001']['id'], 'quantity' => 20000.00
        ], ['shipment_id', 'product_id']);

        $this->upsert('shipment_milestones', [
            'shipment_id' => $shipId2, 'milestone' => 'Vessel Loaded Mundra', 'milestone_at' => '2026-06-28 08:00:00',
            'remarks' => 'MSC Adelaide Voy 104', 'created_by' => $adminUserId
        ], ['shipment_id', 'milestone']);

        // Stock Ledger movement out
        $this->upsert('stock_ledgers', [
            'product_id' => $products['PRD_FEN_001']['id'], 'warehouse_id' => $warehouses['WH_UNJ'],
            'movement_type' => 'out', 'quantity' => 20000.00, 'source_type' => 'commercial_invoice',
            'source_id' => $docId2, 'created_by' => $adminUserId, 'lot_number' => 'LOT-FEN-2601', 'moisture_percent' => 9.10
        ], ['source_type', 'source_id', 'product_id']);

        // Receivables
        $this->upsert('receivables', [
            'buyer_id' => $buyers['BY_HAM_001']['id'], 'document_header_id' => $docId2,
            'amount' => 56000.00 * 83.60, 'paid_amount' => 0.00, 'status' => 1 // Unpaid
        ], ['document_header_id']);


        // 4. Scenario 3: Turmeric ➔ USA, Quotation Pending
        $this->log("Seeding Scenario 3 (Turmeric to USA - Quotation Pending)...");
        $qId3 = $this->upsert('document_headers', [
            'company_id' => $companyId, 'document_type_id' => 1, 'document_number' => 'QTN-26-0003',
            'document_date' => '2026-07-05', 'buyer_id' => $buyers['BY_SFO_001']['id'], 'currency_id' => $currencies['USD'],
            'exchange_rate' => 83.550000, 'rate_locked' => 0, 'lut_active' => 1, 'tax_basis' => 'lut',
            'incoterm_id' => $incoterms['FOB'], 'loading_port_id' => $ports['INNSA'], 'destination_port_id' => $ports['USOAK'],
            'payment_term_id' => $paymentTerms['CAD'], 'status' => 1, 'created_by' => $adminUserId // 1 = Draft / Pending
        ], ['document_number']);

        $this->upsert('document_items', [
            'document_header_id' => $qId3, 'product_id' => $products['PRD_TUR_001']['id'], 'warehouse_id' => $warehouses['WH_AMD'],
            'quantity' => 19000.00, 'rate' => 2.4000, 'net_amount' => 45600.00, 'tax_percent' => 0.00, 'tax_slab_percent' => 5.00
        ], ['document_header_id', 'product_id']);

        // Cost sheet components
        $this->upsert('document_charges', [
            'document_header_id' => $qId3, 'cost_component_id' => $costComponents['CHA_CHARGES'],
            'charge_name' => 'Local CHA Charges', 'charge_amount' => 18000.00,
            'currency_id' => $currencies['INR'], 'exchange_rate' => 1.000000, 'converted_amount_base' => 18000.00
        ], ['document_header_id', 'cost_component_id']);

        // Add pending approval log
        $this->upsert('document_approvals', [
            'document_header_id' => $qId3, 'assigned_role_id' => $managerRoleId, 'approval_status' => 'pending'
        ], ['document_header_id', 'assigned_role_id']);


        // 5. Scenario 4: Sesame ➔ Vietnam, Partial Payment
        $this->log("Seeding Scenario 4 (Sesame to Vietnam - Partial Payment)...");
        $docId4 = $this->createDocumentChain(
            $companyId,
            (int)$buyers['BY_SGN_001']['id'],
            $currencies['USD'],
            83.520000,
            $incoterms['CIF'],
            $ports['INMUN'],
            $ports['NLRTM'],
            $paymentTerms['LC_SIGHT'],
            'QTN-26-0004',
            'PI-26-0004',
            'INV-26-0004',
            'PKG-26-0004',
            $products['PRD_SES_001'],
            19000.00, // 19 MT
            2.1000,
            $warehouses['WH_UNJ'],
            $costComponents,
            $currencies,
            $adminUserId,
            $managerRoleId,
            'approved'
        );

        // Partial Payment Receipt
        $payId4 = $this->upsert('payments', [
            'payment_number' => 'REC-26-0002', 'payment_date' => '2026-06-28', 'party_type' => 'buyer',
            'party_id' => $buyers['BY_SGN_001']['id'], 'bank_id' => 1, 'currency_id' => $currencies['USD'],
            'amount' => 20000.00, 'status' => 2, 'created_by' => $adminUserId
        ], ['payment_number']);

        $this->upsert('payment_allocations', [
            'payment_id' => $payId4, 'document_header_id' => $docId4, 'amount' => 20000.00
        ], ['payment_id', 'document_header_id']);

        // Receivables
        $this->upsert('receivables', [
            'buyer_id' => $buyers['BY_SGN_001']['id'], 'document_header_id' => $docId4,
            'amount' => 39900.00 * 83.52, 'paid_amount' => 20000.00 * 83.52, 'status' => 3 // Partial Paid
        ], ['document_header_id']);


        // 6. Scenario 5: Dehydrated Onion ➔ Malaysia, Shipment Completed
        $this->log("Seeding Scenario 5 (Onion to Malaysia - Shipment Completed)...");
        $docId5 = $this->createDocumentChain(
            $companyId,
            (int)$buyers['BY_PEN_001']['id'],
            $currencies['USD'],
            83.480000,
            $incoterms['CFR'],
            $ports['INNSA'],
            $ports['NLRTM'],
            $paymentTerms['30ADV_70CAD'],
            'QTN-26-0005',
            'PI-26-0005',
            'INV-26-0005',
            'PKG-26-0005',
            $products['PRD_ONN_001'],
            13000.00, // 13 MT
            2.0000,
            $warehouses['WH_AMD'],
            $costComponents,
            $currencies,
            $adminUserId,
            $managerRoleId,
            'completed'
        );

        $shipId5 = $this->upsert('shipments', [
            'shipment_number' => 'SHP-26-0005', 'shipment_date' => '2026-06-10', 'buyer_id' => $buyers['BY_PEN_001']['id'],
            'shipping_line_id' => 2, 'freight_forwarder_id' => 1, 'loading_port_id' => $ports['INNSA'],
            'destination_port_id' => $ports['NLRTM'], 'status' => 3, 'created_by' => $adminUserId
        ], ['shipment_number']);

        $this->upsert('shipment_items', [
            'shipment_id' => $shipId5, 'product_id' => $products['PRD_ONN_001']['id'], 'quantity' => 13000.00
        ], ['shipment_id', 'product_id']);

        $payId5 = $this->upsert('payments', [
            'payment_number' => 'REC-26-0003', 'payment_date' => '2026-06-20', 'party_type' => 'buyer',
            'party_id' => $buyers['BY_PEN_001']['id'], 'bank_id' => 1, 'currency_id' => $currencies['USD'],
            'amount' => 26000.00, 'status' => 2, 'created_by' => $adminUserId
        ], ['payment_number']);

        $this->upsert('payment_allocations', [
            'payment_id' => $payId5, 'document_header_id' => $docId5, 'amount' => 26000.00
        ], ['payment_id', 'document_header_id']);

        // Receivables
        $this->upsert('receivables', [
            'buyer_id' => $buyers['BY_PEN_001']['id'], 'document_header_id' => $docId5,
            'amount' => 26000.00 * 83.48, 'paid_amount' => 26000.00 * 83.48, 'status' => 2 // Fully Paid
        ], ['document_header_id']);


        // 7. Satisfy: "Every Buyer has Quotations" requirement
        $this->log("Generating Quotations for the remaining 15 buyers to ensure 100% CRM coverage...");
        $remainingBuyers = array_diff_key($buyers, [
            'BY_DXB_001' => 1, 'BY_HAM_001' => 1, 'BY_SFO_001' => 1, 'BY_SGN_001' => 1, 'BY_PEN_001' => 1
        ]);
        $k = 6;
        foreach ($remainingBuyers as $code => $byr) {
            $qNo = sprintf("QTN-26-%04d", $k++);
            $this->upsert('document_headers', [
                'company_id' => $companyId, 'document_type_id' => 1, 'document_number' => $qNo,
                'document_date' => '2026-07-08', 'buyer_id' => $byr['id'], 'currency_id' => $currencies['USD'],
                'exchange_rate' => 83.500000, 'rate_locked' => 0, 'lut_active' => 1, 'tax_basis' => 'lut',
                'incoterm_id' => $incoterms['FOB'], 'loading_port_id' => $ports['INMUN'], 'destination_port_id' => $ports['AEJEA'],
                'payment_term_id' => $paymentTerms['30ADV_70CAD'], 'status' => 1, 'created_by' => $adminUserId
            ], ['document_number']);
        }

        // 8. Satisfy: "Every Supplier has Purchase Orders" requirement
        $this->log("Generating Purchase Orders for all 15 suppliers to ensure 100% procurement coverage...");
        $poNum = 1;
        foreach ($suppliers as $code => $sup) {
            $poNo = sprintf("PO-26-%04d", $poNum++);
            $poId = $this->upsert('purchase_orders', [
                'po_number' => $poNo, 'po_date' => '2026-06-05', 'supplier_id' => $sup['id'],
                'currency_id' => $currencies['INR'], 'status' => 2, 'created_by' => $adminUserId // 2 = Approved/Active
            ], ['po_number']);

            $this->upsert('purchase_order_items', [
                'purchase_order_id' => $poId, 'product_id' => $products['PRD_CUM_001']['id'],
                'unit_id' => $products['PRD_CUM_001']['unit_id'], 'quantity' => 1000.00, 'rate' => 290.00, 'amount' => 290000.00
            ], ['purchase_order_id', 'product_id']);

            // Insert stock ledger movement IN (mocking procurement receipts to populate inventory)
            $this->upsert('stock_ledgers', [
                'product_id' => $products['PRD_CUM_001']['id'], 'warehouse_id' => $warehouses['WH_UNJ'],
                'movement_type' => 'in', 'quantity' => 5000.00, 'source_type' => 'purchase_order',
                'source_id' => $poId, 'created_by' => $adminUserId, 'lot_number' => 'LOT-PUR-' . $poNo, 'moisture_percent' => 8.00
            ], ['source_type', 'source_id', 'product_id']);
        }

        // 9. Extra outlays: Expenses and Notifications
        $this->log("Seeding general outlays, notifications, and revision logs...");
        $this->upsert('expenses', [
            'expense_number' => 'EXP-26-0001', 'expense_date' => '2026-06-15', 'expense_category_id' => 2, // Local Freight
            'amount' => 22000.00, 'status' => 2, 'created_by' => $adminUserId
        ], ['expense_number']);

        $this->upsert('notifications', [
            'user_id' => $adminUserId, 'title' => 'New Export Order Recieved', 'message' => 'Order INV-26-0001 has been confirmed by buyer.',
            'related_type' => 'commercial_invoice', 'related_id' => $docId1
        ], ['title']);

        $this->db->exec("
            INSERT IGNORE INTO `email_logs` (`recipient`, `subject`, `status`, `response`)
            VALUES ('tariq@alsadiqspices.ae', 'Order Confirmation QTN-26-0001', 'sent', 'Email sent successfully via SMTP')
        ");

        $this->log("Transaction Seeder completed successfully!");
    }

    /**
     * Helper to create linked Quotation -> PI -> Invoice -> Packing List record chain.
     */
    private function createDocumentChain(
        int $companyId,
        int $buyerId,
        int $currId,
        float $rate,
        int $incotermId,
        int $loadPort,
        int $destPort,
        int $payTerm,
        string $qNo,
        string $piNo,
        string $invNo,
        string $pkgNo,
        array $product,
        float $qty,
        float $itemRate,
        int $warehouseId,
        array $costComponents,
        array $currencies,
        int $userId,
        int $roleId,
        string $finalStatus
    ): int {
        // 1. Quotation
        $qId = $this->upsert('document_headers', [
            'company_id' => $companyId, 'document_type_id' => 1, 'document_number' => $qNo,
            'document_date' => '2026-05-01', 'buyer_id' => $buyerId, 'currency_id' => $currId,
            'exchange_rate' => $rate, 'rate_locked' => 1, 'lut_active' => 1, 'tax_basis' => 'lut',
            'incoterm_id' => $incotermId, 'loading_port_id' => $loadPort, 'destination_port_id' => $destPort,
            'payment_term_id' => $payTerm, 'status' => 2, 'created_by' => $userId, 'approved_by' => $userId // 2 = Approved
        ], ['document_number']);

        $this->upsert('document_items', [
            'document_header_id' => $qId, 'product_id' => $product['id'], 'warehouse_id' => $warehouseId,
            'quantity' => $qty, 'rate' => $itemRate, 'net_amount' => $qty * $itemRate, 'tax_percent' => 0.00, 'tax_slab_percent' => $product['gst_percent']
        ], ['document_header_id', 'product_id']);

        // Local handling charge
        $this->upsert('document_charges', [
            'document_header_id' => $qId, 'cost_component_id' => $costComponents['CHA_CHARGES'],
            'charge_name' => 'Local CHA Charges', 'charge_amount' => 18000.00,
            'currency_id' => $currencies['INR'], 'exchange_rate' => 1.000000, 'converted_amount_base' => 18000.00
        ], ['document_header_id', 'cost_component_id']);

        // 2. Proforma Invoice (Converted from Quotation)
        $piId = $this->upsert('document_headers', [
            'company_id' => $companyId, 'document_type_id' => 2, 'document_number' => $piNo,
            'document_date' => '2026-05-05', 'buyer_id' => $buyerId, 'currency_id' => $currId,
            'exchange_rate' => $rate, 'rate_locked' => 1, 'lut_active' => 1, 'tax_basis' => 'lut',
            'incoterm_id' => $incotermId, 'loading_port_id' => $loadPort, 'destination_port_id' => $destPort,
            'payment_term_id' => $payTerm, 'status' => 2, 'created_by' => $userId, 'approved_by' => $userId,
            'converted_from_id' => $qId
        ], ['document_number']);

        $this->upsert('document_items', [
            'document_header_id' => $piId, 'product_id' => $product['id'], 'warehouse_id' => $warehouseId,
            'quantity' => $qty, 'rate' => $itemRate, 'net_amount' => $qty * $itemRate, 'tax_percent' => 0.00, 'tax_slab_percent' => $product['gst_percent']
        ], ['document_header_id', 'product_id']);

        $this->upsert('document_charges', [
            'document_header_id' => $piId, 'cost_component_id' => $costComponents['CHA_CHARGES'],
            'charge_name' => 'Local CHA Charges', 'charge_amount' => 18000.00,
            'currency_id' => $currencies['INR'], 'exchange_rate' => 1.000000, 'converted_amount_base' => 18000.00
        ], ['document_header_id', 'cost_component_id']);

        // Update Quotation destination
        $this->db->prepare("UPDATE `document_headers` SET `converted_to_id` = :to_id WHERE `id` = :from_id")->execute([
            'to_id' => $piId, 'from_id' => $qId
        ]);

        // 3. Commercial Invoice (Converted from PI)
        $invStatus = $finalStatus === 'completed' ? 4 : 2; // 4 = Completed/Paid; 2 = Approved/Active
        $invId = $this->upsert('document_headers', [
            'company_id' => $companyId, 'document_type_id' => 3, 'document_number' => $invNo,
            'document_date' => '2026-05-10', 'buyer_id' => $buyerId, 'currency_id' => $currId,
            'exchange_rate' => $rate, 'rate_locked' => 1, 'lut_active' => 1, 'tax_basis' => 'lut',
            'incoterm_id' => $incotermId, 'loading_port_id' => $loadPort, 'destination_port_id' => $destPort,
            'payment_term_id' => $payTerm, 'status' => $invStatus, 'created_by' => $userId, 'approved_by' => $userId,
            'converted_from_id' => $piId
        ], ['document_number']);

        $this->upsert('document_items', [
            'document_header_id' => $invId, 'product_id' => $product['id'], 'warehouse_id' => $warehouseId,
            'quantity' => $qty, 'rate' => $itemRate, 'net_amount' => $qty * $itemRate, 'tax_percent' => 0.00, 'tax_slab_percent' => $product['gst_percent']
        ], ['document_header_id', 'product_id']);

        $this->upsert('document_charges', [
            'document_header_id' => $invId, 'cost_component_id' => $costComponents['CHA_CHARGES'],
            'charge_name' => 'Local CHA Charges', 'charge_amount' => 18000.00,
            'currency_id' => $currencies['INR'], 'exchange_rate' => 1.000000, 'converted_amount_base' => 18000.00
        ], ['document_header_id', 'cost_component_id']);

        $this->db->prepare("UPDATE `document_headers` SET `converted_to_id` = :to_id WHERE `id` = :from_id")->execute([
            'to_id' => $invId, 'from_id' => $piId
        ]);

        // Add signed-off approval workflow
        $this->upsert('document_approvals', [
            'document_header_id' => $invId, 'assigned_role_id' => $roleId, 'approver_id' => $userId,
            'approval_status' => 'approved', 'remarks' => 'Sourcing, price, and logistics checklist validated.',
            'actioned_at' => date('Y-m-d H:i:s')
        ], ['document_header_id', 'assigned_role_id']);

        // 4. Packing List (Converted from Invoice)
        // Calculate containerSuggested array: net_weight, gross_weight, packages, volume
        $pkgSizeKg = $product['net_weight'] ?: 25.000;
        $packagesCount = (int)ceil($qty / $pkgSizeKg);
        $totalVolCbm = $packagesCount * ($product['volume_per_package_cbm'] ?: 0.065);
        $totalGrossKg = $packagesCount * ($product['gross_weight'] ?: 25.12);

        $containersJson = json_encode([
            'containers' => [
                [
                    'size' => '20FT_GP',
                    'count' => 1,
                    'packages' => $packagesCount,
                    'volume_cbm' => round($totalVolCbm, 2),
                    'weight_kg' => round($totalGrossKg, 2)
                ]
            ]
        ]);

        $pkgId = $this->upsert('document_headers', [
            'company_id' => $companyId, 'document_type_id' => 4, 'document_number' => $pkgNo,
            'document_date' => '2026-05-12', 'buyer_id' => $buyerId, 'currency_id' => $currId,
            'exchange_rate' => $rate, 'rate_locked' => 1, 'lut_active' => 1, 'tax_basis' => 'lut',
            'incoterm_id' => $incotermId, 'loading_port_id' => $loadPort, 'destination_port_id' => $destPort,
            'payment_term_id' => $payTerm, 'status' => 2, 'created_by' => $userId, 'approved_by' => $userId,
            'converted_from_id' => $invId, 'estimated_containers_json' => $containersJson
        ], ['document_number']);

        $this->upsert('document_items', [
            'document_header_id' => $pkgId, 'product_id' => $product['id'], 'warehouse_id' => $warehouseId,
            'quantity' => $qty, 'rate' => $itemRate, 'net_amount' => $qty * $itemRate, 'tax_percent' => 0.00, 'tax_slab_percent' => $product['gst_percent']
        ], ['document_header_id', 'product_id']);

        $this->db->prepare("UPDATE `document_headers` SET `converted_to_id` = :to_id WHERE `id` = :from_id")->execute([
            'to_id' => $pkgId, 'from_id' => $invId
        ]);

        // Add attachment file reference
        $this->upsert('document_attachments', [
            'document_header_id' => $invId, 'file_name' => "signed_commercial_invoice_{$invNo}.pdf",
            'original_name' => "INV-Signed.pdf", 'file_path' => "/uploads/documents/signed_invoice_{$invId}.pdf",
            'file_type' => 'application/pdf', 'file_size' => 124500, 'attachment_type' => 'invoice_signed', 'uploaded_by' => $userId
        ], ['document_header_id', 'file_name']);

        // Add revision managers log
        $this->upsert('document_revisions', [
            'document_header_id' => $invId, 'revision_number' => 1,
            'document_data' => json_encode(['id' => $invId, 'number' => $invNo, 'total' => $qty * $itemRate]),
            'revision_notes' => 'Initial release base snapshot.', 'created_by' => $userId
        ], ['document_header_id', 'revision_number']);

        return $invId;
    }

    /**
     * Create local directory structures and dummy file payloads for testing.
     */
    private function generatePlaceholders(): void
    {
        $dirs = [
            APP_ROOT . '/uploads',
            APP_ROOT . '/uploads/products',
            APP_ROOT . '/uploads/documents'
        ];
        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }

        // Mock PDF files
        $docs = [
            'signed_invoice_1.pdf' => 'Dummy PDF: Signed Commercial Invoice.',
            'signed_invoice_2.pdf' => 'Dummy PDF: Signed Commercial Invoice.',
            'signed_invoice_4.pdf' => 'Dummy PDF: Signed Commercial Invoice.',
            'signed_invoice_5.pdf' => 'Dummy PDF: Signed Commercial Invoice.',
            'spec_sheet.pdf' => 'Dummy PDF: Agricultural Product Spec Sheet.',
            'lab_report.pdf' => 'Dummy PDF: SGS Quality Analysis Report.'
        ];
        foreach ($docs as $name => $txt) {
            $path = APP_ROOT . '/uploads/documents/' . $name;
            if (!file_exists($path)) {
                file_put_contents($path, $txt);
            }
        }
    }
}
