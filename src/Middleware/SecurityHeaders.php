<?php

namespace App\Middleware;

class SecurityHeaders
{
    public static function apply(): void
    {
        header('X-Frame-Options: DENY');
        header('X-Content-Type-Options: nosniff');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
        // 'unsafe-inline' is required because the admin and public views use
        // inline <script> blocks for page-specific behaviour (delete/toggle
        // modals, form helpers, the configurator). Without it the browser's CSP
        // blocks those scripts and buttons silently stop working.
        header(
            "Content-Security-Policy: " .
            "default-src 'self'; " .
            "script-src 'self' 'unsafe-inline'; " .
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; " .
            "font-src 'self' https://fonts.gstatic.com; " .
            "img-src 'self' data:; " .
            "connect-src 'self'; " .
            "frame-ancestors 'none';"
        );

        self::noCacheHtml();
    }

    /**
     * Stop browsers caching dynamic HTML pages, so catalogue/admin changes are
     * always reflected on a normal refresh. Static assets (CSS/JS/images) are
     * NOT affected — those are cached hard and busted via ?v= version strings.
     */
    public static function noCacheHtml(): void
    {
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
    }
}
