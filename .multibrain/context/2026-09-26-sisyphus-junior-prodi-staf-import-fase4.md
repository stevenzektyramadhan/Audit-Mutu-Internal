# Fase 4 Prodi-Staf Import Error CSV

## Scope completed

Added the protected `POST lpmpi/prodi-staf-import/errors` download for the existing Prodi–Staf import preview. It streams one UTF-8-BOM CSV from normalized validation-error rows stored in the preview's private JSON artifact.

## Preserved invariants

- The preview remains actor-, token-, TTL (30 minute)-, basename-path-, and SHA-256-bound before any JSON is decoded.
- `errors()` reads only the validated private payload and never trusts browser error data.
- `errors()` never renames, clears, unsets, or otherwise consumes the preview. `confirm()` remains the sole atomic `rename()` claimant.
- Previews containing only row-level errors are persisted for export, but retain no confirmation token and cannot be confirmed.
- CSV text fields neutralize spreadsheet formulas when the first non-whitespace character is `=`, `+`, `-`, or `@`, or when the input starts with tab/CR. Row values remain integers.
- No database/schema/migration, user/account, PDDikti, AMI legacy, or sidebar surface changed.

## Changed files

- `application/config/routes.php`
- `application/controllers/lpmpi/Prodi_staf_import.php`
- `application/services/Prodi_staf_import_service.php`
- `application/views/lpmpi/prodi_staf_import/preview.php`
- `tests/prodi_staf_import_regression.php`

## Verification

2026-09-26: `php -l` passed for all five changed PHP files. Passed: `php tests/prodi_staf_import_regression.php`, `php tests/profil_manual_data_regression.php`, `php tests/prodi_staf_manual_regression.php`, `php tests/pddikti_sync_regression.php`, and `php tests/hardening_regression.php`. `git diff --check` passed. PHP LSP was unavailable because installation was previously declined.
