CREATE TABLE IF NOT EXISTS `spmi_auditor_assessment_evidence` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `assessment_item_id` INT NOT NULL,
    `stored_name` VARCHAR(255) NOT NULL,
    `original_name` VARCHAR(255) NOT NULL,
    `mime_type` VARCHAR(100) NOT NULL,
    `size_bytes` INT UNSIGNED NOT NULL,
    `sha256` CHAR(64) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_spmi_auditor_assessment_evidence_stored_name` (`stored_name`),
    KEY `idx_spmi_auditor_assessment_evidence_item` (`assessment_item_id`),
    CONSTRAINT `fk_spmi_auditor_assessment_evidence_item` FOREIGN KEY (`assessment_item_id`) REFERENCES `spmi_auditor_assessment_items` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DELIMITER $$

DROP PROCEDURE IF EXISTS `ami_add_spmi_auditor_evidence_snapshot`$$
CREATE PROCEDURE `ami_add_spmi_auditor_evidence_snapshot`()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'spmi_report_items'
          AND COLUMN_NAME = 'auditor_evidence_snapshot'
    ) THEN
        ALTER TABLE `spmi_report_items`
            ADD COLUMN `auditor_evidence_snapshot` TEXT NULL AFTER `evidence_file_sha256_snapshot`;
    END IF;
END$$

CALL `ami_add_spmi_auditor_evidence_snapshot`()$$

DROP PROCEDURE `ami_add_spmi_auditor_evidence_snapshot`$$

DELIMITER ;
