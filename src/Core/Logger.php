<?php

namespace App\Core;

/**
 * Minimal append-only logger. Used to capture failures that must never be lost
 * silently (e.g. a quote that could not be persisted). Writes to storage/logs/
 * and degrades to PHP's error_log if the directory is not writable.
 */
class Logger
{
    private const DIR = APP_ROOT . '/storage/logs';

    public static function error(string $channel, string $message, array $context = []): void
    {
        self::write('ERROR', $channel, $message, $context);
    }

    public static function warning(string $channel, string $message, array $context = []): void
    {
        self::write('WARN', $channel, $message, $context);
    }

    public static function info(string $channel, string $message, array $context = []): void
    {
        self::write('INFO', $channel, $message, $context);
    }

    private static function write(string $level, string $channel, string $message, array $context): void
    {
        $line = sprintf(
            "[%s] %s.%s: %s%s\n",
            date('Y-m-d H:i:s'),
            $channel,
            $level,
            $message,
            $context ? ' ' . json_encode($context, JSON_UNESCAPED_SLASHES) : ''
        );

        if (!is_dir(self::DIR)) {
            @mkdir(self::DIR, 0775, true);
        }

        $file = self::DIR . '/' . date('Y-m-d') . '.log';
        if (@file_put_contents($file, $line, FILE_APPEND | LOCK_EX) === false) {
            // last resort — never lose the signal
            error_log('PORTES ' . trim($line));
        }
    }
}
