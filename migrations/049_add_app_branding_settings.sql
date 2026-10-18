-- Migration 049: Add app_name and app_short_name to system_settings
-- Allows administrators to configure application branding while preserving defaults

DELIMITER $$

DROP PROCEDURE IF EXISTS `ami_add_app_branding_settings`$$
CREATE PROCEDURE `ami_add_app_branding_settings`()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'system_settings'
          AND COLUMN_NAME = 'app_name'
    ) THEN
        ALTER TABLE `system_settings`
            ADD COLUMN `app_name` VARCHAR(150) NULL DEFAULT 'Audit Mutu Internal' AFTER `id`,
            ADD COLUMN `app_short_name` VARCHAR(30) NULL DEFAULT 'AMI' AFTER `app_name`;
    END IF;
END$$

CALL `ami_add_app_branding_settings`()$$

DROP PROCEDURE `ami_add_app_branding_settings`$$

DELIMITER ;

UPDATE `system_settings`
SET `app_name` = COALESCE(`app_name`, 'Audit Mutu Internal'),
    `app_short_name` = COALESCE(`app_short_name`, 'AMI')
WHERE `id` = 1;
