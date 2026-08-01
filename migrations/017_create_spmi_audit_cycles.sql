-- Migrations 001-017; additive, manual, idempotent M7 schema.
CREATE TABLE IF NOT EXISTS `spmi_audit_cycles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `cycle_code` VARCHAR(64) NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `description` TEXT NULL,
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `state` ENUM('draft','configured','closed') NOT NULL DEFAULT 'draft',
    `created_by` INT NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_spmi_audit_cycles_code` (`cycle_code`),
    KEY `idx_spmi_audit_cycles_state_start` (`state`, `start_date`),
    CONSTRAINT `fk_spmi_audit_cycles_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `spmi_audit_assignments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `cycle_id` INT NOT NULL,
    `source_package_id` INT NOT NULL,
    `auditor_id` INT NOT NULL,
    `auditee_id` INT NOT NULL,
    `created_by` INT NOT NULL,
    `source_version_id` INT NOT NULL,
    `source_version_code` VARCHAR(64) NOT NULL,
    `source_version_title` VARCHAR(200) NOT NULL,
    `source_standard_id` INT NOT NULL,
    `source_standard_code` VARCHAR(64) NOT NULL,
    `source_standard_title` VARCHAR(200) NOT NULL,
    `source_package_code` VARCHAR(64) NOT NULL,
    `source_package_title` VARCHAR(200) NOT NULL,
    `source_package_description` TEXT NULL,
    `auditor_name` VARCHAR(200) NOT NULL,
    `auditor_email` VARCHAR(255) NOT NULL,
    `auditee_name` VARCHAR(200) NOT NULL,
    `auditee_email` VARCHAR(255) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_spmi_audit_assignments_tuple` (`cycle_id`, `source_package_id`, `auditor_id`, `auditee_id`),
    KEY `idx_spmi_audit_assignments_cycle` (`cycle_id`),
    KEY `idx_spmi_audit_assignments_auditor` (`auditor_id`),
    KEY `idx_spmi_audit_assignments_auditee` (`auditee_id`),
    CONSTRAINT `fk_spmi_audit_assignments_cycle` FOREIGN KEY (`cycle_id`) REFERENCES `spmi_audit_cycles` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT `fk_spmi_audit_assignments_package` FOREIGN KEY (`source_package_id`) REFERENCES `spmi_instrument_packages` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT `fk_spmi_audit_assignments_auditor` FOREIGN KEY (`auditor_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT `fk_spmi_audit_assignments_auditee` FOREIGN KEY (`auditee_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT `fk_spmi_audit_assignments_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT `fk_spmi_audit_assignments_version` FOREIGN KEY (`source_version_id`) REFERENCES `spmi_versions` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT `fk_spmi_audit_assignments_standard` FOREIGN KEY (`source_standard_id`) REFERENCES `spmi_standards` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `spmi_audit_assignment_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `assignment_id` INT NOT NULL,
    `source_question_id` INT NOT NULL,
    `source_indicator_id` INT NOT NULL,
    `display_order` INT NOT NULL,
    `question_code` VARCHAR(64) NOT NULL,
    `question_text` TEXT NOT NULL,
    `evidence_instruction` TEXT NOT NULL,
    `indicator_code` VARCHAR(64) NOT NULL,
    `indicator_title` VARCHAR(200) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_spmi_audit_assignment_items_order` (`assignment_id`, `display_order`),
    UNIQUE KEY `uq_spmi_audit_assignment_items_question` (`assignment_id`, `source_question_id`),
    CONSTRAINT `fk_spmi_audit_assignment_items_assignment` FOREIGN KEY (`assignment_id`) REFERENCES `spmi_audit_assignments` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT `fk_spmi_audit_assignment_items_question` FOREIGN KEY (`source_question_id`) REFERENCES `spmi_instrument_questions` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT `fk_spmi_audit_assignment_items_indicator` FOREIGN KEY (`source_indicator_id`) REFERENCES `spmi_indicators` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `spmi_audit_assignment_item_rubrics` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `assignment_item_id` INT NOT NULL,
    `score` TINYINT UNSIGNED NOT NULL,
    `descriptor` TEXT NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_spmi_audit_assignment_item_rubrics_score` (`assignment_item_id`, `score`),
    CONSTRAINT `fk_spmi_audit_assignment_item_rubrics_item` FOREIGN KEY (`assignment_item_id`) REFERENCES `spmi_audit_assignment_items` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
