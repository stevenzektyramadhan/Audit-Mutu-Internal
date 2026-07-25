# Ubuntu / AI Agent Start Here

Dokumen ini adalah entry point singkat untuk melanjutkan AMI dari Ubuntu,
termasuk bila agent yang digunakan adalah OpenCode atau agent lain.

## Snapshot yang harus digunakan

- Repository: `stevenzektyramadhan/Audit-Mutu-Internal`.
- Branch milestone: `codex/m3-spmi-master-versioning`.
- Commit implementasi M0/M1:
  `87b4b165a362311b5d6cbc4035a8960a71e5a6f1`.
- M0 dan M1 selesai.
- M2-01, M2-02, M2-03, M3-01, dan M3-02 sudah diimplementasikan dan diverifikasi di Windows.
- Task berikutnya: **M3-03 — Master 21 Standar**.
- Seluruh task M3 memakai branch yang sama; subtask ditandai checkpoint commit.

Commit paling atas dapat berupa commit dokumentasi handoff setelah commit
implementasi tersebut.

## Checkout pada Ubuntu

Untuk clone baru:

```bash
git clone https://github.com/stevenzektyramadhan/Audit-Mutu-Internal.git
cd Audit-Mutu-Internal
git fetch origin
git switch --track origin/codex/m3-spmi-master-versioning
git status --short
git log --oneline -3
```

Untuk clone yang sudah ada:

```bash
git fetch origin
git switch codex/m3-spmi-master-versioning
git pull --ff-only
git status --short
git log --oneline -3
```

Expected baseline:

- `git status --short` tidak menghasilkan output;
- history memuat commit implementasi `87b4b16`;
- `docs/handoff/CURRENT_HANDOFF.md` tersedia.

## Urutan baca untuk AI agent

1. Baca seluruh `docs/handoff/CURRENT_HANDOFF.md`.
2. Baca `CODEX_IMPLEMENTATION_PLAN_SPMI_AMI_RTM.md`.
3. Baca `docs/architecture/current-state.md`.
4. Baca `docs/database/current-schema.md`.
5. Baca security document yang berkaitan dengan task yang akan disentuh.
6. Baca ADR terkait sebelum mengubah boundary service, authorization, file,
   versioning, snapshot, atau legacy flow.

Prompt awal yang dapat diberikan kepada AI agent:

```text
Baca docs/handoff/UBUNTU_AI_AGENT_START.md dan seluruh
docs/handoff/CURRENT_HANDOFF.md. Verifikasi branch, HEAD, working tree,
dependency, dan seluruh baseline test terlebih dahulu. Jangan mengubah M0/M1,
jangan menjalankan migration pada database bersama/production. Lanjutkan
seluruh task M3 pada branch milestone M3 yang sudah ada. Pertahankan keputusan
ADR, central authorization, private file boundary, security headers, dan
immutable audit ledger.
```

## Environment Ubuntu

Pilih satu workflow dan gunakan secara konsisten:

- PHP/Apache/MySQL native Ubuntu; atau
- Docker Compose repository.

Jangan menyalin file `.env`, credential, log, session, private storage, dump
database, atau data pengguna dari Windows ke Git. Buat konfigurasi development
Ubuntu sendiri berdasarkan `.env.example`, dan simpan nilai sebenarnya di luar
repository.

Sebelum menjalankan aplikasi:

```bash
php --version
composer --version
composer install
```

Repository mensyaratkan PHP minimal 7.4; baseline Windows dan container memakai
PHP 8.3. Pastikan extension PHP yang dibutuhkan CodeIgniter, MySQLi, fileinfo,
mbstring, XML, ZIP, dan spreadsheet tersedia.

## Database Ubuntu

- Untuk database development baru dan kosong, gunakan `database_schema.sql`
  sebagai fresh-install schema.
- Jangan menjalankan seluruh migration `001`–`017` terhadap fresh schema.
  Migration historis tidak semuanya idempotent dan terdapat dua nomor `009`.
- Untuk database existing, backup dan audit schema secara read-only sebelum
  menentukan migration. Migration `010`–`017` dirancang idempotent; migration
  015, 016, dan 017 telah diterapkan pada database development MySQL 8.4.3
  dan diuji lewat database disposable.
- Migration Windows tidak otomatis berarti database Ubuntu sudah ter-upgrade.
- Jangan memakai database production atau database bersama untuk smoke test.

## Baseline verification Ubuntu

Jalankan:

```bash
php tests/security_audit_regression.php
php tests/security_headers_regression.php
php tests/file_security_regression.php
php tests/output_encoding_regression.php
php tests/authentication_security_regression.php
php tests/authorization_policy_regression.php
php tests/security_configuration_regression.php
php tests/hardening_regression.php
php tests/account_settings_regression.php
php tests/organization_units_regression.php
php tests/user_unit_assignments_regression.php
php tests/role_capability_matrix_regression.php
php tests/spmi_versions_regression.php
php tests/smoke/run.php
```

Expected result dari Windows:

- audit regression: 116 checks;
- security headers: 134 checks;
- file security: 100 checks;
- output encoding: 28 checks;
- authentication: 29 checks;
- authorization: 54 checks;
- organization units: 42 checks;
- user unit assignments: 30 checks;
- role capability matrix: 155 checks;
- SPMI version foundation/workflow: 115 checks;
- tiga regression legacy/configuration lainnya lulus;
- smoke suite: 34 cases.

Full PHP lint Windows juga lulus untuk 166 file. Ulangi lint di Linux agar
case sensitivity, path separator, permission, dan dependency Linux ikut
terverifikasi.

## Guardrail sebelum melanjutkan M3-03

- Jangan reset, rewrite, atau squash history tanpa persetujuan maintainer.
- Jangan mengubah migration historis yang sudah digunakan.
- Jangan memasukkan secret atau data nyata ke repository/test fixture.
- Jangan melemahkan fail-closed production config, CSRF, CSP, authorization,
  file validation, atau append-only audit ledger.
- Catat perbedaan hasil Linux terhadap baseline Windows di
  `docs/handoff/CURRENT_HANDOFF.md` atau handoff baru sebelum implementasi.
- Tetap gunakan branch `codex/m3-spmi-master-versioning`. Jangan membuat branch
  baru untuk tiap subtask M3.
- Jangan mengubah activation/retirement M3-02 tanpa mempertahankan combined
  `spmi.version.manage` + organization-unit guard, transaksi state transition,
  private file ownership, audit event, dan negative test.
- Master standar M3-03 harus berelasi ke `spmi_versions`, tidak menimpa tabel
  legacy atau mengubah versi aktif secara langsung.
