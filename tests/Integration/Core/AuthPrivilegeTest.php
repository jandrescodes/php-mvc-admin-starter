<?php

declare(strict_types=1);

namespace Tests\Integration\Core;

use App\Core\Auth;
use App\Models\User;
use Tests\IntegrationTestCase;

/**
 * Privilege boundaries and account-state invalidation in App\Core\Auth.
 * Seed: user 1 = admin (system role), user 2 = Editor with 'users' (direct + role),
 * permission 1 = 'users', permission 2 = 'permissions'.
 */
class AuthPrivilegeTest extends IntegrationTestCase
{
    private User $userModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userModel = new User();

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
        parent::tearDown();
    }

    private function actAs(array $permissions, bool $admin = false): void
    {
        $_SESSION['user_permissions'] = $permissions;
        $_SESSION['user_is_admin']    = $admin;
    }

    private function startSessionFor(int $userId): void
    {
        $state = $this->userModel->getSessionState($userId);
        $_SESSION['user_id'] = $userId;
        $_SESSION['pw_fp']   = hash('sha256', (string) $state['password']);
    }

    // -------------------------------------------------------------------------
    // canGrantPermissionIds()
    // -------------------------------------------------------------------------

    public function test_canGrantPermissionIds_allows_admin_any_permission(): void
    {
        $this->actAs(['*'], true);
        $this->assertTrue(Auth::canGrantPermissionIds([1, 2]));
    }

    public function test_canGrantPermissionIds_allows_permissions_the_actor_holds(): void
    {
        $this->actAs(['users']);
        $this->assertTrue(Auth::canGrantPermissionIds([1]));
    }

    public function test_canGrantPermissionIds_rejects_a_permission_the_actor_lacks(): void
    {
        $this->actAs(['users']);
        $this->assertFalse(Auth::canGrantPermissionIds([1, 2]));
    }

    public function test_canGrantPermissionIds_accepts_an_empty_list(): void
    {
        $this->actAs([]);
        $this->assertTrue(Auth::canGrantPermissionIds([]));
    }

    // -------------------------------------------------------------------------
    // canManageUser()
    // -------------------------------------------------------------------------

    public function test_canManageUser_allows_admin_to_manage_anyone(): void
    {
        $this->actAs(['*'], true);
        $this->assertTrue(Auth::canManageUser($this->userModel->getById(1)));
    }

    public function test_canManageUser_allows_target_whose_permissions_are_a_subset(): void
    {
        $this->actAs(['users']);
        $this->assertTrue(Auth::canManageUser($this->userModel->getById(2)));
    }

    public function test_canManageUser_rejects_system_role_account_for_non_admin(): void
    {
        $this->actAs(['users']);
        $this->assertFalse(Auth::canManageUser($this->userModel->getById(1)));
    }

    public function test_canManageUser_rejects_target_with_extra_direct_permission(): void
    {
        self::$pdo->exec('INSERT INTO user_permissions (user_id, permission_id) VALUES (2, 2)');

        $this->actAs(['users']);
        $this->assertFalse(Auth::canManageUser($this->userModel->getById(2)));
    }

    public function test_canManageUser_rejects_target_whose_role_carries_extra_permission(): void
    {
        self::$pdo->exec('INSERT INTO role_permissions (role_id, permission_id) VALUES (2, 2)');

        $this->actAs(['users']);
        $this->assertFalse(Auth::canManageUser($this->userModel->getById(2)));
    }

    // -------------------------------------------------------------------------
    // checkAccountState() / syncPasswordFingerprint()
    // -------------------------------------------------------------------------

    public function test_checkAccountState_accepts_active_account_with_matching_fingerprint(): void
    {
        $this->startSessionFor(2);
        $this->assertTrue(Auth::checkAccountState());
    }

    public function test_checkAccountState_adopts_fingerprint_when_session_has_none(): void
    {
        $_SESSION['user_id'] = 2;

        $this->assertTrue(Auth::checkAccountState());
        $this->assertArrayHasKey('pw_fp', $_SESSION);
    }

    public function test_checkAccountState_rejects_deactivated_account(): void
    {
        $this->startSessionFor(2);
        self::$pdo->exec('UPDATE users SET status = 0 WHERE id = 2');

        $this->assertFalse(Auth::checkAccountState());
        $this->assertArrayNotHasKey('user_id', $_SESSION);
    }

    public function test_checkAccountState_rejects_pending_account(): void
    {
        $this->startSessionFor(2);
        self::$pdo->exec('UPDATE users SET status = 2 WHERE id = 2');

        $this->assertFalse(Auth::checkAccountState());
    }

    public function test_checkAccountState_rejects_session_after_password_change_elsewhere(): void
    {
        $this->startSessionFor(2);
        $this->userModel->updatePassword(2, 'another-password-1');

        $this->assertFalse(Auth::checkAccountState());
    }

    public function test_checkAccountState_rejects_unknown_user(): void
    {
        $_SESSION['user_id'] = 9999;
        $this->assertFalse(Auth::checkAccountState());
    }

    public function test_syncPasswordFingerprint_keeps_the_session_that_changed_the_password(): void
    {
        $this->startSessionFor(2);
        $this->userModel->updatePassword(2, 'another-password-1');
        Auth::syncPasswordFingerprint();

        $this->assertTrue(Auth::checkAccountState());
    }

    // -------------------------------------------------------------------------
    // reloadPermissions()
    // -------------------------------------------------------------------------

    public function test_reloadPermissions_rebuilds_cache_from_role_and_direct_permissions(): void
    {
        self::$pdo->exec('INSERT INTO user_permissions (user_id, permission_id) VALUES (2, 2)');
        $_SESSION['user_id']          = 2;
        $_SESSION['user_permissions'] = [];

        Auth::reloadPermissions();

        $this->assertEqualsCanonicalizing(['users', 'permissions'], $_SESSION['user_permissions']);
    }

    public function test_reloadPermissions_keeps_role_permissions_when_user_has_no_direct_ones(): void
    {
        self::$pdo->exec('DELETE FROM user_permissions WHERE user_id = 2');
        $_SESSION['user_id']          = 2;
        $_SESSION['user_permissions'] = [];

        Auth::reloadPermissions();

        $this->assertSame(['users'], $_SESSION['user_permissions']);
    }
}
