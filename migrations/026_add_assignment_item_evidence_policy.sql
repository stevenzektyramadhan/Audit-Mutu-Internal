DELIMITER $$

DROP PROCEDURE IF EXISTS `ami_add_spmi_m17_03_column`$$
CREATE PROCEDURE `ami_add_spmi_m17_03_column`(IN p_table_name VARCHAR(64), IN p_column_name VARCHAR(64), IN p_column_definition TEXT)
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = p_table_name
          AND COLUMN_NAME = p_column_name
    ) THEN
        SET @sql = CONCAT('ALTER TABLE `', p_table_name, '` ADD COLUMN `', p_column_name, '` ', p_column_definition);
        PREPARE statement FROM @sql;
        EXECUTE statement;
        DEALLOCATE PREPARE statement;
    END IF;
END$$

CALL `ami_add_spmi_m17_03_column`('spmi_audit_assignment_items', 'evidence_policy', 'ENUM(''none'',''file'',''url'',''either'',''both'') NOT NULL DEFAULT ''none'' AFTER `evidence_instruction`')$$

DROP PROCEDURE `ami_add_spmi_m17_03_column`$$

DELIMITER ;
