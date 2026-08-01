CREATE TABLE IF NOT EXISTS `organization_units` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `parent_id` INT NULL,
    `code` VARCHAR(64) NOT NULL UNIQUE,
    `name` VARCHAR(200) NOT NULL,
    `type` ENUM('university','faculty','upps','study_program','institute','bureau','unit') NOT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `metadata_json` JSON NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    KEY `idx_organization_units_parent_active` (`parent_id`, `is_active`),
    KEY `idx_organization_units_active` (`is_active`),
    CONSTRAINT `fk_organization_units_parent`
        FOREIGN KEY (`parent_id`) REFERENCES `organization_units` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `user_unit_assignments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `organization_unit_id` INT NOT NULL,
    `position_code` VARCHAR(64) NOT NULL,
    `valid_from` DATE NOT NULL,
    `valid_until` DATE NULL,
    `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    KEY `idx_user_unit_assignments_user_dates` (`user_id`, `valid_from`, `valid_until`),
    KEY `idx_user_unit_assignments_unit_active` (`organization_unit_id`, `valid_until`),
    CONSTRAINT `fk_user_unit_assignments_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT `fk_user_unit_assignments_unit`
        FOREIGN KEY (`organization_unit_id`) REFERENCES `organization_units` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `capabilities` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(100) NOT NULL UNIQUE,
    `label` VARCHAR(200) NOT NULL,
    `description` VARCHAR(500) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `role_capabilities` (
    `role` ENUM('super_admin','admin_lpmpi','auditor','auditee') NOT NULL,
    `capability_id` INT NOT NULL,
    PRIMARY KEY (`role`, `capability_id`),
    CONSTRAINT `fk_role_capabilities_capability`
        FOREIGN KEY (`capability_id`) REFERENCES `capabilities` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT IGNORE INTO `organization_units` (`parent_id`, `code`, `name`, `type`, `is_active`)
VALUES (NULL, 'UNIVERSITAS', 'Universitas', 'university', 1);

INSERT IGNORE INTO `capabilities` (`code`, `label`, `description`) VALUES
    ('organization.view', 'Lihat struktur organisasi', 'Melihat hierarki dan ringkasan penempatan.'),
    ('organization.manage', 'Kelola struktur organisasi', 'Membuat, mengubah, dan menonaktifkan unit non-root.'),
    ('organization.assignment.manage', 'Kelola penempatan unit', 'Membuat dan mengakhiri penempatan pengguna pada unit.'),
    ('organization.capability.manage', 'Kelola kapabilitas organisasi', 'Mengubah pemetaan kapabilitas per role.');

INSERT IGNORE INTO `role_capabilities` (`role`, `capability_id`)
SELECT 'super_admin', `id` FROM `capabilities`
WHERE `code` IN ('organization.view', 'organization.manage', 'organization.assignment.manage', 'organization.capability.manage');

INSERT IGNORE INTO `role_capabilities` (`role`, `capability_id`)
SELECT 'admin_lpmpi', `id` FROM `capabilities`
WHERE `code` IN ('organization.view', 'organization.manage', 'organization.assignment.manage');
