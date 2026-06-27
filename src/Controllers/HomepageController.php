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
            'file'  => 'interior-bedroom-black.png',
        ],
        [
            'num'   => '02',
            'name'  => 'Moderne',
            'line'  => 'Quiet, flush, precise.',
            'desc'  => 'Surfaces that disappear into the wall. Grey lacquer and aluminium for interiors where the door is felt, not seen.',
            'file'  => 'interior-grey-kitchen.png',
        ],
        [
            'num'   => '03',
            'name'  => 'Heritage',
            'line'  => 'Crafted to endure.',
            'desc'  => 'Solid timber and aged bronze, shaped by hands that have spent a lifetime studying the threshold.',
            'file'  => 'interior-entry-hall.png',
        ],
    ];

    private const FEATURED = [
        ['name' => 'Scuro',         'collection' => 'Moderne',  'file' => 'porte-scuro.jpg',   'slug' => 'moderne-scuro-chambre-nedabaile'],
        ['name' => 'Chêne Naturel', 'collection' => 'Heritage', 'file' => 'chene.jpg',         'slug' => 'heritage-chne-chambre-ndabaile'],
        ['name' => 'Madera',        'collection' => 'Moderne',  'file' => 'portes-madera.jpg', 'slug' => 'moderne-madera-chambre-nedabaile'],
        ['name' => 'Simza',         'collection' => 'Moderne',  'file' => 'portes-cinza.jpg',  'slug' => 'moderne-simza-chambre-nedabaile'],
    ];

    private const PROCESS = [
        ['num' => '01', 'name' => 'Collection',   'desc' => 'Choose the world that fits your home.'],
        ['num' => '02', 'name' => 'Colour',       'desc' => 'Pick from our signature finishes.'],
        ['num' => '03', 'name' => 'Usage',        'desc' => 'Bedroom, living room, entrance, bathroom.'],
        ['num' => '04', 'name' => 'Construction', 'desc' => 'Select the build that suits the opening.'],
        ['num' => '05', 'name' => 'Design',       'desc' => 'Refine the panel and detailing.'],
        ['num' => '06', 'name' => 'Dimensions',   'desc' => 'Engineered to your exact opening.'],
        ['num' => '07', 'name' => 'Review & Quote','desc' => 'Confirm and request your quote.'],
    ];

    private const INSPIRATION = [
        ['file' => 'interior-bedroom-black.png','caption' => 'Prestige · Noir',  'span' => 'feature'],
        ['file' => 'interior-grey-kitchen.png', 'caption' => 'Moderne · Gris',   'span' => ''],
        ['file' => 'interior-entry-hall.png',   'caption' => 'Heritage · Entrée','span' => ''],
    ];

    public function show(): void
    {
        Session::start();

        // Real colors from the catalogue (uploaded image/texture when present).
        $colors        = $this->loadColors();
        $colorGroups   = $this->groupColorsByCollection($colors);
        $collections   = self::COLLECTIONS;
        $featured      = self::FEATURED;
        $process       = self::PROCESS;
        $inspiration   = self::INSPIRATION;

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
