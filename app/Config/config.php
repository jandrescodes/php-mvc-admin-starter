<?php

/**
 * Main configuration file
 *
 * Contains the global application configuration, including
 * database connection parameters and general settings.
 *
 * @package ProyectoBase
 * @subpackage App\Config
 * @author jandrescodes
 * @version 1.0
 */

$composerAutoload = __DIR__ . '/../../vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
}
unset($composerAutoload);

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 2));
$dotenv->safeLoad();
unset($dotenv);

// Set the timezone, falling back to a default if not defined
$timezone = env('TIMEZONE');
date_default_timezone_set($timezone);

$appUrl = rtrim((string) env('APP_URL', ''), '/') . '/';
if ($appUrl === '/') {
    $appUrl = '/';
}
$appVersion = (string) env('APP_VERSION', '1.0.0');

if (!defined('URL')) {
    define('URL', $appUrl);
}

if (!defined('APP_VERSION')) {
    define('APP_VERSION', $appVersion);
}

if (!defined('APP_BASE_PATH')) {
    $parsedPath = parse_url(URL, PHP_URL_PATH);
    $normalizedPath = rtrim((string) $parsedPath, '/');
    define('APP_BASE_PATH', $normalizedPath === '' ? '/' : $normalizedPath);
}

return [
    'database' => [
        'host' => env('DB_HOST'),
        'name' => env('DB_NAME'),
        'user' => env('DB_USER'),
        'pass' => env('DB_PASS'),
        'charset' => env('DB_CHARSET')
    ],
    'app' => [
        'timezone' => $timezone,
        'name' => 'ProyectoBase',
        'url' => URL,
        'debug' => env('DEBUG'),
        'version' => APP_VERSION
    ]
];
