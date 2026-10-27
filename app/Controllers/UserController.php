<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\PasswordReset;
use App\Models\Role;
use App\Models\User;
use App\Models\Permission;
use App\Services\AuditLogger;
use App\Services\DashboardCache;
use App\Services\ImageService;
use App\Services\LoginThrottleService;
use App\Services\MailService;

class UserController extends Controller
{
    private $userModel;
    private $permissionModel;
    private $roleModel;
    private $imageService;
    private LoginThrottleService $throttle;

    public function __construct()
    {
        $this->userModel       = new User();
        $this->permissionModel = new Permission();
        $this->roleModel       = new Role();
        $this->imageService    = new ImageService(dirname(__DIR__, 2) . '/public/uploads/users/');
        $this->throttle        = new LoginThrottleService($this->userModel);
    }

    public function index()
    {
        $currentUserId = (int) ($_SESSION['user_id'] ?? 0);
        $users         = $this->userModel->getAll();

        $this->render(
            'users/index',
            compact('users', 'currentUserId'),
            ['datatables', 'datatables-export'],
            ['users/index-users'],
            ['users/users']
        );
    }

    public function create()
    {
        $allPermissions = $this->permissionModel->getAllActive();
        $activeRoles    = $this->roleModel->getAllActive();

        $this->render(
            'users/create',
            compact('allPermissions', 'activeRoles'),
            ['select2', 'validate'],
            ['users/create-user'],
            ['users/users']
        );
    }

    public function show($id = null)
    {
        $user            = $this->getUserOrRedirect((int) $id);
        $userPermissions = $this->permissionModel->getByUserId((int) $user['id']);
        $isAdminUser     = (bool) ($user['role_is_system'] ?? false);

        $this->render(
            'users/show',
            compact('user', 'userPermissions', 'isAdminUser'),
            [],
            ['users/show-user'],
            ['users/users']
        );
    }

    public function edit($id = null)
    {
        $user                = $this->getUserOrRedirect((int) $id);
        if (!Auth::canManageUser($user)) {
            \App\Core\ErrorHandler::forbidden();
        }
        $allPermissions      = $this->permissionModel->getAllActive();
        $assignedPermissions = $this->permissionModel->getAssignedIds((int) $user['id']);
        $activeRoles         = $this->roleModel->getAllActive();
        $currentRoleId       = (int) ($user['role_id'] ?? 0);

        $this->render(
            'users/update',
            compact('user', 'allPermissions', 'assignedPermissions', 'activeRoles', 'currentRoleId'),
            ['select2', 'validate'],
            ['users/update-user'],
            ['users/users']
        );
    }

    public function profile()
    {
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        $user   = $this->userModel->getById($userId);

        if (!$user) {
            $_SESSION['message'] = 'User not found.';
            $_SESSION['icon']    = 'error';
            $this->redirect(URL . 'dashboard');
        }

        $imageSrc = URL . 'uploads/users/' . htmlspecialchars(!empty($user['image']) ? $user['image'] : 'user_default.jpg');

        $this->render(
            'users/profile',
            compact('user', 'imageSrc'),
            [],
            ['users/profile-user'],
            ['users/users']
        );
    }

    public function store()
    {
        $this->csrfCheck();

        $result = $this->save();
        $_SESSION['message'] = $result['message'];
        $_SESSION['icon']    = $result['icon'];

        $target = $result['icon'] === 'success' ? 'users' : 'users/create';
        $this->redirect(URL . $target);
    }

    public function updateAction()
    {
        $this->csrfCheck();

        $result = $this->update();
        $_SESSION['message'] = $result['message'];
        $_SESSION['icon']    = $result['icon'];

        $target = 'users';
        if ($result['icon'] !== 'success' && preg_match('/update\.php\?id=(\d+)/', $result['redirect'], $m)) {
            $target = 'users/' . $m[1] . '/edit';
        }
        $this->redirect(URL . $target);
    }

    public function processUpdateProfile()
    {
        $this->csrfCheck();

        $profileController   = new ProfileController();
        $result              = $profileController->updateProfile();
        $_SESSION['message'] = $result['message'];
        $_SESSION['icon']    = $result['icon'];

        regenerateCSRFToken();
        $this->redirect(URL . 'profile');
    }

