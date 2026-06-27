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

/**
 * Reusable CRUD base for simple admin-managed lookup tables
 * (construction_types, door_types/usages, …). Subclasses only declare the
 * table, labels, routes and image paths — the workflow is shared.
 *
 * Provides: index (search + status filter + pagination), create/store,
 * edit/update, delete + toggle (JSON), with optional image upload, slug
 * generation, name-uniqueness, CSRF + auth on every mutation.
 */
abstract class LookupController
{
    protected const PER_PAGE = 50;

    /** @var Authenticator */
    protected Authenticator $auth;

    /** Override per entity. */
    abstract protected function table(): string;          // e.g. 'construction_types'
    abstract protected function routeBase(): string;       // e.g. '/door-showroom/admin/construction-types'
    abstract protected function singular(): string;        // e.g. 'Construction Type'
    abstract protected function plural(): string;          // e.g. 'Construction Types'
    abstract protected function navKey(): string;          // sidebar $currentPage key
    abstract protected function viewDir(): string;         // e.g. 'construction_types'

    /** Override if the table supports an uploaded image. */
    protected function hasImage(): bool { return false; }
    protected function uploadDir(): string { return ''; }
    protected function uploadWebPath(): string { return ''; }
    protected function imageField(): string { return 'image'; }

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
        $table  = $this->table();
        $search = trim($_GET['q'] ?? '');
        $filter = $_GET['status'] ?? '';
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($page - 1) * self::PER_PAGE;

        $where = ['1=1']; $params = [];
        if ($search !== '') {
            $where[] = '(name LIKE ? OR description LIKE ?)';
            $params[] = "%$search%"; $params[] = "%$search%";
        }
        if ($filter === 'active')   $where[] = 'is_active = 1';
        if ($filter === 'inactive') $where[] = 'is_active = 0';
        $clause = implode(' AND ', $where);

        $total = (int)$this->scalar("SELECT COUNT(*) FROM `$table` WHERE $clause", $params);

        $stmt = $db->prepare(
            "SELECT * FROM `$table` WHERE $clause ORDER BY display_order ASC, id ASC LIMIT ? OFFSET ?"
        );
        $stmt->execute([...$params, self::PER_PAGE, $offset]);
        $rows = $stmt->fetchAll();

        $totalPages  = max(1, (int)ceil($total / self::PER_PAGE));
        $csrfToken   = CsrfGuard::token();
        $flash       = Session::getFlash($this->table() . '_success');
        $user        = $this->auth->user();
        $pageTitle   = $this->plural();
        $currentPage = $this->navKey();
        $view        = $this->viewDir() . '/index';

        // expose entity meta to the view
        $meta = $this->viewMeta();

