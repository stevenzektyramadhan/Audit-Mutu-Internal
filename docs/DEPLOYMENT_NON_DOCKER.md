# Panduan Deployment Produksi Non-Docker (Bare-Metal / VM)

Panduan ini adalah dokumentasi resmi untuk tim infrastruktur & sysadmin dalam mendistribusikan aplikasi **Audit Mutu Internal (AMI)** pada server konvensional (Ubuntu / Debian Linux) tanpa Docker.

---

## 1. Arsitektur & Persyaratan Server

> **Batas dokumen:** `compose.yaml` hanya untuk development lokal. Jangan deploy
> kredensial Compose, named volume Docker, atau nilai `local-development-only-key`
> ke server produksi.

Seed `database_dummy.sql` bersifat lokal dan opsional. File tersebut tidak dilacak
Git dan tidak diperlukan untuk instalasi schema atau deployment produksi.

### A. Komponen Perangkat Lunak
- **Sistem Operasi**: Ubuntu 22.04 LTS / 24.04 LTS atau Debian 12
- **Web Server**: Nginx (direkomendasikan) atau Apache 2.4+
- **PHP**: PHP 8.3 dengan PHP-FPM
- **Database**: MySQL 8.0+ atau MariaDB 10.11+
- **Manajer Paket**: Composer 2.x

### B. Ekstensi PHP 8.3 yang Diperlukan
Pastikan ekstensi berikut terpasang dan aktif:
```bash
sudo apt update
sudo apt install -y php8.3-fpm php8.3-cli php8.3-mysql php8.3-mbstring \
    php8.3-xml php8.3-curl php8.3-zip php8.3-gd php8.3-intl php8.3-bcmath
```

### C. Konfigurasi `php.ini` (/etc/php/8.3/fpm/php.ini)
Sesuaikan direktif berikut:
```ini
upload_max_filesize = 20M
post_max_size = 25M
memory_limit = 256M
max_execution_time = 120
date.timezone = Asia/Jakarta
session.cookie_httponly = 1
session.use_strict_mode = 1
```
Restart PHP-FPM:
```bash
sudo systemctl restart php8.3-fpm
```

### D. Siapkan Rilis Aplikasi

Gunakan user deployment terpisah dan direktori rilis yang bukan home directory
operator. File `.env.example` adalah referensi; aplikasi tidak memuat file `.env`
secara otomatis. Nilai production harus masuk ke pool PHP-FPM pada bagian 4.

```bash
sudo install -d -o www-data -g www-data /var/www/ami
sudo -u www-data git clone <URL_REPOSITORY_REVIEWED> /var/www/ami
cd /var/www/ami
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

Gunakan tag atau commit yang sudah direview, bukan branch kerja yang berubah.
Jangan menyimpan `.env`, private key, dump database, atau backup pada document root.

---

## 2. Struktur Direktori & Hak Akses (Permissions)

Aplikasi memisahkan secara ketat antara **Webroot Publik** dan **Private Storage**:

```text
/var/www/ami/               <-- Webroot Aplikasi (FCPATH)
├── application/
├── assets/
├── migrations/
├── uploads/
│   └── profil/            <-- Default local uploads (atau diarahkan via symlink/alias ke APP_PUBLIC_UPLOADS_PATH)
└── index.php

/var/ami-storage/
├── private/               <-- APP_PRIVATE_STORAGE_PATH (DI LUAR WEBROOT)
│   ├── audit_evidence/
│   ├── bukti_auditor/
│   ├── instrumen/
│   ├── penetapan/
│   ├── ppepp_documents/
│   ├── rtm_photos/
│   ├── spmi_source/
│   ├── tmp/
│   └── user_photos/
└── public_uploads/        <-- APP_PUBLIC_UPLOADS_PATH (OPSIONAL, BISA DI LUAR WEBROOT)
    └── profil/            <-- Logo institusi (di-expose via web server alias / symlink)

