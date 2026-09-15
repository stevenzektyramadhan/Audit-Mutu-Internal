DELIMITER $$

DROP PROCEDURE IF EXISTS `ami_add_indicator_evidence_policy`$$
CREATE PROCEDURE `ami_add_indicator_evidence_policy`()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'spmi_indicators'
          AND COLUMN_NAME = 'evidence_policy'
    ) THEN
        ALTER TABLE `spmi_indicators`
            ADD COLUMN `evidence_policy` ENUM('none','file','url','either','both') NOT NULL DEFAULT 'none' AFTER `evidence_requirement`;
    END IF;
END$$

CALL `ami_add_indicator_evidence_policy`()$$

DROP PROCEDURE `ami_add_indicator_evidence_policy`$$

DELIMITER ;
