<?php

function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function asset(string $path): string {
    $config = require __DIR__ . '/../config/app.php';
    return rtrim($config['app_url'], '/') . '/' . ltrim($path, '/');
}

function url(string $path): string {
    $config = require __DIR__ . '/../config/app.php';
    return rtrim($config['app_url'], '/') . '/' . ltrim($path, '/');
}

function base_path(string $path = ''): string {
    $root = dirname(__DIR__);
    return $root . ($path ? DIRECTORY_SEPARATOR . ltrim($path, '/\\') : '');
}

function csrf_field(): string {
    return \App\Middleware\CsrfMiddleware::csrfField();
}

function csrf_token(): string {
    return \App\Middleware\CsrfMiddleware::generateToken();
}