/var/log/ami/               <-- LOGS APLIKASI (DI LUAR WEBROOT)
/var/www/ami/application/cache/sessions/ <-- SESSIONS
```

### Opsi Penyimpanan Public Uploads:
Aplikasi mendukung dua metode penempatan `APP_PUBLIC_UPLOADS_PATH`:
1. **Opsi Standar (Default)**: Folder `uploads/` tetap berada di dalam webroot `/var/www/ami/uploads/`. `APP_PUBLIC_UPLOADS_PATH` tidak perlu diset (otomatis default ke `FCPATH . 'uploads'`).
2. **Opsi Eksternal (Rekomendasi Skalabilitas)**: Simpan di folder penyimpanan terpisah `/var/ami-storage/public_uploads/` dengan menset environment variable:
   ```bash
   APP_PUBLIC_UPLOADS_PATH=/var/ami-storage/public_uploads
   ```
   Dan lakukan salah satu dari dua cara berikut untuk melayani `/uploads/profil/`:
    - **Metode Symlink (Paling Mudah)**:
      ```bash
      sudo mkdir -p /var/ami-storage/public_uploads/profil
      sudo chown -R www-data:www-data /var/ami-storage/public_uploads
      # Pindahkan terlebih dahulu logo lama bila ada; jangan hapus uploads secara buta.
      sudo rsync -a /var/www/ami/uploads/profil/ /var/ami-storage/public_uploads/profil/
      sudo mv /var/www/ami/uploads /var/www/ami/uploads.before-external-storage
      sudo ln -s /var/ami-storage/public_uploads /var/www/ami/uploads
      ```
   - **Metode Web Server Alias (Nginx)**:
     Jangan gunakan `location ^~ /uploads/profil/`: prefix `^~` melewati regex penolak script global. Letakkan dua blok berikut **setelah** blok penolak script uploads dan **sebelum** `location /`:
     ```nginx
      # Hanya format logo yang diizinkan aplikasi. $1 wajib dipakai bersama alias regex.
      location ~* ^/uploads/profil/([A-Za-z0-9][A-Za-z0-9._-]*\.(?:gif|jpe?g|png))$ {
          alias /var/ami-storage/public_uploads/profil/$1;
          expires 30d;
          add_header Cache-Control "public, no-transform";
      }

     # Tolak direktori, extension lain, traversal, dan file executable.
     location /uploads/profil/ {
         return 404;
     }
     ```
      Blok penolak executable `^/(uploads|assets)/.*(\.php|\.phtml|\.phar|...)$` harus tetap berada sebelum regex allowlist di atas. Jangan memakai `location ^~ /uploads/profil/` karena blok tersebut melewati regex penolak executable.

### Perintah Pembuatan & Izin Akses:
```bash
# 1. Buat folder private storage, public uploads, dan logs
sudo mkdir -p /var/ami-storage/private/{audit_evidence,bukti_auditor,instrumen,penetapan,ppepp_documents,rtm_photos,spmi_source,tmp,user_photos}
sudo mkdir -p /var/ami-storage/public_uploads/profil
sudo mkdir -p /var/log/ami
sudo mkdir -p /var/www/ami/application/cache/sessions
sudo mkdir -p /var/www/ami/uploads/profil

# 2. Atur kepemilikan ke user web server (www-data)
sudo chown -R www-data:www-data /var/ami-storage
sudo chown -R www-data:www-data /var/log/ami
sudo chown -R www-data:www-data /var/www/ami/application/cache/sessions
sudo chown -R www-data:www-data /var/www/ami/uploads

# 3. Atur permission yang aman
sudo chmod -R 0700 /var/ami-storage/private
sudo chmod -R 0775 /var/ami-storage/public_uploads
sudo chmod -R 0750 /var/log/ami
sudo chmod -R 0700 /var/www/ami/application/cache/sessions
sudo chmod 0775 /var/www/ami/uploads/profil
```

---

## Development Lokal melalui PHP CLI

Bagian ini hanya untuk development manual di host, bukan deployment produksi. Runner
`run-local-php.sh` memuat `.env` jika file tersebut ada, membuat folder upload yang
dibutuhkan, lalu menjalankan PHP built-in server pada `127.0.0.1:8000`. PHP built-in
server hanya untuk development dan tidak boleh digunakan di production. Deployment
production tetap memakai PHP-FPM sesuai panduan di bawah ini.

### Siapkan `.env` Lokal

Dari root repository, salin `.env.example` ke `.env` hanya jika diperlukan, lalu
sesuaikan variabel berikut. Contoh path host di bawah memakai
`/home/steven/Documents/Audit-Mutu-Internal` sebagai contoh development konkret,
bukan sebagai default production.

```dotenv
CI_ENV=development
APP_LOG_THRESHOLD=1
APP_ENCRYPTION_KEY=local-development-only-key
APP_BASE_URL=http://127.0.0.1:8000/
APP_COOKIE_SECURE=false

