# Docker Migration 044 And 045 Applied

Date: 2026-10-06

Applied the active-SPMI manual migrations to the currently running local Docker MySQL database `ami`, in order:

1. `044_add_spmi_version_auditee_report_scope.sql`
2. `045_add_spmi_rtm_photo.sql`

## Backup Evidence

- Database dump: `backups/ami-before-m044-m045-20261006-192148.sql`
  - SHA-256: `b19ef0c97ed3bfa06cc3eb2597ae7a605dbfc82637f9b49510e6dd0798b7fb4e`
- Private storage archive: `backups/ami-private-before-m044-m045-20261006-192136.tar`
  - SHA-256: `304aa385d37dac7612059b29391fce920169895bce6da705fba784efcc18880a`

The first application-account dump attempt failed because `mysqldump` attempted tablespace metadata without `PROCESS`. It was discarded in favor of the successful `--no-tablespaces --single-transaction --routines --triggers` dump above.

## Verified Database State

- `spmi_report_items` has the three auditor snapshot columns.
- `spmi_reports.report_scope` includes `version_auditee`, with stored generated `version_auditee_scope_guard` and unique index `uq_spmi_reports_version_auditee_tuple`.
- `spmi_rtm_meetings` has all five photo metadata columns and unique index `uq_spmi_rtm_meetings_photo_stored_name`.
- `spmi_upload_size_settings` contains `rtm_photos` at 5 MiB.
- Both migration files were rerun successfully to prove their guarded/idempotent behavior.

## Runtime Verification

- Docker `app` and `db` were healthy.
- `http://127.0.0.1:8081/` returned HTTP 200 after migration.
- Compose application logs contained only normal Apache startup and HTTP 200 requests.
- `git diff --check` remained clean.
