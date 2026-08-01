CREATE TABLE IF NOT EXISTS `audit_logs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `actor_user_id` BIGINT UNSIGNED NULL,
    `actor_role` VARCHAR(32) NULL,
    `event_type` VARCHAR(64) NOT NULL,
    `outcome` VARCHAR(32) NOT NULL,
    `resource_type` VARCHAR(64) NULL,
    `resource_id` BIGINT UNSIGNED NULL,
    `request_method` VARCHAR(8) NULL,
    `route` VARCHAR(255) NULL,
    `client_ip` VARCHAR(45) NULL,
    `metadata_json` TEXT NULL,
    KEY `idx_audit_logs_created_at` (`created_at`),
    KEY `idx_audit_logs_actor_created` (`actor_user_id`, `created_at`),
    KEY `idx_audit_logs_event_created` (`event_type`, `created_at`),
    KEY `idx_audit_logs_resource` (`resource_type`, `resource_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
