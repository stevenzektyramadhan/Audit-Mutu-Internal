# Remediasi Migration 037 Laporan SPMI

Tanggal: 2026-09-19

## Insiden

Halaman laporan SPMI gagal dengan MySQL 1054 pada `r.source_cycle_id` dari `Spmi_reports_model::finalized_versions()`.

## Akar Masalah dan Perbaikan

Kode laporan per versi sudah memakai kolom migration 037, tetapi volume database Docker yang sudah ada belum menjalankan `migrations/037_add_spmi_version_report_scope.sql`. Pemeriksaan `INFORMATION_SCHEMA` sebelum perbaikan mengembalikan nol kolom baru pada `spmi_reports` dan `spmi_report_items`.

Backup transaksi database dibuat di `/tmp/opencode/ami-before-migration-037-20260919.sql` sebelum migration dijalankan. Migration 037 kemudian dijalankan menggunakan user `ami_local`. Setelahnya, lima kolom header laporan dan lima kolom item standar tersedia; query `finalized_versions()` yang sebelumnya gagal menghasilkan lima grup versi siap-lapor. Menjalankan migration kedua kali berhasil tanpa perubahan tambahan.

## Verifikasi

- `php tests/spmi_reports_regression.php` lulus.
- `php tests/m17_schema_regression.php` lulus.
- `php tests/spmi_rtm_regression.php` lulus.
- `php -l` lulus untuk model, service, dan controller laporan.
- Browser anonim pada `/index.php/lpmpi/spmi-reports` mengarah ke login sebagaimana kontrol akses yang diharapkan; pengujian halaman terautentikasi tidak dilakukan karena tidak ada kredensial uji.

## Handoff

Setiap database lama yang menerima kode laporan per versi wajib menjalankan migration 037 setelah backup database dan private storage. Database baru dari `database_schema.sql` sudah memuat schema 037.
