<?php

namespace App\Models;

use App\Support\Database;
use PDO;

class AuditLog
{
    public static function getRecent(int $limit = 200, string $module = null): array
    {
        $pdo = Database::getInstance();
        $query = "
            SELECT a.*, u.full_name as user_name
            FROM audit_logs a
            LEFT JOIN users u ON a.user_id = u.id
        ";
        
        $params = [];
        if ($module) {
            $query .= " WHERE a.module = ?";
            $params[] = $module;
        }

        $query .= " ORDER BY a.created_at DESC LIMIT ?";
        
        $stmt = $pdo->prepare($query);
        
        if ($module) {
            $stmt->bindValue(1, $module, PDO::PARAM_STR);
            $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        } else {
            $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }
}
