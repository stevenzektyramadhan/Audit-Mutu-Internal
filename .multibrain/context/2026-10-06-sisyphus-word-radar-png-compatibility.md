# Word Radar PNG Compatibility Fix

## Decision

The editable native HTML `.doc` export now creates its radar chart with PHP GD and embeds it as a self-contained `data:image/png;base64,...` image. The prior inline VML format was removed because a user-provided export proved their document viewer ignored the VML and showed only the fallback table.

## Invariants

- The chart uses only immutable report item snapshots: `indicator_code_snapshot` and `score`.
- Scores are clamped to the existing 0..4 chart scale.
- No chart dependency, remote URL, stored file, new route, schema change, or live data read was added.
- The escaped HTML indicator/score table remains as accessible text and a compatibility fallback.

## Evidence

- PHP GD was enabled in the local runtime (`PHP 8.3.6`).
- LibreOffice `24.2.7.2` imported a self-contained HTML `.doc` PNG data URI and exported it to PDF; `pdfimages -list` detected the embedded image.
- The final generated-radar fixture produced a detectable `900x700` RGB PNG in its converted PDF.
- `php -l application/views/lpmpi/spmi_reports/word.php`
- `php -l tests/spmi_reports_regression.php`
- `php tests/spmi_reports_regression.php`
- `git diff --check`
- Docker rebuilt successfully; app/db are healthy, root HTTP returned 200, and the running template contains the PNG data URI without VML.

## Remaining Limit

Microsoft Word itself was not available for a viewer-specific manual open test. The observed user artifact, GD runtime check, and LibreOffice import/conversion are the available renderer evidence.
