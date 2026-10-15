<?php

namespace App\Models;

use App\Support\Database;
use PDO;

class StockInReturn
{
    /**
     * Retrieve all returns with joined stock-in, variant, and user details.
     */
    public static function all(): array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->query("
            SELECT r.*,
                   s.reference_no AS stock_in_reference,
                   s.supplier_name,
                   v.name AS variant_name,
                   v.unit AS variant_unit,
                   u.full_name AS returned_by_name
            FROM stock_in_returns r
            JOIN stock_ins s ON r.stock_in_id = s.id
            JOIN rice_variants v ON r.rice_variant_id = v.id
            LEFT JOIN users u ON r.returned_by = u.id
            ORDER BY r.created_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retrieve all returns for a specific stock-in record.
     */
    public static function getByStockIn(int $stockInId): array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            SELECT r.*,
                   v.name AS variant_name,
                   v.unit AS variant_unit,
                   u.full_name AS returned_by_name
            FROM stock_in_returns r
            JOIN rice_variants v ON r.rice_variant_id = v.id
            LEFT JOIN users u ON r.returned_by = u.id
            WHERE r.stock_in_id = ?
            ORDER BY r.created_at DESC
        ");
        $stmt->execute([$stockInId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retrieve a specific return transaction by ID.
     */
    public static function find(int $id): ?array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            SELECT r.*,
                   s.reference_no AS stock_in_reference,
                   s.supplier_name,
                   s.stock_in_date,
                   v.name AS variant_name,
                   v.unit AS variant_unit,
                   v.current_stock_cache,
                   u.full_name AS returned_by_name,
                   (r.quantity * r.cost_per_unit) AS total_value
            FROM stock_in_returns r
            JOIN stock_ins s ON r.stock_in_id = s.id
            JOIN rice_variants v ON r.rice_variant_id = v.id
            LEFT JOIN users u ON r.returned_by = u.id
            WHERE r.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Retrieve all active shipment items that are eligible for return.
     */
    public static function getReturnableItems(): array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->query("
            SELECT i.id AS item_id,
                   i.stock_in_id,
                   i.quantity AS received_qty,
                   i.cost_per_unit,
                   s.reference_no AS stock_in_ref,
                   s.supplier_name,
                   s.stock_in_date,
                   v.id AS variant_id,
                   v.name AS variant_name,
                   v.unit AS variant_unit,
                   v.current_stock_cache,
                   COALESCE((SELECT SUM(quantity) FROM stock_in_returns WHERE stock_in_item_id = i.id), 0) AS returned_qty,
                   (i.quantity - COALESCE((SELECT SUM(quantity) FROM stock_in_returns WHERE stock_in_item_id = i.id), 0)) AS remaining_delivered_qty
            FROM stock_in_items i
            JOIN stock_ins s ON i.stock_in_id = s.id
            JOIN rice_variants v ON i.rice_variant_id = v.id
            WHERE s.status = 'active'
            HAVING remaining_delivered_qty > 0 AND v.current_stock_cache > 0
            ORDER BY s.stock_in_date DESC, s.id DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get aggregate statistics for returns.
     */
    public static function getStats(): array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->query("
            SELECT COUNT(*) AS total_returns_count,
                   COALESCE(SUM(quantity), 0) AS total_sacks_returned,
                   COALESCE(SUM(quantity * cost_per_unit), 0) AS total_returned_value
            FROM stock_in_returns
        ");
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
            'total_returns_count' => 0,
            'total_sacks_returned' => 0,
            'total_returned_value' => 0
        ];
    }
}
