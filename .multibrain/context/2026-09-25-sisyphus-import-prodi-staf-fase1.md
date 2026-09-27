# Import Prodi-Staf Fase 1

## Scope

- Menambahkan impor XLSX protected untuk `profil_prodi` dan relasi `staf_prodi`: template dua sheet, preview, claim privat sekali pakai, dan confirm atomik.
- Menambahkan migration `041_create_staf_prodi.sql`, parity `database_schema.sql`, README, link minimal di Profil Lembaga, dan guard penghapusan Prodi yang memiliki relasi staf.

## Invariants

- Controller `Prodi_staf_import` mewarisi `Admin_Lpmpi_Controller`; preview, confirm, dan cancel POST-only serta semua form memakai helper CodeIgniter/CSRF.
- Preview disimpan private di `private_storage_dir('tmp')`, terikat user/token/hash/expiry 30 menit, di-rename menjadi claim saat confirm, dan dibersihkan.
- Parser membatasi dua sheet/5 kolom/1.000 baris per sheet, menolak formula, memvalidasi duplicate dan referensi Prodi parsed, serta mensyaratkan email dan NIDN/NIP menunjuk user existing yang sama.
- Confirm hanya memakai payload tersimpan, mengunci Prodi/user dalam transaksi, membuat/memperbarui Prodi row-scoped, dan mengaktifkan relasi staf; tidak membuat atau memperbarui user, tidak menyentuh `profil_mahasiswa_stats`, dan tidak mempersistenkan `fakultas` atau `jumlah_mahasiswa`.

## Verification

- Passed after independent-verification fixes: `php -l application/services/Prodi_staf_import_service.php`.
- Passed after independent-verification fixes: `php -l application/models/Prodi_staf_import_model.php`.
- Passed after independent-verification fixes: `php -l application/views/lpmpi/prodi_staf_import/preview.php`.
- Passed after independent-verification fixes: `php -l tests/prodi_staf_import_regression.php`.
- Passed after independent-verification fixes: `php tests/prodi_staf_import_regression.php`.

## Local Docker Deployment

- On 2026-09-25, applied `migrations/041_create_staf_prodi.sql` once to the existing healthy Docker MySQL database (`ami`).
- Before the schema change, created verified snapshots: `backups/ami-before-m041-prodi-staf-20260925-162716.sql` and `backups/ami-private-before-m041-prodi-staf-20260925-162706.tar`.
- Post-apply `SHOW CREATE TABLE staf_prodi` verified the composite unique key, `idx_staf_prodi_prodi_status`, account `RESTRICT`, and Prodi `CASCADE` foreign keys. The new table contains zero rows.
- Docker app/database remained up and healthy; anonymous `GET /index.php/auth` returned HTTP 200.

## Product Decision

- On 2026-09-25, the product owner removed/deferred Fase 2 auto-account creation from the Prodi-Staf roadmap. This workflow remains strict: it links only an existing account whose email and NIDN/NIP both match. The separate Import Master Data Akun workflow remains the only account-creation path.