# Harus menunjuk ke host MySQL/MariaDB yang dapat dijangkau dari host ini.
# Jangan menyimpan IP container Docker sebagai konfigurasi stabil.
DB_HOST=127.0.0.1
DB_USERNAME=ami_local
DB_PASSWORD=<isi-password-MySQL-lokal>
DB_DATABASE=ami

# Keduanya adalah path pada host development.
APP_PRIVATE_STORAGE_PATH=/home/steven/Documents/Audit-Mutu-Internal/storage/private
APP_PUBLIC_UPLOADS_PATH=/home/steven/Documents/Audit-Mutu-Internal/uploads
APP_LOG_PATH=/home/steven/Documents/Audit-Mutu-Internal/storage/logs

# Backend bukti SPMI: local atau google_drive.
SPMI_EVIDENCE_STORAGE_BACKEND=local

# Isi hanya jika backend Google Drive sedang diuji secara lokal.
# GOOGLE_DRIVE_AUTH_MODE=oauth
# GOOGLE_DRIVE_EVIDENCE_FOLDER_ID=
# GOOGLE_DRIVE_SERVICE_ACCOUNT_JSON_PATH=
# GOOGLE_DRIVE_OAUTH_CLIENT_SECRET_JSON_PATH=
# GOOGLE_DRIVE_OAUTH_REFRESH_TOKEN_JSON_PATH=

# Isi hanya jika reset password via Brevo sedang diuji.
# BREVO_API_KEY=
# MAIL_FROM_ADDRESS=
# MAIL_FROM_NAME=AMI Development

# Opsional: daftar endpoint PDDIKTI HTTPS yang dipisahkan koma.
# PDDIKTI_BASE_URLS=https://api.example.org,https://api-backup.example.org
```

`DB_HOST` harus berisi nama host atau alamat IP MySQL/MariaDB yang benar-benar dapat
dijangkau oleh PHP yang berjalan di host ini. Untuk database lokal, gunakan misalnya
`127.0.0.1` atau `localhost`. Jangan memakai IP internal container Docker sebagai
konfigurasi tetap, karena alamat tersebut dapat berubah dan mode PHP CLI ini bukan
mode Compose.

`APP_PRIVATE_STORAGE_PATH` adalah lokasi host untuk file private dan tidak boleh
berada di webroot yang dilayani publik. `APP_PUBLIC_UPLOADS_PATH` adalah lokasi host
untuk upload publik, khususnya profil. Bila variabel path tidak diisi, runner memakai
`storage/private` dan `uploads` di root repository. `APP_LOG_PATH` menentukan lokasi
log aplikasi; buat direktori tersebut sebelum menjalankan aplikasi jika path custom
dipakai. Jangan menyimpan nilai rahasia pada repository atau membagikan file `.env`.

Runner otomatis membuat kategori private berikut saat start:

- `audit_evidence`, bukti audit yang diunggah auditee.
- `bukti_auditor`, bukti atau lampiran yang diunggah auditor.
- `instrumen`, file instrumen yang masih dipakai oleh alur terkait.
- `penetapan`, dokumen tahap Penetapan.
- `ppepp_documents`, arsip dokumen PPEPP manajemen.
- `rtm_photos`, foto dokumentasi Rapat Tinjauan Manajemen.
- `spmi_source`, PDF sumber SPMI.
- `tmp`, file sementara proses aplikasi.
- `user_photos`, foto pengguna yang disimpan private.

Runner juga otomatis membuat folder publik `profil` di bawah
`APP_PUBLIC_UPLOADS_PATH` dan folder sesi di `application/cache/sessions`. Pastikan
akun yang menjalankan runner memiliki hak tulis pada path tersebut. Runner tidak
membuat direktori `APP_LOG_PATH`, sehingga buat sekali apabila nilainya bukan path
log bawaan aplikasi:

```bash
mkdir -p /home/steven/Documents/Audit-Mutu-Internal/storage/logs
```

### Jalankan, Verifikasi, Inspeksi, dan Hentikan

Jalankan dari root repository setelah MySQL/MariaDB dan database `ami` siap:

```bash
./run-local-php.sh > /tmp/ami-local-php.log 2>&1 &
printf '%s\n' "$!" > /tmp/ami-local-php.pid
```

Verifikasi endpoint aplikasi dan lihat status proses:

```bash
curl -i http://127.0.0.1:8000/index.php/auth
ps -p "$(cat /tmp/ami-local-php.pid)" -o pid=,cmd=
```

Periksa log runner atau output PHP ketika mendiagnosis masalah:

```bash
less /tmp/ami-local-php.log
find /home/steven/Documents/Audit-Mutu-Internal/storage/private \
  -maxdepth 1 -mindepth 1 -type d -printf '%f\n' | sort
