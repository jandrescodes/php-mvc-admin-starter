<?php

declare(strict_types=1);

namespace Tests\Integration\Services;

use App\Models\Permission;
use App\Models\User;
use App\Services\DashboardCache;
use Tests\IntegrationTestCase;

/**
 * Verifies that model mutations correctly invalidate DashboardCache entries.
 */
class DashboardCacheInvalidationTest extends IntegrationTestCase
{
    private User       $userModel;
    private Permission $permModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userModel = new User();
        $this->permModel = new Permission();

        // Pre-warm cache so we can verify it gets cleared
        DashboardCache::put('user_stats', ['total' => 99]);
        DashboardCache::put('users_by_status', ['active' => 99, 'inactive' => 0]);
        DashboardCache::put('recent_users', [['id' => 99]]);
        DashboardCache::put('users_by_month', [['ym' => '2025-01', 'total' => 99]]);
        DashboardCache::put('perm_stats', ['total' => 99]);
        DashboardCache::put('top_permissions', [['id' => 99, 'name' => 'x', 'total_users' => 99]]);
    }

    // --- User::create ---

    public function test_user_create_invalidates_user_cache_keys(): void
    {
        $this->userModel->create($this->sampleUserData('inv_create@test.com', '99999901'));

        $this->assertNull(DashboardCache::get('user_stats'));
        $this->assertNull(DashboardCache::get('users_by_status'));
        $this->assertNull(DashboardCache::get('recent_users'));
        $this->assertNull(DashboardCache::get('users_by_month'));
    }

    public function test_user_create_does_not_invalidate_permission_cache_keys(): void
    {
        $this->userModel->create($this->sampleUserData('inv_create2@test.com', '99999902'));

        $this->assertNotNull(DashboardCache::get('perm_stats'));
        $this->assertNotNull(DashboardCache::get('top_permissions'));
    }

    // --- User::update ---

    public function test_user_update_invalidates_user_cache_keys(): void
    {
        $data           = $this->sampleUserData('inv_update@test.com', '99999903');
        $data['status'] = 1;
        $this->userModel->update(1, $data);

        $this->assertNull(DashboardCache::get('user_stats'));
        $this->assertNull(DashboardCache::get('users_by_status'));
        $this->assertNull(DashboardCache::get('recent_users'));
        $this->assertNull(DashboardCache::get('users_by_month'));
    }

    // --- User::updateStatus ---

    public function test_user_updateStatus_invalidates_user_cache_keys(): void
    {
        $this->userModel->updateStatus(1, 0);

        $this->assertNull(DashboardCache::get('user_stats'));
        $this->assertNull(DashboardCache::get('users_by_status'));
        $this->assertNull(DashboardCache::get('recent_users'));
        $this->assertNull(DashboardCache::get('users_by_month'));
    }

    // --- Permission::create ---

    public function test_permission_create_invalidates_permission_cache_keys(): void
    {
        $this->permModel->create(['name' => 'inv_new_perm', 'description' => 'test']);

        $this->assertNull(DashboardCache::get('perm_stats'));
        $this->assertNull(DashboardCache::get('top_permissions'));
    }

    public function test_permission_create_does_not_invalidate_user_cache_keys(): void
    {
        $this->permModel->create(['name' => 'inv_new_perm2', 'description' => 'test']);

        $this->assertNotNull(DashboardCache::get('user_stats'));
        $this->assertNotNull(DashboardCache::get('users_by_status'));
    }

    // --- Permission::update ---

    public function test_permission_update_invalidates_permission_cache_keys(): void
    {
        $this->permModel->update(1, ['name' => 'users_renamed', 'description' => 'updated']);

        $this->assertNull(DashboardCache::get('perm_stats'));
        $this->assertNull(DashboardCache::get('top_permissions'));
    }

    // --- Permission::updateStatus ---

    public function test_permission_updateStatus_invalidates_permission_cache_keys(): void
    {
        $this->permModel->updateStatus(1, 0);

        $this->assertNull(DashboardCache::get('perm_stats'));
        $this->assertNull(DashboardCache::get('top_permissions'));
    }

    // --- Helpers ---

    /**
     * @return array<string, mixed>
     */
    private function sampleUserData(string $email, string $document): array
    {
        return [
            'name'            => 'Test',
            'first_surname'   => 'Invalidation',
            'second_surname'  => '',
            'document_type'   => 'DNI',
            'document_number' => $document,
            'address'         => '',
            'phone'           => '',
            'email'           => $email,
            'position'        => 'editor',
            'password'        => 'password123',
            'image'           => '',
            'role_id'         => 1,
            'status'          => 1,
        ];
    }
}
