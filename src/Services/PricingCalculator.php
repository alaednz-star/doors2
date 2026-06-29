<?php

namespace App\Services;

use App\Core\Database;

/**
 * Pricing engine — matrix model.
 *
 * A price is one cell in the matrix: Collection × Door Usage × Construction Type.
 * The cell holds a base price valid at the reference door size; the ordered size
 * scales that price proportionally by area. A cell flagged unavailable returns no
 * price ("Non disponible").
 *
 *   finalPrice = matrixBasePrice × (orderedArea / referenceArea)
 *
 * Reference size comes from settings (pricing_ref_width_mm / pricing_ref_height_mm),
 * defaulting to 900 × 2100 mm. Optional features are added on top of an available cell.
 */
class PricingCalculator
{
    private const CURRENCY            = 'DZD';
    private const DEFAULT_REF_WIDTH   = 900;
    private const DEFAULT_REF_HEIGHT  = 2100;
    private const UNAVAILABLE_LABEL   = 'Non disponible';

    public function calculate(array $input): array
    {
        $collectionId   = $this->intOrNull($input['collection_id'] ?? null);
        $doorTypeId     = $this->intOrNull($input['door_type_id'] ?? null);
        $constructionId = $this->intOrNull($input['construction_type_id'] ?? null);
        $colorId        = $this->intOrNull($input['color_id'] ?? null);
        $widthMm        = $this->intOrNull($input['width_mm'] ?? null);
        $heightMm       = $this->intOrNull($input['height_mm'] ?? null);
        $featureIds     = isset($input['feature_ids']) ? array_map('intval', (array)$input['feature_ids']) : [];

        $cell = $this->matchCell($collectionId, $doorTypeId, $constructionId);

        // No matrix cell, or the cell is explicitly unavailable → "Non disponible".
        if ($cell === null || (int)$cell['is_available'] !== 1) {
            return $this->unavailable($cell);
        }

        // Base price is valid at the reference size; scale it by ordered area.
        [$refWidth, $refHeight] = $this->referenceSize();
        $referenceArea = $refWidth * $refHeight;

        $orderedWidth  = $widthMm  ?: $refWidth;
        $orderedHeight = $heightMm ?: $refHeight;
        $orderedArea   = $orderedWidth * $orderedHeight;

        // Per-colour surcharge is added to the matrix price BEFORE area scaling,
        // so the colour cost scales with door size too: (matrix + colour) × area.
        $matrixPrice = (float)$cell['base_price'];
        $colorPrice  = $this->colorPrice($colorId);
        $combined    = $matrixPrice + $colorPrice;

        $basePrice   = $referenceArea > 0
            ? $combined * ($orderedArea / $referenceArea)
            : $combined;
        $basePrice = round($basePrice, 2);

        // Optional features sit on top of the (scaled) base price.
        $optionsPrice    = 0.0;
        $featuresApplied = [];
        if (!empty($featureIds)) {
            foreach ($this->loadFeatures($featureIds) as $f) {
                $contrib = $f['price_type'] === 'percent'
                    ? $basePrice * ((float)$f['price'] / 100)
                    : (float)$f['price'];
                $optionsPrice += $contrib;
                $featuresApplied[] = [
                    'id'    => (int)$f['id'],
                    'name'  => $f['name'],
                    'price' => round($contrib, 2),
                ];
            }
        }

        $totalPrice = round($basePrice + $optionsPrice, 2);

        return [
            'available'        => true,
            'label'            => null,

            // raw numeric values
            'base_price'       => $basePrice,
            'options_price'    => round($optionsPrice, 2),
            'total_price'      => $totalPrice,
            'currency'         => self::CURRENCY,

            // formatted strings for direct display
            'base_price_fmt'   => $this->format($basePrice),
            'options_price_fmt'=> $this->format($optionsPrice),
            'total_price_fmt'  => $this->format($totalPrice),

            // pricing transparency / audit
            'matrix_price'     => round($matrixPrice, 2),
            'color_price'      => round($colorPrice, 2),
            'reference_size'   => ['width_mm' => $refWidth, 'height_mm' => $refHeight],
            'ordered_size'     => ['width_mm' => $orderedWidth, 'height_mm' => $orderedHeight],
            'rule_id'          => (int)$cell['id'],
            'rules_applied'    => [[
                'id'           => (int)$cell['id'],
                'name'         => $cell['name'],
                'contribution' => $basePrice,
            ]],
            'features_applied' => $featuresApplied,
        ];
    }

