# Master Data Prodi & Staf Content Redesign

## Scope

- Updated only the hub content view at `application/views/lpmpi/master_data_prodi_staf/index.php`.
- Added `Profil_model::get_prodi_master_data()` as one read-only grouped query over `profil_prodi` with active `staf_prodi` counts; it safely returns zero staff counts if migration 041 has not yet been applied.
- `Master_data_prodi_staf::index()` derives total Prodi, total active linked staff, and distinct jenjang counts from returned rows.
- The view now has exactly three summary cards, actual jenjang badges, searchable code/name, actual jenjang filter options, responsive table, useful empty/no-results states, and client-side pagination fixed at 10 rows per page.

## Preserved contracts

- `Admin_Lpmpi_Controller` authorization and GET-only hub endpoint unchanged.
- Import remains `lpmpi/prodi-import`; add/edit/delete remain `profil/prodi/*`; roster remains `profil/prodi/{id}/staf`.
- Delete remains `form_open()` POST+CSRF with the existing browser confirmation.
- No route, schema, global CSS, sidebar, service, PDDikti, account, API, database write, or legacy AMI changes.

## Verification

- PHP lint passed for modified model, controller, view, and focused regression.
- Passed: `master_data_prodi_staf_regression.php`, `profil_manual_data_regression.php`, `prodi_staf_manual_regression.php`, `prodi_staf_import_regression.php`, `pddikti_sync_regression.php`, `hardening_regression.php`, `sidebar_navigation_regression.php`, and `m17_schema_regression.php`.
- Final `lsp_diagnostics` and `git diff --check` are required after this note is written.
