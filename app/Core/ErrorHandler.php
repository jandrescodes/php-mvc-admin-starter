<?php

namespace App\Core;

final class ErrorHandler
{
    private function __construct()
    {
    }

    public static function notFound(): void
    {
        http_response_code(404);

        if (self::isAjax()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Not found.', 'code' => 404]);
            exit;
        }

        $view = dirname(__DIR__, 2) . '/views/errors/404.php';
        if (file_exists($view)) {
            require $view;
        } else {
            echo '404 - Page Not Found';
        }
        exit;
    }

    public static function forbidden(): void
    {
        http_response_code(403);

        if (self::isAjax()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Access denied.', 'code' => 403]);
            exit;
        }

        $view = dirname(__DIR__, 2) . '/views/errors/403.php';
        if (file_exists($view)) {
            require $view;
        } else {
            echo '403 - Access Denied';
        }
        exit;
    }

    public static function serverError(string $msg = ''): void
    {
        http_response_code(500);

        if ($msg !== '') {
            error_log('Server error: ' . $msg);
        }

        if (self::isAjax()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Internal server error.', 'code' => 500]);
            exit;
        }

        $view = dirname(__DIR__, 2) . '/views/errors/500.php';
        if (file_exists($view)) {
            require $view;
        } else {
            echo '500 - Internal Server Error';
        }
        exit;
    }

    private static function isAjax(): bool
    {
        return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
    }
}
