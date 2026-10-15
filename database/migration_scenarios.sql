-- ============================================================
-- RISMS Scenario Migration
-- Scenario 1: Partial Utang Pickup (TBT + Utang combined)
-- Scenario 2: After-hours backdated sale support
-- Run date: 2026-10-04
-- ============================================================

-- ----------------------------------------------------------
-- 1. sale_items — partial-pickup tracking columns
-- ----------------------------------------------------------
ALTER TABLE sale_items
  ADD COLUMN quantity_taken DECIMAL(10,2) NOT NULL DEFAULT 0
      COMMENT 'Quantity physically handed to customer at sale time',
  ADD COLUMN quantity_pending DECIMAL(10,2) GENERATED ALWAYS AS
      (quantity - quantity_taken) STORED
      COMMENT 'Quantity still reserved at store, awaiting pickup';

-- Back-fill: all previous sales assumed fully taken
UPDATE sale_items SET quantity_taken = quantity WHERE quantity_taken = 0;


-- ----------------------------------------------------------
-- 2. tbt_records — quantity tracking columns
-- ----------------------------------------------------------
ALTER TABLE tbt_records
  ADD COLUMN quantity_reserved DECIMAL(10,2) NOT NULL DEFAULT 0
      COMMENT 'Total quantity set aside for this customer at time of sale',
  ADD COLUMN quantity_released DECIMAL(10,2) NOT NULL DEFAULT 0
      COMMENT 'Cumulative quantity already handed to customer';

-- Back-fill existing records from linked sale_items
UPDATE tbt_records t
  JOIN sale_items si ON t.sale_item_id = si.id
  SET t.quantity_reserved = si.quantity,
      t.quantity_released  = CASE WHEN t.status = 'released' THEN si.quantity ELSE 0 END;


-- ----------------------------------------------------------
-- 3. stock_movements — reference_date for backdating
-- ----------------------------------------------------------
ALTER TABLE stock_movements
  ADD COLUMN reference_date DATE NULL
      COMMENT 'Business date this movement belongs to (may differ from created_at for backdated sales)';

-- Back-fill from created_at
UPDATE stock_movements SET reference_date = DATE(created_at) WHERE reference_date IS NULL;
