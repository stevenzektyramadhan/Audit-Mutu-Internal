# Sistem Penjaminan Mutu Internal (SPMI) Perguruan Tinggi

Aplikasi CodeIgniter 3 ini mendukung alur kerja SPMI sebagai satu-satunya alur aktif. AMI legacy tetap dipertahankan sebagai arsip baca-saja melalui URL `lpmpi/legacy-ami-archive`; bukan menu sidebar atau alur operasional aktif.

## Teknologi

- CodeIgniter 3
- PHP 7.4+ (diuji dengan PHP 8.3.30)
- MySQL/MariaDB
- Bootstrap 4 dan Font Awesome 5
- Session, Query Builder, Form Validation, dan CSRF bawaan CodeIgniter

## Struktur Arsitektur

```text
Controller -> Service -> Model -> Database
```

Business logic, ownership check, dan transaksi database ditempatkan pada service. Controller menangani request/response, sedangkan model hanya mengakses database.

## Alur Kerja SPMI Aktif

1. Manajemen membuat versi standar dan indikator SPMI.
2. Manajemen membuat siklus draft dan penugasan dari versi sumber serta standar yang dipilih.
3. Siklus dikonfigurasi agar auditee dapat mengerjakan penugasan.
4. Auditee mengisi realisasi dan bukti sesuai kebijakan bukti yang tersnapshot, lalu mengirimkannya.
5. Auditor menilai, dapat mengembalikan untuk revisi, lalu memfinalisasi assessment.
6. Setelah seluruh penugasan selesai dinilai, manajemen membuat laporan immutable per versi, auditor, dan auditee.
7. Snapshot laporan digunakan dalam RTM dan tindak lanjut RTM.

### Status dan Transisi

| Entitas | Status dan transisi |
|---|---|
| Versi standar | `draft -> review -> approved -> active -> retired`; `review -> draft` juga didukung. |
| Siklus audit | `draft -> configured -> closed`; `configured -> draft` juga didukung. |
| Submission auditee | `draft`, `submitted`, `returned_for_revision`, `resubmitted`. |
| Assessment auditor | `draft -> finalized`. |
| RTM | `draft -> resolved`; RTM resolved bersifat baca-saja. |

Laporan dibuat hanya ketika seluruh penugasan untuk kombinasi siklus, versi, auditor, dan auditee telah memiliki assessment finalized. Laporan dan itemnya adalah snapshot immutable; laporan historis tetap dapat dibaca.

### Kebijakan Bukti per Indikator

Setiap indikator memakai salah satu nilai `none`, `file`, `url`, `either`, atau `both`. Kebijakan dan petunjuk bukti disalin ke snapshot item penugasan baru, sehingga perubahan indikator berikutnya tidak mengubah penugasan yang sudah ada. File bukti disimpan private dan endpoint unduh memeriksa role serta ownership. Bukti URL hanya divalidasi sebagai URL HTTP/HTTPS; aplikasi tidak mengambil konten URL tersebut.

### Dokumen PPEPP SPMI

Dokumen PPEPP adalah arsip manajemen-only untuk tahap `Penetapan`, `Pelaksanaan`, `Pengendalian`, dan `Peningkatan`. Tidak ada tahap upload `Evaluasi`, tidak menjadi workspace auditor/auditee, dan aksesnya berada di menu manajemen `Dokumen PPEPP`. File PPEPP disimpan pada kategori private `ppepp_documents` di bawah `APP_PRIVATE_STORAGE_PATH`, tanpa fallback ke `public/uploads`; dokumen URL hanya diarahkan ke URL HTTP/HTTPS yang tersimpan. File upload dibatasi aplikasi tepat 10 MiB, sedangkan Docker menaikkan kapasitas request PHP ke `upload_max_filesize = 11M` dan `post_max_size = 12M` agar overhead multipart tidak menggagalkan file 10 MiB yang valid.

## Peran dan Menu Aktif

