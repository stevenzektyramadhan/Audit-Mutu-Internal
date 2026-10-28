# Deployment Native AlmaLinux dari ZIP

Panduan ini adalah handoff utama untuk deployment AMI/SPMI pada AlmaLinux 9.x
dengan Nginx, PHP-FPM, dan MariaDB/MySQL yang dipasang langsung pada host.
Paket diserahkan sebagai ZIP. Tim IT tidak perlu Git, Docker, `.env`, Dotenv,
atau environment variable aplikasi pada Nginx/PHP-FPM.

> **Status verifikasi:** BELUM DIVERIFIKASI di AlmaLinux. Perintah dan template
> di bawah harus dijalankan serta disesuaikan oleh Tim IT pada server target.
> Jangan menganggap dokumentasi ini sebagai bukti bahwa server, ZIP, atau
> packaging sudah berhasil diuji.

## Kontrak deployment

- Root instalasi contoh yang dipakai di seluruh panduan adalah
  `/var/www/html/ami`. Root Nginx menunjuk langsung ke folder yang berisi
  `index.php`.
- `index.php` ditetapkan ke production oleh paket. Tim IT **tidak mengedit**
  file itu.
- Nilai deployment hanya diedit pada tiga file berikut:
  `application/config/production/config.php`,
  `application/config/production/database.php`, dan
  `application/config/production/ami.php`.
- Nginx dan PHP-FPM tidak diberi `CI_ENV`, `APP_BASE_URL`,
  `APP_PRIVATE_STORAGE_PATH`, atau parameter `APP_*` lain untuk konfigurasi
  aplikasi. Nilai tersebut, bila diperlukan oleh implementasi target, harus
  berada di konfigurasi native CI3 yang sesuai.
- Default root upload native adalah `/var/www/html/ami/uploads/`. Nilai root
  private dapat dipindahkan ke path lain melalui satu field pada `ami.php`,
  bukan dengan mengubah helper atau service.
- Jangan menyimpan password, key, token, sertifikat, data pengguna, dump
  database operasional, atau isi upload pengguna di ZIP maupun dokumen ini.

## 1. Prasyarat AlmaLinux 9

Siapkan DNS dan sertifikat TLS sesuai prosedur IT. Contoh berikut memakai PHP
8.3, tetapi versi akhir harus kompatibel dengan `composer.lock` dan paket yang
diserahkan.

```bash
cat /etc/os-release
uname -m
php -v
php -m | grep -E 'mysqli|mbstring|xml|zip|gd|curl|fileinfo|openssl'
php-fpm -v
nginx -v
mariadb --version
unzip -v | head -n 2
getenforce
```

Ekstensi wajib minimal adalah `mysqli`, `mbstring`, `xml`, `zip`, `gd`, `curl`,
dan `fileinfo`. Pastikan Composer atau `vendor/` production tersedia sesuai
isi ZIP. Jika dependency belum dipaketkan, proses build dependency harus
dilakukan pada mesin build Linux yang kompatibel, bukan dengan `composer update`
di server target.

## 2. Transfer dan ekstrak ZIP

Transfer ZIP melalui kanal yang disetujui IT. Verifikasi checksum yang diberikan
oleh pembuat paket, lalu ekstrak ke root yang konsisten.

```bash
sudo install -d -m 0755 /var/www/html/ami
sha256sum AMI-NATIVE-ALMALINUX-<versi>.zip
sudo unzip -q AMI-NATIVE-ALMALINUX-<versi>.zip -d /var/www/html/ami
sudo find /var/www/html/ami -maxdepth 2 -type f -name index.php -print
sudo find /var/www/html/ami/uploads -maxdepth 2 -type d -print
```

Jika ZIP memiliki folder tingkat atas `AMI/`, ekstrak isinya sehingga hasil
akhirnya adalah `/var/www/html/ami/index.php`, bukan
`/var/www/html/ami/AMI/index.php`. Struktur minimum yang harus terlihat:

```text
/var/www/html/ami/
├── index.php
├── application/
├── system/
├── vendor/
├── uploads/
├── database_schema.sql
└── scripts/bootstrap_super_admin.php
```


## FILE APA YANG HARUS DIEDIT TIM IT

Edit hanya tiga file production berikut. Cari komentar `// GANTI` pada template
dan isi nilai yang sudah disetujui. Jangan menyalin password atau key ke tiket,
shell history, log, atau dokumen serah terima.