    public function checkEmail()
    {
        $email  = trim($_POST['email'] ?? '');
        $userId = filter_var($_POST['user_id'] ?? '', FILTER_VALIDATE_INT) ?: null;

        if (!$email) {
            echo 'true';
            exit;
        }

        $exists = $this->userModel->emailExists($email, $userId);
        header('Content-Type: application/json');
        echo $exists ? json_encode('This email is already in use.') : 'true';
        exit;
    }

    public function checkDocument()
    {
        $documentType   = trim($_POST['document_type']   ?? '');
        $documentNumber = trim($_POST['document_number'] ?? '');
        $userId         = filter_var($_POST['user_id'] ?? '', FILTER_VALIDATE_INT) ?: null;

        if (!$documentType || !$documentNumber) {
            echo 'true';
            exit;
        }

        $exists = $this->userModel->documentTypeExists($documentType, $documentNumber, $userId);
        header('Content-Type: application/json');
        echo $exists ? json_encode('This document is already registered.') : 'true';
        exit;
    }

    public function toggleStatusAjax()
    {
        $this->csrfCheck();

        $userId        = filter_var($_POST['id']             ?? null, FILTER_VALIDATE_INT);
        $currentStatus = filter_var($_POST['current_status'] ?? null, FILTER_VALIDATE_INT);

        $result = $this->toggleUserStatus($userId, $currentStatus);

        // Set flash so the toast fires after location.reload()
        $_SESSION['message'] = $result['message'];
        $_SESSION['icon']    = $result['icon'];

        regenerateCSRFToken();
        $this->jsonResponse($result);
    }

    public function ajaxChangePassword()
    {
        $this->csrfCheck();
        $this->jsonResponse($this->updateProfilePasswordAjax());
    }

    public function resendInvitationAjax($id = null): void
    {
        $this->csrfCheck();

        $userId = (int) $id;

        if ($userId <= 0) {
            regenerateCSRFToken();
            $this->jsonResponse(['success' => false, 'message' => 'Invalid user ID.', 'icon' => 'error']);
        }

        $user = $this->userModel->getById($userId);

        if (!$user || (int) $user['status'] !== User::STATUS_PENDING) {
            regenerateCSRFToken();
            $this->jsonResponse(['success' => false, 'message' => 'User is not pending.', 'icon' => 'error']);
        }

        if (!Auth::canManageUser($user)) {
            regenerateCSRFToken();
            $this->jsonResponse(['success' => false, 'message' => 'You cannot manage an account with permissions you do not hold.', 'icon' => 'error']);
        }

        $token       = (new PasswordReset())->create($userId, 'invitation');
        DashboardCache::forget('pending_invitations');
        $inviterName = Auth::user()['name'] ?? 'Administrator';
        (new MailService())->sendInvitationEmail($user['email'], $token, $inviterName);

        AuditLogger::log(
            'users',
            'invite_resent',
            "Invitation resent: {$user['email']}",
            ['user_id' => $userId]
        );

        $_SESSION['message'] = 'Invitation resent successfully.';
        $_SESSION['icon']    = 'success';
        regenerateCSRFToken();
        $this->jsonResponse(['success' => true, 'message' => 'Invitation resent successfully.', 'icon' => 'success']);
    }

