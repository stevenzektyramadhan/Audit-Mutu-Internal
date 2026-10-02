# SPMI Sidebar PPEPP/Evaluasi

- Scope: UI-only changes in `application/views/layouts/sidebar.php` and `tests/sidebar_navigation_regression.php`.
- Management roles now render configured `spmi_ppepp_stages` as direct links, preserving `stage`, `year`, selected-stage `aria-current`, and existing mobile ordinary-link behavior.
- `spmi_standards` and `spmi_audits` are no longer flat role entries; they render as the exact two children of an active/open `Evaluasi` disclosure with unchanged URLs and child-specific `aria-current`.
- Auditor/auditee arrays, unrelated entries, footer hooks, routes, backend behavior, and legacy AMI behavior were not changed.
- Verification: `php -l` both changed PHP files; `php tests/sidebar_navigation_regression.php`; `php tests/spmi_ppepp_documents_regression.php` all passed.
