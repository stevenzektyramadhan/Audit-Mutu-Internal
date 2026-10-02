# PPEPP Navigation Redesign

## Scope

Redesigned only the SPMI LPMPI `Dokumen PPEPP` presentation and navigation. No controller, service, model, route, database, authorization, storage, upload, CRUD, validation, API, workflow, or AMI legacy behavior changed.

## Decisions

- Retained the canonical index query contract: `lpmpi/spmi-ppepp-documents?stage=<stage>&year=<year>`.
- Replaced the in-content stage tabs with a PPEPP-only sidebar disclosure. It reads the existing `spmi_ppepp_stages` configuration, preserves stage order and labels, expands whenever the PPEPP menu is active, and marks a child current only when the index supplies `selected_stage`.
- Added only scoped shared sidebar/workspace CSS and small footer JavaScript. Disclosure controls `aria-expanded` plus its submenu `hidden` state. Existing mobile drawer behavior remains; submenu links retain `ami-nav-link` so they still close the drawer on mobile.
- Rebuilt the PPEPP index as a stage-aware workspace: `Beranda / Dokumen PPEPP / stage` breadcrumb, stage icon/copy, preserved year GET form and create query, Penetapan-only checklist cards, responsive document cards, stage/year-aware empty state, and a client-side search over already-rendered cards. Search does not affect server query, service, route, or data behavior.
- Grid breakpoints are 3 columns at >=1200px, 2 at 768px-1199.98px, and 1 below 768px for both checklist and document cards.

## Files Changed

- `application/views/layouts/sidebar.php`
- `application/views/layouts/header.php`
- `application/views/layouts/footer.php`
- `application/views/lpmpi/spmi_ppepp_documents/index.php`
- `tests/sidebar_navigation_regression.php`
- `tests/spmi_ppepp_documents_regression.php`

## Verification

- PHP syntax checks passed for all six changed PHP files.
- Passed: `sidebar_navigation_regression.php`, `spmi_ppepp_documents_regression.php`, `spmi_dashboard_regression.php`, `hardening_regression.php`, `legacy_ami_archive_regression.php`, `m17_schema_regression.php`, `spmi_auditee_workspace_regression.php`, `spmi_auditor_workspace_regression.php`, `spmi_reports_regression.php`, `spmi_audits_regression.php`, `spmi_rtm_regression.php`, and `m16_security_regression.php`.
- `docker compose config --quiet` passed; the local Docker app was already running.
- PHP LSP diagnostics were unavailable because the PHP server is not installed and installation was previously declined.
- Browser reached the anonymous login page at `http://127.0.0.1:8081/index.php/auth`; console reported zero errors. Authenticated PPEPP visual/interaction verification could not run because no local LPMPI credentials were available in tracked project sources.

## Non-Repository Artifacts

Left user-owned untracked artifacts untouched: `qa-dashboard-authenticated.png`, `qa-login-post-reset.png`, and `tests/__pycache__/`.
