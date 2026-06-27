<?php

namespace App\Controllers\Admin;

use App\Auth\Authenticator;
use App\Auth\CsrfGuard;
use App\Core\Database;
use App\Middleware\AuthMiddleware;
use App\Middleware\SecurityHeaders;

class DashboardController
{
    private Authenticator $auth;

    public function __construct()
    {
        \App\Core\Session::start();
        SecurityHeaders::apply();
        $this->auth = new Authenticator();
    }

    public function index(): void
    {
        AuthMiddleware::requireAuth();

        $user        = $this->auth->user();
        $csrfToken   = CsrfGuard::token();
        $stats       = $this->stats();
        $recent      = $this->recentQuotes();
        $pageTitle   = 'Dashboard';
        $currentPage = 'dashboard';
        $view        = 'dashboard';

        require APP_ROOT . '/src/Views/admin/layout.php';
    }

    private function stats(): array
    {
        $db = Database::conn();

        $tables = array_column(
            $db->query("SHOW TABLES")->fetchAll(\PDO::FETCH_NUM),
            0
        );

        $hasQuotes   = in_array('quote_requests', $tables);
        $hasProducts = in_array('products', $tables);

        $quotes      = $hasQuotes
            ? (int) $db->query("SELECT COUNT(*) FROM quote_requests")->fetchColumn()
            : 0;
        $newQuotes   = $hasQuotes
            ? (int) $db->query("SELECT COUNT(*) FROM quote_requests WHERE status = 'new'")->fetchColumn()
            : 0;
        $products    = $hasProducts
            ? (int) $db->query("SELECT COUNT(*) FROM products WHERE is_active = 1")->fetchColumn()
            : 0;
        $categories  = in_array('categories', $tables)
            ? (int) $db->query("SELECT COUNT(*) FROM categories WHERE is_active = 1")->fetchColumn()
            : 0;
        $wonQuotes   = $hasQuotes
            ? (int) $db->query("SELECT COUNT(*) FROM quote_requests WHERE status = 'completed'")->fetchColumn()
            : 0;

        return [
            'total_quotes'    => $quotes,
            'new_quotes'      => $newQuotes,
            'active_products' => $products,
            'categories'      => $categories,
            'won_quotes'      => $wonQuotes,
            'conversion_rate' => $quotes > 0 ? round(($wonQuotes / $quotes) * 100, 1) : 0,
        ];
    }

    private function recentQuotes(): array
    {
        $db     = Database::conn();
        $tables = array_column(
            $db->query("SHOW TABLES")->fetchAll(\PDO::FETCH_NUM),
            0
        );

        if (!in_array('quote_requests', $tables)) {
            return [];
        }

        $stmt = $db->query(
            "SELECT id, reference, customer_name, customer_phone, status, submitted_at
             FROM quote_requests
             ORDER BY submitted_at DESC
             LIMIT 6"
        );
        return $stmt->fetchAll();
    }
}
