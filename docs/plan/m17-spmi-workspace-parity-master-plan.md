# M17 — SPMI Workspace Parity & Cutover Preparation

> **Dokumen kendali utama untuk AI coding agent**
>
> Letakkan file ini di:
>
> `docs/plans/m17-spmi-workspace-parity-master-plan.md`

---

## 0. One-Time Agent Instruction

Gunakan instruksi berikut satu kali saja ketika memulai pekerjaan:

```text
Read docs/plans/m17-spmi-workspace-parity-master-plan.md completely.

Execute this milestone according to the execution mode, task order, gates,
allowlists, acceptance criteria, test requirements, and stop conditions
defined in that file.

Do not invent requirements outside the document.
Do not skip tasks or gates.
Do not modify legacy behavior unless a task explicitly allows it.
Stop immediately on BLOCKED, failed migration, failed required test,
authorization uncertainty, or conflict with the approved design contract.

At the end of every task, update only the Task Status Table and Execution Log
inside the plan file, commit the focused task, then continue only when the
next task's gate is satisfied.
```

---

## 1. Execution Mode

Default execution mode:

```text
AUTONOMOUS_SEQUENTIAL_WITH_HARD_GATES
```

Artinya:

1. Agent membaca seluruh dokumen sebelum mengubah apa pun.
2. Agent menjalankan task sesuai urutan M17-00 sampai M17-08.
3. Agent hanya boleh melanjutkan ketika gate task sebelumnya terpenuhi.
4. Agent membuat satu commit terfokus untuk setiap task implementasi.
5. Agent berhenti jika terjadi kondisi `BLOCKED`, kegagalan migration, kegagalan test wajib, konflik desain, atau risiko kehilangan data.
6. Agent tidak meminta prompt panjang baru untuk task berikutnya.
7. Agent memperbarui checklist status dan execution log di file ini setelah setiap task.
8. Agent tidak boleh mengubah requirement, scope, atau product decision di file ini.
9. Agent hanya boleh mengubah:
   - status task;
   - commit SHA;
   - ringkasan hasil;
   - test result;
   - blocker;
   - execution log.

### Hard Gate Khusus M17-00

M17-00 menghasilkan design contract. Agent boleh melanjutkan otomatis ke M17-01 hanya jika:

- tidak ada keputusan berstatus `PRODUCT_APPROVAL_REQUIRED`;
- tidak ada konflik dengan schema atau lifecycle yang sudah ada;
- seluruh keputusan penting dapat ditetapkan dari requirement di dokumen ini;
- design contract berstatus `APPROVED_BY_PLAN`.

Jika masih ada keputusan produk yang tidak dapat diselesaikan dari dokumen ini, agent harus:

```text
STATUS: BLOCKED_PRODUCT_DECISION
```

Lalu berhenti tanpa memulai M17-01.

---

## 2. Product Direction

SPMI menjadi alur utama untuk audit baru.

Kemampuan AMI legacy yang berguna diadopsi ke workspace SPMI tanpa menyalin seluruh desain legacy dan tanpa merusak lifecycle SPMI.

Target akhir:

```text
Admin LPMPI
→ membuat instrumen dan aturan bukti
→ membuat siklus dan penugasan

Auditee
→ mengisi realisasi
→ menambahkan URL dan/atau file bukti
→ menyimpan draft
→ submit

Auditor
→ membaca realisasi dan seluruh bukti
→ mengembalikan submission untuk revisi sebelum finalisasi bila diperlukan
→ memberi skor
→ mencatat temuan
→ memilih OB/KTS bila ada temuan
→ memberi rekomendasi
→ finalisasi

System
→ membuat report snapshot immutable
→ menampilkan hasil final kepada auditee
→ meneruskan report ke RTM
→ mengelola tindak lanjut
```

---

## 3. Locked Product Decisions

Keputusan berikut sudah dianggap final untuk milestone M17.

### 3.1 SPMI adalah sumber kebenaran untuk audit baru

- Audit baru menggunakan Siklus dan Penugasan SPMI.
- Legacy tidak dihapus dalam M17.
- Legacy tidak dimigrasikan penuh dalam M17.
- Legacy belum disembunyikan dalam M17.
- Legacy tidak boleh rusak akibat perubahan M17.

