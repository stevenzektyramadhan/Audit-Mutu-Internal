-- Migration: 005_alter_jawaban_audit_new_columns.sql (FIXED - v2)
-- Deskripsi: Menambahkan kolom baru ke tabel jawaban_audit, rename beberapa kolom existing
--
-- Perubahan:
--   1. Rename `tugas_audit_id` -> `tugas_id` (drop FK dulu)
--   2. Rename `catatan` -> `temuan`
--   3. Ubah tipe `link_bukti` TEXT -> VARCHAR(500)
--   4. Tambah kolom: is_submitted, submitted_at, jenis_temuan, saran_perbaikan,
--      rencana_perbaikan, dokumen_bukti, tgl_bukti, is_nilai_submitted, nilai_submitted_at
--
-- ## UP: Menambah & merename kolom
-- ## DOWN: Mengembalikan ke struktur awal

-- UP
ALTER TABLE `jawaban_audit`
  DROP FOREIGN KEY `fk_jawaban_tugas`;

ALTER TABLE `jawaban_audit`
  CHANGE COLUMN `tugas_audit_id` `tugas_id` INT NOT NULL;

ALTER TABLE `jawaban_audit`
  ADD CONSTRAINT `fk_jawaban_tugas`
    FOREIGN KEY (`tugas_id`) REFERENCES `tugas_audit` (`id`) ON DELETE CASCADE;

ALTER TABLE `jawaban_audit`
  CHANGE COLUMN `catatan` `temuan` TEXT NULL,
  MODIFY COLUMN `link_bukti` VARCHAR(500) NULL,
  ADD COLUMN `is_submitted` TINYINT(1) DEFAULT 0 AFTER `link_bukti`,
  ADD COLUMN `submitted_at` DATETIME NULL AFTER `is_submitted`,
  ADD COLUMN `jenis_temuan` ENUM('ob','kts') NULL AFTER `temuan`,
  ADD COLUMN `saran_perbaikan` TEXT NULL AFTER `jenis_temuan`,
  ADD COLUMN `rencana_perbaikan` TEXT NULL AFTER `saran_perbaikan`,
  ADD COLUMN `dokumen_bukti` VARCHAR(255) NULL AFTER `rencana_perbaikan`,
  ADD COLUMN `tgl_bukti` DATE NULL AFTER `dokumen_bukti`,
  ADD COLUMN `is_nilai_submitted` TINYINT(1) DEFAULT 0 AFTER `tgl_bukti`,
  ADD COLUMN `nilai_submitted_at` DATETIME NULL AFTER `is_nilai_submitted`;

-- DOWN
-- ALTER TABLE `jawaban_audit`
--   DROP FOREIGN KEY `fk_jawaban_tugas`;
-- ALTER TABLE `jawaban_audit`
--   DROP COLUMN `nilai_submitted_at`,
--   DROP COLUMN `is_nilai_submitted`,
--   DROP COLUMN `tgl_bukti`,
--   DROP COLUMN `dokumen_bukti`,
--   DROP COLUMN `rencana_perbaikan`,
--   DROP COLUMN `saran_perbaikan`,
--   DROP COLUMN `jenis_temuan`,
--   DROP COLUMN `submitted_at`,
--   DROP COLUMN `is_submitted`,
--   MODIFY COLUMN `link_bukti` TEXT NULL,
--   CHANGE COLUMN `temuan` `catatan` TEXT NULL,
--   CHANGE COLUMN `tugas_id` `tugas_audit_id` INT NOT NULL;
-- ALTER TABLE `jawaban_audit`
--   ADD CONSTRAINT `fk_jawaban_tugas`
--     FOREIGN KEY (`tugas_audit_id`) REFERENCES `tugas_audit` (`id`) ON DELETE CASCADE;
