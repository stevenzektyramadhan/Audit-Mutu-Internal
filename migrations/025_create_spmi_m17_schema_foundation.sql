DELIMITER $$

DROP PROCEDURE IF EXISTS `ami_add_spmi_m17_column`$$
CREATE PROCEDURE `ami_add_spmi_m17_column`(IN p_table_name VARCHAR(64), IN p_column_name VARCHAR(64), IN p_column_definition TEXT)
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = p_table_name
          AND COLUMN_NAME = p_column_name
    ) THEN
        SET @sql = CONCAT('ALTER TABLE `', p_table_name, '` ADD COLUMN `', p_column_name, '` ', p_column_definition);
        PREPARE statement FROM @sql;
        EXECUTE statement;
        DEALLOCATE PREPARE statement;
    END IF;
END$$

CALL `ami_add_spmi_m17_column`('spmi_instrument_questions', 'evidence_policy', 'ENUM(''none'',''file'',''url'',''either'',''both'') NOT NULL DEFAULT ''none'' AFTER `evidence_instruction`')$$
CALL `ami_add_spmi_m17_column`('spmi_auditee_submission_items', 'evidence_url', 'VARCHAR(500) NULL AFTER `realization`')$$
CALL `ami_add_spmi_m17_column`('spmi_auditor_assessment_items', 'finding_type', 'ENUM(''ob'',''kts'') NULL AFTER `finding`')$$
CALL `ami_add_spmi_m17_column`('spmi_report_items', 'finding_type_snapshot', 'ENUM(''ob'',''kts'') NULL AFTER `finding_snapshot`')$$
CALL `ami_add_spmi_m17_column`('spmi_report_items', 'evidence_url_snapshot', 'VARCHAR(500) NULL AFTER `realization_snapshot`')$$
CALL `ami_add_spmi_m17_column`('spmi_report_items', 'evidence_file_original_name_snapshot', 'VARCHAR(255) NULL AFTER `evidence_url_snapshot`')$$
CALL `ami_add_spmi_m17_column`('spmi_report_items', 'evidence_file_mime_type_snapshot', 'VARCHAR(100) NULL AFTER `evidence_file_original_name_snapshot`')$$
CALL `ami_add_spmi_m17_column`('spmi_report_items', 'evidence_file_size_bytes_snapshot', 'INT UNSIGNED NULL AFTER `evidence_file_mime_type_snapshot`')$$
CALL `ami_add_spmi_m17_column`('spmi_report_items', 'evidence_file_sha256_snapshot', 'CHAR(64) NULL AFTER `evidence_file_size_bytes_snapshot`')$$

DROP PROCEDURE `ami_add_spmi_m17_column`$$

DELIMITER ;

CREATE TABLE IF NOT EXISTS `spmi_auditee_submission_revision_events` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `submission_id` INT NOT NULL,
    `assignment_id` INT NOT NULL,
    `actor_user_id` INT NOT NULL,
    `reason` TEXT NOT NULL,
    `submission_version` INT UNSIGNED NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_spmi_submission_revision_events_submission` (`submission_id`, `created_at`),
    KEY `idx_spmi_submission_revision_events_assignment` (`assignment_id`, `created_at`),
    CONSTRAINT `fk_spmi_submission_revision_events_submission` FOREIGN KEY (`submission_id`) REFERENCES `spmi_auditee_submissions` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT `fk_spmi_submission_revision_events_assignment` FOREIGN KEY (`assignment_id`) REFERENCES `spmi_audit_assignments` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT `fk_spmi_submission_revision_events_actor` FOREIGN KEY (`actor_user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
