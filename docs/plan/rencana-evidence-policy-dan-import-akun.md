# Rencana Teknis: Evidence Policy per Indikator & Import Master Data Akun

Status: Fitur A SELESAI (2026-09-15); Fitur B DRAFT — hasil tindak lanjut rapat tinjauan aplikasi AMI/SPMI
Cakupan: dua permintaan tim LPMPI & IT kampus, dikerjakan di branch `dev`

---

## Ringkasan

| Fitur | Masalah | Solusi |
|---|---|---|
| A. Evidence Policy per Indikator | Sejak fitur Instrumen dihapus (migration `034_retire_spmi_instruments.sql`), evidence policy tiap item penugasan dikunci `'none'` — auditee tidak bisa upload bukti file/URL sama sekali | Pindahkan evidence policy ke level **Indikator SPMI**, dipilih Admin LPMPI saat membuat/edit indikator, lalu di-snapshot ke item penugasan saat siklus audit dibuat |
| B. Import Master Data Akun | Akun dosen/tendik hanya bisa dibuat satu-satu lewat form manual | Fitur import Excel (template → upload → preview → konfirmasi) mengikuti pola yang sudah ada di modul Import/Export Master SPMI |

Kedua fitur ini **additive dan backward compatible** — tidak mengubah data/behavior existing, mengikuti prinsip migrasi yang sudah dipakai di seluruh migration SPMI sebelumnya.

---

## Fitur A — Evidence Policy per Indikator (SELESAI — 2026-09-15)

### A.1 Latar Belakang

Saat ini di `application/services/Spmi_audits_service.php:54`, setiap item penugasan dibuat dengan:

```php
'evidence_policy' => 'none'
```

Ini hardcoded — tidak ada sumber data lain karena tabel `spmi_indicators` belum punya kolom evidence policy. Downstream (`Spmi_auditee_workspace_service.php`, view `assignment.php`, `confirm.php`) **sudah generik** dan otomatis mengikuti nilai `evidence_policy` per item, jadi begitu sumbernya benar, seluruh alur upload bukti auditee otomatis berfungsi kembali tanpa perlu ubah service auditee/auditor.

### A.2 Perubahan Skema (migration baru, additive)

**`migrations/035_add_indicator_evidence_policy.sql`**
```sql
ALTER TABLE `spmi_indicators`
    ADD COLUMN `evidence_policy` ENUM('none','file','url','either','both')
    NOT NULL DEFAULT 'none' AFTER `evidence_requirement`;
```
- Default `'none'` menjaga indikator lama tetap sama seperti sekarang (sesuai prinsip backward compatibility di kontrak SPMI sebelumnya).
- Kolom `evidence_policy` di `spmi_audit_assignment_items` **sudah ada** dari migration `026_add_assignment_item_evidence_policy.sql` — tidak perlu migration baru di tabel itu, cukup ubah cara pengisiannya.

### A.3 Perubahan Kode per Layer

| Layer | File | Perubahan |
|---|---|---|
| Model | `application/models/Spmi_indicators_model.php` | Sertakan `evidence_policy` di `upsert_indicator()` / kolom yang di-select |
| Service | `application/services/Spmi_indicators_service.php` | Validasi `evidence_policy` (`in_list[none,file,url,either,both]`) di `create_indicator()` / `update_indicator()` |
| Controller | `application/controllers/lpmpi/Spmi_indicators.php` | Tambah rule di `set_indicator_rules()` |
| View | `application/views/lpmpi/spmi_indicators/indicator_form.php` | Tambah `<select>` evidence policy (label sama seperti yang dipakai di view auditee: none/file/url/either/both) |
| View | `application/views/lpmpi/spmi_indicators/indicator_detail.php` | Tampilkan evidence policy indikator |
| Service | `application/services/Spmi_audits_service.php:54` | Ganti `'evidence_policy' => 'none'` → `'evidence_policy' => $indicator->evidence_policy` |
| _(ditunda)_ | `application/services/Spmi_master_service.php`, `application/controllers/lpmpi/Spmi_master.php` | **Keputusan tim: tidak dikerjakan dulu.** Evidence policy untuk saat ini cukup diatur manual satu-satu lewat form Indikator SPMI. Template Import/Export Master SPMI menyusul kalau IT kampus sudah menentukan preferensinya |

