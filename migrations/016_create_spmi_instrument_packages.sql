CREATE TABLE IF NOT EXISTS `spmi_instrument_packages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `standard_id` INT NOT NULL,
    `package_code` VARCHAR(64) NOT NULL,
    `display_order` INT NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `description` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_spmi_instrument_packages_standard_code` (`standard_id`, `package_code`),
    UNIQUE KEY `uq_spmi_instrument_packages_standard_order` (`standard_id`, `display_order`),
    CONSTRAINT `fk_spmi_instrument_packages_standard` FOREIGN KEY (`standard_id`) REFERENCES `spmi_standards` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `spmi_instrument_questions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `package_id` INT NOT NULL,
    `indicator_id` INT NOT NULL,
    `question_code` VARCHAR(64) NOT NULL,
    `display_order` INT NOT NULL,
    `question_text` TEXT NOT NULL,
    `evidence_instruction` TEXT NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_spmi_instrument_questions_package_code` (`package_id`, `question_code`),
    UNIQUE KEY `uq_spmi_instrument_questions_package_order` (`package_id`, `display_order`),
    CONSTRAINT `fk_spmi_instrument_questions_package` FOREIGN KEY (`package_id`) REFERENCES `spmi_instrument_packages` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT `fk_spmi_instrument_questions_indicator` FOREIGN KEY (`indicator_id`) REFERENCES `spmi_indicators` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `spmi_instrument_rubrics` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `question_id` INT NOT NULL,
    `score` TINYINT UNSIGNED NOT NULL,
    `descriptor` TEXT NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_spmi_instrument_rubrics_question_score` (`question_id`, `score`),
    CONSTRAINT `fk_spmi_instrument_rubrics_question` FOREIGN KEY (`question_id`) REFERENCES `spmi_instrument_questions` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
