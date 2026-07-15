<?php

/**
 * Role Controller
 *
 * Handles all HTTP actions for the Roles module (index, CRUD AJAX, check-name).
 *
 * @package ProyectoBase
 * @subpackage App\Controllers
 * @author jandrescodes
 * @version 1.0
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Services\AuditLogger;

class RoleController extends Controller
{
    /** @var Role */
    private $roleModel;

    public function __construct()
    {
        $this->roleModel = new Role();
    }

    /**
     * Renders the roles index page with stats and full list.
     */
    public function pageIndex()
    {
        $roles      = $this->roleModel->getAllWithUserCount();
        $statistics = $this->roleModel->getStatistics();

        $this->render(
            'roles/index',
            compact('roles', 'statistics'),
            ['datatables', 'datatables-export', 'validate'],
            ['roles/modal-role', 'roles/index-roles']
        );
    }

    /**
     * AJAX — creates a new role.
     */
    public function create()
    {
        $this->csrfCheck();

        $data = $this->roleModel->trimInput([
            'name'        => $_POST['name']        ?? '',
            'description' => $_POST['description'] ?? '',
        ]);

        if (empty($data['name'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Role name is required.']);
        }

        if ($this->roleModel->create($data)) {
            $newRoleId = $this->roleModel->getLastInsertId();
            AuditLogger::log(
                'roles',
                'create',
                "Role created: {$data['name']}",
                ['role_id' => $newRoleId, 'name' => $data['name']]
            );
            regenerateCSRFToken();
            $_SESSION['message'] = 'Role created successfully.';
            $_SESSION['icon']    = 'success';
            $this->jsonResponse([
                'success' => true,
                'message' => 'Role created successfully.',
                'role'    => [
                    'id'          => $newRoleId,
                    'name'        => $data['name'],
                    'status'      => 1,
                    'total_users' => 0,
                ],
            ]);
        }

        $this->jsonResponse(['success' => false, 'message' => $this->roleModel->getLastError()]);
    }

    /**
     * AJAX — updates an existing role.
     */
    public function update()
    {
        $this->csrfCheck();

        $id = (int) ($_POST['id'] ?? 0);

        if (!$id) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid role ID.']);
        }

        $current = $this->roleModel->getById($id);
        if (!$current) {
            $this->jsonResponse(['success' => false, 'message' => 'Role not found.']);
        }

        $data = $this->roleModel->trimInput([
            'name'        => $_POST['name']        ?? '',
            'description' => $_POST['description'] ?? '',
        ]);

        if (empty($data['name'])) {
            $data['name'] = $current['name'];
        }

        if ($this->roleModel->update($id, $data)) {
            AuditLogger::log(
                'roles',
                'update',
                "Role updated: {$data['name']}",
                ['role_id' => $id, 'name' => $data['name'], 'description' => $data['description'] ?? null]
            );
            regenerateCSRFToken();
            $_SESSION['message'] = 'Role updated successfully.';
            $_SESSION['icon']    = 'success';
            $this->jsonResponse([
                'success' => true,
                'message' => 'Role updated successfully.',
                'role'    => [
                    'id'          => $id,
                    'name'        => $data['name'],
                    'status'      => (int) $current['status'],
                    'total_users' => $this->roleModel->countUsers($id),
                ],
            ]);
        }

        $this->jsonResponse(['success' => false, 'message' => $this->roleModel->getLastError()]);
    }

    /**
     * AJAX — toggles the active/inactive status of a role.
     * Rejects deactivation when the role has assigned users.
     */
    public function toggleStatus()
    {
        $this->csrfCheck();

        $id            = filter_var($_POST['id']             ?? null, FILTER_VALIDATE_INT);
        $currentStatus = filter_var($_POST['current_status'] ?? null, FILTER_VALIDATE_INT);

        if (!$id || $currentStatus === false) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid data.']);
        }

        if ($currentStatus == 1 && $this->roleModel->countUsers($id) > 0) {
            $this->jsonResponse(['success' => false, 'message' => 'Cannot deactivate this role because it has assigned users.']);
        }

        $newStatus = $currentStatus == 1 ? 0 : 1;

        if ($this->roleModel->updateStatus($id, $newStatus)) {
            $label = $newStatus == 1 ? 'activated' : 'deactivated';
            AuditLogger::log(
                'roles',
                $newStatus == 1 ? 'activate' : 'deactivate',
                "Role {$label}: ID {$id}",
                ['role_id' => $id, 'new_status' => $newStatus]
            );
            regenerateCSRFToken();
            $_SESSION['message'] = "Role {$label} successfully.";
            $_SESSION['icon']    = 'success';
            $this->jsonResponse([
                'success'    => true,
                'message'    => "Role {$label} successfully.",
                'new_status' => $newStatus,
            ]);
        }

        $this->jsonResponse(['success' => false, 'message' => 'Error changing role status: ' . $this->roleModel->getLastError()]);
    }

    /**
     * Renders the role detail page with permission assignment.
     */
    public function detail($id = null)
    {
        $id   = (int) $id;
        $role = $this->roleModel->getById($id);

        if (!$role) {
            $_SESSION['message'] = 'Role not found.';
            $_SESSION['icon']    = 'error';
            $this->redirect(URL . 'roles');
        }

        $allPermissions = (new Permission())->getAllActive();
        $assignedIds    = array_map('intval', $this->roleModel->getAssignedPermissionIds($id));

        $this->render(
            'roles/detail',
            compact('role', 'allPermissions', 'assignedIds'),
            ['validate'],
            ['roles/detail-role']
        );
    }

    /**
     * AJAX — replaces all permissions for a role and invalidates affected user caches.
     */
    public function syncPermissions()
    {
        $this->csrfCheck();

        $roleId      = (int) ($_POST['role_id'] ?? 0);
        $permissions = array_map('intval', (array) ($_POST['permissions'] ?? []));

        if (!$roleId) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid role ID.']);
        }

        if ($this->roleModel->syncPermissions($roleId, $permissions)) {
            AuditLogger::log(
                'roles',
                'sync_permissions',
                "Role permissions synced: role ID {$roleId}",
                ['role_id' => $roleId, 'permission_ids' => $permissions, 'total' => count($permissions)]
            );
            regenerateCSRFToken();
            $_SESSION['message'] = 'Permissions updated successfully.';
            $_SESSION['icon']    = 'success';
            $this->jsonResponse(['success' => true, 'message' => 'Permissions updated successfully.']);
        }

        $this->jsonResponse(['success' => false, 'message' => $this->roleModel->getLastError()]);
    }

    /**
     * AJAX (remote validation) — checks whether a role name is already taken.
     * Returns true (JSON) if available, or an error string if taken.
     */
    public function checkName()
    {
        $name   = trim($_POST['name']    ?? '');
        $roleId = filter_var($_POST['role_id'] ?? '', FILTER_VALIDATE_INT) ?: null;

        if (!$name) {
            echo 'true';
            exit;
        }

        $exists = $this->roleModel->nameExists($name, $roleId);
        header('Content-Type: application/json');
        echo $exists ? json_encode('This role name already exists.') : 'true';
        exit;
    }
}
