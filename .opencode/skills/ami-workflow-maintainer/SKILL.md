---
name: ami-workflow-maintainer
description: Audit or change Audit Mutu Internal (AMI) workflows, CodeIgniter controllers, services, models, roles, audit status, migrations, Excel import, or evidence files while preserving authorization and data invariants.
---

# AMI Workflow Maintainer

Use this skill for any change that affects the internal quality-audit workflow: assignments, auditee answers, auditor assessment, LPMPI management, reports, audit periods, questions, imports, instruments, profiles, or role-protected endpoints.

## Read First

1. Read `.multibrain/session.md`, then `.multibrain/indexes/ami-workflow.md`.
2. Read the relevant controller, service, model, route, view, and migration/schema files before changing behavior.
3. Preserve the established boundary for the affected code. Prefer controller → service → model for new business logic, but do not refactor an existing controller-to-model workflow solely to enforce layering.

   ```text
   Controller → Service → Model → Database
   ```

   Controllers handle requests, validation feedback, redirects, and views. Services own business rules, ownership, state transitions, and transactions when the affected workflow already uses a service. Models own persistence.

## Workflow Safety Checklist

For every role-specific route or mutation, verify all applicable items:

- Preserve the endpoint’s existing authorization mechanism: role base class from `application/core/MY_Controller.php` where used, or `Auth_guard` access controls where the controller uses `CI_Controller`.
- Auditee and auditor actions enforce ownership of the assignment, answer, evidence, or assessment.
- Multi-table mutations use the project transaction pattern and return the established `['success' => ..., 'message' => ...]` result shape.
- Assignment changes preserve duplicate prevention and question-row expansion.
- Audit states only move through supported transitions. Inspect existing services/models for the authoritative statuses; do not invent a status without schema, UI, reporting, and migration coverage.
- Mutating endpoints remain POST-only and inputs use CodeIgniter validation and escaped request access.
- Dynamic HTML output remains escaped with `html_escape()`.
- Routes preserve current aliases and any legacy controller bridge when a URL or controller moves.

## Cross-Layer Change Map

Map requested behavior before editing. Update only layers the feature actually requires.

| Change area | Inspect and update when needed |
| --- | --- |
| Workflow behavior | controller, service, model, role base/guard, relevant views |
| URL behavior | `application/config/routes.php`, route aliases, bridge controllers |
| Persistent fields or states | uniquely numbered migration after inspecting `migrations/`, `database_schema.sql`, models, services, forms/views, reports, `database_dummy.sql` when demo data relies on it |
| Question import/template | `Pertanyaan_service.php`, import controller/routes, spreadsheet column mapping, validation messages, templates |
| Instruments, logos, evidence | upload validation, writable storage path, ownership/role checks on download, Docker permissions |
| Reports or dashboard totals | report/dashboard service and model aggregates, filters, views |
| PDDikti sync | `Pddikti_service.php`, field mapping, fallback endpoints, error/warning handling |

Do not update `database_schema.sql` without a corresponding uniquely numbered migration. Do not create a migration for a behavior-only change.

## Verification

1. Run PHP syntax checks on every changed PHP file.
2. Run the narrowest available command or a targeted manual workflow check. The repository has no tracked automated test suite; add a small regression check for non-trivial logic where practical.
3. For schema work, verify migration ordering, baseline schema parity, and dummy-data compatibility.
4. For authorization work, test an allowed role/owner and a denied role/non-owner path.
5. For import/export work, test a valid file and an invalid row/column case.
6. Re-read all changed files and report any pre-existing failures separately.

## Boundaries

- Do not bypass authorization, ownership validation, transactions, CSRF/session safeguards, or output escaping for expedience.
- Do not introduce a new framework or dependency when CodeIgniter, PHP standard libraries, or installed PhpSpreadsheet cover the need.
- Do not refactor unrelated legacy controllers while fixing a workflow defect.
- Do not overwrite user changes in `README.md`, `application/config/database.php`, `Dockerfile`, `compose.yaml`, `.dockerignore`, or `.omo/` without explicit instruction.

## Memory Write-Back

After meaningful work, add a concise newest-first entry to `.multibrain/indexes/ami-workflow.md`. Create a dated note under `.multibrain/context/` for decisions, changed workflow invariants, blockers, or verification results.
