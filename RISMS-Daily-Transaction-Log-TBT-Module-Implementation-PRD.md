# Implementation PRD
# Daily Transaction Log & TBT (To Be Taken) Module
## Rice Inventory and Sales Management System (RISMS)

**Document Type:** Technical Implementation Specification (companion to the Module Product PRD)
**Stack:** PHP (vanilla, PDO) · MySQL 8 / InnoDB · HTML · Tailwind CSS · JavaScript
**Audience:** Developer(s) building this module into the existing RISMS codebase

This document translates the Module Product PRD into a concrete, buildable technical plan for the RISMS codebase already scaffolded in the main Implementation PRD. It only covers what's new or changed — folder additions, migrations, controllers, services, views, and a task-level build order for this module.

---

## Table of Contents

1. Relationship to Existing Codebase
2. File/Folder Additions
3. Database Migrations (DDL)
4. Models
5. Controller & Model Responsibilities
6. Core Service Classes (Business Logic)
7. Routing Table Additions
8. Sale Transaction (Multi-Variant + TBT) — Step-by-Step Implementation
9. Stock-In Transaction — Step-by-Step Implementation
10. TBT Release — Step-by-Step Implementation
11. Daily Stock Summary — Query Implementation
12. Validation Rules Implementation
13. Concurrency & Atomicity Handling
14. Audit Logging Hooks
15. Frontend Structure (Views, Partials, JS)
16. Loading States & Idempotency
17. Task Breakdown (Build Checklist)
18. Definition of Done (per sub-module)
19. File-by-File Manifest

---

## 1. Relationship to Existing Codebase

This module slots into the existing `/risms` project structure defined in the main Implementation PRD. It adds new Models, Controllers, Services, Views, and migrations — it does not require restructuring anything already built (Auth, RBAC, CSRF middleware, PriceService, AuditService, IdempotencyService are all reused as-is).

Existing services this module depends on:
- `Database.php` (PDO singleton) — reused
- `AuditService.php` — reused, called on every write
- `IdempotencyService.php` — reused for transaction submission
- `Validator.php` — reused
- `RiceVariant` model / `rice_variants` table — reused as the FK target for variants

---

## 2. File/Folder Additions

```
/src
├── /Controllers
│   ├── TransactionController.php      # NEW
│   ├── StockInController.php          # NEW (separate from existing StockInController if one already exists for a different flow — merge if so)
│   └── TbtController.php              # NEW
├── /Models
│   ├── Transaction.php                # NEW
│   ├── TransactionItem.php            # NEW
│   ├── TbtRecord.php                  # NEW
│   └── StockIn.php                    # NEW (rename/merge if a StockIn model already exists)
├── /Services
│   ├── TransactionService.php         # NEW
│   ├── TbtService.php                 # NEW
│   └── DailyStockSummaryService.php   # NEW
├── /Enums
│   ├── TransactionRemark.php          # NEW — enum: OUT, TBT
│   ├── FulfillmentType.php            # NEW — enum: WALKIN, DELIVERED
│   └── TbtStatus.php                  # NEW — enum: PENDING_PICKUP, RELEASED

/views
├── /transactions
│   ├── create.php                     # NEW — multi-line-item sale entry form
├── /stock-ins
│   ├── create.php                     # NEW — delivery entry form
│   └── index.php                      # NEW — recent stock-ins list
├── /tbt
│   └── index.php                      # NEW — TBT monitoring page
├── /reports
│   └── daily-stock-summary.php        # NEW

/public/assets/js
├── transaction-form.js                # NEW — dynamic line-item add/remove, conditional TBT fields
└── tbt-monitor.js                     # NEW — search/filter/release AJAX handling
```

---

## 3. Database Migrations (DDL)

