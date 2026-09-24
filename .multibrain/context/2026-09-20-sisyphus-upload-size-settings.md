# Upload Size Settings

Timestamp: 2026-09-20
Agent: Sisyphus

## Scope

Migration `039_create_upload_size_settings.sql` adds runtime-configurable whole-MiB limits for `super_admin` and `admin_lpmpi` only. The categories and defaults are: `spmi_evidence` 5 MiB, `ppepp_documents` 10 MiB, `profile_photos` 2 MiB, `spreadsheet_imports` 2 MiB, `spmi_source_pdf` 5 MiB, and `institution_logo` 4 MiB.

The management page is `lpmpi/upload-size-settings`. It uses the existing management role base class, a POST-only save endpoint, and the global CSRF-enabled form helper.

## Invariants

- The effective application limit is clamped to the smallest of 10 MiB, PHP `upload_max_filesize`, and PHP `post_max_size` less 1 MiB multipart headroom. Saves above that ceiling are rejected, while pre-existing stored values are safely clamped at read time after deployment changes.
- Existing MIME and image validation, private evidence/profile storage, ownership checks, evidence count limits, Google Drive handling, SPMI mutability checks, and import preview binding remain unchanged.
- Legacy AMI upload endpoints remain excluded. The institutional logo keeps its existing legacy-compatible storage location and PDDikti URL behavior.

## Verification

- PHP syntax checks passed for all changed PHP files.
- `php tests/upload_size_settings_regression.php`, relevant workspace/PPEPP/account/import/standards/PDDikti/sidebar regressions, `m17_schema`, `m16_security`, and `hardening` passed.
- `docker compose config --quiet` passed. Authenticated browser and upload runtime verification was not run because no Docker services or approved credentials were available in this session.
