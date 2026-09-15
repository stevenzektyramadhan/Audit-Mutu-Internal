# Fitur A: Evidence Policy per Indikator SPMI

- Date: 2026-09-15
- Scope: completed only Fitur A from `docs/plan/rencana-evidence-policy-dan-import-akun.md`; no Spmi_master import/export, user/auth, AMI legacy, existing assignment/submission data, or `database_dummy.sql` changes.

## Delivered invariant

`migrations/035_add_indicator_evidence_policy.sql` additively and idempotently adds `spmi_indicators.evidence_policy` as `ENUM('none','file','url','either','both') NOT NULL DEFAULT 'none' AFTER evidence_requirement`. The bootstrap schema matches. The indicator controller and service both allowlist the field, while the existing model's generic payload persistence and `i.*` reads need no modification. New assignment items now copy `$indicator->evidence_policy`; stored assignment items remain immutable snapshots.

## Regression and verification

- Added `tests/spmi_indicator_evidence_policy_regression.php`; updated audit, auditee workspace, and instrument-retirement static assertions from the retired static `none` source to the dynamic indicator source while retaining retirement prohibitions.
- PHP syntax checks passed for every changed PHP file. Focused schema/indicator/audit/workspace tests and required auditor, reports, RTM, M16 security, and hardening regressions passed.
- `legacy_ami_archive_regression.php` fails pre-existing/out-of-scope because it expects the sidebar literal `'key' => 'dashboard'`; this task did not touch sidebar or legacy files.
- `docker compose config --quiet` passed; `docker compose ps` showed no running services, so no non-destructive HTTP smoke target existed. `git diff --check` passed. PHP LSP is not installed, and no `lint:md` package script exists.