### 3.2 Assessment dan follow-up tidak boleh dicampur

Assessment auditor menyimpan:

- skor;
- temuan;
- jenis temuan;
- rekomendasi auditor.

Assessment auditor **tidak** menyimpan action plan aktif.

RTM menyimpan:

- keputusan;
- aksi yang disepakati.

Follow-up menyimpan:

- rencana tindakan;
- penanggung jawab;
- tenggat;
- catatan pelaksanaan;
- status;
- penyelesaian;
- verifikasi.

### 3.3 Dua jenis revisi harus dibedakan

Sebelum assessment final:

```text
Auditor menemukan submission belum layak dinilai
→ return for revision
→ auditee memperbaiki
→ resubmit
```

Setelah assessment final:

```text
Temuan sudah menjadi hasil audit
→ report
→ RTM
→ follow-up
```

Follow-up tidak boleh digunakan sebagai pengganti revisi submission.

### 3.4 Finalized assessment immutable

- Assessment finalized tidak dapat dibuka kembali.
- Perubahan setelah finalisasi dilakukan melalui RTM dan follow-up.
- Koreksi administratif terhadap snapshot harus menjadi task terpisah di luar M17.

### 3.5 OB/KTS berada pada assessment item

Target nilai tersimpan:

```text
NULL
ob
kts
```

Makna:

- `NULL` = tidak ada klasifikasi temuan;
- `ob` = observasi;
- `kts` = ketidaksesuaian.

Aturan:

- tidak semua skor wajib memiliki OB/KTS;
- OB/KTS hanya dipilih jika ada temuan;
- jika OB/KTS dipilih, temuan wajib;
- rekomendasi wajib untuk KTS;
- report menyimpan snapshot hasil final, bukan menjadi pemilik data aktif.

### 3.6 Bukti auditee mendukung file dan URL

Per item submission dapat memiliki:

- realisasi;
- evidence URL opsional;
- maksimal lima file evidence sesuai batas sistem saat ini.

Policy bukti per pertanyaan:

```text
none
file
url
either
both
```

Default untuk data instrumen lama:

```text
none
```

### 3.7 Hasil final dapat dilihat auditee

Auditee hanya dapat melihat hasil final milik assignment-nya.

Sumber tampilan hasil:

```text
report snapshot final
```

Bukan draft assessment aktif.

---

## 4. Scope

M17 mencakup:

1. Design contract.
2. Schema foundation.
3. Evidence policy pada instrumen.
4. Evidence URL dan file pada workspace auditee.
5. Submission revision lifecycle.
6. Auditor assessment parity.
7. Report snapshot dan hasil auditee.
8. Runtime serta end-to-end hardening.
9. Cutover readiness audit.

---

## 5. Non-Goals

Agent dilarang melakukan hal berikut dalam M17:

- menghapus fitur AMI legacy;
- menyembunyikan menu legacy;
- menghapus route legacy;
- menjadikan legacy read-only;
- mengubah Penetapan legacy;
- mengubah Periode Audit legacy;
- memigrasikan seluruh data legacy;
- membuat cutover produksi;
- mengubah dashboard di luar kebutuhan status workspace;
- membuka kembali assessment finalized;
- menambahkan rencana perbaikan ke assessment auditor;
- menduplikasi recommendation aktif ke assessment, report, RTM, dan follow-up;
- refactor unrelated;
- mengganti framework;
- mengubah pola arsitektur repository secara besar-besaran;
- memperbaiki fitur di luar scope hanya karena ditemukan saat audit;
- menghapus atau melemahkan test agar pipeline lulus;
- push ke remote tanpa instruksi eksplisit.

Attachment bukti tindak lanjut RTM bukan bagian wajib M17. Jika diperlukan, catat sebagai follow-up milestone.

---

## 6. Source of Truth

Urutan sumber kebenaran:

1. Current repository HEAD.
2. Current schema dan migrations.
3. Routes, controllers, services, models, views.
4. Existing regression tests.
5. Dokumen ini.
6. Audit pendukung:
   - `docs/audit/admin-legacy-vs-spmi-audit.md`
   - `docs/audit/auditee-auditor-legacy-vs-spmi-parity.md`

Audit adalah bukti awal, bukan pengganti pembacaan source aktual.

---

## 7. Global Execution Rules

