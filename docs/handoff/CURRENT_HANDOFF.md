# Current Handoff — M0, M1, M2-01, dan M2-02

- **Tanggal handoff:** 2026-07-25
- **Workspace asal:** Windows 11, Laragon, PHP 8.3.30, MySQL 8.4.3
- **Status:** M0 dan M1 selesai; M2-01 dan M2-02 sudah diimplementasikan dan diverifikasi. Task berikutnya M2-03.

Dokumen ini tidak memuat secret, password, API key, isi `.env`, credential
database, atau data pengguna.

## 1. Branch

- Branch integrasi asal: `dev`.
- Branch M1: `codex/m1-security-foundation-handoff`.
- Branch kerja seluruh milestone M2: `codex/m2-organization-role-scope`.
- Satu branch dipakai untuk seluruh task M2; tiap subtask menjadi checkpoint
  commit, bukan branch baru.
- Checkpoint M2-01: `45c6a6d feat: implement M2-01 organization unit master`.
- Branch M2 belum di-push pada saat dokumen ini diperbarui.
- Dokumen ringkas untuk agent Ubuntu/OpenCode:
  `docs/handoff/UBUNTU_AI_AGENT_START.md`.

## 2. Commit SHA terakhir

- Commit implementasi M0/M1:
  `87b4b165a362311b5d6cbc4035a8960a71e5a6f1`.
- Short SHA implementasi: `87b4b16`.
- Subject: `feat: complete M0 discovery and M1 security foundation`.
- Commit dasar dari `dev`:
  `31791355f4699c230a166ccd7c5ce889b6b6516b`.
- Commit dokumen handoff adalah `HEAD` branch setelah checkout/pull. Gunakan
  `git rev-parse HEAD` karena sebuah commit tidak dapat menyimpan SHA miliknya
  sendiri di dalam isinya.

## 3. Milestone dan task yang selesai

### M0 — Discovery dan baseline

- M0-01 — Buat Peta Arsitektur Aktual.
- M0-02 — Buat Inventaris Database.
- M0-03 — Baseline Test dan Smoke Test.
- M0-04 — Decision Log dan ADR.

### M1 — Security Foundation

- M1-01 — Threat Modeling.
- M1-02 — Security Configuration Audit.
- M1-03 — Session dan Authentication Hardening.
- M1-04 — Central Authorization Guard.
- M1-05 — Output Encoding dan XSS Protection.
- M1-06 — File Security Foundation.
- M1-07 — Security Headers.
- M1-08 — Immutable Audit Log.

### M2 — Organisasi, Role, dan Scope

- M2-01 — Master Unit Organisasi.
- M2-02 — Keanggotaan User dan Jabatan.

## 4. Acceptance criteria yang dipenuhi

### M0-01

- `docs/architecture/current-state.md` memetakan entry point, runtime,
  framework, dependency, database, session, CSRF, cookie, storage, route,
  controller, service, model, migration, test, dan deployment.
- Klaim current state diberi referensi path aktual.
- Implementasi duplikat/legacy/tidak pasti dan alur login sampai laporan
  didokumentasikan.
- Task dokumentasi ini tidak mengubah behavior aplikasi.

### M0-02

- `docs/database/current-schema.md` membandingkan schema repository,
  migration, query model, dan metadata runtime lokal tanpa mencetak nilai row.
- Constraint/index yang hilang, ambiguity migration, dan risiko data dicatat.
- `scripts/database/audit_readonly.php` menyediakan pemeriksaan read-only
  untuk orphan, state/flag tidak konsisten, duplikasi, periode, skor, dan
  referensi file.

### M0-03

- Smoke suite dapat dijalankan dengan satu command.
- Runner mengembalikan exit code non-zero ketika kasus gagal.
- Suite tidak memerlukan internet dan memakai database disposable, bukan
  database production.
- Cakupan meliputi login, dashboard role, penugasan, draft/submit Auditee,
  penilaian Auditor, ownership/IDOR, laporan, output, file, header, auth, dan
  immutable ledger.

### M0-04

