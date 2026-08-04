# SPMI Workspace Parity Contract

## 1. Scope

Contract ini mengunci parity workspace SPMI untuk audit baru, sebelum migration atau perubahan production code M17 berjalan. Source of truth untuk keputusan teknis tetap current repository HEAD, schema dan migrations, lalu routes, controllers, services, models, views, dan regression test yang sudah ada, sesuai plan M17-00 dan urutan source of truth di `docs/plan/m17-spmi-workspace-parity-master-plan.md:288-301`.

## 2. Non-Goals

M17 tidak menghapus legacy AMI, tidak menyembunyikan legacy menu, tidak mengubah route legacy, tidak membuka assessment finalized kembali, tidak memindahkan seluruh data legacy, dan tidak mengubah non SPMI workflow. Workspace parity hanya boleh memakai pola Controller -> Service -> Model yang sudah ada, bukan refactor besar lintas modul. Batas ini mengikuti `docs/plan/m17-spmi-workspace-parity-master-plan.md:261-284` dan `README.md:11-18`.

## 3. Current Architecture Map

Current SPMI surface sudah terpisah jelas, dengan route auditee dan auditor di `application/config/routes.php:185-207`, controller workspace di `application/controllers/Spmi_auditee_workspace.php:4-61` dan `application/controllers/Spmi_auditor_workspace.php:4-15`, service di `application/services/Spmi_auditee_workspace_service.php:4-23` dan `application/services/Spmi_auditor_workspace_service.php:4-76`, model di `application/models/Spmi_auditee_workspace_model.php:4-23` dan `application/models/Spmi_auditor_workspace_model.php:4-38`, report service dan model di `application/services/Spmi_reports_service.php:4-43` dan `application/models/Spmi_reports_model.php:4-74`, RTM service dan model di `application/services/Spmi_rtm_service.php:4-65` dan `application/models/Spmi_rtm_model.php:4-22`, serta instrument authoring di `application/controllers/lpmpi/Spmi_instruments.php:4-34`, `application/services/Spmi_instruments_service.php:4-36`, dan `application/models/Spmi_instruments_model.php:4-30`.

## 4. Final Workflow

Alur final yang dikunci adalah: Admin LPMPI menyiapkan instrumen dan aturan bukti, Auditee mengisi realisasi dan evidence, Auditor menilai dan dapat meminta revisi sebelum finalisasi, System membuat report snapshot immutable, dan Auditee melihat hasil final assignment miliknya. Alur ini sama dengan product direction di `docs/plan/m17-spmi-workspace-parity-master-plan.md:80-113`.

## 5. Submission State Machine

State submission yang dipakai tetap `draft`, `submitted`, `returned_for_revision`, `resubmitted`, `under_assessment`, `completed`, sesuai contract plan di `docs/plan/m17-spmi-workspace-parity-master-plan.md:409-440`. Current implementation sudah memakai token version untuk menahan perubahan stale, misalnya save dan submit auditee mensyaratkan `submission_status === 'draft'` plus version cocok di `application/services/Spmi_auditee_workspace_service.php:13-14`, `application/models/Spmi_auditee_workspace_model.php:13-19`, dan workspace read-only saat submitted di `application/views/spmi_auditee_workspace/assignment.php:1-6`.

## 6. Assessment State Machine

State assessment tetap `draft` lalu `finalized`, sesuai locked lifecycle di `docs/plan/m17-spmi-workspace-parity-master-plan.md:441-468`. Current auditor service hanya mengizinkan mutasi saat submission `submitted`, assessment `draft`, dan version cocok, lalu finalization mengubah status ke `finalized` di `application/services/Spmi_auditor_workspace_service.php:47-69` dan `application/models/Spmi_auditor_workspace_model.php:23-37`. View juga mengunci workspace saat assessment finalized di `application/views/spmi_auditor_workspace/assignment.php:1-2`.

## 7. Revision Contract