    public function persist(array $input, array $result): int
    {
        $token = bin2hex(random_bytes(32));

        Database::conn()->prepare(
            'INSERT INTO price_calculations
             (session_token, product_id, material_id, color_id, door_type_id,
              width_mm, height_mm, features_json, rules_applied, base_price, options_price, total_price, currency)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)'
        )->execute([
            $token,
            $input['product_id']   ?? null,
            $input['material_id']  ?? null,
            $input['color_id']     ?? null,
            $input['door_type_id'] ?? null,
            $input['width_mm']     ?? null,
            $input['height_mm']    ?? null,
            json_encode($input['feature_ids'] ?? []),
            json_encode($result['rules_applied'] ?? []),
            $result['base_price']    ?? 0,
            $result['options_price'] ?? 0,
            $result['total_price']   ?? 0,
            $result['currency']      ?? self::CURRENCY,
        ]);

        return (int)Database::conn()->lastInsertId();
    }

    /**
     * The single matrix cell for this Collection × Usage × Construction combination.
     * Returns null when any axis is missing or no active rule matches.
     */
    private function matchCell(?int $collectionId, ?int $doorTypeId, ?int $constructionId): ?array
    {
        if (!$collectionId || !$doorTypeId || !$constructionId) {
            return null;
        }

        $stmt = Database::conn()->prepare(
            'SELECT * FROM price_rules
             WHERE is_active = 1
               AND collection_id = ?
               AND door_type_id = ?
               AND construction_type_id = ?
             ORDER BY priority DESC, id ASC
             LIMIT 1'
        );
        $stmt->execute([$collectionId, $doorTypeId, $constructionId]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    /** Reference door size from settings, falling back to 900 × 2100 mm. */
    private function referenceSize(): array
    {
        $width  = (int)$this->setting('pricing_ref_width_mm',  self::DEFAULT_REF_WIDTH);
        $height = (int)$this->setting('pricing_ref_height_mm', self::DEFAULT_REF_HEIGHT);

        if ($width  <= 0) $width  = self::DEFAULT_REF_WIDTH;
        if ($height <= 0) $height = self::DEFAULT_REF_HEIGHT;

        return [$width, $height];
    }

    private function setting(string $key, $default)
    {
        try {
            $stmt = Database::conn()->prepare(
                'SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1'
            );
            $stmt->execute([$key]);
            $val = $stmt->fetchColumn();
            return ($val === false || $val === null || $val === '') ? $default : $val;
        } catch (\Throwable $e) {
            // settings table missing → safe default, never break pricing.
            return $default;
        }
    }

    private function unavailable(?array $cell): array
    {
        return [
            'available'        => false,
            'label'            => self::UNAVAILABLE_LABEL,

            'base_price'       => 0.0,
            'options_price'    => 0.0,
            'total_price'      => 0.0,
            'currency'         => self::CURRENCY,

            'base_price_fmt'   => self::UNAVAILABLE_LABEL,
            'options_price_fmt'=> self::UNAVAILABLE_LABEL,
            'total_price_fmt'  => self::UNAVAILABLE_LABEL,

            'matrix_price'     => $cell ? round((float)$cell['base_price'], 2) : 0.0,
            'rule_id'          => $cell ? (int)$cell['id'] : null,
            'rules_applied'    => [],
            'features_applied' => [],
        ];
    }

    /** Per-colour surcharge (0 when no colour, colour not found, or column absent). */
    private function colorPrice(?int $colorId): float
    {
        if (!$colorId) {
            return 0.0;
        }
        try {
            $stmt = Database::conn()->prepare('SELECT price FROM colors WHERE id = ? LIMIT 1');
            $stmt->execute([$colorId]);
            $val = $stmt->fetchColumn();
            return $val !== false ? (float)$val : 0.0;
        } catch (\Throwable $e) {
            return 0.0;
        }
    }

    private function loadFeatures(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = Database::conn()->prepare(
            "SELECT id, name, price, price_type FROM optional_features
             WHERE id IN ($placeholders) AND is_active = 1"
        );
        $stmt->execute($ids);
        return $stmt->fetchAll();
    }

    private function format(float $amount): string
    {
        return number_format($amount, 0, '.', ' ') . ' ' . self::CURRENCY;
    }

    private function intOrNull($v): ?int
    {
        return (isset($v) && $v !== '' && (int)$v > 0) ? (int)$v : null;
    }
}
