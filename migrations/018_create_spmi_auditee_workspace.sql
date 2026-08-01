CREATE TABLE IF NOT EXISTS `spmi_auditee_submissions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `assignment_id` INT NOT NULL,
    `status` ENUM('draft','submitted') NOT NULL DEFAULT 'draft',
    `submitted_at` DATETIME NULL DEFAULT NULL,
    `version` INT UNSIGNED NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_spmi_auditee_submissions_assignment` (`assignment_id`),
    KEY `idx_spmi_auditee_submissions_status` (`status`),
    CONSTRAINT `fk_spmi_auditee_submissions_assignment` FOREIGN KEY (`assignment_id`) REFERENCES `spmi_audit_assignments` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `spmi_auditee_submission_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `submission_id` INT NOT NULL,
    `assignment_item_id` INT NOT NULL,
    `realization` TEXT NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_spmi_auditee_submission_items_item` (`submission_id`, `assignment_item_id`),
    CONSTRAINT `fk_spmi_auditee_submission_items_submission` FOREIGN KEY (`submission_id`) REFERENCES `spmi_auditee_submissions` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT `fk_spmi_auditee_submission_items_assignment_item` FOREIGN KEY (`assignment_item_id`) REFERENCES `spmi_audit_assignment_items` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `spmi_auditee_evidence` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `submission_item_id` INT NOT NULL,
    `stored_name` VARCHAR(255) NOT NULL,
    `original_name` VARCHAR(255) NOT NULL,
    `mime_type` VARCHAR(100) NOT NULL,
    `size_bytes` INT UNSIGNED NOT NULL,
    `sha256` CHAR(64) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_spmi_auditee_evidence_stored_name` (`stored_name`),
    KEY `idx_spmi_auditee_evidence_item` (`submission_item_id`),
    CONSTRAINT `fk_spmi_auditee_evidence_submission_item` FOREIGN KEY (`submission_item_id`) REFERENCES `spmi_auditee_submission_items` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
