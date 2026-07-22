-- Manual, idempotent upgrade for private self-service user profile photos.

DELIMITER $$

DROP PROCEDURE IF EXISTS `ami_add_users_profile_photo_path`$$
CREATE PROCEDURE `ami_add_users_profile_photo_path`()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'users'
          AND COLUMN_NAME = 'profile_photo_path'
    ) THEN
        ALTER TABLE `users` ADD COLUMN `profile_photo_path` VARCHAR(255) NULL AFTER `jenis_unit`;
    END IF;
END$$

CALL `ami_add_users_profile_photo_path`()$$
DROP PROCEDURE `ami_add_users_profile_photo_path`$$

DELIMITER ;
