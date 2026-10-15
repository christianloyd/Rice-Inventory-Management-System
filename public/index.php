<?php

use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

// Security Headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: same-origin');

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

$appConfig = require __DIR__ . '/../config/app.php';
date_default_timezone_set($appConfig['timezone']);

session_name($appConfig['session_name']);
session_set_cookie_params([
    'lifetime' => 0, // Until browser closes
    'path' => '/',
    'domain' => '',
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

$router = new App\Support\Router(require __DIR__ . '/../config/routes.php');
$request = new App\Support\Request();

try {
    $response = $router->dispatch($request);
    $response->send();
} catch (Exception $e) {
    if ($_ENV['APP_ENV'] === 'local') {
        echo "<pre>" . htmlspecialchars((string) $e) . "</pre>";
    } else {
        error_log($e->getMessage() . "\n" . $e->getTraceAsString());
        http_response_code(500);
        echo "Something went wrong, please try again.";
    }
}
