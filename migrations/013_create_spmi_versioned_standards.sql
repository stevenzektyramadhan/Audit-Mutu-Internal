CREATE TABLE IF NOT EXISTS `spmi_versions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `version_code` VARCHAR(64) NOT NULL UNIQUE,
    `title` VARCHAR(200) NOT NULL,
    `description` TEXT NULL,
    `status` ENUM('draft','review','approved','active','retired') NOT NULL DEFAULT 'draft',
    `source_file_path` VARCHAR(255) NULL,
    `created_by` INT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_spmi_versions_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `spmi_standards` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `version_id` INT NOT NULL,
    `standard_code` VARCHAR(64) NOT NULL,
    `display_order` INT NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `description` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_spmi_standard_code` (`version_id`, `standard_code`),
    UNIQUE KEY `uq_spmi_standard_order` (`version_id`, `display_order`),
    CONSTRAINT `fk_spmi_standards_version` FOREIGN KEY (`version_id`) REFERENCES `spmi_versions` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT IGNORE INTO `spmi_versions` (`version_code`, `title`, `description`, `status`, `created_by`)
VALUES ('M3-INITIAL', 'Katalog Standar SPMI M3', NULL, 'draft', NULL);

INSERT IGNORE INTO `spmi_standards` (`version_id`, `standard_code`, `display_order`, `title`, `description`)
SELECT `id`, CONCAT('SPMI-', LPAD(numbers.n, 2, '0')), numbers.n, CONCAT('Standar SPMI ', LPAD(numbers.n, 2, '0')), NULL
FROM `spmi_versions`
CROSS JOIN (
    SELECT 1 n UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5
    UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10
    UNION ALL SELECT 11 UNION ALL SELECT 12 UNION ALL SELECT 13 UNION ALL SELECT 14 UNION ALL SELECT 15
    UNION ALL SELECT 16 UNION ALL SELECT 17 UNION ALL SELECT 18 UNION ALL SELECT 19 UNION ALL SELECT 20
    UNION ALL SELECT 21
) numbers
WHERE `version_code` = 'M3-INITIAL';