- Enam ADR wajib dibuat dengan konteks, keputusan, alternatif, konsekuensi,
  risiko, dan status.
- Decision register untuk keputusan bisnis yang belum pasti tersedia.

### M1-01

- Threat model memuat asset dan skenario minimum dari implementation plan.
- Setiap threat mempunyai impact, existing control, required control, dan
  rencana test.

### M1-02

- Production fail-closed untuk environment/URL/Host, encryption key, cookie
  secure, log/session/private storage, debug, dan credential database yang
  berbahaya.
- Secret hanya dibaca dari environment; `.env.example` tidak berisi secret.
- Error 5xx production generik dan tidak membocorkan stack trace/path.
- Request/log mempunyai correlation ID; log melakukan normalisasi, redaksi,
  pembatasan ukuran pesan, dan permission file ketat.
- Runbook konfigurasi production terdokumentasi.

### M1-03

- Session ID diregenerasi setelah login dan ID lama dihancurkan.
- Logout hanya POST + CSRF dan menghancurkan session.
- Idle timeout 30 menit dan absolute timeout 8 jam diterapkan.
- Login dibatasi per identitas ter-normalisasi dan per IP pada window bersama
  di database.
- Pesan gagal login generik; akun nonaktif ditolak.
- Perubahan password/role/status menaikkan `session_version` sehingga session
  lama dicabut.
- Password memakai API hashing bawaan PHP.
- Event autentikasi tidak menyimpan password, email mentah, atau IP mentah.

### M1-04

- Capability dan object/state authorization dipusatkan di
  `Authorization_policy` dan `Auth_guard`.
- Controller utama tidak lagi mengandalkan role atau ID URL saja.
- Query participant membatasi scope Auditee/Auditor.
- Super Admin override eksplisit, membutuhkan alasan, dan dicatat.
- Policy untuk domain yang belum ada bersifat deny-default.
- Negative test mencakup cross-user assignment, cross-auditor assessment,
  evidence IDOR, final-state mutation, dan capability denial.

### M1-05

- Output HTML, teks, URL HTTP(S), dan JSON memakai helper context-specific.
- Input dinamis pada view aktif di-escape.
- URL bukti dibatasi ke HTTP/HTTPS.
- XLSX menulis input sebagai data/string, bukan formula aktif.
- Payload HTML/SVG/script/entity/formula diuji.
- Rich text tetap tidak diaktifkan karena belum ada kebutuhan dan sanitizer
  allowlist khusus.

### M1-06

- Upload/download sensitif melewati `File_security`.
- File private disimpan di luar document root pada production.
- Storage name acak, original name hanya metadata.
- Extension, MIME dari isi, signature/content, ukuran kategori, dan path
  traversal divalidasi.
- File executable/script/HTML/SVG/archive yang tidak diizinkan ditolak.
- Download memakai authorization, attachment, `nosniff`, CSP sandbox,
  `no-store`, dan checksum verification.
- Registry file, security event, soft-delete, retention, temporary cleanup,
  dan guarded legacy migration tersedia.

### M1-07

- CSP nonce, frame denial, `nosniff`, referrer policy, permissions policy,
  COOP/CORP, cross-domain policy, dan no-store diterapkan secara global.
- HSTS hanya aktif untuk production HTTPS.
- Script tidak memakai `unsafe-inline` atau `unsafe-eval`; inline event
  handler ditolak.
- Header halaman dan download diuji melalui regression dan HTTP smoke.

### M1-08

- `security_audit_logs` dan chain-state dibuat.
- Audit append-only: model tidak menyediakan update/delete dan trigger
  database menolak keduanya.
- Event memakai UUID, request ID, before/after HMAC, allowlisted summary,
  pseudonymized network identifiers, previous hash, dan entry hash.
- Writer diserialisasi melalui row lock pada chain state; verifier CLI
  memvalidasi seluruh rantai.
- Request body, password, token, cookie, authorization header, dan field
  sensitif tidak dicatat.
