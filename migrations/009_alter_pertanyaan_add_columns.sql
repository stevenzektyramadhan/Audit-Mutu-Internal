-- Migration: 009_alter_pertanyaan_add_columns.sql
-- Deskripsi: Menambahkan metadata indikator dan urutan untuk import pertanyaan via Excel.
-- BACKUP SEBELUM MENJALANKAN:
-- mysqldump -u root ami pertanyaan > pertanyaan_before_task16.sql

-- UP
ALTER TABLE `pertanyaan`
  ADD COLUMN `urutan` INT NULL AFTER `standar_id`,
  ADD COLUMN `nilai_standar` TEXT NULL AFTER `isi_pertanyaan`,
  ADD COLUMN `baseline` VARCHAR(255) NULL AFTER `nilai_standar`,
  ADD COLUMN `target_2025` VARCHAR(255) NULL AFTER `baseline`,
  ADD COLUMN `target_2026` VARCHAR(255) NULL AFTER `target_2025`,
  ADD COLUMN `target_2027` VARCHAR(255) NULL AFTER `target_2026`,
  ADD COLUMN `target_2028` VARCHAR(255) NULL AFTER `target_2027`,
  ADD COLUMN `target_2029` VARCHAR(255) NULL AFTER `target_2028`,
  ADD COLUMN `target_2030` VARCHAR(255) NULL AFTER `target_2029`,
  ADD COLUMN `kategori` ENUM('IKU', 'IKT') NULL AFTER `target_2030`;

UPDATE `pertanyaan`
SET `urutan` = `id`
WHERE `urutan` IS NULL;

-- DOWN
ALTER TABLE `pertanyaan`
  DROP COLUMN `urutan`,
  DROP COLUMN `nilai_standar`,
  DROP COLUMN `baseline`,
  DROP COLUMN `target_2025`,
  DROP COLUMN `target_2026`,
  DROP COLUMN `target_2027`,
  DROP COLUMN `target_2028`,
  DROP COLUMN `target_2029`,
  DROP COLUMN `target_2030`,
  DROP COLUMN `kategori`;