| Peran | Redirect login | Menu sidebar terlihat |
|---|---|---|
| `super_admin` | `lpmpi/spmi-dashboard` | Dashboard SPMI; Manajemen Pengguna; Struktur Organisasi; Standar SPMI; Siklus & Penugasan SPMI; Dokumen PPEPP; Laporan SPMI; RTM SPMI; Tindak Lanjut RTM; Akun Saya; Profil Lembaga |
| `admin_lpmpi` | `lpmpi/spmi-dashboard` | Dashboard SPMI; Manajemen Pengguna; Struktur Organisasi; Standar SPMI; Siklus & Penugasan SPMI; Dokumen PPEPP; Laporan SPMI; RTM SPMI; Tindak Lanjut RTM; Akun Saya; Profil Lembaga |
| `auditor` | `auditor/spmi-dashboard` | Dashboard SPMI; Penilaian SPMI; Akun Saya |
| `auditee` | `auditee/spmi-dashboard` | Dashboard SPMI; Workspace SPMI; Akun Saya |

## Peta Kode SPMI

| Tanggung jawab | Lokasi |
|---|---|
| Route | `application/config/routes.php` |
| Controller manajemen | `application/controllers/lpmpi/Spmi_*.php` |
| Controller auditee, auditor, dan dashboard | `application/controllers/Spmi_*.php` |
| Service | `application/services/Spmi_*.php` |
| Model | `application/models/Spmi_*.php` |
| Dokumen PPEPP | `application/controllers/lpmpi/Spmi_ppepp_documents.php`, `application/services/Spmi_ppepp_documents_service.php`, `application/models/Spmi_ppepp_documents_model.php`, `application/views/lpmpi/spmi_ppepp_documents` |
| View | `application/views/lpmpi/spmi_*`, `application/views/spmi_auditee_workspace`, `application/views/spmi_auditor_workspace` |
| Regression guard | `tests/*_regression.php` |
| Upgrade manual | `migrations/` |

## Setup dan Deployment

Docker bersifat opsional. Pilih satu mode sesuai lingkungan. Compose app sudah set `CI_ENV=development`, jadi jangan tambah mode lain yang mengubah perilaku itu.

| Mode | Pilih jika | Menjalankan aplikasi | Database |
|---|---|---|---|
| Apache/PHP dan MySQL lokal | Menggunakan XAMPP atau Laragon tanpa Docker | Apache lokal | MySQL/MariaDB lokal |
| Docker penuh | Ingin aplikasi dan MySQL terisolasi | Container `app` | Container `db` internal |
| Apache/PHP lokal dan MySQL Docker | Belum didukung oleh konfigurasi aplikasi saat ini | - | Gunakan MySQL lokal atau Docker penuh |
| Produksi non-Docker | Server produksi dikelola langsung oleh tim deployment | Apache atau PHP-FPM | Database produksi terpisah |

### Prasyarat Lokal

Gunakan PHP 7.4+ dan aktifkan ekstensi `mysqli`, `mbstring`, `xml`, `zip`, `gd`, `curl`, dan `fileinfo`. Dari root proyek, pasang dependency PHP sebelum menjalankan aplikasi lokal:

```bash
composer install
```

Pada Windows, jalankan perintah tersebut dari PowerShell atau Command Prompt yang memiliki `php` dan `composer` pada `PATH`. Docker penuh tidak memerlukan Composer di host karena image memasang dependency saat build.

`index.php` mewajibkan `CI_ENV`. Untuk Apache lokal, set `CI_ENV=development` pada konfigurasi VirtualHost atau `.htaccess` yang diizinkan, misalnya:

```apache
SetEnv CI_ENV development
SetEnv APP_BASE_URL http://localhost/AMI/
```

Ganti `APP_BASE_URL` dengan URL proyek yang dipakai, selalu dengan garis miring penutup.

### Apache/PHP dan MySQL Lokal, XAMPP atau Laragon

1. Jalankan Apache dan MySQL/MariaDB, lalu pastikan `CI_ENV=development` pada konfigurasi Apache.
2. Untuk database baru, import `database_schema.sql`.

   ```bash
   mysql -u root < database_schema.sql
   ```

   `database_dummy.sql` adalah seed AMI legacy, bukan kebutuhan alur SPMI dan tidak membuat akun SPMI. Jangan import file ini untuk onboarding SPMI.
