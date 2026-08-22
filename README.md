# Web Audit Mutu Internal (AMI) Perguruan Tinggi

Aplikasi MVP Audit Mutu Internal berbasis CodeIgniter 3 untuk mengelola pengguna, standar, pertanyaan, penugasan audit, bukti auditee, dan penilaian auditor.

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

## Setup dan Deployment

Docker bersifat opsional. Pilih satu mode sesuai lingkungan. Compose app sudah set `CI_ENV=development`, jadi jangan tambah mode lain yang mengubah perilaku itu.

| Mode | Pilih jika | Menjalankan aplikasi | Database |
|---|---|---|---|
| Apache/PHP dan MySQL lokal | Menggunakan XAMPP atau Laragon tanpa Docker | Apache lokal | MySQL/MariaDB lokal |
| Docker penuh | Ingin aplikasi dan MySQL terisolasi | Container `app` | Container `db` internal |
| Apache/PHP lokal dan MySQL Docker | Belum didukung oleh konfigurasi aplikasi saat ini | — | Gunakan MySQL lokal atau Docker penuh |
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

Ganti `APP_BASE_URL` dengan URL proyek yang dipakai, selalu dengan garis miring penutup. Pada development, `APP_BASE_URL` dapat dikosongkan dan CodeIgniter mendeteksi URL dari request, tetapi menetapkannya menghindari URL yang salah saat memakai virtual host atau subdirektori.

### Apache/PHP dan MySQL Lokal, XAMPP atau Laragon

Pilih mode ini bila Apache dan MySQL/MariaDB sudah tersedia di XAMPP atau Laragon. Contoh lokasi proyek Windows adalah `C:\laragon\www\AMI` atau `C:\xampp\htdocs\AMI`.

1. Jalankan Apache dan MySQL/MariaDB dari Laragon atau XAMPP, lalu pastikan `CI_ENV=development` pada konfigurasi Apache seperti bagian prasyarat.
2. Untuk database baru, import `database_schema.sql`. Ini membuat struktur database `ami`, bukan akun demo.

   ```powershell
   mysql -u root -e "source C:/laragon/www/AMI/database_schema.sql"
   ```

   Shell Unix dapat memakai:

   ```bash
   mysql -u root < database_schema.sql
   ```

3. Opsional, import seed setelah schema bila memerlukan standar, pertanyaan, tugas, dan jawaban contoh.

   ```powershell
   mysql -u root -e "source C:/laragon/www/AMI/database_dummy.sql"
   ```

   ```bash
   mysql -u root < database_dummy.sql
   ```

   `database_dummy.sql` tidak membuat pengguna demo. Buat pengguna melalui proses aplikasi atau masukkan data pengguna secara terkontrol sebelum mengharapkan tugas seed terhubung ke auditor dan auditee.
4. Set variabel database pada Apache atau PHP. Nilai default konfigurasi lokal adalah `DB_HOST=localhost`, `DB_USERNAME=root`, `DB_PASSWORD=` kosong, dan `DB_DATABASE=ami`. Jika instalasi MySQL memakai kredensial lain, set nilai yang sesuai, misalnya:

   ```apache
   SetEnv DB_HOST localhost
   SetEnv DB_USERNAME root
   SetEnv DB_PASSWORD ""
   SetEnv DB_DATABASE ami
   ```

5. Buka URL yang memakai `/index.php`, misalnya `http://localhost/AMI/index.php`. Semua URL aplikasi tetap memakai `/index.php` karena konfigurasi saat ini memang begitu.

### Docker Penuh

Pilih mode ini bila Docker Desktop dan Compose v2 tersedia dan aplikasi maupun MySQL harus berjalan dalam container. Dari root proyek, jalankan:

```bash
docker compose up -d --build
```

Periksa status dengan:

```bash
docker compose ps
```

Lihat log app dengan:

```bash
docker compose logs --tail=100 app
```

Pada volume database baru, MySQL menjalankan `database_schema.sql` sebagai `01-schema.sql`, lalu `database_dummy.sql` sebagai `02-demo.sql`. Seed tidak membuat pengguna demo. Buka login di `http://127.0.0.1:8081/index.php/auth/login`.

Gunakan hanya satu host selama sesi, yaitu `127.0.0.1:8081`. Jangan berganti ke `localhost:8081`, karena cookie sesi dan CSRF tersimpan per host yang berbeda. Jika sudah berganti dan login ditolak, tutup tab aplikasi lalu hapus site data untuk kedua host sebelum membuka `127.0.0.1` lagi.

Compose sengaja tidak memublikasikan port MySQL ke host. Sesi, upload, dan data MySQL tersimpan dalam named volume. `docker compose down` menghentikan container dan mempertahankan named volume.

```bash
docker compose down
```

`docker compose down -v` menghapus named volume, jadi database lokal, sesi, dan upload hilang.

```bash
docker compose down -v
```

### Apache/PHP Lokal dan MySQL Docker

