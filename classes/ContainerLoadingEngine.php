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
        // 1. Fetch document items with document type info
        $stmt = $this->db->prepare("
            SELECT di.*, p.net_weight, p.gross_weight, p.volume_per_package_cbm, u.code as unit_code, di.unit_id,
                   dt.code as document_type_code
            FROM document_items di
            JOIN document_headers dh ON di.document_header_id = dh.id
            JOIN document_types dt ON dh.document_type_id = dt.id
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
            $docType = $item['document_type_code'] ?? '';

            $quality = json_decode((string) ($item['quality'] ?? ''), true);

            if ($docType === 'packing_list') {
                $packagesCount = $qty;
                $itemNet = (float) ($item['net_weight'] ?? 0.0);
                $itemGross = (float) ($item['gross_weight'] ?? 0.0);
                $cbmPerPkg = (float) ($quality['net_weight_per_package'] ?? $item['volume_per_package_cbm'] ?? 0.0); // fallback or direct
                $itemVol = (float) ($quality['cbm'] ?? ($packagesCount * (float)($quality['cbm_per_package'] ?? $item['volume_per_package_cbm'] ?? 0.0)));
                if ($itemVol <= 0.0) {
                    $itemVol = $packagesCount * 0.06;
                }
            } else {
                $itemNetWeightPerPkg = (float) ($item['net_weight'] ?? 0.0);
                $itemGrossWeightPerPkg = (float) ($item['gross_weight'] ?? 0.0);
                $itemVolumePerPkg = (float) ($item['volume_per_package_cbm'] ?? 0.0);

                $packagesCount = 0.0;
                if ($this->isWeightUnit($unitCode)) {
                    if ($bagUnitId !== null) {
                        $packagesCount = $this->unitEngine->convert($qty, $unitId, $bagUnitId, $productId);
                    } else {
                        $packagesCount = $itemNetWeightPerPkg > 0.0 ? ($qty / $itemNetWeightPerPkg) : $qty;
                    }
                } else {
                    $packagesCount = $qty;
                }

                $itemNet = $this->unitEngine->convert($qty, $unitId, $kgUnitId ?: $unitId, $productId);
                $grossMultiplier = $itemNetWeightPerPkg > 0.0 ? ($itemGrossWeightPerPkg / $itemNetWeightPerPkg) : 1.05;
                $itemGross = $itemGrossWeightPerPkg > 0.0 ? ($packagesCount * $itemGrossWeightPerPkg) : ($itemNet * $grossMultiplier);
                $itemVol = $itemVolumePerPkg > 0.0 ? ($packagesCount * $itemVolumePerPkg) : ($packagesCount * 0.06);
            }

            $totalNetWeight += $itemNet;
            $totalGrossWeight += $itemGross;
            $totalVolumeCbm += $itemVol;
            $totalPackages += $packagesCount;
        }

        // 2. Fetch container type preference
        $headerStmt = $this->db->prepare("SELECT internal_notes FROM document_headers WHERE id = :id");
        $headerStmt->execute(['id' => $documentId]);
        $headerNotes = $headerStmt->fetchColumn();
        $headerMeta = json_decode((string) $headerNotes, true) ?: [];
        $selectedContainer = $headerMeta['selected_container_type'] ?? '20FT';

        // 3. Determine container recommendation mix
        $recommendation = $this->runRecommendationAlgorithm($totalGrossWeight, $totalVolumeCbm, $selectedContainer);

        // 4. Construct response output
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
    private function runRecommendationAlgorithm(float $weight, float $volume, string $selectedContainer = '20FT'): array
    {
        if ($weight === 0.0 || $volume === 0.0) {
            return [];
        }

        $containerTypeKey = '20FT_DRY';
        if ($selectedContainer === '40FT') {
            $containerTypeKey = '40FT_DRY';
        } elseif ($selectedContainer === '40HC') {
            $containerTypeKey = '40FT_HC';
        }

        $spec = self::CONTAINER_SPECS[$containerTypeKey];

        $countByWeight = ceil($weight / $spec['max_weight_kg']);
        $countByVolume = ceil($volume / $spec['max_volume_cbm']);
        $containerCount = max($countByWeight, $countByVolume);
        if ($containerCount <= 0) {
            $containerCount = 1;
        }

        return [
            'type' => $containerTypeKey,
            'count' => (int) $containerCount,
            'name' => $spec['name'],
            'utilization' => [
                'weight_percent' => ($weight / ($containerCount * $spec['max_weight_kg'])) * 100.0,
                'volume_percent' => ($volume / ($containerCount * $spec['max_volume_cbm'])) * 100.0
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
