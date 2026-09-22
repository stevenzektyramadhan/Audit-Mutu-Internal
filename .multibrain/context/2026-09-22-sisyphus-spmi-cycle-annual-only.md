# SPMI Cycle Annual-Only

Timestamp: 2026-09-22 11:38 WIB
Agent: Sisyphus

## Scope

SPMI audit cycles are annual-only: the create/edit flow no longer requests, validates, or persists `semester`; cycle list and detail pages display `academic_year` only. The nullable `spmi_audit_cycles.semester` column and any historic values remain unchanged.

## Invariants

- AMI legacy, schemas, migrations, persisted data, routes, assignments, reports, RTM, and auditor/auditee workspaces were not changed.
- `academic_year` remains required free text with its existing maximum length. Cycle code, title, description, dates, transitions, and POST-only mutation behavior remain unchanged.
- Historic cycles with `ganjil` or `genap` values remain readable without displaying a semester.

## Verification

- Static: PHP syntax checks passed for all six changed PHP files. `spmi_audits`, `m17_schema`, `spmi_reports`, `hardening`, and `sidebar_navigation` regressions passed.
- Static baseline failure: `legacy_ami_archive_regression.php` fails because `HEAD` already lacks the asserted `dashboard` sidebar target; neither its test nor sidebar was changed.
- Runtime: anonymous Docker login endpoint returned HTTP 200. Authenticated cycle create/edit verification was not run because no approved account was available (`NOT_RUN_ENVIRONMENT`).
