<?php

class SessionManager
{
    private const INACTIVITY_TIMEOUT = 7200; // 2 Stunden

    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        session_start();

        // Inaktivitäts-Timeout prüfen
        if (isset($_SESSION['last_active']) && (time() - $_SESSION['last_active']) > self::INACTIVITY_TIMEOUT) {
            self::destroy();
            self::start();
            return;
        }

        $_SESSION['last_active'] = time();
    }

    public static function setUser(int $userId, int $orgId, string $email): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id']     = $userId;
        $_SESSION['org_id']      = $orgId;
        $_SESSION['email']       = $email;
        $_SESSION['last_active'] = time();
    }

    public static function user(): ?array
    {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }
        return [
            'id'     => (int)$_SESSION['user_id'],
            'org_id' => (int)$_SESSION['org_id'],
            'email'  => $_SESSION['email'],
        ];
    }

    public static function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public static function destroy(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(
                session_name(), '', time() - 86400,
                $p['path'], $p['domain'], $p['secure'], $p['httponly']
            );
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    public static function csrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrf(string $token): bool
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}
