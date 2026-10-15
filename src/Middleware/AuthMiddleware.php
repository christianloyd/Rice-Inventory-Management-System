<?php

namespace App\Middleware;

use App\Support\Response;

class AuthMiddleware
{
    public static function handle(): void
    {
        $appConfig = require __DIR__ . '/../../config/app.php';
        
        if (!isset($_SESSION['user_id'])) {
            Response::redirect(\url('/login'))->send();
        }

        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $appConfig['session_lifetime'])) {
            session_unset();
            session_destroy();
            session_start();
            Response::setFlash('error', 'Session expired. Please log in again.');
            Response::redirect(\url('/login'))->send();
        }

        $_SESSION['last_activity'] = time();
    }
}
