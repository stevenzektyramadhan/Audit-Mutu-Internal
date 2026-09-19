# SPMI Version-Level Reports

## Decision

New formal SPMI reports are immutable snapshots for one tuple of cycle, SPMI
version, auditor, and auditee. Generation starts from an existing finalized
assessment anchor but requires every assignment in that tuple to have a
finalized assessment whose source submission is still valid. The report is
shown, printed, and exported in separate sections per standard.

## Compatibility

Migration 037 retains historical standard-level reports and their item rows.
New nullable scope and standard-item snapshot columns let one version report
share the existing report/RTM relation without rewriting historical data.
Legacy items fall back to the old report-standard snapshot while rendering.
AMI legacy routes and behavior are unchanged.

## Changed Paths

- `migrations/037_add_spmi_version_report_scope.sql`
- `database_schema.sql`
- `README.md`
- `application/models/Spmi_reports_model.php`
- `application/services/Spmi_reports_service.php`
- `application/controllers/lpmpi/Spmi_reports.php`
- `application/views/lpmpi/spmi_reports/{index,detail,print}.php`
- `tests/spmi_reports_regression.php`
- `docs/plan/spmi-version-level-reports.md`

## Verification

`php -l` passed for all changed report PHP files. Passed: report, audit,
auditee workspace, auditor workspace, RTM, M17 schema, and hardening
regressions. `legacy_ami_archive_regression.php` remains pre-existing red
because the sidebar lacks its expected `dashboard` target. Docker was not
running during final validation, so no authenticated runtime mutation was made.
