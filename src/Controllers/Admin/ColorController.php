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
use App\Validators\ColorValidator;

class ColorController
{
    private const PER_PAGE   = 20;
    private const UPLOAD_DIR = APP_ROOT . '/public/uploads/colors';
    private const WEB_PATH   = '/door-showroom/uploads/colors';

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
        $search = trim($_GET['q']     ?? '');
        $filter = $_GET['status']     ?? '';

        $where  = ['1=1'];
        $params = [];

        if ($search !== '') {
            $like     = '%' . $search . '%';
            $where[]  = '(c.name LIKE ? OR c.hex LIKE ?)';
            $params[] = $like;
            $params[] = $like;
        }
        if ($filter === 'active') {
            $where[] = 'c.is_active = 1';
        } elseif ($filter === 'inactive') {
            $where[] = 'c.is_active = 0';
        }
        $clause = implode(' AND ', $where);

        // All colors (no pagination — catalogue is small), ordered for grouping.
        $stmt = $db->prepare(
            "SELECT c.*,
                    col.name AS collection_name,
                    col.display_order AS collection_order,
                    COUNT(DISTINCT pc.product_id) AS product_count
             FROM colors c
             LEFT JOIN collections col ON col.id = c.collection_id
             LEFT JOIN product_colors pc ON pc.color_id = c.id
             WHERE $clause
             GROUP BY c.id
             ORDER BY col.display_order ASC, col.name ASC, c.display_order ASC, c.name ASC"
        );
        $stmt->execute($params);
        $colors = $stmt->fetchAll();
        $total  = count($colors);

        // All active collections, so empty collections still render a group.
        $collections = $db->query(
            'SELECT id, name FROM collections WHERE is_active = 1 ORDER BY display_order ASC, name ASC'
        )->fetchAll();

        // Group colors under their collection. Key 0 = "No collection".
        $grouped = [];
        foreach ($collections as $col) {
            $grouped[(int)$col['id']] = ['name' => $col['name'], 'colors' => []];
        }
        foreach ($colors as $c) {
            $cid = $c['collection_id'] !== null ? (int)$c['collection_id'] : 0;
            if (!isset($grouped[$cid])) {
                $grouped[$cid] = ['name' => $c['collection_name'] ?? 'No collection', 'colors' => []];
            }
            $grouped[$cid]['colors'][] = $c;
        }
        // Append an "unassigned" bucket only if such colors exist.
        $unassigned = array_filter($colors, static fn ($c) => $c['collection_id'] === null);
        if (!empty($unassigned) && !isset($grouped[0])) {
            $grouped[0] = ['name' => 'No collection', 'colors' => array_values($unassigned)];
        }

        $csrfToken   = CsrfGuard::token();
        $flash       = Session::getFlash('color_success');
        $user        = $this->auth->user();
        $pageTitle   = 'Colors';
        $currentPage = 'colors';
        $view        = 'colors/index';

