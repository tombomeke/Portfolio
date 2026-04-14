<?php
// app/Models/SiteSettingModel.php
require_once __DIR__ . '/../Config/Database.php';

class SiteSettingModel {

    // ── Static get/set (mirrors Laravel's SiteSetting::get()) ────────────────

    public static function get(string $key, $default = null) {
        try {
            $db   = Database::getConnection();
            $stmt = $db->prepare("SELECT value, type FROM site_settings WHERE `key` = :key LIMIT 1");
            $stmt->execute([':key' => $key]);
            $row = $stmt->fetch();
            return $row ? self::castValue($row['value'], $row['type']) : $default;
        } catch (\Throwable $e) {
            return $default;
        }
    }

    public static function set(string $key, $value): bool {
        try {
            Database::getConnection()->prepare(
                "UPDATE site_settings SET value=:value, updated_at=NOW() WHERE `key`=:key"
            )->execute([':value' => (string) $value, ':key' => $key]);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    // ── Admin queries ─────────────────────────────────────────────────────────

    public function getAllGrouped(): array {
        $db     = Database::getConnection();
        $rows   = $db->query("SELECT * FROM site_settings ORDER BY `group`, id")->fetchAll();
        $grouped = [];
        foreach ($rows as $row) {
            $row['typed_value'] = self::castValue($row['value'], $row['type']);
            $grouped[$row['group']][] = $row;
        }
        return $grouped;
    }

    public function getAll(): array {
        return Database::getConnection()->query("SELECT * FROM site_settings ORDER BY `group`, id")->fetchAll();
    }

    public function updateAll(array $postData, array $settings): void {
        $db   = Database::getConnection();
        $stmt = $db->prepare("UPDATE site_settings SET value=:value, updated_at=NOW() WHERE `key`=:key");
        foreach ($settings as $setting) {
            $key   = $setting['key'];
            $value = $setting['type'] === 'boolean'
                ? (isset($postData[$key]) ? '1' : '0')
                : ($postData[$key] ?? '');
            $stmt->execute([':value' => $value, ':key' => $key]);
        }
    }

    public function ensureCronSettings(): void {
        try {
            Database::getConnection()->prepare(
                "INSERT INTO site_settings (`key`, value, type, `group`, label, description)
                 VALUES ('cron_sync_min_interval_seconds', '3600', 'integer', 'security', 'Cron Sync Min Interval (seconds)', 'Minimum seconds between successful cron roadmap sync runs')
                 ON DUPLICATE KEY UPDATE `key` = `key`"
            )->execute();
        } catch (\Throwable $e) {
            // Keep settings page usable when seed insert cannot run.
        }
    }

    public function count(): int {
        try {
            return (int) Database::getConnection()->query("SELECT COUNT(*) FROM site_settings")->fetchColumn();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private static function castValue($value, string $type) {
        return match($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'json'    => json_decode($value, true),
            default   => (string) ($value ?? ''),
        };
    }

    public function getGroupIcon(string $group): string {
        return match($group) {
            'general'  => 'sliders-h',
            'features' => 'toggle-on',
            'contact'  => 'envelope',
            'social'   => 'share-alt',
            'seo'      => 'search',
            'branding' => 'palette',
            'analytics'=> 'chart-bar',
            'email'    => 'at',
            'security' => 'shield-alt',
            'uploads'  => 'upload',
            'legal'    => 'balance-scale',
            'ux'       => 'desktop',
            default    => 'cog',
        };
    }
}
