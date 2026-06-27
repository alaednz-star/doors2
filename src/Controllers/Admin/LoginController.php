<?php

namespace App\Controllers\Admin;

use App\Auth\Authenticator;
use App\Auth\CsrfGuard;
use App\Auth\RateLimiter;
use App\Core\Database;
use App\Core\Session;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\SecurityHeaders;
use App\Validators\LoginValidator;

class LoginController
{
    private Authenticator $auth;
    private RateLimiter   $limiter;

    public function __construct()
    {
        Session::start();
        SecurityHeaders::apply();
        $this->auth    = new Authenticator();
        $this->limiter = new RateLimiter();
    }

    public function showLogin(): void
    {
        AuthMiddleware::requireGuest();
        $csrfToken = CsrfGuard::token();
        require dirname(__DIR__, 2) . '/Views/admin/login.php';
    }

    public function handleLogin(): void
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
            return;
        }

        CsrfMiddleware::verify();

        $ip  = $this->ip();
        $key = 'login:' . $ip;

        if ($this->limiter->isBlocked($key)) {
            $wait = (int) ceil($this->limiter->waitSeconds($key) / 60);
            http_response_code(429);
            echo json_encode([
                'success' => false,
                'message' => "Too many failed attempts. Try again in {$wait} minute(s).",
            ]);
            return;
        }

        $body      = (array) (json_decode(file_get_contents('php://input'), true) ?? []);
        $validator = new LoginValidator();

        if (!$validator->validate($body)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'errors' => $validator->errors()]);
            return;
        }

        $data   = $validator->data();
        $result = $this->auth->attempt($data['email'], $data['password'], $data['remember']);

        if (!$result['success']) {
            $this->limiter->hit($key);
            $this->log(null, 'login.failed', ['email' => $data['email']]);
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => $result['message']]);
            return;
        }

        $this->limiter->clear($key);
        CsrfGuard::rotate();
        $this->log($result['user']['id'], 'login.success');

        echo json_encode([
            'success'  => true,
            'redirect' => Session::getFlash('redirect_after_login', '/door-showroom/admin'),
            'csrf'     => CsrfGuard::token(),
        ]);
    }

    public function handleLogout(): void
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
            return;
        }

        CsrfMiddleware::verify();

        $user = $this->auth->user();
        if ($user) {
            $this->log($user['id'], 'logout');
        }

        $this->auth->logout();

        echo json_encode(['success' => true, 'redirect' => '/door-showroom/admin/login']);
    }

    public function csrfToken(): void
    {
        header('Content-Type: application/json');
        echo json_encode(['token' => CsrfGuard::token()]);
    }

    private function ip(): string
    {
        foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
            $val = $_SERVER[$key] ?? '';
            if ($val) {
                $ip = trim(explode(',', $val)[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
            }
        }
        return '0.0.0.0';
    }

    private function log(?int $userId, string $action, array $meta = []): void
    {
        try {
            Database::conn()
                ->prepare(
                    'INSERT INTO activity_log (admin_user_id, action, ip_address, user_agent, metadata)
                     VALUES (?, ?, ?, ?, ?)'
                )
                ->execute([
                    $userId,
                    $action,
                    $this->ip(),
                    substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 300),
                    $meta ? json_encode($meta) : null,
                ]);
        } catch (\Throwable $e) {
            error_log('[activity_log] ' . $e->getMessage());
        }
    }
}
