<?php

namespace App\Auth;

use App\Core\Config;
use App\Core\Database;
use App\Core\Session;

class Authenticator
{
    private const SESS_USER   = '_auth_user';
    private const SESS_TIME   = '_auth_time';
    private const COOKIE_NAME = 'ds_remember';

    private int $lifetime;
    private int $rememberDays;
    private int $bcryptCost;

    public function __construct()
    {
        $this->lifetime     = (int) Config::get('security', 'session_lifetime', 1800);
        $this->rememberDays = (int) Config::get('security', 'remember_me_days', 30);
        $this->bcryptCost   = (int) Config::get('security', 'bcrypt_cost', 12);
    }

    public function attempt(string $email, string $password, bool $remember): array
    {
        $db   = Database::conn();
        $stmt = $db->prepare(
            'SELECT id, name, email, password_hash, role, is_active, locked_until
             FROM admin_users WHERE email = ? LIMIT 1'
        );
        $stmt->execute([strtolower(trim($email))]);
        $user = $stmt->fetch();

        if (!$user) {
            return ['success' => false, 'message' => 'Invalid email or password.'];
        }

        if (!(bool) $user['is_active']) {
            return ['success' => false, 'message' => 'This account has been deactivated.'];
        }

        if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
            $mins = (int) ceil((strtotime($user['locked_until']) - time()) / 60);
            return ['success' => false, 'message' => "Account locked. Try again in {$mins} minute(s)."];
        }

        if (!password_verify($password, $user['password_hash'])) {
            $this->incrementFailures((int) $user['id']);
            return ['success' => false, 'message' => 'Invalid email or password.'];
        }

        $this->clearFailures((int) $user['id']);
        $this->createSession($user);

        if ($remember) {
            $this->issueRememberCookie((int) $user['id']);
        }

        return ['success' => true, 'user' => $this->userPayload($user)];
    }

    public function check(): bool
    {
        if (Session::has(self::SESS_USER)) {
            $elapsed = time() - (int) Session::get(self::SESS_TIME, 0);
            if ($elapsed > $this->lifetime) {
                $this->logout();
                return false;
            }
            Session::set(self::SESS_TIME, time());
            return true;
        }

        return $this->resumeFromCookie();
    }

    public function user(): ?array
    {
        return Session::get(self::SESS_USER);
    }

    public function logout(): void
    {
        $user = Session::get(self::SESS_USER);
        if ($user) {
            $this->deleteRememberTokens((int) $user['id']);
        }
        $this->expireRememberCookie();
        Session::destroy();
    }

    public function hashPassword(string $plain): string
    {
        return password_hash($plain, PASSWORD_BCRYPT, ['cost' => $this->bcryptCost]);
    }

    private function createSession(array $user): void
    {
        Session::regenerate();
        Session::set(self::SESS_USER, $this->userPayload($user));
        Session::set(self::SESS_TIME, time());

        Database::conn()
            ->prepare('UPDATE admin_users SET last_login_at = NOW(), last_login_ip = ? WHERE id = ?')
            ->execute([$_SERVER['REMOTE_ADDR'] ?? null, $user['id']]);
    }

    private function userPayload(array $user): array
    {
        return [
            'id'    => (int) $user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'role'  => $user['role'],
        ];
    }

    private function incrementFailures(int $id): void
    {
        $db   = Database::conn();
        $stmt = $db->prepare('SELECT failed_login_count FROM admin_users WHERE id = ?');
        $stmt->execute([$id]);
        $row  = $stmt->fetch();

        $count       = (int) ($row['failed_login_count'] ?? 0) + 1;
        $lockedUntil = $count >= 5 ? date('Y-m-d H:i:s', time() + 900) : null;

        $db->prepare('UPDATE admin_users SET failed_login_count = ?, locked_until = ? WHERE id = ?')
           ->execute([$count, $lockedUntil, $id]);
    }

    private function clearFailures(int $id): void
    {
        Database::conn()
            ->prepare('UPDATE admin_users SET failed_login_count = 0, locked_until = NULL WHERE id = ?')
            ->execute([$id]);
    }

    private function issueRememberCookie(int $userId): void
    {
        $raw  = bin2hex(random_bytes(32));
        $hash = hash('sha256', $raw);
        $exp  = date('Y-m-d H:i:s', time() + ($this->rememberDays * 86400));

        $this->deleteRememberTokens($userId);

        Database::conn()
            ->prepare('INSERT INTO remember_tokens (admin_user_id, token_hash, expires_at) VALUES (?, ?, ?)')
            ->execute([$userId, $hash, $exp]);

        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

        setcookie(self::COOKIE_NAME, $raw, [
            'expires'  => time() + ($this->rememberDays * 86400),
            'path'     => '/',
            'secure'   => $secure,
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
    }

    private function resumeFromCookie(): bool
    {
        $raw = $_COOKIE[self::COOKIE_NAME] ?? '';
        if (empty($raw)) return false;

        $hash = hash('sha256', $raw);
        $stmt = Database::conn()->prepare(
            'SELECT rt.expires_at, u.id, u.name, u.email, u.role, u.is_active
             FROM remember_tokens rt
             JOIN admin_users u ON u.id = rt.admin_user_id
             WHERE rt.token_hash = ? LIMIT 1'
        );
        $stmt->execute([$hash]);
        $row = $stmt->fetch();

        if (!$row || !$row['is_active'] || strtotime($row['expires_at']) < time()) {
            if ($row) $this->deleteRememberTokens((int) $row['id']);
            $this->expireRememberCookie();
            return false;
        }

        $this->createSession($row);
        $this->issueRememberCookie((int) $row['id']);
        return true;
    }

    private function deleteRememberTokens(int $userId): void
    {
        Database::conn()
            ->prepare('DELETE FROM remember_tokens WHERE admin_user_id = ?')
            ->execute([$userId]);
    }

    private function expireRememberCookie(): void
    {
        if (isset($_COOKIE[self::COOKIE_NAME])) {
            setcookie(self::COOKIE_NAME, '', [
                'expires'  => time() - 3600,
                'path'     => '/',
                'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
                'httponly' => true,
                'samesite' => 'Strict',
            ]);
        }
    }
}
