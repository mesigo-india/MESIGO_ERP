<?php
declare(strict_types=1);

namespace App\Core\Seeds;

use PDO;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $this->log("Starting Unit Seeder...");

        // 1. Units
        $units = [
            ['code' => 'KG', 'name' => 'Kilogram', 'description' => 'Standard unit of mass', 'status' => 1],
            ['code' => 'MT', 'name' => 'Metric Ton', 'description' => '1000 Kilograms', 'status' => 1],
            ['code' => 'GM', 'name' => 'Gram', 'description' => '0.001 Kilograms', 'status' => 1],
            ['code' => 'BAG', 'name' => 'Bag', 'description' => 'Agricultural package bag (standard 25kg or 50kg)', 'status' => 1],
            ['code' => 'L', 'name' => 'Liter', 'description' => 'Standard unit of volume', 'status' => 1],
            ['code' => 'ML', 'name' => 'Milliliter', 'description' => '0.001 Liters', 'status' => 1],
            ['code' => 'CTN', 'name' => 'Carton', 'description' => 'Standard carton box', 'status' => 1],
            ['code' => 'PLT', 'name' => 'Pallet', 'description' => 'Shipping pallet', 'status' => 1]
        ];

        $unitIds = [];
        foreach ($units as $unit) {
            $unitIds[$unit['code']] = $this->upsert('units', $unit, ['code']);
        }
        $this->log("Seeded Units.");

        // 2. Global Unit Conversions (product_id = NULL)
        $conversions = [
            // From KG
            ['from_unit_id' => $unitIds['KG'], 'to_unit_id' => $unitIds['MT'], 'factor' => 0.001000000, 'product_id' => null, 'status' => 1],
            ['from_unit_id' => $unitIds['KG'], 'to_unit_id' => $unitIds['GM'], 'factor' => 1000.000000000, 'product_id' => null, 'status' => 1],
            // From MT
            ['from_unit_id' => $unitIds['MT'], 'to_unit_id' => $unitIds['KG'], 'factor' => 1000.000000000, 'product_id' => null, 'status' => 1],
            // From GM
            ['from_unit_id' => $unitIds['GM'], 'to_unit_id' => $unitIds['KG'], 'factor' => 0.001000000, 'product_id' => null, 'status' => 1],
            // From L
            ['from_unit_id' => $unitIds['L'], 'to_unit_id' => $unitIds['ML'], 'factor' => 1000.000000000, 'product_id' => null, 'status' => 1],
            // From ML
            ['from_unit_id' => $unitIds['ML'], 'to_unit_id' => $unitIds['L'], 'factor' => 0.001000000, 'product_id' => null, 'status' => 1],
            // From CTN
            ['from_unit_id' => $unitIds['CTN'], 'to_unit_id' => $unitIds['PLT'], 'factor' => 0.020000000, 'product_id' => null, 'status' => 1], // 50 Cartons per Pallet
            // From PLT
            ['from_unit_id' => $unitIds['PLT'], 'to_unit_id' => $unitIds['CTN'], 'factor' => 50.000000000, 'product_id' => null, 'status' => 1]
        ];

        foreach ($conversions as $conv) {
            $this->upsert('unit_conversions', $conv, ['from_unit_id', 'to_unit_id', 'product_id']);
        }
        $this->log("Seeded Global Unit Conversions.");

        $this->log("Unit Seeder completed successfully!");
    }
}
