<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Permission;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\DashboardCache;

class PermissionController extends Controller
{
    private $permissionModel;

    public function __construct()
    {
        $this->permissionModel = new Permission();
    }

    public function pageIndex()
    {
        $permissions = $this->permissionModel->getAllWithUserCount();
        $statistics  = $this->permissionModel->getStatistics();

        $this->render(
            'permissions/index',
            compact('permissions', 'statistics'),
            ['datatables', 'datatables-export', 'validate'],
            ['permissions/modal-permission', 'permissions/index-permissions'],
            ['permissions/permissions']
        );
    }

    public function detail($id = null)
    {
        $permission = $this->getPermissionOrRedirect((int) $id);

        $users           = $permission['users'] ?? [];
        $usersWithoutPerm = $this->permissionModel->getUsersWithoutPermission((int) $permission['id']);
        $isInactive      = ((int) $permission['status']) === 0;
        $permissionId    = (int) $permission['id'];

        $this->render(
            'permissions/detail',
            compact('permission', 'users', 'usersWithoutPerm', 'isInactive', 'permissionId'),
            ['datatables', 'select2', 'validate'],
            ['permissions/modal-permission', 'permissions/detail-permission'],
            ['permissions/permissions']
        );
    }

    public function create()
    {
        $this->csrfCheck();

        $data = [
            'name'        => trim($_POST['name']        ?? ''),
            'description' => trim($_POST['description'] ?? '') ?: null,
        ];

        $data = $this->permissionModel->trimInput($data);

        if (empty($data['name'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Permission name is required.']);
        }

        if ($this->permissionModel->create($data)) {
            $newPermId = $this->permissionModel->getLastInsertId();
            AuditLogger::log(
                'permissions',
                'create',
                "Permission created: {$data['name']}",
                ['permission_id' => $newPermId, 'name' => $data['name']]
            );
            regenerateCSRFToken();
            $_SESSION['message'] = 'Permission created successfully.';
            $_SESSION['icon']    = 'success';
            $this->jsonResponse([
                'success'    => true,
                'message'    => 'Permission created successfully.',
                'permission' => [
                    'id'          => $newPermId,
                    'name'        => $data['name'],
                    'status'      => 1,
                    'total_users' => 0,
                ],
            ]);
        }

        $this->jsonResponse(['success' => false, 'message' => $this->permissionModel->getLastError()]);
    }

    public function update()
    {
        $this->csrfCheck();

        $id = (int) ($_POST['id'] ?? 0);

        if (!$id) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid permission ID.']);
        }

        $current = $this->permissionModel->getById($id);
        if (!$current) {
            $this->jsonResponse(['success' => false, 'message' => 'Permission not found.']);
        }

        $data = [
            'name'        => trim($_POST['name']        ?? '') ?: $current['name'],
            'description' => trim($_POST['description'] ?? '') ?: $current['description'],
        ];

        $data = $this->permissionModel->trimInput($data);

        if (empty($data['name'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Permission name is required.']);
        }

        if ($this->permissionModel->update($id, $data)) {
            AuditLogger::log(
                'permissions',
                'update',
                "Permission updated: {$data['name']}",
                ['permission_id' => $id, 'name' => $data['name']]
            );
            regenerateCSRFToken();
            $_SESSION['message'] = 'Permission updated successfully.';
            $_SESSION['icon']    = 'success';
            $this->jsonResponse([
                'success'    => true,
                'message'    => 'Permission updated successfully.',
                'permission' => [
                    'id'          => $id,
                    'name'        => $data['name'],
                    'status'      => $current['status'],
                    'total_users' => $this->permissionModel->countUsers($id),
                ],
            ]);
        }

        $this->jsonResponse(['success' => false, 'message' => $this->permissionModel->getLastError()]);
    }

    public function toggleStatus()
    {
        $this->csrfCheck();

        $id            = filter_var($_POST['id']             ?? null, FILTER_VALIDATE_INT);
        $currentStatus = filter_var($_POST['current_status'] ?? null, FILTER_VALIDATE_INT);

        if (!$id || $currentStatus === false) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid data.']);
        }

        if ($currentStatus == 1 && $this->permissionModel->countUsers($id) > 0) {
            $this->jsonResponse(['success' => false, 'message' => 'Cannot deactivate this permission because it has assigned users.']);
        }

        $newStatus = $currentStatus == 1 ? 0 : 1;

        if ($this->permissionModel->updateStatus($id, $newStatus)) {
            $label = $newStatus == 1 ? 'activated' : 'deactivated';
            AuditLogger::log(
                'permissions',
                $newStatus == 1 ? 'activate' : 'deactivate',
                "Permission {$label}: ID {$id}",
                ['permission_id' => $id, 'new_status' => $newStatus]
            );
            regenerateCSRFToken();
            $_SESSION['message'] = "Permission $label successfully.";
            $_SESSION['icon']    = 'success';
            $this->jsonResponse([
                'success'    => true,
                'message'    => "Permission $label successfully.",
                'new_status' => $newStatus,
            ]);
        }

        $this->jsonResponse(['success' => false, 'message' => 'Error changing permission status: ' . $this->permissionModel->getLastError()]);
    }

    public function assignUser()
    {
        $this->csrfCheck();

        $userId       = filter_var($_POST['user_id']       ?? 0, FILTER_VALIDATE_INT);
        $permissionId = filter_var($_POST['permission_id'] ?? 0, FILTER_VALIDATE_INT);

        if (!$userId || !$permissionId) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid data.']);
        }

        $ok = $this->permissionModel->assign($userId, $permissionId);
        if ($ok) {
            (new User())->updatePermissionsTimestamp($userId);
            DashboardCache::forget('perm_stats');
            DashboardCache::forget('top_permissions');
            AuditLogger::log(
                'permissions',
                'assign_user',
                "Permission assigned to user",
                ['permission_id' => $permissionId, 'target_user_id' => $userId]
            );
            regenerateCSRFToken();
            $_SESSION['message'] = 'User assigned successfully.';
            $_SESSION['icon']    = 'success';
        }

        $this->jsonResponse(['success' => (bool) $ok, 'message' => $ok ? 'User assigned successfully.' : 'Error assigning user.']);
    }

    public function revokeUser()
    {
        $this->csrfCheck();

        $userId       = filter_var($_POST['user_id']       ?? 0, FILTER_VALIDATE_INT);
        $permissionId = filter_var($_POST['permission_id'] ?? 0, FILTER_VALIDATE_INT);

        if (!$userId || !$permissionId) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid data.']);
        }

        $ok = $this->permissionModel->revoke($userId, $permissionId);
        if ($ok) {
            (new User())->updatePermissionsTimestamp($userId);
            DashboardCache::forget('perm_stats');
            DashboardCache::forget('top_permissions');
            AuditLogger::log(
                'permissions',
                'revoke_user',
                "Permission revoked from user",
                ['permission_id' => $permissionId, 'target_user_id' => $userId]
            );
            regenerateCSRFToken();
            $_SESSION['message'] = 'Permission revoked successfully.';
            $_SESSION['icon']    = 'success';
        }

        $this->jsonResponse(['success' => (bool) $ok, 'message' => $ok ? 'Permission revoked successfully.' : 'Error revoking permission.']);
    }

