-- Database schema for AMI CodeIgniter 3
-- Mencakup seluruh perubahan dari migration 001-007

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
    `isi_pertanyaan` TEXT NOT NULL,
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

INSERT INTO `users` (`nama`, `email`, `password`, `role`) VALUES
('Admin Super', 'admin@ami.test', '$2y$10$NpwgFOOtWUjqClbtkVnXFeeHAK1K5NPh4PP7h2BJuWDqbv/CmnPp6', 'super_admin'),
('Auditor Demo', 'auditor@ami.test', '$2y$10$9m5mqeBxJl1KyCW70xSIBOSYy3PdTsfrEBeC7TiNHJqAPcsgBbiOK', 'auditor'),
('Auditee Demo', 'auditee@ami.test', '$2y$10$J/KZtzTXxrlr9oIsd9Fh9.FQznUGJL/1e3fKbEVKiOWjYy4Dzj8fi', 'auditee')
ON DUPLICATE KEY UPDATE
    `nama` = VALUES(`nama`),
    `password` = VALUES(`password`),
    `role` = VALUES(`role`);