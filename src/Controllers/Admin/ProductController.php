<?php

namespace App\Controllers\Admin;

use App\Auth\Authenticator;
use App\Auth\CsrfGuard;
use App\Core\Database;
use App\Core\Session;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\SecurityHeaders;
use App\Services\ImageUploader;
use App\Validators\ProductValidator;

class ProductController
{
    private const PER_PAGE   = 12;
    private const UPLOAD_DIR = APP_ROOT . '/public/uploads/products';
    private const UPLOAD_WEB = '/door-showroom/uploads/products';

    private Authenticator $auth;

    public function __construct()
    {
        Session::start();
        SecurityHeaders::apply();
        $this->auth = new Authenticator();
    }

    public function index(): void
    {
        AuthMiddleware::requireAuth();

        $db     = Database::conn();
        $search = trim($_GET['q'] ?? '');
        $filter = $_GET['filter'] ?? '';
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($page - 1) * self::PER_PAGE;

        [$where, $params] = $this->buildWhere($search, $filter);

        $total = (int) $this->scalar(
            "SELECT COUNT(*) FROM products p {$where}",
            $params
        );

        $stmt = $db->prepare(
            "SELECT p.*, c.name AS category_name, col.name AS collection_name,
                    clr.name AS color_name, clr.hex AS color_hex,
                    dt.name AS usage_name, ct.name AS construction_name,
                    (SELECT filename FROM product_images WHERE product_id = p.id AND is_cover = 1 LIMIT 1) AS cover_image
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             LEFT JOIN collections col ON col.id = p.collection_id
             LEFT JOIN colors clr ON clr.id = p.color_id
             LEFT JOIN door_types dt ON dt.id = p.door_type_id
             LEFT JOIN construction_types ct ON ct.id = p.construction_type_id
             {$where}
             ORDER BY col.display_order, clr.display_order, dt.display_order, p.id
             LIMIT ? OFFSET ?"
        );
        $stmt->execute(array_merge($params, [self::PER_PAGE, $offset]));

        $products    = $stmt->fetchAll();
        $totalPages  = (int) ceil($total / self::PER_PAGE);
        $csrfToken   = CsrfGuard::token();
        $flash       = Session::getFlash('product_success');
        $user        = $this->auth->user();
        $pageTitle   = 'Products';
        $currentPage = 'products';
        $view        = 'products/index';

        require APP_ROOT . '/src/Views/admin/layout.php';
    }

    public function create(): void
    {
        AuthMiddleware::requireAuth();

        $user        = $this->auth->user();
        $csrfToken   = CsrfGuard::token();
        $errors      = Session::getFlash('form_errors', []);
        $old         = Session::getFlash('form_old', []);
        $pageTitle   = 'Add Product';
        $currentPage = 'products';
        $view        = 'products/form';
        $product     = null;
        $images      = [];
        $formAction  = '/door-showroom/admin/products/store';

        [$categories, $collections, $constructionTypes, $colors] = $this->lookups();

        $selectedColors     = array_map('intval', (array)($old['color_ids'] ?? []));
        $slugPreviewValue   = '';

        require APP_ROOT . '/src/Views/admin/layout.php';
    }

    public function store(): void
    {
        AuthMiddleware::requireAuth();
        CsrfMiddleware::verify();

        $input     = $this->postInput();
        $validator = new ProductValidator();

        if (!$validator->validate($input)) {
            Session::flash('form_errors', $validator->errors());
            Session::flash('form_old', $input);
            $this->redirect('/door-showroom/admin/products/create');
        }

        $data = $validator->data();
        $user = $this->auth->user();
        $db   = Database::conn();

        $db->prepare(
            'INSERT INTO products (name, slug, sku, description, width_mm, height_mm, display_order,
                                   category_id, collection_id, construction_type_id, is_featured, is_active, created_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        )->execute([
            $data['name'], $data['slug'], $data['sku'], $data['description'],
            $data['width_mm'], $data['height_mm'], $data['display_order'], $data['category_id'],
            $data['collection_id'], $data['construction_type_id'], $data['is_featured'], $data['is_active'], $user['id'],
        ]);

        $productId = (int) $db->lastInsertId();

        $this->syncPivot($productId, 'product_colors', 'color_id', $data['color_ids']);

        if (!empty($_FILES['images']['name'][0])) {
            $uploadErrors = $this->handleImages($productId, $_FILES['images'], true);
            if (!empty($uploadErrors)) {
                Session::flash('upload_errors', $uploadErrors);
            }
        }

        Session::flash('product_success', 'Product "' . $data['name'] . '" created successfully.');
        $this->redirect('/door-showroom/admin/products');
    }