Return for revision wajib terjadi sebelum assessment final dan harus tercatat sebagai event yang membawa actor, timestamp, reason, dan submission version. Struktur ini mengikuti field ownership contract di `docs/plan/m17-spmi-workspace-parity-master-plan.md:471-491` dan dua revisi yang dibedakan di `docs/plan/m17-spmi-workspace-parity-master-plan.md:155-181`. Current source belum punya revision route atau table khusus, jadi M17-00 mengunci bahwa revision history akan disimpan pada submission version history dan revision event table yang immutable.

Draft assessment yang sudah dibuat untuk submission version lama tidak boleh difinalisasi setelah revision diminta. Karena current service sudah memaksa version token cocok sebelum save atau finalize, `application/services/Spmi_auditor_workspace_service.php:47-69`, maka behavior yang konsisten adalah stale draft, bukan cancel hard delete: draft lama tetap ada sebagai history, tetapi service harus menolak finalisasi dan penyimpanan dengan conflict saat submission version berubah. Ini juga konsisten dengan auditee side yang menaikkan version setiap draft save, submit, upload, dan delete di `application/services/Spmi_auditee_workspace_service.php:13-17`.

Target contract untuk M17 menetapkan behavior berikut, dan bukan klaim bahwa current HEAD sudah memilikinya: draft yang terbit untuk submission version lama tetap non-finalizable setelah revision diminta, history tetap tersimpan, lalu service menolak save atau finalize dengan conflict saat submission version berubah. Ini mengunci required future behavior untuk stale draft, bukan current-HEAD capability.

## 8. Evidence Policy Contract

Evidence policy per question memakai nilai `none`, `file`, `url`, `either`, dan `both`, dengan default untuk existing question `none`, sesuai plan di `docs/plan/m17-spmi-workspace-parity-master-plan.md:207-229` dan task requirement di `docs/plan/m17-spmi-workspace-parity-master-plan.md:593-607,680-703`. Current instrument authoring sudah punya input instruksti bukti yang wajib valid di `application/services/Spmi_instruments_service.php:31-33` dan `application/controllers/lpmpi/Spmi_instruments.php:28-31`, jadi M17-00 menetapkan policy sebagai field baru pada instrument question, bukan mengganti legacy `pertanyaan`.

Evidence URL disimpan pada submission item sebagai canonical active data, bukan pada report dan bukan pada file table. Ini paling selaras dengan current item based storage untuk realisasi di `application/models/Spmi_auditee_workspace_model.php:8-10`, file evidence child table di `application/models/Spmi_auditee_workspace_model.php:11-21`, dan plan field ownership yang mengizinkan "Submission item atau child metadata sesuai contract" untuk Evidence URL di `docs/plan/m17-spmi-workspace-parity-master-plan.md:471-491`. URL harus nullable, per item, dan hanya aktif untuk policy yang mengizinkannya.

## 9. OB/KTS Contract

Stored value untuk finding type hanya `NULL`, `ob`, atau `kts`, dengan `NULL` berarti tidak ada klasifikasi temuan. Ini mengikuti locked decision di `docs/plan/m17-spmi-workspace-parity-master-plan.md:183-205` dan current source yang membuktikan pilihan OB/KTS sudah ada di form auditor `application/views/auditor/form_penilaian.php:294-304` serta validasi skor server-side berjalan di `application/controllers/Auditor.php:222-227` dan `application/services/Auditor_service.php:98-113`. Contract di bawah ini tetap target future behavior, bukan klaim bahwa current HEAD sudah menyimpan OB/KTS sebagai behavior final.

Validation rule finalnya adalah: jika OB atau KTS dipilih, finding wajib ada; jika KTS dipilih, recommendation wajib ada; score tetap wajib 1 sampai 4 saat finalization. Current auditor service already enforces score `1..4` and finalization completeness in `application/services/Spmi_auditor_workspace_service.php:61-69`, while report generation also rejects incomplete scores and missing rubric matches in `application/services/Spmi_reports_service.php:22-37`.

## 10. Field Ownership Matrix

| Field | Owner | Lifecycle | Canonical Storage |
|---|---|---|---|
| Realization | Auditee | Submission | Submission item |
| Evidence URL | Auditee | Submission | Submission item |
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

