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

class MediaController
{
    private const PER_PAGE   = 24;
    private const UPLOAD_DIR = APP_ROOT . '/public/uploads/media';
    private const WEB_PATH   = '/door-showroom/uploads/media';

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
        $search = trim($_GET['q']    ?? '');
        $type   = $_GET['type']      ?? '';
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($page - 1) * self::PER_PAGE;

        $where  = ['1=1'];
        $params = [];

        if ($search !== '') {
            $like     = '%' . $search . '%';
            $where[]  = '(m.original_name LIKE ? OR m.alt_text LIKE ?)';
            $params[] = $like;
            $params[] = $like;
        }

        if (in_array($type, ['product', 'collection', 'color'])) {
            $where[]  = 'm.entity_type = ?';
            $params[] = $type;
        } elseif ($type === 'unassigned') {
            $where[] = 'm.entity_type IS NULL';
        }

        $clause = implode(' AND ', $where);

        $total = (int)$this->scalar("SELECT COUNT(*) FROM media m WHERE $clause", $params);

        $stmt = $db->prepare(
            "SELECT m.* FROM media m WHERE $clause ORDER BY m.created_at DESC LIMIT ? OFFSET ?"
        );
        $stmt->execute([...$params, self::PER_PAGE, $offset]);
        $items = $stmt->fetchAll();

        $totalPages  = (int)ceil($total / self::PER_PAGE);
        $csrfToken   = CsrfGuard::token();
        $flash       = Session::getFlash('media_success');
        $user        = $this->auth->user();
        $pageTitle   = 'Media Library';
        $currentPage = 'media';
        $view        = 'media/index';