```sql
-- Migration: 2026_xx_xx_create_transactions_table.sql
CREATE TABLE transactions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    transaction_date DATE NOT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB;

-- Migration: 2026_xx_xx_create_transaction_items_table.sql
CREATE TABLE transaction_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    transaction_id BIGINT UNSIGNED NOT NULL,
    receipt_no VARCHAR(20) NOT NULL,
    rice_variant_id BIGINT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    remarks ENUM('out','tbt') NOT NULL DEFAULT 'out',
    fulfillment_type ENUM('walkin','delivered') NOT NULL DEFAULT 'walkin',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (transaction_id) REFERENCES transactions(id),
    FOREIGN KEY (rice_variant_id) REFERENCES rice_variants(id),
    INDEX idx_receipt (receipt_no),
    INDEX idx_variant_date (rice_variant_id, created_at)
) ENGINE=InnoDB;

-- Migration: 2026_xx_xx_create_tbt_records_table.sql
CREATE TABLE tbt_records (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    transaction_item_id BIGINT UNSIGNED NOT NULL UNIQUE,
    customer_name VARCHAR(150) NOT NULL,
    contact_number VARCHAR(30) NULL,
    status ENUM('pending_pickup','released') NOT NULL DEFAULT 'pending_pickup',
    recorded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    released_by BIGINT UNSIGNED NULL,
    released_at DATETIME NULL,
    FOREIGN KEY (transaction_item_id) REFERENCES transaction_items(id),
    FOREIGN KEY (released_by) REFERENCES users(id),
    INDEX idx_status (status),
    INDEX idx_customer_name (customer_name)
) ENGINE=InnoDB;

-- Migration: 2026_xx_xx_create_stock_ins_table.sql
CREATE TABLE stock_ins (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    stock_in_date DATE NOT NULL,
    rice_variant_id BIGINT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    reference_note VARCHAR(255) NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (rice_variant_id) REFERENCES rice_variants(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_variant_date (rice_variant_id, stock_in_date)
) ENGINE=InnoDB;
```

**Note:** if the existing `stock_movements` table (from the main Implementation PRD) is already the single source of truth for stock deltas, both `TransactionService` and `StockInService` below must also insert a `stock_movements` row per unit of stock change (`movement_type = 'sale_out' | 'stock_in'`), so `InventoryService`'s existing stock-derivation logic picks these up automatically without needing a parallel computation path.

---

## 4. Models

```php
// src/Models/Transaction.php
class Transaction {
    public int $id;
    public string $transactionDate;
    public int $createdBy;
    public string $createdAt;
    /** @var TransactionItem[] */
    public array $items = [];
}

// src/Models/TransactionItem.php
class TransactionItem {
    public int $id;
    public int $transactionId;
    public string $receiptNo;
    public int $riceVariantId;
    public int $quantity;
    public float $unitPrice;
    public float $subtotal;
    public string $remarks;          // TransactionRemark::OUT | TBT
    public string $fulfillmentType;  // FulfillmentType::WALKIN | DELIVERED
    public ?TbtRecord $tbtRecord = null;
}

// src/Models/TbtRecord.php
class TbtRecord {
    public int $id;
    public int $transactionItemId;
    public string $customerName;
    public ?string $contactNumber;
    public string $status;           // TbtStatus::PENDING_PICKUP | RELEASED
    public string $recordedAt;
    public ?int $releasedBy;
    public ?string $releasedAt;
}

// src/Models/StockIn.php
class StockIn {
    public int $id;
    public string $stockInDate;
    public int $riceVariantId;
    public int $quantity;
    public ?string $referenceNote;
    public int $createdBy;
    public string $createdAt;
}
```

---

## 5. Controller & Model Responsibilities

| Controller | Responsibility |
|---|---|
| `TransactionController` | Render sale entry form (`GET /transactions/new`); validate + delegate to `TransactionService::createTransaction()` (`POST /transactions`) |
| `StockInController` | Render stock-in form + recent list; delegate to `StockInService::createStockIn()` |
| `TbtController` | Render TBT monitoring list with filters (`GET /tbt`); handle release action (`POST /tbt/{id}/release`) |
| `ReportController` (extend existing) | Add `dailyStockSummary()` action calling `DailyStockSummaryService` |