- Login/logout, role/account change, master/indicator/period change,
  assignment, Auditee submit/reopen, Auditor submit, file lifecycle, override,
  dan sensitive export pada surface yang tersedia sudah terintegrasi.
- Tidak ada UI edit/delete ledger.

## 5. File yang dibuat atau diubah

Daftar berikut berasal dari working tree terhadap commit dasar sebelum file
handoff ini ditambahkan.

### Root dan schema — diubah

- `.gitignore`
- `README.md`
- `compose.yaml`
- `database_schema.sql`
- `index.php`

### Root — dibuat

- `.env.example`
- `BUSINESS_REQUIREMENTS_SPMI_AMI_RTM.md`
- `CODEX_IMPLEMENTATION_PLAN_SPMI_AMI_RTM.md`

### Application configuration/core/helper — diubah

- `application/config/config.php`
- `application/config/database.php`
- `application/core/MY_Controller.php`
- `application/helpers/app_helper.php`

### Application configuration/core — dibuat

- `application/config/security_bootstrap.php`
- `application/config/security_headers.php`
- `application/core/MY_Exceptions.php`
- `application/core/MY_Log.php`

### Controllers — diubah

- `application/controllers/Account.php`
- `application/controllers/Auditee.php`
- `application/controllers/Auditor.php`
- `application/controllers/Auth.php`
- `application/controllers/Dashboard.php`
- `application/controllers/Pertanyaan.php`
- `application/controllers/Profil.php`
- `application/controllers/Standar.php`
- `application/controllers/Tugas_audit.php`
- `application/controllers/Users.php`
- `application/controllers/auditee/Tugas.php`
- `application/controllers/lpmpi/Akun.php`
- `application/controllers/lpmpi/Instrumen.php`
- `application/controllers/lpmpi/Laporan.php`
- `application/controllers/lpmpi/Penetapan.php`
- `application/controllers/lpmpi/Penugasan.php`

### Controllers — dibuat

- `application/controllers/Maintenance.php`
- `application/controllers/Organization_units.php`
- `application/controllers/User_unit_assignments.php`

### Libraries — diubah

- `application/libraries/Auth_guard.php`

### Libraries — dibuat

- `application/libraries/Audit_logger.php`
- `application/libraries/Auth_security.php`
- `application/libraries/Authorization_policy.php`
- `application/libraries/File_security.php`

### Models — diubah

- `application/models/Jawaban_model.php`
- `application/models/User_model.php`

### Models — dibuat

- `application/models/Auth_security_event_model.php`
- `application/models/File_asset_model.php`
- `application/models/Organization_unit_model.php`
- `application/models/Security_audit_log_model.php`
- `application/models/User_unit_assignment_model.php`

### Services — diubah

- `application/services/Auth_service.php`
- `application/services/Periode_service.php`
- `application/services/Pertanyaan_service.php`
- `application/services/Standar_service.php`
- `application/services/Tugas_audit_service.php`
- `application/services/User_service.php`

### Services — dibuat

- `application/services/Organization_unit_service.php`
- `application/services/User_unit_assignment_service.php`

### Views — diubah

