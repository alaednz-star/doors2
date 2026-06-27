<?php

namespace App\Controllers\Admin;

use App\Auth\Authenticator;
use App\Auth\CsrfGuard;
use App\Core\Database;
use App\Core\Session;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\SecurityHeaders;
use App\Validators\QuoteValidator;

class QuoteController
{
    private const PER_PAGE = 20;

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

        $db      = Database::conn();
        $status  = $_GET['status'] ?? '';
        $search  = trim($_GET['q'] ?? '');
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $offset  = ($page - 1) * self::PER_PAGE;

        $where  = ['1=1'];
        $params = [];

        if ($status !== '' && in_array($status, QuoteValidator::statuses(), true)) {
            $where[]  = 'qr.status = ?';
            $params[] = $status;
        }

        if ($search !== '') {
            $like     = '%' . $search . '%';
            $where[]  = '(qr.customer_name LIKE ? OR qr.customer_phone LIKE ? OR qr.reference LIKE ? OR qr.customer_email LIKE ?)';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $whereClause = implode(' AND ', $where);

        $total = (int)$this->scalar(
            "SELECT COUNT(*) FROM quote_requests qr WHERE $whereClause",
            $params
        );

        $stmt = $db->prepare(
            "SELECT qr.*,
                    p.name  AS product_name,
                    m.name  AS material_name,
                    c.name  AS color_name,
                    dt.name AS door_type_name,
                    u.name  AS assigned_name
             FROM quote_requests qr
             LEFT JOIN products   p  ON p.id  = qr.product_id
             LEFT JOIN materials  m  ON m.id  = qr.material_id
             LEFT JOIN colors     c  ON c.id  = qr.color_id
             LEFT JOIN door_types dt ON dt.id = qr.door_type_id
             LEFT JOIN admin_users u ON u.id  = qr.assigned_to
             WHERE $whereClause
             ORDER BY qr.submitted_at DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->execute([...$params, self::PER_PAGE, $offset]);
        $quotes = $stmt->fetchAll();

        $counts     = $this->statusCounts();
        $totalPages = (int)ceil($total / self::PER_PAGE);
        $csrfToken  = CsrfGuard::token();
        $flash      = Session::getFlash('quote_success');
        $user       = $this->auth->user();
        $pageTitle  = 'Quote Requests';
        $currentPage= 'quotes';
        $view       = 'quotes/index';

        require APP_ROOT . '/src/Views/admin/layout.php';
    }

    public function show(int $id): void
    {
        AuthMiddleware::requireAuth();

        $quote     = $this->findOrFail($id);
        $log       = $this->statusLog($id);
        $selects   = $this->loadSelects();
        $features  = $this->resolveFeatures($quote['features_json']);
        $user      = $this->auth->user();
        $csrfToken = CsrfGuard::token();
        $flash     = Session::getFlash('quote_success');

        $allowed    = QuoteValidator::allowedTransitions($quote['status']);
        $pageTitle  = 'Quote ' . htmlspecialchars($quote['reference'], ENT_QUOTES, 'UTF-8');
        $currentPage= 'quotes';
        $view       = 'quotes/show';

        require APP_ROOT . '/src/Views/admin/layout.php';
    }

    public function edit(int $id): void
    {
        AuthMiddleware::requireAuth();

        $quote     = $this->findOrFail($id);
        $selects   = $this->loadSelects();
        $user      = $this->auth->user();
        $csrfToken = CsrfGuard::token();
        $errors    = Session::getFlash('form_errors', []);
        $old       = Session::getFlash('form_old', []);

        $pageTitle  = 'Edit Quote ' . htmlspecialchars($quote['reference'], ENT_QUOTES, 'UTF-8');
        $currentPage= 'quotes';
        $view       = 'quotes/edit';
        $formAction = '/door-showroom/admin/quotes/' . $id . '/update';

        require APP_ROOT . '/src/Views/admin/layout.php';
    }

    public function create(): void
    {
        AuthMiddleware::requireAuth();

        $selects   = $this->loadSelects();
        $user      = $this->auth->user();
        $csrfToken = CsrfGuard::token();
        $errors    = Session::getFlash('form_errors', []);
        $old       = Session::getFlash('form_old', []);
        $quote     = null;

        $pageTitle  = 'New Quote Request';
        $currentPage= 'quotes';
        $view       = 'quotes/edit';
        $formAction = '/door-showroom/admin/quotes/store';

        require APP_ROOT . '/src/Views/admin/layout.php';
    }

    public function store(): void
    {
        AuthMiddleware::requireAuth();
        CsrfMiddleware::verify();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/door-showroom/admin/quotes');
        }

        $input     = $this->postInput();
        $validator = new QuoteValidator();

        if (!$validator->validateCreate($input)) {
            Session::flash('form_errors', $validator->errors());
            Session::flash('form_old', $input);
            $this->redirect('/door-showroom/admin/quotes/create');
        }

        $d   = $validator->data();
        $ref = $this->generateReference();

        $db = Database::conn();
        $db->prepare(
            'INSERT INTO quote_requests
             (reference, customer_name, customer_phone, customer_email, customer_city, notes,
              product_id, material_id, color_id, door_type_id,
              width_mm, height_mm, handle, features_json, final_price, status)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
        )->execute([
            $ref,
            $d['customer_name'], $d['customer_phone'], $d['customer_email'],
            $d['customer_city'], $d['notes'],
            $d['product_id'], $d['material_id'],
            $d['color_id'], $d['door_type_id'],
            $d['width_mm'], $d['height_mm'], $d['handle'],
            $d['features_json'], $d['final_price'], 'new',
        ]);

        $newId = (int)$db->lastInsertId();
        $user  = $this->auth->user();

        $this->logStatusChange($newId, null, 'new', 'Quote created manually.', $user['id']);

        Session::flash('quote_success', 'Quote ' . $ref . ' created.');
        $this->redirect('/door-showroom/admin/quotes/' . $newId);
    }

