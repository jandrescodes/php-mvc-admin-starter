<?php

declare(strict_types=1);

namespace Tests\Integration\Models;

use App\Models\ActivityLog;
use Tests\IntegrationTestCase;

/**
 * Integration tests for ActivityLog model.
 *
 * Each test runs inside a transaction that is rolled back on teardown,
 * so the DB remains clean between tests (inherited from IntegrationTestCase).
 */
class ActivityLogTest extends IntegrationTestCase
{
    private ActivityLog $model;

    /** Minimal valid payload for a log entry */
    private array $baseData = [
        'actor_id'    => 1,
        'actor_label' => 'Admin Test (admin@test.com)',
        'module'      => 'users',
        'action'      => 'create',
        'description' => 'User created: Jane Doe',
        'details'     => ['name' => 'Jane Doe', 'email' => 'jane@test.com'],
        'ip_address'  => '127.0.0.1',
        'user_agent'  => 'PHPUnit',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new ActivityLog();
    }

    // -------------------------------------------------------------------------
    // create()
    // -------------------------------------------------------------------------

    public function test_create_returns_inserted_id(): void
    {
        $id = $this->model->create($this->baseData);

        $this->assertIsInt($id);
        $this->assertGreaterThan(0, $id);
    }

    public function test_create_anonymous_entry_without_actor(): void
    {
        $data = $this->baseData;
        unset($data['actor_id'], $data['actor_label']);
        $data['module'] = 'auth';
        $data['action'] = 'login_failed';

        $id = $this->model->create($data);
        $this->assertIsInt($id);
        $this->assertGreaterThan(0, $id);
    }

    public function test_create_with_null_details_succeeds(): void
    {
        $data           = $this->baseData;
        $data['details'] = null;

        $id = $this->model->create($data);
        $this->assertIsInt($id);
    }

    // -------------------------------------------------------------------------
    // getAll()
    // -------------------------------------------------------------------------

    public function test_getAll_returns_created_entries(): void
    {
        $this->model->create($this->baseData);
        $this->model->create(array_merge($this->baseData, ['action' => 'update']));

        $rows = $this->model->getAll();
        $this->assertCount(2, $rows);
    }

    public function test_getAll_returns_empty_when_no_entries(): void
    {
        $this->assertSame([], $this->model->getAll());
    }

    public function test_getAll_ordered_most_recent_first(): void
    {
        // Insert two entries; the second one should appear first in the result
        $this->model->create(array_merge($this->baseData, ['action' => 'create']));
        $this->model->create(array_merge($this->baseData, ['action' => 'update']));

        $rows = $this->model->getAll();
        $this->assertSame('update', $rows[0]['action']);
        $this->assertSame('create', $rows[1]['action']);
    }

    public function test_getAll_filter_by_module(): void
    {
        $this->model->create($this->baseData);                                        // module=users
        $this->model->create(array_merge($this->baseData, ['module' => 'auth', 'action' => 'login']));

        $rows = $this->model->getAll(['module' => 'auth']);
        $this->assertCount(1, $rows);
        $this->assertSame('auth', $rows[0]['module']);
    }

    public function test_getAll_filter_by_action(): void
    {
        $this->model->create($this->baseData);                                        // action=create
        $this->model->create(array_merge($this->baseData, ['action' => 'delete']));

        $rows = $this->model->getAll(['action' => 'delete']);
        $this->assertCount(1, $rows);
        $this->assertSame('delete', $rows[0]['action']);
    }

    public function test_getAll_filter_by_actor_id(): void
    {
        $this->model->create($this->baseData);                              // actor_id=1
        $this->model->create(array_merge($this->baseData, ['actor_id' => null, 'actor_label' => null]));

        $rows = $this->model->getAll(['actor_id' => 1]);
        $this->assertCount(1, $rows);
        $this->assertSame('1', (string) $rows[0]['actor_id']);
    }

    public function test_getAll_with_limit_returns_at_most_limit_rows(): void
    {
        $this->model->create($this->baseData);
        $this->model->create(array_merge($this->baseData, ['action' => 'update']));
        $this->model->create(array_merge($this->baseData, ['action' => 'delete']));

        $rows = $this->model->getAll([], 2);
        $this->assertCount(2, $rows);
    }

    public function test_getAll_without_limit_returns_all_rows(): void
    {
        $this->model->create($this->baseData);
        $this->model->create(array_merge($this->baseData, ['action' => 'update']));

        $rows = $this->model->getAll([], null);
        $this->assertCount(2, $rows);
    }

    public function test_getAll_with_limit_one_returns_most_recent(): void
    {
        $this->model->create(array_merge($this->baseData, ['action' => 'create']));
        $this->model->create(array_merge($this->baseData, ['action' => 'update']));

        $rows = $this->model->getAll([], 1);
        $this->assertCount(1, $rows);
        $this->assertSame('update', $rows[0]['action']);
    }

