<?php
declare(strict_types=1);

namespace App\Core;

interface AiServiceProviderInterface
{
    /**
     * Send suggestion prompt request to AI provider
     */
    public function suggest(string $prompt, array $settings): array;
}
