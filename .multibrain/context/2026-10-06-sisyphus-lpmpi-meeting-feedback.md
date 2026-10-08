# LPMPI Meeting Feedback Implementation

Date: 2026-10-06

Implemented the active-SPMI decisions from the LPMPI meeting without changing AMI legacy behavior or historical RTM follow-up data.

## Decisions And Invariants

- A single assignment submission can distribute selected standards to different auditors. Each `(cycle, standard, auditee)` can have only one auditor. Empty groups created by unchecked standards are ignored; selected groups still require a valid auditor.
- New reports use immutable `version_auditee` scope: one report per cycle, source version, and auditee across all contributing auditors. Every report item snapshots the responsible auditor ID, name, and email; contributor displays and all exports read these snapshots only.
- Migration `044_add_spmi_version_auditee_report_scope.sql` is additive and manual. Its generated-column unique guard applies only to `version_auditee`, so legacy `standard` and `version` report multiplicity is unchanged.
- Report actions retain browser print/PDF and XLSX. The added editable Word export is a native UTF-8 HTML `.doc`, avoiding a PHPWord dependency.
- RTM supports one optional JPEG, PNG, or WebP documentation photo while draft only. Migration `045_add_spmi_rtm_photo.sql` adds guarded nullable metadata plus the 5 MiB `rtm_photos` limit. Binaries use private storage, a random name, 0700 directory/0600 file permissions, SHA-256 metadata, rollback cleanup, and management-only protected download. Resolved RTMs remain immutable.
- Active RTM follow-up functionality is retired: routes, menus, mutation surfaces, notifications, and metrics are absent. The historic `spmi_rtm_follow_ups` table, migration 022, data, and referenced-user protection remain intact; no archive UI was introduced.

## Deployment

Apply migrations 044 then 045 manually, after the required database and `APP_PRIVATE_STORAGE_PATH` backups. `database_schema.sql` and README parity were updated for fresh installations. No historic report or RTM-photo backfill is required.

## Verification

Focused and adjacent SPMI, schema, security, navigation, private-storage, authentication, and hardening CLI guards passed. All changed, non-deleted PHP files passed `php -l`; `git diff --check` passed. `docker compose up -d --build` produced only the environment Bake/buildx warning, app and MySQL containers were healthy, and unauthenticated `http://127.0.0.1:8081/` returned HTTP 200. No authenticated end-to-end data mutation workflow was run. PHP LSP diagnostics were unavailable because the PHP language server had previously been declined.

Pre-existing dirty workspace edits and untracked QA artifacts were preserved rather than reset or overwritten.
