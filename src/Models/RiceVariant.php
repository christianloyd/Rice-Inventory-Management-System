<?php

namespace App\Models;

use App\Support\Database;
use PDO;

class RiceVariant
{
    public static function all(bool $includeInactive = false): array
    {
        $pdo = Database::getInstance();
        if ($includeInactive) {
            return $pdo->query("SELECT * FROM rice_variants ORDER BY id DESC")->fetchAll();
        }
        return $pdo->query("SELECT * FROM rice_variants WHERE status = 'active' ORDER BY name ASC")->fetchAll();
    }

    public static function allWithCurrentPrice(): array
    {
        $pdo = Database::getInstance();
        return $pdo->query("
            SELECT v.*, p.current_price 
            FROM rice_variants v
            LEFT JOIN rice_prices p ON p.rice_variant_id = v.id
            ORDER BY v.id DESC
        ")->fetchAll();
    }

    public static function create(string $name, string $description, float $minimumStock, float $initialPrice, string $unit, int $userId, int $allowWholesaleDiscount = 1): void
    {
        $pdo = Database::getInstance();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("INSERT INTO rice_variants (name, description, minimum_stock, unit, allow_wholesale_discount) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $description, $minimumStock, $unit, $allowWholesaleDiscount]);
            $variantId = $pdo->lastInsertId();

            if ($initialPrice > 0) {
                // Insert initial price in rice_prices
                $pdo->prepare("INSERT INTO rice_prices (rice_variant_id, current_price, effective_date, updated_by) VALUES (?, ?, CURDATE(), ?)")
                    ->execute([$variantId, $initialPrice, $userId]);
                
                // Add to history
                $pdo->prepare("INSERT INTO rice_price_history (rice_variant_id, old_price, new_price, effective_date, reason, changed_by) VALUES (?, NULL, ?, CURDATE(), 'Initial Setup', ?)")
                    ->execute([$variantId, $initialPrice, $userId]);
            }
            $pdo->commit();
        } catch (\Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function update(int $id, string $name, string $description, float $minimumStock, string $unit, int $allowWholesaleDiscount = 1): void
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("UPDATE rice_variants SET name = ?, description = ?, minimum_stock = ?, unit = ?, allow_wholesale_discount = ? WHERE id = ?");
        $stmt->execute([$name, $description, $minimumStock, $unit, $allowWholesaleDiscount, $id]);
    }

    public static function toggleStatus(int $id): string
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT status FROM rice_variants WHERE id = ?");
        $stmt->execute([$id]);
        $status = $stmt->fetchColumn();

        $newStatus = $status === 'active' ? 'inactive' : 'active';
        $pdo->prepare("UPDATE rice_variants SET status = ? WHERE id = ?")->execute([$newStatus, $id]);
        return $newStatus;
    }
}