        require APP_ROOT . '/src/Views/admin/layout.php';
    }

    public function create(): void
    {
        AuthMiddleware::requireAuth();

        $row         = null;
        $user        = $this->auth->user();
        $csrfToken   = CsrfGuard::token();
        $errors      = Session::getFlash('form_errors', []);
        $old         = Session::getFlash('form_old', []);
        $pageTitle   = 'Add ' . $this->singular();
        $currentPage = $this->navKey();
        $view        = $this->viewDir() . '/form';
        $formAction  = $this->routeBase() . '/store';
        $meta        = $this->viewMeta();

        require APP_ROOT . '/src/Views/admin/layout.php';
    }

    public function store(): void
    {
        AuthMiddleware::requireAuth();
        CsrfMiddleware::verify();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->redirect($this->routeBase());

        [$ok, $errors, $d] = $this->validate();
        if (!$ok) {
            Session::flash('form_errors', $errors);
            Session::flash('form_old', $this->postInput());
            $this->redirect($this->routeBase() . '/create');
        }

        $filename = $this->hasImage() ? $this->handleImageUpload() : null;
        $table = $this->table();

        if ($this->hasImage()) {
            Database::conn()->prepare(
                "INSERT INTO `$table` (name, slug, description, image_filename, display_order, is_active)
                 VALUES (?,?,?,?,?,?)"
            )->execute([$d['name'], $d['slug'], $d['description'], $filename, $d['display_order'], $d['is_active']]);
        } else {
            Database::conn()->prepare(
                "INSERT INTO `$table` (name, slug, description, display_order, is_active)
                 VALUES (?,?,?,?,?)"
            )->execute([$d['name'], $d['slug'], $d['description'], $d['display_order'], $d['is_active']]);
        }

        Session::flash($table . '_success', $this->singular() . ' "' . $d['name'] . '" created.');
        $this->redirect($this->routeBase());
    }

    public function edit(int $id): void
    {
        AuthMiddleware::requireAuth();

        $row         = $this->findOrFail($id);
        $user        = $this->auth->user();
        $csrfToken   = CsrfGuard::token();
        $errors      = Session::getFlash('form_errors', []);
        $old         = Session::getFlash('form_old', []);
        $pageTitle   = 'Edit ' . $this->singular();
        $currentPage = $this->navKey();
        $view        = $this->viewDir() . '/form';
        $formAction  = $this->routeBase() . '/' . $id . '/update';
        $meta        = $this->viewMeta();

        require APP_ROOT . '/src/Views/admin/layout.php';
    }

    public function update(int $id): void
    {
        AuthMiddleware::requireAuth();
        CsrfMiddleware::verify();
        $row = $this->findOrFail($id);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->redirect($this->routeBase());

        [$ok, $errors, $d] = $this->validate($id);
        if (!$ok) {
            Session::flash('form_errors', $errors);
            Session::flash('form_old', $this->postInput());
            $this->redirect($this->routeBase() . '/' . $id . '/edit');
        }

        $table = $this->table();
        if ($this->hasImage()) {
            $filename = $this->handleImageUpload($row['image_filename'] ?? null);
            Database::conn()->prepare(
                "UPDATE `$table` SET name=?, slug=?, description=?, image_filename=?, display_order=?, is_active=? WHERE id=?"
            )->execute([$d['name'], $d['slug'], $d['description'], $filename, $d['display_order'], $d['is_active'], $id]);
        } else {
            Database::conn()->prepare(
                "UPDATE `$table` SET name=?, slug=?, description=?, display_order=?, is_active=? WHERE id=?"
            )->execute([$d['name'], $d['slug'], $d['description'], $d['display_order'], $d['is_active'], $id]);
        }

        Session::flash($table . '_success', $this->singular() . ' "' . $d['name'] . '" updated.');
        $this->redirect($this->routeBase());
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

        $row = $this->findOrFail($id, true);

        // Block delete if referenced by a price rule (protect the matrix).
        if ($ref = $this->blockingReference($id)) {
            echo json_encode(['success' => false, 'message' => $ref]);
            return;
        }

        if ($this->hasImage() && !empty($row['image_filename'])) {
            (new ImageUploader($this->uploadDir(), $this->uploadWebPath()))->delete($row['image_filename']);
        }
        Database::conn()->prepare("DELETE FROM `{$this->table()}` WHERE id = ?")->execute([$id]);

        echo json_encode(['success' => true, 'message' => $this->singular() . ' "' . $row['name'] . '" deleted.']);
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

        $row = $this->findOrFail($id, true);
        $new = $row['is_active'] ? 0 : 1;
        Database::conn()->prepare("UPDATE `{$this->table()}` SET is_active = ? WHERE id = ?")->execute([$new, $id]);

        echo json_encode(['success' => true, 'is_active' => $new, 'label' => $new ? 'Active' : 'Inactive']);
    }

    /** Override to protect referenced lookups (e.g. used in price_rules). */
    protected function blockingReference(int $id): ?string { return null; }

    /** Validation shared by all simple lookups. */
    protected function validate(?int $excludeId = null): array
    {
        $in = $this->postInput();
        $errors = [];

        $name = trim($in['name'] ?? '');
        if ($name === '')              $errors['name'] = 'Name is required.';
        elseif (strlen($name) > 100)   $errors['name'] = 'Name must be under 100 characters.';
        elseif ($this->nameExists($name, $excludeId)) $errors['name'] = 'A ' . strtolower($this->singular()) . ' with this name already exists.';

        $desc = trim($in['description'] ?? '');
        if ($desc !== '' && strlen($desc) > 1000) $errors['description'] = 'Description must be under 1000 characters.';

        $order = $in['display_order'] ?? '0';
        if (!ctype_digit((string)$order) || (int)$order > 9999) $errors['display_order'] = 'Display order must be 0–9999.';

        if ($errors) return [false, $errors, []];

        return [true, [], [
            'name'          => $name,
            'slug'          => $this->slugify($name),
            'description'   => $desc !== '' ? $desc : null,
            'display_order' => (int)$order,
            'is_active'     => isset($in['is_active']) ? 1 : 0,
        ]];
    }

    protected function postInput(): array
    {
        return [
            'name'          => $_POST['name'] ?? '',
            'description'   => $_POST['description'] ?? '',
            'display_order' => $_POST['display_order'] ?? '0',
            'is_active'     => $_POST['is_active'] ?? null,
        ];
    }

    /** Per-entity metadata for views (labels, routes). */
    protected function viewMeta(): array
    {
        return [
            'singular'    => $this->singular(),
            'plural'      => $this->plural(),
            'routeBase'   => $this->routeBase(),
            'hasImage'    => $this->hasImage(),
            'imageField'  => $this->imageField(),
            'imageWebPath'=> $this->uploadWebPath(),
        ];
    }

    protected function nameExists(string $name, ?int $excludeId): bool
    {
        $sql = "SELECT COUNT(*) FROM `{$this->table()}` WHERE name = ?";
        $params = [$name];
        if ($excludeId) { $sql .= ' AND id <> ?'; $params[] = $excludeId; }
        return (int)$this->scalar($sql, $params) > 0;
    }

    protected function slugify(string $s): string
    {
        $s = strtolower(trim($s));
        $s = str_replace(['é','è','ê','ë','à','â','ä','ô','ö','û','ü','î','ï','ç'],
                         ['e','e','e','e','a','a','a','o','o','u','u','i','i','c'], $s);
        $s = preg_replace('/[^a-z0-9]+/', '-', $s);
        return trim($s, '-') ?: 'item';
    }

    protected function handleImageUpload(?string $existing = null): ?string
    {
        $field = $this->imageField();
        if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
            return $existing;
        }
        $uploader = new ImageUploader($this->uploadDir(), $this->uploadWebPath());
        $results  = $uploader->uploadMany($_FILES[$field]);
        if (!empty($uploader->errors()) || empty($results)) return $existing;
        if ($existing) $uploader->delete($existing);
        return $results[0];
    }

    protected function findOrFail(int $id, bool $jsonError = false): array
    {
        $stmt = Database::conn()->prepare("SELECT * FROM `{$this->table()}` WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) {
            if ($jsonError) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => $this->singular() . ' not found.']);
                exit;
            }
            http_response_code(404);
            require APP_ROOT . '/src/Views/admin/404.php';
            exit;
        }
        return $row;
    }

    protected function scalar(string $sql, array $params): mixed
    {
        $stmt = Database::conn()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    protected function redirect(string $url): never
    {
        header('Location: ' . $url);
        exit;
    }
}