Mode ini **belum didukung**. `compose.yaml` sengaja tidak membuka port MySQL ke host, dan konfigurasi CodeIgniter saat ini belum memiliki `DB_PORT` terpisah. Menulis `127.0.0.1:3307` pada `DB_HOST` tidak membuat driver `mysqli` memakai port tersebut.

Untuk development, gunakan salah satu mode yang didukung: Apache/PHP dengan MySQL lokal atau Docker penuh. Jika mode hybrid diperlukan di masa depan, tambahkan konfigurasi `DB_PORT` pada aplikasi terlebih dahulu, kemudian buat override Compose lokal di `test-data/` (direktori yang diabaikan Git), bukan di root proyek.

## Deployment Produksi Non-Docker

Pilih mode ini untuk server produksi tanpa container. Deployment dan secret dikelola oleh tim deployment. Gunakan PHP 7.4+ dengan ekstensi `mysqli`, `mbstring`, `xml`, `zip`, `gd`, `curl`, dan `fileinfo`, lalu pasang dependency dengan `composer install --no-dev --prefer-dist --no-interaction`. Repository belum memiliki `composer.lock`; akibatnya `composer install` dapat memilih versi dependency berbeda pada release berikutnya. Buat, review, dan commit lock file sebelum release produksi agar dependency reproducible. Karena `.gitignore` saat ini mengabaikan file itu, gunakan `git add -f composer.lock` saat menambahkan lock file pertama. Document root harus menunjuk ke root aplikasi yang berisi `index.php`; jangan publikasikan direktori private storage atau log melalui web server.

Siapkan direktori private storage dan log terlebih dahulu, beri hak baca/tulis hanya kepada user PHP-FPM/Apache, lalu set seluruh environment berikut:

```text
CI_ENV=production
APP_BASE_URL=https://ami.example.ac.id/
APP_ENCRYPTION_KEY=<secret acak deployment>
APP_COOKIE_SECURE=true
APP_LOG_THRESHOLD=1
APP_LOG_PATH=/srv/ami/logs
APP_PRIVATE_STORAGE_PATH=/srv/ami/private
DB_HOST=<host database>
DB_USERNAME=<user aplikasi>
DB_PASSWORD=<secret database>
DB_DATABASE=ami
```

`APP_BASE_URL` wajib HTTPS. `APP_LOG_PATH` dan `APP_PRIVATE_STORAGE_PATH` wajib sudah ada, writable, dan berada di luar document root. Aplikasi produksi gagal tertutup jika setting wajib tersebut hilang atau tidak aman. Jangan menyimpan fallback secret di source. Rotasi `APP_ENCRYPTION_KEY` jika key lama pernah digunakan di luar lingkungan tepercaya.

File instrumen, lampiran penetapan, bukti auditor, dan import Excel sementara disimpan di private storage dan hanya diunduh melalui endpoint dengan pemeriksaan role/ownership. Logo profil tetap publik di `uploads/profil`. Record lama yang hanya berisi nama file tetap dibaca dari `uploads/<kategori>`; jangan hapus file lama sebelum proses pemindahan dan verifikasi selesai.

### Database dan Upgrade Manual

`database_schema.sql` adalah bootstrap schema-only untuk database baru. Jangan import `database_dummy.sql` atau memakai akun demo di produksi. Buat administrator awal melalui proses terkontrol tim deployment.

#### Setelah pull perubahan database

Periksa apakah pull membawa file baru di `migrations/`. Untuk database yang sudah ada, backup terlebih dahulu lalu jalankan migration baru satu kali secara berurutan. Setelah upgrade, jalankan pemeriksaan terarah berikut dari root proyek:

```bash
php tests/sidebar_navigation_regression.php
php tests/spmi_rtm_regression.php
php tests/spmi_audits_regression.php
php tests/spmi_ui_consistency_regression.php
```

#### Database baru

Untuk database baru, import `database_schema.sql` dulu. Jangan lanjutkan dengan migration `001` sampai `031` pada database baru, karena schema bootstrap sudah memuat struktur awal yang dibutuhkan.

#### Database lama, legacy, belum punya table organisasi, capability, atau SPMI

Ambil backup penuh dulu, termasuk data, triggers, routines, events, dan storage private plus upload yang terkait. Setelah itu, pilih database yang memang ingin di-upgrade, lalu jalankan hanya migration `012` sampai `031` secara numerik, satu file tiap langkah, dalam urutan naik. Jangan jalankan `001` sampai `011` pada database legacy lama ini.

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

Jalankan satu file tiap langkah, satu per satu, memakai klien MySQL yang dipilih tim ke database yang memang dituju. Jangan membatch file. Jangan menambahkan kredensial.

Jangan pakai `--force`. Jangan matikan foreign key checks. Hentikan di error pertama. Jangan jalankan blok `DOWN` historis.

