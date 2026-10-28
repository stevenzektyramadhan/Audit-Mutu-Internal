# Panduan Operasional Deploy Production AMI di AlmaLinux

> **Catatan Server Multisite:** Jika Anda melakukan deployment pada **server multisite (shared virtual host Nginx / Apache + PHP-FPM)** yang tidak menggunakan dedicated server AlmaLinux mandiri atau tidak ingin membuat file di luar webroot virtual host (`/etc/ami/production.php`), silakan merujuk ke panduan khusus multisite: [`docs/PANDUAN_DEPLOY_MULTISITE_CI3.md`](PANDUAN_DEPLOY_MULTISITE_CI3.md) (atau versi teks: [`docs/PANDUAN_DEPLOY_MULTISITE_CI3.txt`](PANDUAN_DEPLOY_MULTISITE_CI3.txt)).

Panduan ini adalah handoff operasional untuk operator IT pemula. Ikuti urutan A sampai J pada server baru AlmaLinux 9.4 atau lebih baru. Panduan ini menyiapkan Nginx, PHP-FPM 8.3, MariaDB 10.11, CodeIgniter 3, SELinux, TLS, backup, dan uji aplikasi.

## Cara membaca dokumen ini

- **CONTOH** berarti nilai contoh. Jangan dipakai tanpa diganti.
- **ISI** berarti ganti teks dalam kurung siku dengan nilai yang telah disetujui.
- **JALANKAN** berarti perintah yang boleh dijalankan pada server target setelah change window disetujui.
- Jangan menjalankan perintah dari dokumen ini pada workstation penulis. Dokumen ini tidak mengubah server, membuat database, memasang paket, atau melakukan deployment.
- Jangan menaruh password, API key, token, private key, dump database, atau file `.env` production di Git, tiket, command history, webroot, atau log.
- Akun `ami-deploy` hanya menulis release saat build. User runtime `ami` dan Nginx hanya membaca source; hanya direktori runtime yang boleh ditulis oleh user/group PHP-FPM.

## Batas penting dan status sinkronisasi

1. Source CodeIgniter 3 memakai root release yang berisi `index.php`. Jangan menganggap repository ini sudah memiliki pola `public/` terpisah. Root web adalah `/var/www/ami/current`, dan `index.php` berada langsung di sana.
2. Release aktif memakai `/var/www/ami/current`. Release sebelumnya disimpan sebagai direktori release terpisah agar rollback tidak menimpa file.
3. Runtime private storage memakai `/var/lib/ami/private`. Kategori yang wajib disiapkan adalah `audit_evidence`, `bukti_auditor`, `instrumen`, `penetapan`, `ppepp_documents`, `rtm_photos`, `spmi_source`, `tmp`, dan `user_photos`.
4. Public upload hanya memakai `/var/lib/ami/public_uploads/profil`. Jangan menaruh bukti, PPEPP, RTM, atau file private di public upload.
5. Session eksternal memakai `/var/lib/ami/sessions`. Log aplikasi memakai `/var/log/ami`.
6. Konfigurasi production dimuat melalui `APP_PRODUCTION_CONFIG_FILE`. Pada FPM, hanya `CI_ENV` dan `APP_PRODUCTION_CONFIG_FILE` yang menjadi environment aplikasi. File production memakai template native `deploy/config/production.php.example` dengan kelompok `app`, `database`, `email`, `google_drive`, dan `integrations`.
7. Salin template ke `/etc/ami/production.php`, lalu isi hanya pada server target melalui prosedur secret IT. File ini bukan `.env`, tidak berada di repository atau webroot, dan harus tetap `root:ami 0640`.
8. Development tidak berubah. Development tetap membaca `getenv()` dan boleh memakai `.env` lokal yang tidak dilacak Git. Jangan menyalin `.env` ke production.
9. `firebase/php-jwt` memiliki advisory keamanan level rendah. Catat versi, dampak, mitigasi, owner, dan tanggal review. Jangan menyatakan advisory selesai tanpa keputusan tertulis.

## Ringkasan target

| Komponen | Target |
| --- | --- |
| OS | AlmaLinux 9.4 atau lebih baru |
| Web | Nginx, tanpa syntax HTTP/2 wajib |
| PHP | PHP 8.3, PHP-FPM, Remi melalui EPEL dan CRB |
| Database | MariaDB 10.11/server stream resmi AlmaLinux yang disetujui |
| App root | `/var/www/ami/current`, berisi `index.php` |
| FPM user/group | User dan group khusus `ami` |
| FPM socket | `/run/php-fpm/ami.sock`, owner/group `nginx` agar Nginx dapat terhubung |
| Config file | `/etc/ami/production.php`, owner `root:ami`, mode `0640` |
| Config directory | `/etc/ami`, mode `0750` |
| Private | `/var/lib/ami/private` |
| Public | `/var/lib/ami/public_uploads/profil` |
| Session | `/var/lib/ami/sessions` |
| Log | `/var/log/ami` |
| Database runtime | DML account ke `127.0.0.1`, bukan DBA |
| Database import | Akun DBA terpisah, interaktif |
| Firewall | HTTP/HTTPS saja dari publik; jangan buka port 3306 |

# A. Preflight dan persetujuan

**Tujuan:** membuktikan server benar-benar baru untuk instalasi fresh dan semua nilai kerja sudah disetujui.

**ISI:** tiket change, hostname, IP, domain, zona waktu, contact on-call, release commit, akun deployment, nama database, nama user runtime FPM, dan lokasi backup terenkripsi.

**JALANKAN:**

