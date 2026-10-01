# QA Remediation - 2026-09-30

## Scope

Non-destructive post-reset QA remediation. No database, migration, route, controller, or user-data changes.

## Changes

- Replaced stale `assets/img/login-bg.jpg` references in all four authentication views with tracked `assets/img/unmuh-foto.jpg`.
- Added an auth regression guard for the available institutional image.
- Updated the SPMI UI regression guard to accept the intentional `std-button` controls in the standards and indicator detail surfaces while retaining `ami-*` checks elsewhere.
- Updated the legacy archive regression to check current SPMI navigation and remove the obsolete hidden-sidebar entry requirement. Archive routes/controller remain protected and read-only.
- Corrected the M16 smoke GET endpoint to `/auth`; login submission remains POST `/auth/login`.

## Verification

- PHP lint passed for the four auth views and the three changed PHP guards.
- `auth_login_regression.php`, `spmi_ui_consistency_regression.php`, and `legacy_ami_archive_regression.php` passed.
- `python3 -m py_compile tests/m16_http_smoke.py` passed.
- Docker app is up on `127.0.0.1:8081`, database is healthy, `unmuh-foto.jpg` returns HTTP 200, and an anonymous protected SPMI route returns 307 to `/index.php/auth`.
- Playwright login-page QA found the institutional image in the accessibility tree, all image requests returned 200, and browser console errors were zero.

## Residual Risk

Authenticated M16 HTTP smoke was not run because `AMI_SMOKE_EMAIL` and `AMI_SMOKE_PASSWORD` are not configured. PHP LSP remains unavailable because its server was previously declined.
