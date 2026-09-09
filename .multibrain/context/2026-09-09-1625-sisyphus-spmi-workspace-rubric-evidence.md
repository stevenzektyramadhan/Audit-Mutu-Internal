# SPMI Workspace Rubric and Evidence Changes

Date: 2026-09-09 16:25 WIB
Agent: Sisyphus
Branch: `feat/spmi-workspace-rubric-evidence`

## Delivered Behavior

- Retired per-question rubric authoring from the instrument workflow. New assignments use the existing global `skor_audit_options()` scale: 1 Tidak sesuai, 2 Kurang sesuai, 3 Sesuai, 4 Sangat sesuai.
- Assignment creation snapshots those four descriptors into immutable assignment-item rubrics. Historical rubric data, assignment snapshots, and report semantics remain intact.
- Auditee evidence upload uses XHR when JavaScript is available. The page does not navigate, unsaved realization and evidence URL fields remain untouched, and successful responses refresh CSRF/version fields and append safe evidence download/delete controls. Normal multipart POST redirect remains the no-JavaScript fallback.
- Auditor assignment pages render question context, realization, auditee evidence, rubric, assessment fields, autosave controls, and auditor evidence controls in one card per item. Independent evidence forms remain outside the single assessment form to avoid nested forms.
- Auditor autosave refreshes every document `input[name="version"]`, including detached auditor evidence forms, preventing stale optimistic-lock versions after an item save.

## Relevant Files

- Instrument/global rubric: `application/controllers/lpmpi/Spmi_instruments.php`, `application/services/Spmi_instruments_service.php`, `application/models/Spmi_instruments_model.php`, `application/services/Spmi_audits_service.php`, `application/models/Spmi_audits_model.php`, `application/config/routes.php`, `application/views/lpmpi/spmi_instruments/question_detail.php`.
- Auditee upload: `application/controllers/Spmi_auditee_workspace.php`, `application/services/Spmi_auditee_workspace_service.php`, `application/models/Spmi_auditee_workspace_model.php`, `application/views/spmi_auditee_workspace/assignment.php`.
- Auditor card layout: `application/views/spmi_auditor_workspace/assignment.php`.
- Regression guards: `tests/spmi_instruments_regression.php`, `tests/spmi_audits_regression.php`, `tests/spmi_auditee_workspace_regression.php`, `tests/spmi_auditor_workspace_regression.php`.

## Commits

- `255012a refactor(spmi): retire per-question rubric authoring`
- `4055184 feat(spmi): use global rubric snapshots`
- `6802229 feat(spmi): show shared rubric scale`
- `3506a69 fix(spmi): preserve auditee answers during evidence upload`
- `261b5d4 fix(spmi): colocate auditor assessment cards`

## Verification

- Passed focused PHP regression scripts for instruments, audits, auditee workspace, and auditor workspace.
- Isolated Docker/Playwright QA used a fresh image whose relevant view hashes matched the workspace. It proved auditee upload preserves unsaved entries, auditor cards are colocated without nested forms, autosave updates detached form versions, and auditor evidence upload succeeds after autosave.
- Shared Compose app was rebuilt/recreated with `docker compose up --build --force-recreate --no-deps --detach app`; database and named session/upload/private-storage volumes were preserved. Current shared container hashes match the workspace auditor and auditee views.
- PHP LSP diagnostics were unavailable because a PHP language server is not installed.

## Operational Notes

- The shared app source is baked into the image by `Dockerfile`; it has no source bind mount. Rebuild/recreate `app` after future PHP/view changes when testing the shared stack.
- Do not remove historical `spmi_instrument_rubrics` data or assignment-item rubric snapshots; they protect historic audit/report meaning.
- The unrelated `tests/m17_07a_runtime_fixture_regression.php` plan-document assertion still fails before fixture setup. Do not alter it or its referenced plan document as part of this workflow work.
- Pre-existing local changes to `Spmi_indicators` and local `.playwright-mcp/` artifacts were deliberately excluded from the feature branch.
