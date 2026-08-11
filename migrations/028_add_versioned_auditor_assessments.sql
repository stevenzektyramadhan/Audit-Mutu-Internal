DELIMITER $$

DROP PROCEDURE IF EXISTS `ami_replace_spmi_m17_07b_assessment_unique`$$
CREATE PROCEDURE `ami_replace_spmi_m17_07b_assessment_unique`()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'spmi_auditor_assessments'
        AND INDEX_NAME = 'uq_spmi_auditor_assessments_assignment_source_version'
    ) THEN
        ALTER TABLE `spmi_auditor_assessments`
            ADD UNIQUE KEY `uq_spmi_auditor_assessments_assignment_source_version` (`assignment_id`, `source_submission_version`);
    END IF;

    IF EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'spmi_auditor_assessments'
          AND INDEX_NAME = 'uq_spmi_auditor_assessments_assignment'
    ) THEN
        SET @sql = 'ALTER TABLE `spmi_auditor_assessments` DROP INDEX `uq_spmi_auditor_assessments_assignment`';
        PREPARE statement FROM @sql;
        EXECUTE statement;
        DEALLOCATE PREPARE statement;
    END IF;
END$$

CALL `ami_replace_spmi_m17_07b_assessment_unique`()$$

DROP PROCEDURE `ami_replace_spmi_m17_07b_assessment_unique`$$

DELIMITER ;
