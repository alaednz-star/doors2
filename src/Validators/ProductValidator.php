<?php

namespace App\Validators;

use App\Core\Database;

class ProductValidator
{
    private array $errors = [];
    private array $clean  = [];

    public function validate(array $input, ?int $excludeId = null): bool
    {
        $this->errors = [];

        $name        = trim($input['name'] ?? '');
        $sku         = trim($input['sku'] ?? '');
        $description = trim($input['description'] ?? '');
        $widthMm     = trim((string)($input['width_mm'] ?? ''));
        $heightMm    = trim((string)($input['height_mm'] ?? ''));
        $order       = $input['display_order'] ?? '0';
        $categoryId  = $input['category_id'] ?? '';
        $collectionId   = $input['collection_id'] ?? '';
        $constructionId = $input['construction_type_id'] ?? '';
        $isFeatured  = isset($input['is_featured']) ? 1 : 0;
        $isActive    = isset($input['is_active']) ? 1 : 0;
        $colorIds    = array_filter(array_map('intval', (array)($input['color_ids'] ?? [])));

        if ($name === '') {
            $this->errors['name'] = 'Product name is required.';
        } elseif (strlen($name) < 2) {
            $this->errors['name'] = 'Name must be at least 2 characters.';
        } elseif (strlen($name) > 180) {
            $this->errors['name'] = 'Name must not exceed 180 characters.';
        } else {
            $slug = $this->slugify($name);
            if ($this->slugExists($slug, $excludeId)) {
                $this->errors['name'] = 'A product with this name already exists.';
            }
        }

        if ($sku !== '') {
            if (strlen($sku) > 60) {
                $this->errors['sku'] = 'SKU must not exceed 60 characters.';
            } elseif (!preg_match('/^[A-Za-z0-9\-_]+$/', $sku)) {
                $this->errors['sku'] = 'SKU may only contain letters, numbers, hyphens, and underscores.';
            } elseif ($this->skuExists($sku, $excludeId)) {
                $this->errors['sku'] = 'This SKU is already in use.';
            }
        }

        if ($description !== '' && strlen($description) > 5000) {
            $this->errors['description'] = 'Description must not exceed 5000 characters.';
        }

        foreach (['width_mm' => $widthMm, 'height_mm' => $heightMm] as $field => $value) {
            if ($value !== '' && (!ctype_digit($value) || (int)$value < 1 || (int)$value > 6000)) {
                $this->errors[$field] = 'Enter a value between 1 and 6000 mm.';
            }
        }

        if (!ctype_digit((string)$order) || (int)$order < 0 || (int)$order > 9999) {
            $this->errors['display_order'] = 'Display order must be a number between 0 and 9999.';
        }

        if ($categoryId !== '' && $categoryId !== '0') {
            $categoryId = (int) $categoryId;
            if (!$this->rowExists('categories', $categoryId)) {
                $this->errors['category_id'] = 'Selected category does not exist.';
            }
        } else {
            $categoryId = null;
        }

        if ($collectionId !== '' && $collectionId !== '0') {
            $collectionId = (int) $collectionId;
            if (!$this->rowExists('collections', $collectionId)) {
                $this->errors['collection_id'] = 'Selected collection does not exist.';
            }
        } else {
            $collectionId = null;
        }

        if ($constructionId !== '' && $constructionId !== '0') {
            $constructionId = (int) $constructionId;
            if (!$this->rowExists('construction_types', $constructionId)) {
                $this->errors['construction_type_id'] = 'Selected construction type does not exist.';
            }
        } else {
            $constructionId = null;
        }

        if (empty($this->errors)) {
            $this->clean = [
                'name'                 => $name,
                'slug'                 => $this->slugify($name),
                'sku'                  => $sku === '' ? null : $sku,
                'description'          => $description === '' ? null : $description,
                'width_mm'             => $widthMm === '' ? null : (int) $widthMm,
                'height_mm'            => $heightMm === '' ? null : (int) $heightMm,
                'display_order'        => (int) $order,
                'category_id'          => $categoryId,
                'collection_id'        => $collectionId,
                'construction_type_id' => $constructionId,
                'is_featured'          => $isFeatured,
                'is_active'            => $isActive,
                'color_ids'            => array_values($colorIds),
            ];
        }

        return empty($this->errors);
    }

    public function errors(): array { return $this->errors; }
    public function data(): array   { return $this->clean; }

    public function slugify(string $text): string
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9\s\-]/u', '', $text);
        $text = preg_replace('/[\s\-]+/', '-', $text);
        return trim($text, '-');
    }

    private function slugExists(string $slug, ?int $excludeId): bool
    {
        $sql  = 'SELECT COUNT(*) FROM products WHERE slug = ?';
        $args = [$slug];
        if ($excludeId !== null) {
            $sql  .= ' AND id != ?';
            $args[] = $excludeId;
        }
        $stmt = Database::conn()->prepare($sql);
        $stmt->execute($args);
        return (int) $stmt->fetchColumn() > 0;
    }

    private function skuExists(string $sku, ?int $excludeId): bool
    {
        $sql  = 'SELECT COUNT(*) FROM products WHERE sku = ?';
        $args = [$sku];
        if ($excludeId !== null) {
            $sql  .= ' AND id != ?';
            $args[] = $excludeId;
        }
        $stmt = Database::conn()->prepare($sql);
        $stmt->execute($args);
        return (int) $stmt->fetchColumn() > 0;
    }

    private function rowExists(string $table, int $id): bool
    {
        $allowed = ['categories', 'collections', 'construction_types'];
        if (!in_array($table, $allowed, true)) {
            return false;
        }
        $stmt = Database::conn()->prepare("SELECT COUNT(*) FROM `{$table}` WHERE id = ?");
        $stmt->execute([$id]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
