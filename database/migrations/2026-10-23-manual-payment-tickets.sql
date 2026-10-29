-- Run once after the physical-ticket migration.
-- Links a physical code to the standard order/PDF system and records who sold it.
ALTER TABLE tis_physical_tickets
    ADD COLUMN order_id BIGINT UNSIGNED DEFAULT NULL AFTER vendor_name,
    ADD COLUMN manual_payment_reference VARCHAR(100) DEFAULT NULL AFTER order_id,
    ADD COLUMN sold_by BIGINT UNSIGNED DEFAULT NULL AFTER sold_at,
    ADD UNIQUE KEY uq_tis_physical_tickets_order (order_id),
    ADD KEY idx_tis_physical_tickets_sold_by (sold_by),
    ADD CONSTRAINT fk_tis_physical_order FOREIGN KEY (order_id) REFERENCES tis_orders (id) ON DELETE SET NULL,
    ADD CONSTRAINT fk_tis_physical_sold_by FOREIGN KEY (sold_by) REFERENCES tis_staff_users (id) ON DELETE SET NULL;