find /home/steven/Documents/Audit-Mutu-Internal/uploads/profil \
  -maxdepth 1 -type d -print
```

Hentikan server development dengan PID yang dicatat saat start. Jangan menghentikan
proses PHP lain milik pengguna atau service production.

```bash
kill "$(cat /tmp/ami-local-php.pid)"
rm -f /tmp/ami-local-php.pid
```

## 3. Konfigurasi Web Server

### Opsi A: Nginx + PHP-FPM (Sangat Direkomendasikan)
Salin template deployment yang disediakan di `deploy/nginx/ami.conf`. Template ini bukan bagian dari runtime Docker development:
```bash
sudo cp /var/www/ami/deploy/nginx/ami.conf /etc/nginx/sites-available/ami.conf
sudo ln -s /etc/nginx/sites-available/ami.conf /etc/nginx/sites-enabled/
```
Edit nama domain, path sertifikat SSL, dan path socket PHP-FPM di `/etc/nginx/sites-available/ami.conf`.
Uji dan terapkan:
```bash
sudo nginx -t
sudo systemctl reload nginx
```

*Keamanan bawaan Nginx*:
- Blokir folder sensitif: `application/`, `system/`, `migrations/`, `tests/`, `backups/`, `.git/`.
- Blokir file `.env*`, `.sql`, `.json`, `.lock`, `.yaml`.
- Blokir eksekusi script PHP di dalam `/uploads/`.
- Routing otomatis ke `index.php`.

### Opsi B: Apache 2.4+
Pastikan modul Apache yang dibutuhkan aktif:
```bash
sudo a2enmod rewrite headers ssl
```
File `.htaccess` bawaan pada root repository telah mengamankan rewrite, blokir folder internal, dan proteksi eksekusi PHP pada upload.
Pastikan VirtualHost mengizinkan `.htaccess`:
```apache
<VirtualHost *:443>
    ServerName ami.unmuhbabel.ac.id
    DocumentRoot /var/www/ami

    <Directory /var/www/ami>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/ami.unmuhbabel.ac.id/fullchain.pem
    SSLCertificateKey /etc/letsencrypt/live/ami.unmuhbabel.ac.id/privkey.pem
</VirtualHost>
```
Jika memakai symlink `uploads/` ke `APP_PUBLIC_UPLOADS_PATH`, pertahankan `+FollowSymLinks` dan `AllowOverride All`; file `uploads/.htaccess` tetap harus ikut tersedia pada target symlink. Symlink hanya boleh menunjuk ke root public uploads, bukan ke root private storage.

Untuk Apache `Alias` sebagai pengganti symlink, gunakan allowlist logo. Jangan memakai `Alias /uploads/ ...`, karena itu dapat mengekspos kategori atau tipe file baru tanpa review:
```apache
AliasMatch ^/uploads/profil/([A-Za-z0-9][A-Za-z0-9._-]*\.(?:gif|jpe?g|png))$ /var/ami-storage/public_uploads/profil/$1
<Directory /var/ami-storage/public_uploads>
    Options -Indexes -ExecCGI
    AllowOverride None
    Require all denied
    <FilesMatch "(?i)\.(php|phtml|phar|cgi|pl|py|sh|bash)$">
        Require all denied
    </FilesMatch>
