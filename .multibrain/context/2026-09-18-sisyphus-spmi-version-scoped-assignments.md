# SPMI Version-Scoped Assignments

## Decision

Assignment creation now selects one eligible SPMI version (`draft` or `review`) and optionally submits `source_standard_ids[]`. No submitted standard IDs means every standard in the locked version. Each selected standard remains one independent existing-format assignment snapshot.

## Safety Invariants

- The service locks the draft cycle, then the selected version, then the version-scoped standards.
- Standard IDs must be scalar positive integers and belong to the selected version.
- Any missing indicator, invalid scope, invalid actor roles, or duplicate tuple rolls back the complete batch before an insert is committed.
- Each generated assignment retains indicator evidence requirement/policy snapshots and score 1-4 rubric snapshots.
- No schema, route, migration, report, finalized-report, or AMI legacy behavior changed.

## Changed Paths

- `application/controllers/lpmpi/Spmi_audits.php`
- `application/services/Spmi_audits_service.php`
- `application/models/Spmi_audits_model.php`
- `application/views/lpmpi/spmi_audits/assignment_form.php`
- `tests/spmi_audits_regression.php`
- `tests/spmi_instrument_retirement_regression.php`
- `docs/plan/spmi-assignment-version-standard-scope.md`

## Verification

PHP syntax and the SPMI audits, instrument retirement, evidence policy, auditee workspace, auditor workspace, report, M17 schema, and hardening regressions passed. `legacy_ami_archive_regression.php` remains pre-existing red because the sidebar has no `dashboard` target. Docker Compose was rebuilt and started; root login returned HTTP 200 and the protected SPMI route redirected to login through `index.php`. Authenticated workflow smoke testing remains blocked without an authorized LPMPI session and controlled draft-cycle fixture.
