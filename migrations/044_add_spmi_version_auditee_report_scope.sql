DELIMITER $$

DROP PROCEDURE IF EXISTS `ami_add_spmi_version_auditee_report_scope`$$
CREATE PROCEDURE `ami_add_spmi_version_auditee_report_scope`()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'spmi_report_items'
          AND COLUMN_NAME = 'auditor_id_snapshot'
    ) THEN
        ALTER TABLE `spmi_report_items`
            ADD COLUMN `auditor_id_snapshot` INT NULL AFTER `report_id`,
            ADD COLUMN `auditor_name_snapshot` VARCHAR(200) NULL AFTER `auditor_id_snapshot`,
            ADD COLUMN `auditor_email_snapshot` VARCHAR(255) NULL AFTER `auditor_name_snapshot`;
    END IF;

    ALTER TABLE `spmi_reports`
        MODIFY COLUMN `report_scope` ENUM('standard','version','version_auditee') NOT NULL DEFAULT 'standard';

    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'spmi_reports'
          AND COLUMN_NAME = 'version_auditee_scope_guard'
    ) THEN
        ALTER TABLE `spmi_reports`
            ADD COLUMN `version_auditee_scope_guard` TINYINT GENERATED ALWAYS AS (CASE WHEN `report_scope` = 'version_auditee' THEN 1 ELSE NULL END) STORED AFTER `report_scope`;
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'spmi_reports'
          AND INDEX_NAME = 'uq_spmi_reports_version_auditee_tuple'
    ) THEN
        ALTER TABLE `spmi_reports`
            ADD UNIQUE KEY `uq_spmi_reports_version_auditee_tuple` (`source_cycle_id`, `source_version_id`, `auditee_id_snapshot`, `version_auditee_scope_guard`);
    END IF;
END$$

CALL `ami_add_spmi_version_auditee_report_scope`()$$

DROP PROCEDURE `ami_add_spmi_version_auditee_report_scope`$$

DELIMITER ;