| File | Field yang diisi | Keterangan |
|---|---|---|
| `application/config/production/config.php` | `base_url`, `encryption_key`, `sess_save_path`, `log_path`, cookie, `index_page` | URL HTTPS, key acak persisten, path session dan log, serta mode rewrite yang diuji. |
| `application/config/production/database.php` | `hostname`, `username`, `password`, `database`, `dbdriver` | Gunakan user aplikasi, bukan akun DBA. |
| `application/config/production/ami.php` | `private_storage_path`, `public_uploads_path`, integrasi yang memang dipakai | Default upload adalah `FCPATH . 'uploads/'`; ubah root private hanya bila IT menyiapkan path dan aturan SELinux-nya. |

`index.php` tidak perlu diedit. File konfigurasi production yang aktif harus
dilindungi permission dan tidak boleh ditimpa ketika ZIP baru diekstrak.

## 4. Siapkan direktori, ownership, permission, dan SELinux

Temukan user/group PHP-FPM aktual. Jangan mengasumsikan selalu `nginx:nginx`.

```bash
systemctl status php-fpm --no-pager
ps -eo user,group,comm,args | grep '[p]hp-fpm'
systemctl cat php-fpm | grep -E '^(User|Group)=' || true
stat -c '%U:%G %a %n' /var/www/html/ami /var/www/html/ami/uploads
namei -l /var/www/html/ami/uploads
```

Ganti `<fpm_user>` dan `<fpm_group>` dengan hasil pemeriksaan, lalu siapkan
semua kategori yang dipakai aplikasi. Jangan memakai `chmod 777`.

```bash
sudo mkdir -p /var/www/html/ami/uploads/{audit_evidence,bukti_auditor,instrumen,penetapan,ppepp_documents,profil,rtm_photos,spmi_source,tmp,user_photos}
sudo mkdir -p /var/www/html/ami/application/{cache,sessions,logs}
sudo chown -R <fpm_user>:<fpm_group> /var/www/html/ami/uploads /var/www/html/ami/application/cache /var/www/html/ami/application/sessions /var/www/html/ami/application/logs
sudo find /var/www/html/ami/uploads /var/www/html/ami/application/cache /var/www/html/ami/application/sessions /var/www/html/ami/application/logs -type d -exec chmod 0750 {} +
sudo find /var/www/html/ami/uploads /var/www/html/ami/application/cache /var/www/html/ami/application/sessions /var/www/html/ami/application/logs -type f -exec chmod 0640 {} +
```

Biarkan SELinux `Enforcing`. Label path runtime sesuai kebijakan host, lalu
pulihkan context. Nama tipe dapat berbeda menurut policy yang terpasang, jadi
periksa hasilnya sebelum melanjutkan.

```bash
getenforce
ls -Zd /var/www/html/ami /var/www/html/ami/uploads
sudo semanage fcontext -a -t httpd_sys_rw_content_t '/var/www/html/ami/uploads(/.*)?'
sudo semanage fcontext -a -t httpd_sys_rw_content_t '/var/www/html/ami/application/cache(/.*)?'
sudo semanage fcontext -a -t httpd_sys_rw_content_t '/var/www/html/ami/application/logs(/.*)?'
sudo restorecon -Rv /var/www/html/ami/uploads /var/www/html/ami/application/cache /var/www/html/ami/application/logs
```

Jika IT memindahkan private root keluar dari project, ulangi pemeriksaan
ownership dan fcontext untuk path baru. Jangan menonaktifkan SELinux untuk
menyelesaikan permission error.

## 5. Nginx dan PHP-FPM

Gunakan socket yang benar dari `systemctl cat php-fpm` atau pool aktif. Ganti
domain dan path sertifikat dengan nilai milik IT. Contoh ini tidak memasukkan
environment variable aplikasi.

```nginx
server {
    listen 80;
    server_name <domain>;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl;
    server_name <domain>;
    root /var/www/html/ami;
    index index.php;

    ssl_certificate     <path-sertifikat>;
    ssl_certificate_key <path-private-key>;
    client_max_body_size 12M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        try_files $uri =404;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_pass unix:<socket-php-fpm>;
    }

    location ~ ^/(application|system|vendor|\.git|uploads/(audit_evidence|bukti_auditor|instrumen|penetapan|ppepp_documents|rtm_photos|spmi_source|tmp|user_photos))(/|$) {
        deny all;
    }

    location ~ ^/uploads/.*\.php$ {
        deny all;
    }
}
```

