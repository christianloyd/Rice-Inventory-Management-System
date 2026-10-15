<?php

namespace App\Services;

use App\Support\Database;
use Exception;
use PDO;

class StockInReturnService
{
    private AuditService $auditService;

    public function __construct()
    {
        $this->auditService = new AuditService();
    }

    /**
     * Executes a Stock-In Return (Supplier Return) atomically.
     * Deducts the returned sacks from inventory (Stock OUT) and records the movement.
     *
     * @param int $stockInId
     * @param int $stockInItemId
     * @param float $returnQty
     * @param string $reason
     * @param string $returnDate
     * @param int $userId
     * @param string $notes
     * @return array ['success' => bool, 'return_no' => string, 'id' => int, 'error' => string]
     */
    public function executeReturn(
        int $stockInId,
        int $stockInItemId,
        float $returnQty,
        string $reason,
        string $returnDate,
        int $userId,
        string $notes = ''
    ): array {
        $pdo = Database::getInstance();

        try {
            $pdo->beginTransaction();

            // 1. Lock and check Stock In header
            $stmt = $pdo->prepare("SELECT * FROM stock_ins WHERE id = ? FOR UPDATE");
            $stmt->execute([$stockInId]);
            $stockIn = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$stockIn) {
                throw new Exception("Stock-In record #{$stockInId} not found.");
            }

            if ($stockIn['status'] === 'cancelled') {
                throw new Exception("Cannot return items from a cancelled Stock-In record.");
            }

            // 2. Lock and check Line Item
            $stmt = $pdo->prepare("
                SELECT * FROM stock_in_items 
                WHERE id = ? AND stock_in_id = ? 
                FOR UPDATE
            ");
            $stmt->execute([$stockInItemId, $stockInId]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$item) {
                throw new Exception("Stock-In item line not found.");
            }

            if ($returnQty <= 0) {
                throw new Exception("Return quantity must be greater than zero.");
            }

            // 3. Compute remaining delivered quantity
            $stmt = $pdo->prepare("
                SELECT COALESCE(SUM(quantity), 0) 
                FROM stock_in_returns 
                WHERE stock_in_item_id = ?
            ");
            $stmt->execute([$stockInItemId]);
            $alreadyReturned = (float)$stmt->fetchColumn();

            $remainingDeliverQty = (float)$item['quantity'] - $alreadyReturned;
            if ($returnQty > $remainingDeliverQty) {
                throw new Exception(
                    "Return quantity ({$returnQty} sacks) exceeds remaining delivered quantity ({$remainingDeliverQty} sacks)."
                );
            }

            // 4. Lock and check current warehouse stock for variant
            $stmt = $pdo->prepare("
                SELECT current_stock_cache, name 
                FROM rice_variants 
                WHERE id = ? 
                FOR UPDATE
            ");
            $stmt->execute([$item['rice_variant_id']]);
            $variant = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$variant) {
                throw new Exception("Rice variant not found.");
            }

            $currentStock = (float)$variant['current_stock_cache'];
            if ($returnQty > $currentStock) {
                throw new Exception(
                    "Cannot return {$returnQty} sacks. Current warehouse stock for '{$variant['name']}' is only {$currentStock} sacks."
                );
            }

            // 5. Generate Return Number (RET-YYYYMMDD-XXXX)
            $datePrefix = date('Ymd');
            $stmt = $pdo->prepare("
                SELECT COUNT(*) FROM stock_in_returns 
                WHERE return_no LIKE ?
            ");
            $stmt->execute(["RET-{$datePrefix}-%"]);
            $countToday = (int)$stmt->fetchColumn();
            $nextSeq = str_pad((string)($countToday + 1), 4, '0', STR_PAD_LEFT);
            $returnNo = "RET-{$datePrefix}-{$nextSeq}";

            // 6. Insert return record
            $costPerUnit = (float)($item['cost_per_unit'] ?? 0);
            $insertReturnStmt = $pdo->prepare("
                INSERT INTO stock_in_returns 
                (return_no, stock_in_id, stock_in_item_id, rice_variant_id, quantity, cost_per_unit, return_date, reason, notes, returned_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $insertReturnStmt->execute([
                $returnNo,
                $stockInId,
                $stockInItemId,
                $item['rice_variant_id'],
                $returnQty,
                $costPerUnit,
                $returnDate ?: date('Y-m-d'),
                $reason,
                $notes,
                $userId
            ]);
            $returnId = (int)$pdo->lastInsertId();

            // 7. STOCK OUT: Deduct from current_stock_cache
            $updateStockStmt = $pdo->prepare("
                UPDATE rice_variants 
                SET current_stock_cache = current_stock_cache - ? 
                WHERE id = ?
            ");
            $updateStockStmt->execute([$returnQty, $item['rice_variant_id']]);

            // 8. Record stock movement (movement_type = STOCK_IN_REVERSAL)
            $movementStmt = $pdo->prepare("
                INSERT INTO stock_movements 
                (rice_variant_id, movement_type, quantity, reference_type, reference_id, balance_after, performed_by, reference_date)
                VALUES (?, 'STOCK_IN_REVERSAL', ?, 'STOCK_IN_RETURN', ?, (SELECT current_stock_cache FROM rice_variants WHERE id = ?), ?, ?)
            ");
            $movementStmt->execute([
                $item['rice_variant_id'],
                $returnQty,
                $returnId,
                $item['rice_variant_id'],
                $userId,
                $returnDate ?: date('Y-m-d')
            ]);

            // 9. Audit trail
            $this->auditService->log(
                $userId,
                'stockin.returned',
                'stock',
                'stock_in_returns',
                $returnId,
                null,
                [
                    'return_no'       => $returnNo,
                    'stock_in_id'     => $stockInId,
                    'stock_in_ref'    => $stockIn['reference_no'],
                    'variant_id'      => $item['rice_variant_id'],
                    'variant_name'    => $variant['name'],
                    'quantity'        => $returnQty,
                    'reason'          => $reason
                ]
            );

            $pdo->commit();
            return [
                'success'   => true,
                'return_no' => $returnNo,
                'id'        => $returnId
            ];

        } catch (Exception $e) {
            $pdo->rollBack();
            return [
                'success' => false,
                'error'   => $e->getMessage()
            ];
        }
    }
}
