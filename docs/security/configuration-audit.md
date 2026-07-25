# M1-02 Security Configuration Audit

- Tanggal audit: 2026-07-24
- Cakupan: source repository dan runtime development lokal
- Production runtime/data: belum diinspeksi
- Status: Selesai untuk baseline M1-02; residual gap dicatat untuk task berikutnya

## Ringkasan hasil

Konfigurasi production sekarang fail-closed untuk secret wajib, URL/Host, debug, lokasi storage/log/session, dan credential database berbahaya. Setiap request memperoleh correlation ID server-generated yang dikirim sebagai `X-Request-ID` dan ditambahkan ke log. Error 5xx yang dirender aplikasi tidak menampilkan exception, stack trace, atau filesystem path pada production.

Audit ini tidak menyatakan deployment production sudah aman. Tim deployment tetap harus membuktikan TLS, permission/ACL, web-server deny rules, backup, database privilege, dan proxy behavior pada host aktual.

## Matriks audit

| Area | Kondisi baseline | Hasil M1-02 | Status |
|---|---|---|---|
| `ENVIRONMENT` | `CI_ENV` wajib dan hanya menerima `development`, `testing`, atau `production`. | Dipertahankan; nilai kosong/tidak dikenal gagal dengan pesan 503 generik. | Pass |
| Display error | `development` menampilkan error; `testing` dan `production` mematikannya. | Production juga memvalidasi `display_errors` tetap nonaktif. | Pass |
| Logging | Production default threshold 1 dan path eksternal diwajibkan, tetapi threshold lebih tinggi masih diterima dan belum ada correlation ID. | Production hanya menerima threshold 1; file log baru mode `0600`; semua log diberi request ID, newline dinormalisasi, pola secret umum di-redact, dan pesan dibatasi 8 KiB. | Fixed |
| Encryption key | Berasal dari `APP_ENCRYPTION_KEY`; production menolak nilai kosong. | Dipertahankan; tidak ada fallback secret di source atau `.env.example`. | Pass |
| Session driver | File driver dengan save path di bawah `application/cache/sessions`. | Production mewajibkan `APP_SESSION_SAVE_PATH` yang sudah ada, writable, dan berada di luar document root. | Fixed |
| Session expiration | Baseline 7.200 detik. | M1-03 mengubah lifetime maksimum menjadi 28.800 detik dengan enforcement idle 1.800 detik dan absolute 28.800 detik pada guard. | Fixed by M1-03 |
| Session regeneration | Regenerasi periodik 300 detik; login meregenerasi dengan destroy. Auto-regeneration belum destroy. | M1-03 mengaktifkan destroy pada auto-regeneration dan membuktikan cookie ID berubah setelah login. | Fixed by M1-03 |
| Cookie Secure | Dapat diatur lewat environment dan production mewajibkan `true`. | Dipertahankan. | Pass |
| Cookie HttpOnly | Aktif. | Dipertahankan. | Pass |
| Cookie SameSite | `Lax` pada cookie umum dan session. | Dipertahankan; perubahan harus diuji bersama workflow login/CSRF. | Pass |
| CSRF | Aktif global tanpa URI exclusion. | Dipertahankan; GET dengan side effect tetap harus dibenahi pada task workflow terkait. | Pass / known gap |
| Base URL | Production mewajibkan nilai dan prefix HTTPS, tetapi format lain belum divalidasi penuh. | Wajib URL HTTPS valid dengan host sah, tanpa user info, query, atau fragment. | Fixed |
| Allowed hosts | Belum ada pemeriksaan Host aplikasi. | `APP_ALLOWED_HOSTS` menerima daftar hostname eksplisit; default hanya host dari base URL. Request Host lain gagal tertutup. | Fixed |
| Proxy headers | `proxy_ips` kosong sehingga forwarded client IP tidak dipercaya oleh Input class. | `APP_TRUSTED_PROXIES` menerima IP/CIDR tervalidasi. Nilai kosong tetap default. Web server wajib menghapus/menimpa forwarded headers dari client. | Partial; deployment verification |
| Upload limits | Controller menerapkan 2 MiB import, 4 MiB logo, dan 5 MiB dokumen/bukti. PHP/web-server limit tidak didefinisikan repo. | M1-06 memusatkan limit kategori (2/4/5/10 MiB), extension/content validation, random name, checksum, dan retention. Production tetap wajib memasang hard cap PHP/web server. | Fixed in application; deployment cap verification |
| Database credentials | Production memerlukan seluruh environment non-empty; `db_debug` nonaktif. Password default dan user `root` masih dapat diterima. Compose juga membawa password development literal. | User DB privileged dan password default/known-development ditolak; query recording dimatikan pada production; Compose mewajibkan password development dari environment tanpa fallback. | Fixed |
| Private storage path | Production mewajibkan path existing, writable, dan di luar document root. | Validasi containment dibuat portable untuk separator Windows/Linux; tetap fail-closed. | Pass |
| File permissions | Writable diperiksa; log file default `0644`; directory ownership deployment tidak dapat dibuktikan dari source. | Production log file memakai `0600`. Permission direktori/ACL tetap langkah wajib runbook. | Partial; deployment verification |
| Production debug | `display_errors=0`, `db_debug=false`, tetapi log threshold 2–4 dapat diaktifkan. | Threshold selain 1 ditolak dan query recording dimatikan. | Fixed |
| Default account | Schema dan seed tidak membuat user, tetapi README masih memuat kredensial contoh mudah ditebak. | Daftar kredensial dihapus; repository menegaskan tidak ada akun default. | Fixed |
| Error response | Production menyembunyikan PHP exception karena display error nonaktif; framework error 5xx belum mempunyai template aman khusus. | `MY_Exceptions` mengganti error 5xx dengan pesan generik + request ID; detail teknis tetap hanya di log private. | Fixed |
| Correlation ID | Belum ada. | ID 128-bit dibuat server per request, dikirim melalui `X-Request-ID`, dan otomatis ditambahkan oleh `MY_Log`. | Fixed |