3. Set variabel database. Nilai default lokal adalah `DB_HOST=localhost`, `DB_USERNAME=root`, `DB_PASSWORD=` kosong, dan `DB_DATABASE=ami`.
4. Buka URL yang memakai `/index.php`, misalnya `http://localhost/AMI/index.php`.

### Docker Penuh

Jalankan dari root proyek:

```bash
docker compose up -d --build
docker compose logs --tail=100 app
```

Pada volume database baru, Compose saat ini menjalankan `database_schema.sql` sebagai `01-schema.sql`, lalu `database_dummy.sql` sebagai `02-demo.sql`. File kedua tersebut hanya seed AMI legacy, tidak membuat pengguna, dan tidak diperlukan oleh alur SPMI. Buka login di `http://127.0.0.1:8081/index.php/auth/login`.

Gunakan hanya satu host selama sesi, yaitu `127.0.0.1:8081`. Jangan berganti ke `localhost:8081`, karena cookie sesi dan CSRF tersimpan per host berbeda. Compose tidak memublikasikan port MySQL ke host. Sesi, upload, database, dan private storage berada dalam named volume; `docker compose down` mempertahankannya, sedangkan `docker compose down -v` menghapusnya. Image Docker menyalin `docker/php-upload.ini` ke `$PHP_INI_DIR/conf.d/99-upload.ini`; nilai `upload_max_filesize = 11M` dan `post_max_size = 12M` hanya memberi ruang overhead request PHP, bukan menaikkan batas aplikasi PPEPP yang tetap tepat 10 MiB.

### Apache/PHP Lokal dan MySQL Docker

Mode ini **belum didukung**. `compose.yaml` sengaja tidak membuka port MySQL ke host dan konfigurasi CodeIgniter belum memiliki `DB_PORT` terpisah. Gunakan Apache/PHP dengan MySQL lokal atau Docker penuh.

## Deployment Produksi Non-Docker

Gunakan PHP 7.4+ dengan ekstensi `mysqli`, `mbstring`, `xml`, `zip`, `gd`, `curl`, dan `fileinfo`, lalu pasang dependency dengan `composer install --no-dev --prefer-dist --no-interaction`. `composer.lock` tersedia dan terlacak Git; gunakan lock file tersebut agar dependency produksi reproducible. Document root harus menunjuk ke root aplikasi yang berisi `index.php`; jangan publikasikan private storage atau log melalui web server.

Siapkan direktori private storage dan log, beri hak baca/tulis hanya kepada user PHP-FPM/Apache, lalu set environment berikut:

```text
CI_ENV=production
APP_BASE_URL=https://ami.example.ac.id/
APP_ENCRYPTION_KEY=<secret acak deployment>
APP_COOKIE_SECURE=true
APP_LOG_THRESHOLD=1
APP_LOG_PATH=/srv/ami/logs
APP_PRIVATE_STORAGE_PATH=/srv/ami/private
SPMI_EVIDENCE_STORAGE_BACKEND=local
GOOGLE_DRIVE_AUTH_MODE=service_account
GOOGLE_DRIVE_EVIDENCE_FOLDER_ID=
GOOGLE_DRIVE_SERVICE_ACCOUNT_JSON_PATH=
GOOGLE_DRIVE_OAUTH_CLIENT_SECRET_JSON_PATH=
GOOGLE_DRIVE_OAUTH_REFRESH_TOKEN_JSON_PATH=
BREVO_API_KEY=<secret Brevo>
MAIL_FROM_ADDRESS=<verified sender address>
MAIL_FROM_NAME=<sender display name, opsional>
DB_HOST=<host database>
DB_USERNAME=<user aplikasi>
DB_PASSWORD=<secret database>
DB_DATABASE=ami
```

