<?php
declare(strict_types=1);

namespace App\Core\Seeds;

use PDO;
use DateTime;
use DateInterval;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $this->log("Starting Currency Seeder...");

        // 1. Currencies
        $currencies = [
            ['code' => 'INR', 'name' => 'Indian Rupee', 'symbol' => '₹', 'exchange_rate' => 1.000000, 'status' => 1],
            ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'exchange_rate' => 0.011976, 'status' => 1], // Base rates to 1 INR
            ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€', 'exchange_rate' => 0.011099, 'status' => 1],
            ['code' => 'AED', 'name' => 'UAE Dirham', 'symbol' => 'د.إ', 'exchange_rate' => 0.043987, 'status' => 1],
            ['code' => 'SAR', 'name' => 'Saudi Riyal', 'symbol' => 'ر.س', 'exchange_rate' => 0.044910, 'status' => 1],
            ['code' => 'GBP', 'name' => 'British Pound', 'symbol' => '£', 'exchange_rate' => 0.009452, 'status' => 1],
            ['code' => 'MYR', 'name' => 'Malaysian Ringgit', 'symbol' => 'RM', 'exchange_rate' => 0.056497, 'status' => 1],
            ['code' => 'CAD', 'name' => 'Canadian Dollar', 'symbol' => 'C$', 'exchange_rate' => 0.016393, 'status' => 1],
            ['code' => 'AUD', 'name' => 'Australian Dollar', 'symbol' => 'A$', 'exchange_rate' => 0.018051, 'status' => 1]
        ];

        $currencyIds = [];
        foreach ($currencies as $curr) {
            $currencyIds[$curr['code']] = $this->upsert('currencies', $curr, ['code']);
        }
        $this->log("Seeded Currencies.");

        // 2. Exchange Rates History (90 Days)
        // Table columns: currency_id, rate_date, rate, source
        // Let's use rates expressed as: how many INR per 1 unit of that currency (e.g. 1 USD = 83.50 INR)
        $baseExchangeRates = [
            'USD' => 83.500000,
            'EUR' => 90.100000,
            'AED' => 22.730000,
            'SAR' => 22.260000,
            'GBP' => 105.800000,
            'MYR' => 17.700000,
            'CAD' => 61.000000,
            'AUD' => 55.400000
        ];

        $startDate = new DateTime('2026-04-10'); // 90 days before July 10, 2026
        $interval = new DateInterval('P1D');

        $this->log("Generating 90 days of exchange rates with floating daily variations...");
        
        $this->db->beginTransaction();
        try {
            for ($i = 0; $i < 91; $i++) {
                $rateDate = $startDate->format('Y-m-d');
                
                foreach ($baseExchangeRates as $code => $baseRate) {
                    $currId = $currencyIds[$code];
                    
                    // Add a tiny pseudo-random daily fluctuation (-0.5% to +0.5%)
                    $seed = crc32($code . $rateDate);
                    mt_srand($seed);
                    $factor = 1 + (mt_rand(-50, 50) / 10000); // e.g. 0.9950 to 1.0050
                    $fluctuatedRate = round($baseRate * $factor, 6);

                    // Insert or update rate record
                    $this->db->prepare("
                        INSERT INTO `exchange_rates` (`currency_id`, `rate_date`, `rate`, `source`)
                        VALUES (:cid, :rdate, :rate, 'api_live')
                        ON DUPLICATE KEY UPDATE `rate` = :rate_update
                    ")->execute([
                        'cid' => $currId,
                        'rdate' => $rateDate,
                        'rate' => $fluctuatedRate,
                        'rate_update' => $fluctuatedRate
                    ]);
                }
                
                $startDate->add($interval);
            }
            $this->db->commit();
            $this->log("Seeded 90-day Exchange Rates history.");
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->log("Error seeding exchange rates: " . $e->getMessage());
            throw $e;
        }

        $this->log("Currency Seeder completed successfully!");
    }
}
