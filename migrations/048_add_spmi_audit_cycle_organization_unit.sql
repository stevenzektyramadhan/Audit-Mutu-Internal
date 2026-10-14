DELIMITER $$

DROP PROCEDURE IF EXISTS `ami_add_spmi_audit_cycle_organization_unit`$$
CREATE PROCEDURE `ami_add_spmi_audit_cycle_organization_unit`()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'spmi_audit_cycles'
          AND COLUMN_NAME = 'organization_unit_id'
    ) THEN
        ALTER TABLE `spmi_audit_cycles`
            ADD COLUMN `organization_unit_id` INT NULL AFTER `academic_year`;
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'spmi_audit_cycles'
          AND INDEX_NAME = 'idx_spmi_audit_cycles_organization_unit'
    ) THEN
        ALTER TABLE `spmi_audit_cycles`
            ADD KEY `idx_spmi_audit_cycles_organization_unit` (`organization_unit_id`);
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'spmi_audit_cycles'
          AND CONSTRAINT_NAME = 'fk_spmi_audit_cycles_organization_unit'
          AND CONSTRAINT_TYPE = 'FOREIGN KEY'
    ) THEN
        ALTER TABLE `spmi_audit_cycles`
            ADD CONSTRAINT `fk_spmi_audit_cycles_organization_unit`
                FOREIGN KEY (`organization_unit_id`) REFERENCES `organization_units` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
    END IF;
END$$

CALL `ami_add_spmi_audit_cycle_organization_unit`()$$

DROP PROCEDURE `ami_add_spmi_audit_cycle_organization_unit`$$

DELIMITER ;