- `application/views/account/index.php`
- `application/views/auditee/form_isian.php`
- `application/views/auditee/inbox.php`
- `application/views/auditee/index.php`
- `application/views/auditee/isi.php`
- `application/views/auditee/tugas.php`
- `application/views/auditor/form_penilaian.php`
- `application/views/auditor/inbox.php`
- `application/views/auditor/index.php`
- `application/views/auditor/nilai.php`
- `application/views/auditor/tugas.php`
- `application/views/auth/login.php`
- `application/views/dashboard/auditee.php`
- `application/views/dashboard/auditor.php`
- `application/views/dashboard/super_admin.php`
- `application/views/errors/html/error_404.php`
- `application/views/errors/html/error_db.php`
- `application/views/errors/html/error_exception.php`
- `application/views/errors/html/error_general.php`
- `application/views/errors/html/error_php.php`
- `application/views/layouts/footer.php`
- `application/views/layouts/header.php`
- `application/views/layouts/sidebar.php`
- `application/views/lpmpi/akun/form.php`
- `application/views/lpmpi/akun/index.php`
- `application/views/lpmpi/akun/list_auditee.php`
- `application/views/lpmpi/akun/list_auditor.php`
- `application/views/lpmpi/instrumen/index.php`
- `application/views/lpmpi/laporan/detail.php`
- `application/views/lpmpi/laporan/index.php`
- `application/views/lpmpi/penetapan/index.php`
- `application/views/lpmpi/penugasan/form.php`
- `application/views/lpmpi/penugasan/index.php`
- `application/views/lpmpi/periode/form.php`
- `application/views/lpmpi/periode/index.php`
- `application/views/lpmpi/profil/form_edit.php`
- `application/views/lpmpi/profil/index.php`
- `application/views/lpmpi/organization_units/form.php`
- `application/views/lpmpi/organization_units/index.php`
- `application/views/lpmpi/user_unit_assignments/form.php`
- `application/views/lpmpi/user_unit_assignments/index.php`
- `application/views/pertanyaan/create.php`
- `application/views/pertanyaan/edit.php`
- `application/views/pertanyaan/import_preview.php`
- `application/views/pertanyaan/index.php`
- `application/views/standar/create.php`
- `application/views/standar/edit.php`
- `application/views/standar/index.php`
- `application/views/tugas_audit/create.php`
- `application/views/tugas_audit/hasil.php`
- `application/views/tugas_audit/index.php`
- `application/views/tugas_audit/show.php`
- `application/views/users/create.php`
- `application/views/users/edit.php`
- `application/views/users/index.php`
- `application/views/welcome_message.php`

### Documentation — dibuat

- `docs/adr/0001-service-layer-boundary.md`
- `docs/adr/0002-spmi-versioning.md`
- `docs/adr/0003-audit-snapshot.md`
- `docs/adr/0004-private-file-storage.md`
- `docs/adr/0005-role-and-scope-authorization.md`
- `docs/adr/0006-legacy-migration-strategy.md`
- `docs/adr/README.md`
- `docs/architecture/current-state.md`
- `docs/database/current-schema.md`
- `docs/product/decision-register.md`
- `docs/security/authentication-hardening.md`
- `docs/security/authorization-policy.md`
- `docs/security/configuration-audit.md`
- `docs/security/file-security.md`
- `docs/security/immutable-audit-log.md`
- `docs/security/output-encoding.md`
- `docs/security/security-headers.md`
- `docs/security/threat-model.md`
- `docs/handoff/CURRENT_HANDOFF.md`
- `docs/milestones/M2-01-master-unit-organisasi.md`
- `docs/milestones/M2-02-user-unit-assignments.md`

### Migration dan scripts — dibuat

- `migrations/012_authentication_hardening.sql`
- `migrations/013_file_security_foundation.sql`
- `migrations/014_immutable_security_audit_log.sql`
- `migrations/015_create_organization_units.sql`
- `migrations/016_create_user_unit_assignments.sql`
- `scripts/database/apply_local_m1_06.php`
- `scripts/database/apply_local_m1_08.php`
- `scripts/database/audit_readonly.php`
- `scripts/migrate_private_storage.php`

### Tests — diubah

- `tests/account_settings_regression.php`
- `tests/hardening_regression.php`

### Tests — dibuat

- `tests/authentication_security_regression.php`
- `tests/authorization_policy_regression.php`
- `tests/file_security_regression.php`
- `tests/output_encoding_regression.php`
- `tests/security_audit_regression.php`
- `tests/security_configuration_regression.php`
- `tests/security_headers_regression.php`
- `tests/organization_units_regression.php`
- `tests/user_unit_assignments_regression.php`
- `tests/smoke/README.md`
- `tests/smoke/run.php`

## 6. Migration yang dibuat

