-- M3-03: version-owned SPMI standard master.
-- Manual, idempotent upgrade. Run after migration 017.

CREATE TABLE IF NOT EXISTS `spmi_standards` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `spmi_version_id` BIGINT UNSIGNED NOT NULL,
    `code` VARCHAR(64) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `group_type` ENUM(
        'education',
        'research',
        'community_service',
        'internal'
    ) NOT NULL,
    `standard_type` ENUM('sn_dikti','internal') NOT NULL,
    `rationale` TEXT NULL,
    `definitions` TEXT NULL,
    `sort_order` INT UNSIGNED NOT NULL,
    `active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    `updated_at` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6)
        ON UPDATE CURRENT_TIMESTAMP(6),
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_spmi_standard_code` (`spmi_version_id`, `code`),
    KEY `idx_spmi_standard_order`
        (`spmi_version_id`, `sort_order`, `id`),
    KEY `idx_spmi_standard_group`
        (`spmi_version_id`, `group_type`, `active`),
    CONSTRAINT `fk_spmi_standard_version`
        FOREIGN KEY (`spmi_version_id`) REFERENCES `spmi_versions` (`id`)
        ON UPDATE RESTRICT ON DELETE RESTRICT,
    CONSTRAINT `chk_spmi_standard_identity`
        CHECK (
            CHAR_LENGTH(TRIM(`code`)) > 0
            AND CHAR_LENGTH(TRIM(`name`)) > 0
        ),
    CONSTRAINT `chk_spmi_standard_sort_order`
        CHECK (`sort_order` > 0),
    CONSTRAINT `chk_spmi_standard_active`
        CHECK (`active` IN (0, 1)),
    CONSTRAINT `chk_spmi_standard_group_type`
        CHECK (
            (
                `standard_type` = 'sn_dikti'
                AND `group_type` IN (
                    'education',
                    'research',
                    'community_service'
                )
            )
            OR (
                `standard_type` = 'internal'
                AND `group_type` = 'internal'
            )
        )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TRIGGER IF EXISTS `trg_spmi_standards_insert_draft`;
DELIMITER $$
CREATE TRIGGER `trg_spmi_standards_insert_draft`
BEFORE INSERT ON `spmi_standards`
FOR EACH ROW
BEGIN
    DECLARE parent_status VARCHAR(16);
    SET parent_status = (
        SELECT `status`
        FROM `spmi_versions`
        WHERE `id` = NEW.`spmi_version_id`
    );

    IF parent_status IS NULL OR parent_status <> 'draft' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'spmi standards can only be added to a draft version';
    END IF;
END$$
DELIMITER ;

DROP TRIGGER IF EXISTS `trg_spmi_standards_update_draft`;
DELIMITER $$
CREATE TRIGGER `trg_spmi_standards_update_draft`
BEFORE UPDATE ON `spmi_standards`
FOR EACH ROW
BEGIN
    DECLARE parent_status VARCHAR(16);
    SET parent_status = (
        SELECT `status`
        FROM `spmi_versions`
        WHERE `id` = OLD.`spmi_version_id`
    );

    IF NOT (NEW.`spmi_version_id` <=> OLD.`spmi_version_id`) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'spmi standard cannot move between versions';
    END IF;

    IF parent_status IS NULL OR parent_status <> 'draft' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'spmi standards are immutable outside a draft version';
    END IF;
END$$
DELIMITER ;

DROP TRIGGER IF EXISTS `trg_spmi_standards_no_delete`;
CREATE TRIGGER `trg_spmi_standards_no_delete`
BEFORE DELETE ON `spmi_standards`
FOR EACH ROW
SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'spmi standard history cannot be deleted';

-- Rollback is intentionally manual and destructive:
-- 1. Stop application traffic and back up the database.
-- 2. Verify SELECT COUNT(*) FROM spmi_standards returns 0.
-- 3. DROP TRIGGER trg_spmi_standards_no_delete;
-- 4. DROP TRIGGER trg_spmi_standards_update_draft;
-- 5. DROP TRIGGER trg_spmi_standards_insert_draft;
-- 6. DROP TABLE spmi_standards;
-- Never run this rollback automatically when version content exists.
