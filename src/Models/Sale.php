<?php

namespace App\Models;

use App\Support\Database;
use PDO;
use RuntimeException;

class Sale
{
    public static function all(): array
    {
        $pdo = Database::getInstance();
        return $pdo->query("
            SELECT s.*, u.full_name as cashier_name 
            FROM sales s 
            LEFT JOIN users u ON s.recorded_by = u.id 
            ORDER BY s.created_at DESC
        ")->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            SELECT s.*, u.full_name as cashier_name
            FROM sales s
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
            $stmt = $pdo->prepare("SELECT status FROM sales WHERE id = ? FOR UPDATE");
            $stmt->execute([$id]);
            if ($stmt->fetchColumn() === 'cancelled') {
                $pdo->rollBack();
                return;
            }

            // Mark cancelled
            $pdo->prepare("UPDATE sales SET status = 'cancelled' WHERE id = ?")->execute([$id]);

            // Void any linked TBT records automatically so they no longer appear pending
            $pdo->prepare("
                UPDATE tbt_records tr
                JOIN sale_items si ON tr.sale_item_id = si.id
                SET tr.status = 'cancelled'
                WHERE si.sale_id = ?
            ")->execute([$id]);

            // Revert stock (add back to inventory)
            $items = $pdo->prepare("SELECT rice_variant_id, quantity FROM sale_items WHERE sale_id = ?");
            $items->execute([$id]);
            
            $undoMovement = $pdo->prepare("
                INSERT INTO stock_movements 
                (rice_variant_id, reference_type, reference_id, movement_type, quantity, balance_after, performed_by) 
                VALUES (?, 'SALE', ?, 'SALE_REVERSAL', ?, ?, ?)
            ");
            $updateStock = $pdo->prepare("UPDATE rice_variants SET current_stock_cache = current_stock_cache + ? WHERE id = ?");

            foreach ($items->fetchAll() as $item) {
                // Get current stock
                $s = $pdo->prepare("SELECT current_stock_cache FROM rice_variants WHERE id = ? FOR UPDATE");
                $s->execute([$item['rice_variant_id']]);
                $prevStock = (float) $s->fetchColumn();
                $qty = (float) $item['quantity'];
                $newStock = $prevStock + $qty;

                $updateStock->execute([$qty, $item['rice_variant_id']]);
                $undoMovement->execute([$item['rice_variant_id'], $id, $qty, $newStock, $userId]);
            }

            $pdo->commit();
        } catch (\Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Assign a sequential control number to a sale on first receipt generation.
     * Idempotent: if the sale already has a control_number it is returned as-is.
     * Uses a transaction + FOR UPDATE locks to prevent duplicate assignment
     * under concurrent requests.
     *
     * @throws RuntimeException if the sale is not found or the counter is unavailable.
     */
    public static function assignControlNumber(int $saleId): string
    {
        $pdo = Database::getInstance();
        $pdo->beginTransaction();

        try {
            // Lock the sale row so concurrent requests wait here.
            $stmt = $pdo->prepare("SELECT control_number FROM sales WHERE id = ? FOR UPDATE");
            $stmt->execute([$saleId]);
            $existing = $stmt->fetchColumn();

            if ($existing !== false && $existing !== null && $existing !== '') {
                // Already assigned — return without touching the counter.
                $pdo->commit();
                return (string) $existing;
            }

            // Lock the counter row so we get a unique value.
            $cStmt = $pdo->prepare(
                "SELECT setting_value FROM settings WHERE setting_key = 'next_control_number' FOR UPDATE"
            );
            $cStmt->execute();
            $nextRaw = $cStmt->fetchColumn();

            if ($nextRaw === false) {
                throw new RuntimeException('next_control_number setting not found. Please run migrations.');
            }

            $nextInt    = (int) $nextRaw;
            $controlNo  = str_pad((string) $nextInt, 6, '0', STR_PAD_LEFT);
            $incremented = $nextInt + 1;

            // Write the control number to the sale.
            $pdo->prepare("UPDATE sales SET control_number = ? WHERE id = ?")
                ->execute([$controlNo, $saleId]);

            // Increment the counter in settings.
            $pdo->prepare(
                "UPDATE settings SET setting_value = ? WHERE setting_key = 'next_control_number'"
            )->execute([(string) $incremented]);

            $pdo->commit();
            return $controlNo;

        } catch (\Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