---

## 6. Core Service Classes (Business Logic)

```php
// src/Services/TransactionService.php
class TransactionService
{
    public function __construct(
        private PDO $db,
        private AuditService $audit,
        private IdempotencyService $idempotency
    ) {}

    /**
     * @param array $header ['transaction_date' => ..., 'created_by' => ...]
     * @param array $items  each: ['receipt_no','rice_variant_id','quantity','unit_price',
     *                              'remarks','fulfillment_type','customer_name'?,'contact_number'?]
     */
    public function createTransaction(array $header, array $items): Transaction
    {
        if (empty($items)) {
            throw new ValidationException('At least one line item is required.');
        }

        $this->db->beginTransaction();
        try {
            $transactionId = $this->insertTransactionHeader($header);

            foreach ($items as $item) {
                $subtotal = $item['quantity'] * $item['unit_price'];
                $itemId = $this->insertTransactionItem($transactionId, $item, $subtotal);

                // Stock deduction happens for BOTH 'out' and 'tbt' remarks —
                // TBT is deducted immediately per business rule.
                $this->recordStockMovement(
                    riceVariantId: $item['rice_variant_id'],
                    quantityDelta: -1 * $item['quantity'],
                    movementType: 'sale_out',
                    referenceType: 'transaction_item',
                    referenceId: $itemId
                );

                if ($item['remarks'] === TransactionRemark::TBT) {
                    if (empty($item['customer_name'])) {
                        throw new ValidationException('Customer name is required for TBT items.');
                    }
                    $this->insertTbtRecord($itemId, $item['customer_name'], $item['contact_number'] ?? null);
                }
            }

            $this->audit->log($header['created_by'], 'transaction.created', ['transaction_id' => $transactionId]);
            $this->db->commit();

            return $this->hydrateTransaction($transactionId);
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // insertTransactionHeader(), insertTransactionItem(), insertTbtRecord(),
    // recordStockMovement(), hydrateTransaction() — private helpers, PDO prepared statements
}
```

```php
// src/Services/TbtService.php
class TbtService
{
    public function __construct(private PDO $db, private AuditService $audit) {}

    public function listRecords(array $filters): array
    {
        // filters: ['status' => 'pending_pickup'|'released'|null, 'search' => customer name substring]
        // Builds a parameterized query joining tbt_records -> transaction_items -> rice_variants
    }

    public function release(int $tbtRecordId, int $userId): void
    {
        $stmt = $this->db->prepare(
            "UPDATE tbt_records SET status = 'released', released_by = ?, released_at = NOW()
             WHERE id = ? AND status = 'pending_pickup'"
        );
        $stmt->execute([$userId, $tbtRecordId]);

        if ($stmt->rowCount() === 0) {
            throw new ConflictException('Record already released or not found.');
        }

        $this->audit->log($userId, 'tbt.released', ['tbt_record_id' => $tbtRecordId]);
        // No stock_movements insert here — stock was already deducted at sale time.
    }
}
```

```php
// src/Services/DailyStockSummaryService.php
class DailyStockSummaryService
{
    public function __construct(private PDO $db) {}

    public function forDate(string $date): array
    {
        // Old Stock = remaining stock as of end of (date - 1 day), per variant
        // Stock In  = SUM(stock_ins.quantity) WHERE stock_in_date = :date
        // Stock Out Walk-in / Delivered = SUM(transaction_items.quantity)
        //     WHERE DATE(created_at) = :date AND fulfillment_type = 'walkin'|'delivered'
        // Remaining Stock = Old Stock + Stock In - (Walk-in + Delivered)
        // Implemented as one query per variant, or a single query with conditional SUMs (CASE WHEN)
        // grouped by rice_variant_id — see Section 11 for the SQL.
    }
}
```

