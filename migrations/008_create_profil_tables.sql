-- Migration: 008_create_profil_tables.sql
-- Deskripsi: Menambahkan tabel profil lembaga, program studi, dan statistik mahasiswa.

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