The matrix is anchored to the locked plan at `docs/plan/m17-spmi-workspace-parity-master-plan.md:471-491` and to current storage split already visible in submission, assessment, report, and RTM models.

## 11. Proposed Schema Changes

M17-01 must add backward compatible columns and history tables only, with safe defaults and no destructive legacy changes. Minimum proposed changes are: instrument question evidence policy, submission item evidence URL, revision event or history table, submission version history support, and any report snapshot columns needed to persist final immutable data. This follows the M17-01 capability list in `docs/plan/m17-spmi-workspace-parity-master-plan.md:629-659` and the report snapshot rules in `docs/plan/m17-spmi-workspace-parity-master-plan.md:883-916`.

The schema must keep existing rows valid, keep legacy audit working, and preserve private file handling. The current private storage pattern already exists in `application/services/Spmi_auditee_workspace_service.php:14-17` and current report generation already treats finalized assessment as source for immutable inserts in `application/services/Spmi_reports_service.php:14-42`.

## 12. Proposed Route and Service Changes

Auditee needs a canonical final-result route that is separate from workspace edit routes. M17-00 defines `GET auditee/spmi/result/(:num)` as the readonly route for a final report snapshot, while the existing workspace routes remain for draft activity at `application/config/routes.php:185-192`. Auditor and auditee mutating endpoints stay POST-only, matching current `require_post()` guards in `application/controllers/Spmi_auditee_workspace.php:34-61`, `application/controllers/Spmi_auditor_workspace.php:10-15`, and instrument admin routes in `application/controllers/lpmpi/Spmi_instruments.php:10-25`.

Service responsibilities stay where they already live. Workspace services own ownership checks, version conflict checks, transaction boundaries, and file handling, as shown in `application/services/Spmi_auditee_workspace_service.php:10-23` and `application/services/Spmi_auditor_workspace_service.php:13-76`. Model layers remain persistence only.

## 13. Report Snapshot Contract

Report snapshots must be immutable and generated only from finalized assessments. Current report service already requires `aa.status === 'finalized'`, checks `finalized_at`, and snapshots cycle, standard, package, auditor, auditee, and assessment timestamps into `spmi_reports` and `spmi_report_items` in `application/services/Spmi_reports_service.php:14-37` and `application/models/Spmi_reports_model.php:20-74`.

Snapshot fields required by M17 include cycle identity, assignment identity, standard and version identity, question and instrument snapshot, auditee realization, evidence URL metadata, evidence file metadata, score, finding, finding type, recommendation, auditor identity, and finalization timestamps. The report must never read draft assessment as canonical result, and existing report rows must remain unchanged when master data changes, which matches the current immutable insert pattern in `application/services/Spmi_reports_service.php:30-37`.

## 14. Auditee Result Contract

Auditee final-result view is readonly, assignment scoped, and must show the final report snapshot, not the active draft. The route contract is `GET auditee/spmi/result/(:num)`, backed by assignment ownership checks equivalent to current auditee workspace ownership checks in `application/models/Spmi_auditee_workspace_model.php:7-17` and `application/controllers/Spmi_auditee_workspace.php:23-58`.

Direct URL access to another assignment must be denied, and cross-user evidence access must remain blocked. The current evidence download path already resolves file access through ownership guarded model lookups in `application/services/Spmi_auditee_workspace_service.php:17-19` and `application/services/Spmi_auditor_workspace_service.php:72-73`.

## 15. Authorization and Security Rules

Authorization must keep current guard patterns intact. Auditee routes require `auth_guard->only(['auditee'])` in `application/controllers/Spmi_auditee_workspace.php:8-16`, auditor routes require `auth_guard->only(['auditor'])` in `application/controllers/Spmi_auditor_workspace.php:4-9`, and admin instrument routes use the admin LPMPI controller base in `application/controllers/lpmpi/Spmi_instruments.php:4-8`. Mutations must stay POST-only via `require_post()` guards in the workspace controllers and instrument controller.

