<?php

declare(strict_types=1);

namespace Tests\Integration\Core;

use App\Core\Auth;
use App\Models\User;
use Tests\IntegrationTestCase;

/**
 * Tests for Auth methods that cross into the model/DB layer:
 * refreshPermissionsIfStale() and attemptRememberLogin().
 */
class AuthIntegrationTest extends IntegrationTestCase
{
    private User $userModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userModel = new User();

        // Start a real session so Auth::login() can call session_regenerate_id()
        // without emitting warnings in CLI.
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        unset($_COOKIE[getenv('REMEMBER_ME_COOKIE_NAME') ?: 'remember_me']);
        parent::tearDown();
    }

    // -------------------------------------------------------------------------
    // refreshPermissionsIfStale()
    // -------------------------------------------------------------------------

    public function test_refresh_is_no_op_when_session_has_no_user(): void
    {
        Auth::refreshPermissionsIfStale(); // must not throw
        $this->assertArrayNotHasKey('user_permissions', $_SESSION);
    }

    public function test_refresh_is_no_op_when_permissions_ts_is_current(): void
    {
        $_SESSION['user_id']          = 2;
        $_SESSION['user_permissions'] = ['users'];
        $_SESSION['user_is_admin']    = false;
        // Set session timestamp to now — DB has no permissions_updated_at, so no reload
        $_SESSION['permissions_ts'] = date('Y-m-d H:i:s');

        Auth::refreshPermissionsIfStale();

        $this->assertSame(['users'], $_SESSION['user_permissions']);
    }

    public function test_refresh_reloads_cache_when_db_timestamp_is_newer(): void
    {
        // Bump DB timestamp first
        $this->userModel->updatePermissionsTimestamp(2);
        $dbTs = $this->userModel->getPermissionsTimestamp(2);

        // Set session timestamp to one second before DB timestamp
        $stale = date('Y-m-d H:i:s', strtotime($dbTs) - 1);

        $_SESSION['user_id']          = 2;
        $_SESSION['user_permissions'] = ['stale_data'];
        $_SESSION['user_is_admin']    = false;
        $_SESSION['permissions_ts']   = $stale;

        Auth::refreshPermissionsIfStale();

        // After refresh, 'stale_data' must be gone; 'users' (from seed) must be present
        $this->assertNotContains('stale_data', $_SESSION['user_permissions']);
        $this->assertContains('users', $_SESSION['user_permissions']);
    }

    public function test_refresh_assigns_wildcard_for_admin_user(): void
    {
        $this->userModel->updatePermissionsTimestamp(1);
        $dbTs  = $this->userModel->getPermissionsTimestamp(1);
        $stale = date('Y-m-d H:i:s', strtotime($dbTs) - 1);

        $_SESSION['user_id']          = 1;
        $_SESSION['user_permissions'] = ['stale'];
        $_SESSION['user_is_admin']    = true;
        $_SESSION['permissions_ts']   = $stale;

        Auth::refreshPermissionsIfStale();

        $this->assertContains('*', $_SESSION['user_permissions']);
    }

    // -------------------------------------------------------------------------
    // refreshPermissionsIfStale() — UNION direct + role permissions
    // -------------------------------------------------------------------------

    public function test_refresh_merges_direct_and_role_permissions(): void
    {
        // Seed: user 2 has 'users' directly AND role Editor (id=2) also has 'users' via role_permissions.
        // Add 'permissions' only to role so it comes exclusively from the role.
        self::$pdo->exec("INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (2, 2)");

        $this->userModel->updatePermissionsTimestamp(2);
        $dbTs  = $this->userModel->getPermissionsTimestamp(2);
        $stale = date('Y-m-d H:i:s', strtotime($dbTs) - 1);

        $_SESSION['user_id']          = 2;
        $_SESSION['user_permissions'] = ['stale'];
        $_SESSION['user_is_admin']    = false;
        $_SESSION['permissions_ts']   = $stale;

        Auth::refreshPermissionsIfStale();

        $perms = $_SESSION['user_permissions'];
        $this->assertContains('users', $perms, 'direct permission must be present');
        $this->assertContains('permissions', $perms, 'role permission must be present');
    }

    public function test_refresh_deduplicates_overlapping_permissions(): void
    {
        // user 2 has 'users' directly; Editor role also has 'users' — must appear only once.
        $this->userModel->updatePermissionsTimestamp(2);
        $dbTs  = $this->userModel->getPermissionsTimestamp(2);
        $stale = date('Y-m-d H:i:s', strtotime($dbTs) - 1);

        $_SESSION['user_id']          = 2;
        $_SESSION['user_permissions'] = ['stale'];
        $_SESSION['user_is_admin']    = false;
        $_SESSION['permissions_ts']   = $stale;

        Auth::refreshPermissionsIfStale();

        $perms = $_SESSION['user_permissions'];
        $this->assertSame(
            count($perms),
            count(array_unique($perms)),
            'permissions must not contain duplicates'
        );
    }

    public function test_refresh_returns_empty_when_user_has_no_role_permissions_and_no_direct_permissions(): void
    {
        // Remove direct permissions; assign a role that has no role_permissions rows.
        // role_id = 2 (Editor in minimal_seed) — we wipe its role_permissions for this test.
        self::$pdo->exec("DELETE FROM user_permissions WHERE user_id = 2");
        self::$pdo->exec("DELETE FROM role_permissions WHERE role_id = (SELECT role_id FROM users WHERE id = 2 LIMIT 1)");

        $this->userModel->updatePermissionsTimestamp(2);
        $dbTs  = $this->userModel->getPermissionsTimestamp(2);
        $stale = date('Y-m-d H:i:s', strtotime($dbTs) - 1);

        $_SESSION['user_id']          = 2;
        $_SESSION['user_permissions'] = ['stale'];
        $_SESSION['user_is_admin']    = false;
        $_SESSION['permissions_ts']   = $stale;

        Auth::refreshPermissionsIfStale();

        $this->assertSame([], $_SESSION['user_permissions']);
    }

    // -------------------------------------------------------------------------
    // attemptRememberLogin()
    // -------------------------------------------------------------------------

    public function test_attempt_remember_login_returns_false_when_no_cookie(): void
    {
        $this->assertFalse(Auth::attemptRememberLogin());
    }

    public function test_attempt_remember_login_returns_false_for_wrong_length_token(): void
    {
        $cookieName          = getenv('REMEMBER_ME_COOKIE_NAME') ?: 'remember_me';
        $_COOKIE[$cookieName] = 'tooshort';

        $this->assertFalse(Auth::attemptRememberLogin());
    }

    public function test_attempt_remember_login_logs_in_with_valid_token(): void
    {
        $rawToken  = bin2hex(random_bytes(32)); // 64 hex chars
        $tokenHash = hash('sha256', $rawToken);
        $expires   = date('Y-m-d H:i:s', time() + 3600);

        $this->userModel->setRememberToken(2, $tokenHash, $expires);

        $cookieName          = getenv('REMEMBER_ME_COOKIE_NAME') ?: 'remember_me';
        $_COOKIE[$cookieName] = $rawToken;

        // Suppress setcookie() warning (no HTTP context in CLI)
        set_error_handler(fn () => true);
        $result = Auth::attemptRememberLogin();
        restore_error_handler();

        $this->assertTrue($result);
        $this->assertTrue(Auth::check());
        $this->assertSame(2, Auth::id());
    }

    public function test_attempt_remember_login_returns_false_for_expired_token(): void
    {
        $rawToken  = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $rawToken);
        $expires   = date('Y-m-d H:i:s', time() - 1); // already expired

        $this->userModel->setRememberToken(2, $tokenHash, $expires);

        $cookieName          = getenv('REMEMBER_ME_COOKIE_NAME') ?: 'remember_me';
        $_COOKIE[$cookieName] = $rawToken;

        $this->assertFalse(Auth::attemptRememberLogin());
        $this->assertFalse(Auth::check());
    }

    public function test_attempt_remember_login_returns_false_for_tampered_token(): void
    {
        $rawToken  = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $rawToken);
        $expires   = date('Y-m-d H:i:s', time() + 3600);

        $this->userModel->setRememberToken(2, $tokenHash, $expires);

        $cookieName          = getenv('REMEMBER_ME_COOKIE_NAME') ?: 'remember_me';
        // Tamper by flipping the last character
        $tampered = substr($rawToken, 0, -1) . ($rawToken[-1] === 'a' ? 'b' : 'a');
        $_COOKIE[$cookieName] = $tampered;

        $this->assertFalse(Auth::attemptRememberLogin());
    }

    public function test_attempt_remember_login_rotates_token_on_success(): void
    {
        $rawToken  = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $rawToken);
        $expires   = date('Y-m-d H:i:s', time() + 3600);

        $this->userModel->setRememberToken(2, $tokenHash, $expires);

        $cookieName          = getenv('REMEMBER_ME_COOKIE_NAME') ?: 'remember_me';
        $_COOKIE[$cookieName] = $rawToken;

        set_error_handler(fn () => true);
        Auth::attemptRememberLogin();
        restore_error_handler();

        // Original token hash must no longer be valid
        $this->assertFalse($this->userModel->findByRememberToken($tokenHash));
    }
}