    public function test_getAll_filter_by_date_range(): void
    {
        $this->model->create($this->baseData);

        $today = date('Y-m-d');
        $rows  = $this->model->getAll(['date_from' => $today, 'date_to' => $today]);
        $this->assertCount(1, $rows);
    }

    public function test_getAll_date_range_excludes_outside_entries(): void
    {
        $this->model->create($this->baseData);

        // Use a past date range that should return nothing
        $rows = $this->model->getAll(['date_from' => '2000-01-01', 'date_to' => '2000-01-02']);
        $this->assertCount(0, $rows);
    }

    // -------------------------------------------------------------------------
    // getDistinctModules()
    // -------------------------------------------------------------------------

    public function test_getDistinctModules_returns_unique_modules(): void
    {
        $this->model->create($this->baseData);                                          // users
        $this->model->create(array_merge($this->baseData, ['module' => 'auth']));      // auth
        $this->model->create(array_merge($this->baseData, ['module' => 'users']));     // users again

        $modules = $this->model->getDistinctModules();
        $this->assertCount(2, $modules);
        $this->assertContains('auth', $modules);
        $this->assertContains('users', $modules);
    }

    public function test_getDistinctModules_returns_empty_when_no_entries(): void
    {
        $this->assertSame([], $this->model->getDistinctModules());
    }

    // -------------------------------------------------------------------------
    // getDistinctActions()
    // -------------------------------------------------------------------------

    public function test_getDistinctActions_returns_unique_actions(): void
    {
        $this->model->create($this->baseData);                                           // create
        $this->model->create(array_merge($this->baseData, ['action' => 'update']));     // update
        $this->model->create(array_merge($this->baseData, ['action' => 'create']));     // create again

        $actions = $this->model->getDistinctActions();
        $this->assertCount(2, $actions);
        $this->assertContains('create', $actions);
        $this->assertContains('update', $actions);
    }

    // -------------------------------------------------------------------------
    // getActorsWithLogs()
    // -------------------------------------------------------------------------

    public function test_getActorsWithLogs_returns_actors_with_entries(): void
    {
        $this->model->create($this->baseData);                                           // actor_id=1
        $this->model->create(array_merge($this->baseData, ['actor_id' => null, 'actor_label' => null]));

        $actors = $this->model->getActorsWithLogs();
        $this->assertCount(1, $actors);
        $this->assertSame('1', (string) $actors[0]['actor_id']);
    }

    public function test_getActorsWithLogs_excludes_anonymous_entries(): void
    {
        $data = array_merge($this->baseData, ['actor_id' => null, 'actor_label' => null]);
        $this->model->create($data);

        $this->assertSame([], $this->model->getActorsWithLogs());
    }

    // -------------------------------------------------------------------------
    // countToday()
    // -------------------------------------------------------------------------

    public function test_countToday_returns_zero_when_empty(): void
    {
        $this->assertSame(0, $this->model->countToday());
    }

    public function test_countToday_counts_entries_created_today(): void
    {
        $this->model->create($this->baseData);
        $this->model->create($this->baseData);

        $this->assertSame(2, $this->model->countToday());
    }

    // -------------------------------------------------------------------------
    // purgeOlderThan()
    // -------------------------------------------------------------------------

    public function test_purgeOlderThan_returns_zero_when_no_old_entries(): void
    {
        $this->model->create($this->baseData);

        $deleted = $this->model->purgeOlderThan(90);
        $this->assertSame(0, $deleted);
    }

    public function test_purgeOlderThan_deletes_old_entries(): void
    {
        // Insert a row and then back-date it directly so it appears old
        $id = $this->model->create($this->baseData);

        self::$pdo->exec(
            "UPDATE activity_logs
             SET created_at = DATE_SUB(NOW(), INTERVAL 100 DAY)
             WHERE id = {$id}"
        );

        $deleted = $this->model->purgeOlderThan(90);
        $this->assertSame(1, $deleted);
        $this->assertSame([], $this->model->getAll());
    }

    public function test_purgeOlderThan_keeps_recent_entries(): void
    {
        // One old, one new
        $oldId = $this->model->create($this->baseData);
        $this->model->create(array_merge($this->baseData, ['action' => 'update']));  // recent

        self::$pdo->exec(
            "UPDATE activity_logs
             SET created_at = DATE_SUB(NOW(), INTERVAL 100 DAY)
             WHERE id = {$oldId}"
        );

        $deleted = $this->model->purgeOlderThan(90);
        $this->assertSame(1, $deleted);
        $this->assertCount(1, $this->model->getAll());
    }
}
