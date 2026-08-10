M17 — SPMI Workspace Parity & Cutover Preparation

Revision V2: task boundaries diperjelas dan blocker dibatasi hanya pada risiko nyata.

Dokumen kendali utama untuk AI coding agent

Letakkan file ini di:

docs/plan/m17-spmi-workspace-parity-master-plan.md

0. One-Time Agent Instruction

Gunakan instruksi berikut satu kali saja ketika memulai pekerjaan:

Read docs/plan/m17-spmi-workspace-parity-master-plan.md completely.

Execute this milestone according to the execution mode, task order, gates,
allowlists, acceptance criteria, test requirements, and stop conditions
defined in that file.

Do not invent requirements outside the document.
Do not skip task dependencies.
Do not modify legacy behavior unless a task explicitly allows it.
Resolve non-destructive wording, documentation, and task-boundary ambiguity
using the precedence and automatic-resolution rules in this document.
Stop only for a TRUE BLOCKER as explicitly defined in the Stop Conditions.

At the end of every task, update the Task Status Table and Execution Log,
commit the focused task, and continue automatically when the next task's
entry gate is satisfied. Do not stop merely to ask for permission to begin
the next task.

1. Execution Mode

Default execution mode:

AUTONOMOUS_SEQUENTIAL_WITH_SAFETY_GATES

Artinya:

Agent membaca seluruh dokumen sebelum mengubah apa pun.

Agent menjalankan task sesuai urutan M17-00 sampai M17-08.

Agent hanya boleh melanjutkan ketika gate task sebelumnya terpenuhi.

Agent membuat satu commit terfokus untuk setiap task implementasi.

Agent berhenti hanya pada TRUE BLOCKER: keputusan produk yang belum ada, migration destruktif/berisiko kehilangan data, authorization tidak aman, konflik perubahan user, atau required test failure yang tidak dapat diselesaikan dalam scope.

Agent tidak meminta prompt panjang baru untuk task berikutnya.

Agent memperbarui checklist status dan execution log di file ini setelah setiap task.

Agent tidak boleh mengubah requirement, scope, atau product decision di file ini.

Agent hanya boleh mengubah:

status task;

commit SHA;

ringkasan hasil;

test result;

blocker;

execution log.

Precedence and Automatic Conflict Resolution

Jika dua bagian dokumen tampak bertentangan, agent wajib memakai urutanprecedence berikut:

1. Locked Product Decisions
2. Approved Design Contract M17-00
3. Task Objective and Acceptance Criteria
4. Task Boundary Rules
5. General Execution Rules
6. Audit reports and explanatory notes

Aturan resolusi otomatis:

Perbedaan wording atau dokumentasi bukan blocker.

Schema additive yang dibutuhkan task berikutnya boleh dibuat lebih awal diM17-01 dalam keadaan dormant, selama tidak mengaktifkan behavior taskberikutnya.

Menambah kolom snapshot di M17-01 bukan berarti mengimplementasikan reportgeneration M17-06. M17-06 tetap memiliki service, population, authorization,view, export, dan runtime behavior.

Jika satu requirement disebut "bila diperlukan", sementara task berikutnyasecara eksplisit membutuhkan field tersebut, field dianggap diperlukan.

Jika source aktual memakai nama file, tabel, atau method berbeda, gunakannama aktual dan dokumentasikan pemetaan tanpa berhenti.

Agent boleh memperbaiki typo, referensi file, status table, dan executionlog tanpa menjadikannya blocker atau task implementasi terpisah.

Bootstrap commit, status-only commit, dan gate-repair documentation committidak dihitung sebagai pelanggaran aturan satu focused implementation commit.

Histori commit yang sudah ada tidak perlu di-rewrite atau di-squash.

Safety Gate Khusus M17-00

M17-00 menghasilkan design contract. Agent boleh melanjutkan otomatis keM17-01 jika:

tidak ada keputusan bisnis berstatus PRODUCT_APPROVAL_REQUIRED;

contract tidak meminta migration destruktif;

lifecycle final tidak bertentangan dengan Locked Product Decisions;

design contract berstatus APPROVED_BY_PLAN.

Kesalahan referensi, perbedaan wording, jumlah commit dokumentasi, ataupenempatan bootstrap plan bukan alasan untuk memblokir M17-00. Agent harusmemperbaikinya sebagai documentation repair dan melanjutkan.

Jika benar-benar ada keputusan produk yang tidak dapat diselesaikan dariLocked Product Decisions, gunakan:

STATUS: BLOCKED_PRODUCT_DECISION

Adoption Rule for an In-Progress Repository

Jika V2 ini dipasang setelah M17 sudah mulai:

pertahankan commit dan execution log yang sudah ada;

jangan mengulang task yang sudah benar-benar selesai;

validasi ulang gate berdasarkan aturan V2;

ubah blocker prosedural lama menjadi resolved note;

lanjutkan dari task implementasi pertama yang belum selesai;

jangan menganggap bootstrap/status/gate-repair commit sebagai pelanggaran.

2. Product Direction

SPMI menjadi alur utama untuk audit baru.

Kemampuan AMI legacy yang berguna diadopsi ke workspace SPMI tanpa menyalin seluruh desain legacy dan tanpa merusak lifecycle SPMI.

Target akhir:

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

3. Locked Product Decisions

Keputusan berikut sudah dianggap final untuk milestone M17.

3.1 SPMI adalah sumber kebenaran untuk audit baru

Audit baru menggunakan Siklus dan Penugasan SPMI.

Legacy tidak dihapus dalam M17.

Legacy tidak dimigrasikan penuh dalam M17.

Legacy belum disembunyikan dalam M17.

Legacy tidak boleh rusak akibat perubahan M17.

3.2 Assessment dan follow-up tidak boleh dicampur

Assessment auditor menyimpan:

skor;

temuan;

jenis temuan;

rekomendasi auditor.

Assessment auditor tidak menyimpan action plan aktif.

RTM menyimpan:

keputusan;

aksi yang disepakati.

Follow-up menyimpan:

rencana tindakan;

penanggung jawab;

tenggat;

catatan pelaksanaan;

status;

penyelesaian;

verifikasi.

3.3 Dua jenis revisi harus dibedakan

Sebelum assessment final:

Auditor menemukan submission belum layak dinilai
→ return for revision
→ auditee memperbaiki
→ resubmit

Setelah assessment final:

Temuan sudah menjadi hasil audit
→ report
→ RTM
→ follow-up

Follow-up tidak boleh digunakan sebagai pengganti revisi submission.

3.4 Finalized assessment immutable

Assessment finalized tidak dapat dibuka kembali.

Perubahan setelah finalisasi dilakukan melalui RTM dan follow-up.

Koreksi administratif terhadap snapshot harus menjadi task terpisah di luar M17.

3.5 OB/KTS berada pada assessment item

Target nilai tersimpan:

NULL
ob
kts

Makna:

NULL = tidak ada klasifikasi temuan;

ob = observasi;

kts = ketidaksesuaian.

Aturan:

tidak semua skor wajib memiliki OB/KTS;

OB/KTS hanya dipilih jika ada temuan;

jika OB/KTS dipilih, temuan wajib;

rekomendasi wajib untuk KTS;

report menyimpan snapshot hasil final, bukan menjadi pemilik data aktif.

3.6 Bukti auditee mendukung file dan URL

Per item submission dapat memiliki:

realisasi;

evidence URL opsional;

maksimal lima file evidence sesuai batas sistem saat ini.

Policy bukti per pertanyaan:

none
file
url
either
both

Default untuk data instrumen lama:

none

3.7 Hasil final dapat dilihat auditee

Auditee hanya dapat melihat hasil final milik assignment-nya.

Sumber tampilan hasil:

report snapshot final

Bukan draft assessment aktif.

4. Scope

M17 mencakup:

Design contract.

Schema foundation.

Evidence policy pada instrumen.

Evidence URL dan file pada workspace auditee.

Submission revision lifecycle.

Auditor assessment parity.

Report snapshot dan hasil auditee.

Runtime serta end-to-end hardening.

Cutover readiness audit.

5. Non-Goals

Agent dilarang melakukan hal berikut dalam M17:

menghapus fitur AMI legacy;

menyembunyikan menu legacy;

menghapus route legacy;

menjadikan legacy read-only;

mengubah Penetapan legacy;

mengubah Periode Audit legacy;

memigrasikan seluruh data legacy;

membuat cutover produksi;

mengubah dashboard di luar kebutuhan status workspace;

membuka kembali assessment finalized;

menambahkan rencana perbaikan ke assessment auditor;

menduplikasi recommendation aktif ke assessment, report, RTM, dan follow-up;

refactor unrelated;

mengganti framework;

mengubah pola arsitektur repository secara besar-besaran;

memperbaiki fitur di luar scope hanya karena ditemukan saat audit;

menghapus atau melemahkan test agar pipeline lulus;

push ke remote tanpa instruksi eksplisit.

Attachment bukti tindak lanjut RTM bukan bagian wajib M17. Jika diperlukan, catat sebagai follow-up milestone.

6. Source of Truth

Urutan sumber kebenaran:

Current repository HEAD.

Current schema dan migrations.

Routes, controllers, services, models, views.

Existing regression tests.

Dokumen ini.

Audit pendukung:

docs/audit/admin-legacy-vs-spmi-audit.md

docs/audit/auditee-auditor-legacy-vs-spmi-parity.md

Audit adalah bukti awal, bukan pengganti pembacaan source aktual.

7. Global Execution Rules

7.1 Preflight

Sebelum setiap task:

Tampilkan branch aktif.

Tampilkan HEAD SHA.

