# Konfigurasi IT Ringkas, AlmaLinux ZIP

> **BELUM DIVERIFIKASI di AlmaLinux.** Ini ringkasan handoff. Untuk penjelasan
> lengkap, lihat [Deployment Native AlmaLinux dari ZIP](DEPLOY_NATIVE_ALMALINUX_ZIP.md).

1. Pastikan AlmaLinux 9.x, Nginx, PHP-FPM/PHP CLI, MariaDB/MySQL, unzip, dan
   ekstensi `mysqli`, `mbstring`, `xml`, `zip`, `gd`, `curl`, `fileinfo` tersedia.
2. Tetapkan root tunggal: `/var/www/html/ami`. Root ini harus berisi `index.php`.
3. Transfer ZIP dan cocokkan `sha256sum` dengan checksum release.
4. Ekstrak ZIP tanpa membuat lapisan path ganda seperti `ami/AMI/`.
5. Siapkan `uploads/` dan kategori aplikasi; jangan hapus folder itu saat upgrade.
6. Temukan user/group FPM dengan `ps`, `systemctl cat php-fpm`, `stat`, dan `namei`.
7. Atur ownership, permission terbatas, dan SELinux fcontext. Jangan gunakan `777`.
8. Edit hanya tiga file di tabel berikut. `index.php` tidak perlu diedit.
9. Pasang server block Nginx, socket FPM, TLS, rewrite, dan blokir folder private.
10. Untuk fresh, import `database_schema.sql` sekali lalu jalankan
    `scripts/bootstrap_super_admin.php`.
11. Untuk existing, backup lalu jalankan migration yang sudah direview. Jangan
    import schema fresh atau reset database existing.
12. Jalankan lint, doctor, `nginx -t`, status service, dan `curl -I`.
13. Jalankan smoke test login, PPEPP upload/download/delete, foto, bukti,
    session, dan penolakan URL langsung ke file private.
14. Catat path, checksum, hasil test, backup, dan rollback tanpa menulis secret.

## Tiga file konfigurasi

| File | Isi yang harus diganti |
|---|---|
| `application/config/production/config.php` | `base_url`, encryption key, session/log path, cookie, `index_page`. Tandai dengan `// GANTI`. |
| `application/config/production/database.php` | Host, user aplikasi, password, nama database, driver. Tandai dengan `// GANTI`. |
| `application/config/production/ami.php` | Root private/public upload dan integrasi yang dipakai. Default root: `/var/www/html/ami/uploads/`. Tandai dengan `// GANTI`. |

## Path dan perintah cek

```bash
php -l /var/www/html/ami/application/config/production/config.php
php -l /var/www/html/ami/application/config/production/database.php
php -l /var/www/html/ami/application/config/production/ami.php
php /var/www/html/ami/scripts/ami_doctor.php
sudo nginx -t
sudo systemctl is-active php-fpm mariadb nginx
curl -I https://<domain>/
stat -c '%U:%G %a %n' /var/www/html/ami /var/www/html/ami/uploads
namei -l /var/www/html/ami/uploads/ppepp_documents
getenforce
```

Contoh sukses: `No syntax errors detected`, doctor `PASS`, `syntax is ok`,
semua service `active`, HTTP `/` `200`, dan SELinux `Enforcing`. Endpoint
`/index.php/auth/login` boleh `405` jika hanya menerima POST. Uji login dari
halaman `/`.

## Checklist IT

- [ ] Root aplikasi: `________________________________`
- [ ] Domain HTTPS: `________________________________`
- [ ] Database host dan nama: `________________________________`
- [ ] User database aplikasi: `________________________________`
- [ ] Root upload/private: `________________________________`
- [ ] PHP-FPM socket dan user/group: `________________________________`
- [ ] Path sertifikat TLS: `________________________________`
- [ ] Checksum ZIP dan versi release dicatat.
- [ ] Tiga file production diisi, tanpa secret dalam dokumen handoff.
- [ ] Ownership, permission, SELinux, Nginx, dan PHP-FPM lulus.
- [ ] Fresh atau existing dipilih dengan prosedur yang benar.
- [ ] Backup dan rollback disetujui.
- [ ] Smoke test PPEPP dan direct-private denial lulus.