    public function edit(int $id): void
    {
        AuthMiddleware::requireAuth();

        $product = $this->findOrFail($id);
        $images  = $this->productImages($id);

        $user        = $this->auth->user();
        $csrfToken   = CsrfGuard::token();
        $errors      = Session::getFlash('form_errors', []);
        $old         = Session::getFlash('form_old', []);
        $pageTitle   = 'Edit Product';
        $currentPage = 'products';
        $view        = 'products/form';
        $formAction  = '/door-showroom/admin/products/' . $id . '/update';

        [$categories, $collections, $constructionTypes, $colors] = $this->lookups();

        $selectedColors = !empty($old)
            ? array_map('intval', (array)($old['color_ids'] ?? []))
            : $this->pivotIds($id, 'product_colors', 'color_id');

        $slugPreviewValue = $product['slug'];

        require APP_ROOT . '/src/Views/admin/layout.php';
    }

    public function update(int $id): void
    {
        AuthMiddleware::requireAuth();
        CsrfMiddleware::verify();

        $this->findOrFail($id);

        $input     = $this->postInput();
        $validator = new ProductValidator();

        if (!$validator->validate($input, $id)) {
            Session::flash('form_errors', $validator->errors());
            Session::flash('form_old', $input);
            $this->redirect('/door-showroom/admin/products/' . $id . '/edit');
        }

        $data = $validator->data();
        $db   = Database::conn();

        $db->prepare(
            'UPDATE products SET name=?, slug=?, sku=?, description=?, width_mm=?, height_mm=?,
             display_order=?, category_id=?, collection_id=?, construction_type_id=?, is_featured=?, is_active=?
             WHERE id=?'
        )->execute([
            $data['name'], $data['slug'], $data['sku'], $data['description'],
            $data['width_mm'], $data['height_mm'], $data['display_order'], $data['category_id'],
            $data['collection_id'], $data['construction_type_id'], $data['is_featured'], $data['is_active'], $id,
        ]);

        $this->syncPivot($id, 'product_colors', 'color_id', $data['color_ids']);

        if (!empty($_FILES['images']['name'][0])) {
            $uploadErrors = $this->handleImages($id, $_FILES['images'], false);
            if (!empty($uploadErrors)) {
                Session::flash('upload_errors', $uploadErrors);
            }
        }

        Session::flash('product_success', 'Product "' . $data['name'] . '" updated successfully.');
        $this->redirect('/door-showroom/admin/products/' . $id . '/edit');
    }

    public function delete(int $id): void
    {
        header('Content-Type: application/json');
        AuthMiddleware::requireAuth();
        CsrfMiddleware::verify();

        $product = $this->findOrFail($id, true);
        $images  = $this->productImages($id);

        $uploader = new ImageUploader(self::UPLOAD_DIR, self::UPLOAD_WEB);
        foreach ($images as $img) {
            $uploader->delete($img['filename']);
        }

        Database::conn()->prepare('DELETE FROM products WHERE id = ?')->execute([$id]);

        echo json_encode(['success' => true, 'message' => 'Product "' . $product['name'] . '" deleted.']);
    }

