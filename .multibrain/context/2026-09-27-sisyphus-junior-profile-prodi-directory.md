# Profile Prodi Directory Handoff

- Scope: Added a read-only Program Studi directory to Profil Lembaga without changing Prodi CRUD/import/roster ownership.
- Controller: `Profil::index()` now passes `$prodi` from existing `Profil_model::get_prodi()` only when the profile schema is ready; existing identity, PDDikti, accreditation, and student-statistics data paths remain unchanged.
- View: `application/views/lpmpi/profil/index.php` renders escaped code/name/jenjang rows in a responsive `table-responsive` table, with an empty state and only a management-only link to `lpmpi/master-data-prodi-staf`. No Prodi mutation form or action is added.
- Regression: `tests/profil_manual_data_regression.php` now guards the read contract, escaping, empty state, responsive table, forbidden Profile Prodi actions, and Master Data CRUD ownership.
- Verification: Run the requested PHP lint, focused/related regression scripts, and `git diff --check` after implementation.
