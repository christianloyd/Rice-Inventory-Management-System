<?php
namespace App\Services;

use App\Support\Database;
use PDO;

class DailyStockSummaryService
{
    /**
     * Get the derived stock summary for a given date.
     * Assumes a fully derived calculation via SQL query.
     */
    public function forDate(string $date): array
    {
        $pdo = Database::getInstance();
        
        $sql = "SELECT 
                    rv.id AS rice_variant_id,
                    rv.name AS variant_name,
                    COALESCE(prev.old_stock, 0) AS old_stock,
                    COALESCE(si.stock_in_total, 0) AS stock_in,
                    COALESCE(ti.walkin_total, 0) AS stock_out_walkin,
                    COALESCE(ti.delivered_total, 0) AS stock_out_delivered,
                    COALESCE(ret.return_total, 0) AS stock_out_returned,
                    (COALESCE(ti.walkin_total, 0) + COALESCE(ti.delivered_total, 0) + COALESCE(ret.return_total, 0)) AS stock_out_total,
                    (COALESCE(prev.old_stock, 0) + COALESCE(si.stock_in_total, 0) - (COALESCE(ti.walkin_total, 0) + COALESCE(ti.delivered_total, 0) + COALESCE(ret.return_total, 0))) AS remaining_stock
                FROM rice_variants rv
                
                LEFT JOIN (
                    -- For 'Old Stock', ideally use daily_stock_snapshots for better performance.
                    -- If no snapshot for (date - 1), we fallback to 0 in this basic implementation.
                    SELECT rice_variant_id, remaining_stock AS old_stock 
                    FROM daily_stock_snapshots 
                    WHERE snapshot_date = DATE_SUB(?, INTERVAL 1 DAY)
                ) prev ON prev.rice_variant_id = rv.id
                
                LEFT JOIN (
                    SELECT rice_variant_id, SUM(quantity) AS stock_in_total
                    FROM stock_deliveries 
                    WHERE stock_in_date = ? 
                    GROUP BY rice_variant_id
                ) si ON si.rice_variant_id = rv.id
                
                LEFT JOIN (
                    SELECT 
                        rice_variant_id,
                        SUM(CASE WHEN remark = 'out' THEN quantity ELSE 0 END) AS walkin_total,
                        SUM(CASE WHEN remark = 'tbt' THEN quantity ELSE 0 END) AS delivered_total
                    FROM sale_items
                    JOIN sales ON sale_items.sale_id = sales.id
                    WHERE DATE(sales.sale_date) = ?
                    GROUP BY rice_variant_id
                ) ti ON ti.rice_variant_id = rv.id

                LEFT JOIN (
                    SELECT rice_variant_id, SUM(quantity) AS return_total
                    FROM stock_in_returns
                    WHERE DATE(COALESCE(return_date, created_at)) = ?
                    GROUP BY rice_variant_id
                ) ret ON ret.rice_variant_id = rv.id
                
                WHERE rv.status = 'active'
                ORDER BY rv.name";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$date, $date, $date, $date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