```php
// src/Services/StockInService.php
class StockInService
{
    public function __construct(private PDO $db, private AuditService $audit) {}

    public function createStockIn(array $data): StockIn
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO stock_ins (stock_in_date, rice_variant_id, quantity, reference_note, created_by)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                $data['stock_in_date'], $data['rice_variant_id'], $data['quantity'],
                $data['reference_note'] ?? null, $data['created_by'],
            ]);
            $stockInId = (int) $this->db->lastInsertId();

            $this->recordStockMovement(
                riceVariantId: $data['rice_variant_id'],
                quantityDelta: $data['quantity'],
                movementType: 'stock_in',
                referenceType: 'stock_in',
                referenceId: $stockInId
            );

            $this->audit->log($data['created_by'], 'stock_in.created', ['stock_in_id' => $stockInId]);
            $this->db->commit();

            return $this->hydrate($stockInId);
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
```

---

## 7. Routing Table Additions

| Method | Path | Controller@Action | Middleware |
|---|---|---|---|
| GET | /transactions/new | TransactionController@create | Auth |
| POST | /transactions | TransactionController@store | Auth, Csrf, Idempotency |
| GET | /stock-ins | StockInController@index | Auth |
| GET | /stock-ins/new | StockInController@create | Auth |
| POST | /stock-ins | StockInController@store | Auth, Csrf |
| GET | /tbt | TbtController@index | Auth |
| POST | /tbt/{id}/release | TbtController@release | Auth, Csrf |
| GET | /reports/daily-stock-summary | ReportController@dailyStockSummary | Auth |

---

## 8. Sale Transaction (Multi-Variant + TBT) — Step-by-Step Implementation

1. `GET /transactions/new` renders `views/transactions/create.php` with an empty first line-item row and a rice-variant dropdown populated from `rice_variants`.
2. `transaction-form.js` handles:
   - "Add line item" button → clones a line-item row template
   - Per-row `remarks` toggle (Out/TBT) → shows/hides that row's Customer Name + Contact Number inputs
   - Per-row `fulfillment_type` toggle (Walk-in/Delivered)
   - Live subtotal calculation (`quantity × unit_price`) and running grand total display
3. On submit, form posts an array payload: `items[0][receipt_no]`, `items[0][rice_variant_id]`, etc.
4. `TransactionController@store`:
   - Verifies CSRF token and idempotency key (prevent duplicate-submit on double-click)
   - Validates payload (Section 12)
   - Calls `TransactionService::createTransaction($header, $items)`
   - On success, redirects to a transaction confirmation/receipt view; on `ValidationException`, re-renders form with field errors
5. All stock deduction and TBT record creation happens inside the single DB transaction in `TransactionService`, so a failure partway through rolls back everything (no partial sales).

---

## 9. Stock-In Transaction — Step-by-Step Implementation

1. `GET /stock-ins/new` renders a simple form (date, variant dropdown, quantity, reference note).
2. `GET /stock-ins` (index) lists recent entries, most recent first, for quick review/audit.
3. `POST /stock-ins` → `StockInController@store` → `StockInService::createStockIn()`.
4. This is intentionally a separate flow from the sales form — no receipt number, no customer, no TBT fields.

---

## 10. TBT Release — Step-by-Step Implementation

1. `GET /tbt` renders `views/tbt/index.php`, calling `TbtService::listRecords($filters)` with query params `?status=pending_pickup&search=juan`.
2. Table renders with a "Mark as Released" button per pending row.
3. `tbt-monitor.js` sends `POST /tbt/{id}/release` via `fetch()` with the CSRF token header; on success, updates that row's status badge in place (no full page reload) and moves it out of the "Pending" filter view.
4. `TbtController@release` calls `TbtService::release($id, $currentUserId)`.
5. Concurrency guard: the `UPDATE ... WHERE status = 'pending_pickup'` clause ensures a double-click or two staff releasing simultaneously results in only one successful update; the second attempt gets a `ConflictException` → JS shows "Already released by someone else, refresh the list."

