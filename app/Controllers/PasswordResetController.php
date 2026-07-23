<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\PasswordReset;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\DashboardCache;
use App\Services\MailService;

class PasswordResetController extends Controller
{
    private User          $userModel;
    private PasswordReset $resets;
    private MailService   $mailService;

    public function __construct()
    {
        $this->userModel   = new User();
        $this->resets      = new PasswordReset();
        $this->mailService = new MailService();
    }

    public function showResetPasswordForm(): void
    {
        $token = trim($_GET['token'] ?? '');

        if (empty($token)) {
            $_SESSION['message'] = 'Invalid or missing token.';
            $_SESSION['icon']    = 'error';
            $this->redirect(URL . 'login');
        }

        $row = $this->resets->findValidByToken($token, 'reset');

        if (!$row) {
            $_SESSION['message'] = 'The link has expired or is invalid.';
            $_SESSION['icon']    = 'error';
            $this->redirect(URL . 'login');
        }

        $this->renderStandalone('auth/reset_password', compact('token'), 'Reset Password', ['reset_password']);
    }

    public function requestReset(): void
    {
        $this->csrfCheck();

        $email = trim($_POST['email'] ?? '');

        if (empty($email)) {
            $_SESSION['message'] = 'Email is required.';
            $_SESSION['icon']    = 'error';
            $this->redirect(URL . 'forgot-password');
        }

        // Generic message to avoid user enumeration (covers unknown email AND pending accounts)
        $genericMessage = 'If the email is registered, you will receive a reset link.';

        $userId = $this->userModel->getIdByEmail($email);

        if (!$userId) {
            if (env('DEBUG')) {
                $_SESSION['message'] = 'The email address is not registered in our system (DEBUG mode).';
                $_SESSION['icon']    = 'error';
                $this->redirect(URL . 'forgot-password');
            }

            $_SESSION['message'] = $genericMessage;
            $_SESSION['icon']    = 'info';
            $this->redirect(URL . 'login');
        }

        // Block pending accounts — same generic message to avoid enumeration
        $user = $this->userModel->getById($userId);
        if ($user && (int) $user['status'] === User::STATUS_PENDING) {
            $_SESSION['message'] = $genericMessage;
            $_SESSION['icon']    = 'info';
            $this->redirect(URL . 'login');
        }

        $token = $this->resets->create($userId, 'reset');
        DashboardCache::forget('resets_this_week');
        $this->mailService->sendPasswordResetEmail($email, $token);

        regenerateCSRFToken();

        $_SESSION['message'] = $genericMessage;
        $_SESSION['icon']    = 'success';
        $this->redirect(URL . 'login');
    }

    public function resetPassword(): void
    {
        $this->csrfCheck();

        $token    = $_POST['token']            ?? '';
        $password = $_POST['password']         ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';

        if (empty($token)) {
            $_SESSION['message'] = 'Invalid or missing token.';
            $_SESSION['icon']    = 'error';
            $this->redirect(URL . 'login');
        }

        $pwErrors = $this->userModel->validateNewPassword($password, $confirm);
        // Reset password flow requires minimum 8 chars (stricter than profile)
        if (empty($pwErrors) && strlen($password) < 8) {
            $pwErrors[] = 'Password must be at least 8 characters.';
        }
        if (!empty($pwErrors)) {
            $_SESSION['message'] = $pwErrors[0];
            $_SESSION['icon']    = 'error';
            $this->redirect(URL . 'reset-password?token=' . urlencode($token));
        }

        $row = $this->resets->findValidByToken($token, 'reset');

        if (!$row) {
            $_SESSION['message'] = 'The link has expired or is invalid.';
            $_SESSION['icon']    = 'error';
            $this->redirect(URL . 'login');
        }

        if ($this->userModel->resetPassword($row['user_id'], $password)) {
            $this->resets->markUsed((int) $row['id']);

            AuditLogger::log(
                'auth',
                'password_reset',
                'Password reset completed',
                ['user_id' => $row['user_id']]
            );

            regenerateCSRFToken();
            $_SESSION['message'] = 'Password updated successfully. You can now log in.';
            $_SESSION['icon']    = 'success';
        } else {
            $_SESSION['message'] = 'Error updating password. Please try again.';
            $_SESSION['icon']    = 'error';
        }

        $this->redirect(URL . 'login');
    }
}
