<?php

namespace App\Services;

use App\Support\Database;
use App\Models\RiceVariant;
use Exception;

class SaleService
{
    private AuditService $auditService;

    public function __construct()
    {
        $this->auditService = new AuditService();
    }

    /**
     * Creates a sale atomically using row-level locking.
     * @throws Exception
     */
    public function createSale(array $variants, array $quantities, array $inputPrices, array $remarks, string $customerName, string $paymentMethod, float $amountTendered, int $userId, array $notes = [], float $globalDiscount = 0, ?string $saleDate = null, array $quantitiesTaken = [], ?string $chequeNumber = null, ?string $chequeDate = null): array
    {
        $pdo = Database::getInstance();
        
        try {
            $pdo->beginTransaction();

            $totalAmount = 0;
            $items = [];
            $hasUtang = false;

            // Check upfront if any remark indicates utang / credit sale
            foreach ($remarks as $r) {
                if ($r === 'utang') {
                    $hasUtang = true;
                    break;
                }
            }

            // First, aggregate total quantities per variant to check wholesale logic
            $variantTotalQty = [];
            foreach ($variants as $index => $variantId) {
                if (empty($variantId) || empty($quantities[$index])) continue;
                $vId = (int)$variantId;
                $variantTotalQty[$vId] = ($variantTotalQty[$vId] ?? 0) + (float)$quantities[$index];
            }

            // Loop through variants and lock rows, then validate stock
            foreach ($variants as $index => $variantId) {
                if (empty($variantId) || empty($quantities[$index])) continue;
                
                $qty = (float)$quantities[$index];
                $vIdForDiscount = (int)$variantId;
                $aggregatedQty = $variantTotalQty[$vIdForDiscount] ?? 0;
                
                // Select FOR UPDATE places a write-lock on the rows being read until the transaction commits.
                $stmt = $pdo->prepare("
                    SELECT v.current_stock_cache, v.name, v.allow_wholesale_discount, p.current_price 
                    FROM rice_variants v
                    JOIN rice_prices p ON v.id = p.rice_variant_id
                    WHERE v.id = ? 
                    FOR UPDATE
                ");
                $stmt->execute([$variantId]);
                $variant = $stmt->fetch();

                if (!$variant) {
                    throw new Exception("Variant not found.");
                }

                if ($qty > $variant['current_stock_cache']) {
                    throw new Exception("Insufficient stock for " . $variant['name'] . ". Only " . $variant['current_stock_cache'] . " available.");
                }

                $price = (float)$variant['current_price'];
                $remark = isset($remarks[$index]) ? $remarks[$index] : 'out';
                if ($remark === 'utang') {
                    $hasUtang = true;
                }
                
                // Automatic Wholesale Discount: only apply for cash purchases (NEVER on Utang / Credit)
                $wholesaleDiscount = 0;
                $isDiscountable = (int)($variant['allow_wholesale_discount'] ?? 1) === 1;
                if (!$hasUtang && $remark !== 'utang' && $isDiscountable && $aggregatedQty >= 10) {
                    $wholesaleDiscount = 10 * $qty;
                }

                $subtotal = $qty * $price;
                $totalAmount += $subtotal;
                $globalDiscount += $wholesaleDiscount;

                $items[] = [
                    'id'      => $variantId,
                    'qty'     => $qty,
                    'price'   => $price,
                    'subtotal'=> $subtotal,
                    'remark'  => $remark,
                    '_index'  => $index,   // preserve original form index for quantity_taken lookup
                ];
            }

            if (empty($items)) {
                throw new Exception("No valid items in sale.");
            }

            // Apply discount to total amount first so amount tendered is validated against net total
            $totalAmount = max((float)0, $totalAmount - $globalDiscount);

            // Force cheque amounts fully tendered if they didn't input enough, or whatever they inputted
            // Wait, actually the user wanted to type the amount, let's just not force it.
            // But if they haven't reloaded yet, it will send 0. So let's force it if it's 0.
            if ($paymentMethod === 'cheque' && $amountTendered < $totalAmount) {
                $amountTendered = $totalAmount;
            }

            if (!$hasUtang && round($amountTendered, 2) < round($totalAmount, 2)) {
                throw new Exception("Amount tendered is less than the total amount.");
            }
            if ($hasUtang && (trim($customerName) === '' || strtolower(trim($customerName)) === 'walk-in customer')) {
                throw new Exception("Customer Name is required for Utang (Credit) sales.");
            }

            $transactionNo = 'INV-' . date('Ymd-His') . '-' . rand(100, 999);
            $noteText = isset($notes[0]) ? $notes[0] : '';
            $idempotencyKey = uniqid('sale_');
            // sale_date: use the explicitly provided date (backdated/after-hours) or today
            $saleDateValue = (!empty($saleDate) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $saleDate))
                ? $saleDate
                : date('Y-m-d');

            $paymentStatus = ($amountTendered >= $totalAmount) ? 'paid' : 'outstanding';

            // Insert Sale
            $saleStmt = $pdo->prepare("
                INSERT INTO sales (transaction_no, customer_name, payment_method, payment_status, total_amount, discount, amount_tendered, recorded_by, idempotency_key, notes, sale_date, cheque_number, cheque_date)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $saleStmt->execute([
                $transactionNo,
                $customerName,
                $paymentMethod,
                $paymentStatus,
                $totalAmount,
                $globalDiscount,
                $amountTendered,
                $userId,
                $idempotencyKey,
                $noteText,
                $saleDateValue,
                empty($chequeNumber) ? null : $chequeNumber,
                empty($chequeDate) ? null : $chequeDate
            ]);
            $saleId = $pdo->lastInsertId();

            if ($hasUtang && $amountTendered > 0) {
                // Record the initial downpayment in utang_payments
                $pdo->prepare("INSERT INTO utang_payments (sale_id, amount_paid, recorded_by) VALUES (?, ?, ?)")
                    ->execute([$saleId, $amountTendered, $userId]);
            }

            $itemStmt = $pdo->prepare("INSERT INTO sale_items (sale_id, rice_variant_id, quantity, unit_price, remark, quantity_taken) VALUES (?, ?, ?, ?, ?, ?)");
            $updateStockStmt = $pdo->prepare("UPDATE rice_variants SET current_stock_cache = current_stock_cache - ? WHERE id = ?");
            $movementStmt = $pdo->prepare("
                INSERT INTO stock_movements (rice_variant_id, movement_type, quantity, reference_type, reference_id, balance_after, performed_by, reference_date)
                VALUES (?, 'SALE', ?, 'SALE', ?, (SELECT current_stock_cache FROM rice_variants WHERE id = ?), ?, ?)
            ");
            // TBT stmt: includes quantity_reserved and quantity_released
            $tbtStmt = $pdo->prepare("INSERT INTO tbt_records (sale_item_id, customer_name, status, quantity_reserved, quantity_released) VALUES (?, ?, 'pending_pickup', ?, 0)");

            foreach ($items as $item) {
                // Determine how much was physically taken at sale time
                $quantityTaken = isset($quantitiesTaken[$item['_index']]) && $quantitiesTaken[$item['_index']] !== ''
                    ? min((float)$quantitiesTaken[$item['_index']], $item['qty'])
                    : $item['qty']; // default: fully taken
                $quantityPending = round($item['qty'] - $quantityTaken, 4);

                $itemStmt->execute([$saleId, $item['id'], $item['qty'], $item['price'], $item['remark'], $quantityTaken]);
                $saleItemId = $pdo->lastInsertId();

                // Create TBT record for:
                //   Any TBT or Utang items where the customer didn't take everything immediately
                if (($item['remark'] === 'tbt' || $item['remark'] === 'utang') && $quantityPending > 0) {
                    $tbtStmt->execute([$saleItemId, $customerName, $quantityPending]);
                }

                $updateStockStmt->execute([$item['qty'], $item['id']]);

                // Record movement history with business reference_date for reporting
                $movementStmt->execute([
                    $item['id'],
                    $item['qty'],
                    $saleId,
                    $item['id'],
                    $userId,
                    $saleDateValue
                ]);
            }

            // Write Audit Log
            $this->auditService->log(
                $userId,
                'sale.created',
                'sales',
                'sale',
                $saleId,
                null, 
                ['transaction_no' => $transactionNo, 'total_amount' => $totalAmount]
            );

            $pdo->commit();
            return ['success' => true, 'id' => $saleId];

        } catch (Exception $e) {
            $pdo->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