| Migration | Tujuan |
|---|---|
| `012_authentication_hardening.sql` | Status akun, session version, metadata login/password, dan `auth_security_events`. |
| `013_file_security_foundation.sql` | Registry `file_assets`, `file_security_events`, checksum, lifecycle, dan retention. |
| `014_immutable_security_audit_log.sql` | Immutable ledger, chain state, index, dan trigger penolak update/delete. |
| `015_create_organization_units.sql` | Hierarki organisasi, code unik, status aktif, self FK, dan seed universitas root. |
| `016_create_user_unit_assignments.sql` | Assignment user/unit/jabatan, masa berlaku, primary, index overlap, check, dan FK RESTRICT. |

Migration `001`–`011` sudah ada sebelum rangkaian kerja ini. Terdapat dua file
bernomor `009`.

## 7. Migration yang dijalankan dan environment

| Migration/upgrade | Environment | Hasil |
|---|---|---|
| `012_authentication_hardening.sql` | Development lokal Windows/Laragon, MySQL 8.4.3 | Dijalankan dua kali; berhasil dan hasil idempotent. |
| `013_file_security_foundation.sql` | Development lokal Windows/Laragon, MySQL 8.4.3 | Dijalankan dua kali; berhasil dan hasil idempotent. |
| `014_immutable_security_audit_log.sql` | Development lokal Windows/Laragon, MySQL 8.4.3 | Dijalankan dua kali; kedua tabel dan dua trigger terverifikasi. |
| `015_create_organization_units.sql` | Development lokal Windows/Laragon dan database disposable, MySQL 8.4.3 | Schema, index, self FK, dan seed root terverifikasi. |
| `016_create_user_unit_assignments.sql` | Development lokal Windows/Laragon, MySQL 8.4.3 | Tabel, tiga index, dua FK RESTRICT, dua check constraint, dan 0 initial rows terverifikasi. |
| `database_schema.sql` | Database disposable milik smoke suite | Import berhasil untuk membuat baseline test terisolasi. |

Audit read-only terakhir menemukan 15 base tables dan 171 columns melalui
migration 014. Tidak ada migration yang dijalankan atau diverifikasi pada
production. Repository tidak memiliki migration ledger, sehingga keberadaan
efek migration lama tidak membuktikan kapan file `001`–`011` dijalankan.

## 8. Perubahan konfigurasi keamanan

- `CI_ENV` harus `development`, `testing`, atau `production`; nilai lain gagal
  tertutup.
- Production mewajibkan base URL HTTPS valid dan Host allowlist.
- Trusted proxy hanya menerima IP/CIDR tervalidasi.
- Encryption key wajib berasal dari environment.
- Cookie production wajib Secure; HttpOnly dan SameSite Lax dipertahankan.
- CSRF global dipertahankan; logout diubah menjadi POST + CSRF.
- Log/session/private storage production wajib existing, writable, absolute,
  dan di luar document root.
- Production log threshold dibatasi ke level non-debug dan query recording
  dinonaktifkan.
- Database user privileged dan credential default/development ditolak.
- Error 5xx production diganti response generik dengan request ID.
- Correlation ID dibuat server-side, dikirim sebagai `X-Request-ID`, dan
  ditambahkan ke log aman.
- Session memakai idle/absolute timeout, regeneration dengan old-ID destroy,
  dan database-backed revocation.
- File policy dipusatkan berdasarkan kategori, ukuran, extension, MIME,
  signature, storage scope, checksum, dan retention.
- Security headers global dan nonce CSP diterapkan; HSTS conditional pada
  production HTTPS.
- Audit security/domain memakai HMAC/redaction; raw credential, cookie,
  request body, IP, dan user-agent tidak disimpan.
- Compose development tidak lagi mempunyai fallback password bawaan.

## 9. Command test yang dijalankan

Command generik berikut dijalankan dengan binary PHP Laragon eksplisit pada
Windows. `php` dapat menggantikan path binary pada Linux.

```powershell
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
php tests/smoke/run.php
```

Validasi tambahan:

```powershell
$env:CI_ENV = 'development'
php scripts/database/audit_readonly.php schema
php scripts/database/audit_readonly.php checks
php index.php maintenance verify_audit_log
git diff --check
```

