<?php
namespace App\Services;

use App\Support\Database;
use Exception;
use PDO;

class TbtService
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
        
        $sql = "SELECT t.*, 
                       ti.quantity, ti.subtotal, ti.remark as sale_remark,
                       t.quantity_reserved, t.quantity_released,
                       (t.quantity_reserved - t.quantity_released) as quantity_still_pending,
                       rv.name as variant_name, rv.unit as variant_unit, tr.sale_date, tr.created_at as sale_created_at,
                       DATEDIFF(NOW(), t.recorded_at) as days_pending
                FROM tbt_records t
                JOIN sale_items ti ON t.sale_item_id = ti.id
                JOIN sales tr ON ti.sale_id = tr.id
                JOIN rice_variants rv ON ti.rice_variant_id = rv.id
                WHERE 1 = 1";

        if (!empty($filters['status'])) {
            if ($filters['status'] === 'pending_pickup') {
                // Show records that still have pending quantity
                $sql .= " AND t.status = 'pending_pickup'";
            } elseif ($filters['status'] === 'released') {
                $sql .= " AND t.status = 'released'";
            }
        }

        if (!empty($filters['search'])) {
            $sql .= " AND t.customer_name LIKE ?";
            $params[] = "%{$filters['search']}%";
        }

        $sql .= " ORDER BY t.recorded_at ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Release items for a TBT record.
     * Supports partial releases — pass $quantityToRelease less than reserved.
     * Marks status = 'released' only when all reserved quantity is released.
     *
     * @param int   $tbtRecordId
     * @param int   $userId
     * @param float $quantityToRelease  0 = release all remaining
     * @throws Exception
     */
    public function release(int $tbtRecordId, int $userId, float $quantityToRelease = 0): array
    {
        $pdo = Database::getInstance();
        $pdo->beginTransaction();

        try {
            // Lock the record
            $stmt = $pdo->prepare(
                "SELECT * FROM tbt_records WHERE id = ? AND status = 'pending_pickup' FOR UPDATE"
            );
            $stmt->execute([$tbtRecordId]);
            $record = $stmt->fetch();

            if (!$record) {
                throw new Exception('Record already released or not found.');
            }

            $remaining = round((float)$record['quantity_reserved'] - (float)$record['quantity_released'], 4);

            if ($remaining <= 0) {
                throw new Exception('No remaining quantity to release.');
            }

            // 0 means "release all remaining"
            if ($quantityToRelease <= 0 || $quantityToRelease >= $remaining) {
                $toRelease = $remaining;
                $newReleased = (float)$record['quantity_reserved'];
                $newStatus   = 'released';
            } else {
                $toRelease   = $quantityToRelease;
                $newReleased = round((float)$record['quantity_released'] + $toRelease, 4);
                $newStatus   = 'pending_pickup'; // still has more to collect
            }

            $now = date('Y-m-d H:i:s');
            $pdo->prepare(
                "UPDATE tbt_records 
                 SET quantity_released = ?, status = ?, released_by = ?, released_at = ?
                 WHERE id = ?"
            )->execute([$newReleased, $newStatus, $userId, $now, $tbtRecordId]);

            $this->auditService->log(
                $userId,
                'tbt.released',
                'tbt_records',
                'tbt_record',
                $tbtRecordId,
                null,
                ['quantity_released' => $toRelease, 'status' => $newStatus]
            );

            // Log exact physical release for accurate daily stock-out reporting
            $pdo->prepare(
                "INSERT INTO tbt_releases (tbt_record_id, quantity, released_at, released_by) VALUES (?, ?, ?, ?)"
            )->execute([$tbtRecordId, $toRelease, $now, $userId]);

            $pdo->commit();

            return [
                'released_at'        => $now,
                'quantity_released'  => $toRelease,
                'quantity_remaining' => round($remaining - $toRelease, 4),
                'status'             => $newStatus,
            ];
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
