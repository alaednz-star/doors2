<?php

namespace App\Controllers\Admin;

use App\Auth\Authenticator;
use App\Auth\CsrfGuard;
use App\Core\Database;
use App\Core\Session;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\SecurityHeaders;

class SettingsController
{
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

        $db   = Database::conn();
        $user = $this->auth->user();

        $rows = $db->query(
            'SELECT setting_key, setting_value, label, group_name FROM settings ORDER BY group_name, id'
        )->fetchAll();

        $settings = [];
        $groups   = [];
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
            $groups[$row['group_name']][]  = $row;
        }

        $csrfToken   = CsrfGuard::token();
        $flash       = Session::getFlash('settings_success');
        $flashError  = Session::getFlash('settings_error');
        $pageTitle   = 'Settings';
        $currentPage = 'settings';
        $view        = 'settings/index';

        require APP_ROOT . '/src/Views/admin/layout.php';
    }

    public function update(): void
    {
        AuthMiddleware::requireAuth();
        CsrfMiddleware::verify();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/door-showroom/admin/settings');
        }

        $db      = Database::conn();
        $allowed = $db->query('SELECT setting_key FROM settings')->fetchAll(\PDO::FETCH_COLUMN);

        $stmt = $db->prepare(
            'UPDATE settings SET setting_value = ? WHERE setting_key = ?'
        );

        foreach ($allowed as $key) {
            $value = isset($_POST[$key]) ? trim($_POST[$key]) : '';
            if ($key === 'maintenance_mode' || $key === 'quote_email_notify') {
                $value = isset($_POST[$key]) ? '1' : '0';
            }
            $stmt->execute([$value, $key]);
        }

        Session::flash('settings_success', 'Settings saved successfully.');
        $this->redirect('/door-showroom/admin/settings');
    }

    private function redirect(string $url): never
    {
        header('Location: ' . $url);
        exit;
    }
}
