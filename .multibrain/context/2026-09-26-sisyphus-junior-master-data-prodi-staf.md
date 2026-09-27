# Master Data Prodi & Staf Hub

## Scope

- Added `application/controllers/lpmpi/Master_data_prodi_staf.php` with a GET-only `index()` extending `Admin_Lpmpi_Controller`.
- The controller loads `Profil_model`, reads only `get_prodi()`, and renders the central read-only directory view with active menu key `master_data_prodi_staf`.
- Added route `lpmpi/master-data-prodi-staf` and a responsive Bootstrap/AMI view.
- The view links the existing `/lpmpi/prodi-staf-import` workflow and each existing `/profil/prodi/{id}/staf` roster page. It contains no CRUD, forms, account data, counts, PDDikti interaction, or duplicate import/roster logic.

## Authorization and boundaries

- Sidebar entry is present exactly for `super_admin` and `admin_lpmpi` in Management, after Struktur Organisasi and before Standar SPMI.
- Auditor and auditee menus remain unchanged and do not expose the hub.
- Existing Profile page links, roster/import services, PDDikti service, schema/migrations, account behavior, and AMI legacy flow are unchanged.

## Verification

- `php -l` passed for the new controller, route file, sidebar, hub view, focused regression, and sidebar regression.
- Passed: `master_data_prodi_staf_regression.php`, `sidebar_navigation_regression.php`, `profil_manual_data_regression.php`, `prodi_staf_manual_regression.php`, `prodi_staf_import_regression.php`, `pddikti_sync_regression.php`, `hardening_regression.php`, and `m17_schema_regression.php`.
- `git diff --check` passed with no output.
- PHP LSP diagnostics were attempted for all changed PHP files but were unavailable because the PHP language server is not installed and installation was previously declined.
