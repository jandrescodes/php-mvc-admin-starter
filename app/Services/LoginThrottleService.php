<?php

/**
 * LoginThrottleService
 *
 * Orchestrates brute-force login protection using throttle columns stored
 * directly on the users table. Reads configuration from .env:
 *   LOGIN_MAX_ATTEMPTS   — max consecutive failures before lockout (default 5)
 *   LOGIN_LOCKOUT_MINUTES — lockout duration in minutes (default 15)
 *
 * @package ProyectoBase
 * @subpackage App\Services
 * @author jandrescodes
 * @version 1.0
 */

namespace App\Services;

use App\Models\User;

class LoginThrottleService
{
    /**
     * @var User
     */
    private User $userModel;

    public function __construct(User $userModel)
    {
        $this->userModel = $userModel;
    }

    /**
     * Checks whether a user is currently locked out.
     *
     * @param  array $user  User row (must contain 'id')
     * @return array{locked: bool, remaining_seconds: int, message: string}
     */
    public function isLocked(array $user): array
    {
        $status = $this->userModel->getLockStatus((int) $user['id']);

        $message = '';
        if ($status['locked']) {
            $message = 'Tu cuenta está bloqueada por demasiados intentos fallidos. '
                     . 'Intenta de nuevo en ' . $this->formatRemaining($status['remaining_seconds']) . '.';
        }

        return [
            'locked'            => $status['locked'],
            'remaining_seconds' => $status['remaining_seconds'],
            'message'           => $message,
        ];
    }

    /**
     * Registers a failed login attempt for the given user.
     *
     * @param  array $user  User row (must contain 'id')
     * @return void
     */
    public function registerFailure(array $user): void
    {
        $fresh = $this->userModel->recordFailure((int) $user['id']);

        // Log account_locked exactly once — when this failure just crossed the threshold.
        // isLocked() is checked before registerFailure() is called, so if locked_until
        // is set now, the lock was triggered by this very failure.
        if (!empty($fresh['locked_until'])) {
            $label = trim(($user['name'] ?? '') . ' ' . ($user['first_surname'] ?? ''));
            AuditLogger::log(
                'auth',
                'account_locked',
                "Account locked after too many failed attempts: {$label}",
                [
                    'login_attempts' => (int) ($fresh['login_attempts'] ?? 0),
                    'locked_until'   => $fresh['locked_until'],
                ],
                (int) $user['id'],
                $label
            );
        }
    }

    /**
     * Clears throttle counters after a successful login.
     *
     * @param  array $user  User row (must contain 'id')
     * @return void
     */
    public function clearOnSuccess(array $user): void
    {
        $this->userModel->clearAttempts((int) $user['id']);
    }

    /**
     * Unlocks a user manually (admin action).
     *
     * @param  int  $userId
     * @return bool
     */
    public function unlock(int $userId): bool
    {
        $ok = $this->userModel->unlock($userId);

        if ($ok) {
            AuditLogger::log(
                'auth',
                'account_unlocked',
                "Account manually unlocked for user ID: {$userId}",
                ['target_user_id' => $userId]
            );
        }

        return $ok;
    }

    /**
     * Converts a number of seconds into a human-readable string.
     * Example: 270 → "4 minutos 30 segundos"
     *
     * @param  int    $seconds
     * @return string
     */
    public function formatRemaining(int $seconds): string
    {
        if ($seconds <= 0) {
            return '0 segundos';
        }

        $minutes = intdiv($seconds, 60);
        $secs    = $seconds % 60;

        $parts = [];

        if ($minutes > 0) {
            $parts[] = $minutes . ' ' . ($minutes === 1 ? 'minuto' : 'minutos');
        }

        if ($secs > 0) {
            $parts[] = $secs . ' ' . ($secs === 1 ? 'segundo' : 'segundos');
        }

        return implode(' ', $parts);
    }
}