Jalankan git status --short.

Jangan menyentuh perubahan milik user yang sudah ada.

Baca task aktif secara lengkap.

Baca design contract setelah M17-00 selesai.

Petakan:

route;

controller;

service;

model;

table;

view;

test.

Tulis daftar file yang diperkirakan berubah.

Catat risiko dan asumsi.

Berhenti jika scope bertentangan dengan design contract.

7.2 Implementation Discipline

Kerjakan tepat satu task pada satu waktu.

Satu task implementasi = satu commit terfokus.

Tidak boleh mencampur refactor unrelated.

Ikuti konvensi CodeIgniter 3 yang sudah ada.

Business validation harus berada di service/model, bukan hanya JavaScript atau view.

Authorization dan ownership harus divalidasi di server.

Private file harus tetap private.

Mutasi POST harus mempertahankan CSRF protection.

Gunakan optimistic/version locking bila contract membutuhkannya.

Data existing harus tetap valid.

Migration harus backward-compatible.

Tidak boleh melanjutkan jika migration tidak dapat diuji dengan aman.

7.3 Testing Discipline

Urutan test:

Test yang baru dibuat.

Targeted regression untuk area task.

SPMI workspace regression.

Report, RTM, dan follow-up regression yang terdampak.

Legacy regression yang relevan.

Security dan authorization regression.

Runtime HTTP test bila environment mendukung.

Agent wajib membedakan:

STATIC SOURCE-CONTRACT TEST

dan:

RUNTIME BEHAVIOR TEST

Jangan mengklaim runtime berhasil jika hanya static test yang dijalankan.

7.4 Commit Discipline

Commit hanya setelah acceptance criteria dan test wajib lulus.

Commit message:

docs(m17): define SPMI workspace parity contract

feat(m17): add SPMI parity schema foundation

dan seterusnya sesuai task.

Jangan amend commit lama yang tidak terkait.

Jangan squash unrelated commit.

Jangan push.

7.5 Stop Conditions — True Blockers Only

Agent hanya boleh berhenti jika salah satu kondisi berikut nyata terjadi:

keputusan bisnis yang mengubah perilaku produk tidak dijawab oleh LockedProduct Decisions atau approved contract;

migration bersifat destruktif, berpotensi kehilangan data, atau tidak punyasafe default/backfill;

authorization atau ownership tidak dapat dibuat aman tanpa keputusan produk;

required test tetap gagal setelah penyebab dalam scope diperiksa;

runtime membuktikan cross-user access, private-file exposure, atau finalizeddata menjadi editable;

working tree user benar-benar konflik pada file yang harus diubah;

implementasi memerlukan perubahan legacy yang secara eksplisit dilarang;

source aktual membuat acceptance criteria mustahil dipenuhi tanpa perubahanscope bisnis.

Hal berikut bukan blocker dan harus diselesaikan otomatis:

typo atau referensi file yang salah;

ketidakkonsistenan wording antara plan dan contract;

field schema task berikutnya perlu dibuat dormant pada M17-01;

plan bootstrap memiliki commit terpisah;

status/log membutuhkan commit dokumentasi;

nama file atau method aktual berbeda dari perkiraan;

markdown lint tidak tersedia;

static test tersedia tetapi runtime environment belum tersedia. Untuk kasusterakhir, tandai runtime NOT_RUN_ENVIRONMENT dan lanjutkan sampai task yangmemang mewajibkan runtime, yaitu M17-07.

Status blocker yang sah:

BLOCKED_PRODUCT_DECISION
BLOCKED_DESTRUCTIVE_MIGRATION
BLOCKED_AUTHORIZATION
BLOCKED_TEST_FAILURE
BLOCKED_USER_CHANGES
BLOCKED_SCOPE_CONFLICT

Setiap blocker harus menyebutkan:

requirement yang tidak dapat dipenuhi;

bukti source aktual;

risiko konkret jika dilanjutkan;

keputusan minimum yang diperlukan.

8. Target Lifecycle Contract

8.1 Submission State Machine

Target state:

draft
→ submitted
→ returned_for_revision
→ resubmitted
→ under_assessment
→ completed

Aturan:

draft: editable oleh auditee pemilik assignment.

submitted: readonly untuk auditee.

returned_for_revision: editable oleh auditee, wajib memiliki revision reason.

resubmitted: readonly untuk auditee, menunggu auditor.

under_assessment: assessment auditor sedang aktif.

completed: assessment finalized dan report final tersedia.

Transisi yang dilarang:

completed → draft

completed → returned_for_revision

submitted → draft tanpa revision request

perubahan item oleh user bukan pemilik

revisi setelah assessment finalized

8.2 Assessment State Machine

Target state:

draft
→ finalized

Assessment draft hanya boleh ada untuk submission:

submitted
resubmitted
under_assessment

Aturan:

draft dapat disimpan sebagian;

finalisasi hanya jika seluruh item valid;

finalized immutable;

return for revision hanya boleh dilakukan sebelum finalized;

saat return for revision, draft assessment ditangani sesuai design contract M17-00:

dibatalkan secara aman; atau

dipertahankan sebagai stale draft yang tidak boleh difinalisasi;

pilihan final harus ditetapkan di M17-00.

9. Field Ownership Contract

Field

Owner

Lifecycle

Canonical Storage

Realization

Auditee

Submission

Submission item

Evidence URL

Auditee

Submission

Submission item atau child metadata sesuai contract

Evidence file

Auditee

Submission

Evidence child table

Score

Auditor

Assessment

Assessment item

Finding

Auditor

Assessment

Assessment item

Finding type OB/KTS

Auditor

Assessment

Assessment item

Recommendation

Auditor

Assessment

Assessment item

Revision reason

Auditor

Pre-final revision

Revision event/history

Submission version

System

Submission lifecycle

Submission/version history

Report data

System

Post-finalization

Immutable report snapshot

RTM decision

Admin LPMPI/RTM

RTM

RTM decision

Agreed action

RTM

RTM

RTM action/decision

Improvement plan

Follow-up owner

Follow-up

Follow-up

Responsible person

RTM/follow-up owner

Follow-up

Follow-up

Due date

RTM/follow-up owner

Follow-up

Follow-up

Follow-up note

Follow-up owner

Follow-up

Follow-up

Completion verification

Authorized verifier

Follow-up

Follow-up

10. Task Dependency Graph

M17-00 Design Contract
↓
M17-01 Schema Foundation
↓
M17-02 Instrument Evidence Policy
↓
M17-03 Auditee Evidence Parity
↓
M17-01A Revision Lifecycle Schema Correction
↓
M17-04 Submission Revision Lifecycle
↓
M17-05 Auditor Assessment Parity
↓
M17-06 Report Snapshot & Auditee Result
↓
M17-05A Auditor Evidence Read Parity
↓
M17-07 Runtime and E2E Hardening
↓
M17-08 Cutover Readiness Audit

Tidak boleh melompati dependency.

11. Task Specifications

M17-00 — Design Contract SPMI Workspace Parity

Type

READ-ONLY PLANNING

Allowed File

Hanya:

docs/product/spmi-workspace-parity-contract.md

Dan bagian status/log dalam file plan ini.

Objective

Mengunci desain teknis dan lifecycle sebelum migration atau code production.

Required Analysis

Map current SPMI auditee flow.

Map current SPMI auditor flow.

Map report generation.

Map RTM dan follow-up integration.

Tetapkan submission state machine.

Tetapkan assessment state machine.

Tetapkan return-for-revision behavior.

Tetapkan evidence policy.

Tetapkan OB/KTS behavior.

Tetapkan field ownership.

Usulkan exact schema changes.

Usulkan route/service/model/controller/view changes.

Tetapkan backward compatibility.

Tetapkan authorization dan security rules.

Tetapkan migration order.

Tetapkan test matrix.

Tetapkan task-by-task file allowlist.

Required Document Structure

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

Required Decisions

Contract harus menetapkan tanpa ambigu:

penyimpanan evidence URL;

policy default untuk existing questions;

revision event/history structure;

submission versioning;

penanganan draft assessment ketika revision diminta;

exact stored values untuk OB/KTS;

validation recommendation;

snapshot fields pada report;

route hasil final auditee;

access control setiap endpoint;

stale version behavior.

Acceptance Criteria

Tidak ada production code berubah.

Tidak ada schema, migration, test, route, atau config berubah.

Semua keputusan penting memiliki bukti file.

Bagian Decisions Requiring Product Approval kosong atau menyatakan tidak ada.

Contract berstatus:

APPROVED_BY_PLAN

Commit

docs(m17): define SPMI workspace parity contract

M17-01 — Schema Foundation

Objective

Menambahkan seluruh fondasi database additive yang dibutuhkan M17-02 sampaiM17-06 sesuai approved design contract. Field boleh belum dipakai oleh serviceatau UI sampai task pemilik behavior dikerjakan.

Task Boundary — Explicit

M17-01 memiliki schema, bukan behavior.

M17-01 boleh dan wajib menambahkan dormant schema untuk task berikutnya,termasuk kolom report snapshot. Hal ini tidak dianggap mengerjakan M17-06lebih awal selama M17-01 tidak mengubah report generator, controller, route,view, export, atau runtime population behavior.

M17-06 memiliki:

pengisian/population snapshot;

report generation rules;

immutable read path;

auditee result route dan view;

export dan RTM integration;

authorization serta runtime tests.

Expected Change Areas

satu atau beberapa migration additive sesuai konvensi repository;

canonical schema snapshot bila repository mengharuskannya;

schema regression test;

model constants atau enum contract jika konvensi repository memakai itu.