</Directory>
<LocationMatch "^/uploads/profil/[A-Za-z0-9][A-Za-z0-9._-]*\.(?:gif|jpe?g|png)$">
    Require all granted
</LocationMatch>
```

---

## 4. Konfigurasi Environment Variables

Pada mode `ENVIRONMENT === 'production'`, aplikasi menjalankan **Fail-Closed Gate**. Jika ada variabel wajib yang hilang atau tidak valid, aplikasi akan menampilkan `HTTP 503 Service Unavailable` secara aman tanpa mengekspos error detail.

### Metode Konfigurasi Terbaik (PHP-FPM Pool):
Tambahkan variabel environment pada pool PHP-FPM Anda (misal `/etc/php/8.3/fpm/pool.d/ami.conf` atau `www.conf`).

> **PENTING UNTUK PHP-FPM**:
> Secara default, direktif PHP-FPM `clear_env = yes` aktif, yang berarti variabel environment sistem dari OS (`/etc/environment` atau `systemd`) **TIDAK AKAN DITERUSKAN** ke worker PHP-FPM!
> Anda WAJIB mendefinisikan variabel secara eksplisit menggunakan sintaks `env[...] = ...` di dalam file pool konfigurasi PHP-FPM di bawah ini:

```ini
env[CI_ENV] = 'production'
env[APP_BASE_URL] = 'https://ami.unmuhbabel.ac.id/'
env[APP_ENCRYPTION_KEY] = 'generate_key_32_karakter_acak_hex_atau_string'
env[APP_COOKIE_SECURE] = 'true'
env[APP_LOG_THRESHOLD] = '1'
env[DB_HOST] = '127.0.0.1'
env[DB_USERNAME] = 'ami_user'
env[DB_PASSWORD] = 'password_database_rahasia'
env[DB_DATABASE] = 'ami'
env[APP_PRIVATE_STORAGE_PATH] = '/var/ami-storage/private'
env[APP_PUBLIC_UPLOADS_PATH] = '/var/ami-storage/public_uploads'
env[APP_LOG_PATH] = '/var/log/ami/'
env[SPMI_EVIDENCE_STORAGE_BACKEND] = 'local'

; Email Transaksional (Brevo API)
env[BREVO_API_KEY] = 'xkeysib-xxxxxxxxxxxxxxxxxxxx'
env[MAIL_FROM_ADDRESS] = 'no-reply@example.org'
env[MAIL_FROM_NAME] = 'AMI Administrator'

; Opsional: override endpoint PDDIKTI, dipisahkan koma
; env[PDDIKTI_BASE_URLS] = 'https://api.example.org,https://api-backup.example.org'
```

`MAIL_TRANSPORT`, `SMTP_*`, `BREVO_SENDER_EMAIL`, dan `BREVO_SENDER_NAME` bukan
kontrak runtime saat ini. Reset password memakai Brevo HTTPS API dan hanya membaca
`BREVO_API_KEY`, `MAIL_FROM_ADDRESS`, serta `MAIL_FROM_NAME`.

Jika `APP_PUBLIC_UPLOADS_PATH` tidak diset, aplikasi memakai `FCPATH/uploads`.
Jika diset, path harus sudah ada dan writable; web server harus memakai pemetaan
allowlist `/uploads/profil/` pada bagian 3. Jangan letakkan `.env` di webroot.

*Kunci Enkripsi (`APP_ENCRYPTION_KEY`)*:
Generate kunci acak:
```bash
php -r 'echo bin2hex(random_bytes(32)) . PHP_EOL;'
```
> **PENTING**: Kunci ini digunakan untuk enkripsi AES-256-GCM token Cloudflare Turnstile dan hash rate limiting. Jangan pernah mengubah kunci ini setelah deployment karena ciphertext Turnstile tidak akan bisa didekripsi jika kunci berubah.

Database menggunakan `mysqli` tanpa TLS aplikasi (`encrypt => FALSE`). Tempatkan
MySQL pada localhost, private subnet, atau jaringan terenkripsi. Jika DB melintasi
jaringan tidak tepercaya, tambahkan konfigurasi TLS MySQL dan CA secara terpisah
sebelum go-live.

---

## 5. Instalasi Dependensi & Build Asset

Jalankan perintah berikut di folder aplikasi:
```bash
cd /var/www/ami

