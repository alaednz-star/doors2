<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Lightweight i18n for the public site.
 *
 * Language is resolved once per request in this order:
 *   1. ?lang=xx query param (also sets the persistence cookie)
 *   2. portes_lang cookie
 *   3. default language (French)
 *
 * Translation dictionaries live in /lang/{code}.php and return a nested array.
 * Lookups use dot keys: t('nav.collections'). Missing keys fall back to the
 * default language, then to the key itself, so the page never shows blanks.
 */
final class I18n
{
    public const DEFAULT = 'fr';
    public const COOKIE  = 'portes_lang';

    /** code => [native label, text direction] */
    public const LANGS = [
        'fr' => ['label' => 'FR', 'name' => 'Français', 'dir' => 'ltr'],
        'en' => ['label' => 'EN', 'name' => 'English',  'dir' => 'ltr'],
        'ar' => ['label' => 'AR', 'name' => 'العربية',  'dir' => 'rtl'],
    ];

    private static string $lang = self::DEFAULT;
    private static array $dict = [];
    private static array $fallback = [];
    private static bool $booted = false;

    /** Resolve the language and load dictionaries. Safe to call multiple times. */
    public static function boot(): void
    {
        if (self::$booted) {
            return;
        }

        $lang = self::detect();
        self::$lang = $lang;
        self::$dict = self::load($lang);
        self::$fallback = $lang === self::DEFAULT ? self::$dict : self::load(self::DEFAULT);
        self::$booted = true;
    }

    private static function detect(): string
    {
        $supported = array_keys(self::LANGS);

        // 1. explicit ?lang=xx — persist it for subsequent requests
        $q = isset($_GET['lang']) ? strtolower((string)$_GET['lang']) : '';
        if ($q !== '' && in_array($q, $supported, true)) {
            self::persist($q);
            return $q;
        }

        // 2. cookie
        $c = isset($_COOKIE[self::COOKIE]) ? strtolower((string)$_COOKIE[self::COOKIE]) : '';
        if ($c !== '' && in_array($c, $supported, true)) {
            return $c;
        }

        // 3. default
        return self::DEFAULT;
    }

    /** Write the persistence cookie (1 year) if headers are not yet sent. */
    public static function persist(string $lang): void
    {
        if (!isset(self::LANGS[$lang]) || headers_sent()) {
            return;
        }
        $_COOKIE[self::COOKIE] = $lang;
        setcookie(self::COOKIE, $lang, [
            'expires'  => time() + 31536000,
            'path'     => '/',
            'samesite' => 'Lax',
        ]);
    }

    private static function load(string $lang): array
    {
        $file = APP_ROOT . '/lang/' . $lang . '.php';
        if (is_file($file)) {
            $data = require $file;
            if (is_array($data)) {
                return $data;
            }
        }
        return [];
    }

    public static function lang(): string
    {
        self::boot();
        return self::$lang;
    }

    public static function dir(): string
    {
        self::boot();
        return self::LANGS[self::$lang]['dir'] ?? 'ltr';
    }

    public static function isRtl(): bool
    {
        return self::dir() === 'rtl';
    }

    /**
     * Translate a dot-keyed string. Optional :placeholder replacement via $args.
     * Returns the current language value, else the default-language value, else
     * the key itself.
     */
    public static function t(string $key, array $args = []): string
    {
        self::boot();

        $val = self::dig(self::$dict, $key);
        if ($val === null) {
            $val = self::dig(self::$fallback, $key);
        }
        if ($val === null) {
            $val = $key;
        }

        if ($args) {
            foreach ($args as $k => $v) {
                $val = str_replace(':' . $k, (string)$v, $val);
            }
        }
        return (string)$val;
    }

    /** Return a whole sub-tree (e.g. an array of step names) for the current language. */
    public static function group(string $key): array
    {
        self::boot();
        $val = self::dig(self::$dict, $key);
        if (!is_array($val)) {
            $val = self::dig(self::$fallback, $key);
        }
        return is_array($val) ? $val : [];
    }

    private static function dig(array $arr, string $key)
    {
        if (array_key_exists($key, $arr)) {
            return $arr[$key];
        }
        $node = $arr;
        foreach (explode('.', $key) as $part) {
            if (is_array($node) && array_key_exists($part, $node)) {
                $node = $node[$part];
            } else {
                return null;
            }
        }
        return $node;
    }
}