### 7.1 Preflight

Sebelum setiap task:

1. Tampilkan branch aktif.
2. Tampilkan HEAD SHA.
3. Jalankan `git status --short`.
4. Jangan menyentuh perubahan milik user yang sudah ada.
5. Baca task aktif secara lengkap.
6. Baca design contract setelah M17-00 selesai.
7. Petakan:
   - route;
   - controller;
   - service;
   - model;
   - table;
   - view;
   - test.
8. Tulis daftar file yang diperkirakan berubah.
9. Catat risiko dan asumsi.
10. Berhenti jika scope bertentangan dengan design contract.

### 7.2 Implementation Discipline

- Kerjakan tepat satu task pada satu waktu.
- Satu task implementasi = satu commit terfokus.
- Tidak boleh mencampur refactor unrelated.
- Ikuti konvensi CodeIgniter 3 yang sudah ada.
- Business validation harus berada di service/model, bukan hanya JavaScript atau view.
- Authorization dan ownership harus divalidasi di server.
- Private file harus tetap private.
- Mutasi POST harus mempertahankan CSRF protection.
- Gunakan optimistic/version locking bila contract membutuhkannya.
- Data existing harus tetap valid.
- Migration harus backward-compatible.
- Tidak boleh melanjutkan jika migration tidak dapat diuji dengan aman.

### 7.3 Testing Discipline

Urutan test:

1. Test yang baru dibuat.
2. Targeted regression untuk area task.
3. SPMI workspace regression.
4. Report, RTM, dan follow-up regression yang terdampak.
5. Legacy regression yang relevan.
6. Security dan authorization regression.
7. Runtime HTTP test bila environment mendukung.

Agent wajib membedakan:

```text
STATIC SOURCE-CONTRACT TEST
```

dan:

```text
RUNTIME BEHAVIOR TEST
```

Jangan mengklaim runtime berhasil jika hanya static test yang dijalankan.

### 7.4 Commit Discipline

- Commit hanya setelah acceptance criteria dan test wajib lulus.
- Commit message:
  - `docs(m17): define SPMI workspace parity contract`
  - `feat(m17): add SPMI parity schema foundation`
  - dan seterusnya sesuai task.
- Jangan amend commit lama yang tidak terkait.
- Jangan squash unrelated commit.
- Jangan push.

### 7.5 Stop Conditions

Agent harus berhenti jika:

- ada keputusan produk yang tidak terjawab;
- design contract dan source aktual konflik;
- migration berisiko kehilangan data;
- authorization tidak dapat dipastikan;
- test wajib gagal;
- runtime menunjukkan cross-user access;
- finalized data menjadi editable;
- perubahan membutuhkan modifikasi legacy yang dilarang;
- working tree memiliki konflik dengan perubahan user;
- task memerlukan scope tambahan di luar dokumen ini.

Status yang digunakan:

```text
BLOCKED_PRODUCT_DECISION
BLOCKED_SCHEMA_CONFLICT
BLOCKED_AUTHORIZATION
BLOCKED_TEST_FAILURE
BLOCKED_USER_CHANGES
BLOCKED_SCOPE_CONFLICT
```

---

## 8. Target Lifecycle Contract

### 8.1 Submission State Machine

Target state:

```text
draft
→ submitted
→ returned_for_revision
→ resubmitted
→ under_assessment
→ completed
```

Aturan:

- `draft`: editable oleh auditee pemilik assignment.
- `submitted`: readonly untuk auditee.
- `returned_for_revision`: editable oleh auditee, wajib memiliki revision reason.
- `resubmitted`: readonly untuk auditee, menunggu auditor.
- `under_assessment`: assessment auditor sedang aktif.
- `completed`: assessment finalized dan report final tersedia.

Transisi yang dilarang:

- `completed → draft`
- `completed → returned_for_revision`
- `submitted → draft` tanpa revision request
- perubahan item oleh user bukan pemilik
- revisi setelah assessment finalized

### 8.2 Assessment State Machine

Target state:

```text
draft
→ finalized
```

Assessment draft hanya boleh ada untuk submission:

```text
submitted
resubmitted
under_assessment
```

Aturan:

