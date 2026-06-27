<?php

namespace App\Auth;

use App\Core\Database;
use App\Core\Config;

class RateLimiter
{
    private int $max;
    private int $window;
    private int $lockout;

    public function __construct()
    {
        $this->max     = (int) Config::get('security', 'rate_limit_max', 5);
        $this->window  = (int) Config::get('security', 'rate_limit_window', 900);
        $this->lockout = (int) Config::get('security', 'rate_limit_lockout', 900);
    }

    public function isBlocked(string $key): bool
    {
        $row = $this->row($key);
        if (!$row) return false;

        if ($row['blocked_until'] && strtotime($row['blocked_until']) > time()) {
            return true;
        }

        return false;
    }

    public function waitSeconds(string $key): int
    {
        $row = $this->row($key);
        if (!$row || !$row['blocked_until']) return 0;
        return max(0, (int) strtotime($row['blocked_until']) - time());
    }

    public function hit(string $key): void
    {
        $db  = Database::conn();
        $now = date('Y-m-d H:i:s');
        $row = $this->row($key);

        if (!$row) {
            $db->prepare('INSERT INTO rate_limits (`key`, attempts, window_start) VALUES (?, 1, ?)')
               ->execute([$key, $now]);
            return;
        }

        if ((time() - strtotime($row['window_start'])) > $this->window) {
            $db->prepare('UPDATE rate_limits SET attempts = 1, window_start = ?, blocked_until = NULL WHERE `key` = ?')
               ->execute([$now, $key]);
            return;
        }

        $attempts     = (int) $row['attempts'] + 1;
        $blockedUntil = $attempts >= $this->max
            ? date('Y-m-d H:i:s', time() + $this->lockout)
            : null;

        $db->prepare('UPDATE rate_limits SET attempts = ?, blocked_until = ? WHERE `key` = ?')
           ->execute([$attempts, $blockedUntil, $key]);
    }

    public function clear(string $key): void
    {
        Database::conn()
            ->prepare('DELETE FROM rate_limits WHERE `key` = ?')
            ->execute([$key]);
    }

    private function row(string $key): array|false
    {
        $stmt = Database::conn()->prepare(
            'SELECT attempts, window_start, blocked_until FROM rate_limits WHERE `key` = ? LIMIT 1'
        );
        $stmt->execute([$key]);
        return $stmt->fetch();
    }
}
