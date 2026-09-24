# Fitur B: Import Master Data Akun

- Date: 2026-09-15
- Scope: completed Feature B from `docs/plan/rencana-evidence-policy-dan-import-akun.md`; no legacy AMI route/behavior, database migration execution, user-data changes, dependencies, or credential export/download were added.

## Delivered invariant

`migrations/036_add_users_import_support.sql` additively and idempotently adds nullable unique `users.identity_number` and default-zero `users.must_change_password`; `database_schema.sql` and README migration instructions now match migration 036.

`Akun_import` extends `Admin_Lpmpi_Controller`; template, upload preview, confirm, and cancel use the explicit `lpmpi/akun-import` routes. Preview/confirm/cancel are POST-only where mutating. XLSX parsing accepts only sheet `Master Akun` and exact headers `NIP/NIDN`, `Nama`, `Email`, `Role`, bounded to 1,000 rows/2 MiB; formulas, invalid values, file duplicates, and existing NIP/NIDN/email are rejected. Preview JSON is private-storage `tmp`, bound to actor/session token, SHA-256 integrity, 30-minute expiry, and atomically renamed on claim. Confirm transactionally revalidates and locks identities, creates only auditor/auditee accounts, hashes fresh random temporary passwords, and sets the change-password flag. Plaintext passwords are only supplied to the immediate result render; they are absent from session, flash, logs, disk, database, and exports.

Login preserves current role redirects, shows the imported-account advisory on the normal destination, and password reset clears the flag in its existing successful transaction. The Users index links directly to import.

## Verification

- `php -l` passed for every changed PHP file and `tests/akun_import_regression.php`.
- Passed: akun import, auth login, password reset, users consolidation, M17 schema, auditee/auditor workspace, reports, audits, RTM, sidebar navigation, instrument retirement, M16 security, and hardening regressions.
- `docker compose config --quiet` passed; `docker compose up -d --build` succeeded, `docker compose ps` found healthy app/database, and `curl -i http://127.0.0.1:8081/index.php/auth/login` returned HTTP 200. No authenticated import execution ran because it would create user data.
- `legacy_ami_archive_regression.php` remains out-of-scope/pre-existing failure: it expects retired sidebar literal `'key' => 'dashboard'`. `tests/spmi_instruments_regression.php` is absent.
- PHP LSP is unavailable; `git diff --check` passed. User-owned `.playwright-mcp/` and `backups/` were not changed.
