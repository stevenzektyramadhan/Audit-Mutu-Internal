# SPMI Report Live Search

## Scope

- Changed only `application/controllers/lpmpi/Spmi_reports.php`, `application/views/lpmpi/spmi_reports/index.php`, `application/views/lpmpi/spmi_reports/report_list.php`, and AMI memory.
- No route, service, model, schema, auth, role, report-detail, print, or export changes.

## Behavior

- Async requests use existing `index_data()` filter/pagination semantics and return server-rendered escaped list HTML.
- Search and dropdown changes wait exactly 300ms, abort stale fetches, reset page through `FormData`/`URLSearchParams`, and update only the visible q/filter/page URL.
- Pagination is delegated and fetches list HTML without document reload.
- Existing full GET submit remains available when JavaScript is absent or fails.
- Highlighting creates text nodes and `mark.report-search-match`; empty q removes marks.
- List replacement retains input node, focus, and caret; server-rendered report links remain direct detail/print/XLSX/Word URLs.
- `aria-busy` and live status announce loading/page state. Existing no-results copy and regression literals remain in the original index view.

## Verification

- `php -l` passed for controller, full index view, and async partial.
- `php tests/spmi_reports_regression.php` passed.
- `npm run build` passed; Tailwind emitted only existing Browserslist freshness warnings.
- `git diff --check` passed.
- PHP LSP diagnostics unavailable because PHP server installation was previously declined.
