-- Migration: 001_add_admin_lpmpi_role.sql
-- Deskripsi: Menambahkan nilai 'admin_lpmpi' ke ENUM role tabel users
-- 
-- ## UP: Menambahkan role admin_lpmpi
-- ## DOWN: Mengembalikan ENUM ke nilai semula (super_admin, auditor, auditee)

-- UP
ALTER TABLE `users` 
  MODIFY COLUMN `role` ENUM('super_admin','admin_lpmpi','auditor','auditee') NOT NULL;

-- DOWN
-- ⚠️ Hanya jalankan jika tidak ada user dengan role 'admin_lpmpi'
-- ALTER TABLE `users` 
--   MODIFY COLUMN `role` ENUM('super_admin','auditor','auditee') NOT NULL;