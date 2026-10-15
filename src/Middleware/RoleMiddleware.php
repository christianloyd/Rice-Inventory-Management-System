<?php

namespace App\Middleware;

use App\Support\Response;

class RoleMiddleware
{
    public static function handle(array $allowedRoles): void
    {
        $userRole = $_SESSION['role'] ?? null;
        
        // Handle sub-checks like 'staff:stockin'
        foreach ($allowedRoles as $roleDef) {
            $parts = explode(':', $roleDef);
            $baseRole = $parts[0];
            $subRole = $parts[1] ?? null;

            if ($userRole === $baseRole) {
                if ($subRole === 'stockin' && empty($_SESSION['can_record_stock_in'])) {
                    continue; // Base matches but sub-role fails, check next allowed role
                }
                return; // Access granted
            }
        }

        Response::setFlash('error', 'You do not have permission to access this resource.');
        $redirectUrl = $_SERVER['HTTP_REFERER'] ?? \url('/dashboard');
        Response::redirect($redirectUrl)->send();
    }
}
