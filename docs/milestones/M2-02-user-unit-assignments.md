# M2-02 — Keanggotaan User dan Jabatan

- Status: **Implemented and verified**
- Tanggal: 2026-07-25
- Branch milestone: `codex/m2-organization-role-scope`
- Checkpoint sebelumnya: M2-01 commit `45c6a6d`
- Dependensi: migration 015 dan master `organization_units`

## Hasil

M2-02 menambahkan relasi historis `user_unit_assignments` yang menghubungkan
pengguna dengan unit organisasi, kode jabatan, periode berlaku, dan penanda
primary. Satu pengguna dapat mempunyai beberapa assignment pada unit/jabatan
berbeda.

Aturan yang diterapkan:

- unit baru harus aktif;
- `position_code` disimpan uppercase dan hanya menerima huruf, angka, titik,
  garis bawah, atau tanda hubung;
- tanggal harus valid dan `valid_until >= valid_from`;
- periode untuk kombinasi user, unit, dan jabatan yang sama tidak boleh
  bertumpuk;
- hanya satu assignment primary boleh berlaku pada periode yang sama;
- assignment lama tidak dihapus; UI hanya dapat memperpendek atau menetapkan
  `valid_until`;
- assignment aktif mensyaratkan user aktif, unit aktif, serta tanggal acuan
  berada dalam periode inklusif;
- pembuatan diserialisasi dengan lock row user di dalam transaksi agar
  pemeriksaan overlap tidak berlomba.

## Migration dan kompatibilitas

Migration:

```text
migrations/016_create_user_unit_assignments.sql
```

Upgrade database existing:

```bash
mysql -u <user> -p <database> < migrations/015_create_organization_units.sql
mysql -u <user> -p <database> < migrations/016_create_user_unit_assignments.sql
```

Migration 016 bersifat idempotent pada pembuatan tabel. Foreign key ke `users`
dan `organization_units` memakai `RESTRICT` agar histori assignment mencegah
hard delete referensi. Penghapusan user juga ditolak lebih awal oleh service
bila riwayat assignment sudah ada.

Kolom legacy `users.nama_unit` dan `users.jenis_unit` tetap tersedia untuk
workflow lama dan tampilan kompatibilitas. Tidak ada backfill otomatis:
pencocokan teks legacy ke ID unit dapat ambigu dan harus dilakukan melalui
proses data-mapping terpisah yang direview.

Migration 016 sudah diterapkan pada database development lokal `ami`. Tabel,
index, foreign key, check constraint, dan jumlah awal nol row terverifikasi.
Production tetap memerlukan backup serta prosedur DBA/deployment.

## Otorisasi dan audit

- Capability `user_unit_assignments.manage` hanya untuk `super_admin` dan
  `admin_lpmpi`.
- Super Admin dapat mengelola target user apa pun.
- Admin LPMPI hanya dapat mengelola target Auditor/Auditee.
- Auditor dan Auditee ditolak pada route manajemen.
- `activeOrganizationAssignments()`, `activeOrganizationUnitIds()`, dan
  `canAccessOrganizationUnit()` menjadi API policy untuk assignment aktif.
- Scope M2-02 adalah membership langsung. Pewarisan scope ke unit turunan
  belum diasumsikan dan menjadi keputusan M2-03.
- Event `user_unit_assignment_created` dan
  `user_unit_assignment_ended` masuk immutable audit ledger.

## Surface web

Tautan **Unit & Jabatan** tersedia pada daftar pengguna Super Admin dan daftar
akun Auditor/Auditee milik Admin LPMPI. Halaman target menampilkan seluruh
riwayat, status aktif/akan datang/berakhir, assignment primary, form tambah,
dan aksi pengakhiran berbasis tanggal. Tidak ada route atau tombol delete.

## Verification

Hasil Windows/Laragon, PHP 8.3.30, MySQL 8.4.3:

| Pemeriksaan | Hasil |
|---|---|
| `php tests/user_unit_assignments_regression.php` | PASS — 30 checks |
| `php tests/authorization_policy_regression.php` | PASS — 44 checks |
| Seluruh `tests/*_regression.php` | PASS |
| `php tests/smoke/run.php` | PASS — 32 cases |
| Migration 016 pada database development `ami` | PASS — schema lengkap, 0 initial rows |
| Full PHP lint `application`, `tests`, `scripts` | PASS — 156 files |
| `git diff --check` | PASS |

Smoke suite memakai database disposable dan membuktikan create primary,
multi-assignment non-primary, penolakan primary overlap, penolakan role
Auditor, pengakhiran tanpa delete, dan audit event.

## Acceptance criteria

- Satu user dapat memiliki beberapa unit/jabatan: **terpenuhi dan diuji**.
- Assignment mempunyai masa berlaku: **terpenuhi dan diuji**.
- Satu assignment dapat ditandai primary tanpa overlap: **terpenuhi dan
  diuji**.
- Histori tidak dihapus: **terpenuhi pada schema, service, route, dan test**.
- Otorisasi dapat memakai assignment aktif: **API policy tersedia dan
  regression-tested**.

## Langkah berikutnya

M2-03 dapat membangun matriks capability dan aturan scope di atas API
assignment aktif. Sebelum memperluas akses ke child unit, putuskan secara
eksplisit apakah membership parent mencakup descendant atau hanya unit
langsung.