## Environment production

`.env.example` adalah daftar referensi dan sengaja tidak berisi secret. Aplikasi tidak memuat file `.env` otomatis. Inject nilai melalui Apache/PHP-FPM, Windows service environment, container orchestrator, atau secret manager.

| Variable | Wajib production | Aturan |
|---|---:|---|
| `CI_ENV` | Ya | Harus `production`. |
| `APP_BASE_URL` | Ya | HTTPS valid; tanpa credential, query, atau fragment. |
| `APP_ALLOWED_HOSTS` | Disarankan eksplisit | Daftar hostname dipisahkan koma. Jika kosong, hanya host base URL yang diterima. Tidak mendukung wildcard. |
| `APP_TRUSTED_PROXIES` | Hanya bila memakai proxy | IP/CIDR proxy yang benar-benar dikelola; kosong untuk direct deployment. |
| `APP_ENCRYPTION_KEY` | Ya, secret | Nilai acak dari secret store; tidak boleh fallback di source. |
| `APP_COOKIE_SECURE` | Ya | Harus bernilai boolean true. |
| `APP_LOG_THRESHOLD` | Ya | Harus `1`; nilai debug 2–4 ditolak. |
| `APP_LOG_PATH` | Ya | Absolute, existing, writable, dan di luar document root. |
| `APP_SESSION_SAVE_PATH` | Ya | Absolute, existing, writable, dan di luar document root. |
| `APP_PRIVATE_STORAGE_PATH` | Ya | Absolute, existing, writable, dan di luar document root. |
| `DB_HOST` | Ya | Host database deployment. |
| `DB_USERNAME` | Ya | Akun aplikasi least-privilege; user MySQL privileged ditolak. |
| `DB_PASSWORD` | Ya, secret | Bukan password kosong, username, nama database, atau nilai default yang dikenal. |
| `DB_DATABASE` | Ya | Nama database aplikasi. |
| `AMI_DEV_DB_PASSWORD` | Docker development saja | Password user `ami_local`; Compose menolak startup bila kosong. Jangan dipakai di production. |
| `AMI_DEV_DB_ROOT_PASSWORD` | Docker development saja | Password root container lokal; harus berbeda dan tidak dipakai di production. |

## Runbook production

### 1. Siapkan direktori

Gunakan tiga direktori terpisah di luar document root untuk log, session, dan private storage.

Contoh Linux:

```bash
install -d -m 0700 -o <php-user> -g <php-group> /srv/ami/logs
install -d -m 0700 -o <php-user> -g <php-group> /srv/ami/sessions
install -d -m 0700 -o <php-user> -g <php-group> /srv/ami/private
```

Pada Windows, hentikan inheritance yang tidak diperlukan dan berikan Modify hanya kepada service account Apache/PHP serta Full Control kepada administrator yang disetujui. Verifikasi dengan `icacls`; jangan mengandalkan atribut POSIX dari PHP pada NTFS.

### 2. Inject environment

Simpan secret pada fasilitas deployment, bukan `.htaccess`, repository, image, atau command history. Untuk Apache, gunakan konfigurasi virtual host/server yang tidak berada di document root. Untuk PHP-FPM, pastikan environment yang diperlukan diteruskan secara eksplisit kepada pool.

Jangan menyalin placeholder `.env.example` sebagai nilai nyata. File `.env` dan turunannya diabaikan Git.

### 3. Batasi PHP dan web server

