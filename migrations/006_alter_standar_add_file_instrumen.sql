-- Migration: 006_alter_standar_add_file_instrumen.sql
-- Deskripsi: Menambahkan kolom file_instrumen ke tabel standar untuk upload file referensi
--
-- ## UP: Menambah kolom file_instrumen
-- ## DOWN: Menghapus kolom file_instrumen

-- UP
ALTER TABLE `standar`
  ADD COLUMN `file_instrumen` VARCHAR(255) NULL AFTER `deskripsi`;

-- DOWN
-- ALTER TABLE `standar`
--   DROP COLUMN `file_instrumen`;