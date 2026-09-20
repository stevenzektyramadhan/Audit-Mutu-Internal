# PPEPP Deployment Docs Hardening Slice

Timestamp: 2026-09-20
Agent: Sisyphus-Junior

## Scope and Contract

Implemented only the requested PPEPP deployment/docs/memory/hardening slice. The PPEPP implementation was already present on `feat/spmi-ppepp-documents` through commit `5bc4988`; this slice did not modify PPEPP controllers, services, models, routes, sidebar, or views.

Contract captured here:
- Docker image must tolerate multipart overhead for a valid 10 MiB PPEPP upload while the application limit remains exactly `10485760` bytes.
- PPEPP document files are private-only under storage category `ppepp_documents`, with no public uploads fallback.
- README must describe management-only PPEPP archive behavior, migration `038`, bootstrap schema parity `001-038`, private storage, Docker runtime PHP upload config, menu/code map, and targeted regression command.
- Fresh-vs-upgrade DB parity is documented as an operational requirement, not claimed as runtime-proven in this slice.

## Files Touched in This Slice

- `Dockerfile`: copies `docker/php-upload.ini` into `$PHP_INI_DIR/conf.d/99-upload.ini` for the official `php:8.3-apache` image convention.
- `docker/php-upload.ini`: tracked minimal override with only `upload_max_filesize = 11M` and `post_max_size = 12M`.
- `README.md`: added PPEPP management-only archive docs, private category/no-public-fallback behavior, 10 MiB app limit vs PHP multipart overhead, migration/schema parity `038`, code map, and `php tests/spmi_ppepp_documents_regression.php` in post-pull checks.
- `tests/hardening_regression.php`: updated private category allowlist assertion to include `ppepp_documents` and added a specific no-public-fallback assertion for PPEPP.
- `.multibrain/indexes/ami-workflow.md`: newest-first completion entry for this slice.
- `.multibrain/session.md`: updated `ami-workflow` last-updated date to `2026-09-20`.

## Existing PPEPP Feature Files Evident During Handoff

- `migrations/038_create_spmi_ppepp_documents.sql`
- `application/config/spmi_ppepp.php`
- `application/controllers/lpmpi/Spmi_ppepp_documents.php`
- `application/services/Spmi_ppepp_documents_service.php`
- `application/models/Spmi_ppepp_documents_model.php`
- `application/views/lpmpi/spmi_ppepp_documents/*`
- `tests/spmi_ppepp_documents_regression.php`

## Verification Status

Completed in this slice:
- `php -l tests/hardening_regression.php` -> `No syntax errors detected in tests/hardening_regression.php`
- `php tests/hardening_regression.php` -> `Hardening regression checks passed.`
- `docker compose config --quiet` -> exit 0 with no output
- Markdown validation -> unavailable: no project markdown lint script/config found and neither `markdownlint-cli2` nor `markdownlint` was installed

LSP diagnostics were not authoritative in this environment: PHP LSP was previously declined, Markdown had no server configured, and Dockerfile LSP was not installed.

No Docker up/down, migrations, database backup/restore, browser, network, or git actions were performed for this slice.