`APP_BASE_URL` wajib HTTPS. `APP_LOG_PATH` dan `APP_PRIVATE_STORAGE_PATH` wajib berada di luar document root. Bukti SPMI baru memakai backend lokal secara default; `SPMI_EVIDENCE_STORAGE_BACKEND` hanya menerima `local` atau `google_drive`, dan nilai lain kembali aman ke `local`. Dokumen PPEPP selalu memakai private storage lokal kategori `ppepp_documents`, tanpa URL publik atau fallback public uploads untuk file lokalnya. Produksi hanya mendukung `service_account`. File kredensial Drive wajib di luar `FCPATH`/document root dan konfigurasi Drive yang tidak lengkap gagal tertutup. Jangan commit secret. Reset password hanya aktif bila `BREVO_API_KEY` dan `MAIL_FROM_ADDRESS` tersedia; pengiriman memakai Brevo HTTPS API tanpa fallback SMTP, `mail`, atau `sendmail`.

### Handover Google Shared Drive Bukti SPMI

Berlaku hanya untuk bukti SPMI auditee dan auditor baru; file lama, bukti AMI legacy, dan bukti lokal yang sudah ada tetap lokal. Akses tetap lewat endpoint aplikasi dengan pemeriksaan role dan ownership, tanpa URL publik, ID Drive pada UI, atau permission publik di Google Drive. Sebelum cutover, siapkan Shared Drive, Drive API, service account, akses folder bukti, dan minimal dua administrator pemulihan manusia. Simpan folder ID serta JSON service account di secret manager atau path server eksternal di luar repository dan document root. Ambil backup dulu lalu jalankan migration `033_add_spmi_drive_evidence_metadata.sql` satu kali; backup harus mencakup database serta `APP_PRIVATE_STORAGE_PATH`.

Jangan ubah `SPMI_EVIDENCE_STORAGE_BACKEND=local` sampai Shared Drive, service account, credential path, backup, dan rollback siap. Untuk cutover, gunakan `SPMI_EVIDENCE_STORAGE_BACKEND=google_drive` dan `GOOGLE_DRIVE_AUTH_MODE=service_account`, lalu lakukan uji upload/download/delete auditee dan auditor termasuk percobaan non-owner. Retry `spmi_drive_trash_outbox` masih manual: operator menangani row pending/retrying, menjalankan trash dengan service account, lalu memperbarui status, attempt, dan error. root `compose.yaml` tidak boleh memuat secret Drive production.

Untuk melaporkan metadata Drive historis, DBA boleh menjalankan preflight read-only berikut setelah backup. Query ini hanya `SELECT`; tidak melakukan migration, update, delete, operasi file, atau trash action.

```sql
SELECT 'spmi_auditee_evidence' AS source_table, COUNT(*) AS total_rows FROM spmi_auditee_evidence
UNION ALL
SELECT 'spmi_auditor_assessment_evidence' AS source_table, COUNT(*) AS total_rows FROM spmi_auditor_assessment_evidence
UNION ALL
SELECT 'spmi_drive_trash_outbox' AS source_table, COUNT(*) AS total_rows FROM spmi_drive_trash_outbox;
```

Migration `033_add_spmi_drive_evidence_metadata.sql` juga tidak dapat membuktikan bukti yang dibuat sebelum metadata Drive tersedia.

### Google Drive untuk Development Lokal

Gunakan OAuth hanya untuk development pribadi dengan folder My Drive sendiri. Aktifkan Google Drive API, buat OAuth client tipe Desktop application, lalu set `SPMI_EVIDENCE_STORAGE_BACKEND=google_drive`. Mode ini dilarang di production; production tetap memakai `service_account` dan tanpa public Drive links.

Simpan client secret dan refresh token di luar repository serta document root, mount read-only, lalu bootstrap sekali:

```bash
php scripts/google_drive_oauth_bootstrap.php /absolute/client.json /absolute/refresh-token.json
```

### Panduan Uji Lokal Reset Password

