<?php

declare(strict_types=1);

namespace Tests\Integration\Models;

use App\Models\Permission;
use Tests\IntegrationTestCase;

class PermissionDashboardTest extends IntegrationTestCase
{
    private Permission $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new Permission();
    }

    // --- getTopAssigned ---

    public function test_getTopAssigned_returns_array(): void
    {
        $result = $this->model->getTopAssigned();

        $this->assertIsArray($result);
    }

    public function test_getTopAssigned_respects_limit(): void
    {
        $result = $this->model->getTopAssigned(1);

        $this->assertCount(1, $result);
    }

    public function test_getTopAssigned_entries_have_correct_shape(): void
    {
        $result = $this->model->getTopAssigned(5);

        foreach ($result as $entry) {
            $this->assertArrayHasKey('id', $entry);
            $this->assertArrayHasKey('name', $entry);
            $this->assertArrayHasKey('total_users', $entry);
            $this->assertIsInt($entry['id']);
            $this->assertIsString($entry['name']);
            $this->assertIsInt($entry['total_users']);
        }
    }

    public function test_getTopAssigned_ordered_by_total_users_desc(): void
    {
        // Seed: permission 1 ('users') has 1 assignment, permission 2 has 0
        $result = $this->model->getTopAssigned(5);

        $totals = array_column($result, 'total_users');
        $sorted = $totals;
        rsort($sorted);

        $this->assertSame($sorted, $totals);
    }

    public function test_getTopAssigned_only_includes_active_permissions(): void
    {
        // Deactivate permission 1
        self::$pdo->exec("UPDATE permissions SET status = 0 WHERE id = 1");

        $result = $this->model->getTopAssigned(5);

        $ids = array_column($result, 'id');
        $this->assertNotContains(1, $ids);
    }

    public function test_getTopAssigned_returns_correct_user_count(): void
    {
        // Seed: user 2 is assigned to permission 1 ('users')
        $result = $this->model->getTopAssigned(5);

        $byName = array_column($result, null, 'name');
        $this->assertSame(1, $byName['users']['total_users']);
        $this->assertSame(0, $byName['permissions']['total_users']);
    }
}
