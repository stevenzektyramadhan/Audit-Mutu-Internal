DELIMITER $$

DROP PROCEDURE IF EXISTS `ami_add_spmi_auditor_assessment_finding_details`$$
CREATE PROCEDURE `ami_add_spmi_auditor_assessment_finding_details`()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'spmi_auditor_assessment_items'
          AND COLUMN_NAME = 'improvement_plan'
    ) THEN
        ALTER TABLE `spmi_auditor_assessment_items`
            ADD COLUMN `improvement_plan` TEXT NULL AFTER `recommendation`;
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'spmi_auditor_assessment_items'
          AND COLUMN_NAME = 'evidence_date'
    ) THEN
        ALTER TABLE `spmi_auditor_assessment_items`
            ADD COLUMN `evidence_date` DATE NULL AFTER `improvement_plan`;
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'spmi_report_items'
          AND COLUMN_NAME = 'improvement_plan_snapshot'
    ) THEN
        ALTER TABLE `spmi_report_items`
            ADD COLUMN `improvement_plan_snapshot` TEXT NULL AFTER `recommendation_snapshot`;
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'spmi_report_items'
          AND COLUMN_NAME = 'evidence_date_snapshot'
    ) THEN
        ALTER TABLE `spmi_report_items`
            ADD COLUMN `evidence_date_snapshot` DATE NULL AFTER `improvement_plan_snapshot`;
    END IF;
END$$

CALL `ami_add_spmi_auditor_assessment_finding_details`()$$

DROP PROCEDURE `ami_add_spmi_auditor_assessment_finding_details`$$

DELIMITER ;
