DELIMITER $$

DROP PROCEDURE IF EXISTS `ami_add_spmi_version_report_scope`$$
CREATE PROCEDURE `ami_add_spmi_version_report_scope`()
BEGIN
    IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'spmi_reports' AND COLUMN_NAME = 'report_scope') THEN
        ALTER TABLE `spmi_reports`
            MODIFY COLUMN `assessment_id` INT NULL,
            MODIFY COLUMN `source_standard_code_snapshot` VARCHAR(64) NULL,
            MODIFY COLUMN `source_standard_title_snapshot` VARCHAR(200) NULL,
            ADD COLUMN `report_scope` ENUM('standard','version') NOT NULL DEFAULT 'standard' AFTER `report_number`,
            ADD COLUMN `source_cycle_id` INT NULL AFTER `assessment_id`,
            ADD COLUMN `source_version_id` INT NULL AFTER `source_version_title_snapshot`,
            ADD COLUMN `auditor_id_snapshot` INT NULL AFTER `auditor_name_snapshot`,
            ADD COLUMN `auditee_id_snapshot` INT NULL AFTER `auditee_name_snapshot`;
    END IF;

    IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'spmi_reports' AND INDEX_NAME = 'uq_spmi_reports_version_tuple') THEN
        ALTER TABLE `spmi_reports` ADD UNIQUE KEY `uq_spmi_reports_version_tuple` (`source_cycle_id`, `source_version_id`, `auditor_id_snapshot`, `auditee_id_snapshot`, `report_scope`);
    END IF;

    IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'spmi_report_items' AND COLUMN_NAME = 'source_standard_id') THEN
        ALTER TABLE `spmi_report_items`
            ADD COLUMN `source_standard_id` INT NULL AFTER `report_id`,
            ADD COLUMN `source_standard_code_snapshot` VARCHAR(64) NULL AFTER `source_standard_id`,
            ADD COLUMN `source_standard_title_snapshot` VARCHAR(200) NULL AFTER `source_standard_code_snapshot`,
            ADD COLUMN `source_standard_display_order` INT NULL AFTER `source_standard_title_snapshot`,
            ADD COLUMN `standard_item_display_order` INT NULL AFTER `display_order`;
    END IF;
END$$

CALL `ami_add_spmi_version_report_scope`()$$

DROP PROCEDURE `ami_add_spmi_version_report_scope`$$

DELIMITER ;
