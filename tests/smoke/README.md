# AMI Isolated Smoke Test

This harness protects the current AMI workflow and the security foundations
added through M1-08.

## Run with one command

When PHP is available on `PATH`:

```text
php tests/smoke/run.php
```

Laragon PowerShell example:

```powershell
& 'C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe' tests\smoke\run.php
```

The process exits with code `0` only when every smoke scenario passes. A failed
assertion, HTTP error, database error, server startup error, or cleanup error
produces a non-zero exit code.

## Prerequisites

- PHP 7.4 or newer.
- PHP extensions `curl`, `mysqli`, `fileinfo`, and `zip`.
- A local MySQL-compatible test/development server.
- The configured local database user must be able to create and drop a
  temporary database.
- `database_schema.sql` must be present.
- No internet connection is required.

The test intentionally refuses to run when `CI_ENV=production`.

By default, the configured database host must be `localhost`, `127.0.0.1`, or
`::1`. An isolated CI database service may be enabled explicitly with:

```text
SMOKE_ALLOW_REMOTE_DB=1
```

Only use that override when `DB_HOST`, `DB_USERNAME`, `DB_PASSWORD`, and
`DB_DATABASE` point to a dedicated CI/test database server. Never use the
override against production.

## Isolation model

Each run:

1. Loads connection settings from `application/config/database.php` without
   printing credentials.
2. Refuses production mode and, by default, non-local database hosts.
3. Generates an allowlisted name such as `ami_smoke_<pid>_<random>`.
4. Creates that temporary database.
5. Removes `CREATE DATABASE` and `USE` statements from `database_schema.sql`
   before importing the schema into the selected temporary database.
6. Seeds synthetic users, period, standard, and questions.
7. Starts a PHP built-in server with:
   - `CI_ENV=testing`;
   - `DB_DATABASE` set to the temporary database;
   - a temporary private-storage directory;
   - a temporary log directory;
   - an isolated localhost port.
8. Runs the HTTP workflow with separate in-memory cookie sessions.
9. Stops the server, drops only the allowlisted temporary database, and
   removes the uniquely named temporary directory in a `finally` block.

No fixture is copied from production. Fixture names, email addresses, answers,
URLs, findings, and passwords are synthetic and exist only for the duration of
the test.

## Covered behavior

The current suite runs 30 passing cases:

- real HTTP security headers, per-request CSP nonce matching the rendered HTML,
  no-store dynamic cache policy, and development HTTP HSTS exclusion;
- invalid and valid login for all four roles;
- correct role dashboard rendering and login session-ID rotation;
- complete logout invalidation, inactive-account denial, shared login
  throttling, and database-driven session revocation;
- assignment creation and one answer row per question;
- Auditee draft and final submission;
- cross-Auditee and cross-Auditor assignment denial;
- Auditor assessment/finalization and post-final mutation denial;
- assigned evidence download and cross-Auditor evidence denial;
- wrong-role capability denial and valid LPMPI report access;
- raw stored-XSS payload encoding across Auditee, Auditor, admin detail,
  LPMPI detail, and chart JSON;
- unsafe stored evidence URL denial at the rendering boundary;
- XLSX download/reopen verification that formula-like text remains a string
  and an unsafe URL is not exported as a hyperlink value;
- real multipart PDF upload for instruments and Auditor evidence;
- server-side content mismatch and HTML/script upload rejection;
- private storage location, random storage name, original-name metadata,
  SHA-256, integrity-tamper denial, secure attachment headers, and file events;
- replacement soft-delete with a retention deadline and retained old bytes.
- immutable audit events for authentication, assignment, submissions, file
  lifecycle, and sensitive export; continuous hash-chain verification; HMAC
  network identifiers; and database-trigger rejection of direct update/delete.

The tests assert both rendered HTTP responses and resulting rows in the
temporary database:

- assignment starts as `belum_diisi`;
- draft stays `belum_diisi` and is not submitted;
- Auditee submit changes the task to `diisi`;
- every submitted answer has its submission flag and timestamp;
- Auditor finalization changes the task to `dinilai`;
- every final answer has a valid `1..4` score, final flag, and timestamp.

## What the harness does not cover

- Visual layout or JavaScript behavior in a real browser.
- CDN asset availability.
- Antivirus/CDR, EICAR, every supported DOCX/XLSX/image category, malformed
  parser corpus, filesystem ACL/no-execute, and production legacy migration.
- Auditor revision/reopen behavior.
- AJAX per-item assessment save.
- Legacy `auditee/Tugas`, legacy Auditor methods, and compatibility proxy
  reachability.
- Concurrent writes, idempotency, race conditions, or performance.
- Production schema/data/configuration.

These are explicit gaps, not implied passes.

## Failure diagnostics

The runner prints `[PASS]` for completed scenarios and `[FAIL]` for the first
failure. When the local web server is involved in the failure, a short tail of
its temporary error log is printed before cleanup. The output does not print
fixture passwords, database credentials, CSRF tokens, or stored session data.

After an interrupted run, an administrator can check for leftover databases
whose names match the exact `ami_smoke_%` prefix. Do not delete any database
based on a broader pattern. A normal completed run leaves no smoke database or
PHP server process.

## Current M0-03 baseline result

Validated locally on 2026-07-24 with PHP 8.3.30 and MySQL 8.4.3:

```text
Smoke tests passed: 30
Database isolation: temporary database only; cleanup scheduled.
```

This result describes the inspected local environment only.