    public function toggle(int $id): void
    {
        header('Content-Type: application/json');
        AuthMiddleware::requireAuth();
        CsrfMiddleware::verify();

        $product  = $this->findOrFail($id, true);
        $newState = $product['is_active'] ? 0 : 1;

        Database::conn()
            ->prepare('UPDATE products SET is_active = ? WHERE id = ?')
            ->execute([$newState, $id]);

        echo json_encode([
            'success'   => true,
            'is_active' => $newState,
            'label'     => $newState ? 'Active' : 'Inactive',
        ]);
    }

    public function deleteImage(int $imageId): void
    {
        header('Content-Type: application/json');
        AuthMiddleware::requireAuth();
        CsrfMiddleware::verify();

        $db   = Database::conn();
        $stmt = $db->prepare('SELECT * FROM product_images WHERE id = ? LIMIT 1');
        $stmt->execute([$imageId]);
        $img = $stmt->fetch();

        if (!$img) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Image not found.']);
            return;
        }

        $uploader = new ImageUploader(self::UPLOAD_DIR, self::UPLOAD_WEB);
        $uploader->delete($img['filename']);

        $db->prepare('DELETE FROM product_images WHERE id = ?')->execute([$imageId]);

        if ($img['is_cover']) {
            $db->prepare(
                'UPDATE product_images SET is_cover = 1
                 WHERE product_id = ? ORDER BY sort_order ASC, id ASC LIMIT 1'
            )->execute([$img['product_id']]);
        }