```bash
cat /etc/os-release
uname -m
free -h
df -h / /var
timedatectl status
getenforce
sudo ss -lntup
sudo firewall-cmd --list-all
```

**Hasil yang diharapkan:** OS AlmaLinux 9.4+, arsitektur sesuai, waktu tersinkron, disk cukup, SELinux `Enforcing`, dan port 80/443/3306 memiliki rencana yang jelas.

**Jika gagal:** hentikan langkah. Perbaiki image OS, DNS, waktu, disk, akses sudo, SELinux, atau konflik port. Jangan melanjutkan dengan asumsi.

**Catatan database:** fresh gate tidak dijalankan pada preflight karena MariaDB belum tentu terpasang atau aktif. Jalankan gate tersebut pada langkah D, setelah service MariaDB aktif. `database_schema.sql` tidak membuat atau memilih database. Operator membuat database `ami` satu kali, lalu mengimpor schema dengan `--database=ami`. Jangan mengubah nama target tanpa juga mengubah `database.database` di `/etc/ami/production.php`.

# B. Repository paket dan software

**Tujuan:** memasang sumber paket yang disetujui, bukan menebak versi dari host lain.

AlmaLinux 9.4+ menyediakan stream MariaDB 10.11/server yang dapat digunakan setelah stream resmi disetujui. Jangan menyebutnya tidak tersedia. Gunakan stream resmi AlmaLinux yang telah diverifikasi oleh pemilik platform. PHP 8.3 memakai EPEL, CRB, dan Remi.

**JALANKAN setelah persetujuan repository:**

```bash
sudo dnf install -y dnf-plugins-core epel-release
sudo dnf config-manager --set-enabled crb
sudo dnf install -y https://rpms.remirepo.net/enterprise/remi-release-9.rpm
sudo dnf module reset -y php
sudo dnf module enable -y php:remi-8.3
sudo dnf module install -y mariadb:10.11/server
sudo dnf install -y nginx php php-cli php-fpm php-mysqlnd php-mbstring php-xml php-gd php-intl php-zip php-curl php-bcmath composer policycoreutils-python-utils firewalld rsync
php -v
php -m
composer --version
mariadb --version
nginx -v
```

**Hasil yang diharapkan:** PHP 8.3, PHP-FPM, ekstensi `mysqli`, `mbstring`, `xml`, `zip`, `gd`, `curl`, `fileinfo`, `intl`, `bcmath`, Composer, Nginx, MariaDB 10.11, SELinux tools, firewalld, dan rsync tersedia.

**Jika gagal:** jangan mengganti lock file atau memakai repository tidak disetujui. Simpan output, periksa EPEL/CRB/Remi, signature paket, stream, dan arsitektur. Eskalasi ke pemilik platform.

# C. User, release, Composer, dan root aplikasi

**Tujuan:** menempatkan release read-only pada path yang konsisten.

**JALANKAN:**

```bash
sudo groupadd --system ami
sudo useradd --system --gid ami --home-dir /var/lib/ami --shell /sbin/nologin ami
sudo useradd --create-home --shell /bin/bash ami-deploy
sudo install -d -o root -g ami -m 0755 /var/www/ami /var/www/ami/releases
sudo install -d -o root -g ami -m 0750 /var/lib/ami /etc/ami
sudo install -d -o ami-deploy -g ami -m 0750 /var/www/ami/releases/[ISI_RELEASE_ID]
sudo -u ami-deploy git clone --no-checkout [ISI_URL_REPOSITORY] /var/www/ami/releases/[ISI_RELEASE_ID]
cd /var/www/ami/releases/[ISI_RELEASE_ID]
sudo -u ami-deploy git checkout [ISI_COMMIT_YANG_DIREVIEW]
test -f index.php
test -f composer.lock
sudo -u ami-deploy composer validate --strict
sudo -u ami-deploy composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader --no-plugins --no-scripts
sudo -u ami-deploy composer audit --locked
sudo -u ami-deploy php -m
sudo chown -R ami-deploy:ami /var/www/ami/releases/[ISI_RELEASE_ID]
sudo find /var/www/ami/releases/[ISI_RELEASE_ID] -type d -exec chmod 0755 {} +
sudo find /var/www/ami/releases/[ISI_RELEASE_ID] -type f -exec chmod 0644 {} +
sudo ln -sfn /var/www/ami/releases/[ISI_RELEASE_ID] /var/www/ami/current
sudo chown -h root:ami /var/www/ami/current
sudo namei -l /var/www/ami/current/index.php
```

Review `composer.json` scripts before enabling any script. `--no-plugins --no-scripts` adalah default deployment yang aman. Jika aplikasi membutuhkan script tertentu, review dan approve satu per satu, lalu catat alasannya. Jangan menjalankan `composer update` pada server.

**Hasil yang diharapkan:** commit tercatat, `index.php` berada di root release, `vendor/` selesai, dan `current` menunjuk release yang disetujui. Parent `/var/www/ami` dan `/var/www/ami/releases` memakai `0755` agar Nginx dapat menelusuri symlink; source memakai `0755/0644` agar Nginx dapat membaca asset statis dan FPM `ami` dapat membaca PHP. Tidak ada account runtime yang dapat menulis release. Jika `namei` menunjukkan salah satu parent tidak dapat ditelusuri oleh `nginx` atau `ami`, STOP dan perbaiki path, bukan memakai `chmod -R 777`.

**Jika gagal:** jangan membuat source dapat ditulis oleh FPM. Hapus hanya release baru sesuai change procedure, periksa disk, permission, PHP, Composer, dan repository.

# D. Database fresh, akun, dan schema

**Tujuan:** memisahkan import DBA dari akun DML aplikasi dan mencegah overwrite data.