Required Schema Capabilities

Minimal mendukung:

evidence_policy per instrument question;

evidence URL pada submission item atau child metadata sesuai contract;

submission version field;

revision event/history table atau struktur yang disetujui contract;

finding_type pada assessment item;

finding_type_snapshot pada report item;

realization/evidence snapshot metadata tambahan yang secara eksplisitdiperlukan oleh M17-06 dan belum tersedia di schema saat ini;

safe defaults dan backward compatibility untuk semua existing row.

Interpretasi wajib:

finding_type_snapshot wajib dibuat di M17-01.
Kolom tersebut tetap dormant sampai M17-06 mengisi dan menampilkannya.

Rules

Jangan mengubah legacy schema.

Jangan mengubah existing enum secara destruktif.

Jangan membuat existing row invalid.

Jangan menambahkan NOT NULL tanpa safe default atau backfill.

Migration harus mengikuti pola repository dan aman dijalankan pada databaseexisting.

Rollback strategy wajib didokumentasikan.

Jangan mengubah UI, route, report generator, atau lifecycle behavior.

Perbedaan wording contract tentang "bila diperlukan" diselesaikan denganrequirement eksplisit task: semua field yang dibutuhkan M17-02 sampai M17-06dianggap diperlukan pada schema foundation.

Acceptance Criteria

Migration forward lulus.

Migration tidak kehilangan data.

Existing SPMI records tetap dapat dibaca.

Semua schema capability di atas tersedia.

Schema regression lulus.

Legacy regression relevan lulus.

Tidak ada UI atau runtime behavior baru.

Agent melanjutkan otomatis ke M17-02 setelah commit berhasil.

Commit

feat(m17): add SPMI parity schema foundation

M17-02 — Instrument Evidence Policy

Objective

Admin LPMPI dapat menetapkan policy bukti per instrument question.

Policy Values

none
file
url
either
both

Required Behavior

default existing question = none;

create/edit instrument question mendukung policy;

detail page menampilkan policy;

service validation menolak nilai tidak dikenal;

audit log mengikuti pola mutasi existing;

perubahan tidak menyentuh legacy pertanyaan.

Acceptance Criteria

policy tersimpan dan terbaca;

unauthorized user ditolak;

invalid policy ditolak;

old question tetap berfungsi;

test create/update/detail lulus.

Commit

feat(m17): add evidence policy to SPMI instruments

M17-03 — Auditee Evidence Parity

Objective

Workspace Auditee SPMI mendukung realisasi, evidence URL, evidence file, dan submit validation berdasarkan policy.

Required UI per Item

Question
Evidence instruction
Evidence policy
Realization
Evidence URL
Evidence files
Completeness status

Required Rules

draft boleh tidak lengkap;

URL harus HTTP atau HTTPS;

file menggunakan private storage;

MIME dan size mengikuti batas existing;

maksimal lima file per item;

submit memvalidasi policy:

none: realization wajib, bukti tidak wajib;

file: minimal satu file;

url: URL valid wajib;

either: minimal file atau URL;

both: minimal satu file dan URL valid;

submitted item readonly;

delete/download evidence memeriksa ownership;

cross-user access ditolak;

stale version ditolak jika contract menggunakan version token.

Acceptance Criteria

seluruh policy tervalidasi di server;

private evidence tidak dapat diakses tanpa authorization;

draft dan submit behavior lulus runtime;

existing submission tetap dapat dibuka;

legacy tidak berubah.

Commit

feat(m17): add SPMI auditee evidence parity

M17-01A — Revision Lifecycle Schema Correction

Objective

Menambahkan koreksi schema additive yang dibutuhkan untuk lifecycle revisi submission, tanpa menyentuh endpoints, controllers, services, models, views, UI, auth behavior, atau behavior transisi.

Ownership and Boundaries

M17-01A memiliki schema additive dan satu migration forward-safe.

M17-01A hanya boleh menambahkan schema compatibility untuk mendukung lifecycle revisi.

M17-01A tidak boleh mengubah endpoint, controller, service, model, view, UI, runtime transition, auth behavior, report behavior, atau legacy behavior.

M17-01A tidak boleh menghapus data, membuat perubahan destruktif, atau mengandalkan backfill wajib yang diasumsikan.

M17-04 tetap memiliki ownership atas return/resubmit behavior, authorization, version increments, stale rejection, dan runtime lifecycle tests.

Required Schema Capabilities

Minimal harus tersedia:

submission states: draft, submitted, returned_for_revision, resubmitted, under_assessment, completed;

positive non-null submission version dengan default 1, tanpa behavioral increment di task ini;

revision history fields: references, old status, new status, old version, new version, reason, actor, timestamp;

nullable assessment source-submission-version provenance;

no historical guessing;

safe compatibility untuk row existing;

no destructive change;

no deletion;

no mandatory assumed backfill.

Implementation Shape

Satu migration additive.

Schema regression tests terfokus.

Jika repository memakai schema snapshot atau constraint mirror, update hanya yang diperlukan untuk compatibility.

Acceptance Criteria

Schema additive tersedia dan backward-compatible.

Required lifecycle fields and provenance tersedia sesuai contract.

Tidak ada endpoint, controller, service, model, view, UI, atau auth behavior yang berubah.

Tidak ada runtime DB action yang diasumsikan dari task ini.

Commit

feat(m17): add revision lifecycle schema correction

M17-04 — Submission Revision Lifecycle

Objective

Auditor dapat mengembalikan submission untuk revisi sebelum assessment finalized.

Required Behavior

hanya assigned auditor yang dapat meminta revisi;

revision reason wajib;

actor dan timestamp tercatat;

submission version tercatat;

auditee dapat mengedit hanya saat returned_for_revision;

resubmit tercatat sebagai versi baru atau event baru sesuai contract;

finalized assessment memblokir revision;

revision tidak membuat follow-up;

draft assessment tidak boleh difinalisasi setelah source submission berubah;

dashboard/workspace menampilkan status yang benar.

Required Routes

Exact route mengikuti design contract dan konvensi repository.

Minimal capability:

POST return-for-revision
POST resubmit
GET revision history or detail

Acceptance Criteria

seluruh transisi legal berhasil;

seluruh transisi ilegal ditolak;

cross-assignment request ditolak;

stale assessment draft ditolak;

history actor/reason/timestamp dapat dibaca;

runtime lifecycle test lulus.

Commit

feat(m17): add SPMI submission revision lifecycle

M17-05 — Auditor Assessment Parity

Objective

Form Penilaian SPMI minimal setara secara operasional dengan legacy tanpa memasukkan action plan ke assessment.

Required Item Fields

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

Validation

score wajib saat finalisasi;

score harus 1–4;

finding type hanya NULL, ob, atau kts;

jika OB/KTS dipilih, finding wajib;

jika KTS dipilih, recommendation wajib;

draft boleh parsial;

finalisasi hanya jika semua item valid;

finalized immutable;

rencana perbaikan tidak ditambahkan;

auditor hanya dapat menilai assignment miliknya;

evidence URL dan file dapat dibaca secara aman.

UI Requirements

label skor jelas;

finding type menggunakan controlled input;

validation error tampil per item;

draft dan finalisasi dibedakan jelas;

jangan gunakan raw unstyled form jika layout repository menyediakan component/pattern yang konsisten.

Acceptance Criteria

OB/KTS tersimpan;

validation server-side lulus;

finalization lock lulus;

report generation lama tidak rusak sebelum M17-06;

ownership test lulus.

Commit

feat(m17): add OB KTS assessment parity

M17-06 — Report Snapshot and Auditee Result

Objective

Report menyimpan snapshot final lengkap dan auditee dapat melihat hasil final milik assignment-nya.

Task Boundary — Explicit

Schema snapshot yang diperlukan seharusnya sudah ditambahkan secara dormantpada M17-01. M17-06 tidak mengulang schema foundation. M17-06 mengaktifkanbehavior dengan:

mengisi snapshot dari finalized assessment dan submission;

menjaga immutability;

menyediakan read path hasil auditee;

memperbarui export dan RTM integration;

menambahkan authorization dan runtime coverage.

Jika ditemukan satu kolom additive kecil yang terlewat tetapi langsungrequired oleh approved contract, agent boleh menambah migration korektif dalamM17-06 tanpa status BLOCKED, selama aman, non-destruktif, dan didokumentasikan.

Required Report Snapshot

Minimal:

cycle identity;

assignment identity;

standard/version snapshot;

question/instrument snapshot;

auditee realization;

evidence URL metadata;

evidence file metadata yang relevan;

score;

finding;

finding type;

recommendation;

auditor identity;

finalization timestamps.

Rules

report hanya dibuat dari finalized assessment;

report tidak membaca draft sebagai canonical result;

report immutable;

perubahan master tidak mengubah report lama;

auditee hanya dapat melihat report miliknya;

direct URL assignment lain ditolak;

hasil auditee read-only;

RTM tetap membaca report final.

Required Auditee Result View

Tampilkan:

pertanyaan;

realisasi;

bukti;

skor;

temuan;

OB/KTS;

rekomendasi;

status report;

referensi tindak lanjut bila tersedia dan role mengizinkan.

Acceptance Criteria

snapshot membawa finding type;

old report tetap dapat dibaca;

auditee result authorization lulus;

RTM regression lulus;

export tetap membaca snapshot.

Commit

feat(m17): expose final SPMI results to auditee

M17-05A — Auditor Evidence Read Parity

Type

CORRECTIVE DEPENDENCY

Objective

Memastikan ownership-scoped auditor evidence URL read/render parity untuk assignment yang sah, terbatas pada read/render URL bukti milik ownership, beserta focused test dan runtime proof.

