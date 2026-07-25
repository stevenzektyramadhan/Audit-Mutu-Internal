# M3-03 — Master 21 Standar SPMI

- Status: **IMPLEMENTED — DEFERRED — NOT CANONICAL FOR MVP**
- Tanggal: 2026-07-25
- Branch milestone: `codex/m3-spmi-master-versioning`
- Dependensi schema: migration `015`, `016`, `017`, dan `018`

## Hasil

M3-03 menambahkan master standar yang dimiliki oleh satu `spmi_version`.
Implementasi ini additive: tabel legacy `standar` dan alur AMI yang masih
memakainya tidak dipindahkan atau dihapus.

## Keputusan produk M3-03A

UI Versioned SPMI ditunda sampai ada keputusan produk dan rencana cutover yang
menyatukan workflow AMI. Flag `FEATURE_VERSIONED_SPMI` default `false`.

- Saat flag OFF, menu **Versi SPMI** disembunyikan dan seluruh controller
  `Spmi_versions` serta `Spmi_standards` merespons 404, termasuk untuk Super
  Admin.
- Saat flag ON, behavior, authorization, organization scope, service, model,
  audit ledger, dan schema M3-03 tetap berjalan seperti implementasi awal.
- Tabel legacy `standar` tetap menjadi **source of truth MVP** untuk
  pertanyaan, instrumen, tugas audit, jawaban audit, laporan, dan penetapan.
- `spmi_versions` dan `spmi_standards` dipertahankan sebagai backend/schema
  deferred dan **bukan master canonical untuk MVP**.
- Flag tidak menjalankan migration dan tidak mengubah data. Migration `017`
  serta `018` tetap dipertahankan dan tidak di-rollback.

Untuk membuka fitur hanya pada environment yang memang disetujui, inject:

```text
FEATURE_VERSIONED_SPMI=true
```

Struktur awal dimuat melalui config/service, bukan di-hardcode pada view:

- 8 standar pendidikan;
- 3 standar penelitian;
- 3 standar pengabdian kepada masyarakat;
- 7 standar tambahan perguruan tinggi.

Totalnya 14 standar SN Dikti dan 7 standar internal.

## Aturan data

- Kode unik pada satu versi melalui unique key
  `(spmi_version_id, code)`.
- Kode yang sama boleh muncul pada versi berbeda.
- Standar hanya dapat ditambah atau diubah ketika versi induk `draft`.
- Standar tidak mempunyai hard-delete; pengguna dapat menonaktifkan row pada
  draft.
- Trigger database menolak insert/update di luar draft, pemindahan row ke
  versi lain, dan seluruh delete.
- Urutan dapat diubah dan request reorder wajib mencakup seluruh standar
  dengan nilai positif yang unik.
- Kelompok pendidikan/penelitian/pengabdian wajib berjenis `sn_dikti`;
  kelompok internal wajib berjenis `internal`.

## Lifecycle dan clone

Seed awal hanya dapat dijalankan satu kali pada versi draft yang masih kosong.
Ketika versi `approved`, `active`, atau `retired` di-clone, seluruh standar,
urutan, status aktif, rasional, dan definisinya ikut disalin ke draft baru di
dalam transaksi pembuatan versi. Jumlah row sumber dan hasil clone diverifikasi
sebelum commit. Perubahan pada standar hasil clone tidak mengubah sumber.

## Otorisasi dan audit

Semua route memakai capability `spmi.standard.manage` dan direct active
organization-unit scope. Super Admin dapat mengakses seluruh unit aktif; Admin
LPMPI memerlukan assignment aktif pada unit target. Auditor/Auditee ditolak.

Seed, create, edit, activate/deactivate, dan reorder dicatat pada immutable
audit ledger. Clone versi mencatat jumlah standar yang disalin pada metadata
event versi.

## Surface web

- `GET /spmi-versions/{version}/standards`
- `POST /spmi-versions/{version}/standards/seed`
- `GET|POST /spmi-versions/{version}/standards/create|store`
- `GET|POST /spmi-versions/{version}/standards/edit/{id}|update/{id}`
- `POST /spmi-versions/{version}/standards/toggle-active/{id}`
- `POST /spmi-versions/{version}/standards/reorder`

Detail versi menampilkan tombol **Kelola Standar** beserta jumlah row. Versi
non-draft tetap dapat dilihat, tetapi UI dan controller membuatnya read-only.

## File utama

- `migrations/018_create_spmi_standards.sql`
- `application/config/spmi_standard_seed.php`
- `application/models/Spmi_standard_model.php`
- `application/services/Spmi_standard_service.php`
- `application/controllers/Spmi_standards.php`
- `application/views/lpmpi/spmi_standards/*`
- `scripts/database/apply_local_m3_03.php`
- `tests/spmi_standards_regression.php`
- `tests/smoke/run.php`

## Verifikasi

| Gate | Hasil |
|---|---|
| Migration 018 pada database development | PASS — dijalankan dua kali |
| `php tests/spmi_standards_regression.php` | PASS — 146 checks |
| `php tests/spmi_versions_regression.php` | PASS — 115 checks |
| Seluruh `tests/*regression.php` | PASS |
| `php tests/smoke/run.php` | PASS — 34 cases |
| Targeted dan full PHP lint | PASS |
| `git diff --check` | PASS |

Smoke memakai database disposable dan membuktikan seed/distribusi 21 standar,
unique code per versi, edit/toggle/reorder draft, denial role, read-only
review/active, no-delete trigger, clone 21 row, code yang sama lintas versi,
independensi hasil clone, dan audit event.

## Batasan

- Belum ada pernyataan isi standar; itu scope M3-04.
- Belum ada backfill/cutover dari tabel legacy `standar`.
- Belum ada drag-and-drop; reorder memakai nomor urut eksplisit.
- Belum ada concurrency stress test paralel.
- Audit append mengikuti boundary ledger aplikasi saat ini dan terjadi setelah
  business transaction commit.

## Task berikutnya

M3-04 — Pernyataan Isi Standar. Tetap gunakan branch milestone M3 yang sama
dan buat checkpoint commit baru untuk subtask tersebut.
