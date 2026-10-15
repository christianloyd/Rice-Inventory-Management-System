<?php

namespace App\Services;

use App\Support\Database;
use PDO;

class ReportService
{
    public function getSalesSummary(string $startDate, string $endDate): array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            SELECT 
                DATE(s.sale_date) as sale_date,
                COUNT(DISTINCT CASE WHEN si.remark IN ('out', 'tbt') THEN s.id END) as total_transactions,
                COALESCE(SUM(
                    CASE 
                        WHEN si.remark IN ('out', 'tbt') THEN 
                            si.subtotal - (COALESCE(s.discount, 0) * (si.subtotal / NULLIF(tot.gross_subtotal, 0)))
                        ELSE 0 
                    END
                ), 0) as total_revenue
            FROM sales s
            JOIN sale_items si ON s.id = si.sale_id
            JOIN (
                SELECT sale_id, SUM(subtotal) as gross_subtotal 
                FROM sale_items 
                GROUP BY sale_id
            ) tot ON tot.sale_id = s.id
            WHERE s.status = 'completed' AND DATE(s.sale_date) BETWEEN ? AND ?
            GROUP BY DATE(s.sale_date)
            ORDER BY sale_date DESC
        ");
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetchAll();
    }

    public function getVariantSales(string $startDate, string $endDate, string $paymentMethod = 'cash'): array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            SELECT 
                v.name as variant_name,
                v.unit as variant_unit,
                SUM(si.quantity) as total_quantity_sold,
                SUM(si.subtotal) as total_revenue
            FROM sale_items si
            JOIN sales s ON si.sale_id = s.id
            JOIN rice_variants v ON si.rice_variant_id = v.id
            WHERE s.status = 'completed' AND s.payment_method = ? AND si.remark IN ('out', 'tbt') AND DATE(s.sale_date) BETWEEN ? AND ?
            GROUP BY v.id
            ORDER BY total_revenue DESC
        ");
        $stmt->execute([$paymentMethod, $startDate, $endDate]);
        return $stmt->fetchAll();
    }

    public function getTotalGlobalDiscounts(string $startDate, string $endDate, string $paymentMethod = 'all'): float
    {
        $pdo = Database::getInstance();
        
        $sql = "
            SELECT COALESCE(SUM(
                COALESCE(s.discount, 0) * (si.subtotal / NULLIF(tot.gross_subtotal, 0))
            ), 0)
            FROM sales s
            JOIN sale_items si ON s.id = si.sale_id
            JOIN (
                SELECT sale_id, SUM(subtotal) as gross_subtotal
                FROM sale_items
                GROUP BY sale_id
            ) tot ON tot.sale_id = s.id
            WHERE s.status = 'completed' AND si.remark IN ('out', 'tbt') AND DATE(s.sale_date) BETWEEN ? AND ?
        ";
        $params = [$startDate, $endDate];
        
        if ($paymentMethod !== 'all') {
            $sql .= " AND s.payment_method = ?";
            $params[] = $paymentMethod;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (float) $stmt->fetchColumn();
    }

    public function getDeliveryReport(string $startDate, string $endDate): array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            SELECT 
                v.name as variant_name,
                v.unit as variant_unit,
                SUM(cdi.quantity) as total_quantity_delivered,
                SUM(cdi.subtotal) as total_revenue
            FROM customer_delivery_items cdi
            JOIN customer_deliveries cd ON cdi.delivery_id = cd.id
            JOIN rice_variants v ON cdi.rice_variant_id = v.id
            JOIN sales s ON s.transaction_no = cd.receipt_no
            WHERE s.status = 'completed' AND cd.payment_type != 'owner' AND DATE(cd.delivery_date) BETWEEN ? AND ?
            GROUP BY v.id
            ORDER BY total_revenue DESC
        ");
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetchAll();
    }

    public function getComprehensiveReport(string $startDate, string $endDate): array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            SELECT 
                rv.name AS variant_name,
                rv.unit,
                rv.current_stock_cache AS stock_left,
                COALESCE(stk_in.total_in, 0) AS stock_in,
                COALESCE(stk_out.total_out, 0) + COALESCE(stk_tbt_rel.total_tbt_released, 0) + COALESCE(owner_out.total_owner_transfer, 0) + COALESCE(stk_ret.total_returned, 0) AS stock_out,
                COALESCE(stk_ret.total_returned, 0) AS returned_to_supplier,
                COALESCE(stk_tbt.total_tbt, 0) AS total_tbt,
                COALESCE(stk_out.total_revenue, 0) AS revenue,
                COALESCE(stk_void.total_void, 0) AS voided_rice,
                COALESCE(stk_exchange.total_exchanged, 0) AS exchanged_rice
            FROM rice_variants rv
            
            LEFT JOIN (
                SELECT sii.rice_variant_id, SUM(sii.quantity) AS total_in
                FROM stock_in_items sii
                JOIN stock_ins si ON sii.stock_in_id = si.id
                WHERE DATE(COALESCE(si.stock_in_date, si.created_at)) BETWEEN ? AND ?
                GROUP BY sii.rice_variant_id
            ) stk_in ON stk_in.rice_variant_id = rv.id
            
            LEFT JOIN (
                SELECT 
                    si.rice_variant_id, 
                    SUM(si.quantity_taken) AS total_out, 
                    SUM(
                        CASE 
                            WHEN si.remark IN ('out', 'tbt') THEN 
                                si.subtotal
                            ELSE 0 
                        END
                    ) AS total_revenue
                FROM sale_items si
                JOIN sales s ON si.sale_id = s.id
                WHERE s.status = 'completed' AND DATE(s.sale_date) BETWEEN ? AND ?
                GROUP BY si.rice_variant_id
            ) stk_out ON stk_out.rice_variant_id = rv.id
            
            LEFT JOIN (
                SELECT 
                    si.rice_variant_id, 
                    SUM(tr_rel.quantity) AS total_tbt_released
                FROM tbt_releases tr_rel
                JOIN tbt_records tr ON tr_rel.tbt_record_id = tr.id
                JOIN sale_items si ON tr.sale_item_id = si.id
                JOIN sales s ON si.sale_id = s.id
                WHERE s.status = 'completed' AND DATE(tr_rel.released_at) BETWEEN ? AND ?
                GROUP BY si.rice_variant_id
            ) stk_tbt_rel ON stk_tbt_rel.rice_variant_id = rv.id
            
            LEFT JOIN (
                SELECT 
                    si.rice_variant_id, 
                    SUM(tr.quantity_reserved - tr.quantity_released) AS total_tbt
                FROM tbt_records tr
                JOIN sale_items si ON tr.sale_item_id = si.id
                JOIN sales s ON si.sale_id = s.id
                WHERE s.status = 'completed' AND tr.status = 'pending_pickup' 
                  -- Note: The pending TBT doesn't usually filter by sale_date here logically if they want total pending, 
                  -- but for this report it traditionally filtered by sale_date to show pending FOR the period's sales.
                  AND DATE(s.sale_date) BETWEEN ? AND ?
                GROUP BY si.rice_variant_id
            ) stk_tbt ON stk_tbt.rice_variant_id = rv.id
            
            LEFT JOIN (
                SELECT si.rice_variant_id, SUM(si.quantity) AS total_void
                FROM sale_items si
                JOIN sales s ON si.sale_id = s.id
                WHERE s.status = 'cancelled' AND DATE(s.sale_date) BETWEEN ? AND ?
                GROUP BY si.rice_variant_id
            ) stk_void ON stk_void.rice_variant_id = rv.id
            
            LEFT JOIN (
                SELECT si.rice_variant_id, SUM(si.quantity) AS total_exchanged
                FROM sale_items si
                JOIN sales s ON si.sale_id = s.id
                WHERE s.status = 'exchanged' AND DATE(s.sale_date) BETWEEN ? AND ?
                GROUP BY si.rice_variant_id
            ) stk_exchange ON stk_exchange.rice_variant_id = rv.id
            
            LEFT JOIN (
                SELECT cdi.rice_variant_id, SUM(cdi.quantity) AS total_owner_transfer
                FROM customer_delivery_items cdi
                JOIN customer_deliveries cd ON cdi.delivery_id = cd.id
                WHERE cd.payment_type = 'owner' AND DATE(cd.delivery_date) BETWEEN ? AND ?
                GROUP BY cdi.rice_variant_id
            ) owner_out ON owner_out.rice_variant_id = rv.id

            LEFT JOIN (
                SELECT sir.rice_variant_id, SUM(sir.quantity) AS total_returned
                FROM stock_in_returns sir
                WHERE DATE(COALESCE(sir.return_date, sir.created_at)) BETWEEN ? AND ?
                GROUP BY sir.rice_variant_id
            ) stk_ret ON stk_ret.rice_variant_id = rv.id
            
            WHERE rv.status = 'active'
            ORDER BY 
                CASE 
                    WHEN COALESCE(stk_in.total_in, 0) > 0 
                      OR COALESCE(stk_out.total_out, 0) + COALESCE(stk_tbt_rel.total_tbt_released, 0) > 0 
                      OR COALESCE(stk_tbt.total_tbt, 0) > 0 
                      OR COALESCE(stk_out.total_revenue, 0) > 0 
                      OR COALESCE(stk_void.total_void, 0) > 0 
                      OR COALESCE(stk_exchange.total_exchanged, 0) > 0
                      OR COALESCE(owner_out.total_owner_transfer, 0) > 0
                      OR COALESCE(stk_ret.total_returned, 0) > 0
                    THEN 0 
                    ELSE 1 
                END ASC,
                rv.name ASC
        ");
        
        $stmt->execute([
            $startDate, $endDate, 
            $startDate, $endDate, 
            $startDate, $endDate, 
            $startDate, $endDate, 
            $startDate, $endDate,
            $startDate, $endDate,
            $startDate, $endDate,
            $startDate, $endDate
        ]);
        return $stmt->fetchAll();
    }

    public function getExpenses(string $startDate, string $endDate): array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            SELECT description, category, amount, expense_date
            FROM expenses
            WHERE expense_date BETWEEN ? AND ?
            ORDER BY expense_date ASC, id ASC
        ");
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetchAll();
    }

    public function getDailyExpenses(string $date): array
    {
        return $this->getExpenses($date, $date);
    }

    public function getTotalExpenses(string $startDate, string $endDate): float
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(amount), 0)
            FROM expenses
            WHERE expense_date BETWEEN ? AND ?
        ");
        $stmt->execute([$startDate, $endDate]);
        return (float) $stmt->fetchColumn();
    }

    public function getCoins(string $startDate, string $endDate): array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            SELECT dc.*, u.full_name as recorded_by_name
            FROM daily_coins dc
            LEFT JOIN users u ON dc.recorded_by = u.id
            WHERE dc.coin_date BETWEEN ? AND ?
            ORDER BY dc.coin_date DESC, dc.id DESC
        ");
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetchAll();
    }

    public function getTotalCoins(string $startDate, string $endDate): float
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(amount), 0) as total_coins
            FROM daily_coins
            WHERE coin_date BETWEEN ? AND ?
        ");
        $stmt->execute([$startDate, $endDate]);
        $row = $stmt->fetch();
        return (float) ($row['total_coins'] ?? 0);
    }

    public function addCoin(string $date, float $amount, ?string $notes = null, ?int $userId = null): int
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            INSERT INTO daily_coins (coin_date, amount, notes, recorded_by)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$date, $amount, $notes, $userId]);
        return (int) $pdo->lastInsertId();
    }

    public function deleteCoin(int $id): bool
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("DELETE FROM daily_coins WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getUtangReport(string $startDate, string $endDate): array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            SELECT 
                v.name as variant_name,
                v.unit as variant_unit,
                SUM(si.quantity) as total_quantity,
                SUM(
                    si.subtotal - (COALESCE(s.discount, 0) * (si.subtotal / NULLIF(tot.gross_subtotal, 0)))
                ) as total_amount
            FROM sale_items si
            JOIN sales s ON si.sale_id = s.id
            JOIN rice_variants v ON si.rice_variant_id = v.id
            JOIN (
                SELECT sale_id, SUM(subtotal) as gross_subtotal 
                FROM sale_items 
                GROUP BY sale_id
            ) tot ON tot.sale_id = s.id
            WHERE s.status != 'cancelled' AND si.remark = 'utang' AND DATE(s.sale_date) BETWEEN ? AND ?
            GROUP BY v.id
            ORDER BY total_amount DESC
        ");
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetchAll();
    }
}