Private files must remain private. Evidence upload uses `private_storage_dir('audit_evidence')` and `private_storage_path('audit_evidence', ...)` in `application/services/Spmi_auditee_workspace_service.php:14-17`, and download endpoints validate ownership before serving files in `application/services/Spmi_auditee_workspace_service.php:17-19` and `application/services/Spmi_auditor_workspace_service.php:72-73`. No legacy behavior may be weakened to make parity easier.

## 16. Backward Compatibility

Existing submissions, evidence files, and finalized reports must remain readable. Existing auditee save and submit flows already use version tokens and draft only mutation checks in `application/services/Spmi_auditee_workspace_service.php:13-19`, while auditor save and finalize already reject stale versions in `application/services/Spmi_auditor_workspace_service.php:47-69`. Existing report rows are immutable once inserted, so new snapshot fields must default safely and not invalidate prior reports.

Existing instrument questions default to `none` for evidence policy, existing submission item URLs default to NULL, and legacy routes stay live. This is the only backward compatible choice that satisfies the plan requirement for existing questions and keeps current read paths working.

## 17. Migration Order

M17 migration order is dependency driven and backward compatible:

1. Add instrument question evidence policy and submission item evidence URL with safe defaults.
2. Add revision event or history storage plus submission version history support.
3. Add assessment and report snapshot fields needed for immutable final data.
4. Add indexes or constraints only after rows and defaults are safe.

This order aligns with the task dependency graph in `docs/plan/m17-spmi-workspace-parity-master-plan.md:495-517` and the schema capability list in `docs/plan/m17-spmi-workspace-parity-master-plan.md:642-659`.

## 18. Test Matrix

Static source contract checks required for M17-00 are:

| Area | Required check |
|---|---|
| Contract completeness | 21 headings exist and every required decision is stated |
| Source mapping | file line citations exist for routes, controllers, services, models, and views |
| Product approval | Decisions Requiring Product Approval states no decisions |
| Lifecycle | stale assessment behavior is defined as non-finalizable |
| Evidence policy | default none and URL storage decision are explicit |

Future task tests must cover auditee workspace, auditor workspace, report generation, RTM, authorization, private file access, and legacy regression. Current plan already distinguishes static source contract test from runtime test at `docs/plan/m17-spmi-workspace-parity-master-plan.md:344-368`.

## 19. Task-by-Task File Allowlist

Allowed files per M17 task follow the plan gate and must stay narrow:

| Task | Allowed files |
|---|---|
| M17-00 | `docs/product/spmi-workspace-parity-contract.md`, `docs/plan/m17-spmi-workspace-parity-master-plan.md` task status table and execution log cells only |
| M17-01 | schema files, migration SQL, schema regression test |
| M17-02 | instrument controller, service, model, relevant views, targeted tests |
| M17-03 | auditee workspace controller, service, model, views, targeted tests |
| M17-04 | revision workflow controller, service, model, views, targeted tests |
| M17-05 | auditor workspace controller, service, model, views, targeted tests |
| M17-06 | report controller, service, model, views, targeted tests |
| M17-07 | runtime hardening and end to end verification assets only |
| M17-08 | cutover audit docs and verification assets only |

The allowlist is intentionally strict so no unrelated legacy feature gets touched.

## 20. Risks and Rollback Strategy

Main risk is stale version confusion when revision is requested after an assessment draft already exists. The chosen contract behavior is safe stale non-finalizable draft, so rollback is to keep the draft as history, reject further mutation with conflict, and require a new versioned assessment after resubmission. Another risk is leaking private evidence, so all download endpoints must continue to validate ownership before resolving a file path.

If a later schema task uncovers a data compatibility issue, the rollback strategy is to preserve existing rows, keep defaults nullable or safe, and defer any destructive cleanup to a later milestone. This matches the plan prohibition against risky migration and data loss at `docs/plan/m17-spmi-workspace-parity-master-plan.md:381-394`.

## 21. Decisions Requiring Product Approval

None.

contract status: APPROVED_BY_PLAN