**D1. Fresh only, JALANKAN dengan akun DBA secara interaktif:**

```bash
sudo systemctl enable --now mariadb
sudo systemctl is-active mariadb
sudo mariadb -e "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = 'ami';"
sudo mariadb -u [ISI_DBA] -p
```

**Hasil gate yang diharapkan:** query `SCHEMATA` tidak menghasilkan baris. Jika terdapat `ami`, ini bukan fresh deployment: STOP, jangan jalankan bootstrap schema, dan gunakan jalur existing yang disetujui.

Pada prompt MariaDB, buat database dan akun runtime. Password dimasukkan pada prompt, bukan sebagai argumen.

```sql
CREATE DATABASE ami CHARACTER SET utf8 COLLATE utf8_general_ci;
CREATE USER 'ami_runtime'@'127.0.0.1' IDENTIFIED BY '<ISI_SECURE_PASSWORD_INTERAKTIF>';
GRANT SELECT, INSERT, UPDATE, DELETE ON ami.* TO 'ami_runtime'@'127.0.0.1';
FLUSH PRIVILEGES;
SHOW GRANTS FOR 'ami_runtime'@'127.0.0.1';
EXIT;
```

`<ISI_SECURE_PASSWORD_INTERAKTIF>` adalah penanda, bukan password untuk disalin. Gunakan metode operator/password manager yang tidak menaruh password di shell history atau SQL file. DBA tetap memiliki hak import, sedangkan runtime hanya DML. Tidak ada port 3306 yang dibuka ke jaringan publik.

**D2. Import schema sekali, dengan akun DBA:**

Tersedia dua jalur fresh, dan keduanya hanya untuk database yang benar-benar baru dan kosong:
1. `database_schema.sql`: Schema kanonik sampai migration `050`; setelah import, jalankan `scripts/bootstrap_super_admin.php` untuk memilih kredensial operator secara interaktif.
2. `database_init.sql`: Schema kanonik sampai migration `050` dengan satu akun starter `super_admin` yang passwordnya sudah di-hash.

`database_init.sql` bukan input upgrade atau pengganti database operasional yang sudah ada. Setelah akses pertama melalui akun starter, operator wajib segera mengganti atau merotasi kredensial awal. Jangan mencatat kredensial itu di dokumentasi, command history, log, atau tiket.

Pilih salah satu sesuai kebutuhan tim IT:

```bash
cd /var/www/ami/current
# Opsi A: database_init.sql untuk database kosong, dengan satu akun starter
sudo mariadb -u [ISI_DBA] -p --database=ami < database_init.sql

# ATAU Opsi B: database_schema.sql lalu bootstrap_super_admin.php untuk kredensial terpilih operator
# sudo mariadb -u [ISI_DBA] -p --database=ami < database_schema.sql
# sudo -u ami /usr/bin/php scripts/bootstrap_super_admin.php --config=/etc/ami/production.php

sudo mariadb -u [ISI_DBA] -p -e 'USE ami; SHOW TABLES;'
```

**Hasil yang diharapkan:** database `ami` berisi schema bootstrap dan tabel dapat dilihat. Jangan menjalankan migration individual setelah fresh import. CodeIgniter migration tetap nonaktif.

**D3. Existing database adalah jalur berbeda:**

- Jangan menjalankan `database_schema.sql` atau `database_init.sql`.
- Buat backup database, private storage, public uploads yang berisi data, session bila diperlukan, dan `APP_ENCRYPTION_KEY` melalui prosedur backup terkontrol.
- Bandingkan migration yang sudah diterapkan dengan `migrations/` memakai sejarah deployment, bukan tebakan dari nama tabel.
- Terapkan hanya migration yang belum diterapkan, numerik, satu per satu, dengan DBA.
- Berhenti pada error pertama. Jangan memakai `--force`, jangan mematikan foreign key checks, dan jangan menjalankan blok `DOWN` historis.

**Jika gagal:** hentikan traffic dan deployment. Jangan mengulang import atau migration secara buta. Pulihkan pasangan backup database dan storage setelah review DBA.

## Kontrak file server dan permission

Jalankan setiap perubahan pada salinan server, bukan pada file Git. ACL tambahan tidak diperlukan pada layout standar ini: source `0755/0644` dapat dibaca Nginx/FPM, sedangkan runtime dimiliki `ami`. Jika kebijakan lokal menambahkan ACL, dokumentasikan dengan `getfacl` dan jangan memberikan ACL write pada source untuk `ami` atau `nginx`.