- draft dapat disimpan sebagian;
- finalisasi hanya jika seluruh item valid;
- finalized immutable;
- return for revision hanya boleh dilakukan sebelum finalized;
- saat return for revision, draft assessment ditangani sesuai design contract M17-00:
  - dibatalkan secara aman; atau
  - dipertahankan sebagai stale draft yang tidak boleh difinalisasi;
- pilihan final harus ditetapkan di M17-00.

---

## 9. Field Ownership Contract

| Field | Owner | Lifecycle | Canonical Storage |
|---|---|---|---|
| Realization | Auditee | Submission | Submission item |
| Evidence URL | Auditee | Submission | Submission item atau child metadata sesuai contract |
| Evidence file | Auditee | Submission | Evidence child table |
| Score | Auditor | Assessment | Assessment item |
| Finding | Auditor | Assessment | Assessment item |
| Finding type OB/KTS | Auditor | Assessment | Assessment item |
| Recommendation | Auditor | Assessment | Assessment item |
| Revision reason | Auditor | Pre-final revision | Revision event/history |
| Submission version | System | Submission lifecycle | Submission/version history |
| Report data | System | Post-finalization | Immutable report snapshot |
| RTM decision | Admin LPMPI/RTM | RTM | RTM decision |
| Agreed action | RTM | RTM | RTM action/decision |
| Improvement plan | Follow-up owner | Follow-up | Follow-up |
| Responsible person | RTM/follow-up owner | Follow-up | Follow-up |
| Due date | RTM/follow-up owner | Follow-up | Follow-up |
| Follow-up note | Follow-up owner | Follow-up | Follow-up |
| Completion verification | Authorized verifier | Follow-up | Follow-up |

---

## 10. Task Dependency Graph

```text
M17-00 Design Contract
  ↓
M17-01 Schema Foundation
  ↓
M17-02 Instrument Evidence Policy
  ↓
M17-03 Auditee Evidence Parity
  ↓
M17-04 Submission Revision Lifecycle
  ↓
M17-05 Auditor Assessment Parity
  ↓
M17-06 Report Snapshot & Auditee Result
  ↓
M17-07 Runtime and E2E Hardening
  ↓
M17-08 Cutover Readiness Audit
```

Tidak boleh melompati dependency.

---

# 11. Task Specifications

## M17-00 — Design Contract SPMI Workspace Parity

### Type

```text
READ-ONLY PLANNING
```

### Allowed File

Hanya:

```text
docs/product/spmi-workspace-parity-contract.md
```

Dan bagian status/log dalam file plan ini.

### Objective

Mengunci desain teknis dan lifecycle sebelum migration atau code production.

### Required Analysis

1. Map current SPMI auditee flow.
2. Map current SPMI auditor flow.
3. Map report generation.
4. Map RTM dan follow-up integration.
5. Tetapkan submission state machine.
6. Tetapkan assessment state machine.
7. Tetapkan return-for-revision behavior.
8. Tetapkan evidence policy.
9. Tetapkan OB/KTS behavior.
10. Tetapkan field ownership.
11. Usulkan exact schema changes.
12. Usulkan route/service/model/controller/view changes.
13. Tetapkan backward compatibility.
14. Tetapkan authorization dan security rules.
15. Tetapkan migration order.
16. Tetapkan test matrix.
17. Tetapkan task-by-task file allowlist.

### Required Document Structure

```text
# SPMI Workspace Parity Contract

## 1. Scope
## 2. Non-Goals
## 3. Current Architecture Map
## 4. Final Workflow
## 5. Submission State Machine
## 6. Assessment State Machine
## 7. Revision Contract
## 8. Evidence Policy Contract
## 9. OB/KTS Contract
## 10. Field Ownership Matrix
## 11. Proposed Schema Changes
## 12. Proposed Route and Service Changes
## 13. Report Snapshot Contract
## 14. Auditee Result Contract
## 15. Authorization and Security Rules
## 16. Backward Compatibility
## 17. Migration Order
## 18. Test Matrix
## 19. Task-by-Task File Allowlist
## 20. Risks and Rollback Strategy
## 21. Decisions Requiring Product Approval
```

### Required Decisions

Contract harus menetapkan tanpa ambigu:

- penyimpanan evidence URL;
- policy default untuk existing questions;
- revision event/history structure;
- submission versioning;
- penanganan draft assessment ketika revision diminta;
- exact stored values untuk OB/KTS;
- validation recommendation;
- snapshot fields pada report;
- route hasil final auditee;
- access control setiap endpoint;
- stale version behavior.