        require APP_ROOT . '/src/Views/admin/layout.php';
    }

    public function upload(): void
    {
        AuthMiddleware::requireAuth();

        $user        = $this->auth->user();
        $csrfToken   = CsrfGuard::token();
        $entities    = $this->loadEntities();
        $pageTitle   = 'Upload Media';
        $currentPage = 'media';
        $view        = 'media/upload';

        require APP_ROOT . '/src/Views/admin/layout.php';
    }

    public function store(): void
    {
        header('Content-Type: application/json');
        AuthMiddleware::requireAuth();
        CsrfMiddleware::verify();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
            return;
        }

        if (empty($_FILES['files'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'No files received.']);
            return;
        }

        $entityType = $_POST['entity_type'] ?? null;
        $entityId   = !empty($_POST['entity_id']) ? (int)$_POST['entity_id'] : null;

        if (!in_array($entityType, ['product', 'collection', 'color'], true)) {
            $entityType = null;
            $entityId   = null;
        }

        $uploader = new ImageUploader(self::UPLOAD_DIR, self::WEB_PATH);
        $uploaded = $uploader->uploadMany($_FILES['files']);

        $errors   = $uploader->errors();
        $created  = [];
        $db       = Database::conn();
        $userId   = $this->auth->user()['id'] ?? null;

        foreach ($uploaded as $filename) {
            $path = self::UPLOAD_DIR . '/' . $filename;
            $size = is_file($path) ? filesize($path) : 0;
            $mime = is_file($path) ? (new \finfo(FILEINFO_MIME_TYPE))->file($path) : '';

            $width  = null;
            $height = null;
            if (function_exists('getimagesize') && is_file($path)) {
                $info = @getimagesize($path);
                if ($info) { $width = $info[0]; $height = $info[1]; }
            }

            $db->prepare(
                'INSERT INTO media (filename, original_name, mime_type, file_size, width, height, entity_type, entity_id, uploaded_by)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
            )->execute([$filename, $filename, $mime, $size, $width, $height, $entityType, $entityId, $userId]);

            $id      = (int)$db->lastInsertId();
            $created[] = [
                'id'       => $id,
                'filename' => $filename,
                'url'      => self::WEB_PATH . '/' . $filename,
                'width'    => $width,
                'height'   => $height,
                'size'     => $size,
            ];
        }

        echo json_encode([
            'success' => true,
            'created' => $created,
            'errors'  => array_values($errors),
        ]);
    }

    public function storeForm(): void
    {
        AuthMiddleware::requireAuth();
        CsrfMiddleware::verify();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/door-showroom/admin/media');
        }

        if (empty($_FILES['files'])) {
            Session::flash('media_error', 'No files selected.');
            $this->redirect('/door-showroom/admin/media/upload');
        }

        $entityType = $_POST['entity_type'] ?? null;
        $entityId   = !empty($_POST['entity_id']) ? (int)$_POST['entity_id'] : null;

        if (!in_array($entityType, ['product', 'collection', 'color'], true)) {
            $entityType = null;
            $entityId   = null;
        }

        $uploader = new ImageUploader(self::UPLOAD_DIR, self::WEB_PATH);
        $uploaded = $uploader->uploadMany($_FILES['files']);
        $errors   = $uploader->errors();

        $db     = Database::conn();
        $userId = $this->auth->user()['id'] ?? null;
        $count  = 0;

        foreach ($uploaded as $i => $filename) {
            $originalName = '';
            if (isset($_FILES['files']['name'])) {
                $names = $_FILES['files']['name'];
                $originalName = is_array($names) ? ($names[$i] ?? $filename) : $names;
            }

            $path = self::UPLOAD_DIR . '/' . $filename;
            $size = is_file($path) ? filesize($path) : 0;
            $mime = is_file($path) ? (new \finfo(FILEINFO_MIME_TYPE))->file($path) : '';

            $width  = null;
            $height = null;
            if (function_exists('getimagesize') && is_file($path)) {
                $info = @getimagesize($path);
                if ($info) { $width = $info[0]; $height = $info[1]; }
            }

            $db->prepare(
                'INSERT INTO media (filename, original_name, mime_type, file_size, width, height, entity_type, entity_id, uploaded_by)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
            )->execute([$filename, $originalName, $mime, $size, $width, $height, $entityType, $entityId, $userId]);

            $count++;
        }

        if ($count > 0) {
            Session::flash('media_success', $count . ' ' . ($count === 1 ? 'file' : 'files') . ' uploaded successfully.');
        }
        if (!empty($errors)) {
            Session::flash('media_error', implode(' ', $errors));
        }

        $this->redirect('/door-showroom/admin/media');
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

        $item = $this->findOrFail($id, true);

        (new ImageUploader(self::UPLOAD_DIR, self::WEB_PATH))->delete($item['filename']);
        Database::conn()->prepare('DELETE FROM media WHERE id = ?')->execute([$id]);

        echo json_encode(['success' => true]);
    }

    public function assign(int $id): void
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

        $body       = json_decode(file_get_contents('php://input'), true) ?? [];
        $entityType = $body['entity_type'] ?? null;
        $entityId   = !empty($body['entity_id']) ? (int)$body['entity_id'] : null;

        if (!in_array($entityType, ['product', 'collection', 'color'], true)) {
            $entityType = null;
            $entityId   = null;
        }

        Database::conn()->prepare(
            'UPDATE media SET entity_type = ?, entity_id = ? WHERE id = ?'
        )->execute([$entityType, $entityId, $id]);

        echo json_encode(['success' => true, 'entity_type' => $entityType, 'entity_id' => $entityId]);
    }

    public function updateAlt(int $id): void
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

        $body    = json_decode(file_get_contents('php://input'), true) ?? [];
        $altText = isset($body['alt_text']) ? substr(trim($body['alt_text']), 0, 500) : null;

        Database::conn()->prepare(
            'UPDATE media SET alt_text = ? WHERE id = ?'
        )->execute([$altText ?: null, $id]);

        echo json_encode(['success' => true, 'alt_text' => $altText]);
    }

    public function preview(int $id): void
    {
        header('Content-Type: application/json');
        AuthMiddleware::requireAuth();

        $item = $this->findOrFail($id, true);

        $item['url'] = self::WEB_PATH . '/' . $item['filename'];
        $item['size_formatted'] = $this->formatBytes((int)$item['file_size']);

        echo json_encode(['success' => true, 'media' => $item]);
    }

    private function loadEntities(): array
    {
        $db = Database::conn();

        $tables = array_column($db->query("SHOW TABLES")->fetchAll(\PDO::FETCH_NUM), 0);

        return [
            'products'    => in_array('products', $tables)
                ? $db->query("SELECT id, name FROM products ORDER BY name")->fetchAll()
                : [],
            'collections' => in_array('collections', $tables)
                ? $db->query("SELECT id, name FROM collections ORDER BY name")->fetchAll()
                : [],
            'colors'      => in_array('colors', $tables)
                ? $db->query("SELECT id, name FROM colors ORDER BY name")->fetchAll()
                : [],
        ];
    }

    private function findOrFail(int $id, bool $jsonError = false): array
    {
        $stmt = Database::conn()->prepare('SELECT * FROM media WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if (!$row) {
            if ($jsonError) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Media not found.']);
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

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
        if ($bytes >= 1024)    return round($bytes / 1024, 1) . ' KB';
        return $bytes . ' B';
    }

    private function redirect(string $url): never
    {
        header('Location: ' . $url);
        exit;
    }
}
