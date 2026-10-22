-- Run once on the production database before opening the staff pages.
CREATE TABLE IF NOT EXISTS tis_staff_users (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    username VARCHAR(64) NOT NULL,
    display_name VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'gate') NOT NULL DEFAULT 'gate',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_tis_staff_users_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tis_physical_tickets (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    code VARCHAR(20) NOT NULL,
    event_key VARCHAR(64) NOT NULL,
    event_name VARCHAR(190) NOT NULL,
    vendor_name VARCHAR(100) DEFAULT NULL,
    status ENUM('unsold', 'sold', 'checked_in') NOT NULL DEFAULT 'unsold',
    sold_at DATETIME DEFAULT NULL,
    checked_in_at DATETIME DEFAULT NULL,
    checked_in_by BIGINT UNSIGNED DEFAULT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_tis_physical_tickets_code (code),
    KEY idx_tis_physical_tickets_event_status (event_key, status),
    KEY idx_tis_physical_tickets_vendor (vendor_name),
    CONSTRAINT fk_tis_physical_checked_in_by FOREIGN KEY (checked_in_by) REFERENCES tis_staff_users (id) ON DELETE SET NULL,
    CONSTRAINT fk_tis_physical_created_by FOREIGN KEY (created_by) REFERENCES tis_staff_users (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
