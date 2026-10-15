<?php

namespace App\Models;

use App\Support\Database;
use PDO;

class StockInItem
{
    public static function getByStockIn(int $stockInId): array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            SELECT i.*, 
                   v.name as variant_name, 
                   v.unit as variant_unit,
                   v.current_stock_cache as current_variant_stock,
                   COALESCE((SELECT SUM(quantity) FROM stock_in_returns WHERE stock_in_item_id = i.id), 0) as returned_quantity,
                   (i.quantity - COALESCE((SELECT SUM(quantity) FROM stock_in_returns WHERE stock_in_item_id = i.id), 0)) as net_quantity
            FROM stock_in_items i
            JOIN rice_variants v ON i.rice_variant_id = v.id
            WHERE i.stock_in_id = ?
        ");
        $stmt->execute([$stockInId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function createWithTransaction(PDO $pdo, int $stockInId, int $variantId, float $quantity, float $costPrice, int $userId): void
    {
        // 1. Insert Item
        $stmt = $pdo->prepare("INSERT INTO stock_in_items (stock_in_id, rice_variant_id, quantity, cost_price) VALUES (?, ?, ?, ?)");
        $stmt->execute([$stockInId, $variantId, $quantity, $costPrice]);

        // 2. Add Stock Movement
        $stmt = $pdo->prepare("SELECT current_stock_cache FROM rice_variants WHERE id = ? FOR UPDATE");
        $stmt->execute([$variantId]);
        $prevStock = $stmt->fetchColumn() ?: 0;
        $newStock = $prevStock + $quantity;

        $stmt = $pdo->prepare("INSERT INTO stock_movements (rice_variant_id, reference_type, reference_id, type, quantity_change, previous_stock, new_stock, user_id) VALUES (?, 'stock_in', ?, 'in', ?, ?, ?, ?)");
        $stmt->execute([$variantId, $stockInId, $quantity, $prevStock, $newStock, $userId]);

        // 3. Update Variant Cache
        $stmt = $pdo->prepare("UPDATE rice_variants SET current_stock_cache = ? WHERE id = ?");
        $stmt->execute([$newStock, $variantId]);
    }

    public static function createStockIn(string $supplier, string $reference, string $notes, array $items, int $userId): int
    {
        $pdo = Database::getInstance();
        $pdo->beginTransaction();

        try {
            // Calculate Total Cost
            $totalCost = 0;
            foreach ($items as $item) {
                $totalCost += ($item['quantity'] * $item['cost_price']);
            }

            // Create Stock In Record
            $stmt = $pdo->prepare("INSERT INTO stock_ins (supplier_name, reference_number, total_cost, notes, received_by, status) VALUES (?, ?, ?, ?, ?, 'completed')");
            $stmt->execute([$supplier, $reference, $totalCost, $notes, $userId]);
            $stockInId = $pdo->lastInsertId();

            // Create Items & Update Inventory
            foreach ($items as $item) {
                self::createWithTransaction($pdo, $stockInId, $item['rice_variant_id'], $item['quantity'], $item['cost_price'], $userId);
            }

            $pdo->commit();
            return $stockInId;
        } catch (\Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
