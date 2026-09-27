CREATE TABLE IF NOT EXISTS `staf_prodi` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `id_akun` INT NOT NULL,
    `id_prodi` INT NOT NULL,
    `jabatan` VARCHAR(100) NULL,
    `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_staf_prodi_akun_prodi` (`id_akun`, `id_prodi`),
    KEY `idx_staf_prodi_prodi_status` (`id_prodi`, `status`),
    CONSTRAINT `fk_staf_prodi_akun` FOREIGN KEY (`id_akun`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT `fk_staf_prodi_prodi` FOREIGN KEY (`id_prodi`) REFERENCES `profil_prodi` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
