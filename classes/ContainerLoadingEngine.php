<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

/**
 * Container Loading Engine
 * Analyzes shipment weights and volumes to recommend container loading mixes
 */
class ContainerLoadingEngine
{
    private PDO $db;
    private UnitConversionEngine $unitEngine;

    // Standard industrial container specifications
    private const CONTAINER_SPECS = [
        '20FT_DRY' => [
            'name' => '20ft Dry Container',
            'max_weight_kg' => 24000.0,
            'max_volume_cbm' => 33.0,
        ],
        '40FT_DRY' => [
            'name' => '40ft Dry Container',
            'max_weight_kg' => 26500.0,
            'max_volume_cbm' => 67.0,
        ],
        '40FT_HC' => [
            'name' => '40ft High Cube Container',
            'max_weight_kg' => 26500.0,
            'max_volume_cbm' => 76.0,
        ]
    ];

    public function __construct(PDO $db, UnitConversionEngine $unitEngine)
    {
        $this->db = $db;
        $this->unitEngine = $unitEngine;
    }

    /**
     * Estimates cargo specifications and recommends the container load configuration
     */
    public function estimateContainers(int $documentId): array
    {
        // 1. Fetch document items
        $stmt = $this->db->prepare("
            SELECT di.*, p.net_weight, p.gross_weight, p.volume_per_package_cbm, u.code as unit_code, di.unit_id
            FROM document_items di
            JOIN products p ON di.product_id = p.id
            LEFT JOIN units u ON di.unit_id = u.id
            WHERE di.document_header_id = :id
        ");
        $stmt->execute(['id' => $documentId]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $totalNetWeight = 0.0;
        $totalGrossWeight = 0.0;
        $totalVolumeCbm = 0.0;
        $totalPackages = 0.0;

        $bagUnitId = $this->getUnitIdByCode('BAG');
        $kgUnitId = $this->getUnitIdByCode('KG');

        foreach ($items as $item) {
            $qty = (float) $item['quantity'];
            $productId = (int) $item['product_id'];
            $unitId = (int) $item['unit_id'];
            $unitCode = strtoupper((string) ($item['unit_code'] ?? ''));

            $itemNetWeightPerPkg = (float) ($item['net_weight'] ?? 0.0);
            $itemGrossWeightPerPkg = (float) ($item['gross_weight'] ?? 0.0);
            $itemVolumePerPkg = (float) ($item['volume_per_package_cbm'] ?? 0.0);

            // A. Standardize to packaging count (Bags/Boxes) if quantity is entered in KG or MT
            $packagesCount = 0.0;
            if ($this->isWeightUnit($unitCode)) {
                if ($bagUnitId !== null) {
                    $packagesCount = $this->unitEngine->convert($qty, $unitId, $bagUnitId, $productId);
                } else {
                    $packagesCount = $itemNetWeightPerPkg > 0.0 ? ($qty / $itemNetWeightPerPkg) : $qty;
                }
            } else {
                $packagesCount = $qty; // Already entered in bags/boxes
            }

            // B. Calculate weights and volume
            $itemNet = $this->unitEngine->convert($qty, $unitId, $kgUnitId ?: $unitId, $productId);
            
            // Gross weight fallback
            $grossMultiplier = $itemNetWeightPerPkg > 0.0 ? ($itemGrossWeightPerPkg / $itemNetWeightPerPkg) : 1.05;
            $itemGross = $itemGrossWeightPerPkg > 0.0 ? ($packagesCount * $itemGrossWeightPerPkg) : ($itemNet * $grossMultiplier);

            // Volume fallback (assumes 0.06 CBM per 25-50kg package if missing)
            $itemVol = $itemVolumePerPkg > 0.0 ? ($packagesCount * $itemVolumePerPkg) : ($packagesCount * 0.06);

            $totalNetWeight += $itemNet;
            $totalGrossWeight += $itemGross;
            $totalVolumeCbm += $itemVol;
            $totalPackages += $packagesCount;
        }

        // 2. Determine container recommendation mix
        $recommendation = $this->runRecommendationAlgorithm($totalGrossWeight, $totalVolumeCbm);

        // 3. Construct response output
        $summary = [
            'total_packages' => $totalPackages,
            'total_net_weight_kg' => $totalNetWeight,
            'total_gross_weight_kg' => $totalGrossWeight,
            'total_volume_cbm' => $totalVolumeCbm,
            'recommendation' => $recommendation
        ];

        // Cache the estimated cargo details into the document header
        $updateStmt = $this->db->prepare("
            UPDATE document_headers 
            SET estimated_containers_json = :json 
            WHERE id = :id
        ");
        $updateStmt->execute([
            'json' => json_encode($summary),
            'id' => $documentId
        ]);

        return $summary;
    }

    /**
     * Sizing logic matching weight/volume to container specifications
     */
    private function runRecommendationAlgorithm(float $weight, float $volume): array
    {
        if ($weight === 0.0 || $volume === 0.0) {
            return [];
        }

        $spec20 = self::CONTAINER_SPECS['20FT_DRY'];
        $spec40 = self::CONTAINER_SPECS['40FT_DRY'];
        $spec40hc = self::CONTAINER_SPECS['40FT_HC'];

        // Check if fits in one 20ft container
        if ($weight <= $spec20['max_weight_kg'] && $volume <= $spec20['max_volume_cbm']) {
            return [
                'type' => '20FT_DRY',
                'count' => 1,
                'name' => $spec20['name'],
                'utilization' => [
                    'weight_percent' => ($weight / $spec20['max_weight_kg']) * 100.0,
                    'volume_percent' => ($volume / $spec20['max_volume_cbm']) * 100.0
                ]
            ];
        }

        // Check if fits in one 40ft container
        if ($weight <= $spec40['max_weight_kg'] && $volume <= $spec40['max_volume_cbm']) {
            return [
                'type' => '40FT_DRY',
                'count' => 1,
                'name' => $spec40['name'],
                'utilization' => [
                    'weight_percent' => ($weight / $spec40['max_weight_kg']) * 100.0,
                    'volume_percent' => ($volume / $spec40['max_volume_cbm']) * 100.0
                ]
            ];
        }

        // Check if fits in one 40ft High Cube
        if ($weight <= $spec40hc['max_weight_kg'] && $volume <= $spec40hc['max_volume_cbm']) {
            return [
                'type' => '40FT_HC',
                'count' => 1,
                'name' => $spec40hc['name'],
                'utilization' => [
                    'weight_percent' => ($weight / $spec40hc['max_weight_kg']) * 100.0,
                    'volume_percent' => ($volume / $spec40hc['max_volume_cbm']) * 100.0
                ]
            ];
        }

        // Multi-container calculations (e.g. split into multiples of 20ft or 40ft)
        // Default to a count of 40ft HC containers based on the max volume restriction
        $countByVolume = ceil($volume / $spec40hc['max_volume_cbm']);
        $countByWeight = ceil($weight / $spec40hc['max_weight_kg']);
        $hcCount = max($countByVolume, $countByWeight);

        return [
            'type' => '40FT_HC',
            'count' => (int) $hcCount,
            'name' => $spec40hc['name'],
            'utilization' => [
                'weight_percent' => ($weight / ($hcCount * $spec40hc['max_weight_kg'])) * 100.0,
                'volume_percent' => ($volume / ($hcCount * $spec40hc['max_volume_cbm'])) * 100.0
            ]
        ];
    }

    private function isWeightUnit(string $code): bool
    {
        return in_array(strtoupper($code), ['KG', 'KGS', 'MT', 'TONS', 'TON'], true);
    }

    private function getUnitIdByCode(string $code): ?int
    {
        $stmt = $this->db->prepare("SELECT id FROM units WHERE UPPER(code) = UPPER(:code) LIMIT 1");
        $stmt->execute(['code' => $code]);
        $id = $stmt->fetchColumn();
        return $id !== false ? (int) $id : null;
    }
}
