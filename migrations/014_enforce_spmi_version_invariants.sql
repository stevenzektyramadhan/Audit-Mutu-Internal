-- Manual, idempotent reconciliation for version lifecycle invariants after migration 013.
-- It stops without changing data when legacy rows contain more than one active version.

DELIMITER $$

DROP PROCEDURE IF EXISTS `ami_enforce_spmi_version_invariants`$$
CREATE PROCEDURE `ami_enforce_spmi_version_invariants`()
BEGIN
    IF (SELECT COUNT(*) FROM `spmi_versions` WHERE `status` = 'active') > 1 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cannot enforce SPMI active-version invariant: more than one active version exists.';
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'spmi_versions'
          AND COLUMN_NAME = 'active_slot'
    ) THEN
        ALTER TABLE `spmi_versions`
            ADD COLUMN `active_slot` TINYINT GENERATED ALWAYS AS (IF(`status` = 'active', 1, NULL)) STORED;
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'spmi_versions'
          AND INDEX_NAME = 'uq_spmi_versions_active_slot'
    ) THEN
        ALTER TABLE `spmi_versions`
            ADD UNIQUE KEY `uq_spmi_versions_active_slot` (`active_slot`);
    END IF;
END$$

CALL `ami_enforce_spmi_version_invariants`()$$
DROP PROCEDURE `ami_enforce_spmi_version_invariants`$$

DELIMITER ;
