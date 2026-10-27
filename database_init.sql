-- Database starter untuk AMI CodeIgniter 3 (dengan akun initial super_admin)
-- Berisi struktur tabel lengkap, seed referensi/standar SPMI, dan 1 akun super_admin aktif.
-- Jalankan pada database yang sudah dibuat dan dipilih:
-- mariadb -u root -p --database=ami < database_init.sql

SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------
-- Struktur Schema
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nama` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `identity_number` VARCHAR(32) NULL,
    `password` VARCHAR(255) NOT NULL,
    `must_change_password` TINYINT(1) NOT NULL DEFAULT 0,
    `role` ENUM('super_admin','admin_lpmpi','auditor','auditee') NOT NULL,
    `nama_unit` VARCHAR(100) NULL,
    `jenis_unit` ENUM('prodi','unit','lembaga') NULL,
    `profile_photo_path` VARCHAR(255) NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_users_identity_number` (`identity_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `token_hash` CHAR(64) NOT NULL,
    `expires_at` DATETIME NOT NULL,
    `consumed_at` DATETIME NULL DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_password_reset_tokens_hash` (`token_hash`),
    KEY `idx_password_reset_tokens_user_id` (`user_id`),
    CONSTRAINT `fk_password_reset_tokens_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `login_rate_limit_buckets` (
    `ip_address` VARCHAR(45) NOT NULL,
    `attempt_count` INT NOT NULL DEFAULT 0,
    `window_start` INT NOT NULL DEFAULT 0,
    `blocked_until` INT NOT NULL DEFAULT 0,
    PRIMARY KEY (`ip_address`),
    KEY `idx_login_rate_limit_blocked_until` (`blocked_until`),
    KEY `idx_login_rate_limit_window_start` (`window_start`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `organization_units` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nama_unit` VARCHAR(100) NOT NULL,
    `jenis_unit` ENUM('prodi','unit','lembaga') NOT NULL,
    `deskripsi` TEXT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_org_units_nama_jenis` (`nama_unit`, `jenis_unit`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `standar_spmi` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `kode_standar` VARCHAR(50) NOT NULL,
    `nama_standar` VARCHAR(255) NOT NULL,
    `deskripsi` TEXT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `pertanyaan` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `standar_id` INT NOT NULL,
    `pertanyaan` TEXT NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `urutan` INT NULL,
    `nilai_standar` VARCHAR(255) NULL,
    `baseline` VARCHAR(255) NULL,
    `target_2025` VARCHAR(255) NULL,
    `target_2026` VARCHAR(255) NULL,
    `target_2027` VARCHAR(255) NULL,
    `target_2028` VARCHAR(255) NULL,
    `target_2029` VARCHAR(255) NULL,
    `target_2030` VARCHAR(255) NULL,
    `kategori` ENUM('pendidikan','penelitian','pengabdian','tata_pamong','lainnya') NULL,
    KEY `idx_pertanyaan_standar_id` (`standar_id`),
    CONSTRAINT `fk_pertanyaan_standar` FOREIGN KEY (`standar_id`) REFERENCES `standar_spmi` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `periode_audit` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tahun` INT NOT NULL,
    `semester` ENUM('ganjil','genap') NOT NULL,
    `nama_periode` VARCHAR(100) NOT NULL,
    `tanggal_mulai` DATE NOT NULL,
    `tanggal_selesai` DATE NOT NULL,
    `status` ENUM('draft','aktif','selesai') DEFAULT 'draft',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `tugas_audit` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `periode_id` INT NOT NULL,
    `nama_unit` VARCHAR(100) NOT NULL,
    `jenis_unit` ENUM('prodi','unit','lembaga') NOT NULL,
    `status` ENUM('belum_mulai','proses','selesai') DEFAULT 'belum_mulai',
    `tanggal_audit` DATE NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_tugas_audit_periode_id` (`periode_id`),
    CONSTRAINT `fk_tugas_audit_periode` FOREIGN KEY (`periode_id`) REFERENCES `periode_audit` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `tugas_auditor` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tugas_id` INT NOT NULL,
    `auditor_id` INT NOT NULL,
    `peran` ENUM('ketua','anggota') DEFAULT 'anggota',
    UNIQUE KEY `uq_tugas_auditor` (`tugas_id`, `auditor_id`),
    KEY `idx_tugas_auditor_auditor_id` (`auditor_id`),
    CONSTRAINT `fk_tugas_auditor_tugas` FOREIGN KEY (`tugas_id`) REFERENCES `tugas_audit` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_tugas_auditor_user` FOREIGN KEY (`auditor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `jawaban_auditee` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tugas_id` INT NOT NULL,
    `pertanyaan_id` INT NOT NULL,
    `jawaban` ENUM('Ya','Tidak') NOT NULL,
    `deskripsi_bukti` TEXT NULL,
    `file_bukti` VARCHAR(255) NULL,
    `submitted_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `catatan_auditor` TEXT NULL,
    `temuan` ENUM('sesuai','minor','mayor','observasi') NULL,
    `audit_status` ENUM('belum_dinilai','sesuai','revisi','selesai') DEFAULT 'belum_dinilai',
    `reviewed_at` DATETIME NULL,
    UNIQUE KEY `uq_jawaban_tugas_pertanyaan` (`tugas_id`, `pertanyaan_id`),
    KEY `idx_jawaban_auditee_pertanyaan_id` (`pertanyaan_id`),
    CONSTRAINT `fk_jawaban_auditee_pertanyaan` FOREIGN KEY (`pertanyaan_id`) REFERENCES `pertanyaan` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_jawaban_auditee_tugas` FOREIGN KEY (`tugas_id`) REFERENCES `tugas_audit` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `temuan_audit` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tugas_id` INT NOT NULL,
    `pertanyaan_id` INT NULL,
    `jenis_temuan` ENUM('minor','mayor','observasi') NOT NULL,
    `deskripsi` TEXT NOT NULL,
    `akar_masalah` TEXT NULL,
    `rekomendasi` TEXT NULL,
    `rencana_perbaikan` TEXT NULL,
    `target_penyelesaian` DATE NULL,
    `status` ENUM('open','in_progress','closed') DEFAULT 'open',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_temuan_audit_pertanyaan_id` (`pertanyaan_id`),
    KEY `idx_temuan_audit_tugas_id` (`tugas_id`),
    CONSTRAINT `fk_temuan_audit_pertanyaan` FOREIGN KEY (`pertanyaan_id`) REFERENCES `pertanyaan` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_temuan_audit_tugas` FOREIGN KEY (`tugas_id`) REFERENCES `tugas_audit` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `system_settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `turnstile_enabled` TINYINT(1) NOT NULL DEFAULT 0,
    `turnstile_site_key` VARCHAR(255) NULL,
    `turnstile_secret_key` TEXT NULL,
    `turnstile_mode` ENUM('managed','non_interactive','invisible') NOT NULL DEFAULT 'managed',
    `hero_title` VARCHAR(255) NULL,
    `hero_subtitle` TEXT NULL,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `updated_by` INT NULL,
    CONSTRAINT `fk_system_settings_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `upload_size_settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `max_size_kb` INT UNSIGNED NOT NULL DEFAULT 2048,
    `updated_by` INT NULL,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_upload_size_settings_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `profil_lembaga` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nama_lembaga` VARCHAR(255) NOT NULL,
    `nama_ketua` VARCHAR(255) NULL,
    `visi` TEXT NULL,
    `misi` TEXT NULL,
    `sejarah` TEXT NULL,
    `struktur_organisasi_path` VARCHAR(255) NULL,
    `logo_path` VARCHAR(255) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `user_unit_assignments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `unit_id` INT NOT NULL,
    `assigned_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_user_unit_assignment` (`user_id`, `unit_id`),
    KEY `idx_user_unit_assignments_unit_id` (`unit_id`),
    CONSTRAINT `fk_user_unit_assignments_unit` FOREIGN KEY (`unit_id`) REFERENCES `organization_units` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_user_unit_assignments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `capabilities` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(64) NOT NULL,
    `label` VARCHAR(128) NOT NULL,
    `category` VARCHAR(64) NOT NULL DEFAULT 'spmi',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_capabilities_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `role_capabilities` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `role` ENUM('super_admin','admin_lpmpi','auditor','auditee') NOT NULL,
    `capability_id` INT NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_role_capability` (`role`, `capability_id`),
    KEY `idx_role_capabilities_capability_id` (`capability_id`),
    CONSTRAINT `fk_role_capabilities_capability` FOREIGN KEY (`capability_id`) REFERENCES `capabilities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `spmi_versions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `version_code` VARCHAR(64) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `status` ENUM('draft','active','archived') NOT NULL DEFAULT 'draft',
    `created_by` INT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `activated_at` DATETIME NULL,
    `archived_at` DATETIME NULL,
    UNIQUE KEY `uq_spmi_versions_code` (`version_code`),
    KEY `idx_spmi_versions_created_by` (`created_by`),
    CONSTRAINT `fk_spmi_versions_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `spmi_standards` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `version_id` INT NOT NULL,
    `standard_code` VARCHAR(64) NOT NULL,
    `display_order` INT NOT NULL DEFAULT 1,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_spmi_standards_version_code` (`version_id`, `standard_code`),
    KEY `idx_spmi_standards_version_order` (`version_id`, `display_order`),
    CONSTRAINT `fk_spmi_standards_version` FOREIGN KEY (`version_id`) REFERENCES `spmi_versions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `spmi_indicators` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `standard_id` INT NOT NULL,
    `indicator_code` VARCHAR(64) NOT NULL,
    `display_order` INT NOT NULL DEFAULT 1,
    `title` VARCHAR(255) NOT NULL,
    `target_value` TEXT NULL,
    `baseline_value` TEXT NULL,
    `evidence_guide` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_spmi_indicators_standard_code` (`standard_id`, `indicator_code`),
    KEY `idx_spmi_indicators_standard_order` (`standard_id`, `display_order`),
    CONSTRAINT `fk_spmi_indicators_standard` FOREIGN KEY (`standard_id`) REFERENCES `spmi_standards` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `spmi_instruments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `version_id` INT NOT NULL,
    `target_level` ENUM('university','faculty','study_program','unit') NOT NULL,
    `status` ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
    `published_at` DATETIME NULL,
    `created_by` INT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_spmi_instruments_version_id` (`version_id`),
    KEY `idx_spmi_instruments_created_by` (`created_by`),
    CONSTRAINT `fk_spmi_instruments_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_spmi_instruments_version` FOREIGN KEY (`version_id`) REFERENCES `spmi_versions` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `spmi_instrument_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `instrument_id` INT NOT NULL,
    `indicator_id` INT NOT NULL,
    `display_order` INT NOT NULL DEFAULT 1,
    `weight` DECIMAL(5,2) NOT NULL DEFAULT 1.00,
    `verification_instruction` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_spmi_instrument_indicator` (`instrument_id`, `indicator_id`),
    KEY `idx_spmi_instrument_items_order` (`instrument_id`, `display_order`),
    KEY `idx_spmi_instrument_items_indicator_id` (`indicator_id`),
    CONSTRAINT `fk_spmi_instrument_items_indicator` FOREIGN KEY (`indicator_id`) REFERENCES `spmi_indicators` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_spmi_instrument_items_instrument` FOREIGN KEY (`instrument_id`) REFERENCES `spmi_instruments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `spmi_audits` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(64) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `period_id` INT NOT NULL,
    `instrument_id` INT NOT NULL,
    `unit_id` INT NOT NULL,
    `lead_auditor_id` INT NULL,
    `status` ENUM('draft','scheduled','in_progress','completed','cancelled') NOT NULL DEFAULT 'draft',
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `desk_evaluation_due` DATE NULL,
    `field_evaluation_date` DATE NULL,
    `conclusion` TEXT NULL,
    `created_by` INT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_spmi_audits_code` (`code`),
    KEY `idx_spmi_audits_period_unit` (`period_id`, `unit_id`),
    KEY `idx_spmi_audits_instrument_id` (`instrument_id`),
    KEY `idx_spmi_audits_unit_id` (`unit_id`),
    KEY `idx_spmi_audits_lead_auditor_id` (`lead_auditor_id`),
    KEY `idx_spmi_audits_created_by` (`created_by`),
    CONSTRAINT `fk_spmi_audits_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_spmi_audits_instrument` FOREIGN KEY (`instrument_id`) REFERENCES `spmi_instruments` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_spmi_audits_lead_auditor` FOREIGN KEY (`lead_auditor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_spmi_audits_period` FOREIGN KEY (`period_id`) REFERENCES `periode_audit` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_spmi_audits_unit` FOREIGN KEY (`unit_id`) REFERENCES `organization_units` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `spmi_audit_auditors` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `audit_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `role` ENUM('lead','member') NOT NULL DEFAULT 'member',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_spmi_audit_user` (`audit_id`, `user_id`),
    KEY `idx_spmi_audit_auditors_user_id` (`user_id`),
    CONSTRAINT `fk_spmi_audit_auditors_audit` FOREIGN KEY (`audit_id`) REFERENCES `spmi_audits` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_spmi_audit_auditors_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `spmi_audit_assignments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `audit_id` INT NOT NULL,
    `instrument_item_id` INT NOT NULL,
    `assigned_auditor_id` INT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_spmi_audit_assignment_item` (`audit_id`, `instrument_item_id`),
    KEY `idx_spmi_audit_assignments_auditor` (`assigned_auditor_id`),
    KEY `idx_spmi_audit_assignments_item` (`instrument_item_id`),
    CONSTRAINT `fk_spmi_audit_assignments_audit` FOREIGN KEY (`audit_id`) REFERENCES `spmi_audits` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_spmi_audit_assignments_auditor` FOREIGN KEY (`assigned_auditor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_spmi_audit_assignments_item` FOREIGN KEY (`instrument_item_id`) REFERENCES `spmi_instrument_items` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `spmi_assignment_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `assignment_id` INT NOT NULL,
    `instrument_item_id` INT NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_spmi_assignment_items_unique` (`assignment_id`, `instrument_item_id`),
    KEY `idx_spmi_assignment_items_item` (`instrument_item_id`),
    CONSTRAINT `fk_spmi_assignment_items_assignment` FOREIGN KEY (`assignment_id`) REFERENCES `spmi_audit_assignments` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_spmi_assignment_items_item` FOREIGN KEY (`instrument_item_id`) REFERENCES `spmi_instrument_items` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `spmi_evidence_files` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `audit_id` INT NOT NULL,
    `original_name` VARCHAR(255) NOT NULL,
    `stored_name` VARCHAR(255) NOT NULL,
    `file_path` VARCHAR(255) NOT NULL,
    `file_size_kb` INT NOT NULL,
    `mime_type` VARCHAR(100) NOT NULL,
    `sha256_hash` CHAR(64) NULL,
    `storage_backend` ENUM('local','google_drive') NOT NULL DEFAULT 'local',
    `drive_file_id` VARCHAR(128) NULL,
    `drive_web_view_link` TEXT NULL,
    `drive_uploaded_at` DATETIME NULL,
    `drive_sync_error` TEXT NULL,
    `uploaded_by` INT NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_spmi_evidence_files_audit_id` (`audit_id`),
    KEY `idx_spmi_evidence_files_uploaded_by` (`uploaded_by`),
    CONSTRAINT `fk_spmi_evidence_files_audit` FOREIGN KEY (`audit_id`) REFERENCES `spmi_audits` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_spmi_evidence_files_uploader` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `spmi_indicator_evidence_policies` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `indicator_id` INT NOT NULL,
    `evidence_policy` ENUM('mandatory','optional') NOT NULL DEFAULT 'mandatory',
    `updated_by` INT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_spmi_indicator_evidence_policy_indicator` (`indicator_id`),
    KEY `idx_spmi_indicator_evidence_policies_updated_by` (`updated_by`),
    CONSTRAINT `fk_spmi_indicator_evidence_policies_indicator` FOREIGN KEY (`indicator_id`) REFERENCES `spmi_indicators` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_spmi_indicator_evidence_policies_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `spmi_assignment_item_evidence_policies` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `assignment_item_id` INT NOT NULL,
    `evidence_policy` ENUM('mandatory','optional') NOT NULL DEFAULT 'mandatory',
    `updated_by` INT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_spmi_assignment_item_evidence_policy_item` (`assignment_item_id`),
    KEY `idx_spmi_assignment_item_evidence_policy_updated_by` (`updated_by`),
    CONSTRAINT `fk_spmi_assignment_item_evidence_policy_item` FOREIGN KEY (`assignment_item_id`) REFERENCES `spmi_assignment_items` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_spmi_assignment_item_evidence_policy_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `spmi_findings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `audit_id` INT NOT NULL,
    `assignment_id` INT NULL,
    `type` ENUM('kts_mayor','kts_minor','ob') NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NOT NULL,
    `criteria` TEXT NOT NULL,
    `root_cause` TEXT NULL,
    `recommendation` TEXT NOT NULL,
    `due_date` DATE NULL,
    `status` ENUM('draft','open','submitted_response','verified','closed') NOT NULL DEFAULT 'draft',
    `correction_plan` TEXT NULL,
    `corrective_action_plan` TEXT NULL,
    `pic_user_id` INT NULL,
    `created_by` INT NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_spmi_findings_audit_id` (`audit_id`),
    KEY `idx_spmi_findings_assignment_id` (`assignment_id`),
    KEY `idx_spmi_findings_pic_user_id` (`pic_user_id`),
    KEY `idx_spmi_findings_created_by` (`created_by`),
    CONSTRAINT `fk_spmi_findings_assignment` FOREIGN KEY (`assignment_id`) REFERENCES `spmi_audit_assignments` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_spmi_findings_audit` FOREIGN KEY (`audit_id`) REFERENCES `spmi_audits` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_spmi_findings_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_spmi_findings_pic` FOREIGN KEY (`pic_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `spmi_rtm_sessions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `period_id` INT NOT NULL,
    `session_date` DATE NOT NULL,
    `location` VARCHAR(255) NOT NULL,
    `chairperson_id` INT NULL,
    `status` ENUM('planned','ongoing','completed','archived') NOT NULL DEFAULT 'planned',
    `notes` TEXT NULL,
    `photo_path` VARCHAR(255) NULL,
    `created_by` INT NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_spmi_rtm_sessions_period_id` (`period_id`),
    KEY `idx_spmi_rtm_sessions_chairperson` (`chairperson_id`),
    KEY `idx_spmi_rtm_sessions_created_by` (`created_by`),
    CONSTRAINT `fk_spmi_rtm_sessions_chair` FOREIGN KEY (`chairperson_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_spmi_rtm_sessions_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_spmi_rtm_sessions_period` FOREIGN KEY (`period_id`) REFERENCES `periode_audit` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `spmi_rtm_attendees` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `rtm_session_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `attendance_role` VARCHAR(100) NOT NULL DEFAULT 'Peserta',
    `attended` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_spmi_rtm_attendee` (`rtm_session_id`, `user_id`),
    KEY `idx_spmi_rtm_attendees_user_id` (`user_id`),
    CONSTRAINT `fk_spmi_rtm_attendees_session` FOREIGN KEY (`rtm_session_id`) REFERENCES `spmi_rtm_sessions` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_spmi_rtm_attendees_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `spmi_rtm_follow_ups` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `rtm_session_id` INT NOT NULL,
    `finding_id` INT NULL,
    `issue_summary` TEXT NOT NULL,
    `action_plan` TEXT NOT NULL,
    `responsible_user_id` INT NULL,
    `target_date` DATE NOT NULL,
    `status` ENUM('open','in_progress','completed','deferred') NOT NULL DEFAULT 'open',
    `result_notes` TEXT NULL,
    `completed_at` DATETIME NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_spmi_rtm_follow_ups_session` (`rtm_session_id`),
    KEY `idx_spmi_rtm_follow_ups_finding` (`finding_id`),
    KEY `idx_spmi_rtm_follow_ups_responsible` (`responsible_user_id`),
    CONSTRAINT `fk_spmi_rtm_follow_ups_finding` FOREIGN KEY (`finding_id`) REFERENCES `spmi_findings` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_spmi_rtm_follow_ups_resp` FOREIGN KEY (`responsible_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_spmi_rtm_follow_ups_session` FOREIGN KEY (`rtm_session_id`) REFERENCES `spmi_rtm_sessions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `spmi_ppepp_documents` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `stage` ENUM('penetapan','pelaksanaan','evaluasi','pengendalian','peningkatan') NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `document_number` VARCHAR(100) NULL,
    `effective_date` DATE NULL,
    `stored_name` VARCHAR(255) NOT NULL,
    `original_name` VARCHAR(255) NOT NULL,
    `file_path` VARCHAR(255) NOT NULL,
    `file_size_kb` INT NOT NULL,
    `mime_type` VARCHAR(100) NOT NULL,
    `uploaded_by` INT NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_spmi_ppepp_documents_stage` (`stage`),
    KEY `idx_spmi_ppepp_documents_uploaded_by` (`uploaded_by`),
    CONSTRAINT `fk_spmi_ppepp_documents_uploader` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `spmi_revision_requests` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `audit_id` INT NOT NULL,
    `assignment_id` INT NOT NULL,
    `assignment_item_id` INT NOT NULL,
    `cycle_number` INT NOT NULL DEFAULT 1,
    `auditor_notes` TEXT NOT NULL,
    `status` ENUM('open','submitted','reviewed') NOT NULL DEFAULT 'open',
    `requested_by` INT NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `reviewed_at` DATETIME NULL,
    KEY `idx_spmi_revision_requests_audit` (`audit_id`),
    KEY `idx_spmi_revision_requests_item` (`assignment_item_id`),
    KEY `idx_spmi_revision_requests_requester` (`requested_by`),
    CONSTRAINT `fk_spmi_revision_requests_assignment` FOREIGN KEY (`assignment_id`) REFERENCES `spmi_audit_assignments` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_spmi_revision_requests_audit` FOREIGN KEY (`audit_id`) REFERENCES `spmi_audits` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_spmi_revision_requests_item` FOREIGN KEY (`assignment_item_id`) REFERENCES `spmi_assignment_items` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_spmi_revision_requests_user` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `spmi_revision_submissions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `revision_request_id` INT NOT NULL,
    `assignment_item_id` INT NOT NULL,
    `auditee_notes` TEXT NOT NULL,
    `submitted_by` INT NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_spmi_revision_submissions_request` (`revision_request_id`),
    KEY `idx_spmi_revision_submissions_item` (`assignment_item_id`),
    KEY `idx_spmi_revision_submissions_user` (`submitted_by`),
    CONSTRAINT `fk_spmi_revision_submissions_item` FOREIGN KEY (`assignment_item_id`) REFERENCES `spmi_assignment_items` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_spmi_revision_submissions_request` FOREIGN KEY (`revision_request_id`) REFERENCES `spmi_revision_requests` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_spmi_revision_submissions_user` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `spmi_revision_evidence_files` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `revision_submission_id` INT NOT NULL,
    `evidence_file_id` INT NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_spmi_revision_evidence_file` (`revision_submission_id`, `evidence_file_id`),
    KEY `idx_spmi_revision_evidence_files_evidence` (`evidence_file_id`),
    CONSTRAINT `fk_spmi_revision_evidence_files_evidence` FOREIGN KEY (`evidence_file_id`) REFERENCES `spmi_evidence_files` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_spmi_revision_evidence_files_submission` FOREIGN KEY (`revision_submission_id`) REFERENCES `spmi_revision_submissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `spmi_revision_item_reviews` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `revision_request_id` INT NOT NULL,
    `assignment_item_id` INT NOT NULL,
    `cycle_number` INT NOT NULL DEFAULT 1,
    `decision` ENUM('accept','re_request_revision') NOT NULL,
    `review_notes` TEXT NOT NULL,
    `reviewed_by` INT NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_spmi_revision_item_reviews_request` (`revision_request_id`),
    KEY `idx_spmi_revision_item_reviews_item` (`assignment_item_id`),
    KEY `idx_spmi_revision_item_reviews_reviewer` (`reviewed_by`),
    CONSTRAINT `fk_spmi_revision_item_reviews_item` FOREIGN KEY (`assignment_item_id`) REFERENCES `spmi_assignment_items` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_spmi_revision_item_reviews_request` FOREIGN KEY (`revision_request_id`) REFERENCES `spmi_revision_requests` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_spmi_revision_item_reviews_user` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `legacy_ami_archive_runs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `initiated_by_user_id` INT NOT NULL,
    `target_period_id` INT NULL,
    `dry_run` TINYINT(1) NOT NULL DEFAULT 1,
    `status` ENUM('pending','running','completed','failed') NOT NULL DEFAULT 'pending',
    `tasks_total` INT NOT NULL DEFAULT 0,
    `tasks_eligible` INT NOT NULL DEFAULT 0,
    `tasks_archived` INT NOT NULL DEFAULT 0,
    `tasks_blocked` INT NOT NULL DEFAULT 0,
    `answers_archived` INT NOT NULL DEFAULT 0,
    `evidence_files_archived` INT NOT NULL DEFAULT 0,
    `findings_archived` INT NOT NULL DEFAULT 0,
    `evidence_storage_bytes` BIGINT NOT NULL DEFAULT 0,
    `manifest_sha256` CHAR(64) NULL,
    `error_summary` TEXT NULL,
    `started_at` DATETIME NULL,
    `completed_at` DATETIME NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_legacy_ami_archive_runs_status` (`status`),
    KEY `idx_legacy_ami_archive_runs_target_period` (`target_period_id`),
    CONSTRAINT `fk_legacy_ami_archive_runs_initiator` FOREIGN KEY (`initiated_by_user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_legacy_ami_archive_runs_target_period` FOREIGN KEY (`target_period_id`) REFERENCES `periode_audit` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `legacy_ami_archive_tasks` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `run_id` INT NOT NULL,
    `legacy_tugas_id` INT NOT NULL,
    `periode_id` INT NOT NULL,
    `tahun_snapshot` INT NOT NULL,
    `semester_snapshot` ENUM('ganjil','genap') NOT NULL,
    `nama_periode_snapshot` VARCHAR(100) NOT NULL,
    `nama_unit_snapshot` VARCHAR(100) NOT NULL,
    `jenis_unit_snapshot` ENUM('prodi','unit','lembaga') NOT NULL,
    `status_tugas_snapshot` ENUM('belum_mulai','proses','selesai') NOT NULL,
    `tanggal_audit_snapshot` DATE NULL,
    `is_eligible` TINYINT(1) NOT NULL DEFAULT 0,
    `blocking_reason` VARCHAR(255) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_legacy_ami_archive_tasks_run_task` (`run_id`, `legacy_tugas_id`),
    KEY `idx_legacy_ami_archive_tasks_legacy_tugas` (`legacy_tugas_id`),
    CONSTRAINT `fk_legacy_ami_archive_tasks_run` FOREIGN KEY (`run_id`) REFERENCES `legacy_ami_archive_runs` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `legacy_ami_archive_answers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `archive_task_id` INT NOT NULL,
    `legacy_jawaban_id` INT NOT NULL,
    `pertanyaan_id` INT NOT NULL,
    `pertanyaan_snapshot` TEXT NOT NULL,
    `standar_id_snapshot` INT NOT NULL,
    `nama_standar_snapshot` VARCHAR(255) NOT NULL,
    `jawaban` ENUM('Ya','Tidak') NOT NULL,
    `deskripsi_bukti` TEXT NULL,
    `file_bukti_original_name` VARCHAR(255) NULL,
    `file_bukti_stored_path` VARCHAR(255) NULL,
    `file_bukti_sha256` CHAR(64) NULL,
    `catatan_auditor` TEXT NULL,
    `temuan` ENUM('sesuai','minor','mayor','observasi') NULL,
    `audit_status` ENUM('belum_dinilai','sesuai','revisi','selesai') NOT NULL,
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

-- --------------------------------------------------------
-- Data Referensi Standar & Konfigurasi Sistem
-- --------------------------------------------------------

INSERT IGNORE INTO `system_settings` (`id`, `turnstile_enabled`, `turnstile_site_key`, `turnstile_secret_key`, `turnstile_mode`, `hero_title`, `hero_subtitle`, `updated_at`, `updated_by`)
VALUES (1, 0, NULL, NULL, 'managed', 'Audit Mutu Internal', 'Sistem Penjaminan Mutu Internal Universitas Muhammadiyah Bangka Belitung', NOW(), NULL);

INSERT IGNORE INTO `upload_size_settings` (`id`, `max_size_kb`, `updated_by`, `updated_at`)
VALUES (1, 20480, NULL, NOW());

INSERT IGNORE INTO `organization_units` (`id`, `nama_unit`, `jenis_unit`, `deskripsi`, `created_at`)
VALUES
(1, 'LPMPI', 'lembaga', 'Lembaga Penjaminan Mutu dan Pengembangan Institusi', NOW()),
(2, 'Pendidikan Guru Sekolah Dasar', 'prodi', 'Program Studi PGSD', NOW()),
(3, 'Pendidikan Matematika', 'prodi', 'Program Studi Pendidikan Matematika', NOW()),
(4, 'Pendidikan Jasmani Kesehatan dan Rekreasi', 'prodi', 'Program Studi PJKR', NOW()),
(5, 'Pendidikan Bahasa Inggris', 'prodi', 'Program Studi Pendidikan Bahasa Inggris', NOW()),
(6, 'Ilmu Komputer', 'prodi', 'Program Studi Ilmu Komputer', NOW());

INSERT IGNORE INTO `capabilities` (`id`, `code`, `label`, `category`, `created_at`)
VALUES
(1, 'spmi.dashboard.view', 'Lihat Dashboard SPMI', 'spmi', NOW()),
(2, 'spmi.audit.manage', 'Kelola Audit SPMI', 'spmi', NOW()),
(3, 'spmi.audit.execute', 'Pelaksanaan Audit SPMI', 'spmi', NOW()),
(4, 'spmi.workspace.access', 'Akses Workspace Auditee SPMI', 'spmi', NOW());

INSERT IGNORE INTO `role_capabilities` (`role`, `capability_id`)
VALUES
('super_admin', 1),
('super_admin', 2),
('super_admin', 3),
('super_admin', 4),
('admin_lpmpi', 1),
('admin_lpmpi', 2),
('auditor', 3),
('auditee', 4);

INSERT IGNORE INTO `spmi_versions` (`version_code`, `title`, `description`, `status`, `created_by`)
VALUES ('M3-INITIAL', 'Katalog Standar SPMI M3', NULL, 'draft', NULL);

INSERT IGNORE INTO `spmi_standards` (`version_id`, `standard_code`, `display_order`, `title`, `description`)
SELECT `id`, CONCAT('SPMI-', LPAD(numbers.n, 2, '0')), numbers.n, CONCAT('Standar SPMI ', LPAD(numbers.n, 2, '0')), NULL FROM `spmi_versions`
CROSS JOIN (SELECT 1 n UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10 UNION ALL SELECT 11 UNION ALL SELECT 12 UNION ALL SELECT 13 UNION ALL SELECT 14 UNION ALL SELECT 15 UNION ALL SELECT 16 UNION ALL SELECT 17 UNION ALL SELECT 18 UNION ALL SELECT 19 UNION ALL SELECT 20 UNION ALL SELECT 21) numbers
WHERE `version_code` = 'M3-INITIAL';

-- --------------------------------------------------------
-- Akun Administrator Awal (Super Admin)
-- Email: admin.ami@unmuhbabel.ac.id
-- Password plaintext: AdminAmi123,. (disimpan sebagai bcrypt hash PASSWORD_DEFAULT)
-- --------------------------------------------------------

INSERT INTO `users` (`nama`, `email`, `identity_number`, `password`, `must_change_password`, `role`, `nama_unit`, `jenis_unit`, `created_at`)
VALUES (
    'Administrator',
    'admin.ami@unmuhbabel.ac.id',
    NULL,
    '$2y$10$Dm/D11Y1fCFNY2/1y51TseQuaPVTHLowmNh73ofECHrDX7zYK8uKS',
    0,
    'super_admin',
    'LPMPI',
    'lembaga',
    NOW()
)
ON DUPLICATE KEY UPDATE
    `password` = VALUES(`password`),
    `role` = VALUES(`role`),
    `must_change_password` = VALUES(`must_change_password`);

SET FOREIGN_KEY_CHECKS = 1;
