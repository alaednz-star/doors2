<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Session;

class CollectionsController
{
    /** Editorial layer keyed by slug — storytelling + curated imagery. */
    private const META = [
        'heritage' => [
            'order'    => 1,
            'tagline'  => 'Crafted to endure.',
            'story'    => 'Classical proportion in solid timber and aged bronze. Doors shaped by hands that have spent a lifetime studying the threshold — built to outlast the rooms they open.',
            'cover'    => 'chene.jpg',
            'tone'     => 'Wood',
            'doors'    => 14,
        ],
        'moderne' => [
            'order'    => 2,
            'tagline'  => 'Quiet, flush, precise.',
            'story'    => 'Surfaces that disappear into the wall. Grey lacquer, aluminium and hairline reveals for interiors where the door is felt, not seen.',
            'cover'    => 'gris-prestige.jpg',
            'tone'     => 'Minimal',
            'doors'    => 11,
        ],
        'prestige' => [
            'order'    => 3,
            'tagline'  => 'Luxury without compromise.',
            'story'    => 'The summit of the catalogue. Bespoke dimensions, rare veneers and finishes that cannot be ordered from a page — reserved for landmark interiors.',
            'cover'    => 'marron-prestige.jpg',
            'tone'     => 'Bespoke',
            'doors'    => 9,
        ],
    ];

    private const FALLBACK_COVER = 'porte-scuro.jpg';

    public function show(): void
    {
        Session::start();

        $db = Database::conn();

        $q = trim((string)($_GET['q'] ?? ''));
        if (strlen($q) > 80) {
            $q = substr($q, 0, 80);
        }

        try {
            $sql = 'SELECT c.id, c.name, c.slug, c.description, c.display_order,
                           (SELECT COUNT(*) FROM products p
                            WHERE p.collection_id = c.id AND p.is_active = 1) AS product_count,
                           (SELECT pi.filename FROM products p
                            JOIN product_images pi ON pi.product_id = p.id
                            WHERE p.collection_id = c.id AND p.is_active = 1 AND pi.is_cover = 1
                            ORDER BY p.display_order ASC LIMIT 1) AS cover
                    FROM collections c
                    WHERE c.is_active = 1';

            $params = [];
            if ($q !== '') {
                $sql .= ' AND (c.name LIKE :qn OR c.description LIKE :qd)';
                $params[':qn'] = '%' . $q . '%';
                $params[':qd'] = '%' . $q . '%';
            }

            $sql .= ' ORDER BY c.display_order ASC, c.name ASC';

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $collections = $stmt->fetchAll();
        } catch (\Throwable $e) {
            $collections = [];
        }

        foreach ($collections as &$c) {
            $meta = self::META[$c['slug']] ?? [];

            if (empty($c['cover'])) {
                $c['cover_url'] = '/door-showroom/assets/images/'
                    . ($meta['cover'] ?? self::FALLBACK_COVER);
            } else {
                $c['cover_url'] = '/door-showroom/uploads/' . $c['cover'];
            }

            $c['tagline']    = $meta['tagline'] ?? '';
            $c['story']      = $meta['story'] ?? ($c['description'] ?: '');
            $c['tone']       = $meta['tone'] ?? '';
            // show the larger of the real product count or the curated figure,
            // so the page never reads "1 Door" while the catalogue is half-seeded
            $c['door_count'] = max((int) $c['product_count'], (int) ($meta['doors'] ?? 0));
            $c['num']        = str_pad((string) ($meta['order'] ?? 0), 2, '0', STR_PAD_LEFT);
        }
        unset($c);

        require APP_ROOT . '/src/Views/collections.php';
    }

