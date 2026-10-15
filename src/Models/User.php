<?php

namespace App\Models;

use App\Support\Database;
use PDO;

class User
{
    public static function findByUsername(string $username): ?array
    {
        $stmt = Database::getInstance()->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function findById(int $id): ?array
    {
        $stmt = Database::getInstance()->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function updateLogins(int $id, int $failedAttempts, ?string $lockedUntil): void
    {
        $stmt = Database::getInstance()->prepare("UPDATE users SET failed_login_attempts = ?, locked_until = ? WHERE id = ?");
        $stmt->execute([$failedAttempts, $lockedUntil, $id]);
    }
}
