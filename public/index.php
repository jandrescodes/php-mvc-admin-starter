<?php

/**
 * Front Controller - Entry point for all requests
 *
 * All HTTP requests are routed through this file via Apache rewriting.
 * The router determines which controller method to execute.
 */

// Start session once, at the entry point, before anything else reads $_SESSION.
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.use_strict_mode', 1);
    session_start();
}

require_once dirname(__DIR__) . '/app/Config/config.php';

// Initialize and dispatch the request
$router = new App\Core\Router();
$router->dispatch($_SERVER['REQUEST_URI']);
