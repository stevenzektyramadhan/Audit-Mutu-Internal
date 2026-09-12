# SPMI Auditor Visibility Alignment

## Decision

The auditor SPMI list exposed closed assignments without a current-version assessment even though `Spmi_auditor_workspace_service::workspace()` deliberately rejects them. Hide only that inaccessible state in `assignments()`, `cycle_options()`, and `attention_count()` using `c.state != 'closed' OR aa.id IS NOT NULL`.

Configured assignments remain visible without an assessment because opening them can create a draft. Closed assignments with a current-version draft or finalized assessment remain visible as readable history.

## Verification

- `php -l application/models/Spmi_auditor_workspace_model.php`
- `php -l tests/spmi_auditor_workspace_regression.php`
- `php tests/spmi_auditor_workspace_regression.php`
- `php tests/spmi_auditee_workspace_regression.php`
- `php tests/spmi_audits_regression.php`
- Authenticated browser: assignments 15-17 no longer appear; configured assignment 11 and closed assignment 18 with a draft assessment open successfully; direct SPMI URL for 15 remains controlled 404.

No migration, database mutation, audit-state change, or legacy AMI change was made. The demo database contains no SPMI assignment owned by another auditor, so non-owner runtime denial could not be exercised without creating test data.