| File server | Template repository | Command pemasangan | Parameter yang diubah | Validasi dan hasil | Jika gagal |
| --- | --- | --- | --- | --- | --- |
| `/etc/ami/production.php` | `/var/www/ami/current/deploy/config/production.php.example` | `sudo install -o root -g ami -m 0640 TEMPLATE /etc/ami/production.php` | Isi placeholder domain, key vault, DB, path, email/integrasi. Sebelum: `[GANTI_OLEH_TIM_IT_*]`; sesudah: nilai target dari vault. | `sudo stat -c '%U:%G %a %n' /etc/ami/production.php`; hasil `root:ami 640`. | STOP jika berada di webroot, dapat ditulis group/world, atau placeholder tertinggal. |
| `/etc/php-fpm.d/ami.conf` | `/var/www/ami/current/deploy/php-fpm/ami.conf` | `sudo install -o root -g root -m 0644 TEMPLATE /etc/php-fpm.d/ami.conf` | Tidak ada secret. Pastikan user `ami`, socket `/run/php-fpm/ami.sock`, hanya dua `env[...]`. | `sudo php-fpm -t`; hasil sukses lalu socket `nginx:nginx 660`. | Jangan restart; periksa path/template dan journal. |
| `/etc/nginx/conf.d/ami.conf` fase ACME | `/var/www/ami/current/deploy/nginx/ami-acme-bootstrap.conf` | `sudo install -o root -g root -m 0644 TEMPLATE /etc/nginx/conf.d/ami.conf` | Ganti domain dan ACME webroot. | `sudo nginx -t`; hasil sukses, hanya challenge HTTP tersedia. | Jangan start/reload Nginx bila test gagal. |
| `/etc/nginx/conf.d/ami.conf` fase HTTPS | `/var/www/ami/current/deploy/nginx/ami.conf` | `sudo install -o root -g root -m 0644 TEMPLATE /etc/nginx/conf.d/ami.conf` | Ganti domain, root, ACME/public-upload path, certificate path. Sebelum: `[GANTI_OLEH_TIM_IT_*]`; sesudah: path target. | `sudo nginx -t`; hasil sukses sebelum reload. | Kembalikan file ACME atau config valid terakhir, lalu ulangi setelah path sertifikat benar. |
| `/var/www/ami/current` | symlink ke `/var/www/ami/releases/[ISI_RELEASE_ID]` | `sudo ln -sfn RELEASE /var/www/ami/current` | Release ID/commit ter-review. | `sudo namei -l /var/www/ami/current/index.php`; parent `0755`, source dir/file `0755/0644`. | STOP jika `ami`/`nginx` mendapat write source atau tidak dapat traverse/read. |

**Validasi ownership dan ACL:**

```bash
sudo stat -c '%U:%G %a %n' /var/www/ami /var/www/ami/releases /var/www/ami/current /var/lib/ami /etc/ami
sudo getfacl -p /var/www/ami/current /var/lib/ami/private /var/lib/ami/public_uploads/profil
```

**Hasil yang diharapkan:** tidak ada ACL write tambahan pada source untuk `ami`/`nginx`; `ami` memiliki write hanya pada runtime; Nginx dapat read/traverse source dan read public profile. Jika hasil berbeda, STOP dan perbaiki ACL spesifik, bukan `chmod -R 777`.

# E. Template config production dan PHP-FPM

**Tujuan:** FPM hanya menerima dua environment variable, lalu aplikasi membaca file config non-secret yang aman.

**Schema native:** gunakan persis `deploy/config/production.php.example` dari release yang diverifikasi. Kelompok final adalah `app`, `database`, `email`, `google_drive`, dan `integrations`; jangan mengubah nama key atau menambahkan dotenv. Siapkan sebelum mengedit: domain HTTPS, DB runtime host/user/password/database, encryption key dari vault, lokasi storage/log/session, pengirim Brevo bila dipakai, dan parameter Google Drive/PDDIKTI bila dipakai.

**JALANKAN setelah template tersedia, tanpa mencetak isi secret:**

```bash
test -f /var/www/ami/current/deploy/config/production.php.example
sha256sum /var/www/ami/current/deploy/config/production.php.example
sudo install -d -o root -g ami -m 0750 /etc/ami
sudo install -o root -g ami -m 0640 /var/www/ami/current/deploy/config/production.php.example /etc/ami/production.php
sudo install -o root -g root -m 0644 /var/www/ami/current/deploy/php-fpm/ami.conf /etc/php-fpm.d/ami.conf
sudo -u ami /usr/bin/php -l /etc/ami/production.php
sudo php-fpm -t
```

Salinan `/etc/ami/production.php` harus diisi melalui mekanisme non-secret yang disetujui. Walaupun file template disebut non-secret, file production dapat memuat password atau key, sehingga tetap perlakukan sebagai secret. Jangan menaruh nilai pada command line. Jangan mengubah file template di Git.

`install` di atas memasang template pool yang benar. Jangan menyalin blok berikut secara manual bila template sudah dipasang; blok hanya dipakai untuk pemeriksaan nilai yang harus sama.

**JALANKAN konfigurasi pool, setelah mapping template selesai:**

```ini
[ami]
user = ami
group = ami
listen = /run/php-fpm/ami.sock
listen.owner = nginx
listen.group = nginx
listen.mode = 0660
clear_env = yes
pm = ondemand
pm.max_children = 10
pm.process_idle_timeout = 10s
pm.max_requests = 500
env[CI_ENV] = production
env[APP_PRODUCTION_CONFIG_FILE] = /etc/ami/production.php
php_admin_value[session.save_path] = /var/lib/ami/sessions
php_admin_value[upload_tmp_dir] = /var/lib/ami/private/tmp
php_admin_value[sys_temp_dir] = /var/lib/ami/private/tmp
php_admin_value[upload_max_filesize] = 20M
php_admin_value[post_max_size] = 25M
php_admin_value[memory_limit] = 256M
php_admin_value[max_execution_time] = 120
```

Jangan menambahkan `env[DB_PASSWORD]`, `env[APP_ENCRYPTION_KEY]`, atau key lain ke pool setelah migrasi config ini. Hanya `CI_ENV` dan `APP_PRODUCTION_CONFIG_FILE` yang menjadi FPM environment. Pool dan file config sama-sama harus melalui review change.

**JALANKAN validasi:**

```bash
sudo php-fpm -t
sudo systemctl enable php-fpm
sudo systemctl restart php-fpm
sudo systemctl is-active php-fpm
sudo stat -c '%U:%G %a %n' /run/php-fpm/ami.sock
sudo stat -c '%U:%G %a %n' /etc/ami /etc/ami/production.php /etc/php-fpm.d/ami.conf
```

