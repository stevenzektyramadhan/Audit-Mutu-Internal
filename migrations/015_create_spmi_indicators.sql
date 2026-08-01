CREATE TABLE IF NOT EXISTS `spmi_indicators` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `standard_id` INT NOT NULL,
    `indicator_code` VARCHAR(64) NOT NULL,
    `indicator_type` ENUM('IKU','IKT') NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `scope_organization_unit_id` INT NOT NULL,
    `responsible_organization_unit_id` INT NOT NULL,
    `responsible_pic_name` VARCHAR(200) NULL,
    `evidence_requirement` TEXT NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_spmi_indicators_standard_code` (`standard_id`, `indicator_code`),
    KEY `idx_spmi_indicators_scope_unit` (`scope_organization_unit_id`),
    KEY `idx_spmi_indicators_responsible_unit` (`responsible_organization_unit_id`),
    CONSTRAINT `fk_spmi_indicators_standard` FOREIGN KEY (`standard_id`) REFERENCES `spmi_standards` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT `fk_spmi_indicators_scope_unit` FOREIGN KEY (`scope_organization_unit_id`) REFERENCES `organization_units` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT `fk_spmi_indicators_responsible_unit` FOREIGN KEY (`responsible_organization_unit_id`) REFERENCES `organization_units` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `spmi_indicator_targets` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `indicator_id` INT NOT NULL,
    `target_year` SMALLINT UNSIGNED NOT NULL,
    `target_value` TEXT NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_spmi_indicator_targets_indicator_year` (`indicator_id`, `target_year`),
    CONSTRAINT `fk_spmi_indicator_targets_indicator` FOREIGN KEY (`indicator_id`) REFERENCES `spmi_indicators` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
