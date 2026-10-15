<?php

namespace App\Models;

use App\Support\Database;
use PDO;

class RicePriceHistory
{
    public static function getHistory(int $limit = 100): array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            SELECT h.*, v.name as variant_name, v.unit as variant_unit, u.full_name as changed_by_name
            FROM rice_price_history h
            JOIN rice_variants v ON h.rice_variant_id = v.id
            LEFT JOIN users u ON h.changed_by = u.id
            ORDER BY h.changed_at DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function getHistoryByVariant(int $variantId): array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            SELECT h.*, u.full_name as changed_by_name
            FROM rice_price_history h
            LEFT JOIN users u ON h.changed_by = u.id
            WHERE h.rice_variant_id = ?
            ORDER BY h.changed_at DESC
        ");
        $stmt->execute([$variantId]);
        return $stmt->fetchAll();
    }
}
