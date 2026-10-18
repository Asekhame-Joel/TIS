-- Run once against the existing production database before deploying the PHP changes.
ALTER TABLE tis_orders
    ADD COLUMN referrer_name VARCHAR(100) DEFAULT NULL AFTER phone;
