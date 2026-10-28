# SUPERSEDED: Jangan gunakan untuk deployment baru

Dokumen Ubuntu/Debian ini digantikan oleh
[`DEPLOY_NATIVE_ALMALINUX_ZIP.md`](DEPLOY_NATIVE_ALMALINUX_ZIP.md).

Panduan ZIP AlmaLinux adalah runbook production yang berlaku untuk paket saat
ini. Panduan tersebut memakai tiga file konfigurasi native CI3 di
`application/config/production/`, bukan `.env`, dan mencakup PHP-FPM, Nginx,
TLS, MariaDB, SELinux, backup, rollback, checklist, serta sign-off.

Jangan memakai instruksi, contoh permission, konfigurasi Apache, `env[...]`, atau
prosedur schema dari versi historis ini.