Ownership and Boundaries

M17-05A hanya memiliki ownership-scoped auditor evidence URL read/render.

M17-05A hanya boleh mengerjakan focused test dan runtime proof untuk bukti read/render URL milik assignment yang berwenang.

M17-05A tidak boleh mengubah report snapshot, submission revision, assessment behavior, legacy behavior, route lain, atau scope M17-07.

M17-07 tetap BLOCKED sampai M17-05A membuktikan PASS.

Acceptance Criteria

Evidence URL milik assignment yang sah dapat dibaca dan dirender oleh role yang berwenang.

Cross-user access tetap ditolak.

Focused test dan runtime proof tercatat.

M17-07 tetap BLOCKED sampai bukti PASS M17-05A tersedia.

Commit

fix(m17): expose auditee evidence URL to auditor

M17-07 — Runtime and End-to-End Hardening

Objective

Membuktikan alur lengkap secara runtime, bukan hanya static source-contract.

Dependency

M17-07 bergantung pada PASS M17-05A Auditor Evidence Read Parity.

M17-07 tetap BLOCKED sampai M17-05A membuktikan PASS.

Required Runtime Lanes

Auditee menyimpan draft tidak lengkap.

Submit gagal karena evidence policy belum terpenuhi.

Submit valid dan menjadi readonly.

Auditor membaca URL dan file evidence.

Auditor meminta revisi dengan reason.

Auditee memperbaiki dan resubmit.

Stale assessment draft tidak dapat difinalisasi.

Auditor mengisi score, finding, OB/KTS, recommendation.

Finalisasi gagal jika item invalid.

Finalisasi berhasil dan immutable.

Report snapshot dibuat.

Auditee melihat hasil final.

RTM membaca report final.

Cross-user access ditolak.

Direct URL unauthorized ditolak.

Invalid MIME, oversize, dan evidence cap ditolak.

Existing SPMI record tetap dapat dibuka.

Legacy flow relevan tetap lulus.

Security Checks

CSRF;

ownership;

role gate;

private download;

path traversal;

stale version;

finalized lock;

direct URL access.

Acceptance Criteria

seluruh required lane PASS;

test result didokumentasikan;

tidak ada known high-risk failure;

tidak ada claim runtime tanpa eksekusi runtime;

legacy regression PASS.

Commit

test(m17): harden SPMI workspace parity lifecycle

M17-08 — Cutover Readiness Audit

Type

READ-ONLY AUDIT

Allowed File

Hanya:

docs/audit/spmi-parity-cutover-readiness.md

Dan status/log file plan ini.

Objective

Menentukan apakah SPMI sudah layak menjadi satu-satunya alur untuk audit baru.

Required Status per Area

PASS
FAIL
BLOCKED

Area wajib:

instrument evidence policy;

auditee evidence parity;

submission revision;

auditor assessment parity;

OB/KTS;

finalization immutability;

report snapshot;

auditee result;

authorization;

private evidence;

runtime E2E;

backward compatibility;

legacy historical access;

RTM integration;

regression coverage.

Required Output Structure

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

Restrictions

M17-08 tidak boleh:

hide sidebar;

menjadikan legacy read-only;

menghapus route;

menghentikan task legacy;

menjalankan cutover;

membuat migration;

mengubah production code.

Final Decision

Hanya salah satu:

READY_FOR_CUTOVER_MILESTONE
NOT_READY
BLOCKED

Commit

docs(m17): audit SPMI parity cutover readiness

12. Required Test Families

Agent harus menemukan nama test aktual dari repository. Minimal area:

SPMI instruments;

SPMI auditee workspace;

SPMI auditor workspace;

SPMI reports;

SPMI RTM;

SPMI follow-ups;

dashboard/status;

authorization/security;

legacy auditee;

legacy auditor;

legacy reports;

migration/schema.

Tidak boleh menghapus test existing.

13. Task Status Table

Agent hanya boleh memperbarui kolom Status, Commit, Tests, dan Notes.

Task

Status

Commit

Tests

Notes

M17-00 Design Contract

PASS

1c2b5ba, 7473bc4, 318029a, 1c56a01, 8c2a1c8, a25f188

STATIC SOURCE REVIEW only, no runtime; markdown autofix and lint commands unavailable in repo root

Bootstrap commit `1c2b5ba` is excluded from M17-00 output, `7473bc4` and `318029a` are part of the documented M17-00 documentation history, `1c56a01` is the repair commit that finalizes the gate record, `8c2a1c8` is the final gate-record commit now recorded here, `a25f188` is the latest committed plan baseline recovered from Git history, `c97114f` is the current documentation adoption commit recorded in the execution log, status/log-only updates are exempt from the one focused commit rule, one focused implementation commit applies to M17-01 through M17-07, M17-00 allows one contract commit plus one metadata/gate update commit, and existing history is preserved without rewrite or squash

M17-01 Schema Foundation

PASS

759baad

php -l tests/m17_schema_regression.php; php tests/m17_schema_regression.php; php tests/spmi_instruments_regression.php; php tests/spmi_auditee_workspace_regression.php; php tests/spmi_auditor_workspace_regression.php; php tests/spmi_reports_regression.php; runtime NOT_RUN_ENVIRONMENT (deferred to M17-07)

Schema-only Wave 3 verification PASS; no migration, DB, routes, auth, UI, legacy behavior, or M17-02+ changes; M17-02 eligible but NOT started here

M17-02 Evidence Policy

PASS

099a6db

php -l application/controllers/lpmpi/Spmi_instruments.php; php -l application/services/Spmi_instruments_service.php; php -l application/views/lpmpi/spmi_instruments/question_form.php; php -l application/views/lpmpi/spmi_instruments/question_detail.php; php -l tests/spmi_instruments_regression.php; php tests/spmi_instruments_regression.php; php tests/spmi_auditee_workspace_regression.php; php tests/spmi_auditor_workspace_regression.php; php tests/spmi_reports_regression.php; GIT_MASTER=1 git diff --check; runtime NOT_RUN_ENVIRONMENT (deferred to M17-07)

Static evidence-policy CRUD/display verification PASS for Admin LPMPI question policy only; no schema/migration, routes, models, legacy, auditee evidence enforcement, assignment policy snapshot, revision lifecycle, report/RTM/export, auth/config/sidebar, or M17-03+ changes; M17-03 eligible but NOT_STARTED and not started by this task

M17-03 Auditee Evidence Parity

PASS

see Git history (self SHA unavailable in preimage)

php -l application/controllers/Spmi_auditee_workspace.php; php -l application/models/Spmi_audits_model.php; php -l application/models/Spmi_auditee_workspace_model.php; php -l application/services/Spmi_audits_service.php; php -l application/services/Spmi_auditee_workspace_service.php; php -l application/views/spmi_auditee_workspace/assignment.php; php -l tests/spmi_auditee_workspace_regression.php; php tests/m17_schema_regression.php; php tests/spmi_instruments_regression.php; php tests/spmi_auditee_workspace_regression.php; php tests/spmi_auditor_workspace_regression.php; php tests/spmi_reports_regression.php; GIT_MASTER=1 git diff --check; runtime NOT_RUN_ENVIRONMENT (deferred to M17-07)

Static assignment-item evidence_policy snapshot and server-side auditee submit enforcement verification PASS; tested diff excludes M17-04 revision lifecycle, M17-05 auditor assessment, M17-06 reporting/result, legacy, config, route/auth, and .multibrain changes; runtime, DB, migration, and browser verification NOT_RUN_ENVIRONMENT; M17-04 eligible after PASS but NOT_STARTED and not started by this task; exact implementation SHA is represented by Git history because a commit cannot record its own final SHA in the preimage

M17-01A Revision Lifecycle Schema Correction

PASS

see Git history (self SHA unavailable in preimage)

php -l tests/m17_schema_regression.php; php tests/m17_schema_regression.php; php tests/spmi_auditee_workspace_regression.php; php tests/spmi_auditor_workspace_regression.php; php tests/spmi_reports_regression.php; php tests/spmi_audits_regression.php; php tests/legacy_ami_archive_regression.php; php tests/m16_security_regression.php; php tests/hardening_regression.php; GIT_MASTER=1 git diff --check; runtime/DB/migration/browser NOT_RUN_ENVIRONMENT

Static schema correction acceptance PASS; additive lifecycle schema compatibility is present in migration/schema/test artifacts only; runtime, DB, migration execution, and browser verification NOT_RUN_ENVIRONMENT; exact implementation commit SHA is represented by Git history because a commit cannot prewrite its own final SHA

M17-04 Revision Lifecycle

PASS

implementation SHA recorded in Git history

Static tests passed; runtime/DB/migration/browser NOT_RUN_ENVIRONMENT.

Schema blocker remains preserved as historical context and is RESOLVED by M17-01A revision lifecycle schema correction; M17-04 submission revision lifecycle source implementation is complete for this gate, and M17-05 is eligible after this PASS but must not be started by this task.

M17-05 Auditor Assessment Parity

PASS

see Git history (self SHA unavailable in preimage)

php -l application/services/Spmi_auditor_workspace_service.php; php -l application/views/spmi_auditor_workspace/assignment.php; php -l tests/spmi_auditor_workspace_regression.php; php tests/m17_schema_regression.php; php tests/spmi_instruments_regression.php; php tests/spmi_auditee_workspace_regression.php; php tests/spmi_auditor_workspace_regression.php; php tests/spmi_reports_regression.php; php tests/spmi_audits_regression.php; php tests/legacy_ami_archive_regression.php; php tests/m16_security_regression.php; php tests/hardening_regression.php; GIT_MASTER=1 git diff --check; runtime/DB/migration/browser NOT_RUN_ENVIRONMENT