        require APP_ROOT . '/src/Views/admin/layout.php';
    }

    public function create(): void
    {
        AuthMiddleware::requireAuth();

        $products    = $this->allProducts();
        $collections = $this->allCollections();
        $user      = $this->auth->user();
        $csrfToken = CsrfGuard::token();
        $errors    = Session::getFlash('form_errors', []);
        $old       = Session::getFlash('form_old', []);

        $color       = null;
        $assigned    = [];
        $pageTitle   = 'Add Color';
        $currentPage = 'colors';
        $view        = 'colors/form';
        $formAction  = '/door-showroom/admin/colors/store';

        require APP_ROOT . '/src/Views/admin/layout.php';
    }

    public function store(): void
    {
        AuthMiddleware::requireAuth();
        CsrfMiddleware::verify();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/door-showroom/admin/colors');
        }

        $input     = $this->postInput();
        $validator = new ColorValidator();

        if (!$validator->validateColor($input)) {
            Session::flash('form_errors', $validator->errors());
            Session::flash('form_old', $input);
            $this->redirect('/door-showroom/admin/colors/create');
        }

        $d         = $validator->data();
        $filename  = $this->handleImageUpload('color_image');
        $texture   = $this->handleImageUpload('texture_image');
        $collId    = ($_POST['collection_id'] ?? '') !== '' ? (int)$_POST['collection_id'] : null;

        Database::conn()->prepare(
            'INSERT INTO colors (collection_id, name, hex, price, description, image_filename, texture_filename, display_order, is_active)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        )->execute([
            $collId, $d['name'], $d['hex'], $d['price'], $d['description'],
            $filename, $texture, $d['display_order'], $d['is_active'],
        ]);

        $colorId = (int)Database::conn()->lastInsertId();
        $this->syncProducts($colorId, $_POST['product_ids'] ?? []);

        Session::flash('color_success', 'Color "' . $d['name'] . '" created.');
        $this->redirect('/door-showroom/admin/colors');
    }

    public function edit(int $id): void
    {
        AuthMiddleware::requireAuth();

        $color     = $this->findOrFail($id);
        $products  = $this->allProducts();
        $collections = $this->allCollections();
        $assigned  = $this->assignedProducts($id);
        $user      = $this->auth->user();
        $csrfToken = CsrfGuard::token();
        $errors    = Session::getFlash('form_errors', []);
        $old       = Session::getFlash('form_old', []);

        $pageTitle   = 'Edit Color';
        $currentPage = 'colors';
        $view        = 'colors/form';
        $formAction  = '/door-showroom/admin/colors/' . $id . '/update';

        require APP_ROOT . '/src/Views/admin/layout.php';
    }

    public function update(int $id): void
    {
        AuthMiddleware::requireAuth();
        CsrfMiddleware::verify();

        $color = $this->findOrFail($id);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/door-showroom/admin/colors');
        }

        $input     = $this->postInput();
        $validator = new ColorValidator();

        if (!$validator->validateColor($input, $id)) {
            Session::flash('form_errors', $validator->errors());
            Session::flash('form_old', $input);
            $this->redirect('/door-showroom/admin/colors/' . $id . '/edit');
        }

        $d        = $validator->data();
        $filename = $this->handleImageUpload('color_image', $color['image_filename']);
        $texture  = $this->handleImageUpload('texture_image', $color['texture_filename'] ?? null);
        $collId   = ($_POST['collection_id'] ?? '') !== '' ? (int)$_POST['collection_id'] : null;

        Database::conn()->prepare(
            'UPDATE colors
             SET collection_id=?, name=?, hex=?, price=?, description=?, image_filename=?, texture_filename=?, display_order=?, is_active=?
             WHERE id=?'
        )->execute([
            $collId, $d['name'], $d['hex'], $d['price'], $d['description'],
            $filename, $texture, $d['display_order'], $d['is_active'], $id,
        ]);

        $this->syncProducts($id, $_POST['product_ids'] ?? []);

        Session::flash('color_success', 'Color "' . $d['name'] . '" updated.');
        $this->redirect('/door-showroom/admin/colors');
    }

    public function delete(int $id): void
    {
        header('Content-Type: application/json');
        AuthMiddleware::requireAuth();
        CsrfMiddleware::verify();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
            return;
        }

        $color = $this->findOrFail($id, true);

        if ($color['image_filename']) {
            (new ImageUploader(self::UPLOAD_DIR, self::WEB_PATH))
                ->delete($color['image_filename']);
        }

        Database::conn()->prepare('DELETE FROM colors WHERE id = ?')->execute([$id]);

        echo json_encode(['success' => true, 'message' => 'Color "' . $color['name'] . '" deleted.']);
    }

    public function toggle(int $id): void
    {
        header('Content-Type: application/json');
        AuthMiddleware::requireAuth();
        CsrfMiddleware::verify();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
            return;
        }

        $color    = $this->findOrFail($id, true);
        $newState = $color['is_active'] ? 0 : 1;

        Database::conn()
            ->prepare('UPDATE colors SET is_active = ? WHERE id = ?')
            ->execute([$newState, $id]);

        echo json_encode(['success' => true, 'is_active' => $newState, 'label' => $newState ? 'Active' : 'Inactive']);
    }

    public function deleteImage(int $id): void
    {
        header('Content-Type: application/json');
        AuthMiddleware::requireAuth();
        CsrfMiddleware::verify();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
            return;
        }

        $color = $this->findOrFail($id, true);

        if (!$color['image_filename']) {
            echo json_encode(['success' => false, 'message' => 'No image to delete.']);
            return;
        }

        (new ImageUploader(self::UPLOAD_DIR, self::WEB_PATH))
            ->delete($color['image_filename']);

        Database::conn()
            ->prepare('UPDATE colors SET image_filename = NULL WHERE id = ?')
            ->execute([$id]);

        echo json_encode(['success' => true]);
    }

    private function handleImageUpload(string $field, ?string $existing = null): ?string
    {
        if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
            return $existing;
        }

        $uploader = new ImageUploader(self::UPLOAD_DIR, self::WEB_PATH);
        $results  = $uploader->uploadMany($_FILES[$field]);

        if (!empty($uploader->errors())) {
            return $existing;
        }

        if (!empty($results)) {
            if ($existing) {
                $uploader->delete($existing);
            }
            return $results[0];
        }

        return $existing;
    }

    private function syncProducts(int $colorId, array $productIds): void
    {
        $db  = Database::conn();
        $db->prepare('DELETE FROM product_colors WHERE color_id = ?')->execute([$colorId]);

        $ids = array_filter(array_map('intval', $productIds));
        if (empty($ids)) return;

        $placeholders = implode(',', array_fill(0, count($ids), '(?, ?)'));
        $values       = [];
        foreach ($ids as $pid) {
            $values[] = $pid;
            $values[] = $colorId;
        }
        $db->prepare("INSERT IGNORE INTO product_colors (product_id, color_id) VALUES $placeholders")
           ->execute($values);
    }

    private function allCollections(): array
    {
        return Database::conn()
            ->query('SELECT id, name FROM collections WHERE is_active = 1 ORDER BY display_order, name')
            ->fetchAll();
    }

    private function allProducts(): array
    {
        return Database::conn()
            ->query('SELECT id, name FROM products WHERE is_active = 1 ORDER BY name')
            ->fetchAll();
    }

    private function assignedProducts(int $colorId): array
    {
        $stmt = Database::conn()->prepare(
            'SELECT product_id FROM product_colors WHERE color_id = ?'
        );
        $stmt->execute([$colorId]);
        return array_column($stmt->fetchAll(), 'product_id');
    }

    private function findOrFail(int $id, bool $jsonError = false): array
    {
        $stmt = Database::conn()->prepare('SELECT * FROM colors WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if (!$row) {
            if ($jsonError) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Color not found.']);
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
            'name'          => $_POST['name']          ?? '',
            'hex'           => $_POST['hex']           ?? '',
            'price'         => $_POST['price']         ?? '0',
            'description'   => $_POST['description']   ?? '',
            'collection_id' => $_POST['collection_id'] ?? '',
            'display_order' => $_POST['display_order'] ?? '0',
            'is_active'     => $_POST['is_active']     ?? null,
        ];
    }

    private function redirect(string $url): never
    {
        header('Location: ' . $url);
        exit;
    }
}
