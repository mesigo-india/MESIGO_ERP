<?php
$title = $title ?? 'Product Master Data';
$action = $action ?? '';
$row = $row ?? null;
$config = $config ?? [];
$masterKey = $masterKey ?? '';
$fields = $config['fields'] ?? [];
?>
<div class="page-header">
    <h1><?= escapeHtml((string) $title) ?></h1>
    <a href="/settings/master-data/<?= escapeHtml((string) $masterKey) ?>" class="btn btn-outline-secondary">Back</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="post" action="<?= escapeHtml((string) $action) ?>" class="needs-validation" novalidate>
            <?= csrfToken() ?>

            <div class="row">
                <?php foreach ($fields as $field => $meta): ?>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="<?= escapeHtml((string) $field) ?>"><?= escapeHtml((string) $meta['label']) ?></label>
                        <?php if (($meta['type'] ?? '') === 'textarea'): ?>
                            <textarea id="<?= escapeHtml((string) $field) ?>" name="<?= escapeHtml((string) $field) ?>" class="form-control" rows="3"><?= escapeHtml((string) ($row[$field] ?? '')) ?></textarea>
                        <?php else: ?>
                            <input type="<?= ($meta['type'] ?? '') === 'number' ? 'number' : 'text' ?>" step="<?= escapeHtml((string) ($meta['step'] ?? '1')) ?>" id="<?= escapeHtml((string) $field) ?>" name="<?= escapeHtml((string) $field) ?>" class="form-control" value="<?= escapeHtml((string) ($row[$field] ?? '')) ?>" <?= !empty($meta['required']) ? 'required' : '' ?> <?= !empty($meta['max']) ? 'maxlength="' . (int) $meta['max'] . '"' : '' ?>>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="mb-3">
                <label class="form-label" for="status">Status</label>
                <select id="status" name="status" class="form-select">
                    <option value="1" <?= (int) ($row['status'] ?? 1) === 1 ? 'selected' : '' ?>>Active</option>
                    <option value="0" <?= (int) ($row['status'] ?? 1) === 0 ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Save <?= escapeHtml((string) ($config['singular'] ?? 'Master')) ?></button>
        </form>
    </div>
</div>