### A.4 Test yang Perlu Diperbarui

`tests/spmi_instrument_retirement_regression.php` saat ini **mengunci literal** `"'evidence_policy' => 'none'"` sebagai string wajib ada di kode (baris kontrak retirement). Assertion ini perlu diperbarui supaya tidak lagi mensyaratkan nilai statis `'none'`, diganti jadi memastikan kode memakai `$indicator->evidence_policy` (dinamis) tanpa membuka kembali dependency ke tabel instrumen yang sudah dihapus — bagian larangan lain di test ini (`source_package_id`, `spmi_instrument_*`, dst.) **tetap dipertahankan apa adanya**.

Tambahan test baru yang disarankan: `tests/spmi_indicator_evidence_policy_regression.php` — memastikan:
- Default `'none'` untuk indikator lama
- CRUD evidence policy di form indikator tervalidasi
- Snapshot ke assignment item sama persis dengan evidence policy indikator sumber saat penugasan dibuat

### A.5 Task Breakdown

1. Migration `035_add_indicator_evidence_policy.sql`
2. Model + service + controller + view indikator (CRUD evidence policy)
3. `Spmi_audits_service::create_assignment` snapshot dari indikator
4. Update `spmi_instrument_retirement_regression.php` + tambah regression baru
5. (Opsional, tergantung keputusan tim) selaraskan `Spmi_master` import/export
6. Update `docs/product/spmi-workspace-parity-contract.md` bagian 8–9 (Evidence Policy Contract) supaya dokumentasi tidak menyesatkan — sumber evidence policy sekarang indikator, bukan instrument question

---

## Fitur B — Import Master Data Akun

### B.1 Alur Pengguna

```
Admin LPMPI → download template (.xlsx)
           → isi/generate dari data SIMPEG/SDM kampus
           → upload
           → preview (baris valid vs error ditampilkan, tanpa disimpan)
           → konfirmasi
           → sistem generate akun + password sementara
           → daftar (nama, email, password sementara) ditampilkan SEKALI untuk dibagikan admin
           → penempatan unit dilakukan terpisah lewat menu "Struktur Organisasi" (sudah ada)
```

### B.2 Template Kolom (`Import Master Akun.xlsx`, sheet `Master Akun`)

| Kolom | Wajib | Validasi |
|---|---|---|
| NIP/NIDN | Ya | Unik dalam file & terhadap data existing; dipakai untuk deteksi duplikat saat re-import |
| Nama | Ya | Tidak kosong, maks 100 karakter (sesuai `users.nama`) |
| Email | Ya | Format email valid, unik dalam file & terhadap `users.email` |
| Role | Ya | Hanya `auditor` atau `auditee` (super_admin/admin_lpmpi tetap manual) |

Tidak ada kolom unit — sesuai kesepakatan, penempatan unit tetap manual lewat modul Organisasi.

### B.3 Perubahan Skema (migration baru, additive)

**`migrations/036_add_users_import_support.sql`**
```sql
ALTER TABLE `users`
    ADD COLUMN `identity_number` VARCHAR(32) NULL UNIQUE AFTER `email`,
    ADD COLUMN `must_change_password` TINYINT(1) NOT NULL DEFAULT 0 AFTER `password`;
```
- Nullable + unique agar user lama (tanpa NIP/NIDN) tidak terpengaruh.
- `must_change_password` dipakai untuk memaksa ganti password saat login pertama bagi akun hasil import.

### B.4 Perubahan Kode per Layer

