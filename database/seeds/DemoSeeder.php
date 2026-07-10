<?php
declare(strict_types=1);

namespace App\Core\Seeds;

use PDO;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->log("==================================================");
        $this->log("Executing MESIGO ERP Demo & Dev Database Seeder");
        $this->log("==================================================");

        // Run all seeders in topological dependency order
        $this->log("Step 1: Running Master Metadata Seeder...");
        (new MasterSeeder($this->db))->run();

        $this->log("Step 2: Running Currency Engine Seeder...");
        (new CurrencySeeder($this->db))->run();

        $this->log("Step 3: Running Unit Conversion Seeder...");
        (new UnitSeeder($this->db))->run();

        $this->log("Step 4: Running Buyer Sourcing Seeder...");
        (new BuyerSeeder($this->db))->run();

        $this->log("Step 5: Running Supplier Sourcing Seeder...");
        (new SupplierSeeder($this->db))->run();

        $this->log("Step 6: Running Product Sourcing Seeder...");
        (new ProductSeeder($this->db))->run();

        $this->log("Step 7: Running Transaction Sourcing Seeder...");
        (new TransactionSeeder($this->db))->run();

        $this->log("==================================================");
        $this->log("Demo Database Seeding Complete!");
        $this->log("==================================================");
    }
}
