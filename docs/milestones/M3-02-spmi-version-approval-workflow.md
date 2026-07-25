# M3-02 — Workflow Persetujuan Versi SPMI

- Status: **Selesai dan diverifikasi**
- Tanggal: 2026-07-25
- Branch milestone: `codex/m3-spmi-master-versioning`
- Dependensi schema: migration `013`, `015`, `016`, dan `017`

## Hasil

M3-02 membuka workflow versi SPMI yang sebelumnya hanya memiliki foundation
schema/read model:

```text
draft → review → approved → active → retired
```

Versi dibuat dalam scope satu unit organisasi dan capability
`spmi.version.manage`. Super Admin mempunyai scope organisasi global. Admin
LPMPI harus mempunyai assignment aktif langsung pada unit target.

## Aturan yang diterapkan

- Hanya `draft` yang dapat diedit.
- Perpindahan status dilakukan melalui POST + CSRF dan service workflow.
- Pembuat tidak dapat menjadi satu-satunya approver.
- Approval menyimpan `approved_by` dan `approved_at`.
- Versi hanya dapat diaktifkan pada atau setelah tanggal efektif.
- Jika dokumen yang sama sudah mempunyai versi aktif, retirement versi lama
  dan activation versi baru dilakukan dalam satu transaksi setelah identity
  row lock.
- Tanggal efektif versi pengganti harus setelah versi aktif sebelumnya.
- Versi aktif read-only pada service dan trigger database.
- Tidak ada endpoint delete atau reverse-state. Koreksi/rollback bisnis
  dilakukan dengan clone ke draft baru sehingga histori lama tetap ada.
- Create, update, submit, approve, activate, retire, dan clone menghasilkan
  event pada immutable audit ledger.

## PDF sumber

- Upload memakai kategori `spmi_source`: PDF-only, maksimal 20 MiB, private.
- Aset upload awal belum mempunyai `owner_id`; service mengikatnya ke ID versi
  pada transaksi create.
- Pergantian file draft membuat aset baru dan menandai aset lama untuk
  retention setelah update berhasil.
- Clone menyalin byte sumber yang sudah diverifikasi ke random storage name dan
  row `file_assets` baru. Checksum harus sama, tetapi satu aset tidak pernah
  dipakai oleh dua versi.
- Download hanya melalui controller terotorisasi dan memverifikasi ukuran serta
  SHA-256 sebelum stream attachment.

## Surface web

Menu **Versi SPMI** tersedia untuk Super Admin dan Admin LPMPI. Endpoint utama:

- `GET /spmi-versions`
- `GET|POST /spmi-versions/create|store`
- `GET|POST /spmi-versions/edit/{id}|update/{id}`
- `POST /spmi-versions/submit-review/{id}`
- `POST /spmi-versions/approve/{id}`
- `POST /spmi-versions/activate/{id}`
- `POST /spmi-versions/retire/{id}`
- `GET|POST /spmi-versions/clone/{id}|clone-store/{id}`
- `GET /spmi-versions/download/{id}`

## File utama

- `application/services/Spmi_version_workflow_service.php`
- `application/controllers/Spmi_versions.php`
- `application/models/Spmi_version_model.php`
- `application/models/File_asset_model.php`
- `application/libraries/File_security.php`
- `application/views/lpmpi/spmi_versions/*`
- `tests/spmi_versions_regression.php`
- `tests/smoke/run.php`

## Verifikasi

| Gate | Hasil |
|---|---|
| `php tests/spmi_versions_regression.php` | PASS — 115 checks |
| Seluruh `tests/*regression.php` | PASS |
| `php tests/smoke/run.php` | PASS — 34 cases |
| Targeted dan full PHP lint | PASS |
| `git diff --check` | PASS |

Smoke M3-02 melakukan multipart upload nyata dan membuktikan denial role,
edit draft, self-approval denial, approval oleh aktor berbeda, provenance
approval, activation, denial edit active, private download, clone ke aset
baru, atomic replacement, histori retired/active, dan audit event.

## Batasan

- Belum ada electronic signature/sertifikat approval.
- Belum ada komentar/rejection/reopen review; perubahan dilakukan sebelum
  submit atau lewat clone.
- Belum ada concurrency stress test. Row lock dan unique active slot menjadi
  kontrol runtime/database, tetapi race test paralel masih perlu.
- Audit append dilakukan setelah business transaction commit sesuai boundary
  ledger aplikasi saat ini; operasi dan ledger belum menjadi distributed
  atomic unit.
- Pada checkpoint M3-02 master standar masih legacy. M3-03 kemudian
  menambahkan `spmi_standards`; pernyataan dan turunan lain tetap menunggu
  task berikutnya.

## Task berikutnya

M3-03 — Master 21 Standar sudah selesai. Task lanjutan adalah M3-04 —
Pernyataan Isi Standar pada branch milestone M3 yang sama.
