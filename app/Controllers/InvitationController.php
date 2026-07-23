<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\PasswordReset;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\DashboardCache;

class InvitationController extends Controller
{
    private User          $userModel;
    private PasswordReset $resets;

    public function __construct()
    {
        $this->userModel = new User();
        $this->resets    = new PasswordReset();
    }

    public function showAcceptForm(): void
    {
        $token = trim($_GET['token'] ?? '');

        if (empty($token)) {
            $_SESSION['message'] = 'Invalid or missing invitation link.';
            $_SESSION['icon']    = 'error';
            $this->redirect(URL . 'login');
        }

        $row = $this->resets->findValidByToken($token, 'invitation');

        if (!$row) {
            $_SESSION['message'] = 'The invitation link has expired or is invalid.';
            $_SESSION['icon']    = 'error';
            $this->redirect(URL . 'login');
        }

        $this->renderStandalone('auth/accept_invitation', compact('token'), 'Accept Invitation', ['accept-invitation']);
    }

    public function acceptInvitation(): void
    {
        $this->csrfCheck();

        $token    = $_POST['token']            ?? '';
        $password = $_POST['password']         ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';

        if (empty($token)) {
            $_SESSION['message'] = 'Invalid or missing invitation link.';
            $_SESSION['icon']    = 'error';
            $this->redirect(URL . 'login');
        }

        $pwErrors = $this->userModel->validateNewPassword($password, $confirm);
        if (empty($pwErrors) && strlen($password) < 8) {
            $pwErrors[] = 'Password must be at least 8 characters.';
        }
        if (!empty($pwErrors)) {
            $_SESSION['message'] = $pwErrors[0];
            $_SESSION['icon']    = 'error';
            $this->redirect(URL . 'accept-invitation?token=' . urlencode($token));
        }

        $row = $this->resets->findValidByToken($token, 'invitation');

        if (!$row) {
            $_SESSION['message'] = 'The invitation link has expired or is invalid.';
            $_SESSION['icon']    = 'error';
            $this->redirect(URL . 'login');
        }

        $userId = (int) $row['user_id'];

        if ($this->userModel->resetPassword($userId, $password)) {
            $this->userModel->activate($userId);
            $this->resets->markUsed((int) $row['id']);
            DashboardCache::forget('pending_invitations');

            AuditLogger::log(
                'users',
                'invitation_accepted',
                'Invitation accepted — account activated',
                ['user_id' => $userId]
            );

            regenerateCSRFToken();
            $_SESSION['message'] = 'Your account is active. You can now log in.';
            $_SESSION['icon']    = 'success';
        } else {
            $_SESSION['message'] = 'Error activating account. Please try again.';
            $_SESSION['icon']    = 'error';
        }

        $this->redirect(URL . 'login');
    }
}
