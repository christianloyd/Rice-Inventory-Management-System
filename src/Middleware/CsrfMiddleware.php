<?php

namespace App\Middleware;

use App\Support\Request;
use App\Support\Response;

class CsrfMiddleware
{
    public static function handle(Request $request): void
    {
        if ($request->method() !== 'POST') {
            return;
        }

        $sessionToken = $_SESSION['_csrf'] ?? null;
        $requestToken = $request->input('_csrf', $request->server['HTTP_X_CSRF_TOKEN'] ?? null);

        if (!$sessionToken || !$requestToken || !hash_equals($sessionToken, $requestToken)) {
            http_response_code(419);
            echo "CSRF token mismatch.";
            exit;
        }
    }

    public static function generateToken(): string
    {
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf'];
    }

    public static function csrfField(): string
    {
        $token = self::generateToken();
        return "<input type=\"hidden\" name=\"_csrf\" value=\"{$token}\">";
    }
}
