<?php
namespace App\Services;

use App\Support\Database;
use Exception;
use PDO;

class UtangService
{
    private AuditService $auditService;

    public function __construct()
    {
        $this->auditService = new AuditService();
    }

    public function listRecords(array $filters): array
    {
        $pdo = Database::getInstance();
        $params = [];
        
        $sql = "SELECT s.*, 
                       u.full_name as cashier_name,
                       COALESCE((SELECT SUM(amount_paid) FROM utang_payments WHERE sale_id = s.id), 0) as paid_amount,
                       (s.total_amount - COALESCE((SELECT SUM(amount_paid) FROM utang_payments WHERE sale_id = s.id), 0)) as balance_remaining,
                       (SELECT GROUP_CONCAT(CONCAT(rv.name, ' (', CAST(v.quantity AS UNSIGNED), ')') SEPARATOR ', ') 
                        FROM sale_items v JOIN rice_variants rv ON v.rice_variant_id = rv.id 
                        WHERE v.sale_id = s.id) as variants_summary
                FROM sales s
                LEFT JOIN users u ON s.recorded_by = u.id
                WHERE EXISTS (SELECT 1 FROM sale_items i WHERE i.sale_id = s.id AND i.remark = 'utang')";

        if (!empty($filters['status']) && in_array($filters['status'], ['outstanding', 'paid'])) {
            $sql .= " AND s.payment_status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND s.customer_name LIKE ?";
            $params[] = "%{$filters['search']}%";
        }

        $sql .= " ORDER BY s.created_at ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function addPayment(int $saleId, float $amount, int $userId): array
    {
        $pdo = Database::getInstance();
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare("SELECT total_amount, payment_status, COALESCE((SELECT SUM(amount_paid) FROM utang_payments WHERE sale_id = sales.id), 0) as paid_amount FROM sales WHERE id = ? FOR UPDATE");
            $stmt->execute([$saleId]);
            $sale = $stmt->fetch();

            if (!$sale) {
                throw new Exception("Sale record not found.");
            }

            if ($sale['payment_status'] === 'paid') {
                throw new Exception("Sale is already fully paid.");
            }

            $currentBalance = round((float)($sale['total_amount'] - $sale['paid_amount']), 2);
            if ($amount <= 0) {
                throw new Exception("Payment amount must be greater than zero.");
            }

            if ($amount > ($currentBalance + 0.01)) {
                throw new Exception("Payment amount (₱" . number_format($amount, 2) . ") exceeds remaining balance (₱" . number_format($currentBalance, 2) . ").");
            }

            $pdo->prepare("INSERT INTO utang_payments (sale_id, amount_paid, recorded_by) VALUES (?, ?, ?)")
                ->execute([$saleId, $amount, $userId]);

            $newSum = $sale['paid_amount'] + $amount;
            if ($newSum >= $sale['total_amount']) {
                $pdo->prepare("UPDATE sales SET payment_status = 'paid' WHERE id = ?")->execute([$saleId]);
                // Sync to customer_deliveries if it's a delivery
                $pdo->prepare("UPDATE customer_deliveries SET pay_status = 'paid' WHERE receipt_no = (SELECT transaction_no FROM sales WHERE id = ?)")->execute([$saleId]);
            }
            
            // Also update amount_tendered to reflect total received cash.
            $pdo->prepare("UPDATE sales SET amount_tendered = amount_tendered + ? WHERE id = ?")->execute([$amount, $saleId]);

            $this->auditService->log($userId, 'utang.payment', 'sales', 'sale', $saleId, null, ['amount' => $amount]);
            $pdo->commit();

            return ['success' => true, 'new_balance' => max(0, $sale['total_amount'] - $newSum)];
        } catch (\Exception $e) {
            $pdo->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
