<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Session;

class HomepageController
{
    private const COLLECTIONS = [
        [
            'num'   => '01',
            'name'  => 'Prestige',
            'line'  => 'Luxury without compromise.',
            'desc'  => 'The summit of the catalogue. Bespoke dimensions, rare veneers and finishes that cannot be ordered from a page.',
            'file'  => 'marron-prestige.jpg',
        ],
        [
            'num'   => '02',
            'name'  => 'Moderne',
            'line'  => 'Quiet, flush, precise.',
            'desc'  => 'Surfaces that disappear into the wall. Grey lacquer and aluminium for interiors where the door is felt, not seen.',
            'file'  => 'gris-prestige.jpg',
        ],
        [
            'num'   => '03',
            'name'  => 'Heritage',
            'line'  => 'Crafted to endure.',
            'desc'  => 'Solid timber and aged bronze, shaped by hands that have spent a lifetime studying the threshold.',
            'file'  => 'chene.jpg',
        ],
    ];

    private const FEATURED = [
        ['name' => 'Wengué Pivot',  'collection' => 'Prestige', 'file' => 'porte-scuro.jpg'],
        ['name' => 'Chêne Naturel', 'collection' => 'Heritage', 'file' => 'chene.jpg'],
        ['name' => 'Madera',        'collection' => 'Heritage', 'file' => 'portes-madera.jpg'],
        ['name' => 'Simza',         'collection' => 'Moderne',  'file' => 'portes-cinza.jpg'],
    ];

    public function show(): void
    {
        Session::start();

        // Real colors from the catalogue (uploaded image/texture when present).
        $colors        = $this->loadColors();
        $colorGroups   = $this->groupColorsByCollection($colors);
        $collections   = self::COLLECTIONS;
        $featured      = self::FEATURED;

        require APP_ROOT . '/src/Views/homepage.php';
    }

    /** Active colors (with their collection) the homepage showcase expects. */
    private function loadColors(): array
    {
        try {
            $rows = Database::conn()->query(
                'SELECT c.name, c.hex, c.image_filename, c.texture_filename,
                        col.name AS collection_name, col.display_order AS collection_order
                 FROM colors c
                 LEFT JOIN collections col ON col.id = c.collection_id
                 WHERE c.is_active = 1
                 ORDER BY col.display_order ASC, col.name ASC, c.display_order ASC, c.name ASC'
            )->fetchAll();
        } catch (\Throwable $e) {
            return [];
        }

        return array_map(static function (array $c): array {
            return [
                'name'       => $c['name'],
                'collection' => $c['collection_name'] ?? '',
                'hex'        => $c['hex'] ?: '#9A9389',
                'file'       => $c['image_filename']   ? '/door-showroom/uploads/colors/' . $c['image_filename']   : null,
                'tex'        => $c['texture_filename'] ? '/door-showroom/uploads/colors/' . $c['texture_filename'] : null,
            ];
        }, $rows);
    }

    /** Colors grouped by collection name, preserving order. */
    private function groupColorsByCollection(array $colors): array
    {
        $grouped = [];
        foreach ($colors as $c) {
            $key = $c['collection'] !== '' ? $c['collection'] : 'Other';
            $grouped[$key][] = $c;
        }
        return $grouped;
    }
}
