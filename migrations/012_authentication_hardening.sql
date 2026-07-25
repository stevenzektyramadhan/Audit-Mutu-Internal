-- M1-03: authentication and session hardening.
-- Manual, idempotent upgrade. Run after selecting the AMI database.

DELIMITER $$

DROP PROCEDURE IF EXISTS `ami_add_users_auth_column`$$
CREATE PROCEDURE `ami_add_users_auth_column`(IN p_column_name VARCHAR(64), IN p_column_definition TEXT)
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'users'
          AND COLUMN_NAME = p_column_name
    ) THEN
        SET @sql = CONCAT(
            'ALTER TABLE `users` ADD COLUMN `',
            p_column_name,
            '` ',
            p_column_definition
        );
        PREPARE statement FROM @sql;
        EXECUTE statement;
        DEALLOCATE PREPARE statement;
    END IF;
END$$

CALL `ami_add_users_auth_column`(
    'is_active',
    'TINYINT(1) NOT NULL DEFAULT 1 AFTER `profile_photo_path`'
)$$
CALL `ami_add_users_auth_column`(
    'session_version',
    'INT UNSIGNED NOT NULL DEFAULT 1 AFTER `is_active`'
)$$
CALL `ami_add_users_auth_column`(
    'password_changed_at',
    'DATETIME NULL AFTER `session_version`'
)$$
CALL `ami_add_users_auth_column`(
    'last_login_at',
    'DATETIME NULL AFTER `password_changed_at`'
)$$

DROP PROCEDURE `ami_add_users_auth_column`$$

DELIMITER ;

CREATE TABLE IF NOT EXISTS `auth_security_events` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT NULL,
    `email_hash` CHAR(64) NOT NULL,
    `ip_hash` CHAR(64) NOT NULL,
    `user_agent_hash` CHAR(64) NOT NULL,
    `event_type` VARCHAR(40) NOT NULL,
    `reason` VARCHAR(40) NULL,
    `request_id` CHAR(64) NOT NULL,
    `created_at` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    PRIMARY KEY (`id`),
    KEY `idx_auth_event_email` (`email_hash`, `event_type`, `created_at`),
    KEY `idx_auth_event_ip` (`ip_hash`, `event_type`, `created_at`),
    KEY `idx_auth_event_user` (`user_id`, `created_at`),
    KEY `idx_auth_event_request` (`request_id`),
    CONSTRAINT `fk_auth_event_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
