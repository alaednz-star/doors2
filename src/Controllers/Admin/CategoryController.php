<?php

namespace App\Controllers\Admin;

use App\Auth\Authenticator;
use App\Auth\CsrfGuard;
use App\Core\Database;
use App\Core\Session;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\SecurityHeaders;
use App\Validators\CategoryValidator;

class CategoryController
{
    private const PER_PAGE = 10;

    private Authenticator $auth;

    public function __construct()
    {
        Session::start();
        SecurityHeaders::apply();
        $this->auth = new Authenticator();
    }

    /* ── LIST ── */
    public function index(): void
    {
        AuthMiddleware::requireAuth();

        $db      = Database::conn();
        $user    = $this->auth->user();
        $search  = trim($_GET['q'] ?? '');
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $offset  = ($page - 1) * self::PER_PAGE;

        if ($search !== '') {
            $like  = '%' . $search . '%';
            $total = (int) $this->scalar(
                'SELECT COUNT(*) FROM categories WHERE name LIKE ? OR description LIKE ?',
                [$like, $like]
            );
            $stmt = $db->prepare(
                'SELECT c.*, u.name AS creator_name
                 FROM categories c
                 LEFT JOIN admin_users u ON u.id = c.created_by
                 WHERE c.name LIKE ? OR c.description LIKE ?
                 ORDER BY c.display_order ASC, c.created_at DESC
                 LIMIT ? OFFSET ?'
            );
            $stmt->execute([$like, $like, self::PER_PAGE, $offset]);
        } else {
            $total = (int) $this->scalar('SELECT COUNT(*) FROM categories', []);
            $stmt  = $db->prepare(
                'SELECT c.*, u.name AS creator_name
                 FROM categories c
                 LEFT JOIN admin_users u ON u.id = c.created_by
                 ORDER BY c.display_order ASC, c.created_at DESC
                 LIMIT ? OFFSET ?'
            );
            $stmt->execute([self::PER_PAGE, $offset]);
        }

        $categories  = $stmt->fetchAll();
        $totalPages  = (int) ceil($total / self::PER_PAGE);
        $csrfToken   = CsrfGuard::token();
        $flash       = Session::getFlash('category_success');
        $pageTitle   = 'Categories';
        $currentPage = 'categories';
        $view        = 'categories/index';

        require APP_ROOT . '/src/Views/admin/layout.php';
    }

    /* ── CREATE FORM ── */
    public function create(): void
    {
        AuthMiddleware::requireAuth();

        $user        = $this->auth->user();
        $csrfToken   = CsrfGuard::token();
        $errors      = Session::getFlash('form_errors', []);
        $old         = Session::getFlash('form_old', []);
        $pageTitle   = 'Add Category';
        $currentPage = 'categories';
        $view        = 'categories/form';
        $category    = null;
        $formAction  = '/door-showroom/admin/categories/store';

        require APP_ROOT . '/src/Views/admin/layout.php';
    }

    /* ── STORE ── */
    public function store(): void
    {
        AuthMiddleware::requireAuth();
        CsrfMiddleware::verify();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/door-showroom/admin/categories');
        }

        $input     = $this->postInput();
        $validator = new CategoryValidator();

        if (!$validator->validate($input)) {
            Session::flash('form_errors', $validator->errors());
            Session::flash('form_old', $input);
            $this->redirect('/door-showroom/admin/categories/create');
        }

        $data = $validator->data();
        $user = $this->auth->user();

        Database::conn()->prepare(
            'INSERT INTO categories (name, slug, description, display_order, is_active, created_by)
             VALUES (?, ?, ?, ?, ?, ?)'
        )->execute([
            $data['name'],
            $data['slug'],
            $data['description'],
            $data['display_order'],
            $data['is_active'],
            $user['id'],
        ]);

        Session::flash('category_success', 'Category "' . $data['name'] . '" created successfully.');
        $this->redirect('/door-showroom/admin/categories');
    }

    /* ── EDIT FORM ── */
    public function edit(int $id): void
    {
        AuthMiddleware::requireAuth();

        $category = $this->findOrFail($id);

        $user        = $this->auth->user();
        $csrfToken   = CsrfGuard::token();
        $errors      = Session::getFlash('form_errors', []);
        $old         = Session::getFlash('form_old', []);
        $pageTitle   = 'Edit Category';
        $currentPage = 'categories';
        $view        = 'categories/form';
        $formAction  = '/door-showroom/admin/categories/' . $id . '/update';

        require APP_ROOT . '/src/Views/admin/layout.php';
    }

    /* ── UPDATE ── */
    public function update(int $id): void
    {
        AuthMiddleware::requireAuth();
        CsrfMiddleware::verify();

        $this->findOrFail($id);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/door-showroom/admin/categories');
        }

        $input     = $this->postInput();
        $validator = new CategoryValidator();

        if (!$validator->validate($input, $id)) {
            Session::flash('form_errors', $validator->errors());
            Session::flash('form_old', $input);
            $this->redirect('/door-showroom/admin/categories/' . $id . '/edit');
        }

        $data = $validator->data();

        Database::conn()->prepare(
            'UPDATE categories
             SET name = ?, slug = ?, description = ?, display_order = ?, is_active = ?
             WHERE id = ?'
        )->execute([
            $data['name'],
            $data['slug'],
            $data['description'],
            $data['display_order'],
            $data['is_active'],
            $id,
        ]);

        Session::flash('category_success', 'Category "' . $data['name'] . '" updated successfully.');
        $this->redirect('/door-showroom/admin/categories');
    }

    /* ── DELETE ── */
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

        $category = $this->findOrFail($id, true);

        Database::conn()
            ->prepare('DELETE FROM categories WHERE id = ?')
            ->execute([$id]);

        echo json_encode([
            'success' => true,
            'message' => 'Category "' . $category['name'] . '" deleted.',
        ]);
    }

    /* ── TOGGLE STATUS (inline) ── */
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

        $category = $this->findOrFail($id, true);
        $newState = $category['is_active'] ? 0 : 1;

        Database::conn()
            ->prepare('UPDATE categories SET is_active = ? WHERE id = ?')
            ->execute([$newState, $id]);

        echo json_encode([
            'success'   => true,
            'is_active' => $newState,
            'label'     => $newState ? 'Active' : 'Inactive',
        ]);
    }

    /* ── Helpers ── */
    private function findOrFail(int $id, bool $jsonError = false): array
    {
        $stmt = Database::conn()->prepare('SELECT * FROM categories WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if (!$row) {
            if ($jsonError) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Category not found.']);
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