    public function unlockLoginAjax($id = null)
    {
        $this->csrfCheck();

        $userId = (int) $id;

        if ($userId <= 0) {
            regenerateCSRFToken();
            $this->jsonResponse(['success' => false, 'message' => 'Invalid user ID.', 'icon' => 'error']);
        }

        $target = $this->userModel->getById($userId);
        if (!$target || !Auth::canManageUser($target)) {
            regenerateCSRFToken();
            $this->jsonResponse(['success' => false, 'message' => 'You cannot manage an account with permissions you do not hold.', 'icon' => 'error']);
        }

        if ($this->throttle->unlock($userId)) {
            $_SESSION['message'] = 'Login unlocked successfully.';
            $_SESSION['icon']    = 'success';
            regenerateCSRFToken();
            $this->jsonResponse(['success' => true, 'message' => 'Login unlocked successfully.', 'icon' => 'success']);
        }

        regenerateCSRFToken();
        $this->jsonResponse(['success' => false, 'message' => 'Error unlocking the user.', 'icon' => 'error']);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function save()
    {
        $isInvite = !empty($_POST['invite']) && $_POST['invite'] === '1';
        $prepared = $this->prepareUserData($_POST);
        $data     = $this->userModel->trimInput($prepared);
        // La contraseña no se recorta: los espacios forman parte de ella.
        $data['password'] = $prepared['password'];
        $errors   = $this->userModel->validateData($data);

        if (!empty($errors)) {
            return ['success' => false, 'message' => $errors[0], 'icon' => 'error', 'redirect' => 'create.php'];
        }

        if ($this->isSystemRole($data['role_id'] ?? null) && !Auth::isAdmin()) {
            return ['success' => false, 'message' => 'Only administrators can assign the administrator role.', 'icon' => 'error', 'redirect' => 'create.php'];
        }

        if (!Auth::canGrantPermissionIds(array_map('intval', (array) ($_POST['permissions'] ?? [])))
            || !$this->canGrantRole($data['role_id'] ?? null)) {
            return ['success' => false, 'message' => 'You cannot grant permissions you do not hold.', 'icon' => 'error', 'redirect' => 'create.php'];
        }

        if (!$isInvite) {
            $confirmPassword = (string) ($_POST['confirm_password'] ?? '');
            $plainPassword   = (string) ($_POST['password'] ?? '');
            if ($plainPassword !== $confirmPassword) {
                return ['success' => false, 'message' => 'Passwords do not match.', 'icon' => 'error', 'redirect' => 'create.php'];
            }
        }

        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $imagePath = $this->imageService->processImage($_FILES['image']);
            if ($imagePath) {
                $data['image'] = $imagePath;
            } else {
                return ['success' => false, 'message' => 'Error processing image. Check format and size.', 'icon' => 'error', 'redirect' => 'create.php'];
            }
        }

        if ($this->userModel->create($data)) {
            $userId = $this->userModel->getLastInsertId();
            $this->processPermissions($userId, $_POST);

            if ($isInvite) {
                $token       = (new PasswordReset())->create($userId, 'invitation');
                DashboardCache::forget('pending_invitations');
                $inviterName = Auth::user()['name'] ?? 'Administrator';
                (new MailService())->sendInvitationEmail($data['email'], $token, $inviterName);
                AuditLogger::log(
                    'users',
                    'invite',
                    "Invitation sent: {$data['email']}",
                    ['name' => $data['name'], 'first_surname' => $data['first_surname'], 'user_id' => $userId]
                );
                regenerateCSRFToken();
                return ['success' => true, 'message' => 'Invitation sent successfully.', 'icon' => 'success', 'redirect' => 'index.php'];
            }

            AuditLogger::log(
                'users',
                'create',
                "User created: {$data['name']} {$data['first_surname']}",
                ['name' => $data['name'], 'first_surname' => $data['first_surname'], 'email' => $data['email']]
            );
            regenerateCSRFToken();
            return ['success' => true, 'message' => 'User created successfully.', 'icon' => 'success', 'redirect' => 'index.php'];
        }

        return ['success' => false, 'message' => 'Error creating user: ' . $this->userModel->getLastError(), 'icon' => 'error', 'redirect' => 'create.php'];
    }