Jika `index_page` pada `config.php` adalah `''`, URL rewrite harus diuji. Jika
deployment memakai URL dengan `index.php`, isi `index_page` secara konsisten.
Jangan mencampur dua pola tanpa pengujian. Jalankan:

```bash
sudo nginx -t
sudo systemctl reload nginx
sudo systemctl status php-fpm nginx --no-pager
```

## 6. Database, fresh atau existing

### Fresh install

Fresh berarti database belum berisi aplikasi. Buat database kosong dengan DBA,
import `database_schema.sql` satu kali, lalu buat administrator pertama dengan
script bootstrap. Jangan memakai `database_init.sql` sebagai alur native ini.

```bash
sudo mariadb -u <dba_user> -p -e "CREATE DATABASE <db_name> CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
sudo mariadb -u <dba_user> -p <db_name> < /var/www/html/ami/database_schema.sql
php /var/www/html/ami/scripts/bootstrap_super_admin.php
```

Periksa database dan tabel tanpa menaruh password pada command line:

```bash
mariadb -u <app_user> -p -h <db_host> -e 'SELECT 1' <db_name>
mariadb -u <app_user> -p -h <db_host> -e 'SHOW TABLES' <db_name>
```

### Existing atau upgrade

Jangan import `database_schema.sql` atau bootstrap fresh ke database existing.
Backup database dan `uploads/`, lalu jalankan hanya migration manual yang telah
direview, belum diterapkan, dan sesuai urutan release. Jangan reset atau drop
database. Catat migration yang dijalankan dan hasilnya pada change record IT.

## 7. Pemeriksaan konfigurasi dan doctor

Jalankan lint pada tiga file production, script diagnosis bila tersedia dalam
paket, dan pemeriksaan web server. Doctor harus read-only, tidak menampilkan
password, key, token, atau isi file pribadi.

```bash
php -l /var/www/html/ami/application/config/production/config.php
php -l /var/www/html/ami/application/config/production/database.php
php -l /var/www/html/ami/application/config/production/ami.php
php /var/www/html/ami/scripts/ami_doctor.php
sudo nginx -t
sudo systemctl is-active php-fpm mariadb nginx
curl -I https://<domain>/
```

Hasil sukses yang diharapkan adalah `No syntax errors detected`, doctor
`PASS`, `syntax is ok`, status service `active`, dan HTTP `200` untuk `/`.
`/index.php/auth/login` boleh mengembalikan `405` bila route tersebut memang
khusus POST. Uji halaman login melalui `/` dan browser. Untuk sertifikat
self-signed saat uji sementara, gunakan `curl -k` hanya pada server uji dan
jangan menjadikannya konfigurasi produksi.

## 8. Smoke test aplikasi

Lakukan setelah konfigurasi dan database siap, dengan akun uji yang disetujui.

1. Buka `/` dan pastikan halaman login tampil.
2. Login, buka dashboard, lalu logout.
3. Buat atau pilih dokumen PPEPP, upload PDF kecil, dan pastikan status sukses.
4. Pastikan file berada di `<private_storage_path>/ppepp_documents/`.
5. Download dokumen melalui endpoint aplikasi, lalu hapus dari aplikasi.
6. Uji foto profil, bukti audit, dan session sesuai role yang tersedia.
7. Coba download dokumen private sebagai user tanpa hak akses, hasilnya harus
   ditolak.
8. Buka URL langsung ke file private, hasilnya harus ditolak Nginx dan tidak
   boleh mengunduh isi file.
9. Uji alur PPEPP Penetapan, Pelaksanaan, Pengendalian, dan Peningkatan sesuai
   data uji. Tidak ada tahap upload Evaluasi.
10. Periksa log aplikasi, PHP-FPM, dan Nginx tanpa mencatat secret.

## 9. Troubleshooting berbasis gejala

