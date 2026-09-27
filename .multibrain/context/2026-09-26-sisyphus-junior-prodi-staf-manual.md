# Fase 3 Prodi-Staf Manual

- Scope: management-only roster existing `auditor`/`auditee` under existing Profil; no account, credential, import, PDDikti, migration, schema, sidebar, or legacy AMI changes.
- Decisions: one account may be active in multiple Prodi; add rejects any existing account–Prodi pair; title may change in either state; move updates only a locked active row and preserves creation identity/title/status; inactive pairs are reactivated explicitly.
- Changed: `Prodi_staf_model` persistence-only access; `Prodi_staf_service` transaction/state rules; `Profil` endpoints, numeric routes, per-Prodi view/link; focused and existing profile static regressions.
- Remediation: PDDikti replacement locks the complete Prodi set before a locked roster snapshot and preserves same-code title/status restoration; manual deletion locks its Prodi plus every active/inactive link in one transaction; user role changes lock the account and reject only a linked-account transition outside `auditor`/`auditee`; moves lock source/target Prodi in ascending ID order.
- Verification: PHP lint, focused manual/profile/import/PDDikti/hardening regressions, diagnostics, and anonymous Docker smoke are recorded with this task's final result.
