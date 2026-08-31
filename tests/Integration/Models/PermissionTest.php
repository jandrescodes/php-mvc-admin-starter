<?php

declare(strict_types=1);

namespace Tests\Integration\Models;

use App\Models\Permission;
use Tests\IntegrationTestCase;

class PermissionTest extends IntegrationTestCase
{
    // syncForUser manages its own PDO transaction, which conflicts with the
    // outer test transaction. Disable auto-wrapping and reload seed per test.
    protected bool $useTransactions = false;

    private Permission $model;

    protected function setUp(): void
    {
        parent::setUp();
        self::reloadSeed();
        $this->model = new Permission();
    }

    protected function tearDown(): void
    {
        self::reloadSeed();
        parent::tearDown();
    }

    // -------------------------------------------------------------------------
    // Read
    // -------------------------------------------------------------------------

    public function test_get_all_returns_seeded_permissions(): void
    {
        $perms = $this->model->getAll();
        $this->assertCount(2, $perms);
    }

    public function test_get_all_active_returns_only_active(): void
    {
        $perms = $this->model->getAllActive();
        $this->assertCount(2, $perms);
        foreach ($perms as $p) {
            $this->assertArrayHasKey('name', $p);
        }
    }

    public function test_get_by_id_returns_permission_row(): void
    {
        $perm = $this->model->getById(1);
        $this->assertIsArray($perm);
        $this->assertSame('users', $perm['name']);
    }

    public function test_get_by_id_returns_false_for_unknown_id(): void
    {
        $this->assertFalse($this->model->getById(9999));
    }

    public function test_get_by_user_id_returns_assigned_permissions(): void
    {
        // User 2 has permission 'users' assigned via minimal seed
        $perms = $this->model->getByUserId(2);
        $names = array_column($perms, 'name');

        $this->assertContains('users', $names);
    }

    public function test_get_by_user_id_returns_empty_for_user_without_perms(): void
    {
        // User 1 is admin — no user_permissions rows in minimal seed
        $this->assertSame([], $this->model->getByUserId(1));
    }

    public function test_get_assigned_ids_returns_permission_ids(): void
    {
        $ids = $this->model->getAssignedIds(2);
        $this->assertContains(1, $ids);
    }

    // -------------------------------------------------------------------------
    // Assign / Revoke
    // -------------------------------------------------------------------------

    public function test_assign_grants_permission_to_user(): void
    {
        $result = $this->model->assign(2, 2); // give user 2 the 'permissions' perm
        $this->assertTrue($result);

        $ids = $this->model->getAssignedIds(2);
        $this->assertContains(2, $ids);
    }

    public function test_assign_is_idempotent(): void
    {
        // User 2 already has perm 1 — assigning again must return true, no duplicate
        $this->assertTrue($this->model->assign(2, 1));
        $this->assertTrue($this->model->assign(2, 1));

        $count = count(array_filter($this->model->getAssignedIds(2), fn ($id) => $id == 1));
        $this->assertSame(1, $count);
    }

    public function test_revoke_removes_assigned_permission(): void
    {
        $this->model->revoke(2, 1);
        $ids = $this->model->getAssignedIds(2);
        $this->assertNotContains(1, $ids);
    }

    public function test_revoke_is_idempotent_on_nonexistent_assignment(): void
    {
        // User 1 has no permissions — revoking must still return true
        $this->assertTrue($this->model->revoke(1, 1));
    }

    // -------------------------------------------------------------------------
    // syncForUser
    // -------------------------------------------------------------------------

    public function test_sync_for_user_replaces_all_permissions(): void
    {
        // User 2 currently has perm 1; sync to perm 2 only
        $names = $this->model->syncForUser(2, [2]);

        $this->assertContains('permissions', $names);
        $this->assertNotContains('users', $names);
    }

    public function test_sync_for_user_with_empty_array_revokes_all(): void
    {
        $names = $this->model->syncForUser(2, []);
        $this->assertSame([], $names);
    }

    // -------------------------------------------------------------------------
    // nameExists
    // -------------------------------------------------------------------------

    public function test_name_exists_returns_true_for_seeded_name(): void
    {
        $this->assertTrue($this->model->nameExists('users'));
    }

    public function test_name_exists_excludes_own_id(): void
    {
        $this->assertFalse($this->model->nameExists('users', 1));
    }

    public function test_name_exists_returns_false_for_unknown_name(): void
    {
        $this->assertFalse($this->model->nameExists('nonexistent'));
    }
}
