DELIMITER $$

DROP PROCEDURE IF EXISTS `ami_add_spmi_rtm_photo`$$
CREATE PROCEDURE `ami_add_spmi_rtm_photo`()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'spmi_rtm_meetings'
          AND COLUMN_NAME = 'photo_stored_name'
    ) THEN
        ALTER TABLE `spmi_rtm_meetings`
            ADD COLUMN `photo_stored_name` VARCHAR(255) NULL AFTER `location`;
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'spmi_rtm_meetings'
          AND COLUMN_NAME = 'photo_original_name'
    ) THEN
        ALTER TABLE `spmi_rtm_meetings`
            ADD COLUMN `photo_original_name` VARCHAR(255) NULL AFTER `photo_stored_name`;
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'spmi_rtm_meetings'
          AND COLUMN_NAME = 'photo_mime_type'
    ) THEN
        ALTER TABLE `spmi_rtm_meetings`
            ADD COLUMN `photo_mime_type` VARCHAR(100) NULL AFTER `photo_original_name`;
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'spmi_rtm_meetings'
          AND COLUMN_NAME = 'photo_size_bytes'
    ) THEN
        ALTER TABLE `spmi_rtm_meetings`
            ADD COLUMN `photo_size_bytes` INT UNSIGNED NULL AFTER `photo_mime_type`;
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'spmi_rtm_meetings'
          AND COLUMN_NAME = 'photo_sha256'
    ) THEN
        ALTER TABLE `spmi_rtm_meetings`
            ADD COLUMN `photo_sha256` CHAR(64) NULL AFTER `photo_size_bytes`;
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'spmi_rtm_meetings'
          AND INDEX_NAME = 'uq_spmi_rtm_meetings_photo_stored_name'
    ) THEN
        ALTER TABLE `spmi_rtm_meetings`
            ADD UNIQUE KEY `uq_spmi_rtm_meetings_photo_stored_name` (`photo_stored_name`);
    END IF;
END$$

CALL `ami_add_spmi_rtm_photo`()$$

DROP PROCEDURE `ami_add_spmi_rtm_photo`$$

DELIMITER ;

INSERT INTO `spmi_upload_size_settings` (`category`, `label`, `limit_mib`)
VALUES ('rtm_photos', 'Foto Dokumentasi RTM', 5)
ON DUPLICATE KEY UPDATE
    `category` = `category`;