    public function getUsersWithout()
    {
        $permissionId = filter_var($_GET['permission_id'] ?? 0, FILTER_VALIDATE_INT);
        if (!$permissionId) {
            $this->jsonResponse([]);
        }

        $this->jsonResponse($this->permissionModel->getUsersWithoutFormatted($permissionId));
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function getPermissionOrRedirect($id)
    {
        if ($id <= 0) {
            $_SESSION['message'] = 'Invalid permission ID.';
            $_SESSION['icon']    = 'error';
            $this->redirect(URL . 'permissions');
        }

        $permission = $this->permissionModel->getById($id);
        if (!$permission) {
            $_SESSION['message'] = 'Permission not found.';
            $_SESSION['icon']    = 'error';
            $this->redirect(URL . 'permissions');
        }

        $permission['total_users'] = $this->permissionModel->countUsers($id);
        $permission['users']       = $this->permissionModel->getUsersByPermission($id);

        return $permission;
    }

    public function checkName()
    {
        $name         = trim($_POST['name']          ?? '');
        $permissionId = filter_var($_POST['permission_id'] ?? '', FILTER_VALIDATE_INT) ?: null;

        if (!$name) {
            echo 'true';
            exit;
        }

        $exists = $this->permissionModel->nameExists($name, $permissionId);
        header('Content-Type: application/json');
        echo $exists ? json_encode('This permission name already exists.') : 'true';
        exit;
    }

}
