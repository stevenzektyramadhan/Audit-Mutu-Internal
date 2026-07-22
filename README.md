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

Docker bersifat opsional. Pilih satu mode sesuai lingkungan, jangan menjalankan `docker compose up` tanpa `CI_ENV` karena aplikasi akan menolak startup.

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

1. Jalankan Apache dan MySQL dari Laragon atau XAMPP, lalu pastikan `CI_ENV=development` pada konfigurasi Apache seperti bagian prasyarat.
2. Buat database dan import schema. `database_schema.sql` membuat struktur database `ami`, tetapi tidak membuat akun demo.

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

5. Buka `http://localhost/AMI/index.php`, atau URL yang sama dengan `APP_BASE_URL` yang telah ditetapkan.

### Docker Penuh

Pilih mode ini bila Docker Desktop dan Compose v2 tersedia dan aplikasi maupun MySQL harus berjalan dalam container. Dari root proyek, jalankan perintah berikut, termasuk `CI_ENV=development`:

```bash
docker compose run --build --service-ports -e CI_ENV=development app
```

Pada volume database baru, MySQL menjalankan `database_schema.sql` sebagai `01-schema.sql`, lalu `database_dummy.sql` sebagai `02-demo.sql`. Seed tidak membuat pengguna demo. Aplikasi dimulai setelah healthcheck MySQL berhasil. Buka login di `http://127.0.0.1:8081/index.php/auth/login`.

Gunakan hanya satu host selama sesi, yaitu `127.0.0.1:8081`. Jangan berganti ke `localhost:8081`, karena cookie sesi dan CSRF tersimpan per host. Jika sudah berganti dan login ditolak, tutup tab aplikasi lalu hapus site data untuk kedua host sebelum membuka URL `127.0.0.1` lagi.

Compose sengaja tidak memublikasikan port MySQL ke host. Sesi, upload, dan data MySQL tersimpan dalam named volume. Hentikan container tanpa menghapus data:

```bash
docker compose down
```

Reset berikut menghapus database, sesi login, dan seluruh upload, lalu inisialisasi schema serta seed kembali pada startup berikutnya:

```bash
docker compose down -v
docker compose run --build --service-ports -e CI_ENV=development app
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

CodeIgniter migrations tetap nonaktif. Direktori root `migrations/` berisi raw SQL yang dijalankan manual oleh tim deployment setelah backup database. Dua migration historis bernomor `009` tidak diubah; untuk deployment yang sudah berjalan, jalankan reconciliation berikut setelah memilih database:

```bash
mysql -u <user> -p <database> < migrations/010_reconcile_pertanyaan_columns.sql
```

Migration `010` idempotent dan aman dijalankan ulang. Untuk release berikutnya, jalankan raw migration baru berdasarkan nomor unik secara berurutan. Backup database dan `APP_PRIVATE_STORAGE_PATH` sebagai satu set, uji restore, lalu lakukan smoke test login, upload/download sesuai role, import pertanyaan, dan laporan sebelum membuka traffic. Rollback aplikasi harus mempertahankan database dan file hasil backup; jangan menjalankan blok `DOWN` migration historis otomatis.

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

## Batasan MVP

Export PDF, email, notifikasi, MFA, rate limit login, dan approval bertingkat belum disediakan. Kebijakan MFA/rate limit untuk akun istimewa harus diterapkan pada lapisan identitas atau reverse proxy sampai tersedia di aplikasi.

## Data Demo

`database_dummy.sql` aman dijalankan ulang dan menyediakan:

- 3 standar audit
- 12 pertanyaan
- tugas dengan status `belum_diisi`, `diisi`, dan `dinilai`
- jawaban, skor, serta catatan contoh
