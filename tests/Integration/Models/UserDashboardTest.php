<?php

declare(strict_types=1);

namespace Tests\Integration\Models;

use App\Models\User;
use Tests\IntegrationTestCase;

class UserDashboardTest extends IntegrationTestCase
{
    private User $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new User();
    }

    // --- getUsersByStatus ---

    public function test_getUsersByStatus_returns_correct_counts(): void
    {
        $result = $this->model->getUsersByStatus();

        // Seed: 2 active users, 0 inactive
        $this->assertArrayHasKey('active', $result);
        $this->assertArrayHasKey('inactive', $result);
        $this->assertSame(2, $result['active']);
        $this->assertSame(0, $result['inactive']);
    }

    public function test_getUsersByStatus_reflects_inactive_user(): void
    {
        self::$pdo->exec("UPDATE users SET status = 0 WHERE id = 2");

        $result = $this->model->getUsersByStatus();

        $this->assertSame(1, $result['active']);
        $this->assertSame(1, $result['inactive']);
    }

    // --- getUsersByMonth ---

    public function test_getUsersByMonth_returns_exactly_n_entries(): void
    {
        $result = $this->model->getUsersByMonth(6);

        $this->assertCount(6, $result);
    }

    public function test_getUsersByMonth_entries_have_correct_shape(): void
    {
        $result = $this->model->getUsersByMonth(3);

        foreach ($result as $entry) {
            $this->assertArrayHasKey('ym', $entry);
            $this->assertArrayHasKey('total', $entry);
            $this->assertMatchesRegularExpression('/^\d{4}-\d{2}$/', $entry['ym']);
            $this->assertIsInt($entry['total']);
        }
    }

    public function test_getUsersByMonth_entries_are_ordered_ascending(): void
    {
        $result = $this->model->getUsersByMonth(6);

        $yms = array_column($result, 'ym');
        $sorted = $yms;
        sort($sorted);

        $this->assertSame($sorted, $yms);
    }

    public function test_getUsersByMonth_fills_empty_months_with_zero(): void
    {
        // All seed users have created_at = NOW(); months far in the past must be 0
        $result = $this->model->getUsersByMonth(6);

        // At least the first 5 entries should exist (may be 0 depending on seed date)
        $totals = array_column($result, 'total');
        $this->assertContains(0, $totals, 'Expected at least one zero-filled month in a 6-month window');
    }

    public function test_getUsersByMonth_accepts_custom_range(): void
    {
        $result = $this->model->getUsersByMonth(3);
        $this->assertCount(3, $result);
    }
}
