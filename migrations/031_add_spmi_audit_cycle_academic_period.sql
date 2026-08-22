DELIMITER $$

DROP PROCEDURE IF EXISTS `ami_add_spmi_audit_cycle_academic_period`$$
CREATE PROCEDURE `ami_add_spmi_audit_cycle_academic_period`()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'spmi_audit_cycles'
          AND COLUMN_NAME = 'academic_year'
    ) THEN
        ALTER TABLE `spmi_audit_cycles`
            ADD COLUMN `academic_year` VARCHAR(20) NULL AFTER `description`;
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'spmi_audit_cycles'
          AND COLUMN_NAME = 'semester'
    ) THEN
        ALTER TABLE `spmi_audit_cycles`
            ADD COLUMN `semester` ENUM('ganjil','genap') NULL AFTER `academic_year`;
    END IF;
END$$

CALL `ami_add_spmi_audit_cycle_academic_period`()$$

DROP PROCEDURE `ami_add_spmi_audit_cycle_academic_period`$$

DELIMITER ;
