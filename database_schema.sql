-- Database schema for AMI CodeIgniter 3
-- Mencakup seluruh perubahan dari migration 001-016

CREATE DATABASE IF NOT EXISTS `ami` CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `ami`;

CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nama` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('super_admin','admin_lpmpi','auditor','auditee') NOT NULL,
    `nama_unit` VARCHAR(100) NULL,
    `jenis_unit` ENUM('prodi','unit','lembaga') NULL,
    `profile_photo_path` VARCHAR(255) NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `session_version` INT UNSIGNED NOT NULL DEFAULT 1,
    `password_changed_at` DATETIME NULL,
    `last_login_at` DATETIME NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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

CREATE TABLE IF NOT EXISTS `auth_security_events` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT NULL,
    `email_hash` CHAR(64) NOT NULL,
    `ip_hash` CHAR(64) NOT NULL,
    `user_agent_hash` CHAR(64) NOT NULL,
    `event_type` VARCHAR(40) NOT NULL,
    `reason` VARCHAR(40) NULL,
    `request_id` CHAR(64) NOT NULL,
    `created_at` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    PRIMARY KEY (`id`),
    KEY `idx_auth_event_email` (`email_hash`, `event_type`, `created_at`),
    KEY `idx_auth_event_ip` (`ip_hash`, `event_type`, `created_at`),
    KEY `idx_auth_event_user` (`user_id`, `created_at`),
    KEY `idx_auth_event_request` (`request_id`),
    CONSTRAINT `fk_auth_event_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `file_assets` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `category` VARCHAR(40) NOT NULL,
    `owner_type` VARCHAR(40) NOT NULL,
    `owner_id` BIGINT UNSIGNED NULL,
    `storage_scope` ENUM('private','public','temporary') NOT NULL DEFAULT 'private',
    `stored_name` VARCHAR(255) NOT NULL,
    `original_name` VARCHAR(255) NOT NULL,
    `extension` VARCHAR(16) NOT NULL,
    `mime_type` VARCHAR(120) NOT NULL,
    `size_bytes` BIGINT UNSIGNED NOT NULL,
    `sha256` CHAR(64) NOT NULL,
    `status` ENUM('active','deleted','quarantined','purged') NOT NULL DEFAULT 'active',
    `is_legacy` TINYINT(1) NOT NULL DEFAULT 0,
    `uploaded_by` INT NULL,
    `deleted_by` INT NULL,
    `deleted_at` DATETIME NULL,
    `retention_until` DATETIME NULL,
    `purged_at` DATETIME NULL,
    `created_at` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_file_asset_storage` (`category`, `stored_name`),
    KEY `idx_file_asset_owner` (`owner_type`, `owner_id`, `status`),
    KEY `idx_file_asset_retention` (`status`, `retention_until`),
    KEY `idx_file_asset_checksum` (`sha256`),
    CONSTRAINT `fk_file_asset_uploaded_by`
        FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_file_asset_deleted_by`
        FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `file_security_events` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `file_asset_id` BIGINT UNSIGNED NULL,
    `actor_user_id` INT NULL,
    `event_type` VARCHAR(40) NOT NULL,
    `outcome` VARCHAR(16) NOT NULL,
    `reason` VARCHAR(64) NULL,
    `category` VARCHAR(40) NOT NULL,
    `owner_type` VARCHAR(40) NOT NULL,
    `owner_id` BIGINT UNSIGNED NULL,
    `request_id` CHAR(64) NOT NULL,
    `created_at` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    PRIMARY KEY (`id`),
    KEY `idx_file_event_asset` (`file_asset_id`, `created_at`),
    KEY `idx_file_event_actor` (`actor_user_id`, `created_at`),
    KEY `idx_file_event_owner` (`owner_type`, `owner_id`, `created_at`),
    KEY `idx_file_event_request` (`request_id`),
    CONSTRAINT `fk_file_event_asset`
        FOREIGN KEY (`file_asset_id`) REFERENCES `file_assets` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_file_event_actor`
        FOREIGN KEY (`actor_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `security_audit_logs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `event_uuid` CHAR(32) NOT NULL,
    `actor_user_id` INT NULL,
    `event_type` VARCHAR(64) NOT NULL,
    `object_type` VARCHAR(64) NOT NULL,
    `object_id` VARCHAR(128) NULL,
    `action` VARCHAR(40) NOT NULL,
    `outcome` ENUM('success','failure','blocked') NOT NULL DEFAULT 'success',
    `before_hash` CHAR(64) NULL,
    `after_hash` CHAR(64) NULL,
    `changes_json` JSON NULL,
    `ip_address` VARCHAR(80) NULL,
    `user_agent` VARCHAR(80) NULL,
    `request_id` CHAR(64) NOT NULL,
    `previous_hash` CHAR(64) NOT NULL,
    `entry_hash` CHAR(64) NOT NULL,
    `created_at` DATETIME(6) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_security_audit_uuid` (`event_uuid`),
    UNIQUE KEY `uq_security_audit_entry_hash` (`entry_hash`),
    KEY `idx_security_audit_actor` (`actor_user_id`, `created_at`),
    KEY `idx_security_audit_object` (`object_type`, `object_id`, `created_at`),
    KEY `idx_security_audit_event` (`event_type`, `created_at`),
    KEY `idx_security_audit_request` (`request_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `security_audit_chain_state` (
    `id` TINYINT UNSIGNED NOT NULL,
    `current_hash` CHAR(64) NOT NULL,
    `last_log_id` BIGINT UNSIGNED NULL,
    `updated_at` DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6)
        ON UPDATE CURRENT_TIMESTAMP(6),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=ascii;

INSERT IGNORE INTO `security_audit_chain_state` (`id`, `current_hash`, `last_log_id`)
VALUES (1, REPEAT('0', 64), NULL);

DROP TRIGGER IF EXISTS `trg_security_audit_logs_no_update`;
CREATE TRIGGER `trg_security_audit_logs_no_update`
BEFORE UPDATE ON `security_audit_logs`
FOR EACH ROW
SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'security_audit_logs is append-only';

DROP TRIGGER IF EXISTS `trg_security_audit_logs_no_delete`;
CREATE TRIGGER `trg_security_audit_logs_no_delete`
BEFORE DELETE ON `security_audit_logs`
FOR EACH ROW
SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'security_audit_logs is append-only';

CREATE TABLE IF NOT EXISTS `periode_audit` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nama_periode` VARCHAR(100) NOT NULL,
    `tahun_akademik` VARCHAR(20) NOT NULL,
    `semester` ENUM('ganjil','genap') NOT NULL,
    `tanggal_buka` DATE NOT NULL,
    `tanggal_tutup` DATE NOT NULL,
    `is_aktif` TINYINT(1) DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `standar` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nama_standar` VARCHAR(200) NOT NULL,
    `deskripsi` TEXT NULL,
    `file_instrumen` VARCHAR(255) NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `pertanyaan` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `standar_id` INT NOT NULL,
    `urutan` INT NULL,
    `isi_pertanyaan` TEXT NOT NULL,
    `nilai_standar` TEXT NULL,
    `baseline` VARCHAR(255) NULL,
    `target_2025` VARCHAR(255) NULL,
    `target_2026` VARCHAR(255) NULL,
    `target_2027` VARCHAR(255) NULL,
    `target_2028` VARCHAR(255) NULL,
    `target_2029` VARCHAR(255) NULL,
    `target_2030` VARCHAR(255) NULL,
    `kategori` ENUM('IKU', 'IKT') NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_pertanyaan_standar`
        FOREIGN KEY (`standar_id`) REFERENCES `standar` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `tugas_audit` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `auditor_id` INT NOT NULL,
    `auditee_id` INT NOT NULL,
    `standar_id` INT NOT NULL,
    `periode_id` INT NULL,
    `status` ENUM('belum_diisi', 'diisi', 'dinilai') DEFAULT 'belum_diisi',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_tugas_auditor`
        FOREIGN KEY (`auditor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_tugas_auditee`
        FOREIGN KEY (`auditee_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_tugas_standar`
        FOREIGN KEY (`standar_id`) REFERENCES `standar` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_tugas_periode`
        FOREIGN KEY (`periode_id`) REFERENCES `periode_audit` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `jawaban_audit` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tugas_id` INT NOT NULL,
    `pertanyaan_id` INT NOT NULL,
    `jawaban` TEXT NULL,
    `link_bukti` VARCHAR(500) NULL,
    `is_submitted` TINYINT(1) DEFAULT 0,
    `submitted_at` DATETIME NULL,
    `skor` INT NULL,
    `temuan` TEXT NULL,
    `jenis_temuan` ENUM('ob','kts') NULL,
    `saran_perbaikan` TEXT NULL,
    `rencana_perbaikan` TEXT NULL,
    `dokumen_bukti` VARCHAR(255) NULL,
    `tgl_bukti` DATE NULL,
    `is_nilai_submitted` TINYINT(1) DEFAULT 0,
    `nilai_submitted_at` DATETIME NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL,
    CONSTRAINT `fk_jawaban_tugas`
        FOREIGN KEY (`tugas_id`) REFERENCES `tugas_audit` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_jawaban_pertanyaan`
        FOREIGN KEY (`pertanyaan_id`) REFERENCES `pertanyaan` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `penetapan` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `standar_id` INT NOT NULL,
    `kategori` ENUM('pelaksanaan','pengendalian','peningkatan') NOT NULL,
    `status` VARCHAR(100) NULL,
    `deskripsi` TEXT NULL,
    `file_path` VARCHAR(255) NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_penetapan_standar`
        FOREIGN KEY (`standar_id`) REFERENCES `standar` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `profil_lembaga` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `id_pt_pddikti` VARCHAR(255) NULL,
    `nama_pt_pddikti` VARCHAR(200) NULL,
    `nama_pt` VARCHAR(200) NULL,
    `kode_pt` VARCHAR(20) NULL,
    `nomor_sk_pt` VARCHAR(100) NULL,
    `tanggal_sk_pt` DATE NULL,
    `tanggal_berdiri` DATE NULL,
    `jumlah_dosen` INT NULL,
    `jumlah_tendik` INT NULL,
    `akreditasi` VARCHAR(100) NULL,
    `akreditasi_berlaku_sampai` DATE NULL,
    `status_pt` VARCHAR(50) NULL,
    `kode_pos` VARCHAR(10) NULL,
    `telepon` VARCHAR(30) NULL,
    `faksimile` VARCHAR(30) NULL,
    `email` VARCHAR(100) NULL,
    `logo_path` VARCHAR(255) NULL,
    `logo_url` VARCHAR(500) NULL,
    `last_sync_at` DATETIME NULL,
    `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `profil_prodi` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `id_prodi_pddikti` VARCHAR(255) NULL,
    `kode_prodi` VARCHAR(20) NULL,
    `nama_prodi` VARCHAR(200) NULL,
    `status` VARCHAR(50) NULL,
    `jenjang` VARCHAR(20) NULL,
    `akreditasi` VARCHAR(50) NULL,
    `tanggal_sk_akreditasi` DATE NULL,
    `rasio_dosen_mahasiswa` VARCHAR(20) NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `profil_mahasiswa_stats` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `jenjang` VARCHAR(50) NULL,
    `jumlah` INT DEFAULT 0,
    `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