Targeted PHP lint M2-01 dijalankan terhadap seluruh file PHP baru dan berubah.
Sebelum commit implementasi, full PHP lint juga dijalankan terhadap seluruh
150 file PHP di `application`, `tests`, dan `scripts`.

## 10. Hasil aktual setiap test

| Command | Hasil aktual terakhir |
|---|---|
| `php tests/security_audit_regression.php` | PASS — 116 checks. |
| `php tests/security_headers_regression.php` | PASS — 130 checks. |
| `php tests/file_security_regression.php` | PASS — 100 checks. |
| `php tests/output_encoding_regression.php` | PASS — 28 checks. |
| `php tests/authentication_security_regression.php` | PASS — 29 checks. |
| `php tests/authorization_policy_regression.php` | PASS — 44 checks. |
| `php tests/security_configuration_regression.php` | PASS — security configuration regression checks passed. |
| `php tests/hardening_regression.php` | PASS — hardening regression checks passed. |
| `php tests/account_settings_regression.php` | PASS — account settings regression checks passed. |
| `php tests/organization_units_regression.php` | PASS — 42 checks. |
| `php tests/user_unit_assignments_regression.php` | PASS — 30 checks. |
| `php tests/smoke/run.php` | PASS — 32 cases; database disposable dibersihkan oleh successful run. |
| Targeted M2-01/M2-02 `php -l` | PASS. |
| Full PHP lint `application`, `tests`, dan `scripts` | PASS — 156 files. |
| `php scripts/database/audit_readonly.php schema` | PASS — local schema terbaca sampai migration 014. |
| `php scripts/database/audit_readonly.php checks` | Command PASS; satu known data issue: 3 tugas tanpa periode valid. |
| `php index.php maintenance verify_audit_log` | PASS — `valid=true`, 0 entries checked, genesis head valid. |
| `git diff --check` | PASS/exit 0; hanya warning normalisasi LF ke CRLF pada Windows. |

Smoke 32-case dijalankan pada branch milestone M2 tanggal 2026-07-25; seluruh
kasus lulus dan disposable database dibersihkan oleh runner.

## 11. Test yang belum dijalankan

- Browser manual/visual dan pemeriksaan console CSP/CDN.
- Linux native dan Docker/Compose end-to-end pada hasil working tree ini.
- Staging/production startup fail-closed dengan secret manager, real virtual
  host, reverse proxy, Host rejection, TLS, dan HSTS.
- Web-server direct-access denial untuk `.git`, `.env*`, `application`,
  `system`, log, session, private storage, backup, dan migration.
- OS ACL/no-execute pada private storage serta permission service account.
- Database TLS, least-privilege production user, dan pencabutan privilege
  update/delete ledger.
- Antivirus/CDR/EICAR, quarantine async, dan malicious OpenXML/image corpus.
- Backup encryption, backup/restore drill, legal hold, dan reconciliation
  database-file-ledger.
- Concurrency/race test untuk submit, finalisasi, dan concurrent audit writers.
- External anchoring/monitoring hash-chain audit.
- Production legacy filesystem migration.

## 12. Known issues

- Tiga dari sembilan tugas pada database development lokal tidak mempunyai
  periode yang valid. Tidak ada row identity atau nilai pengguna disalin.
- Tidak ada migration ledger runtime; status migration lama hanya diinferensi
  dari efek schema.
- Dua migration memakai nomor `009`; urutan numeriknya ambigu.
- Sebagian migration historis tidak idempotent. Migration `005` tidak aman
  diterapkan langsung terhadap current fresh schema.
- Schema masih memakai mixed collation `utf8mb3`, `utf8mb4`, dan ASCII.
- Ledger lokal saat verifikasi masih kosong; verifier membuktikan genesis
  state, sedangkan event/tamper behavior dibuktikan pada disposable smoke DB.
- Audit event domain ditulis setelah transaksi bisnis pada beberapa legacy
  flow, sehingga belum atomic dengan perubahan bisnis.
- Route/controller legacy masih ada; reachability dan pemakaian aktual belum
  seluruhnya dibuktikan.
- CSP masih memerlukan `style-src-attr 'unsafe-inline'` karena inline style
  legacy. Script tetap nonce-only.
