<?php

namespace App\Services;

use App\Support\Database;
use Exception;

class StockInService
{
    private AuditService $auditService;

    public function __construct()
    {
        $this->auditService = new AuditService();
    }

    /**
     * Creates a Stock In atomically using row-level locking.
     * @throws Exception
     */
    public function createStockIn(string $referenceNo, string $supplierName, string $stockInDate, array $variants, array $quantities, array $costs, int $userId, string $notes = ''): array
    {
        $pdo = Database::getInstance();
        
        try {
            $pdo->beginTransaction();
            $items = [];
            
            // Loop through variants and lock rows
            foreach ($variants as $index => $variantId) {
                if (empty($variantId) || empty($quantities[$index])) continue;
                
                $qty = (float)$quantities[$index];
                $cost = isset($costs[$index]) ? (float)$costs[$index] : 0;
                
                // Select FOR UPDATE lock
                $stmt = $pdo->prepare("SELECT current_stock_cache, name FROM rice_variants WHERE id = ? FOR UPDATE");
                $stmt->execute([$variantId]);
                $variant = $stmt->fetch();

                if (!$variant) {
                    throw new Exception("Variant ID {$variantId} not found.");
                }

                $items[] = [
                    'id' => $variantId,
                    'qty' => $qty,
                    'cost' => $cost
                ];
            }

            if (empty($items)) {
                throw new Exception("No valid items provided for Stock In.");
            }

            $stmt = $pdo->prepare("INSERT INTO stock_ins (reference_no, supplier_name, stock_in_date, notes, recorded_by) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $referenceNo,
                $supplierName,
                $stockInDate,
                $notes,
                $userId
            ]);
            $stockInId = $pdo->lastInsertId();

            $itemStmt = $pdo->prepare("INSERT INTO stock_in_items (stock_in_id, rice_variant_id, quantity, cost_per_unit) VALUES (?, ?, ?, ?)");
            $updateStockStmt = $pdo->prepare("UPDATE rice_variants SET current_stock_cache = current_stock_cache + ? WHERE id = ?");
            
            $movementStmt = $pdo->prepare("
                INSERT INTO stock_movements (rice_variant_id, movement_type, quantity, reference_type, reference_id, balance_after, performed_by)
                VALUES (?, 'STOCK_IN', ?, 'STOCK_IN', ?, (SELECT current_stock_cache FROM rice_variants WHERE id = ?), ?)
            ");

            foreach ($items as $item) {
                $itemStmt->execute([$stockInId, $item['id'], $item['qty'], $item['cost']]);
                $updateStockStmt->execute([$item['qty'], $item['id']]);
                
                $movementStmt->execute([
                    $item['id'],
                    $item['qty'],
                    $stockInId,
                    $item['id'],
                    $userId
                ]);
            }

            $this->auditService->log(
                $userId,
                'stockin.created',
                'stock',
                'stock_in',
                $stockInId,
                null,
                ['reference_no' => $referenceNo, 'items_count' => count($items)]
            );

            $pdo->commit();
            return ['success' => true, 'id' => $stockInId];

        } catch (Exception $e) {
            $pdo->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