# 1. Pasang dependensi PHP produksi (termasuk PhpSpreadsheet)
composer install --no-dev --optimize-autoloader

# 2. Kompilasi asset Tailwind CSS
npm ci
npm run build
```

---

## 6. Database: Instalasi Baru & Migrasi

### A. Instalasi Baru (Fresh Installation)
1. Buat database dan user:
   ```sql
   CREATE DATABASE ami CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'ami_user'@'127.0.0.1' IDENTIFIED BY 'password_database_rahasia';
   GRANT ALL PRIVILEGES ON ami.* TO 'ami_user'@'127.0.0.1';
   FLUSH PRIVILEGES;
   ```
2. Impor schema dasar (`database_schema.sql` sudah mencakup struktur lengkap termasuk pengaturan branding aplikasi dan hero login):
   ```bash
   mysql -u ami_user -p ami < database_schema.sql
   ```
   *Catatan*: `database_schema.sql` memuat baris `CREATE DATABASE IF NOT EXISTS ami` dan `USE ami;`. Jika Anda menggunakan nama database yang berbeda (misal `ami_prod`), pastikan menyesuaikan atau menghapus baris `USE ami;` di bagian atas file sebelum impor.

3. Verifikasi migrasi:
   Bootstrap saat ini mencakup parity `001-050`. Jangan jalankan migration lama
   lagi setelah fresh import. Menjalankan 049 dan 050 ulang tetap aman karena
   idempoten (guard `INFORMATION_SCHEMA.COLUMNS`):
   ```bash
   mysql -u ami_user -p ami < migrations/049_add_app_branding_settings.sql
   mysql -u ami_user -p ami < migrations/050_add_login_hero_settings.sql
   ```

### B. Upgrade Database Existing
1. **Backup database sebelum migrasi**:
   ```bash
   mysqldump -u ami_user -p ami > backup_ami_$(date +%Y%m%d_%H%M%S).sql
   ```
2. Tidak ada tabel migration tracker otomatis. Bandingkan riwayat deployment,
   schema, dan daftar file; lalu jalankan **semua migration yang belum diterapkan**
   secara numerik, satu per satu, berhenti pada error pertama. Jangan menjalankan
   `database_schema.sql` pada database existing.
3. Untuk deployment yang telah mencapai 048, jalankan 049 dan 050 berikut. Keduanya
   idempoten dan aman dijalankan ulang:
   ```bash
   mysql -u ami_user -p ami < migrations/049_add_app_branding_settings.sql
   mysql -u ami_user -p ami < migrations/050_add_login_hero_settings.sql
   ```

### C. Backup, Restore, dan Rollback

Sebelum setiap upgrade, backup database **dan** `APP_PRIVATE_STORAGE_PATH`; backup
harus terenkripsi dan diuji restore pada host terpisah.

```bash
mysqldump --single-transaction --routines --events --triggers -u ami_user -p ami \
  > /secure-backups/ami-$(date +%F-%H%M%S).sql
