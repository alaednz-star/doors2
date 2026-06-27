<?php

namespace App\Validators;

use App\Core\Database;

class ColorValidator
{
    private array $errors = [];
    private array $data   = [];

    public function validateColor(array $input, ?int $excludeId = null): bool
    {
        $this->errors = [];

        // A color name is unique WITHIN a collection, not globally — so the same
        // name (e.g. "Gris") may exist in more than one collection.
        $collectionId = isset($input['collection_id']) && $input['collection_id'] !== ''
            ? (int)$input['collection_id'] : null;

        $name = trim($input['name'] ?? '');
        if ($name === '') {
            $this->errors['name'] = 'Color name is required.';
        } elseif (strlen($name) > 80) {
            $this->errors['name'] = 'Color name must be under 80 characters.';
        } elseif ($this->colorNameExists($name, $collectionId, $excludeId)) {
            $this->errors['name'] = 'A color with this name already exists in this collection.';
        }

        $hex = trim($input['hex'] ?? '');
        if ($hex !== '' && !preg_match('/^#[0-9A-Fa-f]{6}$/', $hex)) {
            $this->errors['hex'] = 'Hex must be a valid 6-digit color code (e.g. #2C2C2C).';
        }

        $description = trim($input['description'] ?? '');
        if ($description !== '' && strlen($description) > 1000) {
            $this->errors['description'] = 'Description must be under 1000 characters.';
        }

        $order = $input['display_order'] ?? '0';
        if (!ctype_digit((string)$order) || (int)$order > 9999) {
            $this->errors['display_order'] = 'Display order must be a number between 0 and 9999.';
        }

        if (empty($this->errors)) {
            $this->data = [
                'name'          => $name,
                'hex'           => $hex !== '' ? strtoupper($hex) : null,
                'description'   => $description !== '' ? $description : null,
                'display_order' => (int)$order,
                'is_active'     => isset($input['is_active']) ? 1 : 0,
            ];
        }

        return empty($this->errors);
    }

    public function errors(): array { return $this->errors; }
    public function data(): array   { return $this->data; }

    /** Color names are unique per collection (matches uk_colors_collection_name). */
    private function colorNameExists(string $name, ?int $collectionId, ?int $excludeId): bool
    {
        $sql  = 'SELECT COUNT(*) FROM `colors` WHERE name = ? AND ';
        $sql .= $collectionId === null ? 'collection_id IS NULL' : 'collection_id = ?';
        $args = [$name];
        if ($collectionId !== null) {
            $args[] = $collectionId;
        }
        if ($excludeId !== null) {
            $sql   .= ' AND id != ?';
            $args[] = $excludeId;
        }
        $stmt = Database::conn()->prepare($sql);
        $stmt->execute($args);
        return (int)$stmt->fetchColumn() > 0;
    }
}
