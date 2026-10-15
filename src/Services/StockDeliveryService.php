<?php
namespace App\Services;

use App\Models\StockDelivery;
use App\Support\Database;
use Exception;
use PDO;

class StockDeliveryService
{
    private AuditService $auditService;

    public function __construct()
    {
        $this->auditService = new AuditService();
    }

    public function createStockDelivery(array $data): array
    {
        $pdo = Database::getInstance();
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare(
                "INSERT INTO stock_deliveries (stock_in_date, rice_variant_id, quantity, reference_note, created_by)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                $data['stock_in_date'],
                $data['rice_variant_id'],
                $data['quantity'],
                $data['reference_note'] ?? null,
                $data['created_by'],
            ]);
            
            $stockInId = (int) $pdo->lastInsertId();

            $updateStockStmt = $pdo->prepare(
                "UPDATE rice_variants SET current_stock_cache = current_stock_cache + ? WHERE id = ?"
            );
            $updateStockStmt->execute([$data['quantity'], $data['rice_variant_id']]);

            $movementStmt = $pdo->prepare("
                INSERT INTO stock_movements (rice_variant_id, movement_type, quantity, reference_type, reference_id, balance_after, performed_by)
                VALUES (?, 'STOCK_IN', ?, 'STOCK_DELIVERY', ?, (SELECT current_stock_cache FROM rice_variants WHERE id = ?), ?)
            ");
            $movementStmt->execute([
                $data['rice_variant_id'],
                $data['quantity'],
                $stockInId,
                $data['rice_variant_id'],
                $data['created_by']
            ]);

            $this->auditService->log(
                $data['created_by'],
                'stock_delivery.created',
                'stock_deliveries',
                'stock_delivery',
                $stockInId,
                null,
                ['quantity' => $data['quantity']]
            );
            
            $pdo->commit();
            return ['success' => true, 'id' => $stockInId];
        } catch (Exception $e) {
            $pdo->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
