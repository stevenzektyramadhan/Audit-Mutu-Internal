-- M2-01: master unit organisasi hierarkis.
-- Manual, idempotent upgrade. Run after selecting the AMI database.

CREATE TABLE IF NOT EXISTS `organization_units` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `parent_id` BIGINT UNSIGNED NULL,
    `code` VARCHAR(50) NOT NULL,
    `name` VARCHAR(200) NOT NULL,
    `type` ENUM(
        'university',
        'faculty',
        'study_program',
        'institute',
        'bureau',
        'unit'
    ) NOT NULL,
    `active` TINYINT(1) NOT NULL DEFAULT 1,
    `metadata_json` JSON NULL,
    `created_at` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    `updated_at` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6)
        ON UPDATE CURRENT_TIMESTAMP(6),
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_organization_units_code` (`code`),
    KEY `idx_organization_units_parent` (`parent_id`),
    KEY `idx_organization_units_type_active` (`type`, `active`),
    CONSTRAINT `fk_organization_units_parent`
        FOREIGN KEY (`parent_id`) REFERENCES `organization_units` (`id`)
        ON UPDATE RESTRICT ON DELETE RESTRICT,
    CONSTRAINT `chk_organization_units_active`
        CHECK (`active` IN (0, 1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `organization_units`
    (`parent_id`, `code`, `name`, `type`, `active`, `metadata_json`)
SELECT NULL, 'UNIVERSITY', 'Universitas', 'university', 1, NULL
WHERE NOT EXISTS (
    SELECT 1
    FROM `organization_units`
    WHERE `code` = 'UNIVERSITY'
);
