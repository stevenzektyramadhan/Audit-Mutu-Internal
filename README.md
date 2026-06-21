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

## Instalasi Lokal

1. Letakkan proyek pada direktori web server, misalnya:

   ```text
   C:\laragon\www\AMI
   ```

2. Jalankan Apache dan MySQL dari Laragon atau XAMPP.

3. Import schema database:

   ```powershell
   mysql -u root -e "source C:/laragon/www/AMI/database_schema.sql"
   ```

4. Opsional, import data demo:

   ```powershell
   mysql -u root -e "source C:/laragon/www/AMI/database_dummy.sql"
   ```

5. Sesuaikan koneksi database pada `application/config/database.php` jika username, password, atau nama database berbeda.

6. Sesuaikan `base_url` pada `application/config/config.php` jika proyek tidak diakses melalui:

   ```text
   http://localhost/AMI/
   ```

7. Buka aplikasi:

   ```text
   http://localhost/AMI/index.php
   ```

## Akun Demo

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

Aplikasi hanya menyimpan link bukti. Upload file, export PDF/Excel, email, notifikasi, periode audit, dan approval bertingkat belum disediakan.

## Data Demo

`database_dummy.sql` aman dijalankan ulang dan menyediakan:

- 3 standar audit
- 12 pertanyaan
- tugas dengan status `belum_diisi`, `diisi`, dan `dinilai`
- jawaban, skor, serta catatan contoh