### Acceptance Criteria

- Tidak ada production code berubah.
- Tidak ada schema, migration, test, route, atau config berubah.
- Semua keputusan penting memiliki bukti file:line.
- Bagian `Decisions Requiring Product Approval` kosong atau menyatakan tidak ada.
- Contract berstatus:

```text
APPROVED_BY_PLAN
```

### Commit

```text
docs(m17): define SPMI workspace parity contract
```

---

## M17-01 — Schema Foundation

### Objective

Menambahkan fondasi database sesuai design contract.

### Expected Change Areas

- migration baru;
- canonical schema snapshot bila repository memang mengharuskannya;
- schema regression test;
- model constants atau enum contract bila konvensi repository menggunakan itu.

### Required Schema Capabilities

Minimal mendukung:

- evidence policy per instrument question;
- evidence URL;
- submission revision event/history;
- submission versioning;
- finding type pada assessment item;
- finding type snapshot pada report item;
- backward-compatible defaults.

### Rules

- Jangan mengubah legacy schema.
- Jangan mengubah existing enum secara destruktif.
- Jangan membuat existing row invalid.
- Jangan menambahkan NOT NULL tanpa safe default atau backfill.
- Migration harus idempotent sesuai pola repository.
- Rollback strategy wajib didokumentasikan.

### Acceptance Criteria

- Migration forward lulus.
- Migration tidak kehilangan data.
- Existing SPMI records tetap dapat dibaca.
- Schema regression lulus.
- Legacy regression relevan lulus.
- Belum ada UI behavior baru.

### Commit

```text
feat(m17): add SPMI parity schema foundation
```

---

## M17-02 — Instrument Evidence Policy

### Objective

Admin LPMPI dapat menetapkan policy bukti per instrument question.

### Policy Values

```text
none
file
url
either
both
```

### Required Behavior

- default existing question = `none`;
- create/edit instrument question mendukung policy;
- detail page menampilkan policy;
- service validation menolak nilai tidak dikenal;
- audit log mengikuti pola mutasi existing;
- perubahan tidak menyentuh legacy `pertanyaan`.

### Acceptance Criteria

- policy tersimpan dan terbaca;
- unauthorized user ditolak;
- invalid policy ditolak;
- old question tetap berfungsi;
- test create/update/detail lulus.

### Commit

```text
feat(m17): add evidence policy to SPMI instruments
```

---

## M17-03 — Auditee Evidence Parity

### Objective

Workspace Auditee SPMI mendukung realisasi, evidence URL, evidence file, dan submit validation berdasarkan policy.

### Required UI per Item

```text
Question
Evidence instruction
Evidence policy
Realization
Evidence URL
Evidence files
Completeness status
```

### Required Rules

- draft boleh tidak lengkap;
- URL harus HTTP atau HTTPS;
- file menggunakan private storage;
- MIME dan size mengikuti batas existing;
- maksimal lima file per item;
- submit memvalidasi policy:
  - `none`: realization wajib, bukti tidak wajib;
  - `file`: minimal satu file;
  - `url`: URL valid wajib;
  - `either`: minimal file atau URL;
  - `both`: minimal satu file dan URL valid;
- submitted item readonly;
- delete/download evidence memeriksa ownership;
- cross-user access ditolak;
- stale version ditolak jika contract menggunakan version token.

### Acceptance Criteria

- seluruh policy tervalidasi di server;
- private evidence tidak dapat diakses tanpa authorization;
- draft dan submit behavior lulus runtime;
- existing submission tetap dapat dibuka;
- legacy tidak berubah.

### Commit

```text
feat(m17): add SPMI auditee evidence parity
```

---

## M17-04 — Submission Revision Lifecycle

### Objective

Auditor dapat mengembalikan submission untuk revisi sebelum assessment finalized.

### Required Behavior

- hanya assigned auditor yang dapat meminta revisi;
- revision reason wajib;
- actor dan timestamp tercatat;
- submission version tercatat;
- auditee dapat mengedit hanya saat `returned_for_revision`;
- resubmit tercatat sebagai versi baru atau event baru sesuai contract;
- finalized assessment memblokir revision;
- revision tidak membuat follow-up;
- draft assessment tidak boleh difinalisasi setelah source submission berubah;
- dashboard/workspace menampilkan status yang benar.

