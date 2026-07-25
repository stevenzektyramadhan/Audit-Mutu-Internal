-- M3-01: versioned SPMI source documents.
-- Manual, idempotent upgrade. Run after migrations 013, 015, and 016.

CREATE TABLE IF NOT EXISTS `spmi_versions` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `organization_unit_id` BIGINT UNSIGNED NOT NULL,
    `document_code` VARCHAR(64) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `revision_number` VARCHAR(50) NOT NULL,
    `effective_date` DATE NOT NULL,
    `expires_at` DATE NULL,
    `source_file_asset_id` BIGINT UNSIGNED NOT NULL,
    `source_file_path` VARCHAR(255) NOT NULL,
    `source_file_sha256` CHAR(64)
        CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    `status` ENUM('draft','review','approved','active','retired')
        NOT NULL DEFAULT 'draft',
    `created_by` INT NOT NULL,
    `approved_by` INT NULL,
    `approved_at` DATETIME(6) NULL,
    `created_at` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    `updated_at` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6)
        ON UPDATE CURRENT_TIMESTAMP(6),
    `active_slot` TINYINT
        GENERATED ALWAYS AS (
            CASE WHEN `status` = 'active' THEN 1 ELSE NULL END
        ) STORED,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_spmi_version_revision`
        (`organization_unit_id`, `document_code`, `revision_number`),
    UNIQUE KEY `uq_spmi_version_active_slot`
        (`organization_unit_id`, `document_code`, `active_slot`),
    UNIQUE KEY `uq_spmi_version_source_asset` (`source_file_asset_id`),
    KEY `idx_spmi_version_status_dates`
        (`status`, `effective_date`, `expires_at`),
    KEY `idx_spmi_version_creator` (`created_by`, `created_at`),
    KEY `idx_spmi_version_approver` (`approved_by`, `approved_at`),
    CONSTRAINT `fk_spmi_version_unit`
        FOREIGN KEY (`organization_unit_id`) REFERENCES `organization_units` (`id`)
        ON UPDATE RESTRICT ON DELETE RESTRICT,
    CONSTRAINT `fk_spmi_version_source_asset`
        FOREIGN KEY (`source_file_asset_id`) REFERENCES `file_assets` (`id`)
        ON UPDATE RESTRICT ON DELETE RESTRICT,
    CONSTRAINT `fk_spmi_version_creator`
        FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
        ON UPDATE RESTRICT ON DELETE RESTRICT,
    CONSTRAINT `fk_spmi_version_approver`
        FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`)
        ON UPDATE RESTRICT ON DELETE RESTRICT,
    CONSTRAINT `chk_spmi_version_identity`
        CHECK (
            CHAR_LENGTH(TRIM(`document_code`)) > 0
            AND CHAR_LENGTH(TRIM(`title`)) > 0
            AND CHAR_LENGTH(TRIM(`revision_number`)) > 0
        ),
    CONSTRAINT `chk_spmi_version_dates`
        CHECK (`expires_at` IS NULL OR `expires_at` >= `effective_date`),
    CONSTRAINT `chk_spmi_version_source_path`
        CHECK (
            `source_file_path`
                REGEXP '^spmi_source/[0-9a-f]{48}[.]pdf$'
        ),
    CONSTRAINT `chk_spmi_version_source_sha256`
        CHECK (`source_file_sha256` REGEXP '^[0-9a-f]{64}$'),
    CONSTRAINT `chk_spmi_version_approval_state`
        CHECK (
            (
                `status` IN ('draft', 'review')
                AND `approved_by` IS NULL
                AND `approved_at` IS NULL
            )
            OR (
                `status` IN ('approved', 'active', 'retired')
                AND `approved_by` IS NOT NULL
                AND `approved_at` IS NOT NULL
            )
        )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TRIGGER IF EXISTS `trg_spmi_versions_active_immutable`;
DELIMITER $$
CREATE TRIGGER `trg_spmi_versions_active_immutable`
BEFORE UPDATE ON `spmi_versions`
FOR EACH ROW
BEGIN
    IF OLD.`status` = 'active'
        AND (
            NEW.`status` NOT IN ('active', 'retired')
            OR NOT (NEW.`organization_unit_id` <=> OLD.`organization_unit_id`)
            OR NOT (NEW.`document_code` <=> OLD.`document_code`)
            OR NOT (NEW.`title` <=> OLD.`title`)
            OR NOT (NEW.`revision_number` <=> OLD.`revision_number`)
            OR NOT (NEW.`effective_date` <=> OLD.`effective_date`)
            OR NOT (NEW.`source_file_asset_id` <=> OLD.`source_file_asset_id`)
            OR NOT (NEW.`source_file_path` <=> OLD.`source_file_path`)
            OR NOT (NEW.`source_file_sha256` <=> OLD.`source_file_sha256`)
            OR NOT (NEW.`created_by` <=> OLD.`created_by`)
            OR NOT (NEW.`approved_by` <=> OLD.`approved_by`)
            OR NOT (NEW.`approved_at` <=> OLD.`approved_at`)
        ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'active spmi_version content is immutable';
    END IF;
END$$
DELIMITER ;

DROP TRIGGER IF EXISTS `trg_spmi_versions_no_delete`;
CREATE TRIGGER `trg_spmi_versions_no_delete`
BEFORE DELETE ON `spmi_versions`
FOR EACH ROW
SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'spmi_versions history cannot be deleted';

-- Rollback is intentionally manual and destructive:
-- 1. Stop application traffic and back up database plus private storage.
-- 2. Verify SELECT COUNT(*) FROM spmi_versions returns 0.
-- 3. DROP TRIGGER trg_spmi_versions_no_delete;
-- 4. DROP TRIGGER trg_spmi_versions_active_immutable;
-- 5. DROP TABLE spmi_versions;
-- Never run this rollback automatically when version history exists.