Static auditor assessment parity acceptance PASS for finding_type parsing, persistence, display, and finalize validation only; M17-04 stale/version protections remain preserved; runtime, DB, migration execution, and browser verification NOT_RUN_ENVIRONMENT; exact implementation commit SHA is represented by Git history because a commit cannot prewrite its own final SHA; M17-06 is eligible after this PASS but NOT_STARTED and not started by this task

M17-05A Auditor Evidence Read Parity

PASS

see Git history (self SHA unavailable in preimage)

php -l application/models/Spmi_auditor_workspace_model.php; php -l application/views/spmi_auditor_workspace/assignment.php; php -l tests/spmi_auditor_workspace_regression.php; php -l tests/m17_07a_runtime_fixture_regression.php; php tests/spmi_auditor_workspace_regression.php; php tests/spmi_auditee_workspace_regression.php; php tests/m17_07a_runtime_fixture_regression.php; php tests/m17_schema_regression.php; php tests/m16_security_regression.php; php tests/hardening_regression.php; GIT_MASTER=1 git diff --check; historical runtime proof cited from `bash tests/run_m17_07a_runtime.sh run` exit 0

Ownership-scoped auditor evidence URL read/render parity PASS only. Historical evidence URL blocker is RESOLVED by the existing runtime proof from `bash tests/run_m17_07a_runtime.sh run` (exit 0) with log `/tmp/m17_05a_final_aavbj7f6.log`, project `m17_07a_1000_1786253247_26187`, rendered submitted evidence URL visibility, authorized own private PDF evidence download, cross-auditor A-to-B denial, and exact-resource teardown. M17-07 is now actionable NOT_STARTED for the remaining full runtime and hardening lanes not yet run. M17-08 remains NOT_STARTED.

M17-06 Report & Auditee Result

PASS

see Git history (self SHA unavailable in preimage)

php -l application/config/routes.php; php -l application/controllers/Spmi_auditee_workspace.php; php -l application/controllers/lpmpi/Spmi_reports.php; php -l application/models/Spmi_auditee_workspace_model.php; php -l application/models/Spmi_reports_model.php; php -l application/services/Spmi_auditee_workspace_service.php; php -l application/services/Spmi_reports_service.php; php -l application/views/spmi_auditee_workspace/final_result.php; php -l application/views/lpmpi/spmi_reports/detail.php; php -l application/views/lpmpi/spmi_reports/print.php; php -l tests/spmi_auditee_workspace_regression.php; php -l tests/spmi_reports_regression.php; php tests/m17_schema_regression.php; php tests/spmi_instruments_regression.php; php tests/spmi_auditee_workspace_regression.php; php tests/spmi_auditor_workspace_regression.php; php tests/spmi_reports_regression.php; php tests/spmi_audits_regression.php; php tests/legacy_ami_archive_regression.php; php tests/m16_security_regression.php; php tests/hardening_regression.php; GIT_MASTER=1 git diff --check; runtime/DB/migration/browser NOT_RUN_ENVIRONMENT

Static final-report snapshot and auditee final-result acceptance PASS for finalized assessment snapshot population, immutable report reads, auditee-owned final result authorization, and report export snapshot rendering only; no migrations/schema, RTM models/services, legacy, M17-04/05, config/sidebar, or post-M17 behavior changes are included; runtime, DB, migration execution, and browser verification NOT_RUN_ENVIRONMENT; exact implementation commit SHA is represented by Git history because a commit cannot prewrite its own final SHA; M17-07A is eligible after this PASS but NOT_STARTED and not started by this task

M17-07 Runtime Hardening

PASS

e41f0ed (product/schema), 600de8a (test suite), docs commit follows in this batch

Draft runtime lane did not run because the single isolated runner build failed at Composer/Packagist timeout before bootstrap, fixture, or HTTP smoke.

M17-07 PASS execution log: runtime PASS; static PASS; dependency-image-reuse documented. All eight full runtime hardening lanes passed using `M17_07A_REUSE_APP_IMAGE=m17_07a_1000_1786261927_17669-app:latest timeout 900s bash tests/run_m17_07a_runtime.sh run`, exit 0, teardown verified: draft `/tmp/m17_07_draft_reuse_20260809_26187.log`; policy `/tmp/m17_07_policy_reuse_MCbCIM.log`; revision `/tmp/m17_07_revision_reuse_20260809_150243_23466836.log`; assessment `/tmp/m17_07_assessment_reuse_ZM8cjr.log`; report/RTM `/tmp/m17_07_report_rtm_retry_1786265744.log`; upload security `/tmp/m17_07_security_upload_retry_1786267298_c9109a03.log`; legacy archive `/tmp/m17_07_legacy_archive_retry_AxKq.log`; direct download/traversal `/tmp/m17_07_direct_download_retry_FINAL.log`. Static suite PASS: php -l on all 13 modified PHP product/test files; php tests/m17_schema_regression.php; php tests/spmi_instruments_regression.php; php tests/spmi_auditee_workspace_regression.php; php tests/spmi_auditor_workspace_regression.php; php tests/spmi_reports_regression.php; php tests/spmi_audits_regression.php; php tests/spmi_rtm_regression.php; php tests/spmi_rtm_follow_ups_regression.php; php tests/legacy_ami_archive_regression.php; php tests/m16_security_regression.php; php tests/hardening_regression.php; php tests/m17_07a_runtime_fixture_regression.php; PYTHONPYCACHEPREFIX=/tmp/m17_pycache python3 -B -m py_compile tests/m17_07a_http_smoke.py; bash -n tests/run_m17_07a_runtime.sh; GIT_MASTER=1 git diff --check. No runtime migration/DB/browser execution occurred outside isolated container init. M17-08 is eligible after this PASS but NOT_STARTED and not started by this task.

M17-07A Disposable Runtime Fixture Bootstrap

PASS

see Git history (self SHA unavailable in preimage)

php -l tests/m17_07a_runtime_fixture_regression.php; php tests/m17_07a_runtime_fixture_regression.php; python3 -m py_compile tests/m17_07a_http_smoke.py; bash -n tests/run_m17_07a_runtime.sh; docker compose -f tests/m17_07a_runtime.compose.yaml config --quiet; php tests/m17_schema_regression.php; php tests/spmi_instruments_regression.php; php tests/spmi_auditee_workspace_regression.php; php tests/spmi_auditor_workspace_regression.php; php tests/spmi_reports_regression.php; php tests/spmi_audits_regression.php; php tests/legacy_ami_archive_regression.php; php tests/m16_security_regression.php; php tests/hardening_regression.php; tests/run_m17_07a_runtime.sh run; GIT_MASTER=1 git diff --check PASS

Disposable runtime fixture bootstrap PASS only. Verified exact runtime evidence with command `tests/run_m17_07a_runtime.sh run`, project `m17_07a_1000_1786243974_24449`, log `/tmp/m17_07a_runtime_permissions_retry.pykqcy81.log`, fresh named project/network/four volumes, healthy MySQL, `M17-07A HTTP fixture smoke passed.`, and exact-resource teardown removing app/mysql/volumes/network. The readiness curl reset was retried and harmless during startup, not an unaddressed failure. Runtime scope proves only the M17-07A fixture bootstrap for six normal login CSRF accounts plus A/B submission/open and cross-user ownership checks; it does not complete full M17-07 lanes. M17-07 is now actionable NOT_STARTED, and M17-08 remains NOT_STARTED.

M17-08 Cutover Readiness Audit

NOT_STARTED

—

—

—

Allowed status:

NOT_STARTED
IN_PROGRESS
PASS
FAIL
BLOCKED

14. Per-Task Final Output

Setelah setiap task, agent wajib menampilkan:

Task ID dan title.

Branch dan HEAD SHA sebelum task.

Branch dan HEAD SHA setelah task.

Ringkasan behavior yang diterapkan.

File berubah.

Migration/schema changes.

Route dan authorization affected.

Tests ditambahkan.

Commands executed.

Static tests dan runtime tests dibedakan.

Hasil setiap test.

Known limitations.

Konfirmasi non-goals tidak diubah.

git diff --stat.

git status --short.

Commit SHA.

Gate status untuk task berikutnya.

15. Execution Log

Agent boleh menambahkan entry baru di bawah ini tanpa mengubah entry lama.

Format:

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

### 2026-08-05 00:00 — M17-04
- Status: BLOCKED
- Branch: dev
- Start SHA: see Git history
- End SHA: see Git history
- Commit: c823650
- Files: docs/plan/m17-spmi-workspace-parity-master-plan.md
- Tests: Read-only source/Oracle gate review only; runtime not run
- Runtime verification: not run
- Notes: c823650 is the blocker decision commit, and this reconciliation preserves the stop condition. Exact blocker recorded in the task status table. M17-04 locked lifecycle requires persisted `returned_for_revision`/`resubmitted` submission states and assessment-to-source-submission-version provenance to retain stale drafts while rejecting finalization after a source revision. Current schema permits only `draft|submitted` and assessments lack that provenance. V2 assigned the required additive lifecycle schema capacity to M17-01, so M17-04 does not carry a schema-correction allowance. Minimum follow-up decision needed: authorize a corrective M17-01 schema task or an explicit V2 scope decision for one additive forward-safe migration adding only returned/resubmitted state capacity and nullable assessment source-submission-version provenance.
- Next gate: STOP: M17-05 and later tasks must not start until corrective schema scope is authorized and complete.