### Required Routes

Exact route mengikuti design contract dan konvensi repository.

Minimal capability:

```text
POST return-for-revision
POST resubmit
GET revision history or detail
```

### Acceptance Criteria

- seluruh transisi legal berhasil;
- seluruh transisi ilegal ditolak;
- cross-assignment request ditolak;
- stale assessment draft ditolak;
- history actor/reason/timestamp dapat dibaca;
- runtime lifecycle test lulus.

### Commit

```text
feat(m17): add SPMI submission revision lifecycle
```

---

## M17-05 — Auditor Assessment Parity

### Objective

Form Penilaian SPMI minimal setara secara operasional dengan legacy tanpa memasukkan action plan ke assessment.

### Required Item Fields

```text
Score:
4 — Sangat sesuai
3 — Sesuai
2 — Kurang sesuai
1 — Tidak sesuai

Finding
Finding Type:
- Tidak ada
- OB
- KTS

Recommendation
```

### Validation

- score wajib saat finalisasi;
- score harus 1–4;
- finding type hanya NULL, `ob`, atau `kts`;
- jika OB/KTS dipilih, finding wajib;
- jika KTS dipilih, recommendation wajib;
- draft boleh parsial;
- finalisasi hanya jika semua item valid;
- finalized immutable;
- rencana perbaikan tidak ditambahkan;
- auditor hanya dapat menilai assignment miliknya;
- evidence URL dan file dapat dibaca secara aman.

### UI Requirements

- label skor jelas;
- finding type menggunakan controlled input;
- validation error tampil per item;
- draft dan finalisasi dibedakan jelas;
- jangan gunakan raw unstyled form jika layout repository menyediakan component/pattern yang konsisten.

### Acceptance Criteria

- OB/KTS tersimpan;
- validation server-side lulus;
- finalization lock lulus;
- report generation lama tidak rusak sebelum M17-06;
- ownership test lulus.

### Commit

```text
feat(m17): add OB KTS assessment parity
```

---

## M17-06 — Report Snapshot and Auditee Result

### Objective

Report menyimpan snapshot final lengkap dan auditee dapat melihat hasil final milik assignment-nya.

### Required Report Snapshot

Minimal:

- cycle identity;
- assignment identity;
- standard/version snapshot;
- question/instrument snapshot;
- auditee realization;
- evidence URL metadata;
- evidence file metadata yang relevan;
- score;
- finding;
- finding type;
- recommendation;
- auditor identity;
- finalization timestamps.

### Rules

- report hanya dibuat dari finalized assessment;
- report tidak membaca draft sebagai canonical result;
- report immutable;
- perubahan master tidak mengubah report lama;
- auditee hanya dapat melihat report miliknya;
- direct URL assignment lain ditolak;
- hasil auditee read-only;
- RTM tetap membaca report final.

### Required Auditee Result View

Tampilkan:

- pertanyaan;
- realisasi;
- bukti;
- skor;
- temuan;
- OB/KTS;
- rekomendasi;
- status report;
- referensi tindak lanjut bila tersedia dan role mengizinkan.

### Acceptance Criteria

- snapshot membawa finding type;
- old report tetap dapat dibaca;
- auditee result authorization lulus;
- RTM regression lulus;
- export tetap membaca snapshot.

### Commit

```text
feat(m17): expose final SPMI results to auditee
```

---

## M17-07 — Runtime and End-to-End Hardening

### Objective

Membuktikan alur lengkap secara runtime, bukan hanya static source-contract.

### Required Runtime Lanes

1. Auditee menyimpan draft tidak lengkap.
2. Submit gagal karena evidence policy belum terpenuhi.
3. Submit valid dan menjadi readonly.
4. Auditor membaca URL dan file evidence.
5. Auditor meminta revisi dengan reason.
6. Auditee memperbaiki dan resubmit.
7. Stale assessment draft tidak dapat difinalisasi.
8. Auditor mengisi score, finding, OB/KTS, recommendation.
9. Finalisasi gagal jika item invalid.
10. Finalisasi berhasil dan immutable.
11. Report snapshot dibuat.
12. Auditee melihat hasil final.
13. RTM membaca report final.
14. Cross-user access ditolak.
15. Direct URL unauthorized ditolak.
16. Invalid MIME, oversize, dan evidence cap ditolak.
17. Existing SPMI record tetap dapat dibuka.
18. Legacy flow relevan tetap lulus.

