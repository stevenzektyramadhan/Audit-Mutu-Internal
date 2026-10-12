DELIMITER $$

DROP PROCEDURE IF EXISTS `ami_add_system_settings`$$
CREATE PROCEDURE `ami_add_system_settings`()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'profil_lembaga'
          AND COLUMN_NAME = 'deskripsi'
    ) THEN
        ALTER TABLE `profil_lembaga`
            ADD COLUMN `deskripsi` TEXT NULL AFTER `nama_pt`;
    END IF;
END$$

CALL `ami_add_system_settings`()$$

DROP PROCEDURE `ami_add_system_settings`$$

DELIMITER ;

CREATE TABLE IF NOT EXISTS `system_settings` (
    `id` TINYINT UNSIGNED NOT NULL PRIMARY KEY,
    `turnstile_site_key` VARCHAR(255) NULL,
    `turnstile_secret_ciphertext` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `chk_system_settings_singleton` CHECK (`id` = 1)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `system_settings` (`id`, `turnstile_site_key`, `turnstile_secret_ciphertext`)
VALUES (1, NULL, NULL)
ON DUPLICATE KEY UPDATE
    `id` = `id`;
