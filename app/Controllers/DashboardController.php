<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\ActivityLog;
use App\Models\PasswordReset;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\DashboardCache;

class DashboardController extends Controller
{
    public function index(): void
    {
        $userModel       = new User();
        $permissionModel = new Permission();
        $roleModel       = new Role();
        $activityLogModel = new ActivityLog();

        $userStats    = DashboardCache::remember('user_stats', fn () => $userModel->getStatistics());
        $permStats    = DashboardCache::remember('perm_stats', fn () => $permissionModel->getStatistics());
        $roleStats    = DashboardCache::remember('role_stats', fn () => $roleModel->getStatistics());
        $recentUsers  = DashboardCache::remember('recent_users', fn () => $userModel->getRecent(5));
        $usersByStatus = DashboardCache::remember('users_by_status', fn () => $userModel->getUsersByStatus());
        $topPerms     = DashboardCache::remember('top_permissions', fn () => $permissionModel->getTopAssigned(5));
        $usersByMonth = DashboardCache::remember('users_by_month', fn () => $userModel->getUsersByMonth(6));
        $auditToday   = DashboardCache::remember('audit_today', fn () => ['count' => $activityLogModel->countToday()])['count'];

        $passwordResetModel = new PasswordReset();
        $pendingInvitations = DashboardCache::remember('pending_invitations', fn () => ['count' => $passwordResetModel->getPendingInvitationsCount()])['count'];
        $resetsThisWeek     = DashboardCache::remember('resets_this_week', fn () => ['count' => $passwordResetModel->getResetRequestsThisWeek()])['count'];

        $this->render(
            'dashboard/index',
            [
                'userStats'            => $userStats,
                'permStats'            => $permStats,
                'recentUsers'          => $recentUsers,
                'roleStats'            => $roleStats,
                'auditToday'           => $auditToday,
                'pendingInvitations'   => $pendingInvitations,
                'resetsThisWeek'       => $resetsThisWeek,
                'canManageUsers'       => Auth::hasPermission('users'),
                'canManagePermissions' => Auth::hasPermission('permissions'),
                'canManageRoles'       => Auth::hasPermission('roles'),
                'canViewAuditLog'      => Auth::hasPermission('audit_log'),
                'chartData'            => [
                    'usersByStatus' => $usersByStatus,
                    'topPerms'      => $topPerms,
                    'usersByMonth'  => $usersByMonth,
                ],
            ],
            ['chart'],
            ['dashboard/index-dashboard'],
            ['dashboard/dashboard']
        );
    }
}
