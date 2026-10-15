<?php

namespace App\Services;

use App\Support\Database;
use App\Models\Sale;
use Exception;
use PDO;

class ExchangeService
{
    private AuditService $auditService;

    public function __construct()
    {
        $this->auditService = new AuditService();
    }

    /**
     * Execute a rice variant exchange in a single atomic transaction.
     *
     * Business rules enforced:
     *  - Original sale must be status='completed'
     *  - Old variant stock is restocked (SALE_REVERSAL movement)
     *  - New sale is created at the new variant's current price
     *  - If new_total < old_total  → qty is auto-incremented (no cash refund ever)
     *  - amount_paid_difference = max(0, new_total - old_total)  (≥ 0 always)
     *  - Any pending_pickup TBT records on the original sale are auto-released (Option A)
     *  - Old sale is marked `exchanged`, linked to new sale via exchanged_to_sale_id
     *
     * @param int    $originalSaleId
     * @param int    $newVariantId       Rice variant the customer wants
     * @param float  $requestedQty       Quantity customer asked for (may be bumped up)
     * @param string $customerName       Used for the new sale record
     * @param int    $userId
     * @param float|null $amountTendered Optional explicitly tendered amount when difference > 0
     * @return array ['success' => bool, 'new_sale_id' => int, 'difference' => float, 'final_qty' => float]
     */
    public function execute(
        int $originalSaleId,
        int $newVariantId,
        float $requestedQty,
        string $customerName,
        int $userId,
        ?float $amountTendered = null
    ): array {
        $pdo = Database::getInstance();

        try {
            $pdo->beginTransaction();

            // ── Step 1: Lock and validate the original sale ──────────────────
            $stmt = $pdo->prepare(
                "SELECT * FROM sales WHERE id = ? FOR UPDATE"
            );
            $stmt->execute([$originalSaleId]);
            $originalSale = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$originalSale) {
                throw new Exception("Original sale #{$originalSaleId} not found.");
            }
            if ($originalSale['status'] !== 'completed') {
                throw new Exception(
                    "Only completed sales can be exchanged. This sale is currently '{$originalSale['status']}'."
                );
            }

            $oldTotal = (float) $originalSale['total_amount'];

            // ── Step 2: Fetch original sale items ────────────────────────────
            $itemsStmt = $pdo->prepare(
                "SELECT rice_variant_id, quantity FROM sale_items WHERE sale_id = ?"
            );
            $itemsStmt->execute([$originalSaleId]);
            $originalItems = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

            // ── Step 3: Restock old variant(s) — mirrors Sale::cancel() ──────
            $undoMovement = $pdo->prepare("
                INSERT INTO stock_movements
                (rice_variant_id, reference_type, reference_id, movement_type, quantity, balance_after, performed_by)
                VALUES (?, 'SALE', ?, 'SALE_REVERSAL', ?, ?, ?)
            ");
            $updateStock = $pdo->prepare(
                "UPDATE rice_variants SET current_stock_cache = current_stock_cache + ? WHERE id = ?"
            );

            foreach ($originalItems as $oldItem) {
                $s = $pdo->prepare(
                    "SELECT current_stock_cache FROM rice_variants WHERE id = ? FOR UPDATE"
                );
                $s->execute([$oldItem['rice_variant_id']]);
                $prevStock = (float) $s->fetchColumn();
                $qty       = (float) $oldItem['quantity'];
                $newStock  = $prevStock + $qty;

                $updateStock->execute([$qty, $oldItem['rice_variant_id']]);
                $undoMovement->execute([
                    $oldItem['rice_variant_id'],
                    $originalSaleId,
                    $qty,
                    $newStock,
                    $userId
                ]);
            }

            // ── Step 4: Auto-release any pending_pickup TBT records (Option A) ──
            $tbtItems = $pdo->prepare("
                SELECT si.id AS sale_item_id
                FROM sale_items si
                JOIN tbt_records t ON t.sale_item_id = si.id
                WHERE si.sale_id = ? AND t.status = 'pending_pickup'
            ");
            $tbtItems->execute([$originalSaleId]);
            $pendingTbts = $tbtItems->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($pendingTbts)) {
                $releaseTbt = $pdo->prepare("
                    UPDATE tbt_records
                    SET status = 'released', released_by = ?, released_at = NOW()
                    WHERE sale_item_id = ? AND status = 'pending_pickup'
                ");
                foreach ($pendingTbts as $tbt) {
                    $releaseTbt->execute([$userId, $tbt['sale_item_id']]);
                }
            }

            // ── Step 5: Look up new variant price and stock ──────────────────
            $newVariantStmt = $pdo->prepare("
                SELECT v.id, v.name, v.current_stock_cache, p.current_price
                FROM rice_variants v
                JOIN rice_prices p ON p.rice_variant_id = v.id
                WHERE v.id = ? AND v.status = 'active'
                FOR UPDATE
            ");
            $newVariantStmt->execute([$newVariantId]);
            $newVariant = $newVariantStmt->fetch(PDO::FETCH_ASSOC);

            if (!$newVariant) {
                throw new Exception("New variant not found or is inactive.");
            }

            $newPrice = (float) $newVariant['current_price'];

            $finalQty  = $requestedQty;
            $discount  = ($finalQty >= 10) ? (10.00 * $finalQty) : 0.00;
            $newTotal  = ($finalQty * $newPrice) - $discount;

            if ($newPrice > 0) {
                while ($newTotal < $oldTotal) {
                    $finalQty++;
                    $discount = ($finalQty >= 10) ? (10.00 * $finalQty) : 0.00;
                    $newTotal = ($finalQty * $newPrice) - $discount;
                }
            }

            // ── Step 7: Validate new variant has enough stock ─────────────────
            if ($finalQty > (float) $newVariant['current_stock_cache']) {
                throw new Exception(
                    "Insufficient stock for '{$newVariant['name']}'. " .
                    "Need {$finalQty}, have {$newVariant['current_stock_cache']}."
                );
            }

            // ── Step 8: Create new sale record ───────────────────────────────
            $transactionNo    = 'EXC-' . date('Ymd-His') . '-' . rand(100, 999);
            $idempotencyKey   = uniqid('exchange_');
            $difference       = max(0.0, round($newTotal - $oldTotal, 2));
            
            // Set amountTendered gracefully based on if the controller actually fed one.
            if ($amountTendered === null) {
                $amountTendered = $newTotal; // fallback for exact-change / zero-difference
            } else {
                // Adjust for the original total being credited
                $amountTendered = $oldTotal + $amountTendered;
            }

            $saleStmt = $pdo->prepare("
                INSERT INTO sales
                (transaction_no, customer_name, payment_method, total_amount, discount, amount_tendered,
                 recorded_by, idempotency_key, notes)
                VALUES (?, ?, 'cash', ?, ?, ?, ?, ?, ?)
            ");
            $saleStmt->execute([
                $transactionNo,
                $customerName,
                $newTotal,
                $discount,
                $amountTendered,
                $userId,
                $idempotencyKey,
                "EXCHANGE of sale #{$originalSaleId} ({$originalSale['transaction_no']}). Old total: ₱{$oldTotal}. Difference paid: ₱{$difference}."
            ]);
            $newSaleId = (int) $pdo->lastInsertId();

            // Insert sale item
            $pdo->prepare("
                INSERT INTO sale_items (sale_id, rice_variant_id, quantity, unit_price, remark)
                VALUES (?, ?, ?, ?, 'out')
            ")->execute([$newSaleId, $newVariantId, $finalQty, $newPrice]);

            // Deduct stock for new variant
            $pdo->prepare(
                "UPDATE rice_variants SET current_stock_cache = current_stock_cache - ? WHERE id = ?"
            )->execute([$finalQty, $newVariantId]);

            // Record stock movement for new sale
            $pdo->prepare("
                INSERT INTO stock_movements
                (rice_variant_id, movement_type, quantity, reference_type, reference_id, balance_after, performed_by)
                VALUES (?, 'SALE', ?, 'SALE', ?,
                    (SELECT current_stock_cache FROM rice_variants WHERE id = ?),
                    ?)
            ")->execute([$newVariantId, $finalQty, $newSaleId, $newVariantId, $userId]);

            // ── Step 9: Mark original sale as exchanged, link to new sale ────
            $pdo->prepare("
                UPDATE sales
                SET status = 'exchanged',
                    exchanged_to_sale_id = ?,
                    amount_paid_difference = ?
                WHERE id = ?
            ")->execute([$newSaleId, $difference, $originalSaleId]);

            // ── Step 10: Audit log ────────────────────────────────────────────
            $this->auditService->log(
                $userId,
                'sale.exchanged',
                'sales',
                'sale',
                $originalSaleId,
                null,
                [
                    'original_sale_id'    => $originalSaleId,
                    'new_sale_id'         => $newSaleId,
                    'new_variant_id'      => $newVariantId,
                    'final_qty'           => $finalQty,
                    'discount'            => $discount,
                    'old_total'           => $oldTotal,
                    'new_total'           => $newTotal,
                    'difference'          => $difference,
                    'tbt_released_count'  => count($pendingTbts),
                ]
            );

            $pdo->commit();

            return [
                'success'      => true,
                'new_sale_id'  => $newSaleId,
                'difference'   => $difference,
                'final_qty'    => $finalQty,
                'new_total'    => $newTotal,
                'old_total'    => $oldTotal,
                'original_transaction_no' => $originalSale['transaction_no'],
                'original_control_number' => $originalSale['control_number'] ?? null,
            ];

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Search for a sale by transaction number, control number, or customer name+date.
     * Only returns 'completed' sales that are eligible for exchange.
     */
    public function searchOriginalSale(string $query, ?string $date = null): array
    {
        $pdo = Database::getInstance();

        $sql = "
            SELECT s.*, u.full_name AS cashier_name
            FROM sales s
            LEFT JOIN users u ON s.recorded_by = u.id
            WHERE s.status = 'completed'
              AND (
                  s.transaction_no LIKE ?
                  OR s.control_number LIKE ?
                  OR s.customer_name LIKE ?
              )
        ";
        $params = ["%{$query}%", "%{$query}%", "%{$query}%"];

        if ($date) {
            $sql .= " AND DATE(s.sale_date) = ?";
            $params[] = $date;
        }

        $sql .= " ORDER BY s.sale_date DESC, s.created_at DESC LIMIT 20";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