- Frontend bergantung pada CDN; availability dan console browser belum diuji.
- `composer.lock` diabaikan Git, sehingga build dependency clone baru belum
  sepenuhnya reproducible.
- README masih memiliki satu pernyataan lama yang menyebut rate limit login
  belum tersedia, sementara M1-03 sudah mengimplementasikannya. Jangan
  menjadikan kalimat tersebut sebagai sumber current behavior.
- Disposable database/temp artifact dari smoke run yang pernah terinterupsi
  mungkin masih ada. Validasi target secara read-only sebelum cleanup; jangan
  melakukan penghapusan massal.

## 13. Keputusan teknis

- Pertahankan CodeIgniter 3 dan harden current flow secara incremental sebelum
  refactor domain besar.
- Gunakan centralized boundaries: `Authorization_policy`, `File_security`,
  dan `Audit_logger`.
- Authorization memadukan capability, ownership/object state, scoped query,
  deny-default, dan explicit audited override.
- Domain yang belum memiliki model (organisasi/RTM/follow-up) tetap
  deny-default, bukan diimplementasikan secara spekulatif.
- File sensitif disimpan private; nama storage acak dan nama asli hanya
  metadata.
- Lifecycle file memakai soft-delete dan default retention 90 hari.
- Security identifiers dan audit snapshots memakai HMAC-SHA-256; key berasal
  dari encryption key environment.
- General audit ledger memakai serialized SHA-256 hash chain dan database
  trigger append-only.
- `actor_user_id` ledger sengaja tidak mempunyai foreign key agar penghapusan
  akun tidak menghapus/merusak histori audit.
- Metadata audit memakai allowlist dan tidak membaca request body/cookie.
- CSP memakai nonce per request; inline JavaScript dilarang.
- Migration tetap berupa SQL manual karena CodeIgniter migration masih
  disabled; runner lokal diberi guard host/environment.
- Rollback tidak menghapus ledger atau private file; restore database dan file
  harus diperlakukan sebagai satu set.

## 14. Perbedaan terhadap implementation plan

- M1-04 hanya dapat menerapkan policy nyata untuk domain yang sudah ada.
  Policy RTM, PIC, dan follow-up dibuat deny-default karena modelnya baru ada
  pada milestone berikutnya.
- M1-05 tidak mengaktifkan rich text; seluruh field aktif diperlakukan sebagai
  plain text. Export PDF belum ada, sedangkan XLSX sudah diamankan.
- M1-06 belum mengimplementasikan optional antivirus/CDR/quarantine async.
- M1-07 masih mengizinkan `style-src-attr 'unsafe-inline'` untuk style legacy,
  tetapi `script-src` tetap tanpa `unsafe-inline`/`unsafe-eval`.
- M1-08 menambah `event_uuid`, `outcome`, `previous_hash`, `entry_hash`, dan
  tabel chain state di luar field minimum plan.
- Field bernama `ip_address` dan `user_agent` pada ledger berisi nilai HMAC
  berprefiks, bukan nilai mentah.
- Event finalisasi report, finalisasi RTM, perubahan PIC/target, dan verifikasi
  follow-up belum dapat diintegrasikan karena modul tersebut belum ada.
- Standard version create/activate yang sebenarnya belum ada sampai M3;
  current master standard/indicator/period changes yang tersedia sudah dicatat.
- Smoke harness custom dipakai karena repository belum mempunyai test framework
  terintegrasi yang memadai.

## 15. Risiko keamanan yang masih terbuka

- MFA/SSO dan alerting login/security event belum tersedia.
- Tidak ada formal retention/legal-hold policy untuk audit log dan file.
- Tidak ada external hash anchoring atau independent integrity monitor.
- Audit append failure belum fail-closed terhadap seluruh legacy business
  action.
- DB application privilege production belum dibuktikan least-privilege.
- TLS/reverse proxy/Host/HSTS/ACL/backup controls belum diverifikasi pada
  deployment aktual.
