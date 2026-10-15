<?php
namespace App\Services;

use App\Support\Database;
use App\Enums\TransactionRemark;
use Exception;
use PDO;

class TransactionService
{
    private AuditService $auditService;
    private IdempotencyService $idempotencyService;

    public function __construct()
    {
        $this->auditService = new AuditService();
        $this->idempotencyService = new IdempotencyService();
    }

    public function createTransaction(array $header, array $items): array
    {
        if (empty($items)) {
            throw new Exception('At least one line item is required.');
        }

        $pdo = Database::getInstance();
        $pdo->beginTransaction();

        try {
            // Insert header
            $stmt = $pdo->prepare("INSERT INTO transactions (transaction_date, created_by) VALUES (?, ?)");
            $stmt->execute([$header['transaction_date'], $header['created_by']]);
            $transactionId = $pdo->lastInsertId();

            $itemStmt = $pdo->prepare(
                "INSERT INTO transaction_items (transaction_id, receipt_no, rice_variant_id, quantity, unit_price, subtotal, remarks, fulfillment_type) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );

            $tbtStmt = $pdo->prepare(
                "INSERT INTO tbt_records (transaction_item_id, customer_name, contact_number, status) 
                 VALUES (?, ?, ?, 'pending_pickup')"
            );

            $updateStockStmt = $pdo->prepare(
                "UPDATE rice_variants SET current_stock_cache = current_stock_cache - ? WHERE id = ?"
            );
            
            $movementStmt = $pdo->prepare("
                INSERT INTO stock_movements (rice_variant_id, movement_type, quantity, reference_type, reference_id, balance_after, performed_by)
                VALUES (?, 'SALE_OUT', ?, 'DAILY_TRANSACTION', ?, (SELECT current_stock_cache FROM rice_variants WHERE id = ?), ?)
            ");

            foreach ($items as $item) {
                // Insert item
                $subtotal = $item['quantity'] * $item['unit_price'];
                $itemStmt->execute([
                    $transactionId,
                    $item['receipt_no'],
                    $item['rice_variant_id'],
                    $item['quantity'],
                    $item['unit_price'],
                    $subtotal,
                    $item['remarks'],
                    $item['fulfillment_type']
                ]);
                $itemId = $pdo->lastInsertId();

                // If TBT, insert tbt record
                if ($item['remarks'] === TransactionRemark::TBT) {
                    if (empty($item['customer_name'])) {
                        throw new Exception('Customer name is required for TBT items.');
                    }
                    $tbtStmt->execute([
                        $itemId,
                        $item['customer_name'],
                        $item['contact_number'] ?? null
                    ]);
                }

                // Deduct stock for all items
                $updateStockStmt->execute([$item['quantity'], $item['rice_variant_id']]);

                // Record stock movement
                $movementStmt->execute([
                    $item['rice_variant_id'],
                    -$item['quantity'],
                    $transactionId,
                    $item['rice_variant_id'],
                    $header['created_by']
                ]);
            }

            $this->auditService->log(
                $header['created_by'],
                'transaction.created',
                'transactions',
                'transaction',
                $transactionId,
                null,
                ['transaction_date' => $header['transaction_date'], 'items_count' => count($items)]
            );

            $pdo->commit();
            return ['success' => true, 'id' => $transactionId];
        } catch (Exception $e) {
            $pdo->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
