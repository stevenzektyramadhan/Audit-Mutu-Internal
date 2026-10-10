# SPMI Auditi Preview and RTM Recovery

- Scope approved: active SPMI presentation labels use `Auditi`; technical `auditee` roles, routes, tables, snapshots, files, and AMI legacy remain unchanged.
- An auditor sees an owned assignment once its cycle is `configured`. When the auditi submission is absent or `draft`, the route renders a read-only assignment snapshot preview. It cannot read realization/draft content/evidence/revision history or create/edit/finalize an assessment.
- The lightweight auditor waiting indicator is separate from assessable submission/assessment attention counts. Auditi configured-cycle visibility continues through the existing workspace path.
- RTM recovery is only `resolved -> draft`, POST-only and explicitly restricted to `super_admin`; a nonblank reason and immutable resolution event are required in the same transaction. Reports, participants, decisions, photos, and event history are preserved.
- Migration `046_create_spmi_rtm_resolution_events.sql` is additive and has not been applied to the local database. Deployment must back up the database/private storage and execute migration 046 manually before using resolve/unresolve on upgraded databases.
- Verification: PHP syntax checks, `m17_schema`, SPMI audits/auditee/auditor/dashboard/reports/RTM/UI regressions, sidebar, legacy archive, M16 security, hardening, and diff check passed. PHP LSP is unavailable because installation was previously declined.
