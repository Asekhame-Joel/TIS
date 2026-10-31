-- Run once on existing installations before using online-ticket check-in.
ALTER TABLE tis_orders
    ADD COLUMN checked_in_at DATETIME DEFAULT NULL AFTER paid_at,
    ADD COLUMN checked_in_by BIGINT UNSIGNED DEFAULT NULL AFTER checked_in_at,
    ADD KEY idx_tis_orders_checked_in (checked_in_at),
    ADD KEY idx_tis_orders_checked_in_by (checked_in_by);
