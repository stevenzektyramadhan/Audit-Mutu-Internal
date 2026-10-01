# SPMI And Organization Reset - 2026-09-30

## Authorized Scope

User approved a local Docker database reset of all SPMI versions, standards, indicators, reports, audit workflow data, RTM data, PPEPP document metadata, Prodi/staff placements, and organization units below Universitas. All `users` rows had to remain unchanged.

## Backup And Gates

- Application service was stopped during the final backup and transaction to prevent concurrent writes.
- Full database backup, private-storage archive, users-only snapshots, table-count manifests, and SHA-256 checksums are in `backups/spmi-org-reset-20260930T091158Z/`.
- All backup checksums validated.
- Live preflight confirmed database `ami`, `foreign_key_checks = 1`, 15 users, and exactly one valid root unit: `UNIVERSITAS`.
- Legacy AMI tables had no FK references to reset targets and were not deleted.

## Reset

- A rollback-only dry run proved the FK deletion order before commit.
- One committed transaction deleted SPMI descendants before parents: RTM, reports, assessments, submissions/evidence, assignments/cycles, targets/indicators/standards/versions, drive/PPEPP metadata, then Prodi/staff/placements.
- Non-root organization leaves were removed in order: `ILKOM`, then `FTS`.
- No `TRUNCATE` or `FOREIGN_KEY_CHECKS` override was used.
- Private-storage files were backed up but retained because the approved reset was database data and storage included non-SPMI/orphan files.

## Verification

- All reset tables are empty, including SPMI versions, standards, indicators, reports, cycles, audit items/submissions/assessments/evidence, RTM, PPEPP metadata, Prodi, staff relations, and unit placements.
- `organization_units` contains only root `UNIVERSITAS` (ID 1).
- Users remain 15; before/after users dumps have identical SHA-256: `c740ed27d1df98755d077be532ead3331af79c1370323c96b9350e596e6bcdf6`.
- Full pre/post table comparison changed only approved reset tables and organization hierarchy; AMI legacy, security, configuration, and user tables did not change.
- `foreign_key_checks` remains enabled. Docker database is healthy and app was restarted; `GET /index.php/auth` returned HTTP 200.
