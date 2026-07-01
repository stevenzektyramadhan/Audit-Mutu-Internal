-- Migration: 007_alter_users_add_unit_columns.sql
-- Deskripsi: Menambahkan kolom nama_unit dan jenis_unit ke tabel users
--
-- ## UP: Menambah kolom nama_unit & jenis_unit
-- ## DOWN: Menghapus kolom nama_unit & jenis_unit

-- UP
ALTER TABLE `users`
  ADD COLUMN `nama_unit` VARCHAR(100) NULL AFTER `role`,
  ADD COLUMN `jenis_unit` ENUM('prodi','unit','lembaga') NULL AFTER `nama_unit`;

-- DOWN
-- ALTER TABLE `users`
--   DROP COLUMN `jenis_unit`,
--   DROP COLUMN `nama_unit`;