    /** Collection detail page — /door-showroom/collections/{slug}. */
    /**
     * Collection detail page — /door-showroom/collections/{slug}.
     * 100% database-driven. Sections with no data are hidden by the view.
     */
    public function detail(): void
    {
        Session::start();

        $slug = trim((string) ($_GET['slug'] ?? ''));
        $db   = Database::conn();

        $collection = null;
        $products   = [];
        $features   = [];
        $colors     = [];

        try {
            $stmt = $db->prepare(
                'SELECT id, name, slug, description FROM collections
                 WHERE slug = :slug AND is_active = 1 LIMIT 1'
            );
            $stmt->execute([':slug' => $slug]);
            $collection = $stmt->fetch();

            if ($collection) {
                $cid = (int) $collection['id'];

                // Products in this collection (with their cover image)
                $ps = $db->prepare(
                    'SELECT p.id, p.name, p.slug, p.description, p.dimensions,
                            (SELECT pi.filename FROM product_images pi
                             WHERE pi.product_id = p.id
                             ORDER BY pi.is_cover DESC, pi.sort_order ASC LIMIT 1) AS cover
                     FROM products p
                     WHERE p.collection_id = :cid AND p.is_active = 1
                     ORDER BY p.is_featured DESC, p.display_order ASC, p.name ASC'
                );
                $ps->execute([':cid' => $cid]);
                $products = $ps->fetchAll();

                // Features — from collection_features (created on demand)
                $features = $this->loadFeatures($db, $cid);

                // Colors belonging to this collection (the real model)
                $colors = $this->loadCollectionColors($db, $cid);
            }
        } catch (\Throwable $e) {
            // leave whatever loaded; missing collection → 404 below
        }

        if (!$collection) {
            http_response_code(404);
            $notFound = true;
            require APP_ROOT . '/src/Views/collection-detail.php';
            return;
        }

        // Map DB products to model cards. No fabricated entries.
        $models = [];
        foreach ($products as $p) {
            $models[] = [
                'name'     => $p['name'],
                'slug'     => $p['slug'],
                'file'     => $p['cover'] ? '/door-showroom/uploads/' . $p['cover'] : null,
                'desc'     => $p['description'] ? $this->excerpt($p['description']) : '',
                'dimensions' => $p['dimensions'] ?? '',
            ];
        }

        // Collection imagery is derived from real product cover images only.
        $heroImg  = null;
        foreach ($models as $m) {
            if ($m['file']) { $heroImg = $m['file']; break; }
        }

        $page = [
            'name'        => $collection['name'],
            'slug'        => $collection['slug'],
            'description' => $collection['description'] ?? '',
            'hero'        => $heroImg,          // null when no product images exist
            'models'      => $models,
            'features'    => $features,         // [] → section hidden
            'colors'      => $colors,           // [] → section hidden
            'projects'    => [],                // no projects system → always hidden
        ];

        $notFound = false;
        require APP_ROOT . '/src/Views/collection-detail.php';
    }

    /** Features for a collection from collection_features, [] if table/data absent. */
    private function loadFeatures(\PDO $db, int $cid): array
    {
        try {
            $st = $db->prepare(
                'SELECT title, description FROM collection_features
                 WHERE collection_id = :cid AND is_active = 1
                 ORDER BY display_order ASC, id ASC'
            );
            $st->execute([':cid' => $cid]);
            return $st->fetchAll();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /** Colors belonging to this collection, [] if none. */
    private function loadCollectionColors(\PDO $db, int $cid): array
    {
        try {
            $st = $db->prepare(
                'SELECT id, name, hex, description, image_filename
                 FROM colors
                 WHERE collection_id = :cid AND is_active = 1
                 ORDER BY display_order ASC, name ASC'
            );
            $st->execute([':cid' => $cid]);
            $rows = $st->fetchAll();
            foreach ($rows as &$r) {
                $r['image_url'] = $r['image_filename']
                    ? '/door-showroom/uploads/colors/' . $r['image_filename']
                    : null;
            }
            unset($r);
            return $rows;
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function excerpt(string $text, int $limit = 110): string
    {
        $text = trim(preg_replace('/\s+/', ' ', $text));
        if (strlen($text) <= $limit) {
            return $text;
        }
        $cut = substr($text, 0, $limit);
        $sp  = strrpos($cut, ' ');
        return rtrim($sp ? substr($cut, 0, $sp) : $cut, " ,.;:") . '…';
    }
}
