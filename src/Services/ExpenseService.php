<?php

namespace App\Services;

use App\Support\Database;
use PDO;

class ExpenseService
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    public function createExpense(array $data, int $userId): int
    {
        try {
            $this->pdo->beginTransaction();

            $riceVariantId = $data['rice_variant_id'] ?? null;
            $riceQty = isset($data['rice_qty']) && $data['rice_qty'] > 0 ? $data['rice_qty'] : null;

            $stmt = $this->pdo->prepare("
                INSERT INTO expenses (description, category, amount, rice_variant_id, rice_qty, expense_date, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['description'],
                $data['category'],
                $data['amount'],
                $riceVariantId,
                $riceQty,
                $data['expense_date'],
                $userId
            ]);
            
            $expenseId = (int) $this->pdo->lastInsertId();

            if ($riceVariantId && $riceQty) {
                // Deduct from stock
                $updStmt = $this->pdo->prepare("UPDATE rice_variants SET current_stock_cache = current_stock_cache - ? WHERE id = ?");
                $updStmt->execute([$riceQty, $riceVariantId]);
            }

            $this->pdo->commit();
            return $expenseId;

        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function getExpenses(string $startDate, string $endDate): array
    {
        $stmt = $this->pdo->prepare("
            SELECT e.*, u.full_name as creator_name, rv.name as rice_name
            FROM expenses e
            LEFT JOIN users u ON e.created_by = u.id
            LEFT JOIN rice_variants rv ON e.rice_variant_id = rv.id
            WHERE e.expense_date BETWEEN ? AND ?
            ORDER BY e.expense_date DESC, e.id DESC
        ");
        
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteExpense(int $id): bool
    {
        try {
            $this->pdo->beginTransaction();
            
            // Refund stock if this was a Konsumo expense
            $stmt = $this->pdo->prepare("SELECT rice_variant_id, rice_qty FROM expenses WHERE id = ? FOR UPDATE");
            $stmt->execute([$id]);
            $expense = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($expense && $expense['rice_variant_id'] && $expense['rice_qty']) {
                $refStmt = $this->pdo->prepare("UPDATE rice_variants SET current_stock_cache = current_stock_cache + ? WHERE id = ?");
                $refStmt->execute([$expense['rice_qty'], $expense['rice_variant_id']]);
            }

            $delStmt = $this->pdo->prepare("DELETE FROM expenses WHERE id = ?");
            $success = $delStmt->execute([$id]);

            $this->pdo->commit();
            return $success;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
