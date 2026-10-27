<?php

namespace App\Middleware;

use App\Core\Auth;

class AuthMiddleware implements MiddlewareInterface
{
    public function handle(): void
    {
        if (!Auth::check()) {
            if (Auth::attemptRememberLogin()) {
                Auth::refreshPermissionsIfStale();
                return;
            }

            $this->redirectToLogin('Session expired due to inactivity. Please log in again.');
        }

        if (!Auth::checkTimeout() || !Auth::checkSecurity()) {
            $this->ensureSession();
            if (!Auth::attemptRememberLogin()) {
                $this->redirectToLogin('Session expired due to inactivity. Please log in again.');
            }
        }

        if (!Auth::checkAccountState()) {
            $this->redirectToLogin('Your session is no longer valid. Please log in again.');
        }

        Auth::refreshPermissionsIfStale();
    }

    /** Las comprobaciones de sesión la destruyen al fallar; sin una nueva el aviso se perdería. */
    private function ensureSession(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
            session_regenerate_id(true);
        }
    }

    private function redirectToLogin(string $message): never
    {
        $this->ensureSession();
        $_SESSION['message'] = $message;
        $_SESSION['icon']    = 'warning';
        header('Location: ' . URL . 'login');
        exit;
    }
}
