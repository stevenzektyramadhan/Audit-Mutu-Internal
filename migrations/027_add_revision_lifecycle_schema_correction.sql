DELIMITER $$

DROP PROCEDURE IF EXISTS `ami_add_spmi_m17_01a_column`$$
CREATE PROCEDURE `ami_add_spmi_m17_01a_column`(IN p_table_name VARCHAR(64), IN p_column_name VARCHAR(64), IN p_column_definition TEXT)
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

DROP PROCEDURE IF EXISTS `ami_correct_spmi_m17_01a_submission_status`$$
CREATE PROCEDURE `ami_correct_spmi_m17_01a_submission_status`()
BEGIN
    IF EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'spmi_auditee_submissions'
          AND COLUMN_NAME = 'status'
          AND COLUMN_TYPE <> 'enum(''draft'',''submitted'',''returned_for_revision'',''resubmitted'',''under_assessment'',''completed'')'
    ) THEN
        SET @sql = 'ALTER TABLE `spmi_auditee_submissions` MODIFY COLUMN `status` ENUM(''draft'',''submitted'',''returned_for_revision'',''resubmitted'',''under_assessment'',''completed'') NOT NULL DEFAULT ''draft''';
        PREPARE statement FROM @sql;
        EXECUTE statement;
        DEALLOCATE PREPARE statement;
    END IF;
END$$

CALL `ami_correct_spmi_m17_01a_submission_status`()$$
CALL `ami_add_spmi_m17_01a_column`('spmi_auditor_assessments', 'source_submission_version', 'INT UNSIGNED NULL AFTER `version`')$$
CALL `ami_add_spmi_m17_01a_column`('spmi_auditee_submission_revision_events', 'previous_status', 'ENUM(''draft'',''submitted'',''returned_for_revision'',''resubmitted'',''under_assessment'',''completed'') NOT NULL DEFAULT ''draft'' AFTER `submission_version`')$$
CALL `ami_add_spmi_m17_01a_column`('spmi_auditee_submission_revision_events', 'new_status', 'ENUM(''draft'',''submitted'',''returned_for_revision'',''resubmitted'',''under_assessment'',''completed'') NOT NULL DEFAULT ''draft'' AFTER `previous_status`')$$
CALL `ami_add_spmi_m17_01a_column`('spmi_auditee_submission_revision_events', 'previous_version', 'INT UNSIGNED NOT NULL DEFAULT 1 AFTER `new_status`')$$
CALL `ami_add_spmi_m17_01a_column`('spmi_auditee_submission_revision_events', 'resulting_version', 'INT UNSIGNED NOT NULL DEFAULT 1 AFTER `previous_version`')$$

DROP PROCEDURE `ami_add_spmi_m17_01a_column`$$
DROP PROCEDURE `ami_correct_spmi_m17_01a_submission_status`$$

DELIMITER ;
