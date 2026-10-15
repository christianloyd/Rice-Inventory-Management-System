<?php

namespace App\Services;

use App\Support\Database;
use Exception;

class StockAdjustmentService
{
    private AuditService $auditService;

    public function __construct()
    {
        $this->auditService = new AuditService();
    }

    /**
     * Overrides stock to an absolute value cleanly executing deltas natively.
     * @throws Exception
     */
    public function execute(int $variantId, float $absoluteQty, string $reason, int $userId): array
    {
        $pdo = Database::getInstance();

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("SELECT current_stock_cache, name FROM rice_variants WHERE id = ? FOR UPDATE");
            $stmt->execute([$variantId]);
            $variant = $stmt->fetch();

            if (!$variant) {
                throw new Exception("Variant ID {$variantId} not found.");
            }

            $currentStock = (float) $variant['current_stock_cache'];
            
            if ($currentStock == $absoluteQty) {
                $pdo->rollBack();
                return ['success' => true, 'message' => "Stock is already at EXACTLY {$absoluteQty}. No override necessary."];
            }

            $delta = $absoluteQty - $currentStock;
            $movementType = $delta > 0 ? 'ADJUSTMENT_IN' : 'ADJUSTMENT_OUT';

            $updateStockStmt = $pdo->prepare("UPDATE rice_variants SET current_stock_cache = ? WHERE id = ?");
            $updateStockStmt->execute([$absoluteQty, $variantId]);

            $movementStmt = $pdo->prepare("
                INSERT INTO stock_movements 
                (rice_variant_id, movement_type, quantity, reference_type, reference_id, balance_after, performed_by)
                VALUES (?, ?, ?, 'ADJUSTMENT', ?, ?, ?)
            ");
            
            $movementStmt->execute([
                $variantId,
                $movementType,
                abs($delta),
                $variantId, // Fallback ref
                $absoluteQty,
                $userId
            ]);
            
            $movementId = $pdo->lastInsertId();

            $this->auditService->log(
                $userId,
                'stock.absolute_override',
                'stock',
                'stock_movements',
                $movementId,
                null,
                [
                    'old_stock' => $currentStock,
                    'new_stock' => $absoluteQty,
                    'delta' => $delta,
                    'reason' => $reason
                ]
            );

            $pdo->commit();
            return ['success' => true, 'message' => "Stock successfully forcefully adjusted to {$absoluteQty}."];

        } catch (Exception $e) {
            $pdo->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
