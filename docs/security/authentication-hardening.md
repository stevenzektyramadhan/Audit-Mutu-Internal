# M1-03 — Session and Authentication Hardening

Status: implemented and verified on 2026-07-24.

## Controls implemented

- Login uses PHP `password_verify()` and `PASSWORD_DEFAULT`; a valid older hash is transparently rehashed.
- Missing account, wrong password, and inactive account all return the same credential error. A dummy hash verification reduces the timing difference for an unknown email.
- The pre-login session ID is regenerated with the old ID destroyed before authenticated state is written.
- Protected requests validate the current database account on every request. Inactive/deleted accounts, role changes, and `session_version` changes revoke an existing session.
- Idle timeout is 30 minutes. Absolute authenticated-session lifetime is 8 hours.
- Logout is POST + CSRF only, records a security event, and destroys the complete session.
- Login failures are limited in a shared database window: 5 failures per normalized email or 20 failures per client IP within 15 minutes. Once a threshold is reached, login is locked until the oldest relevant failure leaves the window.
- A successful login resets the email-specific failure history for counting purposes. It does not reset the IP-wide history.
- Only active Auditor/Auditee accounts can be selected for new assignments.
- Super Admin and Admin LPMPI account screens expose active/nonactive status. Password, role, and active-status changes increment `session_version`.
- The last active Super Admin cannot be deactivated, demoted, or deleted. A logged-in administrator cannot change their own role/status.

## Security event data

`auth_security_events` is an application append-only event stream for:

- failed, successful, and throttled login;
- logout;
- idle and absolute session expiration;
- session revocation after account changes;
- explicit sensitive Super Admin authorization overrides introduced by M1-04.

The table stores HMAC-SHA-256 values for normalized email, client IP, and user-agent, plus optional user ID, event/reason allowlists, request ID, and timestamp. It does not store a password, raw email, raw IP, raw user-agent, cookie, request body, or credential value. Production HMAC derivation uses `APP_ENCRYPTION_KEY`; production already fails closed if that key is absent.

This table remains a narrowly scoped security-control event stream. M1-04 reuses it for `authorization_override` events, but it is not a substitute for the general immutable domain audit log, retention policy, integrity monitoring, and restricted export planned for M1-08. See `docs/security/authorization-policy.md`.

## Database upgrade

Fresh databases receive the fields and event table from `database_schema.sql`. Existing databases must be backed up and upgraded manually:

```bash
mysql -u <user> -p <database> < migrations/012_authentication_hardening.sql
```

Migration 012 adds `users.is_active`, `users.session_version`, `users.password_changed_at`, `users.last_login_at`, and `auth_security_events`. Existing users default to active with session version 1. It is idempotent and was executed twice successfully against the local Laragon database.

Deploy the migration before deploying the application code. Existing login sessions from the old version do not contain the new authentication timestamps/version and will be asked to log in again.

## Verification

Run:

```powershell
php tests/authentication_security_regression.php
php tests/authorization_policy_regression.php
php tests/security_configuration_regression.php
php tests/hardening_regression.php
php tests/account_settings_regression.php
php tests/smoke/run.php
```

The isolated smoke suite verifies session ID rotation, logout invalidation, inactive-account denial, temporary login throttling, and database-driven session revocation in addition to the existing role workflows.
