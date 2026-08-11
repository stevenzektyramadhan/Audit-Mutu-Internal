-- Database schema for AMI CodeIgniter 3
-- Mencakup current parity migration 001-018 (M8 SPMI auditee workspace)
-- Current parity migration 001-019 adds M9 SPMI auditor workspace.
-- current parity migration 001-019
-- current parity migration 001-020
-- current parity migration 001-021
-- current parity migration 001-022
-- current parity migration 001-023
-- current parity migration 001-024
-- current parity migration 001-025
-- current parity migration 001-027
-- current parity migration 001-028

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
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
    CONSTRAINT `fk_organization_units_parent` FOREIGN KEY (`parent_id`) REFERENCES `organization_units` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
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
    CONSTRAINT `fk_user_unit_assignments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT `fk_user_unit_assignments_unit` FOREIGN KEY (`organization_unit_id`) REFERENCES `organization_units` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
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
    CONSTRAINT `fk_role_capabilities_capability` FOREIGN KEY (`capability_id`) REFERENCES `capabilities` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT IGNORE INTO `organization_units` (`parent_id`, `code`, `name`, `type`, `is_active`) VALUES (NULL, 'UNIVERSITAS', 'Universitas', 'university', 1);
INSERT IGNORE INTO `capabilities` (`code`, `label`, `description`) VALUES
('organization.view', 'Lihat struktur organisasi', 'Melihat hierarki dan ringkasan penempatan.'),
('organization.manage', 'Kelola struktur organisasi', 'Membuat, mengubah, dan menonaktifkan unit non-root.'),
('organization.assignment.manage', 'Kelola penempatan unit', 'Membuat dan mengakhiri penempatan pengguna pada unit.'),
('organization.capability.manage', 'Kelola kapabilitas organisasi', 'Mengubah pemetaan kapabilitas per role.');
INSERT IGNORE INTO `role_capabilities` (`role`, `capability_id`) SELECT 'super_admin', `id` FROM `capabilities` WHERE `code` IN ('organization.view','organization.manage','organization.assignment.manage','organization.capability.manage');
INSERT IGNORE INTO `role_capabilities` (`role`, `capability_id`) SELECT 'admin_lpmpi', `id` FROM `capabilities` WHERE `code` IN ('organization.view','organization.manage','organization.assignment.manage');

CREATE TABLE IF NOT EXISTS `spmi_versions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `version_code` VARCHAR(64) NOT NULL UNIQUE,
    `title` VARCHAR(200) NOT NULL,
    `description` TEXT NULL,
    `status` ENUM('draft','review','approved','active','retired') NOT NULL DEFAULT 'draft',
    `active_slot` TINYINT GENERATED ALWAYS AS (IF(`status` = 'active', 1, NULL)) STORED,
    `source_file_path` VARCHAR(255) NULL,
    `created_by` INT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_spmi_versions_active_slot` (`active_slot`),
    CONSTRAINT `fk_spmi_versions_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `spmi_standards` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `version_id` INT NOT NULL,
    `standard_code` VARCHAR(64) NOT NULL,
    `display_order` INT NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `description` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_spmi_standard_code` (`version_id`, `standard_code`),
    UNIQUE KEY `uq_spmi_standard_order` (`version_id`, `display_order`),
    CONSTRAINT `fk_spmi_standards_version` FOREIGN KEY (`version_id`) REFERENCES `spmi_versions` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `spmi_indicators` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `standard_id` INT NOT NULL,
    `indicator_code` VARCHAR(64) NOT NULL,
    `indicator_type` ENUM('IKU','IKT') NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `scope_organization_unit_id` INT NOT NULL,
    `responsible_organization_unit_id` INT NOT NULL,
    `responsible_pic_name` VARCHAR(200) NULL,
    `evidence_requirement` TEXT NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_spmi_indicators_standard_code` (`standard_id`, `indicator_code`),
    KEY `idx_spmi_indicators_scope_unit` (`scope_organization_unit_id`),
    KEY `idx_spmi_indicators_responsible_unit` (`responsible_organization_unit_id`),
    CONSTRAINT `fk_spmi_indicators_standard` FOREIGN KEY (`standard_id`) REFERENCES `spmi_standards` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT `fk_spmi_indicators_scope_unit` FOREIGN KEY (`scope_organization_unit_id`) REFERENCES `organization_units` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT `fk_spmi_indicators_responsible_unit` FOREIGN KEY (`responsible_organization_unit_id`) REFERENCES `organization_units` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `spmi_indicator_targets` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `indicator_id` INT NOT NULL,
    `target_year` SMALLINT UNSIGNED NOT NULL,
    `target_value` TEXT NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_spmi_indicator_targets_indicator_year` (`indicator_id`, `target_year`),
    CONSTRAINT `fk_spmi_indicator_targets_indicator` FOREIGN KEY (`indicator_id`) REFERENCES `spmi_indicators` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `spmi_instrument_packages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `standard_id` INT NOT NULL,
    `package_code` VARCHAR(64) NOT NULL,
    `display_order` INT NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `description` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_spmi_instrument_packages_standard_code` (`standard_id`, `package_code`),
    UNIQUE KEY `uq_spmi_instrument_packages_standard_order` (`standard_id`, `display_order`),
    CONSTRAINT `fk_spmi_instrument_packages_standard` FOREIGN KEY (`standard_id`) REFERENCES `spmi_standards` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `spmi_instrument_questions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `package_id` INT NOT NULL,
    `indicator_id` INT NOT NULL,
    `question_code` VARCHAR(64) NOT NULL,
    `display_order` INT NOT NULL,
    `question_text` TEXT NOT NULL,
    `evidence_instruction` TEXT NOT NULL,
    `evidence_policy` ENUM('none','file','url','either','both') NOT NULL DEFAULT 'none',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_spmi_instrument_questions_package_code` (`package_id`, `question_code`),
    UNIQUE KEY `uq_spmi_instrument_questions_package_order` (`package_id`, `display_order`),
    CONSTRAINT `fk_spmi_instrument_questions_package` FOREIGN KEY (`package_id`) REFERENCES `spmi_instrument_packages` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT `fk_spmi_instrument_questions_indicator` FOREIGN KEY (`indicator_id`) REFERENCES `spmi_indicators` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `spmi_instrument_rubrics` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `question_id` INT NOT NULL,
    `score` TINYINT UNSIGNED NOT NULL,
    `descriptor` TEXT NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_spmi_instrument_rubrics_question_score` (`question_id`, `score`),
    CONSTRAINT `fk_spmi_instrument_rubrics_question` FOREIGN KEY (`question_id`) REFERENCES `spmi_instrument_questions` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/* Migrations 001-019 parity retained; Migrations 017-019 add isolated SPMI workspaces. */
