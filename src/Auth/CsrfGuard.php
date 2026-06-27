<?php

namespace App\Auth;

use App\Core\Session;

class CsrfGuard
{
    private const KEY = '_csrf';

    public static function token(): string
    {
        if (!Session::has(self::KEY)) {
            Session::set(self::KEY, bin2hex(random_bytes(32)));
        }
        return Session::get(self::KEY);
    }

    public static function verify(): bool
    {
        $stored = Session::get(self::KEY, '');
        $sent   = self::sentToken();

        if (empty($stored) || empty($sent)) {
            return false;
        }

        return hash_equals($stored, $sent);
    }

    /**
     * Read the submitted token from every place a client may legitimately send it:
     *   1. X-CSRF-Token header     — AJAX / fetch requests
     *   2. $_POST['_csrf']         — standard HTML form posts (incl. multipart uploads)
     *   3. JSON body { "_csrf" }   — JSON fetch bodies
     */
    private static function sentToken(): string
    {
        if (!empty($_SERVER['HTTP_X_CSRF_TOKEN'])) {
            return (string) $_SERVER['HTTP_X_CSRF_TOKEN'];
        }

        if (!empty($_POST['_csrf'])) {
            return (string) $_POST['_csrf'];
        }

        // JSON body — only attempt to parse when the request is actually JSON, so a
        // multipart upload (where php://input is unavailable/binary) is never touched.
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (stripos($contentType, 'application/json') !== false) {
            $body = json_decode(file_get_contents('php://input') ?: '', true);
            if (is_array($body) && !empty($body['_csrf'])) {
                return (string) $body['_csrf'];
            }
        }

        return '';
    }

    public static function rotate(): void
    {
        Session::set(self::KEY, bin2hex(random_bytes(32)));
    }
}
