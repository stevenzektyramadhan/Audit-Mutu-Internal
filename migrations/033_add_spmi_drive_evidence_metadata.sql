DELIMITER $$

DROP PROCEDURE IF EXISTS `ami_add_spmi_drive_evidence_metadata`$$
CREATE PROCEDURE `ami_add_spmi_drive_evidence_metadata`()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'spmi_auditee_evidence'
          AND COLUMN_NAME = 'storage_backend'
    ) THEN
        ALTER TABLE `spmi_auditee_evidence`
            ADD COLUMN `storage_backend` ENUM('local','google_drive') NOT NULL DEFAULT 'local' AFTER `sha256`;
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'spmi_auditee_evidence'
          AND COLUMN_NAME = 'drive_file_id'
    ) THEN
        ALTER TABLE `spmi_auditee_evidence`
            ADD COLUMN `drive_file_id` VARCHAR(255) NULL AFTER `storage_backend`;
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'spmi_auditee_evidence'
          AND COLUMN_NAME = 'drive_folder_id'
    ) THEN
        ALTER TABLE `spmi_auditee_evidence`
            ADD COLUMN `drive_folder_id` VARCHAR(255) NULL AFTER `drive_file_id`;
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'spmi_auditee_evidence'
          AND INDEX_NAME = 'idx_spmi_auditee_evidence_drive_file'
    ) THEN
        ALTER TABLE `spmi_auditee_evidence`
            ADD KEY `idx_spmi_auditee_evidence_drive_file` (`storage_backend`, `drive_file_id`);
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'spmi_auditor_assessment_evidence'
          AND COLUMN_NAME = 'storage_backend'
    ) THEN
        ALTER TABLE `spmi_auditor_assessment_evidence`
            ADD COLUMN `storage_backend` ENUM('local','google_drive') NOT NULL DEFAULT 'local' AFTER `sha256`;
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'spmi_auditor_assessment_evidence'
          AND COLUMN_NAME = 'drive_file_id'
    ) THEN
        ALTER TABLE `spmi_auditor_assessment_evidence`
            ADD COLUMN `drive_file_id` VARCHAR(255) NULL AFTER `storage_backend`;
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'spmi_auditor_assessment_evidence'
          AND COLUMN_NAME = 'drive_folder_id'
    ) THEN
        ALTER TABLE `spmi_auditor_assessment_evidence`
            ADD COLUMN `drive_folder_id` VARCHAR(255) NULL AFTER `drive_file_id`;
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'spmi_auditor_assessment_evidence'
          AND INDEX_NAME = 'idx_spmi_auditor_assessment_evidence_drive_file'
    ) THEN
        ALTER TABLE `spmi_auditor_assessment_evidence`
            ADD KEY `idx_spmi_auditor_assessment_evidence_drive_file` (`storage_backend`, `drive_file_id`);
    END IF;

    CREATE TABLE IF NOT EXISTS `spmi_drive_trash_outbox` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `source_table` VARCHAR(64) NOT NULL,
        `source_id` INT UNSIGNED NULL,
        `operation` ENUM('trash') NOT NULL DEFAULT 'trash',
        `drive_file_id` VARCHAR(255) NOT NULL,
        `drive_folder_id` VARCHAR(255) NULL,
        `stored_name` VARCHAR(255) NULL,
        `status` ENUM('pending','retrying','done','failed') NOT NULL DEFAULT 'pending',
        `attempt_count` INT UNSIGNED NOT NULL DEFAULT 0,
        `last_error` VARCHAR(120) NULL,
        `next_attempt_at` DATETIME NULL,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
        KEY `idx_spmi_drive_trash_outbox_status` (`status`, `next_attempt_at`, `id`),
        KEY `idx_spmi_drive_trash_outbox_drive_file` (`drive_file_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    -- Manual retry: mark one pending/retrying row retrying, call Drive trash for drive_file_id with the service account, then set status to done or failed and increment attempt_count.
END$$

CALL `ami_add_spmi_drive_evidence_metadata`()$$

DROP PROCEDURE `ami_add_spmi_drive_evidence_metadata`$$

DELIMITER ;
