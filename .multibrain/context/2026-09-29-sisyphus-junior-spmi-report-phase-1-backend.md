# SPMI Report Phase 1 Backend

- Added idempotent additive migration 043 and bootstrap/README parity for nullable `spmi_reports.academic_year_snapshot`; historical rows are not rewritten and NULL rows only belong to all-years history.
- Generation reads the already locked cycle once, trims `academic_year`, and persists its nonblank value or NULL without changing the version report identity tuple or transaction sequence.
- `Spmi_reports_service::index_data()` is the controller-facing snapshot-only contract: normalized `academic_year`, `cycle_id`, `version_id`, `auditee_id`, `q`, and `page`; contextual option validation; count/pagination; paged report aggregates; summary; and standard analysis. Model index methods use only `spmi_reports` and `spmi_report_items`; report `detail`, `print`, and XLSX paths remain unchanged.
- `application/views/lpmpi/spmi_reports/index.php` was deliberately not edited because Phase 1 UI is owned by another delegate. It must consume the supplied `index_data` payload and remove its old selector/radar client-side contract.
- Verification: PHP lint for controller/service/model/regression; `php tests/spmi_reports_regression.php`, `spmi_audits_regression.php`, `spmi_rtm_regression.php`, `m17_schema_regression.php`, and `hardening_regression.php` passed.
