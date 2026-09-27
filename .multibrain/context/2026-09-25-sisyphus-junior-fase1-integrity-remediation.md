# Fase 1 Prodi-Staf Integrity Remediation

Date: 2026-09-25

## Decisions and invariants

- Corrected un-applied migration 041 and bootstrap parity: `fk_staf_prodi_prodi` is `ON DELETE CASCADE`; the existing manual Prodi delete remains service-blocked by `prodi_has_staf`.
- `Profil_model::replace_prodi()` now snapshots `id_akun`, `jabatan`, `status`, and old `kode_prodi`, deletes `profil_prodi` inside its existing transaction, inserts the non-empty PDDikti replacement, and restores relations only where the replacement retains that code. No PDDikti controller/service behavior changed; empty remote payload still preserves local data.
- Import code lookup returns all matches. Preview reports duplicate existing `kode_prodi`; confirm serializes with MySQL named lock, rechecks multiplicity under `FOR UPDATE` before writes, releases the lock in `finally`, and rejects the complete batch if ambiguous.
- Confirm fully validates the actor-bound stored JSON shape and normalized parser contract before the transaction: exact keys/types, row bounds, lengths, duplicate fields, Prodi references, deferred field shape, and normalized total; it then locks/rechecks the existing email + NIDN/NIP account identity before writes. `fakultas` and `jumlah_mahasiswa` remain deferred/template-only.
- Controller preflights XLSX with built-in `ZipArchive` after upload/MIME checks and before PhpSpreadsheet: 100 entries, 2 MiB total uncompressed, 512 KiB per XML, no encrypted entries, absolute/backslash/traversal names, and safe failure if ZipArchive is unavailable.

## Verification

- PHP lint passed: `Profil_model.php`, `Prodi_staf_import_model.php`, `Prodi_staf_import_service.php`, `Prodi_staf_import.php`, `prodi_staf_import_regression.php`, `pddikti_sync_regression.php`.
- Passed: `prodi_staf_import_regression.php`, `pddikti_sync_regression.php`, `profil_manual_data_regression.php`, `hardening_regression.php`, `sidebar_navigation_regression.php`, `account_settings_regression.php`, `m17_schema_regression.php`, `m16_security_regression.php`.