1. Pull branch, pasang dependency, dan rebuild Compose bila environment berubah.
2. Untuk database lokal yang sudah ada, backup lalu jalankan migration `032_create_password_reset_tokens.sql` dan `033_add_spmi_drive_evidence_metadata.sql` jika belum diterapkan. Untuk database baru, import `database_schema.sql` dan buat user uji terkontrol.
3. Set `CI_ENV=development`, `APP_BASE_URL`, `BREVO_API_KEY`, dan `MAIL_FROM_ADDRESS` hanya di konfigurasi lokal yang diabaikan Git.
4. Pastikan sender Brevo sudah diverifikasi, buat user uji, gunakan alur Lupa Password, lalu login ulang dengan password baru.
5. Jalankan `php tests/password_reset_regression.php`, `php tests/auth_login_regression.php`, dan `php tests/account_settings_regression.php`.

### Database dan Upgrade Manual

`database_schema.sql` adalah bootstrap schema untuk database baru dan telah mencakup parity migration `001-039`. Jangan menjalankan migration individual setelah import schema baru. `database_dummy.sql` bukan data SPMI dan tidak dipakai untuk produksi. Parity fresh-vs-upgrade adalah kewajiban operasional: database baru memakai schema bootstrap, sedangkan database existing harus di-backup lalu menjalankan migration manual yang belum ada; README ini tidak menjadi bukti runtime parity.

#### Akun Administrator Pertama

Masukkan akun administrator pertama melalui proses deployment terkontrol. Contoh SQL hanya memakai placeholder:

```sql
INSERT INTO users (nama, email, password, role)
VALUES (
  '<administrator name>',
  '<administrator email>',
  '<password_hash() output>',
  'super_admin'
);
```

Kolom `password` wajib berisi output PHP `password_hash()` dan tidak boleh berisi password plaintext.

#### Setelah pull perubahan database

Untuk database yang sudah ada, backup terlebih dahulu lalu jalankan migration baru satu kali secara berurutan. Jangan memakai `--force`, jangan menonaktifkan foreign key checks, hentikan pada error pertama, dan jangan menjalankan blok `DOWN` historis.

#### Database baru

Import `database_schema.sql`; jangan lanjutkan dengan migration `001` sampai `039` karena bootstrap schema sudah memuat struktur yang dibutuhkan.

#### Database lama yang perlu di-upgrade

Backup data, triggers, routines, events, dan `APP_PRIVATE_STORAGE_PATH`. Jalankan migration `012` sampai `039` secara numerik, satu file tiap langkah. Jangan jalankan `001` sampai `011` pada database legacy lama.

1. `012_create_organization_structure.sql`
2. `013_create_spmi_versioned_standards.sql`
3. `014_enforce_spmi_version_invariants.sql`
4. `015_create_spmi_indicators.sql`
5. `016_create_spmi_instrument_packages.sql`
6. `017_create_spmi_audit_cycles.sql`
7. `018_create_spmi_auditee_workspace.sql`
8. `019_create_spmi_auditor_workspace.sql`
9. `020_create_spmi_reports.sql`
10. `021_create_spmi_rtm_meetings.sql`
11. `022_create_spmi_rtm_follow_ups.sql`
12. `023_create_legacy_ami_archive.sql`
13. `024_create_audit_logs.sql`
14. `025_create_spmi_m17_schema_foundation.sql`
15. `026_add_assignment_item_evidence_policy.sql`
16. `027_add_revision_lifecycle_schema_correction.sql`
17. `028_add_versioned_auditor_assessments.sql`
18. `029_add_spmi_auditor_assessment_evidence.sql`
19. `030_add_spmi_auditor_assessment_finding_details.sql`
20. `031_add_spmi_audit_cycle_academic_period.sql`
21. `032_create_password_reset_tokens.sql`
22. `033_add_spmi_drive_evidence_metadata.sql`
23. `034_retire_spmi_instruments.sql`
24. `035_add_indicator_evidence_policy.sql`
25. `036_add_users_import_support.sql`
26. `037_add_spmi_version_report_scope.sql`
27. `038_create_spmi_ppepp_documents.sql`
28. `039_create_upload_size_settings.sql`

Migration `037_add_spmi_version_report_scope.sql` bersifat aditif dan idempotent: menambahkan scope serta identitas laporan per versi dan identitas standar pada item laporan, dengan unique tuple untuk laporan versi. Laporan standar historis tetap terbaca dan tidak ditulis ulang.

