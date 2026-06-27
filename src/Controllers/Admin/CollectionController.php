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

class CollectionController
{
    private const UPLOAD_DIR = APP_ROOT . '/public/uploads/collections';
    private const WEB_PATH   = '/door-showroom/uploads/collections';

    private const PER_PAGE = 10;

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
        $user   = $this->auth->user();
        $search = trim($_GET['q'] ?? '');
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($page - 1) * self::PER_PAGE;

        if ($search !== '') {
            $like  = '%' . $search . '%';
            $total = (int) $this->scalar(
                'SELECT COUNT(*) FROM collections WHERE name LIKE ? OR description LIKE ?',
                [$like, $like]
            );
            $stmt = $db->prepare(
                'SELECT * FROM collections
                 WHERE name LIKE ? OR description LIKE ?
                 ORDER BY display_order ASC, created_at DESC
                 LIMIT ? OFFSET ?'
            );
            $stmt->execute([$like, $like, self::PER_PAGE, $offset]);
        } else {
            $total = (int) $this->scalar('SELECT COUNT(*) FROM collections', []);
            $stmt  = $db->prepare(
                'SELECT * FROM collections
                 ORDER BY display_order ASC, created_at DESC
                 LIMIT ? OFFSET ?'
            );
            $stmt->execute([self::PER_PAGE, $offset]);
        }

        $collections = $stmt->fetchAll();
        $totalPages  = (int) ceil($total / self::PER_PAGE);
        $csrfToken   = CsrfGuard::token();
        $flash       = Session::getFlash('collection_success');
        $pageTitle   = 'Collections';
        $currentPage = 'collections';
        $view        = 'collections/index';

