-- Migration: 009_alter_profil_pddikti_id_lengths.sql
-- Deskripsi: Memperpanjang kolom ID terenkripsi PDDikti agar tidak terpotong.

ALTER TABLE `profil_lembaga`
  MODIFY COLUMN `id_pt_pddikti` VARCHAR(255) NULL;

ALTER TABLE `profil_prodi`
  MODIFY COLUMN `id_prodi_pddikti` VARCHAR(255) NULL;
