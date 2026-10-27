# Panduan Deployment Native CodeIgniter 3 AMI pada Server Multisite Nginx / Apache + PHP-FPM

Panduan ini ditujukan bagi Tim IT Kampus / Sysadmin yang mengelola server production multisite (shared virtual host / multi-domain dalam satu server Nginx atau Apache + PHP-FPM) untuk men-deploy aplikasi **Audit Mutu Internal (AMI)** berbasis CodeIgniter 3 secara **100% Native CodeIgniter 3**.

---

## Prinsip Utama Arsitektur Native CI3 Multisite

1. **Murni Konfigurasi Native CodeIgniter 3**: Seluruh pengaturan sistem membaca file konfigurasi PHP standar di `application/config/` dan override environment di `application/config/production/`.
2. **Tidak Menggunakan `.env` atau Dotenv**: Server production tidak memerlukan file `.env`, library pihak ketiga dotenv, maupun ketergantungan variabel environment PHP-FPM khusus.
3. **Pemisahan Mode Lingkungan yang Jelas**: Mode `production` atau `development` dikontrol langsung secara deklaratif pada konstanta `ENVIRONMENT` di `index.php`.
4. **Keamanan Kredensial**: File konfigurasi production dengan password database (`application/config/production/database.php`, `config.php`, `ami.php`) diabaikan oleh Git (`.gitignore`), disediakan template siap salin (`.example`), dan dilindungi hak akses file `0640` di server.
5. **Starter Database Instan**: Tersedia `database_init.sql` dengan skema 50 tabel lengkap dan 1 akun superadmin bawaan siap pakai.

---