        echo json_encode(['success' => true]);
    }

    public function setCover(int $imageId): void
    {
        header('Content-Type: application/json');
        AuthMiddleware::requireAuth();
        CsrfMiddleware::verify();

        $db   = Database::conn();
        $stmt = $db->prepare('SELECT * FROM product_images WHERE id = ? LIMIT 1');
        $stmt->execute([$imageId]);
        $img = $stmt->fetch();

        if (!$img) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Image not found.']);
            return;
        }

        $db->prepare('UPDATE product_images SET is_cover = 0 WHERE product_id = ?')
           ->execute([$img['product_id']]);
        $db->prepare('UPDATE product_images SET is_cover = 1 WHERE id = ?')
           ->execute([$imageId]);

        echo json_encode(['success' => true]);
    }

    public function reorderImages(int $productId): void
    {
        header('Content-Type: application/json');
        AuthMiddleware::requireAuth();
        CsrfMiddleware::verify();

        $body = json_decode(file_get_contents('php://input'), true) ?? [];
        $ids  = array_map('intval', (array)($body['ids'] ?? []));

        if (empty($ids)) {
            echo json_encode(['success' => false, 'message' => 'No order provided.']);
            return;
        }

        $db = Database::conn();
        foreach ($ids as $order => $imgId) {
            $db->prepare(
                'UPDATE product_images SET sort_order = ? WHERE id = ? AND product_id = ?'
            )->execute([$order, $imgId, $productId]);
        }

        echo json_encode(['success' => true]);
    }

    private function handleImages(int $productId, array $filesInput, bool $firstIsCover): array
    {
        $uploader  = new ImageUploader(self::UPLOAD_DIR, self::UPLOAD_WEB);
        $filenames = $uploader->uploadMany($filesInput);

        if (empty($filenames) && !empty($uploader->errors())) {
            return $uploader->errors();
        }

        $db          = Database::conn();
        $existCover  = (bool) $this->scalar(
            'SELECT COUNT(*) FROM product_images WHERE product_id = ? AND is_cover = 1',
            [$productId]
        );

        $sortBase = (int) $this->scalar(
            'SELECT COALESCE(MAX(sort_order), -1) + 1 FROM product_images WHERE product_id = ?',
            [$productId]
        );

        foreach ($filenames as $i => $filename) {
            $isCover = ($firstIsCover && $i === 0 && !$existCover) ? 1 : 0;
            $db->prepare(
                'INSERT INTO product_images (product_id, filename, is_cover, sort_order) VALUES (?, ?, ?, ?)'
            )->execute([$productId, $filename, $isCover, $sortBase + $i]);
        }

        return $uploader->errors();
    }

    private function lookups(): array
    {
        $db = Database::conn();

        $cats = $db->query(
            'SELECT id, name FROM categories WHERE is_active = 1 ORDER BY display_order, name'
        )->fetchAll();

        $cols = $db->query(
            'SELECT id, name FROM collections WHERE is_active = 1 ORDER BY display_order, name'
        )->fetchAll();

        $constr = $db->query(
            'SELECT id, name FROM construction_types WHERE is_active = 1 ORDER BY display_order, name'
        )->fetchAll();

        $clrs = $db->query(
            'SELECT id, name, hex, collection_id FROM colors WHERE is_active = 1 ORDER BY display_order, name'
        )->fetchAll();

        return [$cats, $cols, $constr, $clrs];
    }

    private function syncPivot(int $productId, string $table, string $col, array $ids): void
    {
        $db = Database::conn();
        $db->prepare("DELETE FROM `{$table}` WHERE product_id = ?")->execute([$productId]);

        if (empty($ids)) {
            return;
        }

        $stmt = $db->prepare("INSERT INTO `{$table}` (product_id, `{$col}`) VALUES (?, ?)");
        foreach (array_unique($ids) as $relId) {
            $stmt->execute([$productId, $relId]);
        }
    }

    private function pivotIds(int $productId, string $table, string $col): array
    {
        $stmt = Database::conn()->prepare(
            "SELECT `{$col}` FROM `{$table}` WHERE product_id = ?"
        );
        $stmt->execute([$productId]);
        return array_column($stmt->fetchAll(), $col);
    }

    private function productImages(int $productId): array
    {
        $stmt = Database::conn()->prepare(
            'SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order ASC, id ASC'
        );
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }

    private function buildWhere(string $search, string $filter): array
    {
        $clauses = [];
        $params  = [];

        if ($search !== '') {
            $clauses[] = '(p.name LIKE ? OR p.sku LIKE ? OR p.description LIKE ?)';
            $like      = '%' . $search . '%';
            $params    = array_merge($params, [$like, $like, $like]);
        }

        if ($filter === 'active') {
            $clauses[] = 'p.is_active = 1';
        } elseif ($filter === 'inactive') {
            $clauses[] = 'p.is_active = 0';
        } elseif ($filter === 'featured') {
            $clauses[] = 'p.is_featured = 1';
        }

        $where = empty($clauses) ? '' : 'WHERE ' . implode(' AND ', $clauses);
        return [$where, $params];
    }

    private function findOrFail(int $id, bool $jsonError = false): array
    {
        $stmt = Database::conn()->prepare('SELECT * FROM products WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if (!$row) {
            if ($jsonError) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Product not found.']);
                exit;
            }
            http_response_code(404);
            require APP_ROOT . '/src/Views/admin/404.php';
            exit;
        }

        return $row;
    }

    private function scalar(string $sql, array $params): mixed
    {
        $stmt = Database::conn()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    private function postInput(): array
    {
        return [
            'name'                 => $_POST['name'] ?? '',
            'sku'                  => $_POST['sku'] ?? '',
            'description'          => $_POST['description'] ?? '',
            'width_mm'             => $_POST['width_mm'] ?? '',
            'height_mm'            => $_POST['height_mm'] ?? '',
            'display_order'        => $_POST['display_order'] ?? '0',
            'category_id'          => $_POST['category_id'] ?? '',
            'collection_id'        => $_POST['collection_id'] ?? '',
            'construction_type_id' => $_POST['construction_type_id'] ?? '',
            'is_featured'          => $_POST['is_featured'] ?? null,
            'is_active'            => $_POST['is_active'] ?? null,
            'color_ids'            => $_POST['color_ids'] ?? [],
        ];
    }

    private function redirect(string $url): never
    {
        header('Location: ' . $url);
        exit;
    }
}