---

## 11. Daily Stock Summary — Query Implementation

```sql
SELECT
    rv.id AS rice_variant_id,
    rv.name AS variant_name,
    COALESCE(prev.remaining_stock, 0) AS old_stock,
    COALESCE(si.stock_in_total, 0) AS stock_in,
    COALESCE(ti.walkin_total, 0) AS stock_out_walkin,
    COALESCE(ti.delivered_total, 0) AS stock_out_delivered,
    (COALESCE(prev.remaining_stock, 0) + COALESCE(si.stock_in_total, 0)
        - COALESCE(ti.walkin_total, 0) - COALESCE(ti.delivered_total, 0)) AS remaining_stock
FROM rice_variants rv
LEFT JOIN (
    -- Prior day's remaining stock (recursive from this same query for date - 1,
    -- or maintained via a materialized daily snapshot table for performance)
    SELECT rice_variant_id, remaining_stock FROM daily_stock_snapshots WHERE snapshot_date = :prev_date
) prev ON prev.rice_variant_id = rv.id
LEFT JOIN (
    SELECT rice_variant_id, SUM(quantity) AS stock_in_total
    FROM stock_ins WHERE stock_in_date = :date GROUP BY rice_variant_id
) si ON si.rice_variant_id = rv.id
LEFT JOIN (
    SELECT rice_variant_id,
        SUM(CASE WHEN fulfillment_type = 'walkin' THEN quantity ELSE 0 END) AS walkin_total,
        SUM(CASE WHEN fulfillment_type = 'delivered' THEN quantity ELSE 0 END) AS delivered_total
    FROM transaction_items
    WHERE DATE(created_at) = :date
    GROUP BY rice_variant_id
) ti ON ti.rice_variant_id = rv.id;
```

