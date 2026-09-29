# Master Data Organisasi & Staf

## Delivered

- Added migration `042_link_profil_prodi_to_organization_units.sql`: nullable unique `profil_prodi.organization_unit_id` with a restrictive FK to `organization_units`; no backfill or automatic mapping.
- Expanded `lpmpi/master-data-prodi-staf` into the canonical organization and staff directory with unit hierarchy, six summaries, Prodi mapping state, generic-unit management, and non-Prodi placements.
- Kept `lpmpi/organization` controller, routes, and legacy permissive behavior available for direct compatible access, while removing it from the two management sidebars.
- Manual new Prodi now creates a linked active `study_program` below an explicitly selected active Faculty in one transaction. Mapped edits synchronize code, name, and Faculty parent. Existing unlinked Prodi are linked only through an explicit Faculty form.
- Prodi import retains exactly `kode_prodi`, `nama_prodi`, and `jenjang`; mapped updates synchronize organization code/name transactionally while newly imported Prodi remain unlinked. PDDikti replacement preserves a link only for an unchanged exact Prodi code.

## Invariants

- Canonical Master generic writes permit only Faculty, Bureau, Unit, and Institute. Parent matrix is enforced in the service: Faculty/Institute/Bureau under University; Unit under Bureau; Study Program under Faculty for Prodi-specific use.
- All mutations remain POST/CSRF guarded and capability protected. Profile-to-unit mutations are service-owned transactions; there is no Faculty inference or automatic legacy mapping.
- A mapped Prodi cannot be hard-deleted. The local Docker database has not received migration 042 and must not be changed without an explicit backup/deployment authorization.

## Verification

- PHP lint passed for every changed PHP source, view, and focused regression file.
- Passed Master Data, Organization, Profile/Prodi, import, PDDikti, sidebar, all listed SPMI workspace/report/RTM, user, M17, M16, and hardening regressions.
- `docker compose config --quiet` passed; anonymous Docker login endpoint returned HTTP 200 at `http://127.0.0.1:8081/index.php/auth`.
- `git diff --check` passed. `legacy_ami_archive_regression.php` remains red on its stale expectation for a legacy `dashboard` sidebar key already absent before this work; it is unrelated to the Master Data changes.
