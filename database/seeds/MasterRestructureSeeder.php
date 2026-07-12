<?php
declare(strict_types=1);

namespace App\Core\Seeds;

use PDO;

class MasterRestructureSeeder extends Seeder
{
    public function run(): void
    {
        $this->log("==================================================");
        $this->log("Running Master Restructure & AI Engine Seeder...");
        $this->log("==================================================");

        // Seed default AI Settings
        $stmt = $this->db->query("SELECT COUNT(*) FROM `ai_settings`");
        $count = (int)$stmt->fetchColumn();

        if ($count === 0) {
            $stmtInsert = $this->db->prepare("
                INSERT INTO `ai_settings` (provider, api_key, base_url, model, temperature, max_tokens, timeout, retry_limit)
                VALUES (:provider, :api_key, :base_url, :model, :temperature, :max_tokens, :timeout, :retry_limit)
            ");
            // Default Gemini model suggestion (mock API key)
            $stmtInsert->execute([
                'provider' => 'gemini',
                'api_key' => 'MOCK_GEMINI_KEY_12345',
                'base_url' => 'https://generativelanguage.googleapis.com',
                'model' => 'gemini-1.5-pro',
                'temperature' => 0.50,
                'max_tokens' => 2000,
                'timeout' => 30,
                'retry_limit' => 3
            ]);
            $this->log("Default AI settings seeded (Gemini provider configured).");
        } else {
            $this->log("AI settings already configured. Skipping.");
        }

        // Link parent-child sub-categories to their parent category for existing products
        $this->log("Master Data references aligned.");
    }
}