Catatan penting, migration `014` berhenti bila lebih dari satu active version ditemukan. Migration `028` membuat index composite `(assignment_id, source_submission_version)` dulu, baru menghapus unique index lama, supaya aman untuk FK. Migration `029` menambahkan bukti assessment auditor dan snapshot metadata laporan secara aditif. Migration `030` menambahkan detail temuan dan snapshot laporan secara aditif. Migration `031` menambahkan `academic_year` dan `semester` nullable pada `spmi_audit_cycles` secara aditif dan idempotent. Migration ini tidak melakukan backfill; isi periode akademik untuk siklus draft melalui menu Siklus & Penugasan SPMI setelah upgrade.

CodeIgniter migrations tetap nonaktif. Direktori root `migrations/` berisi raw SQL yang dijalankan manual oleh tim deployment setelah backup database. Untuk database yang sudah masuk jalur legacy di atas, ikuti nomor migration yang sudah ditetapkan, satu file tiap langkah, tanpa melewati urutan atau menjalankan blok `DOWN` historis otomatis. Backup database dan `APP_PRIVATE_STORAGE_PATH` sebagai satu set, uji restore, lalu lakukan smoke test login, upload/download sesuai role, import pertanyaan, dan laporan sebelum membuka traffic. Rollback aplikasi harus mempertahankan database dan file hasil backup.

`tests/fixtures/m17_07_demo_ui_seed.sql` bukan setup normal. Jangan import file itu ke database shared atau production selama hardening safety masih berlangsung.

Konfigurasi web server wajib menerapkan HTTPS dan HSTS, menolak akses ke `application/`, `system/`, `.git/`, `.multibrain/`, log, serta private storage, dan menonaktifkan directory listing. Pantau kapasitas disk serta rotasi log. Error detail hanya masuk log private; browser produksi tidak menampilkan error PHP atau debug database.

## Akun Demo

`database_schema.sql` dan `database_dummy.sql` tidak membuat akun di bawah ini. Gunakan hanya jika akun tersebut telah dibuat secara terkontrol pada database lokal.

| Role | Email | Password |
|---|---|---|
| Super Admin | `admin@ami.test` | `admin123` |
| Auditor | `auditor@ami.test` | `auditor123` |
| Auditee | `auditee@ami.test` | `auditee123` |

Password tersimpan menggunakan `password_hash()` dan diverifikasi dengan `password_verify()`.

## Fitur MVP

### Super Admin

- Dashboard statistik
- CRUD pengguna, standar, dan pertanyaan
- Membuat, melihat detail, dan menghapus tugas audit
- Melihat hasil audit

### Admin LPMPI

- Mengelola profil, periode, instrumen, penetapan, penugasan, dan laporan
- Import pertanyaan serta export laporan Excel

### Auditee

- Melihat tugas miliknya
- Mengisi jawaban singkat dan link bukti
- Melihat status, skor, dan catatan auditor

### Auditor

- Melihat tugas yang ditugaskan kepadanya
- Membuka jawaban dan link bukti
- Memberikan skor 1–4 dan catatan
- Melihat riwayat penilaian

## Alur Demo

1. Login sebagai Super Admin.
2. Tambah auditor dan auditee bila diperlukan.
3. Tambah standar serta pertanyaan.
4. Buat tugas audit.
5. Login sebagai Auditee dan kirim jawaban beserta link bukti.
6. Login sebagai Auditor dan simpan skor serta catatan.
7. Login kembali sebagai Super Admin dan buka Hasil Audit.

## Keamanan Dasar

- Role-based access melalui `Auth_guard`
- Ownership check tugas auditor dan auditee
- CSRF aktif untuk seluruh form POST, termasuk login
- Output dinamis menggunakan `html_escape()`
- Validasi input melalui Form Validation dan service
- Penghapusan data hanya melalui POST
- Transaksi database saat membuat tugas, menyimpan jawaban, dan menyimpan penilaian
- Header respons global no-store/security dan audit log append-only untuk login, logout, dan percobaan mutasi POST

## Batasan MVP

Export PDF, email, notifikasi, MFA, rate limit login, dan approval bertingkat belum disediakan. Kebijakan MFA/rate limit untuk akun istimewa harus diterapkan pada lapisan identitas atau reverse proxy sampai tersedia di aplikasi.

## Data Demo

`database_dummy.sql` aman dijalankan ulang dan menyediakan:

- 3 standar audit
- 12 pertanyaan
- tugas dengan status `belum_diisi`, `diisi`, dan `dinilai`
- jawaban, skor, serta catatan contoh

## Post-pull Check

Jalankan cek berikut setelah pull dan sebelum handoff:

### Semua mode

```bash
php tests/auth_login_regression.php
php tests/spmi_auditee_workspace_regression.php
php tests/m17_schema_regression.php
```

### Docker

```bash
docker compose config --quiet
curl -i http://127.0.0.1:8081/index.php/auth/login
```

### Apache/PHP lokal

```bash
curl -i "${APP_BASE_URL}index.php/auth/login"
```

Pakai `APP_BASE_URL` lokal yang sudah berakhiran `/`, lalu tambahkan `index.php/auth/login` satu kali.

Verifikasi browser yang terautentikasi tetap manual.
