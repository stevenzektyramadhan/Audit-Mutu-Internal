# SPMI Report Cycle Group Export

## Decision

The Laporan SPMI page now selects a cycle first, then one immutable version-report group. A group is one `cycle + SPMI version + auditor + auditee` report snapshot. Only `report_scope = version` rows stored in `spmi_reports` are selectable.

## Implementation

- `Spmi_reports_model` reads selector cycles, groups, and selected reports only from `spmi_reports` snapshots.
- `Spmi_reports_service::version_report_selector()` normalizes positive scalar GET values and resolves a report only when it belongs to the selected cycle and has version scope.
- The index view renders Detail, Print, and XLSX actions only from that validated resolved report. Existing direct report routes remain the compatibility path for historical standard reports and RTM links.

## Boundaries

- No migration, schema, route, or AMI legacy change is required.
- The selector does not read live assignments, submissions, assessments, evidence, users, cycles, or versions.
- XLSX and print still consume the existing immutable report and report-item snapshots.

## Verification

- PHP syntax checks passed for the changed model, service, controller, and index view.
- `spmi_reports`, RTM, audit, workspace, M17 schema, hardening regressions and `git diff --check` passed.
- PHP LSP remains unavailable because its installation was previously declined.