| Layer | File (baru/ubah) | Keterangan |
|---|---|---|
| Model | `application/models/User_model.php` | Tambah `find_by_identity_number()`, sertakan `identity_number`, `must_change_password` di create/update |
| Service | `application/services/Akun_import_service.php` (baru) | Mengikuti pola `Spmi_master_service.php`: `HEADERS`, `parse($path)` (validasi baris, dedup), `confirm($rows)` (insert user + generate password + set `must_change_password=1`) |
| Controller | `application/controllers/lpmpi/Akun_import.php` (baru) | Mengikuti pola `Spmi_master.php`: `template()`, `preview()`, `confirm()`, `cancel()` — batas file 2 MiB, `.xlsx` only, cek MIME, simpan sementara di `private_storage_dir('tmp')`, preview terikat sesi + token + kedaluwarsa 30 menit |
| Controller | `application/controllers/Auth.php` / `application/services/Auth_service.php` | Setelah login sukses, jika `must_change_password = 1`, tampilkan pesan wajib ganti password di halaman login/dashboard awal (bukan halaman terpisah) — **keputusan tim**: cukup pesan saja dulu, bukan halaman wajib tersendiri |
| Route | `application/config/routes.php` | `lpmpi/akun-import`, `lpmpi/akun-import/preview`, `lpmpi/akun-import/confirm`, `lpmpi/akun-import/cancel`, `lpmpi/akun-import/template` |
| View | `application/views/lpmpi/akun_import/*` | Halaman upload, preview (baris valid/error), hasil konfirmasi (daftar password sementara — ditampilkan sekali, ada tombol salin/export, **tidak disimpan ke log/DB dalam bentuk plain text**) |
| View | `application/views/users/index.php` | Tambah tombol "Import Master Akun" di halaman Manajemen Pengguna |

### B.5 Keamanan

- Endpoint hanya untuk `super_admin` & `admin_lpmpi` (mewarisi `Admin_Lpmpi_Controller`, sama seperti modul Master SPMI).
- Semua mutasi POST-only (`require_post()`).
- Role dalam file selain `auditor`/`auditee` ditolak per-baris (bukan gagal seluruh file), agar tim bisa perbaiki baris tertentu saja.
- Password sementara: digenerate acak (`random_bytes`), di-hash dengan `password_hash()` sebelum disimpan; bentuk plain text **hanya tampil satu kali** di halaman hasil konfirmasi, tidak pernah ditulis ke `log_message()` atau tabel manapun.
- File upload divalidasi ekstensi + MIME + ukuran maksimum, disimpan di direktori privat sementara dan dihapus setelah diproses (identik dengan pola `Spmi_master`).
- Preview memakai token terikat sesi + expiry 30 menit, mencegah konfirmasi memakai data preview milik user lain atau yang sudah kedaluwarsa.

### B.6 Task Breakdown

1. Migration `036_add_users_import_support.sql`
2. `Akun_import_service.php` (parse, validasi, dedup, confirm + generate password)
3. `Akun_import.php` controller + routes
4. Views: upload, preview, hasil konfirmasi (password sementara)
5. Flag `must_change_password` → alur wajib ganti password di login
6. Tombol akses dari halaman Manajemen Pengguna
7. Regression test `tests/akun_import_regression.php` (template header, dedup, role restriction, password tidak bocor ke log, must-change-password enforcement, POST-only & role-guard)

---

## Urutan Pengerjaan yang Disarankan

1. **Fitur A dulu** — lebih kecil, langsung memulihkan kemampuan upload bukti yang saat ini hilang total di alur SPMI aktif (dampak fungsional lebih mendesak).
2. **Fitur B setelahnya** — lebih besar karena menyentuh alur autentikasi (`must_change_password`), butuh keputusan kecil soal format pesan/halaman ganti password wajib.

## Keputusan Rapat (Final)

- Evidence Policy untuk saat ini **diatur manual per indikator** lewat form Indikator SPMI. Integrasi ke template Import/Export Master SPMI ditunda sampai IT kampus menentukan preferensinya.
- Notifikasi wajib ganti password untuk akun hasil import cukup berupa **pesan di halaman login** — tidak perlu halaman terpisah.