## Daftar Isi
- [A. Ringkasan File Konfigurasi Native](#a-ringkasan-file-konfigurasi-native)
- [B. Prasyarat Server & Ekstensi PHP 8.3](#b-prasyarat-server--ekstensi-php-83)
- [C. Struktur Direktori Project & Izin Akses (Permissions)](#c-struktur-direktori-project--izin-akses-permissions)
- [D. Persiapan Database MySQL / MariaDB (Import Starter Database)](#d-persiapan-database-mysql--mariadb-import-starter-database)
- [E. Konfigurasi Native CodeIgniter 3 di Production (Langkah demi Langkah)](#e-konfigurasi-native-codeigniter-3-di-production-langkah-demi-langkah)
- [F. Konfigurasi Web Server (Nginx & Apache Multisite)](#f-konfigurasi-web-server-nginx--apache-multisite)
- [G. Verifikasi Akun Super Admin & Pengujian Login](#g-verifikasi-akun-super-admin--pengujian-login)
- [H. Smoke Testing Fitur Utama Aplikasi](#h-smoke-testing-fitur-utama-aplikasi)
- [I. Prosedur Backup & Pemeliharaan Rutin](#i-prosedur-backup--pemeliharaan-rutin)
- [J. Troubleshooting & Masalah Umum (FAQ)](#j-troubleshooting--masalah-umum-faq)

---

## A. Ringkasan File Konfigurasi Native

CodeIgniter 3 memiliki fitur bawaan di mana file di dalam `application/config/{ENVIRONMENT}/` secara otomatis menimpa (override) file di `application/config/`.

| File Konfigurasi | Lokasi di Server | Fungsi & Pengaturan Utama |
|---|---|---|
| Entry Point & Environment | `index.php` | Terpasang `define('ENVIRONMENT', 'production');` secara native. Tim IT tidak perlu mendeklarasikan variabel environment di Nginx / Apache / PHP-FPM. |
| Database Production | `application/config/production/database.php` | Kredensial MySQL: `hostname`, `username`, `password`, `database`. |
| Core Config Production | `application/config/production/config.php` | `base_url`, `encryption_key`, `sess_save_path`, `cookie_secure`, `log_threshold`, `log_path`. |
| Custom App Config Production | `application/config/production/ami.php` | `private_storage_path`, `public_uploads_path`, email/Brevo, Google Drive, PDDIKTI. |

---

## B. Prasyarat Server & Ekstensi PHP 8.3

Aplikasi berjalan optimal di **PHP 8.3** (didukung PHP 7.4 - 8.3). Pastikan ekstensi berikut aktif:

```bash
# Cek versi PHP
php -v

# Cek ekstensi wajib
php -m | grep -E 'mysqli|pdo_mysql|curl|fileinfo|gd|intl|mbstring|xml|zip|openssl'
```

Daftar ekstensi wajib:
1. `mysqli` & `pdo_mysql` (koneksi database)
2. `curl` & `openssl` (integrasi API & download)
3. `fileinfo` (validasi format file upload bukti)
4. `gd` (pengolahan gambar/foto profil & instrumen)
5. `intl` (format penanggalan & lokalisasi)
6. `mbstring` (manipulasi string UTF-8)
7. `xml` & `zip` (export/import Excel via PhpSpreadsheet)

---

## C. Struktur Direktori Project & Izin Akses (Permissions)

Letakkan project pada direktori virtual host yang ditentukan (misalnya: `/var/www/vhosts/ami.unmuhbabel.ac.id/` atau `/var/www/html/ami/`).

### 1. Struktur Folder Upload & Cache
Aplikasi membutuhkan izin tulis (writable) pada folder berikut:
- `application/cache/` (termasuk subfolder `application/cache/sessions/`)
- `application/logs/`
- `uploads/` (beserta 10 subfolder: `profil/`, `audit_evidence/`, `bukti_auditor/`, `instrumen/`, `penetapan/`, `ppepp_documents/`, `rtm_photos/`, `spmi_source/`, `tmp/`, `user_photos/`)

### 2. Perintah Atur Hak Akses (AlmaLinux / RHEL / CentOS / Ubuntu)
Sesuaikan user/group web server (misal `nginx:nginx`, `apache:apache`, atau `www-data:www-data`):

```bash
cd /path/to/Audit-Mutu-Internal

# Atur kepemilikan
sudo chown -R nginx:nginx application/cache application/logs uploads

# Pastikan folder writable
sudo chmod -R 775 application/cache application/logs uploads

# Pastikan folder session ada dan berizin aman
sudo mkdir -p application/cache/sessions
sudo chmod 700 application/cache/sessions
sudo chown nginx:nginx application/cache/sessions
```

Jika server menggunakan **SELinux (Enforcing)**:
```bash
sudo semanage fcontext -a -t httpd_sys_rw_content_t "/path/to/Audit-Mutu-Internal/application/cache(/.*)?"
sudo semanage fcontext -a -t httpd_sys_rw_content_t "/path/to/Audit-Mutu-Internal/application/logs(/.*)?"
sudo semanage fcontext -a -t httpd_sys_rw_content_t "/path/to/Audit-Mutu-Internal/uploads(/.*)?"
sudo restorecon -Rv /path/to/Audit-Mutu-Internal
```

---

## D. Persiapan Database MySQL / MariaDB (Import Starter Database)

Tersedia file **`database_init.sql`** di root repository yang berisi:
- Struktur 50 tabel lengkap.
- Data referensi awal (standar SPMI, siklus PPEPP, unit kerja, hak akses).
- **Akun Super Admin Bawaan**:
  - **Email**: `admin.ami@unmuhbabel.ac.id`
  - **Password**: `AdminAmi123,.`

### Langkah Eksekusi Database:
1. Masuk ke MySQL / MariaDB dan buat database serta user:
   ```sql
   CREATE DATABASE ami_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'ami_user'@'localhost' IDENTIFIED BY 'PasswordKuatDatabase123!';
   GRANT ALL PRIVILEGES ON ami_db.* TO 'ami_user'@'localhost';
   FLUSH PRIVILEGES;
   ```
2. Import file starter database:
   ```bash
   mysql -u ami_user -p ami_db < database_init.sql
   ```

*(Catatan: Jika ingin membuat database kosongan dan membuat akun superadmin sendiri via terminal interaktif, import file `database_schema.sql` kemudian jalankan `php scripts/bootstrap_super_admin.php`).*

---

## E. Konfigurasi Native CodeIgniter 3 di Production (Langkah demi Langkah)

Tidak perlu setup environment variable di server atau file luar. Cukup siapkan file konfigurasi di folder `application/config/production/`:

### Langkah 1: Buat dan Edit `database.php` Production
Salin template:
```bash
cp application/config/production/database.php.example application/config/production/database.php
nano application/config/production/database.php
```

Sesuaikan nilai array berikut:
```php
$db['default']['hostname'] = 'localhost'; // atau 127.0.0.1
$db['default']['username'] = 'ami_user';
$db['default']['password'] = 'PasswordKuatDatabase123!';
$db['default']['database'] = 'ami_db';
```

### Langkah 2: Buat dan Edit `config.php` Production
Salin template:
```bash
cp application/config/production/config.php.example application/config/production/config.php
nano application/config/production/config.php
```

Sesuaikan nilai:
```php
// URL website
$config['base_url'] = 'https://ami.unmuhbabel.ac.id/';

// Generate key acak 32 karakter
$config['encryption_key'] = 'GANTI_DENGAN_32_KARAKTER_ACAK_DAN_AMAN!';

// Cookie HTTPS
$config['cookie_secure'] = TRUE;

// Logging production (Level 1 = Error only)
$config['log_threshold'] = 1;
$config['log_path'] = APPPATH . 'logs/';
```

### Langkah 3: Buat dan Edit `ami.php` Production
Salin template:
```bash
cp application/config/production/ami.php.example application/config/production/ami.php
nano application/config/production/ami.php
```

Sesuaikan nilai upload path:
```php
$config['private_storage_path'] = FCPATH . 'uploads/';
$config['public_uploads_path'] = FCPATH . 'uploads/';
```

### Langkah 4: Kunci Izin Akses File Konfigurasi Production
Pastikan file konfigurasi hanya dapat dibaca oleh web server:
```bash
chmod 640 application/config/production/*.php
chown nginx:nginx application/config/production/*.php
```

### Langkah 5: Pastikan `index.php` Mengarah ke Mode Production
Buka `index.php`:
```bash
nano index.php
```
Pastikan baris environment diset ke:
```php
define('ENVIRONMENT', 'production');
```

---

## F. Konfigurasi Web Server (Nginx & Apache Multisite)

### 1. Konfigurasi Nginx (Virtual Host Server Block)
Simpan konfigurasi pada virtual host Anda (misal: `/etc/nginx/conf.d/ami.conf`):

```nginx
server {
    listen 80;
    server_name ami.unmuhbabel.ac.id;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name ami.unmuhbabel.ac.id;

    # SSL Certificates
    ssl_certificate /etc/letsencrypt/live/ami.unmuhbabel.ac.id/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/ami.unmuhbabel.ac.id/privkey.pem;

    root /var/www/vhosts/ami.unmuhbabel.ac.id;
    index index.php index.html;

    client_max_body_size 25M;

    # Proteksi direktori dan file sensitif
    location ~* ^/(application|system|vendor|tests|deploy|docs|scripts|\.git) {
        deny all;
        return 404;
    }

    # Proteksi eksekusi PHP pada folder uploads
    location ~* ^/uploads/.*\.php$ {
        deny all;
        return 404;
    }

    # Clean URL Routing CodeIgniter 3
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # FastCGI PHP-FPM Handler
    location ~ ^/index\.php(/|$) {
        fastcgi_split_path_info ^(.+?\.php)(/.*)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;

        # Sesuaikan socket atau port PHP-FPM server Anda:
        fastcgi_pass unix:/run/php-fpm/www.sock;
        # atau: fastcgi_pass 127.0.0.1:9000;

        fastcgi_read_timeout 120s;
    }

    # Blokir akses langsung ke file .php selain index.php
    location ~ \.php$ {
        deny all;
        return 404;
    }

    # Cache file statis
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff2|woff|ttf|svg)$ {
        expires 30d;
        add_header Cache-Control "public, no-transform";
        try_files $uri =404;
    }
}
```

Uji dan reload Nginx:
```bash
sudo nginx -t
sudo systemctl reload nginx
```

---

## G. Verifikasi Akun Super Admin & Pengujian Login

1. Buka browser dan akses halaman login:
   ```
   https://ami.unmuhbabel.ac.id/
   ```
2. Halaman form login Audit Mutu Internal akan tampil bersih tanpa error.
3. Masukkan kredensial administrator bawaan:
   - **Email**: `admin.ami@unmuhbabel.ac.id`
   - **Password**: `AdminAmi123,.`
4. Tekan **Masuk**.
5. Sistem akan mengarahkan ke dashboard utama SPMI / LPMPI.
6. Sangat disarankan untuk segera mengubah password admin pada menu Profil setelah login pertama berhasil.

---

## H. Smoke Testing Fitur Utama Aplikasi

Lakukan pengujian cepat berikut setelah instalasi:
1. **Login & Logout**: Pastikan proses autentikasi, pembuatan cookie sesi, dan penghapusan sesi berjalan normal.
2. **Unggah Dokumen PPEPP / Bukti Standar**: Buka menu SPMI / Penetapan Dokumen, unggah satu file PDF pengujian. Pastikan file tersimpan di `uploads/ppepp_documents/` dan dapat diunduh kembali.
3. **Pengaturan Unit Kerja & Pengguna**: Coba tambah 1 akun Auditor pengujian.
4. **Cek Log Aplikasi**: Periksa file di `application/logs/log-YYYY-MM-DD.php` untuk memastikan tidak ada pesan PHP Warning atau Database Error.

---

## I. Prosedur Backup & Pemeliharaan Rutin

### 1. Backup Database Harian (Cron Job)
```bash
mysqldump -u ami_user -p'PasswordKuatDatabase123!' ami_db > /backup/ami_db_$(date +\%F).sql
gzip /backup/ami_db_$(date +\%F).sql
```

### 2. Backup Folder Uploads Mingguan
```bash
tar -czf /backup/ami_uploads_$(date +\%F).tar.gz /var/www/vhosts/ami.unmuhbabel.ac.id/uploads/
```

---

## J. Troubleshooting & Masalah Umum (FAQ)

### 1. Muncul error: `Production configuration is incomplete.` (503)
- **Penyebab**: Syarat minimal keamanan mode production belum terpenuhi di `application/config/production/config.php` atau `database.php`.
- **Solusi**:
  1. Pastikan `base_url` diawali `https://`.
  2. Pastikan `encryption_key` tidak kosong.
  3. Pastikan `cookie_secure` bernilai `TRUE`.
  4. Pastikan `log_threshold` bernilai minimal `1` dan folder `application/logs/` writable.
  5. Pastikan folder `uploads/` dan `application/cache/sessions/` sudah dibuat dan writable.

### 2. Muncul error: `Unable to connect to your database server using the provided settings.`
- **Penyebab**: Kredensial MySQL di `application/config/production/database.php` tidak cocok atau user tidak memiliki hak akses.
- **Solusi**: Uji koneksi dari terminal server:
  ```bash
  mysql -u ami_user -p -h localhost ami_db
  ```

### 3. File upload gagal disimpan (*File dokumen PPEPP gagal disimpan*)
- **Penyebab**: Folder `uploads/` atau subkategorinya tidak writable oleh user web server (`nginx` / `apache`).
- **Solusi**: Jalankan perintah perbaikan izin akses:
  ```bash
  sudo chown -R nginx:nginx uploads/
  sudo chmod -R 775 uploads/
  ```

### 4. Halaman selain beranda mengembalikan 404 Not Found
- **Penyebab**: Nginx belum mengarahkan URL rewrite ke `index.php`.
- **Solusi**: Pastikan blok `try_files $uri $uri/ /index.php?$query_string;` ada di konfigurasi server block Nginx Anda.

---

## K. Prosedur untuk Developer Lokal (Development Workstation)

Karena file `index.php` pada repository ini secara default menetapkan:
```php
define('ENVIRONMENT', 'production');
```
Hal ini bertujuan agar kode langsung aman dan siap pakai saat ditarik (*git pull*) oleh Tim IT ke server production tanpa perlu menyetel variabel lingkungan.

Namun, untuk developer yang bekerja di komputer lokal (workstation):
1. **Mengaktifkan Mode Development Secara Lokal**:
   Ubah baris 59 pada `index.php` di workstation Anda menjadi:
   ```php
   define('ENVIRONMENT', 'development');
   ```
2. **Mencegah Perubahan Lokal Masuk ke Git (*Assume Unchanged*)**:
   Agar perubahan baris tersebut tidak terbawa saat Anda melakukan *git commit* atau *git push*, jalankan perintah berikut di terminal repositori lokal:
   ```bash
   git update-index --assume-unchanged index.php
   ```
   *Jika suatu saat Anda ingin membatalkannya:*
   ```bash
   git update-index --no-assume-unchanged index.php
   ```
Dengan cara ini, developer lokal mendapatkan detail error log (`display_errors = 1`), sedangkan repository dan server production tetap 100% menggunakan `production`.
