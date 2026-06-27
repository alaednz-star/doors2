<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Session;

class ProductPageController
{
    public function show(): void
    {
        Session::start();

        $slug = $_GET['slug'] ?? '';
        if ($slug === '') {
            $this->notFound();
        }

        $db = Database::conn();

        $product = $db->prepare(
            "SELECT p.*,
                    c.name  AS category_name,
                    c.slug  AS category_slug,
                    co.name AS collection_name,
                    co.slug AS collection_slug
             FROM products p
             LEFT JOIN categories  c  ON c.id  = p.category_id
             LEFT JOIN collections co ON co.id = p.collection_id
             WHERE p.slug = ? AND p.is_active = 1
             LIMIT 1"
        );
        $product->execute([$slug]);
        $product = $product->fetch();

        if (!$product) {
            $this->notFound();
        }

        $id = (int)$product['id'];

        $images = $db->prepare(
            "SELECT filename, alt_text, is_cover, sort_order
             FROM product_images
             WHERE product_id = ?
             ORDER BY is_cover DESC, sort_order ASC"
        );
        $images->execute([$id]);
        $images = $images->fetchAll();

        // Available colors: the product's own colors, or the collection's colors
        // when none are linked individually. Both come from the database.
        $colors = $db->prepare(
            "SELECT c.id, c.name, c.hex, c.image_filename
             FROM colors c
             INNER JOIN product_colors pc ON pc.color_id = c.id
             WHERE pc.product_id = ? AND c.is_active = 1
             ORDER BY c.display_order ASC, c.name ASC"
        );
        $colors->execute([$id]);
        $colors = $colors->fetchAll();

        if (!$colors && $product['collection_id'] !== null) {
            $collColors = $db->prepare(
                "SELECT id, name, hex, image_filename
                 FROM colors
                 WHERE collection_id = ? AND is_active = 1
                 ORDER BY display_order ASC, name ASC"
            );
            $collColors->execute([(int)$product['collection_id']]);
            $colors = $collColors->fetchAll();
        }

        $related = $db->prepare(
            "SELECT p2.id, p2.name, p2.slug, p2.description,
                    (SELECT pi.filename FROM product_images pi WHERE pi.product_id = p2.id AND pi.is_cover = 1 LIMIT 1) AS cover,
                    co.name AS collection_name
             FROM products p2
             LEFT JOIN collections co ON co.id = p2.collection_id
             WHERE p2.id != ?
               AND p2.is_active = 1
               AND (p2.collection_id = ? OR p2.category_id = ?)
             ORDER BY p2.is_featured DESC, RAND()
             LIMIT 3"
        );
        $related->execute([$id, $product['collection_id'], $product['category_id']]);
        $related = $related->fetchAll();

        if (count($related) < 3) {
            $exclude = array_merge([$id], array_column($related, 'id'));
            $placeholders = implode(',', array_fill(0, count($exclude), '?'));
            $extra = $db->prepare(
                "SELECT p2.id, p2.name, p2.slug, p2.description,
                        (SELECT pi.filename FROM product_images pi WHERE pi.product_id = p2.id AND pi.is_cover = 1 LIMIT 1) AS cover,
                        co.name AS collection_name
                 FROM products p2
                 LEFT JOIN collections co ON co.id = p2.collection_id
                 WHERE p2.id NOT IN ($placeholders) AND p2.is_active = 1
                 ORDER BY p2.is_featured DESC, RAND()
                 LIMIT " . (3 - count($related))
            );
            $extra->execute($exclude);
            $related = array_merge($related, $extra->fetchAll());
        }

        // Construction type for this product (single, from the new model).
        $construction = null;
        if ($product['construction_type_id'] !== null) {
            $ct = $db->prepare(
                "SELECT name FROM construction_types WHERE id = ? AND is_active = 1 LIMIT 1"
            );
            $ct->execute([(int)$product['construction_type_id']]);
            $construction = $ct->fetchColumn() ?: null;
        }

        // Door usages this product's collection can serve (available matrix cells).
        // Falls back to all active usages if the collection has no matrix yet.
        $doorTypes = [];
        if ($product['collection_id'] !== null) {
            $du = $db->prepare(
                "SELECT DISTINCT dt.id, dt.name
                 FROM door_types dt
                 INNER JOIN price_rules r ON r.door_type_id = dt.id
                 WHERE dt.is_active = 1 AND r.is_active = 1 AND r.is_available = 1
                   AND r.collection_id = ?
                 ORDER BY dt.display_order ASC, dt.name ASC"
            );
            $du->execute([(int)$product['collection_id']]);
            $doorTypes = $du->fetchAll();
        }
        if (!$doorTypes) {
            $doorTypes = $db->query(
                "SELECT id, name FROM door_types WHERE is_active = 1 ORDER BY display_order ASC, name ASC"
            )->fetchAll();
        }

        // Starting price = lowest available matrix base price for this collection
        // (new matrix model). Null when the collection has no available cell.
        $basePrice = null;
        if ($product['collection_id'] !== null) {
            $pr = $db->prepare(
                "SELECT MIN(base_price) FROM price_rules
                 WHERE is_active = 1 AND is_available = 1 AND base_price > 0
                   AND collection_id = ?"
            );
            $pr->execute([(int)$product['collection_id']]);
            $min = $pr->fetchColumn();
            if ($min !== false && $min !== null) {
                $basePrice = (float)$min;
            }
        }

        require APP_ROOT . '/src/Views/product.php';
    }

    private function notFound(): never
    {
        http_response_code(404);
        echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Not Found</title></head><body style="background:#0a0a0a;color:#fff;font-family:sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;flex-direction:column;gap:1rem"><h1 style="font-size:3rem">404</h1><p>This door does not exist.</p><a href="/door-showroom" style="color:#b8935a">Return home</a></body></html>';
        exit;
    }
}