### 2026-08-05 00:00 — M17-01A
- Status: IN_PROGRESS
- Branch: dev
- Start SHA: see Git history
- End SHA: —
- Commit: —
- Files: docs/plan/m17-spmi-workspace-parity-master-plan.md
- Tests: Not run; documentation-only authorization update
- Runtime verification: not run
- Notes: User-authorized corrective schema task to resolve the M17-04 blocker. No schema implementation completed yet, and no runtime or DB action performed.
- Next gate: execute the additive lifecycle schema correction before any M17-04 behavior work.

### 2026-08-09 00:00 — M17-05A
- Status: PASS
- Branch: dev
- Start SHA: ae81c59
- End SHA: see Git history (self SHA unavailable in preimage)
- Commit: fix(m17): expose auditee evidence URL to auditor
- Files: application/models/Spmi_auditor_workspace_model.php; application/views/spmi_auditor_workspace/assignment.php; tests/spmi_auditor_workspace_regression.php; tests/m17_07a_http_smoke.py; tests/m17_07a_runtime_fixture_regression.php; docs/plan/m17-spmi-workspace-parity-master-plan.md
- Tests: php -l application/models/Spmi_auditor_workspace_model.php PASS; php -l application/views/spmi_auditor_workspace/assignment.php PASS; php -l tests/spmi_auditor_workspace_regression.php PASS; php -l tests/m17_07a_runtime_fixture_regression.php PASS; php tests/spmi_auditor_workspace_regression.php PASS; php tests/spmi_auditee_workspace_regression.php PASS; php tests/m17_07a_runtime_fixture_regression.php PASS; php tests/m17_schema_regression.php PASS; php tests/m16_security_regression.php PASS; php tests/hardening_regression.php PASS; GIT_MASTER=1 git diff --check PASS
- Runtime verification: historical proof cited only; `bash tests/run_m17_07a_runtime.sh run` PASS (exit 0) with project `m17_07a_1000_1786253247_26187` and log `/tmp/m17_05a_final_aavbj7f6.log`; no runtime rerun performed in this finalization step
- Notes: User-authorized corrective dependency is complete. Ownership remains limited to auditor evidence URL read/render parity for owned assignments. The cited runtime proof shows rendered submitted evidence URL visibility, authorized own private PDF evidence download, cross-auditor A-to-B denial, and exact-resource teardown. Historical M17-07 BLOCKED entries remain preserved verbatim. M17-07 is now actionable NOT_STARTED for the remaining full hardening lanes not run, and M17-08 remains NOT_STARTED.
- Next gate: start M17-07 only for the remaining full runtime and hardening lanes; do not start M17-08 yet.

16. Final Milestone Acceptance

M17 dinyatakan selesai hanya jika:

M17-00 sampai M17-08 berstatus PASS;

tidak ada assessment finalized yang editable;

OB/KTS bekerja pada assessment item;

auditee evidence policy bekerja;

revision lifecycle bekerja sebelum finalization;

report snapshot immutable membawa field baru;

auditee dapat melihat hasil final miliknya;

runtime E2E PASS;

legacy regression PASS;

cutover audit menyatakan READY_FOR_CUTOVER_MILESTONE;

legacy belum dihapus atau disembunyikan oleh M17.

17. Post-M17 Follow-Up — Belum Dinomori

Milestone baru setelah M17 dapat membahas:

Future Milestone — SPMI Cutover and Legacy Read-Only Transition

Kemungkinan scope milestone lanjutan:

menghentikan pembuatan tugas legacy baru;

menjadikan legacy operational pages read-only;

menyembunyikan legacy dari sidebar aktif;

mempertahankan historical access;

membatasi arsip legacy untuk Super Admin;

cutover landing dashboard;

observability dan rollback window.

Milestone lanjutan belum diberi nomor dan tidak boleh dimulai otomatis oleh agent dalam eksekusi file ini.

18. Final Stop Rule

Setelah M17-08 selesai:

STOP.
DO NOT START ANY POST-M17 MILESTONE.
DO NOT HIDE LEGACY.
DO NOT MAKE CUTOVER CHANGES.

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

### 2026-08-04 00:00 — M17-00
- Status: PASS
- Branch: dev
- Start SHA: 9652f9f
- End SHA: 1c56a01
- Commit: 1c56a01
- Files: docs/product/spmi-workspace-parity-contract.md, docs/plan/m17-spmi-workspace-parity-master-plan.md
- Tests: STATIC SOURCE REVIEW only, no runtime; markdown autofix and lint commands unavailable in repo root, markdown LSP diagnostics unavailable because no markdown server is configured
- Runtime verification: not run, documentation-only gate repair
- Notes: bootstrap commit `1c2b5ba` is excluded from M17-00 output; `7473bc4`, `318029a`, and `1c56a01` are the relevant M17-00 documentation commits recorded here; status/log-only updates are exempt from the one focused commit rule; M17-00 permits one contract commit plus one metadata/gate update commit; current contract now distinguishes target future behavior from current HEAD for stale draft/version handling and uses exact OB/KTS proof citations from the Auditor controller, service, and view; existing history remains untouched
- Next gate: M17-01 is eligible only after this gate stays PASS, but it must not be started by this task

### 2026-08-04 00:00 — M17-00
- Status: PASS
- Branch: dev
- Start SHA: 1c56a01
- End SHA: 8c2a1c8
- Commit: 8c2a1c8
- Files: docs/plan/m17-spmi-workspace-parity-master-plan.md
- Tests: STATIC SOURCE REVIEW only, no runtime
- Runtime verification: not run, documentation-only reconciliation
- Notes: M17-00 remains PASS; this entry reconciles the ledger to record `8c2a1c8` as the final gate-record commit without changing prior historical assertions; only the plan file changed
- Next gate: STOP: M17-01 not started by this task.

### 2026-08-04 00:01 — M17-00
- Status: PASS
- Branch: dev
- Start SHA: c97114f
- End SHA: c97114f
- Commit: c97114f
- Files: docs/plan/m17-spmi-workspace-parity-master-plan.md
- Tests: STATIC SOURCE REVIEW only, no runtime; markdown lint scripts remain unavailable in repo root
- Runtime verification: not run, documentation-only adoption record
- Notes: V2 remains the primary plan; the former procedural and citation blockers are resolved under V2; this append-only record preserves the historical M17-00 ledger while adopting the current working-tree V2 plan; M17-01 remains NOT_STARTED
- Next gate: M17-01 remains NOT_STARTED

### 2026-08-04 00:02 — M17-01
- Status: PASS
- Branch: dev
- Start SHA: f439fe1
- End SHA: 759baad
- Commit: 759baad
- Files: migrations/025_create_spmi_m17_schema_foundation.sql, database_schema.sql, tests/m17_schema_regression.php, docs/plan/m17-spmi-workspace-parity-master-plan.md
- Tests: php -l tests/m17_schema_regression.php PASS; php tests/m17_schema_regression.php PASS; php tests/spmi_instruments_regression.php PASS; php tests/spmi_auditee_workspace_regression.php PASS; php tests/spmi_auditor_workspace_regression.php PASS; php tests/spmi_reports_regression.php PASS
- Runtime verification: NOT_RUN_ENVIRONMENT; no migrations, DB actions, runtime/browser scenarios, or runtime checks because V2 defers runtime to M17-07
- Notes: Static acceptance PASS for schema-only foundation files; no migration or database execution was performed; no routes, authorization, UI, legacy behavior, or M17-02+ changes were made; dormant report snapshot columns remain schema-only; M17-02 is eligible but NOT started by this task
- Next gate: M17-02 eligible but NOT started by this task

### 2026-08-04 00:03 — M17-02
- Status: PASS
- Branch: dev
- Start SHA: 31e5b55
- End SHA: 099a6db
- Commit: 099a6db
- Files: application/controllers/lpmpi/Spmi_instruments.php, application/services/Spmi_instruments_service.php, application/views/lpmpi/spmi_instruments/question_form.php, application/views/lpmpi/spmi_instruments/question_detail.php, tests/spmi_instruments_regression.php, docs/plan/m17-spmi-workspace-parity-master-plan.md
- Tests: php -l application/controllers/lpmpi/Spmi_instruments.php PASS; php -l application/services/Spmi_instruments_service.php PASS; php -l application/views/lpmpi/spmi_instruments/question_form.php PASS; php -l application/views/lpmpi/spmi_instruments/question_detail.php PASS; php -l tests/spmi_instruments_regression.php PASS; php tests/spmi_instruments_regression.php PASS; php tests/spmi_auditee_workspace_regression.php PASS; php tests/spmi_auditor_workspace_regression.php PASS; php tests/spmi_reports_regression.php PASS; GIT_MASTER=1 git diff --check PASS
- Runtime verification: NOT_RUN_ENVIRONMENT; no runtime/browser/live POST/migration/DB action was run because V2 defers runtime verification to M17-07
- Notes: Static acceptance PASS for Admin LPMPI question evidence_policy CRUD/display only; tested diff contains no schema/migration, route, model, legacy, auditee evidence enforcement, assignment policy snapshot, revision lifecycle, report/RTM/export, auth/config/sidebar, or M17-03+ changes; M17-03 is eligible but M17-03 not started by this task
- Next gate: M17-03 eligible but NOT_STARTED; M17-03 not started by this task

