<?php

namespace App\Services;

use App\Support\Database;
use App\Support\Request;

class AuditService
{
    /**
     * Logs an action to the audit_logs table.
     */
    public function log(
        int $userId,
        string $action,
        string $module,
        ?string $recordType = null,
        ?int $recordId = null,
        ?array $oldValue = null,
        ?array $newValue = null
    ): void {
        $pdo = Database::getInstance();
        $ip = (new Request())->ip();

        $stmt = $pdo->prepare("
            INSERT INTO audit_logs (user_id, action, module, record_type, record_id, old_value, new_value, ip_address, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $userId,
            $action,
            $module,
            $recordType,
            $recordId,
            $oldValue ? json_encode($oldValue) : null,
            $newValue ? json_encode($newValue) : null,
            $ip
        ]);
    }
}
