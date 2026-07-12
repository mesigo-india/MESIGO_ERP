<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use Exception;

require_once __DIR__ . '/AiServiceProviderInterface.php';

class AiManager
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Get active AI Settings
     */
    public function getSettings(): array
    {
        $stmt = $this->db->query("SELECT * FROM `ai_settings` ORDER BY id DESC LIMIT 1");
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$settings) {
            return [
                'provider' => 'gemini',
                'api_key' => '',
                'base_url' => 'https://generativelanguage.googleapis.com',
                'model' => 'gemini-1.5-pro',
                'temperature' => 0.50,
                'max_tokens' => 1000,
                'timeout' => 15,
                'retry_limit' => 3
            ];
        }
        return $settings;
    }

    /**
     * Save active AI settings
     */
    public function saveSettings(array $data): bool
    {
        $settings = $this->getSettings();
        
        $filtered = [
            'provider' => $data['provider'] ?? '',
            'api_key' => $data['api_key'] ?? null,
            'base_url' => $data['base_url'] ?? null,
            'model' => $data['model'] ?? '',
            'temperature' => (float)($data['temperature'] ?? 0.7),
            'max_tokens' => (int)($data['max_tokens'] ?? 1000),
            'timeout' => (int)($data['timeout'] ?? 30),
            'retry_limit' => (int)($data['retry_limit'] ?? 3)
        ];
        
        if (isset($settings['id'])) {
            $stmt = $this->db->prepare("
                UPDATE `ai_settings`
                SET provider = :provider, api_key = :api_key, base_url = :base_url,
                    model = :model, temperature = :temperature, max_tokens = :max_tokens,
                    timeout = :timeout, retry_limit = :retry_limit, updated_at = NOW()
                WHERE id = :id
            ");
            $filtered['id'] = $settings['id'];
            return $stmt->execute($filtered);
        } else {
            $stmt = $this->db->prepare("
                INSERT INTO `ai_settings` (provider, api_key, base_url, model, temperature, max_tokens, timeout, retry_limit)
                VALUES (:provider, :api_key, :base_url, :model, :temperature, :max_tokens, :timeout, :retry_limit)
            ");
            return $stmt->execute($filtered);
        }
    }

    /**
     * Parse query text and return suggestions
     */
    public function getSuggestions(string $query, string $masterKey): array
    {
        $startTime = microtime(true);
        $settings = $this->getSettings();
        
        $prompt = "You are Mesigo AI Engine. Suggest field values for Master: '$masterKey' based on text: '$query'.\n" .
                  "Return standard JSON only with suggested values. Keys must correspond to database fields.";

        $responseJson = "";
        $tokens = 0;
        
        // If API key is empty, mock key, or local provider fails, fall back to Intelligent Local Parser
        if (empty($settings['api_key']) || strpos($settings['api_key'], 'MOCK_') === 0) {
            $response = $this->runIntelligentLocalParser($query, $masterKey);
            $responseJson = json_encode($response);
            $tokens = 150;
        } else {
            try {
                $provider = $this->resolveProvider($settings['provider']);
                $response = $provider->suggest($prompt, $settings);
                $responseJson = json_encode($response);
                $tokens = 300; // Mock token usage
            } catch (Exception $e) {
                // Fallback to local parser on api error
                $response = $this->runIntelligentLocalParser($query, $masterKey);
                $responseJson = json_encode($response) . " (Fallback: " . $e->getMessage() . ")";
                $tokens = 150;
            }
        }

        $durationMs = (int)((microtime(true) - $startTime) * 1000);
        $this->logRequest($query, $responseJson, $settings['provider'], $settings['model'], $durationMs, $tokens);

        return json_decode($responseJson, true) ?: [];
    }

    /**
     * Resolve Provider Instantiation
     */
    private function resolveProvider(string $providerName): AiServiceProviderInterface
    {
        switch (strtolower($providerName)) {
            case 'openai':
                return new OpenAiProvider();
            case 'gemini':
                return new GeminiProvider();
            case 'claude':
                return new ClaudeProvider();
            case 'deepseek':
                return new DeepSeekProvider();
            default:
                return new GenericRestProvider();
        }
    }

    /**
     * AI Log Entries
     */
    private function logRequest(string $prompt, string $response, string $provider, string $model, int $durationMs, int $tokens): void
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO `ai_request_logs` (user_id, prompt, response, provider, model, execution_time_ms, tokens_used)
                VALUES (:user_id, :prompt, :response, :provider, :model, :execution_time_ms, :tokens_used)
            ");
            $stmt->execute([
                'user_id' => isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null,
                'prompt' => $prompt,
                'response' => $response,
                'provider' => $provider,
                'model' => $model,
                'execution_time_ms' => $durationMs,
                'tokens_used' => $tokens
            ]);
        } catch (\Throwable $e) {
            // Ignore log failures or echo to debug
        }
    }

    /**
     * Intelligent Local Parsing engine (Rule-based NLP fallback)
     */
    private function runIntelligentLocalParser(string $query, string $masterKey): array
    {
        $queryLower = strtolower($query);
        $res = [];

        // Common defaults
        $res['status'] = 1;
        $res['internal_notes'] = "AI generated suggestions based on input description: " . $query;
        $res['tags'] = "AI_Suggested";

        if ($masterKey === 'products' || $masterKey === 'product-grades') {
            // Determine name
            $res['product_name'] = ucwords(trim($query));
            $res['name'] = ucwords(trim($query));
            $res['grade_name'] = "Premium Grade";

            // Extract purity percentage (e.g. 99.5%, 99%, 98%)
            if (preg_match('/(\d+(?:\.\d+)?)\s*%/i', $query, $matches)) {
                $pct = (float)$matches[1];
                $res['purity'] = $pct;
                $res['purity_percent'] = $pct;
            } else {
                $res['purity'] = 99.00;
                $res['purity_percent'] = 99.00;
            }

            // Extract moisture
            if (preg_match('/moisture\s*(\d+(?:\.\d+)?)/i', $queryLower, $matches)) {
                $res['moisture'] = (float)$matches[1];
                $res['moisture_percent'] = (float)$matches[1];
            } else {
                $res['moisture'] = 8.00;
                $res['moisture_percent'] = 8.00;
            }

            // Extract foreign matter
            $res['foreign_matter'] = 0.50;
            $res['foreign_matter_percent'] = 0.50;

            // Machine Cleaned / Sortex flags
            $res['machine_cleaned'] = strpos($queryLower, 'machine') !== false ? 1 : 0;
            $res['sortex'] = strpos($queryLower, 'sortex') !== false ? 1 : 0;
            $res['organic'] = strpos($queryLower, 'organic') !== false ? 1 : 0;

            // Product specifications
            if (strpos($queryLower, 'cumin') !== false) {
                $res['product_code'] = "PRD_CUM_001";
                $res['grade_code'] = "GRD_CUM_PREM";
                $res['sku'] = "SKU-CUM-995-STX";
                $res['hsn_code'] = "09093129";
                $res['hs_code'] = "09093129";
                $res['gst'] = 5.00;
                $res['scientific_name'] = "Cuminum cyminum";
                $res['trade_name'] = "Jeera / Cumin Seeds";
                $res['storage'] = "Store in cool dry warehouse protected from insects";
                $res['shelf_life'] = 730;
                $res['packing'] = "25 Kg PP Bags";
                $res['moq'] = 10.00;
                $res['buying_price'] = 150.00;
                $res['selling_price'] = 195.00;
                $res['keywords'] = "Jeera, Cumin, Sortex, Machine Cleaned, Export Grade";
                $res['export_markets'] = "Europe, USA, Middle East";
            } elseif (strpos($queryLower, 'rice') !== false) {
                $res['product_code'] = "PRD_RIC_002";
                $res['grade_code'] = "GRD_RIC_PREM";
                $res['sku'] = "SKU-RIC-BASMATI";
                $res['hsn_code'] = "10063020";
                $res['hs_code'] = "10063020";
                $res['gst'] = 5.00;
                $res['scientific_name'] = "Oryza sativa";
                $res['trade_name'] = "Basmati Rice";
                $res['storage'] = "Store in clean, pest-controlled well-ventilated dry place";
                $res['shelf_life'] = 365;
                $res['packing'] = "50 Kg Jute Bags";
                $res['moq'] = 20.00;
                $res['buying_price'] = 90.00;
                $res['selling_price'] = 120.00;
                $res['keywords'] = "Basmati, Raw Rice, Long Grain, Cleaned Rice";
                $res['export_markets'] = "Middle East, Europe, Africa";
            } else {
                $res['product_code'] = "PRD_GEN_" . rand(100, 999);
                $res['grade_code'] = "GRD_GEN_" . rand(100, 999);
                $res['sku'] = "SKU-GEN-" . rand(100, 999);
                $res['hsn_code'] = "12099990";
                $res['hs_code'] = "12099990";
                $res['gst'] = 18.00;
                $res['scientific_name'] = "Genus species";
                $res['trade_name'] = ucwords(trim($query));
                $res['storage'] = "Normal storage";
                $res['shelf_life'] = 180;
                $res['packing'] = "Bag";
                $res['moq'] = 1.00;
                $res['buying_price'] = 100.00;
                $res['selling_price'] = 130.00;
                $res['keywords'] = "Export grade, Master item";
                $res['export_markets'] = "Global";
            }
        } elseif ($masterKey === 'product-categories') {
            $res['code'] = "CAT_" . strtoupper(substr(trim($query), 0, 3)) . "_" . rand(10, 99);
            $res['name'] = ucwords(trim($query));
            $res['commodity_group'] = "Agricultural Goods";
            $res['hs_chapter'] = "09";
            $res['default_gst'] = 5.00;
            $res['default_storage'] = "Ambient temperature warehouse";
            $res['shelf_life'] = 365;
            $res['temperature'] = "20-25 C";
            $res['export_allowed'] = 1;
            $res['import_allowed'] = 1;
            $res['quality_standard'] = "FSSAI Standard / APEDA";
        } elseif ($masterKey === 'warehouses') {
            $res['code'] = "WH_" . strtoupper(substr(trim($query), 0, 3)) . "_" . rand(10, 99);
            $res['name'] = ucwords(trim($query));
            $res['warehouse_type'] = "Dry Cold Store";
            $res['capacity'] = 5000.00;
            $res['temperature'] = "15-20 C";
            $res['humidity'] = "40-60%";
            $res['storage_type'] = "Racks & Pallets";
            $res['gps'] = "12.9716, 77.5946";
            $res['manager'] = "John Operations Manager";
        } elseif ($masterKey === 'ports') {
            $res['code'] = "PORT_" . strtoupper(substr(trim($query), 0, 3));
            $res['name'] = ucwords(trim($query));
            $res['un_locode'] = "INMAA";
            $res['sea_port'] = 1;
            $res['air_port'] = 0;
            $res['land_port'] = 0;
            $res['nearest_icd'] = "ICD Chennai";
            $res['custom_office'] = "Chennai Customs House Zone II";
            $res['shipping_lines'] = "Maersk, MSC, CMA CGM, ONE Line";
        } else {
            // General Master defaults
            $res['code'] = "MST_" . strtoupper(substr(trim($query), 0, 3)) . "_" . rand(10, 99);
            $res['name'] = ucwords(trim($query));
        }

        return $res;
    }
}

// ----------------------------------------------------
// Concrete Mock/Real implementations of Providers
// ----------------------------------------------------
class OpenAiProvider implements AiServiceProviderInterface {
    public function suggest(string $prompt, array $settings): array {
        return []; // In real usage, cURL payload is dispatched here
    }
}

class GeminiProvider implements AiServiceProviderInterface {
    public function suggest(string $prompt, array $settings): array {
        return [];
    }
}

class ClaudeProvider implements AiServiceProviderInterface {
    public function suggest(string $prompt, array $settings): array {
        return [];
    }
}

class DeepSeekProvider implements AiServiceProviderInterface {
    public function suggest(string $prompt, array $settings): array {
        return [];
    }
}

class GenericRestProvider implements AiServiceProviderInterface {
    public function suggest(string $prompt, array $settings): array {
        return [];
    }
}
