<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use Exception;

/**
 * Currency Conversion Engine
 * Decoupled, reusable service for all currency calculations in MESIGO ERP
 */
class CurrencyConversionEngine
{
    private PDO $db;
    private ?int $baseCurrencyId = null;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Convert an amount between two currencies
     * Supports direct input of locked rates, historical rate log lookups, and standard base fallbacks
     */
    public function convert(
        float $amount,
        int $fromCurrencyId,
        int $toCurrencyId,
        ?string $rateDate = null,
        ?float $lockedRate = null
    ): float {
        if ($fromCurrencyId === $toCurrencyId || $amount === 0.0) {
            return $amount;
        }

        $baseId = $this->getBaseCurrencyId();

        // 1. If a locked rate is provided directly, perform direct multiplication/division against the base currency
        if ($lockedRate !== null && $lockedRate > 0.0) {
            if ($fromCurrencyId === $baseId) {
                // Converting from Base (INR) to Foreign (USD) using locked USD rate: divide
                return $amount / $lockedRate;
            }
            if ($toCurrencyId === $baseId) {
                // Converting from Foreign (USD) to Base (INR) using locked USD rate: multiply
                return $amount * $lockedRate;
            }
            // Cross-currency conversion with locked rate for the foreign source
            // Convert source to base using locked rate, then base to target using standard rates
            $baseVal = $amount * $lockedRate;
            return $this->convert($baseVal, $baseId, $toCurrencyId, $rateDate);
        }

        // 2. Fetch rates relative to base currency (e.g. 1 USD = 83.50 INR, 1 INR = 1.00 INR)
        $fromRate = $this->getRate($fromCurrencyId, $rateDate);
        $toRate = $this->getRate($toCurrencyId, $rateDate);

        if ($fromRate <= 0.0 || $toRate <= 0.0) {
            throw new Exception("Invalid or missing exchange rates for currency conversion: FromRate={$fromRate}, ToRate={$toRate}");
        }

        // 3. Perform cross-rate conversion: (Amount * FromRate) / ToRate
        // E.g. USD to EUR: (100 USD * 83.50 INR/USD) / 90.50 INR/EUR = 92.26 EUR
        return ($amount * $fromRate) / $toRate;
    }

    /**
     * Retrieves the exchange rate for a currency relative to the base currency
     * Looks up historical exchange_rates logs first, falling back to standard rate in currencies table
     */
    public function getRate(int $currencyId, ?string $rateDate = null): float
    {
        $baseId = $this->getBaseCurrencyId();
        if ($currencyId === $baseId) {
            return 1.0;
        }

        // A. Look up date-specific historical log if a date is provided
        if ($rateDate !== null) {
            $stmt = $this->db->prepare("
                SELECT rate FROM exchange_rates 
                WHERE currency_id = :currency_id AND rate_date = :rate_date 
                LIMIT 1
            ");
            $stmt->execute(['currency_id' => $currencyId, 'rate_date' => $rateDate]);
            $rate = $stmt->fetchColumn();
            if ($rate !== false) {
                return (float) $rate;
            }
        }

        // B. Fallback to standard exchange rate in the currencies table
        $stmt = $this->db->prepare("SELECT exchange_rate FROM currencies WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $currencyId]);
        $rate = $stmt->fetchColumn();
        
        return $rate !== false ? (float) $rate : 0.0;
    }

    /**
     * Get the Base Currency ID of the system (INR)
     */
    public function getBaseCurrencyId(): int
    {
        if ($this->baseCurrencyId !== null) {
            return $this->baseCurrencyId;
        }

        // Look for the default flag first
        $stmt = $this->db->prepare("SELECT id FROM currencies WHERE is_default = 1 LIMIT 1");
        $stmt->execute();
        $id = $stmt->fetchColumn();
        if ($id !== false) {
            $this->baseCurrencyId = (int) $id;
            return $this->baseCurrencyId;
        }

        // Fallback: search by code
        $stmt = $this->db->prepare("SELECT id FROM currencies WHERE UPPER(code) = 'INR' LIMIT 1");
        $stmt->execute();
        $id = $stmt->fetchColumn();
        if ($id !== false) {
            $this->baseCurrencyId = (int) $id;
            return $this->baseCurrencyId;
        }

        // Hard fallback to standard autoincrement index 1
        $this->baseCurrencyId = 1;
        return 1;
    }
}
