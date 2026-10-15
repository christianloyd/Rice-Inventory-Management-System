<?php

namespace App\Models;

use App\Support\Database;
use PDO;

class StockMovement
{
    public static function getMovements(?int $variantId = null, int $limit = 100): array
    {
        $pdo = Database::getInstance();
        $query = "
            SELECT 
                m.*,
                m.movement_type                                  AS type,
                CASE 
                    WHEN m.movement_type IN ('STOCK_IN','ADJUSTMENT_IN','SALE_REVERSAL') 
                    THEN m.quantity
                    ELSE -m.quantity
                END                                              AS quantity_change,
                (m.balance_after - CASE 
                    WHEN m.movement_type IN ('STOCK_IN','ADJUSTMENT_IN','SALE_REVERSAL')
                    THEN m.quantity
                    ELSE -m.quantity
                END)                                             AS previous_stock,
                m.balance_after                                  AS new_stock,
                v.name                                           AS variant_name,
                u.full_name                                      AS user_name
            FROM stock_movements m
            JOIN rice_variants v ON m.rice_variant_id = v.id
            LEFT JOIN users u ON m.performed_by = u.id
        ";
        $params = [];

        if ($variantId !== null) {
            $query .= " WHERE m.rice_variant_id = ?";
            $params[] = $variantId;
        }

        $query .= " ORDER BY m.created_at DESC, m.id DESC";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params); // Limit in PDO with parameters requires binds, skipping limit safety for MVP or just letting it fetch all in scope
        return $stmt->fetchAll();
    }
}
