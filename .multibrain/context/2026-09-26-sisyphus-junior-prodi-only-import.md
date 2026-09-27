# Prodi-only XLSX import

## Decision and scope

The approved transition removes only Staf processing from the existing import. The canonical management route is `lpmpi/prodi-import`; old `lpmpi/prodi-staf-import/*` endpoints remain aliases for one compatibility release.

## Contract

- The workbook has exactly one `Prodi` sheet with exactly `kode_prodi`, `nama_prodi`, and `jenjang` headers.
- Preview and confirmation retain private storage, actor/session token, SHA-256, 30-minute TTL, ZIP preflight, formula rejection, and atomic rename claim. The confirm payload is exactly `prodi`, `errors`, and `total`.
- Import only reads/writes `profil_prodi`; it does not parse Staf, query `users`, match accounts, or write `staf_prodi`.
- CSV validation errors remain POST-only, private/no-store/nosniff, UTF-8 BOM + `fputcsv`, formula-neutralized, and non-consuming.
- New Prodi-only artifacts use `prodi_import_*`; purge also removes stale `prodi_staf_*` previews/claims, and old payloads cannot validate/confirm.
- Blank spreadsheet rows are ignored consistently: preview `total` counts only nonblank submitted rows, so confirmation accepts a valid workbook with blank interior or trailing rows.
- Fixed confirmation rejection of valid previews: PHP preserves `row_number` as `integer` through the private JSON round-trip, so payload validation now expects `integer` rather than the invalid `int` type label.

## Explicitly retained

Manual Profile roster routes/UI and current `staf_prodi` data/schema/migration 041 remain intact. `Profil_model::replace_prodi()` continues preserving roster relationships for PDDikti reconciliation. No migration, schema, data, PDDikti service, sidebar topology, or AMI legacy behavior changed.

## Verification

- PHP lint and focused Prodi/Profile/roster/PDDikti/security/schema/sidebar regressions passed, together with available SPMI regression scripts.
- Docker Compose rebuilt successfully; app and database are up, anonymous Prodi import access redirects to login, and a POST without CSRF is rejected with HTTP 403.
- Rebuilt Docker after the `row_number` fix; runtime source contains the `integer` contract and the focused import/Profile/roster/PDDikti/hardening checks pass.
- PHP LSP remains unavailable because installation was previously declined. Authenticated upload/confirmation remains untested because no authorized management credentials were supplied.
