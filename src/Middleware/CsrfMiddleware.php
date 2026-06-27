<?php

namespace App\Middleware;

use App\Auth\CsrfGuard;

class CsrfMiddleware
{
    public static function verify(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            return;
        }

        if (!CsrfGuard::verify()) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid or expired request token.']);
            exit;
        }
    }
}
