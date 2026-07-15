<?php

/**
 * AuditLogger
 *
 * Central static service for recording activity log events.
 * Invocable from any layer (controllers, Auth, other services) without
 * injecting a dependency. Never throws — a failure in the logger must never
 * break the surrounding business action.
 *
 * Usage (simplest form, actor resolved automatically from session):
 *   AuditLogger::log('users', 'create', 'User created: Jane Doe', ['email' => 'jane@example.com']);
 *
 * Usage without session (e.g. login_failed):
 *   AuditLogger::log('auth', 'login_failed', 'Failed login attempt', ['identifier' => $id], null, $id);
 *
 * @package ProyectoBase
 * @subpackage App\Services
 * @author jandrescodes
 * @version 1.0
 */

namespace App\Services;

use App\Core\Auth;
use App\Models\ActivityLog;

class AuditLogger
{
    /**
     * Records an activity log entry.
     *
     * @param string      $module      Logical area: auth | users | roles | permissions
     * @param string      $action      Specific event: login | login_failed | create | update | …
     * @param string      $description Human-readable summary (stored as-is, no HTML)
     * @param array       $details     Arbitrary key-value context (never include passwords/tokens)
     * @param int|null    $actorId     Override actor ID; null = resolve from session or stay anonymous
     * @param string|null $actorLabel  Override actor label; null = build from session user data
     * @return void
     */
    public static function log(
        string $module,
        string $action,
        string $description = '',
        array $details = [],
        ?int $actorId = null,
        ?string $actorLabel = null
    ): void {
        try {
            // Resolve actor from the active session when not explicitly provided
            if ($actorId === null) {
                $actorId = Auth::id();
            }

            if ($actorLabel === null && $actorId !== null) {
                $sessionUser = Auth::user();
                if ($sessionUser) {
                    $actorLabel = trim($sessionUser['name'] ?? '');
                }
            }

            $model = new ActivityLog();
            $model->create([
                'actor_id'    => $actorId,
                'actor_label' => $actorLabel,
                'module'      => $module,
                'action'      => $action,
                'description' => $description !== '' ? $description : null,
                'details'     => $details !== [] ? $details : null,
                'ip_address'  => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent'  => isset($_SERVER['HTTP_USER_AGENT'])
                    ? substr($_SERVER['HTTP_USER_AGENT'], 0, 255)
                    : null,
            ]);

            DashboardCache::forget('audit_today');
        } catch (\Throwable) {
            // Silent fail — the log must never break business logic
        }
    }
}
