<?php

namespace App\Validators;

use App\Core\Database;

class CategoryValidator
{
    private array $errors = [];
    private array $clean  = [];

    public function validate(array $input, ?int $excludeId = null): bool
    {
        $this->errors = [];

        $name        = trim($input['name'] ?? '');
        $description = trim($input['description'] ?? '');
        $order       = $input['display_order'] ?? '0';
        $isActive    = isset($input['is_active']) ? 1 : 0;

        if ($name === '') {
            $this->errors['name'] = 'Category name is required.';
        } elseif (strlen($name) < 2) {
            $this->errors['name'] = 'Name must be at least 2 characters.';
        } elseif (strlen($name) > 120) {
            $this->errors['name'] = 'Name must not exceed 120 characters.';
        } else {
            $slug = $this->slugify($name);
            if ($this->slugExists($slug, $excludeId)) {
                $this->errors['name'] = 'A category with this name already exists.';
            }
        }

        if ($description !== '' && strlen($description) > 2000) {
            $this->errors['description'] = 'Description must not exceed 2000 characters.';
        }

        if (!ctype_digit((string)$order) || (int)$order < 0 || (int)$order > 9999) {
            $this->errors['display_order'] = 'Display order must be a number between 0 and 9999.';
        }

        if (empty($this->errors)) {
            $this->clean = [
                'name'          => $name,
                'slug'          => $this->slugify($name),
                'description'   => $description === '' ? null : $description,
                'display_order' => (int) $order,
                'is_active'     => $isActive,
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
        $db   = Database::conn();
        $sql  = 'SELECT COUNT(*) FROM categories WHERE slug = ?';
        $args = [$slug];

        if ($excludeId !== null) {
            $sql  .= ' AND id != ?';
            $args[] = $excludeId;
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($args);
        return (int) $stmt->fetchColumn() > 0;
    }
}
