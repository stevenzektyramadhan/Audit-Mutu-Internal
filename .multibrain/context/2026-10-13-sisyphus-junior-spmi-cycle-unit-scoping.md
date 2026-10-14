# SPMI Cycle Unit Scoping

- Added additive, idempotent migration 048 and bootstrap/README parity for nullable `spmi_audit_cycles.organization_unit_id`, its index, and RESTRICT FK; the migration was applied to the local Docker database with no historical data backfilled.
- Active SPMI audit cycle create/edit now validate a supported active unit. Assignment candidates and forged-POST eligibility are scoped to active `staf_prodi` through linked study-program units, or exact current `user_unit_assignments` for other supported units. Legacy null-unit cycles remain readable but receive no candidate lists or new assignments.
- Verification passed: PHP lint for changed PHP files; `php tests/spmi_audits_regression.php`; `php tests/m17_schema_regression.php`; `php tests/master_data_organization_staf_regression.php`; Docker app health HTTP 200; `git diff --check`.
