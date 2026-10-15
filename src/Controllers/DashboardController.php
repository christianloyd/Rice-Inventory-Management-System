<?php

namespace App\Controllers;

use App\Support\Request;
use App\Support\Response;
use App\Support\View;
use App\Support\Database;

class DashboardController
{
    public function index(Request $request): Response
    {
        $pdo = Database::getInstance();
        
        $activeVariants = $pdo->query("SELECT COUNT(*) FROM rice_variants WHERE status='active'")->fetchColumn();
        
        $lowStock = $pdo->query("SELECT COUNT(*) FROM rice_variants WHERE status='active' AND current_stock_cache <= minimum_stock")->fetchColumn();
        
        $todayStats = $pdo->query("
            SELECT 
                COALESCE(SUM(
                    CASE 
                        WHEN si.remark IN ('out', 'tbt') THEN 
                            si.subtotal - (COALESCE(s.discount, 0) * (si.subtotal / NULLIF(tot.gross_subtotal, 0)))
                        ELSE 0 
                    END
                ), 0) as revenue,
                COUNT(DISTINCT CASE WHEN si.remark IN ('out', 'tbt') THEN s.id END) as counts
            FROM sales s
            JOIN sale_items si ON s.id = si.sale_id
            JOIN (
                SELECT sale_id, SUM(subtotal) as gross_subtotal 
                FROM sale_items 
                GROUP BY sale_id
            ) tot ON tot.sale_id = s.id
            WHERE s.status='completed' AND DATE(s.sale_date) = CURDATE()
        ")->fetch();
        $todaySales = $todayStats['revenue'];
        $todayTransactions = $todayStats['counts'];
        $todayQuantity = $pdo->query("
            SELECT COALESCE(SUM(si.quantity), 0) 
            FROM sale_items si 
            JOIN sales s ON si.sale_id = s.id 
            WHERE s.status='completed' AND si.remark IN ('out', 'tbt') AND DATE(s.sale_date) = CURDATE()
        ")->fetchColumn();

        $monthStats = $pdo->query("
            SELECT 
                COALESCE(SUM(
                    CASE 
                        WHEN si.remark IN ('out', 'tbt') THEN 
                            si.subtotal - (COALESCE(s.discount, 0) * (si.subtotal / NULLIF(tot.gross_subtotal, 0)))
                        ELSE 0 
                    END
                ), 0) as revenue
            FROM sales s
            JOIN sale_items si ON s.id = si.sale_id
            JOIN (
                SELECT sale_id, SUM(subtotal) as gross_subtotal 
                FROM sale_items 
                GROUP BY sale_id
            ) tot ON tot.sale_id = s.id
            WHERE s.status='completed' AND MONTH(s.sale_date) = MONTH(CURDATE()) AND YEAR(s.sale_date) = YEAR(CURDATE())
        ")->fetch();
        $monthSales = $monthStats['revenue'];
        $monthQuantity = $pdo->query("
            SELECT COALESCE(SUM(si.quantity), 0) 
            FROM sale_items si 
            JOIN sales s ON si.sale_id = s.id 
            WHERE s.status='completed' AND si.remark IN ('out', 'tbt') AND MONTH(s.sale_date) = MONTH(CURDATE()) AND YEAR(s.sale_date) = YEAR(CURDATE())
        ")->fetchColumn();

        // Recent Sales mapped with quantity
        $recentSales = $pdo->query("
            SELECT s.*, COALESCE(SUM(si.quantity), 0) as total_quantity 
            FROM sales s 
            LEFT JOIN sale_items si ON s.id = si.sale_id 
            WHERE s.status='completed' 
            GROUP BY s.id 
            ORDER BY s.sale_date DESC 
            LIMIT 5
        ")->fetchAll();

        // Low stock full listing for bottom right pane
        $lowStockItems = $pdo->query("
            SELECT v.name, v.current_stock_cache, v.minimum_stock 
            FROM rice_variants v 
            WHERE v.status='active' AND v.current_stock_cache <= v.minimum_stock
            ORDER BY v.current_stock_cache ASC
            LIMIT 5
        ")->fetchAll();

        return View::render('dashboard/index', [
            'activeVariants' => $activeVariants,
            'lowStock' => $lowStock,
            'todaySales' => $todaySales,
            'todayQuantity' => $todayQuantity,
            'todayTransactions' => $todayTransactions,
            'monthSales' => $monthSales,
            'monthQuantity' => $monthQuantity,
            'recentSales' => $recentSales,
            'lowStockItems' => $lowStockItems
        ]);
    }
}