**Hasil yang diharapkan:** lint PHP menampilkan `No syntax errors detected` tanpa menampilkan nilai file, `php-fpm -t` lulus, pool aktif, `/etc/ami` mode `0750`, config `root:ami 0640`, socket owner/group `nginx`, dan tidak ada secret tercetak.

**Jika gagal:** 503 berarti config production tidak termuat atau gate wajib gagal. 502 berarti socket/pool gagal. Periksa journal dan permission tanpa mencetak environment. Jangan menurunkan `CI_ENV` ke development.

# F. Runtime directory, ownership, dan SELinux

**Tujuan:** memberi FPM akses tulis hanya pada runtime yang diperlukan.

**JALANKAN:**

```bash
sudo install -d -o ami -g ami -m 0700 /var/lib/ami/private
sudo install -d -o ami -g ami -m 0700 /var/lib/ami/private/{audit_evidence,bukti_auditor,instrumen,penetapan,ppepp_documents,rtm_photos,spmi_source,tmp,user_photos}
sudo install -d -o ami -g ami -m 0755 /var/lib/ami/public_uploads /var/lib/ami/public_uploads/profil
sudo install -d -o ami -g ami -m 0700 /var/lib/ami/sessions
sudo install -d -o ami -g ami -m 0750 /var/log/ami
sudo semanage fcontext -a -t httpd_sys_rw_content_t '/var/lib/ami/private(/.*)?'
sudo semanage fcontext -a -t httpd_sys_rw_content_t '/var/lib/ami/public_uploads(/.*)?'
sudo semanage fcontext -a -t httpd_sys_rw_content_t '/var/lib/ami/sessions(/.*)?'
sudo semanage fcontext -a -t httpd_sys_rw_content_t '/var/log/ami(/.*)?'
sudo semanage fcontext -a -t httpd_sys_content_t '/etc/ami/production.php'
sudo restorecon -Rv /var/lib/ami /var/log/ami /etc/ami
sudo setsebool -P httpd_can_network_connect_db 1
```

Label `httpd_sys_content_t` untuk config adalah contoh label read-only custom yang wajib diverifikasi dengan kebijakan SELinux setempat. Jika policy membutuhkan tipe read-only khusus, gunakan tipe yang disetujui platform. Jangan memberi label writable pada config secret. `httpd_can_network_connect_db` diperlukan untuk koneksi database lokal/remote sesuai arsitektur. Boolean outbound lain hanya diaktifkan jika integrasi yang disetujui memang perlu, misalnya API Brevo, PDDIKTI, Cloudflare, atau Google Drive. Jangan mengaktifkan outbound secara umum tanpa kebutuhan.

**Hasil yang diharapkan:** private, public profil, session, log, dan temp dapat ditulis user `ami`; config dapat dibaca FPM tetapi tidak ditulis; private tidak memiliki alias publik.

**Jika gagal:** periksa `ls -ld`, `namei -l`, `matchpathcon`, dan `ausearch -m avc -ts recent`. Perbaiki ownership/label secara spesifik. Jangan `chmod 777` dan jangan menonaktifkan SELinux.

# G. Nginx, firewall, HTTP sementara, TLS, dan renewal

**Tujuan:** memastikan ACME dapat berjalan sebelum HTTPS dipaksa, lalu menutup HTTP setelah sertifikat terbit.

1. Fase pertama: buat ACME webroot dan salin `deploy/nginx/ami-acme-bootstrap.conf` ke `/etc/nginx/conf.d/ami.conf`, isi domain dan ACME webroot, lalu validasi HTTP challenge. Template ini sengaja tidak melayani aplikasi atau HTTPS.
2. Setelah sertifikat terbit: ganti file aktif dengan `deploy/nginx/ami.conf`. Isi domain, root `/var/www/ami/current`, socket, alias `/var/lib/ami/public_uploads`, dan path sertifikat pada salinan saja.
3. HTTP/2 bukan syarat deployment. Ikuti komentar versi Nginx pada template bila organisasi mengaktifkannya.

**JALANKAN fase HTTP:**

```bash
sudo install -d -o root -g nginx -m 0750 /var/lib/ami/acme
sudo install -o root -g root -m 0644 /var/www/ami/current/deploy/nginx/ami-acme-bootstrap.conf /etc/nginx/conf.d/ami.conf
# Ganti placeholder pada SALINAN /etc/nginx/conf.d/ami.conf saja.
sudo firewall-cmd --permanent --add-service=http
sudo firewall-cmd --permanent --add-service=https
sudo firewall-cmd --reload
sudo nginx -t
sudo systemctl enable --now nginx
curl -I http://[ISI_DOMAIN]/.well-known/acme-challenge/[ISI_FILE_UJI]
```

**Hasil yang diharapkan:** `nginx -t` lulus, challenge dapat dijangkau, dan port 3306 tidak muncul sebagai service firewall publik.

**JALANKAN issuance:**

```bash
sudo dnf install -y certbot python3-certbot-nginx
sudo certbot certonly --webroot -w [ISI_ACME_WEBROOT] -d [ISI_DOMAIN]
sudo certbot certificates
sudo install -o root -g root -m 0644 /var/www/ami/current/deploy/nginx/ami.conf /etc/nginx/conf.d/ami.conf
# Ganti semua [GANTI_OLEH_TIM_IT] pada SALINAN; set root=/var/www/ami/current,
# public uploads=/var/lib/ami/public_uploads, socket=/run/php-fpm/ami.sock,
# dan path certificate hasil Certbot.
```

Setelah sertifikat ada, pasang konfigurasi HTTPS template, set certificate path, tambahkan redirect HTTP ke HTTPS, lalu:

