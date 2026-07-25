-- M1-08: append-only, tamper-evident security/domain audit ledger.
-- Manual, idempotent upgrade. Run after selecting the AMI database.

CREATE TABLE IF NOT EXISTS `security_audit_logs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `event_uuid` CHAR(32) NOT NULL,
    `actor_user_id` INT NULL,
    `event_type` VARCHAR(64) NOT NULL,
    `object_type` VARCHAR(64) NOT NULL,
    `object_id` VARCHAR(128) NULL,
    `action` VARCHAR(40) NOT NULL,
    `outcome` ENUM('success','failure','blocked') NOT NULL DEFAULT 'success',
    `before_hash` CHAR(64) NULL,
    `after_hash` CHAR(64) NULL,
    `changes_json` JSON NULL,
    `ip_address` VARCHAR(80) NULL,
    `user_agent` VARCHAR(80) NULL,
    `request_id` CHAR(64) NOT NULL,
    `previous_hash` CHAR(64) NOT NULL,
    `entry_hash` CHAR(64) NOT NULL,
    `created_at` DATETIME(6) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_security_audit_uuid` (`event_uuid`),
    UNIQUE KEY `uq_security_audit_entry_hash` (`entry_hash`),
    KEY `idx_security_audit_actor` (`actor_user_id`, `created_at`),
    KEY `idx_security_audit_object` (`object_type`, `object_id`, `created_at`),
    KEY `idx_security_audit_event` (`event_type`, `created_at`),
    KEY `idx_security_audit_request` (`request_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `security_audit_chain_state` (
    `id` TINYINT UNSIGNED NOT NULL,
    `current_hash` CHAR(64) NOT NULL,
    `last_log_id` BIGINT UNSIGNED NULL,
    `updated_at` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6)
        ON UPDATE CURRENT_TIMESTAMP(6),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=ascii;

INSERT IGNORE INTO `security_audit_chain_state` (`id`, `current_hash`, `last_log_id`)
VALUES (1, REPEAT('0', 64), NULL);

DROP TRIGGER IF EXISTS `trg_security_audit_logs_no_update`;
CREATE TRIGGER `trg_security_audit_logs_no_update`
BEFORE UPDATE ON `security_audit_logs`
FOR EACH ROW
SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'security_audit_logs is append-only';

DROP TRIGGER IF EXISTS `trg_security_audit_logs_no_delete`;
CREATE TRIGGER `trg_security_audit_logs_no_delete`
BEFORE DELETE ON `security_audit_logs`
FOR EACH ROW
SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'security_audit_logs is append-only';
