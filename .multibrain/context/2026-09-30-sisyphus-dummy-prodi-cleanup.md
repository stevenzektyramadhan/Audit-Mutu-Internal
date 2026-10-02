# Dummy Prodi Cleanup - 2026-09-30

## Scope

User explicitly approved local Docker cleanup of dummy Program Studi `74201` Ilmu Hukum (`profil_prodi.id = 6`) and `55211` Sistem Informasi (`profil_prodi.id = 10`), including their inactive staff history.

## Preconditions

- Live `ami` database contained only three Prodi: targets `6` and `10`, plus retained smoke-test Prodi `12` (`SMK-P12-20260927`).
- Target staff links were `staf_prodi.id = 3` for Ilmu Hukum and `id = 1` for Sistem Informasi; both were `inactive`.
- The only child FK to `profil_prodi` was `staf_prodi.id_prodi` with `ON DELETE CASCADE`.
- No child FK referenced `staf_prodi`.

## Operation

- Created target-only backup at `backups/prodi-cleanup-20260930T085300Z/`:
  - `01-target-profil_prodi.sql`
  - `02-target-staf_prodi.sql`
  - `pre-table-counts.tsv`
  - `SHA256SUMS`
- SHA-256 validation passed before and after deletion.
- In a serializable transaction, re-locked and verified the two targets and two inactive staff links, then deleted only the two `profil_prodi` parent rows. InnoDB cascade deleted the two related `staf_prodi` rows.
- Foreign key checks were never disabled. No source, schema, migration, Docker, or Git changes were made.

## Verification

- `deleted_parent_rows = 2`.
- Target Prodi and staff links remaining: `0` each.
- Retained Prodi: ID `12` only; retained staff link: ID `2` to Prodi `12`.
- Users stayed `15`; SPMI reports stayed `19`; SPMI indicators stayed `80`.
- `pre-table-counts.tsv` versus `post-table-counts.tsv` shows only `profil_prodi` (`3 -> 1`) and `staf_prodi` (`3 -> 1`) changed.
- Docker services remained healthy.