        require APP_ROOT . '/src/Views/admin/layout.php';
    }

    public function create(): void
    {
        AuthMiddleware::requireAuth();

        $user        = $this->auth->user();
        $csrfToken   = CsrfGuard::token();
        $errors      = Session::getFlash('form_errors', []);
        $old         = Session::getFlash('form_old', []);
        $pageTitle   = 'Add Collection';
        $currentPage = 'collections';
        $view        = 'collections/form';
        $collection  = null;
        $formAction  = '/door-showroom/admin/collections/store';

        require APP_ROOT . '/src/Views/admin/layout.php';
    }

    public function store(): void
    {
        AuthMiddleware::requireAuth();
        CsrfMiddleware::verify();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/door-showroom/admin/collections');
        }

        $input  = $this->postInput();
        $errors = $this->validate($input);

        if (!empty($errors)) {
            Session::flash('form_errors', $errors);
            Session::flash('form_old', $input);
            $this->redirect('/door-showroom/admin/collections/create');
        }

        $slug  = $this->slugify($input['name']);
        $image = $this->handleImageUpload('collection_image');
        $hero  = $this->handleImageUpload('hero_image');

        Database::conn()->prepare(
            'INSERT INTO collections (name, slug, description, image_filename, hero_image, display_order, is_active)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        )->execute([
            trim($input['name']),
            $slug,
            trim($input['description']) ?: null,
            $image,
            $hero,
            (int)$input['display_order'],
            isset($input['is_active']) ? 1 : 0,
        ]);

        Session::flash('collection_success', 'Collection "' . trim($input['name']) . '" created.');
        $this->redirect('/door-showroom/admin/collections');
    }

    public function edit(int $id): void
    {
        AuthMiddleware::requireAuth();

        $collection  = $this->findOrFail($id);
        $user        = $this->auth->user();
        $csrfToken   = CsrfGuard::token();
        $errors      = Session::getFlash('form_errors', []);
        $old         = Session::getFlash('form_old', []);
        $pageTitle   = 'Edit Collection';
        $currentPage = 'collections';
        $view        = 'collections/form';
        $formAction  = '/door-showroom/admin/collections/' . $id . '/update';

        require APP_ROOT . '/src/Views/admin/layout.php';
    }

    public function update(int $id): void
    {
        AuthMiddleware::requireAuth();
        CsrfMiddleware::verify();

        $existing = $this->findOrFail($id);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/door-showroom/admin/collections');
        }

        $input  = $this->postInput();
        $errors = $this->validate($input, $id);

        if (!empty($errors)) {
            Session::flash('form_errors', $errors);
            Session::flash('form_old', $input);
            $this->redirect('/door-showroom/admin/collections/' . $id . '/edit');
        }

        $slug  = $this->slugify($input['name']);
        $image = $this->handleImageUpload('collection_image', $existing['image_filename'] ?? null);
        $hero  = $this->handleImageUpload('hero_image', $existing['hero_image'] ?? null);

        Database::conn()->prepare(
            'UPDATE collections SET name = ?, slug = ?, description = ?, image_filename = ?, hero_image = ?, display_order = ?, is_active = ?
             WHERE id = ?'
        )->execute([
            trim($input['name']),
            $slug,
            trim($input['description']) ?: null,
            $image,
            $hero,
            (int)$input['display_order'],
            isset($input['is_active']) ? 1 : 0,
            $id,
        ]);

        Session::flash('collection_success', 'Collection "' . trim($input['name']) . '" updated.');
        $this->redirect('/door-showroom/admin/collections');
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

        $collection = $this->findOrFail($id, true);

        Database::conn()->prepare('DELETE FROM collections WHERE id = ?')->execute([$id]);

        echo json_encode(['success' => true, 'message' => 'Collection "' . $collection['name'] . '" deleted.']);
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

        $collection = $this->findOrFail($id, true);
        $newState   = $collection['is_active'] ? 0 : 1;

        Database::conn()->prepare('UPDATE collections SET is_active = ? WHERE id = ?')->execute([$newState, $id]);

        echo json_encode([
            'success'   => true,
            'is_active' => $newState,
            'label'     => $newState ? 'Active' : 'Inactive',
        ]);
    }

    private function validate(array $input, ?int $excludeId = null): array
    {
        $errors = [];
        $name   = trim($input['name'] ?? '');
        $desc   = trim($input['description'] ?? '');
        $order  = $input['display_order'] ?? '0';

        if ($name === '') {
            $errors['name'] = 'Collection name is required.';
        } elseif (strlen($name) < 2) {
            $errors['name'] = 'Name must be at least 2 characters.';
        } elseif (strlen($name) > 120) {
            $errors['name'] = 'Name must not exceed 120 characters.';
        } else {
            $slug = $this->slugify($name);
            $sql  = 'SELECT COUNT(*) FROM collections WHERE slug = ?';
            $args = [$slug];
            if ($excludeId !== null) {
                $sql  .= ' AND id != ?';
                $args[] = $excludeId;
            }
            $stmt = Database::conn()->prepare($sql);
            $stmt->execute($args);
            if ((int)$stmt->fetchColumn() > 0) {
                $errors['name'] = 'A collection with this name already exists.';
            }
        }

        if ($desc !== '' && strlen($desc) > 2000) {
            $errors['description'] = 'Description must not exceed 2000 characters.';
        }

        if (!ctype_digit((string)$order) || (int)$order < 0 || (int)$order > 9999) {
            $errors['display_order'] = 'Display order must be a number between 0 and 9999.';
        }

        return $errors;
    }

    private function handleImageUpload(string $field, ?string $existing = null): ?string
    {
        if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
            return $existing;
        }
        $uploader = new ImageUploader(self::UPLOAD_DIR, self::WEB_PATH);
        $results  = $uploader->uploadMany($_FILES[$field]);
        if (!empty($uploader->errors()) || empty($results)) {
            return $existing;
        }
        if ($existing) {
            $uploader->delete($existing);
        }
        return $results[0];
    }

    private function slugify(string $text): string
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9\s\-]/u', '', $text);
        $text = preg_replace('/[\s\-]+/', '-', $text);
        return trim($text, '-');
    }

    private function findOrFail(int $id, bool $jsonError = false): array
    {
        $stmt = Database::conn()->prepare('SELECT * FROM collections WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if (!$row) {
            if ($jsonError) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Collection not found.']);
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
            'name'          => $_POST['name'] ?? '',
            'description'   => $_POST['description'] ?? '',
            'display_order' => $_POST['display_order'] ?? '0',
            'is_active'     => $_POST['is_active'] ?? null,
        ];
    }

    private function redirect(string $url): never
    {
        header('Location: ' . $url);
        exit;
    }
}
