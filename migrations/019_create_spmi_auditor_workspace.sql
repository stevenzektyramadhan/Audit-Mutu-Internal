CREATE TABLE IF NOT EXISTS `spmi_auditor_assessments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `assignment_id` INT NOT NULL,
    `status` ENUM('draft','finalized') NOT NULL DEFAULT 'draft',
    `finalized_at` DATETIME NULL DEFAULT NULL,
    `version` INT UNSIGNED NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_spmi_auditor_assessments_assignment` (`assignment_id`),
    KEY `idx_spmi_auditor_assessments_status` (`status`),
    CONSTRAINT `fk_spmi_auditor_assessments_assignment` FOREIGN KEY (`assignment_id`) REFERENCES `spmi_audit_assignments` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `spmi_auditor_assessment_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `assessment_id` INT NOT NULL,
    `assignment_item_id` INT NOT NULL,
    `realization_snapshot` TEXT NOT NULL,
    `score` TINYINT UNSIGNED NULL,
    `finding` TEXT NULL,
    `recommendation` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_spmi_auditor_assessment_items_item` (`assessment_id`, `assignment_item_id`),
    KEY `idx_spmi_auditor_assessment_items_item` (`assignment_item_id`),
    CONSTRAINT `fk_spmi_auditor_assessment_items_assessment` FOREIGN KEY (`assessment_id`) REFERENCES `spmi_auditor_assessments` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT `fk_spmi_auditor_assessment_items_assignment_item` FOREIGN KEY (`assignment_item_id`) REFERENCES `spmi_audit_assignment_items` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
