<?php
$settings = $settings ?? [];
?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-0 fw-bold"><i class="fas fa-robot text-primary me-2"></i>AI Engine Integration Console</h4>
        <p class="text-muted small mb-0">Configure active AI providers, models, API keys, temperature parameters, or set up local offline AI endpoints.</p>
    </div>
    <div>
        <a href="/settings/master-data/products" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Back to Products</a>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-dark text-white p-3 fw-bold"><i class="fas fa-sliders-h me-2"></i>Active AI Provider Configurations</div>
            <div class="card-body p-4">
                <form method="post" action="/administration/ai-settings" class="needs-validation" novalidate>
                    <?= csrfToken() ?>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Active AI Provider</label>
                            <select name="provider" class="form-select form-select-sm" onchange="toggleProviderSettings(this.value)">
                                <option value="openai" <?= ($settings['provider'] ?? '') === 'openai' ? 'selected' : '' ?>>OpenAI API</option>
                                <option value="gemini" <?= ($settings['provider'] ?? '') === 'gemini' ? 'selected' : '' ?>>Google Gemini API</option>
                                <option value="claude" <?= ($settings['provider'] ?? '') === 'claude' ? 'selected' : '' ?>>Anthropic Claude API</option>
                                <option value="deepseek" <?= ($settings['provider'] ?? '') === 'deepseek' ? 'selected' : '' ?>>DeepSeek API</option>
                                <option value="groq" <?= ($settings['provider'] ?? '') === 'groq' ? 'selected' : '' ?>>Groq API</option>
                                <option value="ollama" <?= ($settings['provider'] ?? '') === 'ollama' ? 'selected' : '' ?>>Ollama (Local Offline AI)</option>
                                <option value="lm-studio" <?= ($settings['provider'] ?? '') === 'lm-studio' ? 'selected' : '' ?>>LM Studio (Local Offline AI)</option>
                                <option value="custom" <?= ($settings['provider'] ?? '') === 'custom' ? 'selected' : '' ?>>Custom REST API</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Model / Deployment Name</label>
                            <input type="text" name="model" class="form-control form-control-sm" placeholder="e.g. gemini-1.5-pro or gpt-4o" value="<?= escapeHtml((string)($settings['model'] ?? '')) ?>" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-12" id="apiKeyContainer">
                            <label class="form-label small fw-bold">API Key / Access Token</label>
                            <input type="password" name="api_key" class="form-control form-control-sm" placeholder="••••••••••••••••" value="<?= escapeHtml((string)($settings['api_key'] ?? '')) ?>">
                            <div class="form-text small text-muted">Keep blank if using local offline providers that do not require authentication keys.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Base Provider URL</label>
                            <input type="url" name="base_url" class="form-control form-control-sm" placeholder="e.g. https://api.openai.com/v1" value="<?= escapeHtml((string)($settings['base_url'] ?? '')) ?>">
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Temperature (Creativity)</label>
                            <input type="number" name="temperature" class="form-control form-control-sm" min="0.0" max="2.0" step="0.05" value="<?= (float)($settings['temperature'] ?? 0.7) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Max Generated Tokens</label>
                            <input type="number" name="max_tokens" class="form-control form-control-sm" min="10" max="8000" value="<?= (int)($settings['max_tokens'] ?? 1000) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Request Timeout (Sec)</label>
                            <input type="number" name="timeout" class="form-control form-control-sm" min="1" max="120" value="<?= (int)($settings['timeout'] ?? 30) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Retry Limits</label>
                            <input type="number" name="retry_limit" class="form-control form-control-sm" min="0" max="10" value="<?= (int)($settings['retry_limit'] ?? 3) ?>">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save me-1"></i> Save Configurations</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-primary text-white p-3 fw-bold"><i class="fas fa-laptop-house me-2"></i>Local AI Setups</div>
            <div class="card-body p-3">
                <p class="small text-muted mb-3">To use 100% private offline AI configurations, configure the endpoints as follows:</p>
                <div class="bg-light p-3 rounded mb-3">
                    <strong class="small d-block text-dark fw-bold">Ollama Configuration:</strong>
                    <code class="small d-block mt-1">Base URL: http://localhost:11434</code>
                    <code class="small d-block">Model: llama3 or deepseek-r1:8b</code>
                </div>
                <div class="bg-light p-3 rounded">
                    <strong class="small d-block text-dark fw-bold">LM Studio Configuration:</strong>
                    <code class="small d-block mt-1">Base URL: http://localhost:1234/v1</code>
                    <code class="small d-block">Model: deepseek-r1-distill-qwen-8b</code>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleProviderSettings(prov) {
    const keyContainer = document.getElementById('apiKeyContainer');
    if (prov === 'ollama' || prov === 'lm-studio') {
        keyContainer.classList.add('opacity-50');
    } else {
        keyContainer.classList.remove('opacity-50');
    }
}
</script>
