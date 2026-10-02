DELIMITER $$

DROP PROCEDURE IF EXISTS `ami_add_spmi_report_academic_year_snapshot`$$
CREATE PROCEDURE `ami_add_spmi_report_academic_year_snapshot`()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'spmi_reports'
          AND COLUMN_NAME = 'academic_year_snapshot'
    ) THEN
        ALTER TABLE `spmi_reports`
            ADD COLUMN `academic_year_snapshot` VARCHAR(20) NULL AFTER `source_cycle_id`;
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'spmi_reports'
          AND INDEX_NAME = 'idx_spmi_reports_academic_year_generated'
    ) THEN
        ALTER TABLE `spmi_reports`
            ADD KEY `idx_spmi_reports_academic_year_generated` (`academic_year_snapshot`, `generated_at`, `id`);
    END IF;
END$$

CALL `ami_add_spmi_report_academic_year_snapshot`()$$

DROP PROCEDURE `ami_add_spmi_report_academic_year_snapshot`$$

DELIMITER ;
