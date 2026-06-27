<?php

namespace App\Services;

use App\Core\Database;

/**
 * Server-side validation of a door configuration before it is priced or stored.
 * Rejects out-of-range dimensions and non-existent / inactive references so no
 * garbage configuration can ever be persisted or priced.
 */
class ConfigValidator
{
    // Physically sane manufacturing bounds (mm).
    public const WIDTH_MIN  = 500;
    public const WIDTH_MAX  = 2000;
    public const HEIGHT_MIN = 1500;
    public const HEIGHT_MAX = 3000;

    /** Tables whose ids the config may reference, with the config key for each. */
    private const REFS = [
        'room_type_id'         => 'room_types',
        'collection_id'        => 'collections',
        'material_id'          => 'materials',
        'color_id'             => 'colors',
        'door_type_id'         => 'door_types',
        'construction_type_id' => 'construction_types',
        'product_id'           => 'products',
    ];

    /**
     * @return array{0: bool, 1: array<string,string>, 2: array} [ok, errors, cleanConfig]
     */
    public function validate(array $config): array
    {
        $errors = [];
        $clean  = [];
        $db     = Database::conn();

        // Dimensions
        $w = (int) ($config['width_mm'] ?? 0);
        $h = (int) ($config['height_mm'] ?? 0);
        if ($w < self::WIDTH_MIN || $w > self::WIDTH_MAX) {
            $errors['width_mm'] = sprintf('Width must be between %d and %d cm.', self::WIDTH_MIN / 10, self::WIDTH_MAX / 10);
        } else {
            $clean['width_mm'] = $w;
        }
        if ($h < self::HEIGHT_MIN || $h > self::HEIGHT_MAX) {
            $errors['height_mm'] = sprintf('Height must be between %d and %d cm.', self::HEIGHT_MIN / 10, self::HEIGHT_MAX / 10);
        } else {
            $clean['height_mm'] = $h;
        }

        // Reference ids — each must exist and be active (where the table has is_active)
        foreach (self::REFS as $key => $table) {
            $id = $config[$key] ?? null;
            if ($id === null || $id === '' || (int) $id <= 0) {
                continue; // optional unless required-checked below
            }
            $id = (int) $id;
            if (!$this->exists($db, $table, $id)) {
                $errors[$key] = 'Selected option is not available.';
            } else {
                $clean[$key] = $id;
            }
        }

        // A collection is the minimum required to form a real configuration.
        if (empty($clean['collection_id'])) {
            $errors['collection_id'] = 'Please choose a collection.';
        }

        // Feature ids — keep only those that exist and are active.
        $clean['feature_ids'] = $this->cleanFeatureIds($db, $config['feature_ids'] ?? []);

        return [empty($errors), $errors, $clean];
    }

    private function exists(\PDO $db, string $table, int $id): bool
    {
        $hasActive = $this->hasActiveColumn($db, $table);
        $sql = "SELECT 1 FROM `{$table}` WHERE id = ?" . ($hasActive ? ' AND is_active = 1' : '') . ' LIMIT 1';
        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
        return (bool) $stmt->fetchColumn();
    }

    /** @var array<string,bool> */
    private array $activeCache = [];

    private function hasActiveColumn(\PDO $db, string $table): bool
    {
        if (!isset($this->activeCache[$table])) {
            $stmt = $db->prepare(
                "SELECT COUNT(*) FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = 'is_active'"
            );
            $stmt->execute([$table]);
            $this->activeCache[$table] = (bool) $stmt->fetchColumn();
        }
        return $this->activeCache[$table];
    }

    private function cleanFeatureIds(\PDO $db, $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', (array) $ids), static fn ($v) => $v > 0)));
        if (!$ids) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $db->prepare(
            "SELECT id FROM optional_features WHERE id IN ($placeholders) AND is_active = 1"
        );
        $stmt->execute($ids);
        return array_map('intval', $stmt->fetchAll(\PDO::FETCH_COLUMN));
    }
}
