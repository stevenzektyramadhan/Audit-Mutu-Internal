-- Migration: 004_alter_tugas_audit_add_periode.sql
-- Deskripsi: Menambahkan kolom periode_id ke tabel tugas_audit
--
-- ## UP: Menambah kolom periode_id (FK ke periode_audit.id)
-- ## DOWN: Menghapus kolom periode_id

-- UP
ALTER TABLE `tugas_audit`
  ADD COLUMN `periode_id` INT NULL AFTER `standar_id`,
  ADD CONSTRAINT `fk_tugas_periode`
    FOREIGN KEY (`periode_id`) REFERENCES `periode_audit` (`id`) ON DELETE CASCADE;

-- DOWN
-- ALTER TABLE `tugas_audit`
--   DROP FOREIGN KEY `fk_tugas_periode`,
--   DROP COLUMN `periode_id`;