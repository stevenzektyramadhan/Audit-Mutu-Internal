# Prodi UI Ownership Move

- Date: 2026-09-26
- Scope: Approved UI move only; no schema, migration, route, service, model, account, PDDikti, AMI legacy, or sidebar topology changes.
- Master hub: `application/views/lpmpi/master_data_prodi_staf/index.php` now exposes Add Prodi, Import Prodi, escaped row Edit, POST+CSRF Delete with browser confirmation, and the existing roster link.
- Profile: `application/views/lpmpi/profil/index.php` no longer renders Prodi CRUD/directory/roster/import UI; institution profile, accreditation, PDDikti-related surface, and student-statistics actions remain.
- Navigation: Prodi CRUD result redirects and form return/cancel paths target `lpmpi/master-data-prodi-staf`; roster view uses Master active context and Back link while roster mutations still redirect to the same roster route; import index/preview/confirm/cancel use Master context/return.
- Backend preservation: `Profil.php` keeps `profil/prodi/*`, `require_manage()`, `require_schema_ready()`, `require_post()`, Profil_service calls, row-scoped writes, and dedicated staff-link deletion protection. Student-stat result redirects remain Profile.
- Verification: PHP lint on every modified PHP file; `master_data_prodi_staf_regression.php`, `profil_manual_data_regression.php`, `prodi_staf_manual_regression.php`, and `prodi_staf_import_regression.php`, plus focused repository regressions and `git diff --check`.
