# Panduan Deployment Produksi Non-Docker (Bare-Metal / VM)

Panduan ini adalah dokumentasi resmi untuk tim infrastruktur & sysadmin dalam mendistribusikan aplikasi **Audit Mutu Internal (AMI)** pada server konvensional (Ubuntu / Debian Linux) tanpa Docker.

---

## 1. Arsitektur & Persyaratan Server

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
     sudo rm -rf /var/www/ami/uploads
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

## 3. Konfigurasi Web Server

### Opsi A: Nginx + PHP-FPM (Sangat Direkomendasikan)
Salin konfigurasi yang telah disediakan di `docker/nginx-production.conf`:
```bash
sudo cp /var/www/ami/docker/nginx-production.conf /etc/nginx/sites-available/ami.conf
sudo ln -s /etc/nginx/sites-available/ami.conf /etc/nginx/sites-enabled/
```
Edit nama domain, path sertifikat SSL, dan path socket PHP-FPM di `/etc/nginx/sites-available/ami.conf`.
Uji dan terapkan:
```bash
sudo nginx -t
sudo systemctl reload nginx
```

*Keamanan bawaan Nginx*:
- Blokir folder sensitif: `application/`, `system/`, `migrations/`, `tests/`, `backups/`, `.git/`, `.multibrain/`.
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
env[DB_HOST] = '127.0.0.1'
env[DB_USERNAME] = 'ami_user'
env[DB_PASSWORD] = 'password_database_rahasia'
env[DB_DATABASE] = 'ami'
env[APP_PRIVATE_STORAGE_PATH] = '/var/ami-storage/private'
env[APP_LOG_PATH] = '/var/log/ami/'
env[SPMI_EVIDENCE_STORAGE_BACKEND] = 'local'

; Email Transaksional (Brevo API)
env[MAIL_TRANSPORT] = 'brevo'
env[BREVO_API_KEY] = 'xkeysib-xxxxxxxxxxxxxxxxxxxx'
env[BREVO_SENDER_EMAIL] = 'lpmpi@unmuhbabel.ac.id'
env[BREVO_SENDER_NAME] = 'LPMPI UNMUH BABEL'
```

*Kunci Enkripsi (`APP_ENCRYPTION_KEY`)*:
Generate kunci acak:
```bash
php -r 'echo bin2hex(random_bytes(32)) . PHP_EOL;'
```
> **PENTING**: Kunci ini digunakan untuk enkripsi AES-256-GCM token Cloudflare Turnstile dan hash rate limiting. Jangan pernah mengubah kunci ini setelah deployment karena ciphertext Turnstile tidak akan bisa didekripsi jika kunci berubah.

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
   Jika mengimpor `database_schema.sql` terbaru, seluruh skema telah terpasang. Menjalankan migrasi `049` dan `050` setelahnya tetap aman karena keduanya bersifat idempotent (menggunakan guard pengecekan kolom):
   ```bash
   mysql -u ami_user -p ami < migrations/049_add_app_branding_settings.sql
   mysql -u ami_user -p ami < migrations/050_add_login_hero_settings.sql
   ```

### B. Upgrade Database Existing
1. **Backup database sebelum migrasi**:
   ```bash
   mysqldump -u ami_user -p ami > backup_ami_$(date +%Y%m%d_%H%M%S).sql
   ```
2. Jalankan migrasi secara berurutan sesuai file pada folder `migrations/`. Migrasi 049 dan 050 bersifat idempotent dan aman dijalankan ulang:
   ```bash
   mysql -u ami_user -p ami < migrations/049_add_app_branding_settings.sql
   mysql -u ami_user -p ami < migrations/050_add_login_hero_settings.sql
   ```

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
