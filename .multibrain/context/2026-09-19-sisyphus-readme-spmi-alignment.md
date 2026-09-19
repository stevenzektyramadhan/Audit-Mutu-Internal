# README SPMI Alignment

## Decision

Document SPMI as the sole active workflow because authenticated redirects and the visible sidebar are SPMI-only. AMI legacy remains unchanged as a non-sidebar archive URL at `lpmpi/legacy-ami-archive`.

## Changed Paths

- `README.md`: replaced legacy-primary/demo material with verified SPMI workflow, status, evidence-policy, role/menu, code-map, deployment, database, security, and post-pull documentation.
- `.multibrain/indexes/ami-workflow.md`: added this newest-first handoff entry.
- `.multibrain/session.md`: retained the `ami-workflow` date at `2026-09-19`.

## Verification

- Confirmed `composer.lock` exists and is tracked; README now directs production installs to use it.
- Confirmed bootstrap and upgrade documentation reaches migration `037_add_spmi_version_report_scope.sql`.
- Preserved deployment, Drive, and reset-password operational boundaries while correcting documented facts.
- `git diff --check` passed. The following regressions passed: `auth_login`, `sidebar_navigation`, `spmi_audits`, `spmi_auditee_workspace`, `spmi_auditor_workspace`, `spmi_reports`, `m17_schema`, and `hardening`.

## Out-of-Scope Observations

- Docker currently autoloads the legacy-only `database_dummy.sql` after the schema.
- `.gitignore` contains a composer-lock entry despite the tracked `composer.lock`.
- The user manual still references legacy sidebar items.
- Legacy AMI controllers and routes remain in the repository.
