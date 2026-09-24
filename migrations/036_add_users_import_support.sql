DELIMITER $$

DROP PROCEDURE IF EXISTS `ami_add_users_import_support`$$
CREATE PROCEDURE `ami_add_users_import_support`()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'users'
          AND COLUMN_NAME = 'identity_number'
    ) THEN
        ALTER TABLE `users`
            ADD COLUMN `identity_number` VARCHAR(32) NULL AFTER `email`;
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'users'
          AND INDEX_NAME = 'uq_users_identity_number'
    ) THEN
        ALTER TABLE `users`
            ADD UNIQUE KEY `uq_users_identity_number` (`identity_number`);
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'users'
          AND COLUMN_NAME = 'must_change_password'
    ) THEN
        ALTER TABLE `users`
            ADD COLUMN `must_change_password` TINYINT(1) NOT NULL DEFAULT 0 AFTER `password`;
    END IF;
END$$

CALL `ami_add_users_import_support`()$$

DROP PROCEDURE `ami_add_users_import_support`$$

DELIMITER ;
