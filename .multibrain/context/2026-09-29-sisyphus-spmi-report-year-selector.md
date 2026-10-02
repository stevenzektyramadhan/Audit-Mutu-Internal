# SPMI Report Academic-Year Selector

## Decision

The SPMI version-report export selector now uses the existing audit-cycle `academic_year` as its first filter. It does not infer a year from dates or use report generation time.

## Behavior

- The selector order is `Tahun akademik` -> `Siklus` -> `Kelompok penugasan`.
- Years are derived from version-scope report snapshots joined to their source audit cycles.
- An empty or unknown `report_year` is the all-years state, which keeps historical cycles with missing academic-year data discoverable.
- A selected cycle is checked against the cycles available for the selected year before its report groups are loaded; stale or crafted cycle IDs are cleared.
- The lower report-history table stays all-history and client-side filtered.

## Scope And Verification

No migration, schema, route, report generation, radar, detail/print/XLSX, or AMI legacy behavior changed. PHP lint and the report, audits, RTM, M17 schema, and hardening regression guards passed. The healthy Docker app redirected anonymous parameterized report access to login as expected.