```bash
sudo nginx -t
sudo systemctl reload nginx
curl -I http://[ISI_DOMAIN]/
curl -I https://[ISI_DOMAIN]/
sudo certbot renew --dry-run
```

Renewal harus memakai timer systemd Certbot dan deploy hook yang menjalankan `nginx -t` lalu reload Nginx hanya bila sertifikat berubah. Verifikasi timer:

```bash
systemctl list-timers --all | grep certbot
```

**Hasil yang diharapkan:** HTTP mengarah ke HTTPS, sertifikat valid, PHP hanya dieksekusi melalui `index.php`, `/uploads/profil/` hanya allowlist gambar, dan folder private mengembalikan 404/403.

**Jika gagal:** jangan reload konfigurasi gagal dan jangan membuka traffic umum. Periksa DNS, port 80, ACME root, socket, certificate path, permission alias, dan log Nginx.

# H. Service, admin pertama, dan smoke test

**JALANKAN service:**

```bash
sudo nginx -t
sudo systemctl is-enabled mariadb php-fpm nginx
sudo systemctl is-active mariadb php-fpm nginx
sudo ss -lx | grep /run/php-fpm/ami.sock
```

Urutan aktivasi yang benar adalah MariaDB pada langkah D, PHP-FPM setelah `php-fpm -t` pada langkah E, lalu Nginx setelah `nginx -t` pada langkah G. Jangan menjalankan `enable --now` untuk service berikutnya sebelum test konfigurasinya lulus.

**Admin pertama:** jalankan hanya dari terminal operator pada root release. Jangan menjalankan langkah ini di sini, jangan memasukkan credential ke command, dan jangan memakai SQL manual.

```bash
cd /var/www/ami/current
sudo -u ami /usr/bin/php scripts/bootstrap_super_admin.php --config=/etc/ami/production.php
```

Script memuat host/database/username/password dari `/etc/ami/production.php` yang dapat dibaca group `ami`, lalu hanya meminta nama/email/password admin secara interaktif. Path config boleh diganti dengan `--config=/path/absolut` untuk server target lain. Tidak ada password dikirim melalui argumen, ditampilkan, atau ditulis ke history. Script menolak duplikasi sesuai kontraknya. Jika gagal, periksa file config, row existing, dan constraint; jangan mengulang insert secara buta.

**Smoke test browser dan terminal:**

```bash
curl -I https://[ISI_DOMAIN]/
curl -i -X GET https://[ISI_DOMAIN]/index.php/auth/login
curl -i -X POST https://[ISI_DOMAIN]/index.php/auth/login
curl -I https://[ISI_DOMAIN]/application/config/config.php
curl -I https://[ISI_DOMAIN]/uploads/profil/[ISI_FILE_GAMBAR]
```

GET login boleh menghasilkan halaman login. POST tanpa CSRF diharapkan `403`. Jangan menyimpulkan login sehat dari `curl` tanpa sesi dan token; gunakan browser untuk login nyata.

**Matriks smoke module-specific:**

| Area | Uji sebagai | Hasil wajib |
| --- | --- | --- |
| Login | operator uji | GET login 200, POST tanpa CSRF 403, login valid berhasil |
| Dashboard | setiap role | redirect sesuai role, menu tidak melewati permission |
| Bukti SPMI | auditee/auditor | upload, download owner/role benar berhasil; non-owner ditolak |
| Dokumen PPEPP | admin LPMPI | role berwenang dapat upload/download; auditor/auditee ditolak |
| RTM | LPMPI | foto private, lifecycle draft/resolved, download berwenang |
| Profil/logo | admin | gambar profil dapat dibaca melalui public allowlist |
| Private | browser anonim | kategori private mengembalikan 403/404, tidak pernah file |
| Routing | browser | route valid masuk CI3, file source sensitif 404 |
| Database | aplikasi | operasi baca/tulis yang disetujui berhasil melalui runtime DML |
| Upload | operator uji | file valid diterima, extension/script ditolak, batas aplikasi berlaku |

**Hasil yang diharapkan:** 200/redirect yang sesuai, 403 untuk CSRF/permission, 404 untuk private/source, tidak ada 500/502, cookie `Secure`, dan tidak ada secret di response/log. Jika aplikasi belum memiliki akun selain bootstrap awal, buat akun uji hanya melalui alur aplikasi yang telah disetujui; jangan mengubah database langsung untuk smoke test.

# I. Backup, restore, rollback, dan troubleshooting

**Backup database, JALANKAN dengan akun DBA:**

```bash
sudo install -d -o root -g root -m 0700 [ISI_BACKUP_TERENKRIPSI]
sudo mariadb-dump --single-transaction --routines --events --triggers -u [ISI_DBA] -p ami | gpg --symmetric --output [ISI_BACKUP_TERENKRIPSI]/ami.sql.gpg
sudo tar -C /var/lib/ami -czf - private public_uploads | gpg --symmetric --output [ISI_BACKUP_TERENKRIPSI]/ami-storage.tgz.gpg
sudo sha256sum [ISI_BACKUP_TERENKRIPSI]/ami.sql.gpg [ISI_BACKUP_TERENKRIPSI]/ami-storage.tgz.gpg | sudo tee [ISI_BACKUP_TERENKRIPSI]/SHA256SUMS >/dev/null
```

Simpan `APP_ENCRYPTION_KEY` melalui vault/backup key yang konsisten. Uji restore pada host/database terisolasi, bukan langsung ke production: decrypt ke filesystem sementara dengan permission `0700`, buat database uji kosong, lalu jalankan `mariadb --database=ami_restore < dump.sql`, verifikasi `SHOW TABLES`, dan hapus plaintext sementara menggunakan prosedur organisasi. Catat waktu restore, checksum, operator, dan hasil. Jangan mendekripsi backup ke webroot atau menyimpan passphrase di command/history.

