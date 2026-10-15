<?php

namespace App\Models;

use App\Support\Database;
use PDO;

class StockIn
{
    public static function all(): array
    {
        $pdo = Database::getInstance();
        return $pdo->query("
            SELECT s.*, u.full_name as receiver_name,
                   s.reference_no as reference_number,
                   (SELECT COALESCE(SUM(quantity * cost_per_unit), 0) FROM stock_in_items WHERE stock_in_id = s.id) as total_cost,
                   (SELECT COALESCE(SUM(quantity), 0) FROM stock_in_returns WHERE stock_in_id = s.id) as total_returned_qty
            FROM stock_ins s 
            LEFT JOIN users u ON s.recorded_by = u.id 
            ORDER BY s.created_at DESC
        ")->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            SELECT s.*, u.full_name as received_by_name,
                   s.reference_no as reference_number,
                   (SELECT COALESCE(SUM(quantity * cost_per_unit), 0) FROM stock_in_items WHERE stock_in_id = s.id) as total_cost,
                   (SELECT COALESCE(SUM(quantity), 0) FROM stock_in_returns WHERE stock_in_id = s.id) as total_returned_qty
            FROM stock_ins s
            LEFT JOIN users u ON s.recorded_by = u.id
            WHERE s.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function cancel(int $id, int $userId): void
    {
        $pdo = Database::getInstance();
        $pdo->beginTransaction();

        try {
            // Check if already cancelled
            $stmt = $pdo->prepare("SELECT status FROM stock_ins WHERE id = ? FOR UPDATE");
            $stmt->execute([$id]);
            if ($stmt->fetchColumn() === 'cancelled') {
                $pdo->rollBack();
                return;
            }

            // Check if returns already exist on this stock-in
            $returnsCheck = $pdo->prepare("SELECT COUNT(*) FROM stock_in_returns WHERE stock_in_id = ?");
            $returnsCheck->execute([$id]);
            if ((int)$returnsCheck->fetchColumn() > 0) {
                throw new \Exception("Cannot cancel Stock-In: returns have already been recorded for this shipment. Please review returns instead.");
            }

            // Mark as cancelled
            $pdo->prepare("UPDATE stock_ins SET status = 'cancelled' WHERE id = ?")->execute([$id]);

            // Revert stock caches
            $items = $pdo->prepare("SELECT rice_variant_id, quantity FROM stock_in_items WHERE stock_in_id = ?");
            $items->execute([$id]);
            
            $undoMovement = $pdo->prepare("
                INSERT INTO stock_movements 
                (rice_variant_id, movement_type, quantity, reference_type, reference_id, balance_after, performed_by) 
                VALUES (?, 'STOCK_IN_REVERSAL', ?, 'STOCK_IN_CANCEL', ?, ?, ?)
            ");
            $updateStock = $pdo->prepare("UPDATE rice_variants SET current_stock_cache = current_stock_cache - ? WHERE id = ?");

            foreach ($items->fetchAll() as $item) {
                // Get current stock
                $s = $pdo->prepare("SELECT current_stock_cache FROM rice_variants WHERE id = ? FOR UPDATE");
                $s->execute([$item['rice_variant_id']]);
                $prevStock = (float)$s->fetchColumn();
                $newStock = $prevStock - (float)$item['quantity'];

                $updateStock->execute([$item['quantity'], $item['rice_variant_id']]);
                $undoMovement->execute([$item['rice_variant_id'], $item['quantity'], $id, $newStock, $userId]);
            }

            $pdo->commit();
        } catch (\Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