    private function update()
    {
        $id = (int) ($_POST['user_id'] ?? 0);

        if (!$id) {
            return ['success' => false, 'message' => 'Invalid user ID.', 'icon' => 'error', 'redirect' => 'index.php'];
        }

        $currentUser = $this->userModel->getById($id);
        if (!$currentUser) {
            return ['success' => false, 'message' => 'User not found.', 'icon' => 'error', 'redirect' => 'index.php'];
        }

        if (!Auth::canManageUser($currentUser)) {
            return ['success' => false, 'message' => 'You cannot modify an account with permissions you do not hold.', 'icon' => 'error', 'redirect' => "update.php?id=$id"];
        }

        $oldImage = $currentUser['image'];
        $data     = $this->prepareUserData($_POST);
        // La contraseña se valida y guarda aparte (validateNewPassword + updatePassword).
        unset($data['password']);

        $roleChanged = (int) ($data['role_id'] ?? 0) !== (int) ($currentUser['role_id'] ?? 0);
        if ($roleChanged && $this->isSystemRole($data['role_id'] ?? null) && !Auth::isAdmin()) {
            return ['success' => false, 'message' => 'Only administrators can assign the administrator role.', 'icon' => 'error', 'redirect' => "update.php?id=$id"];
        }
        $data['status'] = (int) $currentUser['status'];

        foreach (['name', 'first_surname', 'document_type', 'document_number', 'email'] as $field) {
            if (empty($data[$field]) && isset($currentUser[$field])) {
                $data[$field] = $currentUser[$field];
            }
        }

        $data          = $this->userModel->trimInput($data);
        $data['image'] = $oldImage;

        $errors = $this->userModel->validateData($data, $id);
        if (!empty($errors)) {
            return ['success' => false, 'message' => $errors[0], 'icon' => 'error', 'redirect' => "update.php?id=$id"];
        }

        $requestedPerms = array_map('intval', (array) ($_POST['permissions'] ?? []));
        $newPerms       = array_diff($requestedPerms, array_map('intval', $this->permissionModel->getAssignedIds($id)));
        if (!Auth::canGrantPermissionIds($newPerms) || ($roleChanged && !$this->canGrantRole($data['role_id'] ?? null))) {
            return ['success' => false, 'message' => 'You cannot grant permissions you do not hold.', 'icon' => 'error', 'redirect' => "update.php?id=$id"];
        }

        $password        = (string) ($_POST['password'] ?? '');
        $confirmPassword = (string) ($_POST['confirm_password'] ?? '');
        $changePassword  = $password !== '' || $confirmPassword !== '';
        if ($changePassword) {
            $pwErrors = $this->userModel->validateNewPassword($password, $confirmPassword);
            if (!empty($pwErrors)) {
                return ['success' => false, 'message' => $pwErrors[0], 'icon' => 'error', 'redirect' => "update.php?id=$id"];
            }
        }

        // Todas las validaciones pasaron: recién ahora se toca disco y BD.
        $newImagePath = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $newImagePath = $this->imageService->processImage($_FILES['image']);
            if (!$newImagePath) {
                return ['success' => false, 'message' => 'Error processing the new image.', 'icon' => 'error', 'redirect' => "update.php?id=$id"];
            }
            $data['image'] = $newImagePath;
        }

        $updated = $this->userModel->update($id, $data);
        if ($updated && $newImagePath && $oldImage && $oldImage !== 'user_default.jpg') {
            $this->imageService->deleteImage($oldImage);
        } elseif (!$updated && $newImagePath) {
            $this->imageService->deleteImage($newImagePath);
        }

        $passwordUpdated = true;
        if ($updated && $changePassword) {
            $passwordUpdated = $this->userModel->updatePassword($id, $password);
            if ($passwordUpdated && $id === Auth::id()) {
                Auth::syncPasswordFingerprint();
            }
        }

        if ($updated && $passwordUpdated) {
            // Sin lista enviada (cliente que omite el campo) se conservan los permisos directos.
            if (isset($_POST['permissions']) || !empty($_POST['permissions_submitted'])) {
                $this->processPermissions($id, $_POST);
            }
            AuditLogger::log(
                'users',
                'update',
                "User updated: {$data['name']} {$data['first_surname']}",
                ['user_id' => $id, 'name' => $data['name'], 'first_surname' => $data['first_surname'], 'email' => $data['email']]
            );
            regenerateCSRFToken();
            return ['success' => true, 'message' => 'User updated successfully.', 'icon' => 'success', 'redirect' => 'index.php'];
        }

