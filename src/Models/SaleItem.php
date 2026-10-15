<?php

namespace App\Models;

use App\Support\Database;
use PDO;
use Exception;

class SaleItem
{
    public static function getBySale(int $saleId): array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            SELECT i.*, v.name as variant_name, v.unit as variant_unit
            FROM sale_items i
            JOIN rice_variants v ON i.rice_variant_id = v.id
            WHERE i.sale_id = ?
        ");
        $stmt->execute([$saleId]);
        return $stmt->fetchAll();
    }

    public static function createSale(string $customerName, float $amountTendered, string $notes, array $items, int $userId): int
    {
        $pdo = Database::getInstance();
        $pdo->beginTransaction();

        try {
            // Validate Stock properly before proceeding
            $totalAmount = 0;
            foreach ($items as &$item) {
                $check = $pdo->prepare("SELECT current_stock_cache FROM rice_variants WHERE id = ? FOR UPDATE");
                $check->execute([$item['rice_variant_id']]);
                $currentStock = $check->fetchColumn();

                if ($currentStock < $item['quantity']) {
                    throw new Exception("Insufficient stock for one or more variants.");
                }
                
                $totalAmount += ($item['quantity'] * $item['unit_price']);
            }

            // Create Sale Record
            $invoiceNumber = 'INV-' . date('Ymd-His') . '-' . rand(100, 999);
            $stmt = $pdo->prepare("INSERT INTO sales (invoice_number, customer_name, total_amount, amount_tendered, notes, cashier_id, status) VALUES (?, ?, ?, ?, ?, ?, 'completed')");
            $stmt->execute([$invoiceNumber, $customerName, $totalAmount, $amountTendered, $notes, $userId]);
            $saleId = $pdo->lastInsertId();

            // Insert Items & Drain Stock
            foreach ($items as $item) {
                // Insert Item
                $stmt = $pdo->prepare("INSERT INTO sale_items (sale_id, rice_variant_id, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?)");
                $subtotal = $item['quantity'] * $item['unit_price'];
                $stmt->execute([$saleId, $item['rice_variant_id'], $item['quantity'], $item['unit_price'], $subtotal]);

                // Update Stock Movement
                $s = $pdo->prepare("SELECT current_stock_cache FROM rice_variants WHERE id = ? FOR UPDATE");
                $s->execute([$item['rice_variant_id']]);
                $prevStock = $s->fetchColumn();
                $newStock = $prevStock - $item['quantity'];

                $stmt = $pdo->prepare("INSERT INTO stock_movements (rice_variant_id, reference_type, reference_id, type, quantity_change, previous_stock, new_stock, user_id) VALUES (?, 'sale', ?, 'out', ?, ?, ?, ?)");
                $stmt->execute([$item['rice_variant_id'], $saleId, -$item['quantity'], $prevStock, $newStock, $userId]); // Notice negative quantity_change

                // Update variant cache
                $stmt = $pdo->prepare("UPDATE rice_variants SET current_stock_cache = ? WHERE id = ?");
                $stmt->execute([$newStock, $item['rice_variant_id']]);
            }

            $pdo->commit();
            return $saleId;
        } catch (\Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
