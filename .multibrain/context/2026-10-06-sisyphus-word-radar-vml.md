# Word Export Radar Chart

Date: 2026-10-06

Added a radar chart to the active SPMI native Word-compatible HTML `.doc` export.

## Implementation

- `Spmi_reports::export_word()` now passes the existing immutable report-item collection into `lpmpi/spmi_reports/word`.
- The Word template renders an inline VML radar chart before the report-standard tables when snapshot items exist.
- Chart geometry is generated server-side from ordered `indicator_code_snapshot` and item `score`, defensively clamped to the fixed 0..4 scale.
- It draws four rings, radial axes, and a score polygon. A regular escaped HTML score legend remains visible as a compatibility fallback for Word viewers with incomplete VML support.
- No PHPWord, JavaScript, Chart.js, canvas, SVG, external asset, route, model, service, schema, or migration was introduced.

## Verification

- PHP lint passed for the controller, Word view, and report regression guard.
- `php tests/spmi_reports_regression.php` passed, including immutable item handoff, VML markers, clamped scale, escaped fallback, placement before the report tables, and no browser/external chart dependency.
- `git diff --check` passed.
- Docker was rebuilt; app and database are healthy, root returned HTTP 200, and the running app contains the VML Word template.

## Limitation

No authenticated LPMPI session was available to download an actual `.doc` response and open it in Microsoft Word. The source and container contracts are verified; desktop Word rendering remains the manual final proof.
