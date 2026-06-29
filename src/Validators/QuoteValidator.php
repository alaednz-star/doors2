<?php

namespace App\Validators;

class QuoteValidator
{
    private const VALID_STATUSES = [
        'new', 'contacted', 'quotation_sent',
        'in_progress', 'confirmed', 'delivered', 'completed', 'cancelled',
    ];

    private array $errors = [];
    private array $data   = [];

    public function validateCreate(array $input): bool
    {
        $this->errors = [];

        $name = trim($input['customer_name'] ?? '');
        if ($name === '') {
            $this->errors['customer_name'] = 'Customer name is required.';
        } elseif (mb_strlen($name) > 120) {
            $this->errors['customer_name'] = 'Name must be under 120 characters.';
        }

        $phone = trim($input['customer_phone'] ?? '');
        if ($phone === '') {
            $this->errors['customer_phone'] = 'Phone number is required.';
        } elseif (!preg_match('/^\+?[\d\s\-\(\)]{6,30}$/', $phone)) {
            $this->errors['customer_phone'] = 'Enter a valid phone number.';
        }

        $email = trim($input['customer_email'] ?? '');
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors['customer_email'] = 'Enter a valid email address.';
        }

        $city = trim($input['customer_city'] ?? '');
        if ($city !== '' && mb_strlen($city) > 100) {
            $this->errors['customer_city'] = 'City must be under 100 characters.';
        }

        $notes = trim($input['notes'] ?? '');
        if ($notes !== '' && mb_strlen($notes) > 3000) {
            $this->errors['notes'] = 'Notes must be under 3000 characters.';
        }

        $this->validateConfig($input);

        if (empty($this->errors)) {
            $this->data = [
                'customer_name'  => $name,
                'customer_phone' => $phone,
                'customer_email' => $email !== '' ? $email : null,
                'customer_city'  => $city  !== '' ? $city  : null,
                'notes'          => $notes !== '' ? $notes : null,
                ...$this->configData($input),
            ];
        }

        return empty($this->errors);
    }

    public function validateUpdate(array $input): bool
    {
        return $this->validateCreate($input);
    }

    public function validateStatusChange(array $input): bool
    {
        $this->errors = [];

        $status = $input['status'] ?? '';
        if (!in_array($status, self::VALID_STATUSES, true)) {
            $this->errors['status'] = 'Invalid status value.';
        }

        $notes = trim($input['status_notes'] ?? '');
        if ($notes !== '' && mb_strlen($notes) > 2000) {
            $this->errors['status_notes'] = 'Notes must be under 2000 characters.';
        }

        if (empty($this->errors)) {
            $this->data = [
                'status'       => $status,
                'status_notes' => $notes !== '' ? $notes : null,
            ];
        }

        return empty($this->errors);
    }

    public function errors(): array { return $this->errors; }
    public function data(): array   { return $this->data; }

    public static function statuses(): array
    {
        return self::VALID_STATUSES;
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            'new'            => 'New',
            'contacted'      => 'Contacted',
            'quotation_sent' => 'Quotation Sent',
            'in_progress'    => 'In Progress',
            'confirmed'      => 'Confirmed',
            'delivered'      => 'Delivered',
            'completed'      => 'Completed',
            'cancelled'      => 'Cancelled',
            default          => ucfirst($status),
        };
    }

    public static function allowedTransitions(string $current): array
    {
        return match ($current) {
            'new'            => ['contacted', 'quotation_sent', 'cancelled'],
            'contacted'      => ['quotation_sent', 'in_progress', 'cancelled'],
            'quotation_sent' => ['in_progress', 'confirmed', 'cancelled'],
            'in_progress'    => ['confirmed', 'cancelled'],
            'confirmed'      => ['delivered', 'completed', 'cancelled'],
            'delivered'      => ['completed', 'cancelled'],
            'completed'      => [],
            'cancelled'      => ['new'],
            default          => [],
        };
    }

    private function validateConfig(array $input): void
    {
        foreach (['product_id', 'collection_id', 'material_id', 'color_id', 'door_type_id', 'construction_type_id'] as $field) {
            $val = $input[$field] ?? '';
            if ($val !== '' && $val !== null && !ctype_digit((string)$val)) {
                $this->errors[$field] = 'Must be a valid selection.';
            }
        }

        foreach (['width_mm', 'height_mm'] as $dim) {
            $val = $input[$dim] ?? '';
            if ($val !== '' && $val !== null) {
                if (!ctype_digit((string)$val) || (int)$val < 100 || (int)$val > 6000) {
                    $this->errors[$dim] = 'Must be between 100 and 6000 mm.';
                }
            }
        }

        $handle = trim($input['handle'] ?? '');
        if ($handle !== '' && mb_strlen($handle) > 120) {
            $this->errors['handle'] = 'Handle description must be under 120 characters.';
        }

        $price = $input['final_price'] ?? '';
        if ($price !== '' && $price !== null && (!is_numeric($price) || (float)$price < 0)) {
            $this->errors['final_price'] = 'Final price must be a non-negative number.';
        }
    }

    private function configData(array $input): array
    {
        $nullableInt = fn(mixed $v): ?int => ($v !== '' && $v !== null) ? (int)$v : null;
        $nullableStr = fn(string $v): ?string => trim($v) !== '' ? trim($v) : null;

        $featureIds = [];
        if (!empty($input['feature_ids']) && is_array($input['feature_ids'])) {
            $featureIds = array_values(array_map('intval', $input['feature_ids']));
        }

        $price = $input['final_price'] ?? '';

        $collectionId   = $nullableInt($input['collection_id']        ?? '');
        $constructionId = $nullableInt($input['construction_type_id'] ?? '');
        $colorId        = $nullableInt($input['color_id']             ?? '');
        $doorTypeId     = $nullableInt($input['door_type_id']         ?? '');
        $widthMm        = $nullableInt($input['width_mm']             ?? '');
        $heightMm       = $nullableInt($input['height_mm']            ?? '');

        // Preserve the configurator cart shape so the customer's full selection
        // (collection, construction, dimensions, features) survives an admin edit.
        $config = [
            'width_mm'             => $widthMm,
            'height_mm'            => $heightMm,
            'collection_id'        => $collectionId,
            'color_id'             => $colorId,
            'door_type_id'         => $doorTypeId,
            'construction_type_id' => $constructionId,
            'feature_ids'          => $featureIds,
        ];
        $featuresJson = json_encode([
            'items' => [[
                'config'     => $config,
                'quantity'   => 1,
                'unit_price' => ($price !== '' && $price !== null) ? (float)$price : null,
                'line_total' => ($price !== '' && $price !== null) ? (float)$price : null,
            ]],
        ]);

        return [
            'product_id'           => $nullableInt($input['product_id'] ?? ''),
            'collection_id'        => $collectionId,
            'material_id'          => $nullableInt($input['material_id'] ?? ''),
            'color_id'             => $colorId,
            'door_type_id'         => $doorTypeId,
            'construction_type_id' => $constructionId,
            'width_mm'             => $widthMm,
            'height_mm'            => $heightMm,
            'handle'               => $nullableStr($input['handle'] ?? ''),
            'features_json'        => $featuresJson,
            'final_price'          => ($price !== '' && $price !== null) ? (float)$price : null,
        ];
    }
}
