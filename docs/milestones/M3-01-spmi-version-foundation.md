# M3-01 — Fondasi Versi Dokumen SPMI

- Status: **Implemented and verified**
- Tanggal: 2026-07-25
- Branch milestone: `codex/m3-spmi-master-versioning`
- Baseline: M2-03 commit `cc08b81`
- Migration: `017_create_spmi_versions.sql`

## Current state dan batas task

Requirement bisnis berasal dari `BUSINESS_REQUIREMENTS_SPMI_AMI_RTM.md`.
Struktur aktual berasal dari inspeksi repository. Sebelum M3-01, tabel
`standar` dan `pertanyaan` masih master mutable tanpa identitas versi,
effective dating, approval provenance, atau immutable history.

M3-01 menambahkan foundation baru tanpa mengubah, menghapus, atau melakukan
backfill terhadap master legacy. Controller, UI, upload form, approval,
activation, retirement transaction, dan clone-to-revision belum dibuka.
Workflow tersebut adalah scope M3-02. Karena belum ada endpoint baru, guard
HTTP, CSRF mutation, dan event aktivasi belum relevan pada checkpoint ini.

## Schema

Tabel `spmi_versions` menyimpan:

- identity: `organization_unit_id`, `document_code`, `title`, dan
  `revision_number`;
- effective dating: `effective_date` serta `expires_at`;
- source provenance: `source_file_asset_id`, opaque relative
  `source_file_path`, dan `source_file_sha256`;
- lifecycle: `draft`, `review`, `approved`, `active`, atau `retired`;
- actor provenance: `created_by`, `approved_by`, dan `approved_at`;
- microsecond timestamps serta generated `active_slot`.

`organization_unit_id` adalah adaptasi terhadap schema minimum pada backlog.
Kolom ini diperlukan agar identity/scope versi tidak bergantung pada nama unit
teks dan dapat dikombinasikan dengan capability organization-scoped dari
M2-03.

`source_file_asset_id` juga merupakan tambahan terhadap schema minimum.
Sumber versi wajib berelasi ke registry file M1-06. Kategori
`spmi_source` hanya menerima PDF, dibatasi 20 MiB, disimpan private, memakai
random stored name, dan diverifikasi SHA-256 oleh `File_security`.

## Invariant database

- `(organization_unit_id, document_code, revision_number)` unik.
- Generated `active_slot` dan unique key memastikan maksimal satu versi
  `active` untuk satu unit dan kode dokumen.
- Koeksistensi versi aktif tidak dibuka karena aturan bisnisnya belum
  disetujui.
- Satu private file asset hanya dapat menjadi source satu versi.
- `expires_at` tidak boleh sebelum `effective_date`.
- Source path hanya menerima bentuk
  `spmi_source/<48 lowercase hex>.pdf`.
- Checksum harus 64 lowercase hexadecimal character.
- Status `draft`/`review` wajib belum mempunyai approval provenance; status
  `approved`/`active`/`retired` wajib mempunyai `approved_by` dan
  `approved_at`.
- Seluruh foreign key memakai `RESTRICT`; tidak ada cascade ke histori versi.
- Trigger menolak perubahan konten versi aktif. Perubahan yang diperbolehkan
  hanya mempertahankan status `active` atau melakukan transisi ke `retired`
  tanpa mengganti identity/content/source/approval provenance.
- Trigger kedua menolak hard delete seluruh histori versi.

Single-active constraint saat ini lebih ketat daripada overlap per rentang
tanggal: satu identity/unit hanya dapat mempunyai satu row berstatus
`active`. M3-02 harus melakukan retirement dan activation secara atomik.

## Read model

`Spmi_version_model` menyediakan:

- deteksi kesiapan schema;
- detail versi dengan unit, pembuat, approver, serta metadata file;
- daftar versi per organization unit;
- lookup versi aktif berdasarkan unit, kode dokumen, dan tanggal;
- pemeriksaan duplikasi revision.

Model belum menyediakan create/update/delete. Mutation sengaja menunggu
service workflow M3-02 agar authorization, state transition, transaction,
file ownership, dan immutable audit event tidak dapat dilewati.

## Migration dan rollback

Migration 017 bersifat additive dan idempotent. Ia bergantung pada migration
013 (`file_assets`), 015 (`organization_units`), dan tabel `users`. Migration
tidak menulis row aplikasi dan tidak menyentuh `standar`/`pertanyaan`.

Runner development:

```powershell
& 'C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe' scripts\database\apply_local_m3_01.php
```

Runner menolak `CI_ENV=production`, host database non-local, nama database
tidak aman, dan hasil schema yang tidak sesuai. Production tetap memerlukan
backup database + private storage, maintenance/deployment procedure, review
DBA, serta restore test.

Rollback hanya boleh dipertimbangkan sebelum ada row versi:

1. hentikan traffic dan backup database bersama private storage;
2. pastikan `SELECT COUNT(*) FROM spmi_versions` menghasilkan `0`;
3. drop dua trigger M3-01;
4. drop tabel `spmi_versions`;
5. rollback aplikasi.

Jika sudah ada row, tabel dan private source merupakan histori yang harus
dipertahankan. Jangan menjalankan rollback destruktif otomatis.

## Verification

Hasil Windows/Laragon, PHP 8.3.30, dan MySQL 8.4.3:

| Pemeriksaan | Hasil |
|---|---|
| Migration 017 pada development | PASS, dijalankan dua kali |
| Kolom/index/FK/trigger runtime | PASS melalui runner |
| Initial `spmi_versions` row count | `0` |
| `php tests/spmi_versions_regression.php` | PASS — 83 checks |
| `php tests/smoke/run.php` | PASS — 33 cases |

Smoke memakai database acak terisolasi dan membuktikan:

- private source file dan checksum provenance tersimpan;
- status active tanpa approval provenance ditolak;
- dua active revision untuk identity/unit yang sama ditolak;
- rentang tanggal salah ditolak;
- konten versi aktif tidak dapat diedit;
- histori tidak dapat di-hard-delete;
- retirement membebaskan tepat satu active slot untuk revisi berikutnya.

## Acceptance

- Schema minimum tersedia: **terpenuhi**.
- Satu active version default: **terpenuhi di database**.
- Active version immutable: **terpenuhi di database; service/UI menyusul
  M3-02**.
- Revisi mempunyai row baru: **didukung oleh unique revision identity**.
- Source PDF private dan checksum: **foundation registry/policy/constraint
  terpenuhi; endpoint upload menyusul M3-02**.
- Legacy workflow tetap kompatibel: **terpenuhi; tidak ada cutover atau
  backfill**.

## Langkah berikutnya

Task berikutnya adalah M3-02 — workflow persetujuan versi pada branch M3 yang
sama. Implementasi harus menambahkan mutation service/controller/UI dengan
`spmi.version.manage`, organization-unit scope guard, CSRF, state transition
transaction, file ownership, separation of duties yang tidak mengarang
keputusan stakeholder, audit event activation/retirement, serta negative
authorization/state tests.