**Performance note:** computing "Old Stock" by recursively re-deriving every prior day from day one does not scale. Recommend a `daily_stock_snapshots` table (`snapshot_date`, `rice_variant_id`, `remaining_stock`) populated by a nightly cron/scheduled task (or written at the end of each day's last transaction) so "Old Stock" for any date is a single indexed lookup, not a recursive computation.

---

## 12. Validation Rules Implementation

Extend `Validator.php` with rules used by this module:

- `required_if:remarks,tbt` — for `customer_name` (only required when that line's remarks = tbt)
- `min:1` — for `quantity`
- `unique_per_date:transaction_items,receipt_no,transaction_date` — soft-warning, not a hard block (renders a confirm-anyway prompt in the UI rather than rejecting outright, since manual renumbering happens in real stores)

---

## 13. Concurrency & Atomicity Handling

- All multi-row writes (`TransactionService::createTransaction`, `StockInService::createStockIn`) are wrapped in a single PDO transaction with `beginTransaction()` / `commit()` / `rollBack()`.
- TBT release uses a conditional `UPDATE ... WHERE status = 'pending_pickup'` (optimistic concurrency) rather than a separate `SELECT` then `UPDATE`, to avoid race conditions between two staff members.
- Stock movement inserts reuse the existing `stock_movements` audit-trail pattern from the main Implementation PRD, so `InventoryService`'s existing low-stock/status logic continues to work unmodified.

---

## 14. Audit Logging Hooks

Every write action in this module logs through the existing `AuditService`:
- `transaction.created`
- `stock_in.created`
- `tbt.released`

Each log entry captures `user_id`, `action`, a JSON payload of relevant IDs, and a timestamp — consistent with the audit logging pattern already defined in the main Implementation PRD.

---

## 15. Frontend Structure (Views, Partials, JS)

- `views/transactions/create.php` — extends `layouts/app.php`; includes a `<template>` line-item row cloned by `transaction-form.js`
- `views/tbt/index.php` — extends `layouts/app.php`; table + search bar + filter tabs, progressively enhanced by `tbt-monitor.js` (works with plain page reload via query params if JS fails, then upgrades to AJAX)
- `views/stock-ins/create.php` and `index.php` — simple, no JS dependency beyond standard form submission
- `views/reports/daily-stock-summary.php` — read-only table + date picker (`<input type="date">` triggers a GET reload with `?date=`)

Tailwind classes follow the same design tokens already established in the main Implementation PRD's frontend section (no new color scheme needed for this module).

---

## 16. Loading States & Idempotency

- Sale submission button disables on click and shows a spinner (reuses the existing skeleton/loading pattern) until the `fetch`/form response returns, preventing duplicate-submit.
- `IdempotencyService` (already built) is invoked on `POST /transactions` using a client-generated idempotency key (hidden form field, regenerated per page load) so a network retry doesn't double-create a transaction.

---

## 17. Task Breakdown (Build Checklist)

**Phase 1 — Schema & Models**
- [ ] Run migrations for `transactions`, `transaction_items`, `tbt_records`, `stock_ins`
- [ ] Add `daily_stock_snapshots` table + nightly snapshot job
- [ ] Build Model classes + Enums

**Phase 2 — Sales Transaction Flow**
- [ ] `TransactionService::createTransaction()` with atomic writes + stock deduction
- [ ] `TransactionController` + `views/transactions/create.php`
- [ ] `transaction-form.js` (dynamic rows, conditional TBT fields, subtotal calc)

**Phase 3 — Stock-In Flow**
- [ ] `StockInService`, `StockInController`, views

**Phase 4 — TBT Monitoring**
- [ ] `TbtService::listRecords()` + `release()`
- [ ] `TbtController`, `views/tbt/index.php`
- [ ] `tbt-monitor.js` (search, filter tabs, AJAX release)

**Phase 5 — Reporting**
- [ ] `DailyStockSummaryService::forDate()`
- [ ] `ReportController@dailyStockSummary` + view

**Phase 6 — QA**
- [ ] Test multi-variant transaction with mixed Out/TBT lines
- [ ] Test concurrent TBT release (two tabs, same record)
- [ ] Test daily stock summary against a manually-reconciled sample day from the client's actual log book

---

## 18. Definition of Done (per sub-module)

- **Sales Transaction:** Can record a transaction with 2+ line items across different variants, mixed Out/TBT remarks, mixed Walk-in/Delivered fulfillment; stock deducts correctly for every line regardless of remarks; TBT records auto-created with required customer name.
- **Stock-In:** Can record a delivery; reflected correctly in the next Daily Stock Summary as "Stock In" for that variant/date.
- **TBT Monitoring:** Search by customer name works; release action updates status without altering stock; concurrent release attempts fail safely.
- **Daily Stock Summary:** Matches a manually-reconciled sample day from the client's log book exactly (Old Stock, Stock In, Stock Out Walk-in/Delivered, Remaining Stock).

---

## 19. File-by-File Manifest

```
src/Models/Transaction.php
src/Models/TransactionItem.php
src/Models/TbtRecord.php
src/Models/StockIn.php
src/Enums/TransactionRemark.php
src/Enums/FulfillmentType.php
src/Enums/TbtStatus.php
src/Services/TransactionService.php
src/Services/TbtService.php
src/Services/StockInService.php
src/Services/DailyStockSummaryService.php
src/Controllers/TransactionController.php
src/Controllers/StockInController.php
src/Controllers/TbtController.php
views/transactions/create.php
views/stock-ins/create.php
views/stock-ins/index.php
views/tbt/index.php
views/reports/daily-stock-summary.php
public/assets/js/transaction-form.js
public/assets/js/tbt-monitor.js
migrations/2026_xx_xx_create_transactions_table.sql
migrations/2026_xx_xx_create_transaction_items_table.sql
migrations/2026_xx_xx_create_tbt_records_table.sql
migrations/2026_xx_xx_create_stock_ins_table.sql
migrations/2026_xx_xx_create_daily_stock_snapshots_table.sql
```
