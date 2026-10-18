-- Migration 050: Add login_hero_title and login_hero_description to system_settings
-- Allows administrators to configure login page left-side hero text while preserving defaults

DELIMITER $$

DROP PROCEDURE IF EXISTS `ami_add_login_hero_settings`$$
CREATE PROCEDURE `ami_add_login_hero_settings`()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'system_settings'
          AND COLUMN_NAME = 'login_hero_title'
    ) THEN
        ALTER TABLE `system_settings`
            ADD COLUMN `login_hero_title` VARCHAR(255) NULL DEFAULT 'Budaya mutu tumbuh dari proses yang jelas dan bukti yang tepercaya.' AFTER `app_short_name`,
            ADD COLUMN `login_hero_description` TEXT NULL AFTER `login_hero_title`;
    END IF;
END$$

CALL `ami_add_login_hero_settings`()$$

DROP PROCEDURE `ami_add_login_hero_settings`$$

DELIMITER ;

UPDATE `system_settings`
SET `login_hero_title` = COALESCE(`login_hero_title`, 'Budaya mutu tumbuh dari proses yang jelas dan bukti yang tepercaya.'),
    `login_hero_description` = COALESCE(`login_hero_description`, 'Ruang kerja terpadu untuk pelaksanaan, penilaian, dan tindak lanjut Sistem Penjaminan Mutu Internal perguruan tinggi.')
WHERE `id` = 1;
