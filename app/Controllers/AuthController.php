<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\LoginThrottleService;

class AuthController extends Controller
{
    private User                 $userModel;
    private LoginThrottleService $throttle;

    public function __construct()
    {
        $this->userModel = new User();
        $this->throttle  = new LoginThrottleService($this->userModel);
    }

    public function showLoginForm(): void
    {
        $this->renderStandalone('auth/login', [], 'Sign In', ['login']);
    }

    public function login(): void
    {
        $this->csrfCheck();

        $identifier = trim($_POST['identifier'] ?? '');
        $password   = trim($_POST['password']   ?? '');

        if (empty($identifier) || empty($password)) {
            $_SESSION['message'] = 'Please fill in all fields.';
            $_SESSION['icon']    = 'error';
            $this->redirect(URL . 'login');
        }

        // 1. Resolve user row without verifying password yet
        $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL);
        $user    = $isEmail
            ? $this->userModel->findByEmail($identifier)
            : $this->userModel->findByDocumentNumber($identifier);

        // 2. Throttle check — only when the user exists
        if ($user) {
            $lock = $this->throttle->isLocked($user);
            if ($lock['locked']) {
                $_SESSION['message'] = $lock['message'];
                $_SESSION['icon']    = 'error';
                $this->redirect(URL . 'login');
            }
        }

        // 3. Verify password
        $validCredentials = $user && password_verify($password, $user['password']);

        if (!$validCredentials) {
            // Register failure only when the user exists (avoid phantom rows)
            if ($user) {
                $this->throttle->registerFailure($user);
            }

            AuditLogger::log(
                'auth',
                'login_failed',
                "Failed login attempt for: {$identifier}",
                ['identifier_type' => $isEmail ? 'email' : 'document'],
                null,
                $identifier
            );

            $_SESSION['message'] = 'Incorrect credentials.';
            $_SESSION['icon']    = 'error';
            $this->redirect(URL . 'login');
        }

        // 4. Account status check
        if ((int) $user['status'] === User::STATUS_PENDING) {
            $_SESSION['message'] = 'Your account is pending activation. Check your email to set your password.';
            $_SESSION['icon']    = 'warning';
            $this->redirect(URL . 'login');
        }

        if ((int) $user['status'] === User::STATUS_INACTIVE) {
            $_SESSION['message'] = 'Your account is deactivated. Please contact an administrator.';
            $_SESSION['icon']    = 'warning';
            $this->redirect(URL . 'login');
        }

        // 5. Successful login
        Auth::login($user, Auth::buildPermNames($user));
        regenerateCSRFToken();
        $this->throttle->clearOnSuccess($user);

        AuditLogger::log(
            'auth',
            'login',
            "Successful login: {$user['name']} {$user['first_surname']}",
            ['email' => $user['email']]
        );

        if (!empty($_POST['remember']) && $_POST['remember'] === '1') {
            Auth::issueRememberCookie((int) $user['id']);
        }

        $_SESSION['welcome_user'] = $_SESSION['user_name'];
        $this->redirect(URL);
    }

    public function logout(): void
    {
        $this->csrfCheck();
        AuditLogger::log('auth', 'logout', 'User logged out');
        Auth::logout();
        $this->redirect(URL . 'login');
    }

    public function showForgotPasswordForm(): void
    {
        $this->renderStandalone('auth/forgot_password', [], 'Forgot Password', ['forgot_password']);
    }

    public function requestPasswordReset(): void
    {
        (new PasswordResetController())->requestReset();
    }

    public function resetPassword(): void
    {
        (new PasswordResetController())->resetPassword();
    }
}
