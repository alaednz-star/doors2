<?php

namespace App\Controllers\Admin;

use App\Auth\Authenticator;
use App\Auth\CsrfGuard;
use App\Core\Database;
use App\Core\Session;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\SecurityHeaders;

/**
 * Pricing matrix admin.
 *
 * A rule = (Collection × Door Usage × Construction Type) → Base Price + Available.
 * Base price is the price at the reference door size; dimensions scale it in the
 * engine. The combination is unique, so the same cell is never duplicated.
 */
class PricingController
{
    private const PER_PAGE = 50;

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
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($page - 1) * self::PER_PAGE;

        $where = ['1=1']; $params = [];
        if ($search !== '') {
            $where[] = '(col.name LIKE ? OR dt.name LIKE ? OR ct.name LIKE ?)';
            $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%";
        }
        $clause = implode(' AND ', $where);

        $total = (int)$this->scalar(
            "SELECT COUNT(*) FROM price_rules r
             LEFT JOIN collections col ON col.id=r.collection_id
             LEFT JOIN door_types dt ON dt.id=r.door_type_id
             LEFT JOIN construction_types ct ON ct.id=r.construction_type_id
             WHERE $clause", $params
        );

        $stmt = $db->prepare(
            "SELECT r.*, col.name AS collection_name, dt.name AS usage_name, ct.name AS construction_name
             FROM price_rules r
             LEFT JOIN collections col ON col.id=r.collection_id
             LEFT JOIN door_types dt ON dt.id=r.door_type_id
             LEFT JOIN construction_types ct ON ct.id=r.construction_type_id
             WHERE $clause
             ORDER BY col.display_order, dt.display_order, ct.display_order, r.id
             LIMIT ? OFFSET ?"
        );
        $stmt->execute([...$params, self::PER_PAGE, $offset]);
        $rules = $stmt->fetchAll();

        $totalPages  = max(1, (int)ceil($total / self::PER_PAGE));
        $csrfToken   = CsrfGuard::token();
        $flash       = Session::getFlash('pricing_success');
        $user        = $this->auth->user();
        $pageTitle   = 'Pricing Matrix';
        $currentPage = 'pricing';
        $view        = 'pricing/index';