### 2026-08-04 16:47 — M17-02
- Status: PASS
- Branch: dev
- Start SHA: 099a6db
- End SHA: 099a6db
- Commit: 099a6db
- Files: docs/plan/m17-spmi-workspace-parity-master-plan.md
- Tests: STATIC SOURCE REVIEW only; GIT_MASTER=1 git diff --check PASS
- Runtime verification: NOT_RUN_ENVIRONMENT; no runtime/browser/live POST/migration/DB action was run because V2 defers runtime verification to M17-07
- Notes: This entry reconciles the M17-02 ledger to the implementation SHA `099a6db`; the status/log-only documentation reconciliation commit is represented by Git history and does not introduce a separate self-SHA field, placeholder, or invented value; only the plan file changed; M17-03 remains eligible but was not started by this task
- Next gate: M17-03 eligible but not started by this task

### 2026-08-05 00:00 — M17-03
- Status: PASS
- Branch: dev
- Start SHA: 68b8c06
- End SHA: see Git history (self SHA unavailable in preimage)
- Commit: see Git history (self SHA unavailable in preimage)
- Files: migrations/026_add_assignment_item_evidence_policy.sql, database_schema.sql, application/models/Spmi_audits_model.php, application/services/Spmi_audits_service.php, application/controllers/Spmi_auditee_workspace.php, application/models/Spmi_auditee_workspace_model.php, application/services/Spmi_auditee_workspace_service.php, application/views/spmi_auditee_workspace/assignment.php, tests/spmi_auditee_workspace_regression.php, docs/plan/m17-spmi-workspace-parity-master-plan.md
- Tests: php -l application/controllers/Spmi_auditee_workspace.php PASS; php -l application/models/Spmi_audits_model.php PASS; php -l application/models/Spmi_auditee_workspace_model.php PASS; php -l application/services/Spmi_audits_service.php PASS; php -l application/services/Spmi_auditee_workspace_service.php PASS; php -l application/views/spmi_auditee_workspace/assignment.php PASS; php -l tests/spmi_auditee_workspace_regression.php PASS; php tests/m17_schema_regression.php PASS; php tests/spmi_instruments_regression.php PASS; php tests/spmi_auditee_workspace_regression.php PASS; php tests/spmi_auditor_workspace_regression.php PASS; php tests/spmi_reports_regression.php PASS; GIT_MASTER=1 git diff --check PASS
- Runtime verification: NOT_RUN_ENVIRONMENT; no migrations, DB actions, runtime/browser/live HTTP checks, or runtime checks were run because V2 defers runtime verification to M17-07
- Notes: Static acceptance PASS for assignment-item evidence_policy snapshot and server-side auditee submit enforcement only; tested diff contains no M17-04 revision lifecycle, M17-05 auditor assessment, M17-06 reporting/result, legacy, config, route/auth, or .multibrain changes; exact implementation SHA cannot be written into the preimage of the same commit and is therefore represented by Git history without guesswork or rewrite; M17-04 is eligible after this PASS but was not started by this task
- Next gate: M17-04 eligible but NOT_STARTED; M17-04 not started by this task

### 2026-08-05 00:01 — M17-01A
- Status: PASS
- Branch: dev
- Start SHA: 99a1be2
- End SHA: see Git history (self SHA unavailable in preimage)
- Commit: see Git history (self SHA unavailable in preimage)
- Files: migrations/027_add_revision_lifecycle_schema_correction.sql, database_schema.sql, tests/m17_schema_regression.php, docs/plan/m17-spmi-workspace-parity-master-plan.md
- Tests: php -l tests/m17_schema_regression.php PASS; php tests/m17_schema_regression.php PASS; php tests/spmi_auditee_workspace_regression.php PASS; php tests/spmi_auditor_workspace_regression.php PASS; php tests/spmi_reports_regression.php PASS; php tests/spmi_audits_regression.php PASS; php tests/legacy_ami_archive_regression.php PASS; php tests/m16_security_regression.php PASS; php tests/hardening_regression.php PASS; GIT_MASTER=1 git diff --check PASS
- Runtime verification: NOT_RUN_ENVIRONMENT; migrations, DB actions, runtime/browser/live HTTP checks, and runtime checks were not run in this environment
- Notes: Static source acceptance PASS for the additive revision lifecycle schema correction only; the implementation commit is intentionally referenced via Git history because the final SHA cannot be prewritten into the same commit; no controllers, services, models, views, routes, auth, UI, reports, sidebar, legacy, or M17-04 behavior changes are included; M17-04 is eligible after this PASS but was not started by this task
- Next gate: M17-04 eligible but NOT_STARTED; M17-04 behavior not started by this task; M17-05+ and M18 remain NOT_STARTED

### 2026-08-05 00:01 — M17-04
- Status: PASS
- Branch: dev
- Start SHA: see Git history
- End SHA: implementation SHA recorded in Git history
- Commit: implementation SHA recorded in Git history
- Files: application/config/routes.php, application/controllers/Spmi_auditee_workspace.php, application/controllers/Spmi_auditor_workspace.php, application/models/Spmi_auditee_workspace_model.php, application/models/Spmi_auditor_workspace_model.php, application/services/Spmi_auditee_workspace_service.php, application/services/Spmi_auditor_workspace_service.php, application/views/spmi_auditee_workspace/assignment.php, application/views/spmi_auditor_workspace/assignment.php, application/views/spmi_auditor_workspace/index.php, tests/spmi_auditee_workspace_regression.php, tests/spmi_auditor_workspace_regression.php, docs/plan/m17-spmi-workspace-parity-master-plan.md
- Tests: php -l application/config/routes.php PASS; php -l application/controllers/Spmi_auditee_workspace.php PASS; php -l application/controllers/Spmi_auditor_workspace.php PASS; php -l application/models/Spmi_auditee_workspace_model.php PASS; php -l application/models/Spmi_auditor_workspace_model.php PASS; php -l application/services/Spmi_auditee_workspace_service.php PASS; php -l application/services/Spmi_auditor_workspace_service.php PASS; php -l application/views/spmi_auditee_workspace/assignment.php PASS; php -l application/views/spmi_auditor_workspace/assignment.php PASS; php -l application/views/spmi_auditor_workspace/index.php PASS; php -l tests/spmi_auditee_workspace_regression.php PASS; php -l tests/spmi_auditor_workspace_regression.php PASS; php tests/m17_schema_regression.php PASS; php tests/spmi_instruments_regression.php PASS; php tests/spmi_auditee_workspace_regression.php PASS; php tests/spmi_auditor_workspace_regression.php PASS; php tests/spmi_reports_regression.php PASS; php tests/spmi_audits_regression.php PASS; php tests/legacy_ami_archive_regression.php PASS; php tests/m16_security_regression.php PASS; php tests/hardening_regression.php PASS; GIT_MASTER=1 git diff --check PASS
- Runtime verification: NOT_RUN_ENVIRONMENT; runtime, DB, migration execution, and browser checks were not run in this environment
- Notes: All historical BLOCKED entries remain preserved exactly. Static tests passed for the M17-04 submission revision lifecycle implementation and plan update only; no schema, migration, database, reports/RTM/export, legacy, sidebar/config beyond the reviewed SPMI routes, or post-M17-04 code was changed by this task. The exact implementation SHA is represented by Git history because a commit cannot prewrite its own final SHA.
- Next gate: M17-05 is eligible after this PASS but must not be started by this task; M17-05+ and M18 remain NOT_STARTED

### 2026-08-05 00:01 — M17-05
- Status: PASS
- Branch: dev
- Start SHA: 1790f7f
- End SHA: see Git history (self SHA unavailable in preimage)
- Commit: see Git history (self SHA unavailable in preimage)
- Files: application/services/Spmi_auditor_workspace_service.php, application/views/spmi_auditor_workspace/assignment.php, tests/spmi_auditor_workspace_regression.php, docs/plan/m17-spmi-workspace-parity-master-plan.md
- Tests: php -l application/services/Spmi_auditor_workspace_service.php PASS; php -l application/views/spmi_auditor_workspace/assignment.php PASS; php -l tests/spmi_auditor_workspace_regression.php PASS; php tests/m17_schema_regression.php PASS; php tests/spmi_instruments_regression.php PASS; php tests/spmi_auditee_workspace_regression.php PASS; php tests/spmi_auditor_workspace_regression.php PASS; php tests/spmi_reports_regression.php PASS; php tests/spmi_audits_regression.php PASS; php tests/legacy_ami_archive_regression.php PASS; php tests/m16_security_regression.php PASS; php tests/hardening_regression.php PASS; GIT_MASTER=1 git diff --check PASS
- Runtime verification: NOT_RUN_ENVIRONMENT; runtime, DB, migration execution, and browser checks were not run in this environment
- Notes: Static auditor assessment parity acceptance PASS for finding_type parsing, persistence, display, and finalize validation only; M17-04 stale/version protections remain preserved; no controller, model, routes, schema, migration, report/RTM/export/action-plan/legacy/config/sidebar, or M17-06+ changes are included; exact implementation SHA is represented by Git history because a commit cannot prewrite its own final SHA.
- Next gate: M17-06 is eligible after this PASS but must not be started by this task; M17-06+ and M18 remain NOT_STARTED

