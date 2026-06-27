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
