SET @drop_idx_052 = (
    SELECT IF(
        EXISTS (
            SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'spmi_versions'
              AND INDEX_NAME = 'uq_spmi_versions_active_slot'
        ),
        'ALTER TABLE `spmi_versions` DROP INDEX `uq_spmi_versions_active_slot`',
        'DO 0'
    )
);
PREPARE stmt_052_idx FROM @drop_idx_052;
EXECUTE stmt_052_idx;
DEALLOCATE PREPARE stmt_052_idx;

SET @drop_col_052 = (
    SELECT IF(
        EXISTS (
            SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'spmi_versions'
              AND COLUMN_NAME = 'active_slot'
        ),
        'ALTER TABLE `spmi_versions` DROP COLUMN `active_slot`',
        'DO 0'
    )
);
PREPARE stmt_052_col FROM @drop_col_052;
EXECUTE stmt_052_col;
DEALLOCATE PREPARE stmt_052_col;
