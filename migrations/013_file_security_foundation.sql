-- M1-06: file metadata, integrity, event logging, and retention foundation.
-- Manual, idempotent upgrade. Run after selecting the AMI database.

CREATE TABLE IF NOT EXISTS `file_assets` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `category` VARCHAR(40) NOT NULL,
    `owner_type` VARCHAR(40) NOT NULL,
    `owner_id` BIGINT UNSIGNED NULL,
    `storage_scope` ENUM('private','public','temporary') NOT NULL DEFAULT 'private',
    `stored_name` VARCHAR(255) NOT NULL,
    `original_name` VARCHAR(255) NOT NULL,
    `extension` VARCHAR(16) NOT NULL,
    `mime_type` VARCHAR(120) NOT NULL,
    `size_bytes` BIGINT UNSIGNED NOT NULL,
    `sha256` CHAR(64) NOT NULL,
    `status` ENUM('active','deleted','quarantined','purged') NOT NULL DEFAULT 'active',
    `is_legacy` TINYINT(1) NOT NULL DEFAULT 0,
    `uploaded_by` INT NULL,
    `deleted_by` INT NULL,
    `deleted_at` DATETIME NULL,
    `retention_until` DATETIME NULL,
    `purged_at` DATETIME NULL,
    `created_at` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_file_asset_storage` (`category`, `stored_name`),
    KEY `idx_file_asset_owner` (`owner_type`, `owner_id`, `status`),
    KEY `idx_file_asset_retention` (`status`, `retention_until`),
    KEY `idx_file_asset_checksum` (`sha256`),
    CONSTRAINT `fk_file_asset_uploaded_by`
        FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_file_asset_deleted_by`
        FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `file_security_events` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `file_asset_id` BIGINT UNSIGNED NULL,
    `actor_user_id` INT NULL,
    `event_type` VARCHAR(40) NOT NULL,
    `outcome` VARCHAR(16) NOT NULL,
    `reason` VARCHAR(64) NULL,
    `category` VARCHAR(40) NOT NULL,
    `owner_type` VARCHAR(40) NOT NULL,
    `owner_id` BIGINT UNSIGNED NULL,
    `request_id` CHAR(64) NOT NULL,
    `created_at` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    PRIMARY KEY (`id`),
    KEY `idx_file_event_asset` (`file_asset_id`, `created_at`),
    KEY `idx_file_event_actor` (`actor_user_id`, `created_at`),
    KEY `idx_file_event_owner` (`owner_type`, `owner_id`, `created_at`),
    KEY `idx_file_event_request` (`request_id`),
    CONSTRAINT `fk_file_event_asset`
        FOREIGN KEY (`file_asset_id`) REFERENCES `file_assets` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_file_event_actor`
        FOREIGN KEY (`actor_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
