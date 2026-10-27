# SUPERSEDED: Jangan gunakan untuk deployment baru

Dokumen Ubuntu/Debian ini digantikan oleh
[`PANDUAN_DEPLOY_PRODUCTION_ALMALINUX.md`](PANDUAN_DEPLOY_PRODUCTION_ALMALINUX.md).

Panduan AlmaLinux adalah satu-satunya runbook production yang berlaku untuk paket
saat ini. Panduan tersebut memakai konfigurasi native `/etc/ami/production.php`,
bukan `.env`, dan mencakup PHP-FPM, Nginx, TLS/ACME, MariaDB, SELinux,
backup/restore, rollback, checklist, serta sign-off.

Jangan memakai instruksi, contoh permission, konfigurasi Apache, `env[...]`, atau
prosedur schema dari versi historis ini.
