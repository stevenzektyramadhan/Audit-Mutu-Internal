-- M2-02: keanggotaan user pada unit organisasi dan jabatan.
-- Manual, idempotent upgrade. Run after migration 015.

CREATE TABLE IF NOT EXISTS `user_unit_assignments` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `organization_unit_id` BIGINT UNSIGNED NOT NULL,
    `position_code` VARCHAR(64) NOT NULL,
    `valid_from` DATE NOT NULL,
    `valid_until` DATE NULL,
    `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    `updated_at` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6)
        ON UPDATE CURRENT_TIMESTAMP(6),
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_user_unit_assignment_version`
        (`user_id`, `organization_unit_id`, `position_code`, `valid_from`),
    KEY `idx_user_unit_assignment_active`
        (`user_id`, `valid_from`, `valid_until`),
    KEY `idx_unit_user_assignment_active`
        (`organization_unit_id`, `valid_from`, `valid_until`),
    KEY `idx_user_unit_assignment_primary`
        (`user_id`, `is_primary`, `valid_from`, `valid_until`),
    CONSTRAINT `fk_user_unit_assignment_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
        ON UPDATE RESTRICT ON DELETE RESTRICT,
    CONSTRAINT `fk_user_unit_assignment_unit`
        FOREIGN KEY (`organization_unit_id`) REFERENCES `organization_units` (`id`)
        ON UPDATE RESTRICT ON DELETE RESTRICT,
    CONSTRAINT `chk_user_unit_assignment_dates`
        CHECK (`valid_until` IS NULL OR `valid_until` >= `valid_from`),
    CONSTRAINT `chk_user_unit_assignment_primary`
        CHECK (`is_primary` IN (0, 1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
