<?php

namespace App\Middleware;

use App\Auth\Authenticator;
use App\Core\Session;

class AuthMiddleware
{
    public static function requireAuth(): void
    {
        Session::start();
        $auth = new Authenticator();

        if (!$auth->check()) {
            Session::flash('redirect_after_login', $_SERVER['REQUEST_URI'] ?? '/door-showroom/admin');
            header('Location: /door-showroom/admin/login');
            exit;
        }
    }

    public static function requireGuest(): void
    {
        Session::start();
        $auth = new Authenticator();

        if ($auth->check()) {
            header('Location: /door-showroom/admin');
            exit;
        }
    }

    public static function requireRole(string ...$roles): void
    {
        self::requireAuth();
        $user = (new Authenticator())->user();

        if (!in_array($user['role'] ?? '', $roles, true)) {
            http_response_code(403);
            require dirname(__DIR__) . '/Views/admin/403.php';
            exit;
        }
    }
}
