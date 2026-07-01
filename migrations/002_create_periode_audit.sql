-- Migration: 002_create_periode_audit.sql
-- Deskripsi: Membuat tabel periode_audit untuk manajemen periode audit
--
-- ## UP: Membuat tabel periode_audit
-- ## DOWN: Menghapus tabel periode_audit

-- UP
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

-- DOWN
-- DROP TABLE IF EXISTS `periode_audit`;