sudo tar -C /var/ami-storage -czf /secure-backups/ami-private-$(date +%F-%H%M%S).tgz private
```

Jika migration gagal, hentikan traffic, restore database dan private storage dari
backup pasangan yang sama, lalu investigasi pada salinan DB. Jangan melakukan
rollback SQL manual tanpa prosedur migration yang diuji. `APP_ENCRYPTION_KEY` harus
dipertahankan pada restore; menggantinya membuat ciphertext Turnstile dan HMAC bucket
lama tidak dapat dipakai.

---

## 7. Verifikasi Pasca-Deployment (Smoke Test)

Jalankan checklist verifikasi berikut setelah server aktif:
1. **Akses Root**: Buka `https://ami.unmuhbabel.ac.id/` -> pastikan redirect ke login dan SSL valid.
2. **Uji Proteksi Folder**:
   - Akses `https://ami.unmuhbabel.ac.id/application/config/database.php` -> harus mengembalikan **403 Forbidden** atau **404 Not Found**.
   - Akses `https://ami.unmuhbabel.ac.id/.env.example` -> harus **403/404**.
   - Akses `https://ami.unmuhbabel.ac.id/migrations/` -> harus **403/404**.
3. **Login Pengguna**: Login dengan akun pengelola -> pastikan dashboard terbuka dengan benar.
4. **Unggah & Unduh Dokumen**:
   - Unggah dokumen PPEPP / RTM / Bukti SPMI.
   - Periksa file fisik tersimpan di `/var/ami-storage/private/<kategori>/` dengan nama terenkripsi / hash.
   - Unduh kembali dokumen tersebut melalui aplikasi untuk memastikan download handler berfungsi.
   - Pastikan URL langsung ke storage tidak dapat diakses publik.
5. **Ekspor Laporan**: Uji fitur cetak dan ekspor XLSX/Word pada Laporan SPMI untuk memastikan ekstensi PhpSpreadsheet dan XML bekerja optimal.

## 8. Checklist Handoff IT

Sebelum DNS atau traffic dibuka, operator harus menyediakan dan memverifikasi:

1. Domain HTTPS, sertifikat, `APP_BASE_URL` berakhiran `/`, dan socket PHP-FPM yang benar.
2. Nilai unik rahasia untuk `APP_ENCRYPTION_KEY`, `DB_PASSWORD`, dan `BREVO_API_KEY`; tidak boleh masuk Git, shell history, atau webroot.
3. Semua `env[...]` di pool PHP-FPM, lalu `systemctl restart php8.3-fpm` dan `systemctl reload nginx` atau `apache2`.
4. Ownership/permission storage sesuai bagian 2; private storage dan log berada di luar webroot.
5. Konfigurasi Nginx: ganti domain, root, sertifikat, `fastcgi_pass`, dan alias public upload; jalankan `nginx -t` sebelum reload. Untuk Apache, jalankan `apachectl -t` dan pastikan `AllowOverride All` pada document root.
6. Backup database/private storage sukses dan restore drill tercatat.
7. Smoke test bagian 7 untuk login, upload, download berotorisasi, reset password, dan export laporan.

## 9. Troubleshooting Cepat

| Gejala | Periksa | Tindakan aman |
| --- | --- | --- |
| HTTP 503 saat boot | PHP-FPM `env[...]`, path log/private/public | Pastikan path ada, writable, dan variabel produksi lengkap; restart PHP-FPM. |
| Reset password tidak mengirim email | `BREVO_API_KEY`, `MAIL_FROM_ADDRESS`, log aplikasi | Verifikasi sender Brevo; jangan log atau tampilkan API key. |
| Logo 404 | `APP_PUBLIC_UPLOADS_PATH`, regex alias/symlink | Uji hanya URL `.gif/.jpg/.jpeg/.png`; jangan membuat alias `/uploads/` yang luas. |
| Upload gagal sebelum aplikasi | `client_max_body_size`, `upload_max_filesize`, `post_max_size` | Selaraskan limit web server/PHP di atas batas aplikasi, restart layanan. |
| IP semua pengguna sama | reverse proxy dan `proxy_ips` | Tambahkan hanya proxy tepercaya pada konfigurasi aplikasi; jangan percaya header client langsung. |
| Turnstile menjadi tidak valid | `APP_ENCRYPTION_KEY` berubah atau secret rusak | Super Admin masukkan ulang site key dan secret; jangan pernah menampilkan ciphertext/plaintext. |