        require APP_ROOT . '/src/Views/admin/layout.php';
    }

    public function create(): void
    {
        AuthMiddleware::requireAuth();

        $selects     = $this->loadSelects();
        $rule        = null;
        $user        = $this->auth->user();
        $csrfToken   = CsrfGuard::token();
        $errors      = Session::getFlash('form_errors', []);
        $old         = Session::getFlash('form_old', []);
        $pageTitle   = 'Add Price Rule';
        $currentPage = 'pricing';
        $view        = 'pricing/form';
        $formAction  = '/door-showroom/admin/pricing/store';

        require APP_ROOT . '/src/Views/admin/layout.php';
    }

    public function store(): void
    {
        AuthMiddleware::requireAuth();
        CsrfMiddleware::verify();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->redirect('/door-showroom/admin/pricing');

        [$ok, $errors, $d] = $this->validate();
        if (!$ok) {
            Session::flash('form_errors', $errors);
            Session::flash('form_old', $_POST);
            $this->redirect('/door-showroom/admin/pricing/create');
        }

        $user = $this->auth->user();
        Database::conn()->prepare(
            'INSERT INTO price_rules
             (name, dimension_type, collection_id, door_type_id, construction_type_id,
              base_price, priority, is_active, is_available, created_by)
             VALUES (?,?,?,?,?,?,?,?,?,?)'
        )->execute([
            $d['name'], 'reference_scaled', $d['collection_id'], $d['door_type_id'], $d['construction_type_id'],
            $d['base_price'], 100, $d['is_active'], $d['is_available'], $user['id'] ?? null,
        ]);

        Session::flash('pricing_success', 'Price rule "' . $d['name'] . '" created.');
        $this->redirect('/door-showroom/admin/pricing');
    }

    public function edit(int $id): void
    {
        AuthMiddleware::requireAuth();

        $rule        = $this->findOrFail($id);
        $selects     = $this->loadSelects();
        $user        = $this->auth->user();
        $csrfToken   = CsrfGuard::token();
        $errors      = Session::getFlash('form_errors', []);
        $old         = Session::getFlash('form_old', []);
        $pageTitle   = 'Edit Price Rule';
        $currentPage = 'pricing';
        $view        = 'pricing/form';
        $formAction  = '/door-showroom/admin/pricing/' . $id . '/update';

        require APP_ROOT . '/src/Views/admin/layout.php';
    }

    public function update(int $id): void
    {
        AuthMiddleware::requireAuth();
        CsrfMiddleware::verify();
        $this->findOrFail($id);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->redirect('/door-showroom/admin/pricing');

        [$ok, $errors, $d] = $this->validate($id);
        if (!$ok) {
            Session::flash('form_errors', $errors);
            Session::flash('form_old', $_POST);
            $this->redirect('/door-showroom/admin/pricing/' . $id . '/edit');
        }

        Database::conn()->prepare(
            'UPDATE price_rules
             SET name=?, collection_id=?, door_type_id=?, construction_type_id=?,
                 base_price=?, is_active=?, is_available=?
             WHERE id=?'
        )->execute([
            $d['name'], $d['collection_id'], $d['door_type_id'], $d['construction_type_id'],
            $d['base_price'], $d['is_active'], $d['is_available'], $id,
        ]);

        Session::flash('pricing_success', 'Price rule "' . $d['name'] . '" updated.');
        $this->redirect('/door-showroom/admin/pricing');
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
        $this->findOrFail($id, true);
        Database::conn()->prepare('DELETE FROM price_rules WHERE id = ?')->execute([$id]);
        echo json_encode(['success' => true, 'message' => 'Price rule deleted.']);
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
        $rule = $this->findOrFail($id, true);
        $new  = $rule['is_active'] ? 0 : 1;
        Database::conn()->prepare('UPDATE price_rules SET is_active = ? WHERE id = ?')->execute([$new, $id]);
        echo json_encode(['success' => true, 'is_active' => $new, 'label' => $new ? 'Active' : 'Inactive']);
    }

    /** Toggle availability of a combination (the "Non disponible" flag). */
    public function toggleAvailable(int $id): void
    {
        header('Content-Type: application/json');
        AuthMiddleware::requireAuth();
        CsrfMiddleware::verify();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
            return;
        }
        $rule = $this->findOrFail($id, true);
        $new  = $rule['is_available'] ? 0 : 1;
        Database::conn()->prepare('UPDATE price_rules SET is_available = ? WHERE id = ?')->execute([$new, $id]);
        echo json_encode(['success' => true, 'is_available' => $new, 'label' => $new ? 'Available' : 'Non disponible']);
    }

    private function validate(?int $excludeId = null): array
    {
        $errors = [];
        $collectionId   = (int)($_POST['collection_id'] ?? 0) ?: null;
        $doorTypeId     = (int)($_POST['door_type_id'] ?? 0) ?: null;
        $constructionId = (int)($_POST['construction_type_id'] ?? 0) ?: null;
        $basePrice      = $_POST['base_price'] ?? '';
        $isAvailable    = isset($_POST['is_available']) ? 1 : 0;
        $isActive       = isset($_POST['is_active']) ? 1 : 0;

        if (!$collectionId)   $errors['collection_id'] = 'Choose a collection.';
        if (!$doorTypeId)     $errors['door_type_id'] = 'Choose a door usage.';
        if (!$constructionId) $errors['construction_type_id'] = 'Choose a construction type.';

        // base price required only when the combination IS available
        if ($isAvailable) {
            if ($basePrice === '' || !is_numeric($basePrice) || (float)$basePrice < 0) {
                $errors['base_price'] = 'Enter a valid base price (DZD).';
            }
        }

        // uniqueness of the combination
        if ($collectionId && $doorTypeId && $constructionId) {
            $sql = 'SELECT COUNT(*) FROM price_rules
                    WHERE collection_id=? AND door_type_id=? AND construction_type_id=?';
            $p = [$collectionId, $doorTypeId, $constructionId];
            if ($excludeId) { $sql .= ' AND id<>?'; $p[] = $excludeId; }
            if ((int)$this->scalar($sql, $p) > 0) {
                $errors['collection_id'] = 'A rule for this exact combination already exists.';
            }
        }

        if ($errors) return [false, $errors, []];

        // derive a readable name
        $name = $this->comboName($collectionId, $doorTypeId, $constructionId);

        return [true, [], [
            'name'                 => $name,
            'collection_id'        => $collectionId,
            'door_type_id'         => $doorTypeId,
            'construction_type_id' => $constructionId,
            'base_price'           => $isAvailable ? (float)$basePrice : 0,
            'is_available'         => $isAvailable,
            'is_active'            => $isActive,
        ]];
    }

    private function comboName(?int $coll, ?int $usage, ?int $constr): string
    {
        $col = (string)$this->scalar('SELECT name FROM collections WHERE id=?', [$coll]);
        $u   = (string)$this->scalar('SELECT name FROM door_types WHERE id=?', [$usage]);
        $c   = (string)$this->scalar('SELECT name FROM construction_types WHERE id=?', [$constr]);
        return trim("$col · $u · $c", ' ·');
    }

    private function loadSelects(): array
    {
        $db = Database::conn();
        return [
            'collections'  => $db->query('SELECT id, name FROM collections WHERE is_active=1 ORDER BY display_order, name')->fetchAll(),
            'usages'       => $db->query('SELECT id, name FROM door_types WHERE is_active=1 ORDER BY display_order, name')->fetchAll(),
            'constructions'=> $db->query('SELECT id, name FROM construction_types WHERE is_active=1 ORDER BY display_order, name')->fetchAll(),
        ];
    }

    private function findOrFail(int $id, bool $jsonError = false): array
    {
        $stmt = Database::conn()->prepare('SELECT * FROM price_rules WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) {
            if ($jsonError) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Price rule not found.']);
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

    private function redirect(string $url): never
    {
        header('Location: ' . $url);
        exit;
    }
}
