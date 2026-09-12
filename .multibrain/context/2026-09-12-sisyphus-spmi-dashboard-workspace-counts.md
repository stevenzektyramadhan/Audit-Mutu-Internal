# SPMI Auditor Dashboard Summary Counts

- Decision: dashboard summary and Penilaian SPMI intentionally have different populations. `Penugasan` counts all owner-scoped assignments in configured/closed cycles. `Submission Terkirim` counts those with `submitted` or `resubmitted` submissions. Penilaian remains limited to assignments that are currently readable for assessment.
- Reason: auditor user 3 owns eight assignments: five for `auditee@ami.test` and three for `droidpeix86@gmail.com`. Three closed assignments have no current-version assessment, so they remain absent from Penilaian but must remain counted by dashboard summaries.
- Scope: changed only `application/models/Spmi_auditor_dashboard_model.php` and `tests/spmi_auditor_workspace_regression.php`; no audit data, schema, workflow state, authorization mechanism, or workspace query changed.
- Verification: PHP syntax checks, workspace/dashboard/auditee/audits regression scripts, read-only database checks, Docker rebuild, and authenticated browser QA as `auditor@ami.test` passed. Dashboard shows 8 Penugasan and 8 Submission Terkirim; Penilaian displays five accessible rows.