### Security Checks

- CSRF;
- ownership;
- role gate;
- private download;
- path traversal;
- stale version;
- finalized lock;
- direct URL access.

### Acceptance Criteria

- seluruh required lane PASS;
- test result didokumentasikan;
- tidak ada known high-risk failure;
- tidak ada claim runtime tanpa eksekusi runtime;
- legacy regression PASS.

### Commit

```text
test(m17): harden SPMI workspace parity lifecycle
```

---

## M17-08 — Cutover Readiness Audit

### Type

```text
READ-ONLY AUDIT
```

### Allowed File

Hanya:

```text
docs/audit/spmi-parity-cutover-readiness.md
```

Dan status/log file plan ini.

### Objective

Menentukan apakah SPMI sudah layak menjadi satu-satunya alur untuk audit baru.

### Required Status per Area

```text
PASS
FAIL
BLOCKED
```

Area wajib:

- instrument evidence policy;
- auditee evidence parity;
- submission revision;
- auditor assessment parity;
- OB/KTS;
- finalization immutability;
- report snapshot;
- auditee result;
- authorization;
- private evidence;
- runtime E2E;
- backward compatibility;
- legacy historical access;
- RTM integration;
- regression coverage.

### Required Output Structure

```text
# SPMI Parity Cutover Readiness

## 1. Executive Summary
## 2. Repository State
## 3. Requirement Verification Matrix
## 4. Runtime Verification
## 5. Authorization and Security Verification
## 6. Backward Compatibility
## 7. Legacy Dependency Review
## 8. Cutover Risks
## 9. PASS / FAIL / BLOCKED Decision
## 10. Required Follow-up Before Cutover
## 11. Recommended Cutover Sequence
```

### Restrictions

M17-08 tidak boleh:

- hide sidebar;
- menjadikan legacy read-only;
- menghapus route;
- menghentikan task legacy;
- menjalankan cutover;
- membuat migration;
- mengubah production code.

### Final Decision

Hanya salah satu:

```text
READY_FOR_CUTOVER_MILESTONE
NOT_READY
BLOCKED
```

### Commit

```text
docs(m17): audit SPMI parity cutover readiness
```

---

## 12. Required Test Families

Agent harus menemukan nama test aktual dari repository. Minimal area:

- SPMI instruments;
- SPMI auditee workspace;
- SPMI auditor workspace;
- SPMI reports;
- SPMI RTM;
- SPMI follow-ups;
- dashboard/status;
- authorization/security;
- legacy auditee;
- legacy auditor;
- legacy reports;
- migration/schema.

Tidak boleh menghapus test existing.

---

## 13. Task Status Table

Agent hanya boleh memperbarui kolom Status, Commit, Tests, dan Notes.

| Task | Status | Commit | Tests | Notes |
|---|---|---|---|---|
| M17-00 Design Contract | BLOCKED | 1c2b5ba, 7473bc4 | STATIC SOURCE REVIEW only, no runtime; markdown lint unavailable in repo root | Two-commit history violates the single focused commit gate, contract lines 29-31 overstate stale draft/version enforcement, contract line 41 incorrectly cites Jawaban_audit_model for OB/KTS validation, and the initial commit added the full plan beyond the status/log allowlist |
| M17-01 Schema Foundation | NOT_STARTED | — | — | — |
| M17-02 Evidence Policy | NOT_STARTED | — | — | — |
| M17-03 Auditee Evidence Parity | NOT_STARTED | — | — | — |
| M17-04 Revision Lifecycle | NOT_STARTED | — | — | — |
| M17-05 Auditor Assessment Parity | NOT_STARTED | — | — | — |
| M17-06 Report & Auditee Result | NOT_STARTED | — | — | — |
| M17-07 Runtime Hardening | NOT_STARTED | — | — | — |
| M17-08 Cutover Readiness Audit | NOT_STARTED | — | — | — |

Allowed status:

```text
NOT_STARTED
IN_PROGRESS
PASS
FAIL
BLOCKED
```

