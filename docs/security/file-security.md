# M1-06 — File Security Foundation

Status: implemented for every upload route currently present in AMI. M3-02
uses the private PDF-only `spmi_source` policy for the version workflow.

## Security boundary

`application/libraries/File_security.php` is the only component allowed to accept a new uploaded file. It provides:

- per-category extension and size allowlists;
- server-side MIME/content inspection rather than trusting the browser header;
- PDF signature checks, image decoding checks, and DOCX/XLSX container inspection;
- rejection of legacy executable/script formats, double-extension executable names, macros, ActiveX, and embedded OOXML objects;
- 192-bit random storage names;
- SHA-256 checksum and size verification;
- original-name metadata separate from the storage name;
- upload/download/blocked/retirement/purge events with actor, owner, and request ID;
- forced attachment downloads with `nosniff`, CSP sandbox, and private no-store caching;
- soft-delete with a 90-day retention deadline before physical purge.

The registry is stored in `file_assets`; security events are stored in
`file_security_events`. Apply `migrations/013_file_security_foundation.sql`
before deploying this code. `spmi_versions.source_file_asset_id` references
the registered source; apply migration 017 before the version workflow is
exposed.

## Category policy

| Category | New-upload types | Limit | Scope |
|---|---|---:|---|
| SPMI source document | PDF | 20 MiB | Private |
| Instrument | PDF, DOCX, XLSX, PNG, JPEG | 5 MiB | Private |
| Penetapan attachment | PDF, DOCX, XLSX, PNG, JPEG | 5 MiB | Private |
| Auditor evidence | PDF, DOCX, XLSX, PNG, JPEG | 5 MiB | Private |
| Future minutes/attendance | PDF, DOCX, XLSX, PNG, JPEG | 10 MiB | Private |
| Question import | XLSX | 2 MiB | Private temporary |
| Account photo | PNG, JPEG | 2 MiB | Private |
| Institution logo | PNG, JPEG | 4 MiB | Public, because it is a directly rendered site asset |

DOC, XLS, GIF, SVG, HTML, archives, macro-enabled Office formats, PHP/PHAR/PHTML, scripts, and executable formats are not accepted for new uploads.

The browser `Content-Type` value is ignored for trust decisions. DOCX/XLSX must contain the expected OpenXML entries. OOXML paths, macros, ActiveX, embedded objects, suspicious executable entries, excessive entry count, and excessive uncompressed size are rejected.

## Storage and lifecycle

Sensitive and temporary categories are stored beneath `APP_PRIVATE_STORAGE_PATH`, which production already requires to exist outside the document root. Only the institution logo remains under `uploads/profil`.

Normal replace/delete flows remove the business-record reference and mark the previous `file_assets` row as `deleted`. The bytes remain inaccessible for 90 days. They are physically removed only by the CLI retention job:

```text
php index.php maintenance purge_files 100
```

Temporary XLSX import files are the explicit exception: they are destroyed immediately after parsing and recorded as purged because they are transient transport data, not audit evidence.

Failed uploads that never become a registered business file may also be physically cleaned up immediately.

For SPMI versions, create binds an unowned uploaded asset to the new version
inside the create transaction. Replacing a draft file binds the new asset
before retiring the old one. Clone makes a new random-named private asset,
verifies size/SHA-256 against the source, and binds it to the new draft; it
never reuses the source asset row.

## Legacy migration

Development can still read a legacy filename from `uploads/instrumen`, `uploads/penetapan`, or `uploads/bukti_auditor`; it is registered lazily with its current size and checksum. Production deliberately disables this fallback so sensitive bytes cannot be served from the document root.

Before production rollout:

1. Apply migration 013.
   For the configured local Laragon database only, the guarded runner is:

   ```text
   php scripts/database/apply_local_m1_06.php
   ```

   It refuses production mode and non-local database hosts. Production migration remains a controlled DBA/deployment action.
2. Set `APP_PRIVATE_STORAGE_PATH` to an existing writable directory outside the web root.
3. Run the migration script without mutation and review the exact list:

   ```text
   php scripts/migrate_private_storage.php
   ```

4. After backup and review, move the listed files:

   ```text
   php scripts/migrate_private_storage.php --apply
   ```

The script verifies every copied SHA-256 checksum before removing its legacy source. A migrated legacy file receives a registry row on its first authorized access.

## Verification

```text
php tests/file_security_regression.php
php tests/authorization_policy_regression.php
php tests/spmi_versions_regression.php
php tests/smoke/run.php
```

The isolated smoke suite performs real multipart uploads and verifies:

- content-mismatch and script rejection;
- random names and original-name metadata;
- private location and SHA-256;
- authorized attachment download headers;
- integrity failure after byte tampering;
- upload/download/blocked event records;
- replacement soft-delete and retained bytes;
- Auditor evidence ownership;
- SPMI PDF upload, owner binding, authorized download, clone to a distinct
  private asset, and checksum preservation.

## Residual risk

No antivirus engine or CDR service is integrated. Quarantine status exists as a lifecycle option but is not automatically driven by a scanner. ZIP/OpenXML checks reduce risk but do not replace endpoint malware scanning. Production still needs filesystem ACL/no-execute controls, backup/restore tests, disk monitoring, a scheduled retention job, and a decision on legal hold before evidence retention becomes legally authoritative.
