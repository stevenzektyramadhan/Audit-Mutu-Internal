DELIMITER $$

DROP PROCEDURE IF EXISTS `ami_link_profil_prodi_to_organization_units`$$
CREATE PROCEDURE `ami_link_profil_prodi_to_organization_units`()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'profil_prodi'
          AND COLUMN_NAME = 'organization_unit_id'
    ) THEN
        ALTER TABLE `profil_prodi`
            ADD COLUMN `organization_unit_id` INT NULL AFTER `id`;
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'profil_prodi'
          AND INDEX_NAME = 'uq_profil_prodi_organization_unit'
    ) THEN
        ALTER TABLE `profil_prodi`
            ADD UNIQUE KEY `uq_profil_prodi_organization_unit` (`organization_unit_id`);
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'profil_prodi'
          AND CONSTRAINT_NAME = 'fk_profil_prodi_organization_unit'
          AND CONSTRAINT_TYPE = 'FOREIGN KEY'
    ) THEN
        ALTER TABLE `profil_prodi`
            ADD CONSTRAINT `fk_profil_prodi_organization_unit`
                FOREIGN KEY (`organization_unit_id`) REFERENCES `organization_units` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
    END IF;
END$$

CALL `ami_link_profil_prodi_to_organization_units`()$$

DROP PROCEDURE `ami_link_profil_prodi_to_organization_units`$$

DELIMITER ;