**Rollback aman:** hentikan cutover, pertahankan database dan storage pasangan yang konsisten, ubah symlink `current` ke release sebelumnya, validasi Nginx/FPM, smoke test, lalu buka traffic bertahap. Jangan mengganti encryption key ketika rollback. Jika schema atau data berubah, rollback code saja tidak cukup; gunakan prosedur backup dan migration reverse yang telah disetujui.

| Gejala | Periksa | Respons |
| --- | --- | --- |
| 403 | CSRF, role, SELinux, Nginx deny | Bedakan 403 CSRF yang diharapkan dari permission salah; baca log aman |
| 404 | route, `current`, alias profil, private deny | Pastikan root berisi `index.php`; private 404 adalah hasil yang benar |
| 500 | log aplikasi, config file, PHP extension, DB query | Baca error pertama; jangan aktifkan display error production |
| 502 | socket, service FPM, owner socket, SELinux | Cocokkan `fastcgi_pass` dengan `/run/php-fpm/ami.sock` |
| Session/login loop | `/var/lib/ami/sessions`, cookie secure, base URL HTTPS | Cek owner `ami`, URL, dan cookie, bukan menurunkan HTTPS |
| Upload gagal | PHP/Nginx limit, DB setting, temp, SELinux | Batas DB tidak boleh melampaui PHP; cek `tmp` dan label |
| Database gagal | grant `127.0.0.1`, DB host, boolean, service | Uji `SELECT 1` tanpa mencetak password; jangan buka 3306 publik |
| Private terbuka | Nginx root/alias/location | Tutup traffic, simpan bukti, uji semua kategori private |
| API outbound gagal | SELinux outbound boolean, DNS, CA, endpoint | Aktifkan boolean hanya untuk integrasi yang disetujui |

# J. Handoff, validation, dan blocker

**JALANKAN final validation:**

```bash
sudo nginx -t
sudo php-fpm -t
sudo systemctl is-active mariadb php-fpm nginx
sudo firewall-cmd --list-all
sudo ausearch -m avc -ts recent
sudo journalctl -u mariadb -u php-fpm -u nginx --since '[ISI_WAKTU_MULAI]' --no-pager
```

Handoff wajib menyertakan commit release, hash template config, versi paket, hasil fresh gate, hasil schema, grant runtime, label SELinux, hasil Certbot dry-run, smoke matrix, backup checksum, restore drill, rollback owner, monitoring, dan on-call. Jangan menyertakan secret.

**Status validasi dokumen ini:** target AlmaLinux, paket, TLS, service, database, SELinux, dan smoke test belum dijalankan pada server target. Advisory `firebase/php-jwt` masih harus dinilai oleh owner. Handoff production tetap **blocked** sampai langkah target, backup/restore drill, dan matriks smoke test memiliki bukti.

## Checklist dan berita acara sign-off

Gunakan checklist ini pada tiket perubahan. Semua nilai wajib harus `YA`; satu `TIDAK` berarti STOP.

- [ ] Domain, DNS A/AAAA, firewall 80/443, dan ACME challenge tervalidasi.
- [ ] PHP 8.3/FPM, Composer, MariaDB 10.11, ekstensi wajib, dan package version dicatat.
- [ ] Release commit, `composer validate`, `composer install --no-dev`, hash release, dan symlink `current` tervalidasi.
- [ ] `/etc/ami/production.php` berada di luar webroot, `root:ami 0640`; tidak ada `.env` production.
- [ ] Semua placeholder `[GANTI_OLEH_TIM_IT...]` telah diganti hanya pada file server yang relevan; lint config native sebagai user `ami` lulus tanpa menampilkan secret.
- [ ] `php-fpm -t`, socket `nginx:nginx 0660`, `nginx -t`, TLS, dan renewal dry-run lulus.
- [ ] Database fresh gate atau change approval existing database disetujui; akun runtime hanya DML.
- [ ] Storage/session/log, SELinux labels, backup encryption, restore drill, dan no-public-private proof lulus.
- [ ] Bootstrap super-admin berhasil satu kali; login/logout dan smoke matrix per role lulus.
- [ ] Advisory `firebase/php-jwt` dicatat dengan keputusan accept/mitigate/upgrade, owner, dan tanggal review.

**Berita acara:** Tanggal/waktu: `[ISI]`; change/tiket: `[ISI]`; domain/release commit: `[ISI]`; operator IT: `[ISI]`; DBA: `[ISI]`; hasil preflight: `[LULUS/GAGAL]`; hash backup dan restore drill: `[ISI]`; hasil `php-fpm -t`/`nginx -t`/Certbot/smoke: `[ISI]`; advisory decision: `[ISI]`; rollback owner: `[ISI]`; persetujuan IT: `[NAMA/TANDA TANGAN]`; persetujuan pemilik aplikasi: `[NAMA/TANDA TANGAN]`.

## Tabel migrasi konfigurasi native

Semua baris selain selector FPM berada di `/etc/ami/production.php`, yang memakai schema `deploy/config/production.php.example`. Development tetap memakai variable lama melalui `getenv()`; production tidak memakai fallback secret environment.

