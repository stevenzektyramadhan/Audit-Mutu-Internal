# SPMI Indicator Evidence Policy Schema Remediation

Date: 2026-09-15

## Root Cause

The retained Docker MySQL volume predated raw migration `035_add_indicator_evidence_policy.sql`. The source model selects `i.*`, but the resulting object lacked `evidence_policy` because the database column did not yet exist. The indicator detail view then read the missing property directly.

## Remediation

- Added a detail-view compatibility fallback: an absent runtime property renders escaped `none`; existing values remain escaped and unchanged.
- Applied the existing additive, idempotent migration `035_add_indicator_evidence_policy.sql` to the running `ami` database. No replacement migration, bootstrap import, volume reset, DML, or legacy change was used.
- Created the local recovery set before the schema change:
  - `backups/ami-before-m035-evidence-policy-20260915-132849.sql`
  - `backups/ami-private-before-m035-evidence-policy-20260915-132849.tar`
- The database dump uses `--no-tablespaces` because the local MySQL account lacks the PROCESS privilege required for tablespace metadata; it remains a consistent application-data recovery dump.

## Verification

- Runtime `INFORMATION_SCHEMA` now reports `spmi_indicators.evidence_policy` as `enum('none','file','url','either','both')`, `NOT NULL`, default `none`.
- Existing indicator `id = 7` now returns `evidence_policy = none`.
- Passed: PHP lint for the detail view and focused test; indicator, audit, auditee-workspace, instrument-retirement, and M17 schema regressions; unauthenticated login HTTP smoke returned 200.
- Playwright verified that the reproduced URL redirects an unauthenticated session to login; no privileged credentials were used. App logs after the migration contain no `Undefined property`, PHP warning, fatal, or parse-error entry. A super-admin browser refresh remains the final role-authorized page confirmation.

## Invariants

- Existing assignment-item snapshots, submissions, evidence, reports, Feature B, and AMI legacy data were not modified.
- Future existing environments must apply migration 035 manually after backup; mounting `database_schema.sql` only bootstraps an empty MySQL volume.
