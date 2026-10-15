<?php

namespace App\Models;

use App\Support\Database;
use PDO;

class RicePrice
{
    public static function allActiveWithPrices(): array
    {
        $pdo = Database::getInstance();
        return $pdo->query("
            SELECT v.id, v.name, p.current_price, p.effective_date, u.full_name as updated_by
            FROM rice_variants v
            LEFT JOIN rice_prices p ON p.rice_variant_id = v.id
            LEFT JOIN users u ON p.updated_by = u.id
            WHERE v.status = 'active'
            ORDER BY v.name ASC
        ")->fetchAll();
    }

    public static function updatePrice(int $variantId, float $newPrice, string $reason, int $userId): void
    {
        $pdo = Database::getInstance();
        $pdo->beginTransaction();

        try {
            // Get current price
            $stmt = $pdo->prepare("SELECT current_price FROM rice_prices WHERE rice_variant_id = ?");
            $stmt->execute([$variantId]);
            $oldPrice = $stmt->fetchColumn() ?: null;

            if ($oldPrice == $newPrice && $oldPrice !== null) {
                $pdo->rollBack();
                return; // No change
            }

            // Update or Insert into rice_prices
            if ($oldPrice === null) {
                $stmt = $pdo->prepare("INSERT INTO rice_prices (rice_variant_id, current_price, effective_date, updated_by) VALUES (?, ?, NOW(), ?)");
                $stmt->execute([$variantId, $newPrice, $userId]);
            } else {
                $stmt = $pdo->prepare("UPDATE rice_prices SET current_price = ?, effective_date = NOW(), updated_by = ? WHERE rice_variant_id = ?");
                $stmt->execute([$newPrice, $userId, $variantId]);
            }

            // Insert into history
            $stmt = $pdo->prepare("INSERT INTO rice_price_history (rice_variant_id, old_price, new_price, effective_date, reason, changed_by) VALUES (?, ?, ?, NOW(), ?, ?)");
            $stmt->execute([$variantId, $oldPrice, $newPrice, $reason, $userId]);

            $pdo->commit();
        } catch (\Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
