<?php

namespace App\Services;

use App\Support\Database;

class CustomerDeliveryService
{
    private \PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    // ─── Generate unique receipt number DR-YYYYMMDD-XXXX ──────────────────────
    private function generateReceiptNo(string $date): string
    {
        $prefix = 'DR-' . str_replace('-', '', $date) . '-';
        $stmt   = $this->pdo->prepare(
            "SELECT receipt_no FROM customer_deliveries
             WHERE receipt_no LIKE ?
             ORDER BY id DESC LIMIT 1"
        );
        $stmt->execute([$prefix . '%']);
        $last = $stmt->fetchColumn();

        if ($last) {
            $n = (int) substr($last, -4) + 1;
        } else {
            $n = 1;
        }

        return $prefix . str_pad($n, 4, '0', STR_PAD_LEFT);
    }

    // ─── Create a new customer delivery ───────────────────────────────────────
    public function create(array $data): array
    {
        try {
            $this->pdo->beginTransaction();

            $date       = $data['delivery_date'];
            $receiptNo  = $this->generateReceiptNo($date);
            $payType    = $data['payment_type']; // 'cash' | 'utang'
            $items      = $data['items'];         // array of {rice_variant_id, quantity, unit_price}
            $userId     = $data['recorded_by'];

            // ── Calculate total and discount ─────────────────────────────────
            $total = 0;
            $totalDiscount = 0;
            
            // Get all variant IDs to check stock
            $variantIds = array_column($items, 'rice_variant_id');
            $stockMap = [];
            if (!empty($variantIds)) {
                $inQuery = implode(',', array_fill(0, count($variantIds), '?'));
                $stmtStock = $this->pdo->prepare("SELECT id, current_stock_cache FROM rice_variants WHERE id IN ($inQuery)");
                $stmtStock->execute($variantIds);
                while ($r = $stmtStock->fetch()) {
                    $stockMap[$r['id']] = (float)$r['current_stock_cache'];
                }
            }

            foreach ($items as &$item) {
                $vid   = (int) $item['rice_variant_id'];
                $qty   = (float) $item['quantity'];
                $price = (float) $item['unit_price'];
                $disc  = (float) ($item['discount_per_sack'] ?? 0);
                
                if (!isset($stockMap[$vid]) || $stockMap[$vid] < $qty) {
                    return ['success' => false, 'error' => "Insufficient stock for one or more items."];
                }

                $netPrice = max(0, $price - $disc);
                $item['subtotal'] = round($qty * $netPrice, 2);
                
                if ($payType === 'owner') {
                    $item['unit_price'] = 0;
                    $item['subtotal'] = 0;
                }
                
                $total += $item['subtotal'];
                $totalDiscount += ($qty * $disc);
            }
            unset($item);

            // ── Insert delivery header ───────────────────────────────────────
            if ($payType === 'owner') {
                $total = 0;
                $amountPaid = 0;
                $balance = 0;
                $payStatus = 'paid';
            } else {
                $amountPaid = $payType === 'cash' ? (float)($data['amount_tendered'] ?? $total) : 0;
                $balance    = $payType === 'cash' ? 0 : $total;
                $payStatus  = $payType === 'cash' ? 'paid' : 'outstanding';
            }

            $stmt = $this->pdo->prepare("
                INSERT INTO customer_deliveries
                    (receipt_no, customer_name, delivery_date, payment_type,
                     total_amount, amount_paid, balance, pay_status, notes, recorded_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $receiptNo,
                $data['customer_name'],
                $date,
                $payType,
                $total,
                $amountPaid,
                $balance,
                $payStatus,
                $data['notes'] ?? null,
                $userId,
            ]);
            $deliveryId = (int) $this->pdo->lastInsertId();

            // ── Insert into sales for Cash (daily sales) & Utang (monitor), bypass Owner ──
            $saleId = 0;
            if ($payType !== 'owner') {
                $saleStmt = $this->pdo->prepare("
                    INSERT INTO sales
                        (transaction_no, customer_name, sale_date, total_amount,
                         discount, amount_tendered, payment_method, payment_status,
                         status, notes, recorded_by)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'completed', ?, ?)
                ");
                
                $saleAmountTendered = $payType === 'cash' ? ($data['amount_tendered'] ?? $total) : 0;
                $salePaymentStatus  = $payType === 'cash' ? 'paid' : 'outstanding';
                
                $saleStmt->execute([
                    $receiptNo,
                    $data['customer_name'],
                    $date,
                    $total,
                    $totalDiscount,
                    $saleAmountTendered,
                    $payType === 'utang' ? 'cash' : $payType,
                    $salePaymentStatus,
                    "Delivery - {$receiptNo}",
                    $userId,
                ]);
                $saleId = (int)$this->pdo->lastInsertId();
            }

            // ── Prepare item insertion queries ─────────────────────────────
            $itemStmt = $this->pdo->prepare("
                INSERT INTO customer_delivery_items
                    (delivery_id, rice_variant_id, quantity, unit_price, discount_per_sack, subtotal)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $saleItemStmt = null;
            if ($saleId > 0) {
                $saleItemStmt = $this->pdo->prepare("
                    INSERT INTO sale_items (sale_id, rice_variant_id, quantity, unit_price, remark)
                    VALUES (?, ?, ?, ?, ?)
                ");
            }
            $updateStockStmt = $this->pdo->prepare("
                UPDATE rice_variants SET current_stock_cache = current_stock_cache - ? WHERE id = ?
            ");
            $movementType = $payType === 'owner' ? 'OWNER_TRANSFER' : 'SALE';
            $mvStmt = $this->pdo->prepare("
                INSERT INTO stock_movements
                    (rice_variant_id, movement_type, quantity, reference_type,
                     reference_id, balance_after, performed_by)
                VALUES (?, ?, ?, 'DELIVERY', ?, (SELECT current_stock_cache FROM rice_variants WHERE id = ?), ?)
            ");

            foreach ($items as $item) {
                $itemStmt->execute([
                    $deliveryId,
                    $item['rice_variant_id'],
                    $item['quantity'],
                    $item['unit_price'],
                    $item['discount_per_sack'] ?? 0,
                    $item['subtotal'],
                ]);
                if ($saleItemStmt !== null) {
                    $saleItemStmt->execute([
                        $saleId,
                        $item['rice_variant_id'],
                        $item['quantity'],
                        $item['unit_price'],
                        $payType === 'utang' ? 'utang' : 'out'
                    ]);
                }
                $updateStockStmt->execute([
                    $item['quantity'],
                    $item['rice_variant_id']
                ]);
                $mvStmt->execute([
                    $item['rice_variant_id'],
                    $movementType,
                    $item['quantity'],
                    $deliveryId,
                    $item['rice_variant_id'],
                    $userId,
                ]);
            }

            $this->pdo->commit();

            return ['success' => true, 'delivery_id' => $deliveryId, 'receipt_no' => $receiptNo];

        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ─── Record a payment on an utang delivery ────────────────────────────────
    public function pay(int $id, float $amount, int $userId): array
    {
        try {
            $this->pdo->beginTransaction();

            $delivery = $this->pdo->prepare(
                "SELECT * FROM customer_deliveries WHERE id = ? FOR UPDATE"
            );
            $delivery->execute([$id]);
            $row = $delivery->fetch();

            if (!$row) {
                throw new \RuntimeException('Delivery not found.');
            }
            if ($row['pay_status'] === 'paid') {
                throw new \RuntimeException('This delivery is already fully paid.');
            }
            if ($amount <= 0 || $amount > $row['balance']) {
                throw new \RuntimeException('Invalid payment amount.');
            }

            $newAmountPaid = round((float)$row['amount_paid'] + $amount, 2);
            $newBalance    = round((float)$row['total_amount'] - $newAmountPaid, 2);
            $newStatus     = $newBalance <= 0 ? 'paid' : 'outstanding';

            $this->pdo->prepare("
                UPDATE customer_deliveries
                SET amount_paid = ?, balance = ?, pay_status = ?
                WHERE id = ?
            ")->execute([$newAmountPaid, max(0, $newBalance), $newStatus, $id]);

            $this->pdo->prepare("
                INSERT INTO customer_delivery_payments
                    (delivery_id, amount_paid, recorded_by)
                VALUES (?, ?, ?)
            ")->execute([$id, $amount, $userId]);

            // If fully paid, create a sale record for the paid amount
            if ($newStatus === 'paid' && $row['payment_type'] === 'utang') {
                $saleStmt = $this->pdo->prepare("
                    INSERT INTO sales
                        (transaction_no, customer_name, sale_date, total_amount,
                         discount, amount_tendered, payment_method, payment_status,
                         status, notes, recorded_by)
                    VALUES (?, ?, CURDATE(), ?, 0, ?, 'cash', 'paid', 'completed', ?, ?)
                ");
                $saleStmt->execute([
                    $row['receipt_no'] . '-P',
                    $row['customer_name'],
                    $row['total_amount'],
                    $row['total_amount'],
                    "Utang payment settled - {$row['receipt_no']}",
                    $userId,
                ]);
            }

            $this->pdo->commit();

            return ['success' => true, 'new_balance' => max(0, $newBalance), 'pay_status' => $newStatus];

        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ─── List deliveries with optional filters ────────────────────────────────
    public function list(array $filters = []): array
    {
        $sql    = "SELECT cd.*, u.full_name as recorder_name, s.status as sale_status
                   FROM customer_deliveries cd
                   LEFT JOIN users u ON cd.recorded_by = u.id 
                   LEFT JOIN sales s ON cd.receipt_no = s.transaction_no
                   WHERE 1=1";
        $params = [];

        if (!empty($filters['payment_type'])) {
            $sql     .= " AND cd.payment_type = ?";
            $params[] = $filters['payment_type'];
        }
        if (!empty($filters['pay_status'])) {
            $sql     .= " AND cd.pay_status = ?";
            $params[] = $filters['pay_status'];
        }
        if (!empty($filters['search'])) {
            $sql     .= " AND (cd.receipt_no LIKE ? OR cd.customer_name LIKE ?)";
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }

        $sql .= " ORDER BY cd.created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ─── Get a single delivery with its items ─────────────────────────────────
    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT cd.*, u.full_name as recorder_name, s.status as sale_status
             FROM customer_deliveries cd
             LEFT JOIN users u ON cd.recorded_by = u.id
             LEFT JOIN sales s ON cd.receipt_no = s.transaction_no
             WHERE cd.id = ?"
        );
        $stmt->execute([$id]);
        $delivery = $stmt->fetch();
        if (!$delivery) return null;

        $istmt = $this->pdo->prepare("
            SELECT cdi.*, rv.name as variant_name
            FROM customer_delivery_items cdi
            JOIN rice_variants rv ON cdi.rice_variant_id = rv.id
            WHERE cdi.delivery_id = ?
        ");
        $istmt->execute([$id]);
        $delivery['items'] = $istmt->fetchAll();

        return $delivery;
    }
}