- Antivirus/CDR dan scanning pipeline belum tersedia.
- Inline style CSP exception masih memperlebar style injection surface.
- CDN/SRI/offline availability belum diputuskan.
- Race condition submit/finalisasi dan replay/idempotency belum diselesaikan.
- GET side-effect pada route legacy masih perlu inventory lanjutan.
- Scope organisasi belum ada sampai M2; current policy hanya memakai role dan
  ownership yang tersedia.
- Final business records masih mutable; immutable audit ledger tidak sama
  dengan immutable final snapshot.

## 16. Risiko integritas atau migrasi data

- Tiga tugas development lokal tidak mempunyai periode valid.
- Tidak ada runtime migration ledger dan terdapat nomor migration `009`
  duplikat.
- Historical migrations `001`–`011` tidak seluruhnya idempotent; jangan
  menjalankan ulang secara massal.
- DDL MySQL melakukan implicit commit; rollback comment bukan transactional
  rollback.
- Migration 014 merekonstruksi trigger dan perlu maintenance window.
- Mixed collation dapat menimbulkan perbedaan comparison/index saat schema
  baru memakai `utf8mb4`.
- Business mutation dan general audit append belum selalu satu transaksi.
- Database, private file, checksum registry, dan ledger harus dibackup serta
  direstore sebagai satu set.
- Legacy file migration belum dijalankan pada production.
- Soft-delete/retention tanpa legal hold dapat menyebabkan purge yang tidak
  sesuai kebutuhan regulator bila scheduler diaktifkan terlalu awal.

## 17. Exact milestone dan task berikutnya

- Milestone aktif: **M2 — Organisasi, Role, dan Scope**.
- M2-01 dan M2-02 sudah diimplementasikan dan memenuhi acceptance target.
- Task berikutnya: **TASK M2-03 — Matriks Role-Capability**.
- M2-03 belum dimulai.

Acceptance M2-01 yang sudah diverifikasi:

- hierarchy universitas/fakultas atau UPPS/program studi/lembaga/biro/unit
  dapat direpresentasikan;
- program studi terhubung ke fakultas;
- unit dapat dinonaktifkan tanpa menghapus histori;
- code unik;
- tersedia seed universitas root.

Detail implementasi, aturan hierarki, migration, risiko legacy, dan hasil test:
`docs/milestones/M2-01-master-unit-organisasi.md`.

Acceptance M2-02 yang sudah diverifikasi:

- satu user dapat mempunyai beberapa assignment unit/jabatan;
- masa berlaku dan primary assignment tervalidasi tanpa periode overlap;
- assignment lama diakhiri dengan tanggal dan tidak dihapus;
- assignment aktif menjadi sumber policy membership langsung;
- kolom unit legacy tidak di-backfill otomatis.

Detail M2-02:
`docs/milestones/M2-02-user-unit-assignments.md`.

## 18. Langkah pertama agent di Linux

Langkah pertama agent Linux adalah checkout remote branch, memastikan working
tree bersih, dan membaca dua dokumen handoff sebelum menjalankan Docker atau
migration:

```bash
git fetch origin
git switch --track origin/codex/m2-organization-role-scope
git status --short
git rev-parse HEAD
cat docs/handoff/UBUNTU_AI_AGENT_START.md
cat docs/handoff/CURRENT_HANDOFF.md
```

Jika branch lokal dengan nama yang sama sudah ada, gunakan
`git switch codex/m2-organization-role-scope` lalu `git pull --ff-only`.

Agent Linux harus memastikan checkpoint M2-01 `45c6a6d` berada dalam history
dan working tree bersih, menyiapkan environment development sendiri tanpa
menyalin secret Windows, lalu menjalankan seluruh regression termasuk M2-01
dan M2-02 serta smoke 32-case. Untuk database existing development, jalankan
migration 015 lalu 016; fresh install memakai `database_schema.sql`. Jangan
menjalankan migration terhadap database bersama atau production tanpa
prosedur DBA/deployment. Lanjutkan M2-03 pada branch milestone yang sama dan
buat checkpoint commit, bukan branch task baru.
