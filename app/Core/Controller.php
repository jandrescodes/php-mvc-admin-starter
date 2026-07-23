<?php

namespace App\Core;

abstract class Controller
{
    protected $model;

    public function render($view, $data = [], $plugins = [], $module_scripts = [], $module_styles = [])
    {
        extract($data);

        $currentUser = \App\Core\Auth::user();

        $layoutData = [
            'content'        => $this->getView($view, $data),
            'plugins'        => $plugins,
            'module_scripts' => $module_scripts,
            'module_styles'  => $module_styles,
        ];

        require dirname(__DIR__, 2) . '/views/layouts/header.php';
        echo $layoutData['content'];
        require dirname(__DIR__, 2) . '/views/layouts/footer.php';
    }

    public function renderStandalone($view, $data = [], $title = '', $module_scripts = [])
    {
        $content = $this->getView($view, $data);

        require dirname(__DIR__, 2) . '/views/layouts/auth.php';
    }

    protected function getView($view, $data = [])
    {
        $viewPath = dirname(__DIR__, 2) . '/views/' . $view . '.php';

        if (!file_exists($viewPath)) {
            throw new \Exception("View not found: $viewPath");
        }

        extract($data);
        ob_start();
        require $viewPath;
        return ob_get_clean();
    }

    public function redirect($path)
    {
        header('Location: ' . $path);
        exit;
    }

    public function jsonResponse($data, $statusCode = 200)
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }

    public function csrfCheck()
    {
        $token = $_POST['csrf_token'] ?? '';

        if (!verifyCSRFToken($token)) {
            $isAjax = (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest');

            if ($isAjax) {
                $this->jsonResponse(['success' => false, 'message' => 'Token inválido'], 403);
            } else {
                $_SESSION['message'] = 'Token de seguridad inválido. Por favor, intenta de nuevo.';
                $_SESSION['icon']    = 'error';
                $referer = $_SERVER['HTTP_REFERER'] ?? '';
                $safe    = (defined('URL') && strpos($referer, URL) === 0) ? $referer : URL;
                $this->redirect($safe);
            }
        }
    }

    public function param($key, $default = null)
    {
        $value = $_POST[$key] ?? $_GET[$key] ?? $default;
        return is_string($value) ? trim($value) : $value;
    }

    protected function requireLogin()
    {
        if (!\App\Core\Auth::check()) {
            $this->redirect(URL . 'login');
        }
    }

    protected function requirePermission($permission)
    {
        $this->requireLogin();

        if (!\App\Core\Auth::hasPermission($permission)) {
            \App\Core\ErrorHandler::forbidden();
        }
    }
}
