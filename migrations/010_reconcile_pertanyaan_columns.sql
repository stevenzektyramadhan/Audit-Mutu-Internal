-- Manual, idempotent upgrade for deployments affected by duplicate historical 009 files.
-- Run after selecting the AMI database. Historical migrations remain unchanged.

DELIMITER $$

DROP PROCEDURE IF EXISTS `ami_add_pertanyaan_column`$$
CREATE PROCEDURE `ami_add_pertanyaan_column`(IN p_column_name VARCHAR(64), IN p_column_definition TEXT)
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'pertanyaan'
          AND COLUMN_NAME = p_column_name
    ) THEN
        SET @sql = CONCAT('ALTER TABLE `pertanyaan` ADD COLUMN `', p_column_name, '` ', p_column_definition);
        PREPARE statement FROM @sql;
        EXECUTE statement;
        DEALLOCATE PREPARE statement;
    END IF;
END$$

CALL `ami_add_pertanyaan_column`('urutan', 'INT NULL AFTER `standar_id`')$$
CALL `ami_add_pertanyaan_column`('nilai_standar', 'TEXT NULL AFTER `isi_pertanyaan`')$$
CALL `ami_add_pertanyaan_column`('baseline', 'VARCHAR(255) NULL AFTER `nilai_standar`')$$
CALL `ami_add_pertanyaan_column`('target_2025', 'VARCHAR(255) NULL AFTER `baseline`')$$
CALL `ami_add_pertanyaan_column`('target_2026', 'VARCHAR(255) NULL AFTER `target_2025`')$$
CALL `ami_add_pertanyaan_column`('target_2027', 'VARCHAR(255) NULL AFTER `target_2026`')$$
CALL `ami_add_pertanyaan_column`('target_2028', 'VARCHAR(255) NULL AFTER `target_2027`')$$
CALL `ami_add_pertanyaan_column`('target_2029', 'VARCHAR(255) NULL AFTER `target_2028`')$$
CALL `ami_add_pertanyaan_column`('target_2030', 'VARCHAR(255) NULL AFTER `target_2029`')$$
CALL `ami_add_pertanyaan_column`('kategori', 'ENUM(''IKU'', ''IKT'') NULL AFTER `target_2030`')$$

UPDATE `pertanyaan` SET `urutan` = `id` WHERE `urutan` IS NULL$$
DROP PROCEDURE `ami_add_pertanyaan_column`$$

DELIMITER ;
