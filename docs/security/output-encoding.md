# M1-05 — Output Encoding and XSS Protection

Status: implemented for the current AMI surface and verified on 2026-07-24.

## Output policy

All current business fields are plain text. AMI does not currently accept rich HTML for standards, questions, answers, findings, recommendations, action plans, profile data, file names, or report content.

The canonical helpers in `application/helpers/app_helper.php` are:

| Context | Helper | Rule |
|---|---|---|
| HTML body and quoted attribute | `ami_e()` | `htmlspecialchars` with quotes, invalid UTF-8 substitution, and mandatory double encoding |
| Plain multiline text | `ami_text()` | `ami_e()` followed only by generated line breaks |
| URL supplied by stored/external data | `ami_safe_http_url()` then `ami_e()` | Only absolute HTTP/HTTPS URLs with a host; unsafe/control-character URLs return an empty string |
| Data embedded in `<script>` | `ami_json()` | JSON with `JSON_HEX_TAG`, `AMP`, `APOS`, and `QUOT` plus invalid UTF-8 substitution |

Business views no longer call `html_escape()` directly. Form redisplay obtains the raw value with `set_value(..., FALSE)` and applies `ami_e()` exactly once at the output boundary. Framework-generated validation markup remains the only trusted structural HTML rendered by business views; its field names and messages are application-defined, not stored rich text.

If a future requirement introduces rich text, it must define a field-specific allowlist sanitizer and storage/rendering contract before that field is enabled. `strip_tags()` is not an approved rich-text sanitizer.

## Covered output surfaces

- names, profile fields, standards, descriptions, and questions;
- Auditee answers and evidence URL fields;
- Auditor findings, finding types, recommendations, action plans, and evidence dates;
- legacy/stored file names in text and `data-*` attributes;
- dashboard, profile, and report chart JSON;
- exception/PHP-error details in development and generic production errors;
- external profile-logo URLs and evidence links;
- XLSX report cells and hyperlinks.

Stored evidence links and external logo URLs are not trusted merely because they were validated during input. They are allowlisted again when rendered. Unsafe legacy values remain visible only as encoded form data when editing is applicable and are never emitted as clickable `href`/`src`.

## Excel and other exports

The current repository has XLSX export and no PDF/CSV export.

- Every user-controlled text cell is written with `setCellValueExplicit(..., TYPE_STRING)`.
- Formula-like values such as `=WEBSERVICE(...)` therefore remain string data.
- A hyperlink is created only after `ami_safe_http_url()` accepts an HTTP/HTTPS URL.
- Invalid or non-HTTP evidence URL values are exported as `-`, not as a link.

Any future CSV/PDF/email renderer must implement and test its own output context. HTML encoding must not be reused as CSV, PDF markup, shell, or SQL encoding.

## Verification

Run:

```powershell
php tests/output_encoding_regression.php
php tests/hardening_regression.php
php tests/smoke/run.php
```

The static/runtime regression exercises HTML text, quoted attributes, entity breakout, multiline text, URL schemes/control characters, and script-breaking JSON. The isolated HTTP/database smoke suite stores raw HTML/SVG/script payloads in standards, questions, answers, findings, recommendations, action plans, legacy file names, and URLs. It verifies encoded rendering across Auditee, Auditor, admin detail, LPMPI report, and chart surfaces.

The smoke suite also downloads the generated XLSX, reopens it with PhpSpreadsheet, verifies a formula-like recommendation has string cell type, and confirms an unsafe evidence URL is not exported as a hyperlink value.

RTM decisions, root-cause records, progress notes, PDF, email, and CSV do not exist in the current application. They are not claimed as runtime-covered; their future views/exports must use this policy and add adversarial tests before release.