Controller saat ini menerima maksimal 5 MiB per file. Pasang ceiling sedikit di atas kebutuhan tersebut agar request valid masih dapat diproses:

```ini
upload_max_filesize=6M
post_max_size=8M
max_file_uploads=10
display_errors=Off
log_errors=On
expose_php=Off
```

Web server harus:

- memaksa HTTPS;
- menolak directory listing;
- menolak akses langsung ke `.git`, `.env*`, `application`, `system`, log, session, private storage, backup, dan file migration;
- membatasi body request paling besar 8 MiB kecuali kebutuhan bisnis baru disetujui;
- menghapus forwarded headers dari client dan menulis ulang hanya dari proxy tepercaya;
- tidak meneruskan traffic dengan Host di luar `APP_ALLOWED_HOSTS`.

Security headers M1-07 diterapkan oleh `application/config/security_headers.php`.
Web server/reverse proxy tidak boleh menghapus atau melemahkan CSP, nosniff,
frame protection, referrer policy, permissions policy, dan cache policy
tersebut. HSTS hanya dikirim aplikasi ketika production request dikenali
sebagai HTTPS.

### 4. Database

Gunakan user aplikasi khusus dengan privilege hanya pada schema AMI. Jangan memakai `root` atau user sistem MySQL. Source saat ini belum mengonfigurasi TLS database; jika database melewati jaringan yang tidak sepenuhnya tepercaya, TLS terverifikasi harus ditambahkan dan diuji sebelum production.

Jangan import `database_dummy.sql` ke production dan jangan membuat akun dengan kredensial development.

### 5. Startup verification

Sebelum membuka traffic:

1. Jalankan startup dengan konfigurasi lengkap dan Host yang diizinkan.
2. Pastikan response memiliki `X-Request-ID`.
3. Trigger error sintetis pada staging; browser hanya boleh melihat pesan generik dan request ID.
4. Cocokkan request ID tersebut di log private dan pastikan tidak ada password, token, cookie, stack trace, atau path pada response.
5. Pastikan request dengan Host lain memperoleh 503 generik.
6. Pastikan log, session, dan private storage tidak dapat diakses melalui HTTP.
7. Pastikan CSP mempunyai nonce yang sama dengan tag script/style pada HTML dan
   tidak memuat `script-src 'unsafe-inline'` atau `unsafe-eval`.
8. Pastikan HSTS ada pada production HTTPS, tetapi tidak ada pada HTTP
   development/testing.
9. Jalankan `tests/security_headers_regression.php` dan smoke suite.

### 6. Fail-closed verification

Masing-masing kondisi berikut harus diuji satu per satu pada staging dan menghasilkan status 503 dengan isi hanya `Production configuration is incomplete.`:

| Kondisi | Validator |
|---|---|
| `APP_ENCRYPTION_KEY` kosong | `application/config/config.php` |
| `DB_PASSWORD` memakai nilai default/development atau sama dengan username/database | `application/config/database.php` |
| `DB_USERNAME` memakai user privileged | `application/config/database.php` |
| Private storage hilang/tidak writable/berada di document root | `application/config/config.php` |
| Log atau session path hilang/tidak writable/berada di document root | `application/config/config.php` |
| `APP_LOG_THRESHOLD` bukan `1` atau display error aktif | `application/config/config.php` |
| Base URL bukan HTTPS valid atau Host tidak diizinkan | `application/config/config.php` |
| Trusted proxy berisi IP/CIDR tidak valid | `application/config/config.php` |

## Residual risk dan tindak lanjut

1. Brute-force throttling, status akun, invalidasi session saat role berubah, dan absolute timeout adalah M1-03.
2. Central object authorization adalah M1-04.
3. Pertahankan regression gate output encoding M1-05; rich text tetap dilarang sampai ada field, allowlist sanitizer, dan test khusus.
4. Pertahankan regression M1-06; jalankan migration 013, filesystem migration legacy, scheduled retention purge, serta verifikasi ACL/no-execute. Antivirus/CDR dan legal hold masih keputusan lanjutan.
5. Pertahankan gate M1-07. Atribut style legacy masih memerlukan
   `style-src-attr 'unsafe-inline'`; pindahkan ke class/stylesheet agar
   pengecualian dapat dihapus. Browser visual/console dan availability CDN
   tetap release check.
6. Pertahankan gate M1-08: terapkan migration 014 sebelum code, cabut privilege
   update/delete ledger dari akun aplikasi bila deployment mendukung,
   jalankan `maintenance verify_audit_log`, monitor kegagalan append, dan
   backup ledger bersama chain state. Retention/legal hold masih memerlukan
   keputusan stakeholder.
7. TLS database, OS ACL, reverse proxy, backup encryption, dan restore tetap harus diverifikasi pada deployment aktual.