        $errorMsg = 'Error updating user.';
        if (!$updated) {
            $errorMsg .= ' ' . ($this->userModel->getLastError() ?: 'Unknown error.');
        }
        if (!$passwordUpdated) {
            $errorMsg .= ' Password update failed.';
        }
        return ['success' => false, 'message' => $errorMsg, 'icon' => 'error', 'redirect' => "update.php?id=$id"];
    }

    private function updateProfilePasswordAjax()
    {
        $id      = (int) ($_SESSION['user_id'] ?? 0);
        $current = (string) ($_POST['current_password'] ?? '');
        $new     = (string) ($_POST['new_password']     ?? '');
        $confirm = (string) ($_POST['confirm_password'] ?? '');

        $errors = $this->userModel->validatePasswordChange($id, $current, $new, $confirm);
        if (!empty($errors)) {
            return ['success' => false, 'message' => $errors[0]];
        }

        regenerateCSRFToken();

        if ($this->userModel->updatePassword($id, $new)) {
            Auth::syncPasswordFingerprint();
            AuditLogger::log('users', 'password_changed', 'User changed their own password', ['user_id' => $id]);
            return ['success' => true, 'message' => 'Password updated successfully.', 'csrf_token' => generateCSRFToken()];
        }

        return ['success' => false, 'message' => 'Error updating password: ' . $this->userModel->getLastError()];
    }

    private function toggleUserStatus($id, $currentStatus)
    {
        if ($id === null || $currentStatus === null) {
            return ['success' => false, 'message' => 'Invalid user ID or status.', 'icon' => 'error'];
        }

        $target = $this->userModel->getById((int) $id);
        if (!$target) {
            return ['success' => false, 'message' => 'User not found.', 'icon' => 'error'];
        }
        if (!Auth::canManageUser($target)) {
            return ['success' => false, 'message' => 'You cannot change the status of an account with permissions you do not hold.', 'icon' => 'error'];
        }
        if ((int) $id === Auth::id()) {
            return ['success' => false, 'message' => 'You cannot change your own status.', 'icon' => 'error'];
        }

        // El estado actual se lee de la BD; el del cliente no es confiable.
        $dbStatus = (int) $target['status'];
        if ($dbStatus === User::STATUS_PENDING) {
            return ['success' => false, 'message' => 'A pending user must accept the invitation first.', 'icon' => 'error'];
        }

        $activating = $dbStatus !== User::STATUS_ACTIVE;
        $ok         = $activating
            ? $this->userModel->activate($id)
            : $this->userModel->deactivate($id);

        if ($ok) {
            $action    = $activating ? 'activated' : 'deactivated';
            $auditVerb = $activating ? 'activate' : 'deactivate';
            $newStatus = $activating ? 1 : 0;
            AuditLogger::log(
                'users',
                $auditVerb,
                "User {$action}: ID {$id}",
                ['user_id' => $id, 'new_status' => $newStatus]
            );
            return ['success' => true, 'message' => "User $action successfully.", 'icon' => 'success'];
        }

        return ['success' => false, 'message' => 'Error changing user status: ' . $this->userModel->getLastError(), 'icon' => 'error'];
    }

    /** Asignar un rol concede sus permisos: el actor debe poseerlos todos. */
    private function canGrantRole($roleId): bool
    {
        if (empty($roleId)) {
            return true;
        }
        return Auth::canGrantPermissionIds($this->roleModel->getAssignedPermissionIds((int) $roleId));
    }

    private function isSystemRole($roleId): bool
    {
        if (empty($roleId)) {
            return false;
        }
        $role = $this->roleModel->getById((int) $roleId);
        return $role && !empty($role['is_system']);
    }

    private function getUserOrRedirect($id)
    {
        if ($id <= 0) {
            $_SESSION['message'] = 'Invalid user ID.';
            $_SESSION['icon']    = 'error';
            $this->redirect(URL . 'users');
        }

        $user = $this->userModel->getById($id);
        if (!$user) {
            $_SESSION['message'] = 'User not found.';
            $_SESSION['icon']    = 'error';
            $this->redirect(URL . 'users');
        }

        return $user;
    }

    private function prepareUserData($postData)
    {
        $isInvite = !empty($postData['invite']) && $postData['invite'] === '1';

        return [
            'name'            => trim($postData['name']            ?? ''),
            'first_surname'   => trim($postData['first_surname']   ?? ''),
            'second_surname'  => !empty($postData['second_surname']) ? trim($postData['second_surname']) : null,
            'document_type'   => trim($postData['document_type']   ?? ''),
            'document_number' => trim($postData['document_number'] ?? ''),
            'address'         => !empty($postData['address']) ? trim($postData['address']) : null,
            'phone'           => !empty($postData['phone']) ? trim($postData['phone']) : null,
            'email'           => trim($postData['email']    ?? ''),
            'password'        => $isInvite
                ? password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT)
                : (string) ($postData['password'] ?? ''),
            'status'          => $isInvite
                ? User::STATUS_PENDING
                : (isset($postData['status']) ? (int) $postData['status'] : User::STATUS_ACTIVE),
            'role_id'         => !empty($postData['role_id']) ? (int) $postData['role_id'] : null,
            'image'           => null,
        ];
    }

    private function processPermissions($userId, $postData)
    {
        $selectedIds  = $postData['permissions'] ?? [];
        $this->permissionModel->syncForUser($userId, $selectedIds);

        $this->userModel->updatePermissionsTimestamp($userId);

        if ((int) $userId === Auth::id()) {
            Auth::reloadPermissions();
        }
    }
}
