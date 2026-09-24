CREATE TABLE IF NOT EXISTS `spmi_upload_size_settings` (
    `category` VARCHAR(64) NOT NULL PRIMARY KEY,
    `label` VARCHAR(100) NOT NULL,
    `limit_mib` TINYINT UNSIGNED NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `chk_spmi_upload_size_settings_limit` CHECK (`limit_mib` BETWEEN 1 AND 10)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `spmi_upload_size_settings` (`category`, `label`, `limit_mib`)
VALUES
    ('spmi_evidence', 'Bukti SPMI', 5),
    ('ppepp_documents', 'Dokumen PPEPP', 10),
    ('profile_photos', 'Foto Profil', 2),
    ('spreadsheet_imports', 'Import Spreadsheet', 2),
    ('spmi_source_pdf', 'PDF Sumber SPMI', 5),
    ('institution_logo', 'Logo Lembaga', 4)
ON DUPLICATE KEY UPDATE
    `category` = `category`;