CREATE TABLE IF NOT EXISTS `spmi_audit_cycles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `cycle_code` VARCHAR(64) NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `description` TEXT NULL,
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `state` ENUM('draft','configured','closed') NOT NULL DEFAULT 'draft',
    `created_by` INT NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_spmi_audit_cycles_code` (`cycle_code`),
    KEY `idx_spmi_audit_cycles_state_start` (`state`, `start_date`),
    CONSTRAINT `fk_spmi_audit_cycles_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `spmi_audit_assignments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `cycle_id` INT NOT NULL,
    `source_package_id` INT NOT NULL,
    `auditor_id` INT NOT NULL,
    `auditee_id` INT NOT NULL,
    `created_by` INT NOT NULL,
    `source_version_id` INT NOT NULL,
    `source_version_code` VARCHAR(64) NOT NULL,
    `source_version_title` VARCHAR(200) NOT NULL,
    `source_standard_id` INT NOT NULL,
    `source_standard_code` VARCHAR(64) NOT NULL,
    `source_standard_title` VARCHAR(200) NOT NULL,
    `source_package_code` VARCHAR(64) NOT NULL,
    `source_package_title` VARCHAR(200) NOT NULL,
    `source_package_description` TEXT NULL,
    `auditor_name` VARCHAR(200) NOT NULL,
    `auditor_email` VARCHAR(255) NOT NULL,
    `auditee_name` VARCHAR(200) NOT NULL,
    `auditee_email` VARCHAR(255) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_spmi_audit_assignments_tuple` (`cycle_id`, `source_package_id`, `auditor_id`, `auditee_id`),
    KEY `idx_spmi_audit_assignments_cycle` (`cycle_id`),
    KEY `idx_spmi_audit_assignments_auditor` (`auditor_id`),
    KEY `idx_spmi_audit_assignments_auditee` (`auditee_id`),
    CONSTRAINT `fk_spmi_audit_assignments_cycle` FOREIGN KEY (`cycle_id`) REFERENCES `spmi_audit_cycles` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT `fk_spmi_audit_assignments_package` FOREIGN KEY (`source_package_id`) REFERENCES `spmi_instrument_packages` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT `fk_spmi_audit_assignments_auditor` FOREIGN KEY (`auditor_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT `fk_spmi_audit_assignments_auditee` FOREIGN KEY (`auditee_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT `fk_spmi_audit_assignments_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT `fk_spmi_audit_assignments_version` FOREIGN KEY (`source_version_id`) REFERENCES `spmi_versions` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT `fk_spmi_audit_assignments_standard` FOREIGN KEY (`source_standard_id`) REFERENCES `spmi_standards` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `spmi_audit_assignment_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `assignment_id` INT NOT NULL,
    `source_question_id` INT NOT NULL,
    `source_indicator_id` INT NOT NULL,
    `display_order` INT NOT NULL,
    `question_code` VARCHAR(64) NOT NULL,
    `question_text` TEXT NOT NULL,
    `evidence_instruction` TEXT NOT NULL,
    `evidence_policy` ENUM('none','file','url','either','both') NOT NULL DEFAULT 'none',
    `indicator_code` VARCHAR(64) NOT NULL,
    `indicator_title` VARCHAR(200) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_spmi_audit_assignment_items_order` (`assignment_id`, `display_order`),
    UNIQUE KEY `uq_spmi_audit_assignment_items_question` (`assignment_id`, `source_question_id`),
    CONSTRAINT `fk_spmi_audit_assignment_items_assignment` FOREIGN KEY (`assignment_id`) REFERENCES `spmi_audit_assignments` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT `fk_spmi_audit_assignment_items_question` FOREIGN KEY (`source_question_id`) REFERENCES `spmi_instrument_questions` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT `fk_spmi_audit_assignment_items_indicator` FOREIGN KEY (`source_indicator_id`) REFERENCES `spmi_indicators` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `spmi_audit_assignment_item_rubrics` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `assignment_item_id` INT NOT NULL,
    `score` TINYINT UNSIGNED NOT NULL,
    `descriptor` TEXT NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_spmi_audit_assignment_item_rubrics_score` (`assignment_item_id`, `score`),
    CONSTRAINT `fk_spmi_audit_assignment_item_rubrics_item` FOREIGN KEY (`assignment_item_id`) REFERENCES `spmi_audit_assignment_items` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `spmi_auditee_submissions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `assignment_id` INT NOT NULL,
    `status` ENUM('draft','submitted','returned_for_revision','resubmitted','under_assessment','completed') NOT NULL DEFAULT 'draft',
    `submitted_at` DATETIME NULL DEFAULT NULL,
    `version` INT UNSIGNED NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_spmi_auditee_submissions_assignment` (`assignment_id`),
    KEY `idx_spmi_auditee_submissions_status` (`status`),
    CONSTRAINT `fk_spmi_auditee_submissions_assignment` FOREIGN KEY (`assignment_id`) REFERENCES `spmi_audit_assignments` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `spmi_auditee_submission_revision_events` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `submission_id` INT NOT NULL,
    `assignment_id` INT NOT NULL,
    `actor_user_id` INT NOT NULL,
    `reason` TEXT NOT NULL,
    `submission_version` INT UNSIGNED NOT NULL,
    `previous_status` ENUM('draft','submitted','returned_for_revision','resubmitted','under_assessment','completed') NOT NULL DEFAULT 'draft',
    `new_status` ENUM('draft','submitted','returned_for_revision','resubmitted','under_assessment','completed') NOT NULL DEFAULT 'draft',
    `previous_version` INT UNSIGNED NOT NULL DEFAULT 1,
    `resulting_version` INT UNSIGNED NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_spmi_submission_revision_events_submission` (`submission_id`, `created_at`),
    KEY `idx_spmi_submission_revision_events_assignment` (`assignment_id`, `created_at`),
    CONSTRAINT `fk_spmi_submission_revision_events_submission` FOREIGN KEY (`submission_id`) REFERENCES `spmi_auditee_submissions` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT `fk_spmi_submission_revision_events_assignment` FOREIGN KEY (`assignment_id`) REFERENCES `spmi_audit_assignments` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT `fk_spmi_submission_revision_events_actor` FOREIGN KEY (`actor_user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `spmi_auditee_submission_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `submission_id` INT NOT NULL,
    `assignment_item_id` INT NOT NULL,
    `realization` TEXT NOT NULL,
    `evidence_url` VARCHAR(500) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_spmi_auditee_submission_items_item` (`submission_id`, `assignment_item_id`),
    CONSTRAINT `fk_spmi_auditee_submission_items_submission` FOREIGN KEY (`submission_id`) REFERENCES `spmi_auditee_submissions` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT `fk_spmi_auditee_submission_items_assignment_item` FOREIGN KEY (`assignment_item_id`) REFERENCES `spmi_audit_assignment_items` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `spmi_auditee_evidence` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `submission_item_id` INT NOT NULL,
    `stored_name` VARCHAR(255) NOT NULL,
    `original_name` VARCHAR(255) NOT NULL,
    `mime_type` VARCHAR(100) NOT NULL,
    `size_bytes` INT UNSIGNED NOT NULL,
    `sha256` CHAR(64) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_spmi_auditee_evidence_stored_name` (`stored_name`),
    KEY `idx_spmi_auditee_evidence_item` (`submission_item_id`),
    CONSTRAINT `fk_spmi_auditee_evidence_submission_item` FOREIGN KEY (`submission_item_id`) REFERENCES `spmi_auditee_submission_items` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `spmi_auditor_assessments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `assignment_id` INT NOT NULL,
    `status` ENUM('draft','finalized') NOT NULL DEFAULT 'draft',
    `finalized_at` DATETIME NULL DEFAULT NULL,
    `version` INT UNSIGNED NOT NULL DEFAULT 1,
    `source_submission_version` INT UNSIGNED NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_spmi_auditor_assessments_assignment_source_version` (`assignment_id`, `source_submission_version`),
    KEY `idx_spmi_auditor_assessments_status` (`status`),
    CONSTRAINT `fk_spmi_auditor_assessments_assignment` FOREIGN KEY (`assignment_id`) REFERENCES `spmi_audit_assignments` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `spmi_auditor_assessment_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `assessment_id` INT NOT NULL,
    `assignment_item_id` INT NOT NULL,
    `realization_snapshot` TEXT NOT NULL,
    `score` TINYINT UNSIGNED NULL,
    `finding` TEXT NULL,
    `finding_type` ENUM('ob','kts') NULL,
    `recommendation` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_spmi_auditor_assessment_items_item` (`assessment_id`, `assignment_item_id`),
    KEY `idx_spmi_auditor_assessment_items_item` (`assignment_item_id`),
    CONSTRAINT `fk_spmi_auditor_assessment_items_assessment` FOREIGN KEY (`assessment_id`) REFERENCES `spmi_auditor_assessments` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT `fk_spmi_auditor_assessment_items_assignment_item` FOREIGN KEY (`assignment_item_id`) REFERENCES `spmi_audit_assignment_items` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `spmi_reports` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `assessment_id` INT NOT NULL,
    `report_number` VARCHAR(128) NOT NULL,
    `cycle_code_snapshot` VARCHAR(64) NOT NULL,
    `cycle_title_snapshot` VARCHAR(200) NOT NULL,
    `cycle_start_date_snapshot` DATE NOT NULL,
    `cycle_end_date_snapshot` DATE NOT NULL,
    `source_version_code_snapshot` VARCHAR(64) NOT NULL,
    `source_version_title_snapshot` VARCHAR(200) NOT NULL,
    `source_standard_code_snapshot` VARCHAR(64) NOT NULL,
    `source_standard_title_snapshot` VARCHAR(200) NOT NULL,
    `source_package_code_snapshot` VARCHAR(64) NOT NULL,
    `source_package_title_snapshot` VARCHAR(200) NOT NULL,
    `auditor_name_snapshot` VARCHAR(200) NOT NULL,
    `auditee_name_snapshot` VARCHAR(200) NOT NULL,
    `assessment_finalized_at_snapshot` DATETIME NOT NULL,
    `generated_by` INT NOT NULL,
    `generated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_spmi_reports_assessment` (`assessment_id`),
    UNIQUE KEY `uq_spmi_reports_report_number` (`report_number`),
    CONSTRAINT `fk_spmi_reports_assessment` FOREIGN KEY (`assessment_id`) REFERENCES `spmi_auditor_assessments` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT `fk_spmi_reports_generated_by` FOREIGN KEY (`generated_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `spmi_report_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `report_id` INT NOT NULL,
    `display_order` INT NOT NULL,
    `question_code_snapshot` VARCHAR(64) NOT NULL,
    `question_text_snapshot` TEXT NOT NULL,
    `indicator_code_snapshot` VARCHAR(64) NOT NULL,
    `indicator_title_snapshot` VARCHAR(200) NOT NULL,
    `realization_snapshot` TEXT NOT NULL,
    `evidence_url_snapshot` VARCHAR(500) NULL,
    `evidence_file_original_name_snapshot` VARCHAR(255) NULL,
    `evidence_file_mime_type_snapshot` VARCHAR(100) NULL,
    `evidence_file_size_bytes_snapshot` INT UNSIGNED NULL,
    `evidence_file_sha256_snapshot` CHAR(64) NULL,
    `score` TINYINT UNSIGNED NOT NULL,
    `descriptor_snapshot` TEXT NOT NULL,
    `finding_snapshot` TEXT NULL,
    `finding_type_snapshot` ENUM('ob','kts') NULL,
    `recommendation_snapshot` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_spmi_report_items_order` (`report_id`, `display_order`),
    CONSTRAINT `fk_spmi_report_items_report` FOREIGN KEY (`report_id`) REFERENCES `spmi_reports` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `spmi_rtm_meetings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `meeting_code` VARCHAR(128) NOT NULL,
    `meeting_title` VARCHAR(200) NOT NULL,
    `meeting_date` DATE NOT NULL,
    `location` VARCHAR(200) NOT NULL,
    `status` ENUM('draft','resolved') NOT NULL DEFAULT 'draft',
    `created_by` INT NOT NULL,
    `resolved_by` INT NULL,
    `resolved_at` DATETIME NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_spmi_rtm_meetings_code` (`meeting_code`),
    CONSTRAINT `fk_spmi_rtm_meetings_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT `fk_spmi_rtm_meetings_resolved_by` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `spmi_rtm_meeting_reports` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `meeting_id` INT NOT NULL,
    `report_id` INT NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_spmi_rtm_meeting_reports_report` (`meeting_id`, `report_id`),
    CONSTRAINT `fk_spmi_rtm_meeting_reports_meeting` FOREIGN KEY (`meeting_id`) REFERENCES `spmi_rtm_meetings` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT `fk_spmi_rtm_meeting_reports_report` FOREIGN KEY (`report_id`) REFERENCES `spmi_reports` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `spmi_rtm_participants` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `meeting_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `name_snapshot` VARCHAR(100) NOT NULL,
    `email_snapshot` VARCHAR(100) NOT NULL,
    `role_snapshot` VARCHAR(32) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_spmi_rtm_participants_user` (`meeting_id`, `user_id`),
    CONSTRAINT `fk_spmi_rtm_participants_meeting` FOREIGN KEY (`meeting_id`) REFERENCES `spmi_rtm_meetings` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT `fk_spmi_rtm_participants_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `spmi_rtm_decisions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `meeting_id` INT NOT NULL,
    `display_order` INT UNSIGNED NOT NULL,
    `decision_text` TEXT NOT NULL,
    `action_text` TEXT NOT NULL,
    `report_id` INT NULL,
    `report_item_id` INT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_spmi_rtm_decisions_order` (`meeting_id`, `display_order`),
    CONSTRAINT `fk_spmi_rtm_decisions_meeting` FOREIGN KEY (`meeting_id`) REFERENCES `spmi_rtm_meetings` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT `fk_spmi_rtm_decisions_report` FOREIGN KEY (`report_id`) REFERENCES `spmi_reports` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT `fk_spmi_rtm_decisions_report_item` FOREIGN KEY (`report_item_id`) REFERENCES `spmi_report_items` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `spmi_rtm_follow_ups` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `follow_up_code` VARCHAR(128) NOT NULL,
    `decision_id` INT NOT NULL,
    `decision_text_snapshot` TEXT NOT NULL,
    `action_text_snapshot` TEXT NOT NULL,
    `responsible_user_id` INT NOT NULL,
    `responsible_name_snapshot` VARCHAR(100) NOT NULL,
    `responsible_email_snapshot` VARCHAR(100) NOT NULL,
    `responsible_role_snapshot` VARCHAR(32) NOT NULL,
    `due_date` DATE NULL,
    `follow_up_note` TEXT NULL,
    `status` ENUM('open','in_progress','completed') NOT NULL DEFAULT 'open',
    `started_by` INT NULL,
    `started_at` DATETIME NULL,
    `completion_note` TEXT NULL,
    `completed_by` INT NULL,
    `completed_at` DATETIME NULL,
    `created_by` INT NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_spmi_rtm_follow_ups_code` (`follow_up_code`),
    UNIQUE KEY `uq_spmi_rtm_follow_ups_decision` (`decision_id`),
    CONSTRAINT `fk_spmi_rtm_follow_ups_decision` FOREIGN KEY (`decision_id`) REFERENCES `spmi_rtm_decisions` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT `fk_spmi_rtm_follow_ups_responsible` FOREIGN KEY (`responsible_user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT `fk_spmi_rtm_follow_ups_started_by` FOREIGN KEY (`started_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT `fk_spmi_rtm_follow_ups_completed_by` FOREIGN KEY (`completed_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT `fk_spmi_rtm_follow_ups_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `legacy_ami_archive_runs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `archive_code` VARCHAR(128) NOT NULL,
    `source_label` VARCHAR(200) NOT NULL,
    `status` ENUM('planned','imported','reconciled','failed') NOT NULL DEFAULT 'planned',
    `legacy_task_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `legacy_answer_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `archived_task_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `archived_answer_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `issue_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `notes` TEXT NULL,
    `created_by_snapshot` VARCHAR(200) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_legacy_ami_archive_runs_code` (`archive_code`),
    KEY `idx_legacy_ami_archive_runs_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `legacy_ami_archive_tasks` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `run_id` INT NOT NULL,
    `legacy_tugas_id` INT NOT NULL,
    `legacy_periode_id` INT NULL,
    `legacy_standar_id` INT NULL,
    `legacy_auditor_id` INT NULL,
    `legacy_auditee_id` INT NULL,
    `periode_name_snapshot` VARCHAR(200) NULL,
    `periode_year_snapshot` VARCHAR(20) NULL,
    `periode_semester_snapshot` VARCHAR(20) NULL,
    `standard_name_snapshot` VARCHAR(200) NULL,
    `standard_description_snapshot` TEXT NULL,
    `auditor_name_snapshot` VARCHAR(200) NULL,
    `auditor_email_snapshot` VARCHAR(200) NULL,
    `auditee_name_snapshot` VARCHAR(200) NULL,
    `auditee_email_snapshot` VARCHAR(200) NULL,
    `auditee_unit_snapshot` VARCHAR(200) NULL,
    `auditee_unit_type_snapshot` VARCHAR(64) NULL,
    `legacy_status_snapshot` VARCHAR(64) NOT NULL,
    `legacy_created_at_snapshot` DATETIME NULL,
    `archived_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_legacy_ami_archive_tasks_legacy` (`run_id`, `legacy_tugas_id`),
    KEY `idx_legacy_ami_archive_tasks_run` (`run_id`),
    CONSTRAINT `fk_legacy_ami_archive_tasks_run` FOREIGN KEY (`run_id`) REFERENCES `legacy_ami_archive_runs` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `legacy_ami_archive_answers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `archive_task_id` INT NOT NULL,
    `legacy_jawaban_id` INT NOT NULL,
    `legacy_pertanyaan_id` INT NULL,
    `question_order_snapshot` INT NULL,
    `question_text_snapshot` TEXT NULL,
    `question_category_snapshot` VARCHAR(32) NULL,
    `answer_text_snapshot` TEXT NULL,
    `evidence_link_snapshot` VARCHAR(500) NULL,
    `submitted_snapshot` TINYINT(1) NOT NULL DEFAULT 0,
    `submitted_at_snapshot` DATETIME NULL,
    `score_snapshot` INT NULL,
    `finding_snapshot` TEXT NULL,
    `finding_type_snapshot` VARCHAR(32) NULL,
    `recommendation_snapshot` TEXT NULL,
    `improvement_plan_snapshot` TEXT NULL,
    `evidence_document_name_snapshot` VARCHAR(255) NULL,
    `evidence_date_snapshot` DATE NULL,
    `assessment_submitted_snapshot` TINYINT(1) NOT NULL DEFAULT 0,
    `assessment_submitted_at_snapshot` DATETIME NULL,
    `archived_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_legacy_ami_archive_answers_legacy` (`archive_task_id`, `legacy_jawaban_id`),
    KEY `idx_legacy_ami_archive_answers_task` (`archive_task_id`),
    CONSTRAINT `fk_legacy_ami_archive_answers_task` FOREIGN KEY (`archive_task_id`) REFERENCES `legacy_ami_archive_tasks` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `legacy_ami_archive_issues` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `run_id` INT NOT NULL,
    `archive_task_id` INT NULL,
    `archive_answer_id` INT NULL,
    `severity` ENUM('info','warning','error') NOT NULL DEFAULT 'warning',
    `issue_code` VARCHAR(128) NOT NULL,
    `message` TEXT NOT NULL,
    `legacy_table_snapshot` VARCHAR(64) NULL,
    `legacy_id_snapshot` INT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_legacy_ami_archive_issues_run` (`run_id`, `severity`),
    CONSTRAINT `fk_legacy_ami_archive_issues_run` FOREIGN KEY (`run_id`) REFERENCES `legacy_ami_archive_runs` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT `fk_legacy_ami_archive_issues_task` FOREIGN KEY (`archive_task_id`) REFERENCES `legacy_ami_archive_tasks` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT `fk_legacy_ami_archive_issues_answer` FOREIGN KEY (`archive_answer_id`) REFERENCES `legacy_ami_archive_answers` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `legacy_ami_archive_user_units` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `run_id` INT NOT NULL,
    `archive_task_id` INT NOT NULL,
    `legacy_user_id` INT NULL,
    `role_snapshot` ENUM('auditor','auditee') NOT NULL,
    `user_name_snapshot` VARCHAR(200) NULL,
    `user_email_snapshot` VARCHAR(200) NULL,
    `unit_name_snapshot` VARCHAR(200) NULL,
    `unit_type_snapshot` VARCHAR(64) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_legacy_ami_archive_user_units_run` (`run_id`, `role_snapshot`),
    CONSTRAINT `fk_legacy_ami_archive_user_units_run` FOREIGN KEY (`run_id`) REFERENCES `legacy_ami_archive_runs` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT `fk_legacy_ami_archive_user_units_task` FOREIGN KEY (`archive_task_id`) REFERENCES `legacy_ami_archive_tasks` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `audit_logs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `actor_user_id` BIGINT UNSIGNED NULL,
    `actor_role` VARCHAR(32) NULL,
    `event_type` VARCHAR(64) NOT NULL,
    `outcome` VARCHAR(32) NOT NULL,
    `resource_type` VARCHAR(64) NULL,
    `resource_id` BIGINT UNSIGNED NULL,
    `request_method` VARCHAR(8) NULL,
    `route` VARCHAR(255) NULL,
    `client_ip` VARCHAR(45) NULL,
    `metadata_json` TEXT NULL,
    KEY `idx_audit_logs_created_at` (`created_at`),
    KEY `idx_audit_logs_actor_created` (`actor_user_id`, `created_at`),
    KEY `idx_audit_logs_event_created` (`event_type`, `created_at`),
    KEY `idx_audit_logs_resource` (`resource_type`, `resource_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT IGNORE INTO `spmi_versions` (`version_code`, `title`, `description`, `status`, `created_by`)
VALUES ('M3-INITIAL', 'Katalog Standar SPMI M3', NULL, 'draft', NULL);
INSERT IGNORE INTO `spmi_standards` (`version_id`, `standard_code`, `display_order`, `title`, `description`)
SELECT `id`, CONCAT('SPMI-', LPAD(numbers.n, 2, '0')), numbers.n, CONCAT('Standar SPMI ', LPAD(numbers.n, 2, '0')), NULL FROM `spmi_versions`
CROSS JOIN (SELECT 1 n UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10 UNION ALL SELECT 11 UNION ALL SELECT 12 UNION ALL SELECT 13 UNION ALL SELECT 14 UNION ALL SELECT 15 UNION ALL SELECT 16 UNION ALL SELECT 17 UNION ALL SELECT 18 UNION ALL SELECT 19 UNION ALL SELECT 20 UNION ALL SELECT 21) numbers
WHERE `version_code` = 'M3-INITIAL';
