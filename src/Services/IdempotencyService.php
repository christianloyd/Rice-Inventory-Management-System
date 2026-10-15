<?php

namespace App\Services;

use App\Support\Database;

class IdempotencyService
{
    /**
     * Checks if an idempotency key was already seen, e.g., for Sales.
     */
    public function hasSeenSaleKey(string $key): bool
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT id FROM sales WHERE idempotency_key = ?");
        $stmt->execute([$key]);
        return (bool) $stmt->fetch();
    }
}
