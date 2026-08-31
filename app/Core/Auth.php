<?php

namespace App\Core;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditLogger;

/**
 * Central authentication hub.
 *
 * Owns session state, login/logout lifecycle, permission cache,
 * and remember-me cookies. Replaces AuthorizationService (session
 * checks) and RememberMeService (cookie lifecycle).
 *
 * All methods are static — no instantiation needed.
 */
final class Auth
{
    private function __construct()
    {
    }

    // -------------------------------------------------------------------------
    // Session state
    // -------------------------------------------------------------------------

    public static function check(): bool
    {
        return isset($_SESSION['user_id']) && $_SESSION['authenticated'] === true;
    }

    public static function id(): ?int
    {
        return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    }

    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }
        return [
            'id'    => $_SESSION['user_id']    ?? null,
            'name'  => $_SESSION['user_name']  ?? null,
            'email' => $_SESSION['user_email'] ?? null,
            'role'  => $_SESSION['user_role']  ?? null,
            'image' => $_SESSION['user_image'] ?? 'user_default.jpg',
        ];
    }

    public static function isAdmin(): bool
    {
        return (bool) ($_SESSION['user_is_admin'] ?? false);
    }

    /**
     * Checks a permission against the session cache.
     * '*' grants all permissions (administrators).
     */
    public static function hasPermission(string $name): bool
    {
        $perms = $_SESSION['user_permissions'] ?? [];
        return in_array('*', $perms) || in_array($name, $perms);
    }

    public static function permissions(): array
    {
        return $_SESSION['user_permissions'] ?? [];
    }

    // -------------------------------------------------------------------------
    // Login / logout lifecycle
    // -------------------------------------------------------------------------

    /**
     * Populates the session after successful authentication.
     *
     * @param array $user       Row from the users table
     * @param array $permNames  Permission names to cache (use ['*'] for admins)
     */
    public static function login(array $user, array $permNames): void
    {
        session_regenerate_id(true);

        $_SESSION['user_id']          = $user['id'];
        $_SESSION['user_name']        = $user['name'] . ' ' . $user['first_surname'];
        $_SESSION['user_email']       = $user['email'];
        $_SESSION['user_is_admin']    = (bool) ($user['role_is_system'] ?? false);
        $_SESSION['user_role']        = $user['role_name'] ?? '';
        $_SESSION['user_image']       = $user['image'] ?? 'user_default.jpg';
        $_SESSION['authenticated']    = true;
        $_SESSION['last_access']      = time();
        $_SESSION['ip']               = $_SERVER['REMOTE_ADDR']     ?? '';
        $_SESSION['user_agent']       = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $_SESSION['user_permissions'] = $permNames;
        $_SESSION['permissions_ts']   = date('Y-m-d H:i:s');
    }

    /**
     * Clears the remember-me cookie, then destroys the session.
     */
    public static function logout(): void
    {
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        if ($userId > 0) {
            self::clearRememberCookie($userId);
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }

    // -------------------------------------------------------------------------
    // Session validation
    // -------------------------------------------------------------------------

    /**
     * Returns false and destroys the session if idle time exceeds SESSION_LIFETIME.
     */
    public static function checkTimeout(): bool
    {
        $timeout = (int) env('SESSION_LIFETIME', 1800);
        if ($timeout <= 0) {
            $timeout = 1800;
        }

        if (!isset($_SESSION['last_access'])) {
            session_unset();
            session_destroy();
            return false;
        }

        if (time() - $_SESSION['last_access'] >= $timeout) {
            session_unset();
            session_destroy();
            return false;
        }

        $_SESSION['last_access'] = time();
        return true;
    }

    /**
     * Returns false and destroys the session if IP or User-Agent changed.
     */
    public static function checkSecurity(): bool
    {
        if (!isset($_SESSION['ip'], $_SESSION['user_agent'])) {
            session_unset();
            session_destroy();
            return false;
        }

        if (
            $_SESSION['ip']         !== ($_SERVER['REMOTE_ADDR']     ?? '') ||
            $_SESSION['user_agent'] !== ($_SERVER['HTTP_USER_AGENT'] ?? '')
        ) {
            session_unset();
            session_destroy();
            return false;
        }

        return true;
    }

    // -------------------------------------------------------------------------
    // Permission cache
    // -------------------------------------------------------------------------

    /**
     * Reloads the permission cache from the DB if the user's permissions
     * were updated after the session was last refreshed.
     */
    public static function refreshPermissionsIfStale(): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId || !isset($_SESSION['user_permissions'])) {
            return;
        }

        $userModel   = new User();
        $dbTimestamp = $userModel->getPermissionsTimestamp($userId);
        $sessionTs   = $_SESSION['permissions_ts'] ?? null;

        if ($dbTimestamp && (!$sessionTs || $dbTimestamp > $sessionTs)) {
            $user = $userModel->getById($userId);
            if ($user) {
                $_SESSION['user_permissions'] = self::buildPermNames($user);
            }
            $_SESSION['permissions_ts'] = $dbTimestamp;
        }
    }

    // -------------------------------------------------------------------------
    // Remember-me
    // -------------------------------------------------------------------------

    /**
     * Issues a remember-me cookie and persists the token hash in the DB.
     */
    public static function issueRememberCookie(int $userId): void
    {
        $token     = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $lifetime  = self::cookieLifetime();
        $expires   = date('Y-m-d H:i:s', time() + $lifetime);

        (new User())->setRememberToken($userId, $tokenHash, $expires);
        self::setCookie($token, time() + $lifetime);
    }

    /**
     * Tries to log in using the remember-me cookie.
     * Rotates the token on each successful use.
     *
     * @return bool  True if auto-login succeeded
     */
    public static function attemptRememberLogin(): bool
    {
        $cookieName = (string) env('REMEMBER_ME_COOKIE_NAME', 'remember_me');

        if (empty($_COOKIE[$cookieName])) {
            return false;
        }

        $token = (string) $_COOKIE[$cookieName];
        if (strlen($token) !== 64) {
            self::clearCookieByName($cookieName);
            return false;
        }

        $tokenHash = hash('sha256', $token);
        $userModel = new User();
        $user      = $userModel->findByRememberToken($tokenHash);

        if (!$user) {
            self::clearCookieByName($cookieName);
            return false;
        }

        self::login($user, self::buildPermNames($user));

        AuditLogger::log(
            'auth',
            'remember_login',
            "Auto-login via remember-me: {$user['name']} {$user['first_surname']}",
            ['email' => $user['email']]
        );

        // Rotate token to mitigate cookie theft
        $newToken     = bin2hex(random_bytes(32));
        $newTokenHash = hash('sha256', $newToken);
        $lifetime     = self::cookieLifetime();
        $newExpires   = date('Y-m-d H:i:s', time() + $lifetime);
        $userModel->setRememberToken((int) $user['id'], $newTokenHash, $newExpires);
        self::setCookie($newToken, time() + $lifetime);

        return true;
    }

    /**
     * Deletes the DB token row and expires the browser cookie.
     */
    public static function clearRememberCookie(int $userId): void
    {
        (new User())->clearRememberToken($userId);
        self::clearCookieByName((string) env('REMEMBER_ME_COOKIE_NAME', 'remember_me'));
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Builds the permission name array for a user row.
     * Returns ['*'] for system roles; merges direct + role permissions for others.
     * Returns [] without querying DB when role_id is null (safe for unit tests).
     */
    public static function buildPermNames(array $user): array
    {
        if (!empty($user['role_is_system'])) {
            return ['*'];
        }

        $direct = array_column(
            (new Permission())->getByUserId((int) $user['id']),
            'name'
        );

        $fromRole = !empty($user['role_id'])
            ? (new Role())->getPermissionNames((int) $user['role_id'])
            : [];

        return array_values(array_unique(array_merge($direct, $fromRole)));
    }

    private static function cookieLifetime(): int
    {
        $lifetime = (int) env('REMEMBER_ME_LIFETIME', 2592000);
        return $lifetime > 0 ? $lifetime : 2592000;
    }

    private static function setCookie(string $value, int $expires): void
    {
        $name   = (string) env('REMEMBER_ME_COOKIE_NAME', 'remember_me');
        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        $path   = defined('APP_BASE_PATH') ? (APP_BASE_PATH === '' ? '/' : APP_BASE_PATH) : '/';

        setcookie($name, $value, [
            'expires'  => $expires,
            'path'     => $path,
            'domain'   => '',
            'secure'   => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    private static function clearCookieByName(string $name): void
    {
        $path = defined('APP_BASE_PATH') ? (APP_BASE_PATH === '' ? '/' : APP_BASE_PATH) : '/';
        setcookie($name, '', [
            'expires'  => time() - 3600,
            'path'     => $path,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        unset($_COOKIE[$name]);
    }
}
