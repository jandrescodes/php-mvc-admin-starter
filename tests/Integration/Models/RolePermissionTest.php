<?php

declare(strict_types=1);

namespace Tests\Integration\Models;

use App\Models\Role;
use Tests\IntegrationTestCase;

class RolePermissionTest extends IntegrationTestCase
{
    // syncPermissions manages its own transaction; disable the outer wrap
    protected bool $useTransactions = false;

    private Role $model;

    protected function setUp(): void
    {
        parent::setUp();
        static::reloadSeed();
        $this->model = new Role();
    }

    protected function tearDown(): void
    {
        static::reloadSeed();
        parent::tearDown();
    }

    // -------------------------------------------------------------------------
    // getAssignedPermissionIds
    // -------------------------------------------------------------------------

    public function test_get_assigned_permission_ids_returns_seeded_ids(): void
    {
        // minimal_seed: role 2 (Editor) → permission 1 (users)
        $ids = $this->model->getAssignedPermissionIds(2);
        $this->assertSame([1], $ids);
    }

    public function test_get_assigned_permission_ids_returns_empty_for_role_with_none(): void
    {
        // Auditor (id=3) has no permissions in seed
        $ids = $this->model->getAssignedPermissionIds(3);
        $this->assertSame([], $ids);
    }

    // -------------------------------------------------------------------------
    // getPermissionNames
    // -------------------------------------------------------------------------

    public function test_get_permission_names_returns_active_permission_names(): void
    {
        $names = $this->model->getPermissionNames(2);
        $this->assertContains('users', $names);
    }

    public function test_get_permission_names_omits_inactive_permissions(): void
    {
        // Deactivate permission 1 directly
        self::$pdo->exec('UPDATE permissions SET status = 0 WHERE id = 1');

        $names = $this->model->getPermissionNames(2);
        $this->assertNotContains('users', $names);
    }

    public function test_get_permission_names_returns_empty_for_role_with_none(): void
    {
        $names = $this->model->getPermissionNames(3);
        $this->assertSame([], $names);
    }

    // -------------------------------------------------------------------------
    // syncPermissions
    // -------------------------------------------------------------------------

    public function test_sync_permissions_inserts_new_assignments(): void
    {
        // Assign both permissions (1=users, 2=permissions) to Auditor (id=3)
        $result = $this->model->syncPermissions(3, [1, 2]);
        $this->assertTrue($result);
        $this->assertSame([1, 2], $this->model->getAssignedPermissionIds(3));
    }

    public function test_sync_permissions_replaces_existing_assignments(): void
    {
        // Editor (id=2) already has [1]; replace with [2]
        $result = $this->model->syncPermissions(2, [2]);
        $this->assertTrue($result);
        $this->assertSame([2], $this->model->getAssignedPermissionIds(2));
    }

    public function test_sync_permissions_removes_all_when_empty_array(): void
    {
        $result = $this->model->syncPermissions(2, []);
        $this->assertTrue($result);
        $this->assertSame([], $this->model->getAssignedPermissionIds(2));
    }

    public function test_sync_permissions_is_idempotent(): void
    {
        $this->model->syncPermissions(2, [1]);
        $this->model->syncPermissions(2, [1]);
        $this->assertSame([1], $this->model->getAssignedPermissionIds(2));
    }

    public function test_sync_permissions_keeps_inactive_assignments(): void
    {
        $pdo = \App\Config\Connection::getInstance()->getConnection();
        $pdo->exec("INSERT INTO permissions (id, name, description, status) VALUES (9, 'zz_inactive', 'x', 0)");
        $pdo->exec('INSERT INTO role_permissions (role_id, permission_id) VALUES (2, 9)');

        $this->assertTrue($this->model->syncPermissions(2, [2]));

        $this->assertEqualsCanonicalizing([2, 9], array_map('intval', $this->model->getAssignedPermissionIds(2)));
    }

    public function test_sync_permissions_does_not_duplicate_a_kept_inactive_assignment(): void
    {
        $pdo = \App\Config\Connection::getInstance()->getConnection();
        $pdo->exec("INSERT INTO permissions (id, name, description, status) VALUES (9, 'zz_inactive', 'x', 0)");
        $pdo->exec('INSERT INTO role_permissions (role_id, permission_id) VALUES (2, 9)');

        $this->assertTrue($this->model->syncPermissions(2, [1, 9]));

        $this->assertEqualsCanonicalizing([1, 9], array_map('intval', $this->model->getAssignedPermissionIds(2)));
    }

    // -------------------------------------------------------------------------
    // getUserIdsByRole
    // -------------------------------------------------------------------------

    public function test_get_user_ids_by_role_returns_seeded_users(): void
    {
        // minimal_seed: user 1 → Administrator (id=1), user 2 → Editor (id=2)
        $ids = $this->model->getUserIdsByRole(2);
        $this->assertSame([2], $ids);
    }

    public function test_get_user_ids_by_role_returns_empty_for_unused_role(): void
    {
        $ids = $this->model->getUserIdsByRole(3);
        $this->assertSame([], $ids);
    }

    public function test_get_user_ids_by_role_reflects_assignment_changes(): void
    {
        self::$pdo->exec('UPDATE users SET role_id = 3 WHERE id = 2');
        $ids = $this->model->getUserIdsByRole(3);
        $this->assertContains('2', array_map('strval', $ids));
    }
}
