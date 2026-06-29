<?php

declare(strict_types=1);

use App\Core\I18n;

if (!function_exists('t')) {
    /** Translate a dot-keyed string (current public-site language). */
    function t(string $key, array $args = []): string
    {
        return I18n::t($key, $args);
    }
}

if (!function_exists('te')) {
    /** Translate + HTML-escape — for use inside HTML text/attributes. */
    function te(string $key, array $args = []): string
    {
        return htmlspecialchars(I18n::t($key, $args), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('current_lang')) {
    function current_lang(): string
    {
        return I18n::lang();
    }
}

if (!function_exists('asset')) {
    /**
     * Versioned asset URL for cache-busting. Appends ?v=<file-mtime> so the
     * browser fetches a fresh copy only when the file actually changes, while
     * caching aggressively the rest of the time.
     *
     *   asset('/assets/css/home.css')  →  /door-showroom/assets/css/home.css?v=1719600000
     */
    function asset(string $path): string
    {
        $path = '/' . ltrim($path, '/');
        $full = APP_ROOT . '/public' . $path;
        $ver  = is_file($full) ? filemtime($full) : null;
        return '/door-showroom' . $path . ($ver ? '?v=' . $ver : '');
    }
}

if (!function_exists('contact_info')) {
    /**
     * Admin-editable contact details (email / phone / WhatsApp / address) read
     * from the settings table, with safe fallbacks. Loaded once per request.
     * Returns: ['email','phone','whatsapp','whatsapp_url','tel_href','address'].
     */
    function contact_info(): array
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }

        $vals = [
            'contact_email'    => '',
            'contact_phone'    => '',
            'contact_whatsapp' => '',
            'contact_address'  => '',
        ];
        try {
            $stmt = \App\Core\Database::conn()->prepare(
                "SELECT setting_key, setting_value FROM settings
                 WHERE setting_key IN ('contact_email','contact_phone','contact_whatsapp','contact_address')"
            );
            $stmt->execute();
            foreach ($stmt->fetchAll(\PDO::FETCH_KEY_PAIR) as $k => $v) {
                if ($v !== '' && $v !== null) {
                    $vals[$k] = $v;
                }
            }
        } catch (\Throwable $e) {
            // settings table missing → fall back to defaults below.
        }

        $email    = $vals['contact_email']    ?: 'contact@portes.dz';
        $phone    = $vals['contact_phone']    ?: '';
        $whatsapp = preg_replace('/\D+/', '', $vals['contact_whatsapp'] ?? '');

        $cache = [
            'email'        => $email,
            'phone'        => $phone,
            'whatsapp'     => $whatsapp,
            'whatsapp_url' => $whatsapp !== '' ? 'https://wa.me/' . $whatsapp : '',
            'tel_href'     => $phone !== '' ? 'tel:' . preg_replace('/[^0-9+]/', '', $phone) : '',
            'address'      => $vals['contact_address'] ?? '',
        ];
        return $cache;
    }
}