Migration `038_create_spmi_ppepp_documents.sql` bersifat aditif: menambahkan tabel metadata arsip dokumen PPEPP manajemen-only. File fisik PPEPP tetap berada di private storage kategori `ppepp_documents`; backup upgrade harus mencakup database dan `APP_PRIVATE_STORAGE_PATH`.

Migration `039_create_upload_size_settings.sql` bersifat aditif: menambahkan pengaturan batas upload per kategori. Halaman `Pengaturan Upload` hanya tersedia untuk `super_admin` dan `admin_lpmpi`. Nilai awalnya adalah bukti SPMI 5 MiB, dokumen PPEPP 10 MiB, foto profil akun 2 MiB, import spreadsheet 2 MiB, PDF sumber SPMI 5 MiB, dan logo lembaga 4 MiB. Pengaturan berlaku pada uploader aktif terkait; validasi tipe file, private storage, ownership, serta alur Google Drive tidak berubah. Upload AMI legacy tidak termasuk cakupan ini, dan penyimpanan logo lembaga yang kompatibel dengan legacy tetap berada di lokasi semula.

Batas aplikasi maksimum 10 MiB. Nilai pengaturan tidak dapat melampaui batas efektif PHP: `upload_max_filesize` dan `post_max_size` dikurangi cadangan 1 MiB untuk multipart. Operator deployment tetap harus mengatur PHP/web server dengan ruang di atas batas file terbesar; halaman aplikasi tidak dapat menaikkan batas PHP yang sudah menolak request sebelum CodeIgniter berjalan.

CodeIgniter migrations tetap nonaktif. Direktori `migrations/` berisi raw SQL manual yang dijalankan tim deployment setelah backup. Setelah upgrade, lakukan smoke test login, upload/download sesuai role, import, dan laporan sebelum membuka traffic.

## Keamanan dan Batasan Operasional

- Route memakai role guard; seluruh mutasi wajib POST dengan CSRF aktif.
- Service menjalankan ownership check, business rule, dan transaksi yang diperlukan.
- Output dinamis di-escape dengan `html_escape()`.
- Bukti disimpan private dan unduhan memvalidasi role serta ownership.
- Audit log mencatat login, logout, dan percobaan mutasi POST; web server produksi harus menolak akses ke `application/`, `system/`, `.git/`, `.multibrain/`, log, dan private storage.
- SPMI adalah satu-satunya workflow aktif yang direpresentasikan oleh login redirect dan sidebar. Artefak AMI legacy serta route `lpmpi/legacy-ami-archive` tetap di luar lingkup operasional README ini.

## Batasan Saat Ini

Export PDF, email/notifikasi workflow, MFA, rate limit login, dan approval bertingkat belum tersedia. Terapkan MFA atau rate limit akun istimewa pada identity layer atau reverse proxy hingga tersedia di aplikasi.

## Post-pull Check

```bash
php tests/auth_login_regression.php
php tests/sidebar_navigation_regression.php
php tests/spmi_audits_regression.php
php tests/spmi_auditee_workspace_regression.php
php tests/spmi_auditor_workspace_regression.php
php tests/spmi_reports_regression.php
php tests/spmi_ppepp_documents_regression.php
php tests/m17_schema_regression.php
php tests/hardening_regression.php
```

Untuk Docker, jalankan `docker compose config --quiet` dan `curl -i http://127.0.0.1:8081/index.php/auth/login`. Untuk Apache/PHP lokal, jalankan `curl -i "${APP_BASE_URL}index.php/auth/login"` dengan `APP_BASE_URL` yang berakhiran `/`.

## Dokumentasi Terkait

- `AGENTS.md`
- `docs/plan/rencana-evidence-policy-dan-import-akun.md`
- `docs/plan/m17-spmi-workspace-parity-master-plan.md`
- `docs/product/spmi-workspace-parity-contract.md`
- `.multibrain/session.md`
- `.multibrain/indexes/ami-workflow.md`