---

## 14. Per-Task Final Output

Setelah setiap task, agent wajib menampilkan:

1. Task ID dan title.
2. Branch dan HEAD SHA sebelum task.
3. Branch dan HEAD SHA setelah task.
4. Ringkasan behavior yang diterapkan.
5. File berubah.
6. Migration/schema changes.
7. Route dan authorization affected.
8. Tests ditambahkan.
9. Commands executed.
10. Static tests dan runtime tests dibedakan.
11. Hasil setiap test.
12. Known limitations.
13. Konfirmasi non-goals tidak diubah.
14. `git diff --stat`.
15. `git status --short`.
16. Commit SHA.
17. Gate status untuk task berikutnya.

---

## 15. Execution Log

Agent boleh menambahkan entry baru di bawah ini tanpa mengubah entry lama.

Format:

```text
### YYYY-MM-DD HH:MM — M17-XX
- Status:
- Branch:
- Start SHA:
- End SHA:
- Commit:
- Files:
- Tests:
- Runtime verification:
- Notes:
- Next gate:
```

### 2026-08-04 00:00 — M17-00
- Status: PASS
- Branch: dev
- Start SHA: 9652f9f
- End SHA: 1c2b5ba
- Commit: 1c2b5ba
- Files: docs/product/spmi-workspace-parity-contract.md, docs/plan/m17-spmi-workspace-parity-master-plan.md
- Tests: STATIC SOURCE-CONTRACT TEST only, markdown lint command unavailable at repo root because there is no package.json or bun script in /home/steven/Documents/Audit-Mutu-Internal
- Runtime verification: not run, contract task only
- Notes: Contract approved by plan, no product approval decisions remain, no production or schema files changed
- Next gate: M17-01 blocked until commit SHA is finalized and M17-00 hard gate remains APPROVED_BY_PLAN

### 2026-08-04 00:00 — M17-00
- Status: BLOCKED
- Branch: dev
- Start SHA: 9652f9f
- End SHA: 7473bc4
- Commit: 1c2b5ba, 7473bc4
- Files: docs/plan/m17-spmi-workspace-parity-master-plan.md
- Tests: STATIC SOURCE REVIEW only, no runtime; markdown lint unavailable in repo root because there is no package.json or bun script in /home/steven/Documents/Audit-Mutu-Internal
- Runtime verification: not run
- Notes: Contract lines 29-31 overstate stale draft/version enforcement in auditor service, which currently only permits submitted; contract line 41 incorrectly cites Jawaban_audit_model for OB/KTS validation; M17-00 history is two commits (1c2b5ba and 7473bc4) despite the one focused commit rule; the initial commit added the entire plan beyond the status/log allowlist, and that historical scope and commit-rule violation cannot be made compliant without prohibited amend/rewrite
- Next gate: STOP: M17-01 not authorized

---

## 16. Final Milestone Acceptance

M17 dinyatakan selesai hanya jika:

- M17-00 sampai M17-08 berstatus PASS;
- tidak ada assessment finalized yang editable;
- OB/KTS bekerja pada assessment item;
- auditee evidence policy bekerja;
- revision lifecycle bekerja sebelum finalization;
- report snapshot immutable membawa field baru;
- auditee dapat melihat hasil final miliknya;
- runtime E2E PASS;
- legacy regression PASS;
- cutover audit menyatakan `READY_FOR_CUTOVER_MILESTONE`;
- legacy belum dihapus atau disembunyikan oleh M17.

---

## 17. Post-M17 Follow-Up

Milestone baru setelah M17 dapat membahas:

```text
M18 — SPMI Cutover and Legacy Read-Only Transition
```

Kemungkinan scope M18:

- menghentikan pembuatan tugas legacy baru;
- menjadikan legacy operational pages read-only;
- menyembunyikan legacy dari sidebar aktif;
- mempertahankan historical access;
- membatasi arsip legacy untuk Super Admin;
- cutover landing dashboard;
- observability dan rollback window.

M18 tidak boleh dimulai otomatis oleh agent dalam eksekusi file ini.

---

## 18. Final Stop Rule

Setelah M17-08 selesai:

```text
STOP.
DO NOT START M18.
DO NOT HIDE LEGACY.
DO NOT MAKE CUTOVER CHANGES.
```
