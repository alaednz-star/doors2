<?php

namespace App\Core;

class Config
{
    private static array $cache = [];

    public static function get(string $file, string $key = null, mixed $default = null): mixed
    {
        if (!isset(self::$cache[$file])) {
            $path = dirname(__DIR__, 2) . '/config/' . $file . '.php';
            self::$cache[$file] = file_exists($path) ? require $path : [];
        }

        $data = self::$cache[$file];

        if ($key === null) {
            return $data;
        }

        return $data[$key] ?? $default;
    }
}
