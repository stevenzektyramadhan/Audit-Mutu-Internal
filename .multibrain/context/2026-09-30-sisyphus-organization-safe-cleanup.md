# Organization Safe Cleanup - 2026-09-30

User selected the safe-cleanup option for local Docker database `ami`: preserve accounts, all `profil_prodi` rows, SPMI indicators, reports, and other audit data; remove only organization structure that is not referenced by SPMI indicators.

## Backup

- Database dump: `backups/organization-master-cleanup-20260930-142623/ami-before-cleanup.sql`
- SHA-256: `bf4ea43b20f39601b8501e7981d90f0bc7151ee4575685aeb457459f7a5c6f75`
- Private storage copy: `backups/organization-master-cleanup-20260930-142623/private-storage`
- The first dump attempt failed only because the local DB account lacks `PROCESS`; the successful retry used `mysqldump --no-tablespaces` and did not mutate data.

## Applied Transaction

- Cleared nullable `profil_prodi.organization_unit_id` links for Prodi IDs 3 (`57201`), 5 (`62201`), 7 (`14201`), and 10 (`55211`).
- Deleted the sole `user_unit_assignments` row (user 12 at unit `ILKOM`), as requested to clear staff placement.
- Deleted unreferenced organization units `FEB` (8), `14201` (9), `FKIP` (10), `55211` (11), `57201` (12), and `62201` (13), in child-before-parent order.
- Retained `UNIVERSITAS` (1), `FTS` (6), and `ILKOM` (7), because all are referenced by the 80 SPMI indicators. Foreign-key checks were never disabled.

## Verification

- Preserved: 15 users, 11 Prodi, 80 SPMI indicators, and 19 SPMI reports.
- Remaining organization units: exactly 3 (`UNIVERSITAS -> FTS -> ILKOM`).
- User-unit assignments: 0.
- Deleted unit IDs still present: 0.
- Stale Prodi links to deleted units: 0.
- Broken SPMI indicator organization references: 0.