| Konfigurasi lama | File konfigurasi native baru | Parameter yang diubah | Contoh nilai | Keterangan |
| --- | --- | --- | --- | --- |
| `CI_ENV` | `/etc/php-fpm.d/ami.conf` | `env[CI_ENV]` | `production` | Selector awal bootstrap; non-secret. |
| `APP_PRODUCTION_CONFIG_FILE` | `/etc/php-fpm.d/ami.conf` | `env[APP_PRODUCTION_CONFIG_FILE]` | `/etc/ami/production.php` | Pointer non-secret ke file native di luar webroot. |
| `APP_BASE_URL` | `/etc/ami/production.php` | `app.base_url` | `https://[ISI_DOMAIN]/` | HTTPS dan slash penutup wajib. |
| `APP_COOKIE_SECURE` | `/etc/ami/production.php` | `app.cookie_secure` | `true` | Wajib true. |
| `APP_ENCRYPTION_KEY` | `/etc/ami/production.php` | `app.encryption_key` | `[ISI_DARI_VAULT]` | Persisten; jangan ganti tanpa recovery. |
| `APP_LOG_THRESHOLD` | `/etc/ami/production.php` | `app.log_threshold` | `1` | Harus non-zero. |
| `APP_LOG_PATH` | `/etc/ami/production.php` | `app.log_path` | `/var/log/ami` | Di luar webroot dan writable. |
| `APP_PRIVATE_STORAGE_PATH` | `/etc/ami/production.php` | `app.private_storage_path` | `/var/lib/ami/private` | Tidak boleh berada di webroot. |
| `APP_PUBLIC_UPLOADS_PATH` | `/etc/ami/production.php` | `app.public_uploads_path` | `/var/lib/ami/public_uploads` | Hanya `profil` dialias Nginx. |
| `SPMI_EVIDENCE_STORAGE_BACKEND` | `/etc/ami/production.php` | `app.spmi_evidence_storage_backend` | `local` | `google_drive` hanya setelah konfigurasi valid. |
| Session baru | `/etc/ami/production.php` | `app.session_save_path` | `/var/lib/ami/sessions` | Wajib ada dan writable sebelum FPM aktif. |
| `DB_HOST` | `/etc/ami/production.php` | `database.hostname` | `127.0.0.1` | Tidak ada parameter port pada source. |
| `DB_USERNAME` | `/etc/ami/production.php` | `database.username` | `[ISI_RUNTIME_USER]` | Akun DML, bukan DBA. |
| `DB_PASSWORD` | `/etc/ami/production.php` | `database.password` | `[ISI_DARI_VAULT]` | Secret; tidak di argv/history. |
| `DB_DATABASE` | `/etc/ami/production.php` | `database.database` | `ami` | Fresh bootstrap hanya untuk database baru `ami`. |
| `BREVO_API_KEY` | `/etc/ami/production.php` | `email.brevo_api_key` | kosong | Isi hanya bila reset password email disetujui. |
| `MAIL_FROM_ADDRESS` | `/etc/ami/production.php` | `email.from_address` | `noreply@[ISI_DOMAIN]` | Sender terverifikasi. |
| `MAIL_FROM_NAME` | `/etc/ami/production.php` | `email.from_name` | `AMI` | Opsional. |
| `GOOGLE_DRIVE_AUTH_MODE` | `/etc/ami/production.php` | `google_drive.auth_mode` | `service_account` | OAuth dilarang production. |
| `GOOGLE_DRIVE_EVIDENCE_FOLDER_ID` | `/etc/ami/production.php` | `google_drive.evidence_folder_id` | `[ISI_FOLDER_ID]` | Hanya bila backend Drive dipakai. |
| `GOOGLE_DRIVE_SERVICE_ACCOUNT_JSON_PATH` | `/etc/ami/production.php` | `google_drive.service_account_json_path` | `/etc/ami/drive.json` | File di luar webroot. |
| `GOOGLE_DRIVE_OAUTH_CLIENT_SECRET_JSON_PATH`, `GOOGLE_DRIVE_OAUTH_REFRESH_TOKEN_JSON_PATH` | `/etc/ami/production.php` | key OAuth | `''` | Harus kosong pada production. |
| `PDDIKTI_BASE_URLS` | `/etc/ami/production.php` | `integrations.pddikti_base_urls` | `https://[ISI_API]/api` | Override HTTPS opsional. |

`Turnstile` bukan environment variable. Site key dan encrypted secret berada pada `system_settings`, dengan key enkripsi dari `APP_ENCRYPTION_KEY`. Konfigurasi PPEPP dan RTM bukan environment variable: dokumen PPEPP, kategori, metadata RTM, foto, lifecycle, dan batas upload berada pada schema/database serta private storage. Jangan menambahkan `SMTP_*`, `MAIL_TRANSPORT`, `PPEPP_*`, atau `RTM_*` sebagai key production tanpa perubahan source dan schema yang disetujui.

## Sumber yang diperiksa

- `index.php`, `application/config/config.php`, `application/config/database.php`, `application/config/email.php`
- `application/helpers/app_helper.php`, `application/config/spmi_ppepp.php`
- `application/services/System_settings_service.php`, `Spmi_ppepp_documents_service.php`, `Spmi_rtm_service.php`
- `database_schema.sql`, `migrations/`, `deploy/php-fpm/ami.conf`, `deploy/nginx/ami.conf`
- `docs/DEPLOYMENT_NON_DOCKER.md`, `.env.example`, `README.md`
- [Remi configuration documentation](https://blog.remirepo.net/pages/Config-en)
- [Red Hat MariaDB documentation](https://docs.redhat.com/en/documentation/red_hat_enterprise_linux/9/html/configuring_and_using_database_servers/using-mariadb_configuring-and-using-database-servers)
- [Certbot Nginx instructions](https://certbot.eff.org/instructions?os=pip&ws=nginx)
