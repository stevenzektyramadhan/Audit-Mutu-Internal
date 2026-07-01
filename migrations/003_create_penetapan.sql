-- Migration: 003_create_penetapan.sql
-- Deskripsi: Membuat tabel penetapan untuk manajemen penetapan standar per kategori
--
-- ## UP: Membuat tabel penetapan
-- ## DOWN: Menghapus tabel penetapan

-- UP
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

-- DOWN
-- DROP TABLE IF EXISTS `penetapan`;