| Gejala | Pemeriksaan awal |
|---|---|
| `503 Production configuration is incomplete` | Lint tiga file, cek placeholder, URL HTTPS, key, session/log path, upload root, ownership, dan log PHP-FPM. |
| `500` atau blank page | `journalctl -u php-fpm`, log Nginx, log aplikasi, dan `php -l`; jangan menampilkan key pada laporan. |
| `Allowed memory size exhausted` saat bootstrap config | Cari self-reference seperti `config_item('base_url')` di file yang sedang memuat config. Periksa `config.php` production dan jangan autodetect host. |
| DB `1045` | Cek host, port, nama database, user aplikasi, grant, dan koneksi `SELECT 1`; jangan memakai akun DBA sebagai runtime. |
| Folder upload invalid atau not writable | `stat`, `namei`, user pool PHP-FPM, permission parent, SELinux `audit.log`, `semanage`, dan `restorecon`. |
| `move_uploaded_file` gagal | Cek folder kategori, `upload_tmp_dir`, `upload_max_filesize`, `post_max_size`, dan `client_max_body_size`. |
| File muncul di path yang salah | Bandingkan `private_storage_path` efektif, `realpath`, kategori, dan root Nginx. Jangan mencari fallback temp. |
| SSL gagal | Periksa path sertifikat, key, permission, nama domain, `nginx -t`, dan chain TLS. |
| SELinux menolak akses | Baca audit log, tetapkan fcontext yang tepat, lalu jalankan `restorecon`; jangan menonaktifkan SELinux. |
| HTTP `405` pada endpoint login | Endpoint itu mungkin menerima POST saja. Uji login melalui halaman `/`, bukan mengubah route. |
| Redirect `307` atau kembali ke login | Pastikan session writable, cookie HTTPS benar, URL dasar konsisten, dan request sudah login. |

Log yang relevan biasanya dapat dilihat dengan `journalctl -u php-fpm`,
`journalctl -u nginx`, log error Nginx, dan log aplikasi yang ditetapkan pada
`config.php`.

## 10. Backup, upgrade, dan rollback

Backup sebelum fresh setup lanjutan, migration, atau upgrade release:

- database lengkap sesuai prosedur DBA;
- `/var/www/html/ami/uploads/` atau private/public root yang dikonfigurasi;
- tiga file production aktif;
- log dan session bila diwajibkan kebijakan IT;
- checksum release dan catatan migration.

Untuk upgrade, ekstrak release baru ke direktori release sementara, salin hanya
konfigurasi production yang sudah direview, dan jangan menimpa `uploads/`, log,
session, atau database. Jalankan lint, doctor, migration yang disetujui, dan
smoke test sebelum mengubah symlink atau root aktif. Rollback berarti
mengembalikan root kode ke release sebelumnya dan mempertahankan data persisten.
Jangan menghapus data untuk memulihkan kode.

## Checklist go-live

- [ ] AlmaLinux 9.x, Nginx, PHP-FPM, ekstensi wajib, MariaDB/MySQL, dan unzip diverifikasi.
- [ ] ZIP, checksum, versi release, dan status vendor dicatat.
- [ ] Root aplikasi: `________________________________`.
- [ ] Domain HTTPS: `________________________________`.
- [ ] Database host/name: `________________________________`.
- [ ] Database user aplikasi: `________________________________`.
- [ ] Absolute upload/private root: `________________________________`.
- [ ] PHP-FPM socket dan user/group: `________________________________`.
- [ ] TLS certificate path: `________________________________`.
- [ ] Tiga file production diisi dan dilindungi, tanpa secret di dokumen handoff.
- [ ] `php -l`, doctor, `nginx -t`, status service, dan `curl` lulus.
- [ ] SELinux tetap `Enforcing`, tanpa `chmod 777`.
- [ ] Fresh install atau migration existing sudah dipilih dengan benar.
- [ ] Backup dan rollback release sudah diuji atau disetujui.
- [ ] Smoke test login, PPEPP upload/download/delete, foto, bukti, session, dan penolakan direct-private URL lulus.

## Catatan sumber dan batas verifikasi

Kontrak tiga file, production tetap, root upload, fresh versus existing, dan
status verifikasi di atas mengikuti prompt deployment native AlmaLinux ZIP.
Perubahan kode, packaging ZIP, dependency vendor, doctor, dan pengujian pada
AlmaLinux harus dibuktikan oleh agent atau Tim IT yang menjalankannya. Dokumen
ini tidak mengklaim eksekusi yang belum dilakukan.
