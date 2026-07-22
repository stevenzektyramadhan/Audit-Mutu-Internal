-- Database schema for AMI CodeIgniter 3
-- Mencakup seluruh perubahan dari migration 001-011

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