### 2026-08-05 00:01 — M17-06
- Status: PASS
- Branch: dev
- Start SHA: e730d2e
- End SHA: see Git history (self SHA unavailable in preimage)
- Commit: see Git history (self SHA unavailable in preimage)
- Files: application/config/routes.php, application/controllers/Spmi_auditee_workspace.php, application/controllers/lpmpi/Spmi_reports.php, application/models/Spmi_auditee_workspace_model.php, application/models/Spmi_reports_model.php, application/services/Spmi_auditee_workspace_service.php, application/services/Spmi_reports_service.php, application/views/spmi_auditee_workspace/final_result.php, application/views/lpmpi/spmi_reports/detail.php, application/views/lpmpi/spmi_reports/print.php, tests/spmi_auditee_workspace_regression.php, tests/spmi_reports_regression.php, docs/plan/m17-spmi-workspace-parity-master-plan.md
- Tests: php -l application/config/routes.php PASS; php -l application/controllers/Spmi_auditee_workspace.php PASS; php -l application/controllers/lpmpi/Spmi_reports.php PASS; php -l application/models/Spmi_auditee_workspace_model.php PASS; php -l application/models/Spmi_reports_model.php PASS; php -l application/services/Spmi_auditee_workspace_service.php PASS; php -l application/services/Spmi_reports_service.php PASS; php -l application/views/spmi_auditee_workspace/final_result.php PASS; php -l application/views/lpmpi/spmi_reports/detail.php PASS; php -l application/views/lpmpi/spmi_reports/print.php PASS; php -l tests/spmi_auditee_workspace_regression.php PASS; php -l tests/spmi_reports_regression.php PASS; php tests/m17_schema_regression.php PASS; php tests/spmi_instruments_regression.php PASS; php tests/spmi_auditee_workspace_regression.php PASS; php tests/spmi_auditor_workspace_regression.php PASS; php tests/spmi_reports_regression.php PASS; php tests/spmi_audits_regression.php PASS; php tests/legacy_ami_archive_regression.php PASS; php tests/m16_security_regression.php PASS; php tests/hardening_regression.php PASS; GIT_MASTER=1 git diff --check PASS
- Runtime verification: NOT_RUN_ENVIRONMENT; runtime, DB, migration execution, and browser checks were not run in this environment
- Notes: Static final-report snapshot and auditee final-result acceptance PASS for finalized assessment snapshot population, immutable report reads, auditee-owned final result authorization, and report export snapshot rendering only; no migrations/schema, RTM models/services, legacy, M17-04/05, config/sidebar, or post-M17 behavior changes are included; exact implementation SHA is represented by Git history because a commit cannot prewrite its own final SHA.
- Next gate: M17-07A is eligible after this PASS but must not be started by this task; M17-07+ and M18 remain NOT_STARTED

### 2026-08-05 00:02 — M17-07
- Status: BLOCKED
- Branch: dev
- Start SHA: see Git history
- End SHA: see Git history (self SHA unavailable in preimage)
- Commit: docs(m17): mark M17-07 runtime fixture blocker
- Files: docs/plan/m17-spmi-workspace-parity-master-plan.md
- Tests: STATIC SOURCE REVIEW only; GIT_MASTER=1 git diff --check PASS; markdown lint scripts unavailable in repo root because there is no package.json or bun script in /home/steven/Documents/Audit-Mutu-Internal; no Docker/DB/migration/HTTP/browser mutation was run
- Runtime verification: not run
- Notes: Static preflight evidence found Docker/Compose isolation available, database_dummy.sql:1-3 says it does not create demo users, database_dummy.sql:7-8 selects pre-existing auditor/auditee users, application/services/Auth_service.php:16-25 requires a stored password hash for login, and application/services/User_service.php:47-68 shows user creation exists as application behavior; however no repository-controlled isolated fixture/bootstrap CLI or entrypoint exists to provision synthetic roles and the minimal SPMI runtime graph in a uniquely named fresh Compose project with teardown, so the required M17-07 multi-role runtime graph cannot be provisioned safely without ad hoc test data or shared-state changes; minimum resolution is a repository-controlled disposable fixture bootstrap that creates the synthetic roles and minimal SPMI graph in a fresh Compose project with teardown, then rerun all required runtime lanes; M17-08 remains NOT_STARTED and was not made eligible
- Next gate: STOP: M17-08 must not start; no post-M17 milestone may start

### 2026-08-09 — M17-07A
- Status: IN_PROGRESS
- Branch: dev
- Start SHA: uncommitted working tree
- End SHA: not applicable before completion
- Commit: not created by this task
- Files: tests/m17_07a_runtime.compose.yaml, tests/fixtures/m17_07a_runtime.sql, tests/m17_07a_runtime_fixture_regression.php, tests/m17_07a_http_smoke.py, tests/run_m17_07a_runtime.sh, docs/plan/m17-spmi-workspace-parity-master-plan.md
- Tests: RED static fixture contract confirmed before infrastructure existed; static validation passed; no Docker/DB/migration/HTTP/browser runtime lane was run
- Runtime verification: NOT_RUN_ENVIRONMENT by task scope
- Notes: This dependency owns disposable test-only runtime bootstrap infrastructure only. It does not start or complete M17-07, alter production behavior, replace the historical M17-07 BLOCKED record, or change M17-08 status.
- Next gate: complete static fixture validation, then separately authorize and execute M17-07 runtime lanes; M17-08 remains NOT_STARTED

### 2026-08-09 09:53 — M17-07A
- Status: PASS
- Branch: dev
- Start SHA: 25f3714
- End SHA: see Git history (self SHA unavailable in preimage)
- Commit: test(m17): add disposable runtime fixture bootstrap
- Files: tests/m17_07a_runtime.compose.yaml, tests/fixtures/m17_07a_runtime.sql, tests/m17_07a_runtime_fixture_regression.php, tests/m17_07a_http_smoke.py, tests/run_m17_07a_runtime.sh, docs/plan/m17-spmi-workspace-parity-master-plan.md
- Tests: php -l tests/m17_07a_runtime_fixture_regression.php PASS; php tests/m17_07a_runtime_fixture_regression.php PASS; python3 -m py_compile tests/m17_07a_http_smoke.py PASS; bash -n tests/run_m17_07a_runtime.sh PASS; docker compose -f tests/m17_07a_runtime.compose.yaml config --quiet PASS; php tests/m17_schema_regression.php PASS; php tests/spmi_instruments_regression.php PASS; php tests/spmi_auditee_workspace_regression.php PASS; php tests/spmi_auditor_workspace_regression.php PASS; php tests/spmi_reports_regression.php PASS; php tests/spmi_audits_regression.php PASS; php tests/legacy_ami_archive_regression.php PASS; php tests/m16_security_regression.php PASS; php tests/hardening_regression.php PASS; GIT_MASTER=1 git diff --check PASS
- Runtime verification: tests/run_m17_07a_runtime.sh run PASS (exit 0) with project `m17_07a_1000_1786243974_24449`; log `/tmp/m17_07a_runtime_permissions_retry.pykqcy81.log` shows fresh named network plus four named volumes, healthy MySQL, harmless readiness `curl: (56) Recv failure: Connection reset by peer` retry, `M17-07A HTTP fixture smoke passed.`, and exact-resource teardown removing app/mysql/volumes/network
- Notes: This PASS proves only the disposable M17-07A runtime fixture bootstrap: six normal login CSRF accounts, auditee A/B submission, auditor A/B open, and cross-user denial checks in a fresh schema seeded from `database_schema.sql` plus `tests/fixtures/m17_07a_runtime.sql`. It does not perform broader M17-07 runtime hardening, Docker/DB/HTTP changes beyond the fixture lane, or any M17-08/M18 work. Historical M17-07 BLOCKED evidence remains preserved verbatim.
- Next gate: M17-07 is now actionable and remains NOT_STARTED; M17-08 remains NOT_STARTED and must not start yet

### 2026-08-09 10:00 — M17-07
- Status: BLOCKED
- Branch: dev
- Start SHA: see Git history
- End SHA: see Git history
- Commit: docs(m17): mark M17-07 build environment blocker
- Files: docs/plan/m17-spmi-workspace-parity-master-plan.md
- Tests: STATIC SOURCE REVIEW only; GIT_MASTER=1 git diff --check PASS; markdown lint scripts unavailable in repo root because there is no package.json or bun script in /home/steven/Documents/Audit-Mutu-Internal; no Docker/DB/migration/HTTP/browser action was run
- Runtime verification: not run
- Notes: M17-07 is blocked by a confirmed environment failure in the runtime lane, not by a product or runtime behavior defect. The one runner invocation `bash tests/run_m17_07a_runtime.sh run` failed during Docker image build before bootstrap, fixture, or HTTP smoke because Composer timed out while downloading Packagist metadata, surfaced as `curl error 28`, and exited with Composer code 100. The exact log is `/tmp/m17_07_draft_wkqh7rgj.log`. The draft lane is NOT_RUN, no retry occurred, no product defect is inferred from this failure, and no M17-08 or post-M17 work is authorized from this record. Historical M17-07 BLOCKED entries remain preserved verbatim.
- Next gate: STOP: M17-08 must not start; no post-M17 milestone may start

### 2026-08-09 10:01 — M17-07
- Status: BLOCKED
- Branch: dev
- Start SHA: see Git history
- End SHA: see Git history
- Commit: docs(m17): mark M17-07 build environment blocker
- Files: docs/plan/m17-spmi-workspace-parity-master-plan.md
- Tests: STATIC SOURCE REVIEW only; GIT_MASTER=1 git diff --check PASS; markdown lint scripts unavailable in repo root because there is no package.json or bun script in /home/steven/Documents/Audit-Mutu-Internal; no Docker/DB/migration/HTTP/browser action was run
- Runtime verification: NOT_RUN
- Notes: Confirmed environment blocker for the draft lane. The single runner invocation `bash tests/run_m17_07a_runtime.sh run` failed during Docker image build before bootstrap, fixture, or HTTP smoke because Composer timed out downloading Packagist metadata, reported `curl error 28`, and exited 100. Exact log `/tmp/m17_07_draft_wkqh7rgj.log`. No retry occurred, no product defect is inferred, and no M17-08 or post-M17 work is authorized from this record.
- Next gate: STOP: M17-08 must not start; no post-M17 milestone may start
