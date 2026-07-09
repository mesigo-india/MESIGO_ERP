<?php
declare(strict_types=1);

namespace App\Core;

class SettingsController extends Controller
{
    private \PDO $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
    }

    public function index(): void
    {
        $this->requireLogin();
        $this->requirePermission('settings.view');

        $stmt = $this->db->query("SELECT `key`, `value`, `type`, `group` FROM settings WHERE status = 1 ORDER BY `group`, `key`");
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Group settings by their group key for template use
        $settings = [];
        foreach ($rows as $row) {
            $group = $row['group'] ?? 'general';
            $settings[$group][$row['key']] = $row['value'];
        }

        $this->render('settings/index', [
            'title'    => 'System Settings',
            'settings' => $settings,
        ]);
    }

    public function update(): void
    {
        $this->requireLogin();
        $this->requirePermission('settings.update');

        if (!$this->validateCsrf()) {
            Response::redirect('/settings', 'Invalid security token.');
        }

        $submitted = $_POST['settings'] ?? [];
        if (!is_array($submitted)) {
            Response::redirect('/settings', 'Invalid data.');
        }

        foreach ($submitted as $key => $value) {
            $key = preg_replace('/[^a-zA-Z0-9_.\-]/', '', (string) $key);
            if ($key === '') {
                continue;
            }
            $value = (string) $value;

            // Upsert: update if exists, insert if not
            $stmt = $this->db->prepare("
                INSERT INTO settings (`key`, `value`, `type`, `group`, status, created_at, updated_at)
                VALUES (:key, :value, 'string', 'general', 1, NOW(), NOW())
                ON DUPLICATE KEY UPDATE `value` = :value2, updated_at = NOW()
            ");
            $stmt->execute([
                ':key'    => $key,
                ':value'  => $value,
                ':value2' => $value,
            ]);
        }

        $this->logger->info('Settings updated');
        Response::redirect('/settings', 'Settings saved successfully.');
    }
}
