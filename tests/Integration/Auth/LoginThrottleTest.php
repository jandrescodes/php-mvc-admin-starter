<?php

declare(strict_types=1);

namespace Tests\Integration\Auth;

use App\Models\User;
use App\Services\LoginThrottleService;
use Tests\IntegrationTestCase;

/**
 * Integration tests for brute-force login throttling.
 *
 * Covers:
 *  - User::recordFailure()      — counter increment and lockout trigger
 *  - User::getLockStatus()      — active lock, expired lock, no lock
 *  - User::clearAttempts()      — reset after successful login
 *  - User::unlock()             — manual admin unlock
 *  - LoginThrottleService       — isLocked message, formatRemaining
 */
class LoginThrottleTest extends IntegrationTestCase
{
    private User                 $userModel;
    private LoginThrottleService $throttle;

    /** User row from minimal_seed (id=2, normal user) */
    private array $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userModel = new User();
        $this->throttle  = new LoginThrottleService($this->userModel);

        $this->user = $this->userModel->getById(2);
    }

    // =========================================================================
    // User::recordFailure
    // =========================================================================

    public function test_recordFailure_incrementsLoginAttempts(): void
    {
        $this->userModel->recordFailure(2);

        $row = self::$pdo->query('SELECT login_attempts FROM users WHERE id = 2')->fetch();
        $this->assertSame(1, (int) $row['login_attempts']);
    }

    public function test_recordFailure_setsLastAttemptAt(): void
    {
        $this->userModel->recordFailure(2);

        $row = self::$pdo->query('SELECT last_attempt_at FROM users WHERE id = 2')->fetch();
        $this->assertNotNull($row['last_attempt_at']);
    }

    public function test_recordFailure_doesNotSetLockedUntilBeforeMaxAttempts(): void
    {
        $max = (int) env('LOGIN_MAX_ATTEMPTS', 5);

        for ($i = 0; $i < $max - 1; $i++) {
            $this->userModel->recordFailure(2);
        }

        $row = self::$pdo->query('SELECT locked_until FROM users WHERE id = 2')->fetch();
        $this->assertNull($row['locked_until']);
    }

    public function test_recordFailure_setsLockedUntilOnMaxAttempts(): void
    {
        $max = (int) env('LOGIN_MAX_ATTEMPTS', 5);

        for ($i = 0; $i < $max; $i++) {
            $this->userModel->recordFailure(2);
        }

        $row = self::$pdo->query('SELECT locked_until FROM users WHERE id = 2')->fetch();
        $this->assertNotNull($row['locked_until']);
        $this->assertGreaterThan(time(), strtotime($row['locked_until']));
    }

    public function test_recordFailure_returnsUpdatedRow(): void
    {
        $result = $this->userModel->recordFailure(2);

        $this->assertArrayHasKey('login_attempts', $result);
        $this->assertArrayHasKey('locked_until', $result);
        $this->assertArrayHasKey('last_attempt_at', $result);
        $this->assertSame(1, (int) $result['login_attempts']);
    }

    // =========================================================================
    // User::getLockStatus
    // =========================================================================

    public function test_getLockStatus_returnsNotLockedForFreshUser(): void
    {
        $status = $this->userModel->getLockStatus(2);

        $this->assertFalse($status['locked']);
        $this->assertSame(0, $status['remaining_seconds']);
    }

    public function test_getLockStatus_returnsLockedAfterMaxAttempts(): void
    {
        $max = (int) env('LOGIN_MAX_ATTEMPTS', 5);

        for ($i = 0; $i < $max; $i++) {
            $this->userModel->recordFailure(2);
        }

        $status = $this->userModel->getLockStatus(2);

        $this->assertTrue($status['locked']);
        $this->assertGreaterThan(0, $status['remaining_seconds']);
    }

    public function test_getLockStatus_returnsNotLockedWhenLockedUntilIsExpired(): void
    {
        // Manually set locked_until to the past
        self::$pdo->exec("UPDATE users SET locked_until = DATE_SUB(NOW(), INTERVAL 1 MINUTE) WHERE id = 2");

        $status = $this->userModel->getLockStatus(2);

        $this->assertFalse($status['locked']);
        $this->assertSame(0, $status['remaining_seconds']);
    }

    // =========================================================================
    // User::clearAttempts
    // =========================================================================

    public function test_clearAttempts_resetsAllThrottleColumns(): void
    {
        $max = (int) env('LOGIN_MAX_ATTEMPTS', 5);
        for ($i = 0; $i < $max; $i++) {
            $this->userModel->recordFailure(2);
        }

        $this->userModel->clearAttempts(2);

        $row = self::$pdo->query('SELECT login_attempts, locked_until, last_attempt_at FROM users WHERE id = 2')->fetch();
        $this->assertSame(0, (int) $row['login_attempts']);
        $this->assertNull($row['locked_until']);
        $this->assertNull($row['last_attempt_at']);
    }

    // =========================================================================
    // User::unlock
    // =========================================================================

    public function test_unlock_resetsThrottleColumnsAndReturnsTrue(): void
    {
        $max = (int) env('LOGIN_MAX_ATTEMPTS', 5);
        for ($i = 0; $i < $max; $i++) {
            $this->userModel->recordFailure(2);
        }

        $result = $this->userModel->unlock(2);

        $this->assertTrue($result);

        $row = self::$pdo->query('SELECT login_attempts, locked_until, last_attempt_at FROM users WHERE id = 2')->fetch();
        $this->assertSame(0, (int) $row['login_attempts']);
        $this->assertNull($row['locked_until']);
        $this->assertNull($row['last_attempt_at']);
    }

    // =========================================================================
    // LoginThrottleService::isLocked
    // =========================================================================

    public function test_isLocked_returnsFalseForFreshUser(): void
    {
        $result = $this->throttle->isLocked($this->user);

        $this->assertFalse($result['locked']);
        $this->assertSame(0, $result['remaining_seconds']);
        $this->assertSame('', $result['message']);
    }

    public function test_isLocked_returnsTrueWithMessageWhenLocked(): void
    {
        $max = (int) env('LOGIN_MAX_ATTEMPTS', 5);
        for ($i = 0; $i < $max; $i++) {
            $this->userModel->recordFailure(2);
        }

        // Refresh user row so locked_until is populated
        $user   = $this->userModel->getById(2);
        $result = $this->throttle->isLocked($user);

        $this->assertTrue($result['locked']);
        $this->assertGreaterThan(0, $result['remaining_seconds']);
        $this->assertStringContainsString('bloqueada', $result['message']);
        $this->assertStringContainsString('minuto', $result['message']);
    }

    public function test_isLocked_returnsFalseWhenLockExpired(): void
    {
        self::$pdo->exec("UPDATE users SET locked_until = DATE_SUB(NOW(), INTERVAL 1 MINUTE) WHERE id = 2");

        $user   = $this->userModel->getById(2);
        $result = $this->throttle->isLocked($user);

        $this->assertFalse($result['locked']);
        $this->assertSame('', $result['message']);
    }

    // =========================================================================
    // LoginThrottleService::formatRemaining
    // =========================================================================

    public function test_formatRemaining_returnsMinutesAndSeconds(): void
    {
        $this->assertSame('4 minutos 30 segundos', $this->throttle->formatRemaining(270));
    }

    public function test_formatRemaining_returnsOnlyMinutesWhenNoRemainingSeconds(): void
    {
        $this->assertSame('5 minutos', $this->throttle->formatRemaining(300));
    }

    public function test_formatRemaining_returnsOnlySeconds(): void
    {
        $this->assertSame('45 segundos', $this->throttle->formatRemaining(45));
    }

    public function test_formatRemaining_returnsZeroSecondsForZeroInput(): void
    {
        $this->assertSame('0 segundos', $this->throttle->formatRemaining(0));
    }

    public function test_formatRemaining_usesSingularForOneMinute(): void
    {
        $this->assertSame('1 minuto', $this->throttle->formatRemaining(60));
    }

    public function test_formatRemaining_usesSingularForOneSecond(): void
    {
        $this->assertSame('1 segundo', $this->throttle->formatRemaining(1));
    }
}