    public function update(int $id): void
    {
        AuthMiddleware::requireAuth();
        CsrfMiddleware::verify();

        $this->findOrFail($id);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/door-showroom/admin/quotes');
        }

        $input     = $this->postInput();
        $validator = new QuoteValidator();

        if (!$validator->validateUpdate($input)) {
            Session::flash('form_errors', $validator->errors());
            Session::flash('form_old', $input);
            $this->redirect('/door-showroom/admin/quotes/' . $id . '/edit');
        }

        $d = $validator->data();

        Database::conn()->prepare(
            'UPDATE quote_requests
             SET customer_name=?, customer_phone=?, customer_email=?, customer_city=?, notes=?,
                 product_id=?, material_id=?, color_id=?, door_type_id=?,
                 width_mm=?, height_mm=?, handle=?, features_json=?, final_price=?
             WHERE id=?'
        )->execute([
            $d['customer_name'], $d['customer_phone'], $d['customer_email'],
            $d['customer_city'], $d['notes'],
            $d['product_id'], $d['material_id'],
            $d['color_id'], $d['door_type_id'],
            $d['width_mm'], $d['height_mm'], $d['handle'],
            $d['features_json'], $d['final_price'],
            $id,
        ]);

        Session::flash('quote_success', 'Quote updated successfully.');
        $this->redirect('/door-showroom/admin/quotes/' . $id);
    }

    public function updateStatus(int $id): void
    {
        header('Content-Type: application/json');
        AuthMiddleware::requireAuth();
        CsrfMiddleware::verify();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
            return;
        }

        $quote = $this->findOrFail($id, true);

        $raw   = file_get_contents('php://input');
        $body  = json_decode($raw, true) ?? [];
        if (empty($body)) {
            $body = $_POST;
        }

        $validator = new QuoteValidator();
        if (!$validator->validateStatusChange($body)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'errors' => $validator->errors()]);
            return;
        }

        $d           = $validator->data();
        $newStatus   = $d['status'];
        $fromStatus  = $quote['status'];
        $allowed     = QuoteValidator::allowedTransitions($fromStatus);

        if (!in_array($newStatus, $allowed, true)) {
            http_response_code(422);
            echo json_encode([
                'success' => false,
                'message' => 'Transition from "' . $fromStatus . '" to "' . $newStatus . '" is not allowed.',
            ]);
            return;
        }

        $user = $this->auth->user();

        Database::conn()->prepare(
            'UPDATE quote_requests SET status=?, status_notes=? WHERE id=?'
        )->execute([$newStatus, $d['status_notes'], $id]);

        $this->logStatusChange($id, $fromStatus, $newStatus, $d['status_notes'], $user['id']);

        echo json_encode([
            'success'      => true,
            'status'       => $newStatus,
            'status_label' => QuoteValidator::statusLabel($newStatus),
            'allowed_next' => QuoteValidator::allowedTransitions($newStatus),
        ]);
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

        $quote = $this->findOrFail($id, true);

        Database::conn()->prepare('DELETE FROM quote_requests WHERE id=?')->execute([$id]);

        echo json_encode(['success' => true, 'message' => 'Quote ' . $quote['reference'] . ' deleted.']);
    }

    private function statusCounts(): array
    {
        $rows = Database::conn()
            ->query("SELECT status, COUNT(*) AS cnt FROM quote_requests GROUP BY status")
            ->fetchAll();

        $counts = array_fill_keys(QuoteValidator::statuses(), 0);
        foreach ($rows as $r) {
            $counts[$r['status']] = (int)$r['cnt'];
        }
        $counts['_total'] = array_sum($counts);
        return $counts;
    }

    private function statusLog(int $quoteId): array
    {
        $stmt = Database::conn()->prepare(
            'SELECT l.*, u.name AS changed_by_name
             FROM quote_status_log l
             LEFT JOIN admin_users u ON u.id = l.changed_by
             WHERE l.quote_id = ?
             ORDER BY l.changed_at DESC'
        );
        $stmt->execute([$quoteId]);
        return $stmt->fetchAll();
    }

    private function logStatusChange(int $quoteId, ?string $from, string $to, ?string $notes, ?int $userId): void
    {
        Database::conn()->prepare(
            'INSERT INTO quote_status_log (quote_id, from_status, to_status, notes, changed_by)
             VALUES (?, ?, ?, ?, ?)'
        )->execute([$quoteId, $from, $to, $notes, $userId]);
    }

    private function loadSelects(): array
    {
        $db = Database::conn();
        return [
            'products'  => $db->query('SELECT id, name FROM products   WHERE is_active=1 ORDER BY name')->fetchAll(),
            'materials' => $db->query('SELECT id, name FROM materials  WHERE is_active=1 ORDER BY display_order')->fetchAll(),
            'colors'    => $db->query('SELECT id, name, hex FROM colors WHERE is_active=1 ORDER BY display_order')->fetchAll(),
            'doorTypes' => $db->query('SELECT id, name FROM door_types WHERE is_active=1 ORDER BY display_order')->fetchAll(),
            'features'  => $db->query('SELECT id, name, price, price_type FROM optional_features WHERE is_active=1 ORDER BY display_order')->fetchAll(),
        ];
    }

    private function resolveFeatures(?string $json): array
    {
        if (!$json) return [];
        $ids = json_decode($json, true);
        if (empty($ids)) return [];

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = Database::conn()->prepare(
            "SELECT id, name, price, price_type FROM optional_features WHERE id IN ($placeholders)"
        );
        $stmt->execute($ids);
        return $stmt->fetchAll();
    }

    private function generateReference(): string
    {
        $prefix = 'QR-' . date('Ym') . '-';
        $last   = $this->scalar(
            "SELECT reference FROM quote_requests WHERE reference LIKE ? ORDER BY id DESC LIMIT 1",
            [$prefix . '%']
        );

        $next = $last ? ((int)substr($last, strrpos($last, '-') + 1) + 1) : 1;
        return $prefix . str_pad((string)$next, 4, '0', STR_PAD_LEFT);
    }

    private function findOrFail(int $id, bool $jsonError = false): array
    {
        $stmt = Database::conn()->prepare(
            'SELECT qr.*,
                    p.name  AS product_name,
                    col.name AS collection_name,
                    m.name  AS material_name,
                    c.name  AS color_name,
                    c.hex   AS color_hex,
                    dt.name AS door_type_name,
                    rt.name AS room_type_name
             FROM quote_requests qr
             LEFT JOIN products    p   ON p.id   = qr.product_id
             LEFT JOIN collections col ON col.id = qr.collection_id
             LEFT JOIN materials   m   ON m.id   = qr.material_id
             LEFT JOIN colors      c   ON c.id   = qr.color_id
             LEFT JOIN door_types  dt  ON dt.id  = qr.door_type_id
             LEFT JOIN room_types  rt  ON rt.id  = qr.room_type_id
             WHERE qr.id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if (!$row) {
            if ($jsonError) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Quote not found.']);
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
            'customer_name'  => $_POST['customer_name']  ?? '',
            'customer_phone' => $_POST['customer_phone'] ?? '',
            'customer_email' => $_POST['customer_email'] ?? '',
            'customer_city'  => $_POST['customer_city']  ?? '',
            'notes'          => $_POST['notes']          ?? '',
            'product_id'     => $_POST['product_id']     ?? '',
            'material_id'    => $_POST['material_id']    ?? '',
            'color_id'       => $_POST['color_id']       ?? '',
            'door_type_id'   => $_POST['door_type_id']   ?? '',
            'width_mm'       => $_POST['width_mm']       ?? '',
            'height_mm'      => $_POST['height_mm']      ?? '',
            'handle'         => $_POST['handle']         ?? '',
            'feature_ids'    => $_POST['feature_ids']    ?? [],
            'final_price'    => $_POST['final_price']    ?? '',
        ];
    }

    private function redirect(string $url): never
    {
        header('Location: ' . $url);
        exit;
    }
}
