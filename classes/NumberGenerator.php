<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use Exception;

/**
 * Number Generator for Document Series
 */
class NumberGenerator
{
    private PDO $db;
    private const TABLE = 'number_series';

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Generate next number for a series
     */
    public function generate(string $seriesName, ?string $year = null): string
    {
        $this->db->beginTransaction();
        try {
            $year = $year ?? date('Y');
            
            // Get current series
            $stmt = $this->db->prepare("
                SELECT * FROM " . self::TABLE . " 
                WHERE name = :name AND status = 1
                FOR UPDATE
            ");
            $stmt->execute(['name' => $seriesName]);
            $series = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$series) {
                throw new Exception("Number series '{$seriesName}' not found");
            }

            $nextNumber = $series['next_number'];
            $prefix = $series['prefix'];
            $padding = $series['padding'];
            $suffix = $series['suffix'] ?? '';

            // Update next number
            $updateStmt = $this->db->prepare("
                UPDATE " . self::TABLE . " 
                SET next_number = next_number + 1, updated_at = NOW()
                WHERE id = :id
            ");
            $updateStmt->execute(['id' => $series['id']]);

            $this->db->commit();

            // Format: PREFIX-YEAR-PADDING
            $formattedNumber = $prefix . '-' . $year . '-' . str_pad((string) $nextNumber, $padding, '0', STR_PAD_LEFT);
            
            if ($suffix) {
                $formattedNumber .= '-' . $suffix;
            }

            return $formattedNumber;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Get next number without formatting
     */
    public function getNextNumber(string $seriesName): int
    {
        $stmt = $this->db->prepare("
            SELECT next_number FROM " . self::TABLE . " 
            WHERE name = :name AND status = 1
        ");
        $stmt->execute(['name' => $seriesName]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$result) {
            throw new Exception("Number series '{$seriesName}' not found");
        }

        return (int) $result['next_number'];
    }

    /**
     * Reset series number
     */
    public function reset(string $seriesName, int $startFrom = 1): bool
    {
        $stmt = $this->db->prepare("
            UPDATE " . self::TABLE . " 
            SET next_number = :start_from, updated_at = NOW()
            WHERE name = :name
        ");
        return $stmt->execute([
            'name' => $seriesName,
            'start_from' => $startFrom,
        ]);
    }

    /**
     * Initialize default series
     */
    public static function initializeDefaultSeries(PDO $db): void
    {
        $series = [
            ['name' => 'quotation', 'prefix' => 'QTN', 'padding' => 5],
            ['name' => 'proforma_invoice', 'prefix' => 'PI', 'padding' => 5],
            ['name' => 'commercial_invoice', 'prefix' => 'CI', 'padding' => 5],
            ['name' => 'packing_list', 'prefix' => 'PL', 'padding' => 5],
            ['name' => 'shipping_bill', 'prefix' => 'SB', 'padding' => 5],
            ['name' => 'bill_of_lading', 'prefix' => 'BL', 'padding' => 5],
            ['name' => 'certificate_of_origin', 'prefix' => 'CO', 'padding' => 5],
            ['name' => 'non_hazardous_cert', 'prefix' => 'NHC', 'padding' => 5],
            ['name' => 'phytosanitary', 'prefix' => 'PS', 'padding' => 5],
            ['name' => 'insurance', 'prefix' => 'INS', 'padding' => 5],
            ['name' => 'inspection', 'prefix' => 'INS', 'padding' => 5],
            ['name' => 'payment_receipt', 'prefix' => 'PR', 'padding' => 5],
        ];

        $stmt = $db->prepare("
            INSERT IGNORE INTO " . self::TABLE . " 
            (name, prefix, next_number, padding, status, created_at) 
            VALUES (:name, :prefix, 1, :padding, 1, NOW())
        ");

        foreach ($series as $s) {
            $stmt->execute([
                'name' => $s['name'],
                'prefix' => $s['prefix'],
                'padding' => $s['padding'],
            ]);
        }
    }
}