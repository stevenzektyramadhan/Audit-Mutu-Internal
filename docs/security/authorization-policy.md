# M1-04 — Central Authorization Policy

Status: implemented through M2-03 and verified on 2026-07-25.

## Decision path

Authentication and authorization are separate:

1. `Auth_guard` validates the authenticated session and current database account.
2. `Auth_guard::require_capability()` delegates role-to-capability resolution to `Authorization_policy`.
3. Object actions ask `Authorization_policy` for an assignment/evidence already scoped to the current user.
4. Models repeat owner/state checks before mutations as defense-in-depth.
5. Missing capability, missing scope model, wrong owner, or forbidden state defaults to deny.

IDs from URLs and form fields are identifiers only. They never establish permission.

## Current capability matrix

| Capability | `super_admin` | `admin_lpmpi` | `auditor` | `auditee` |
|---|:---:|:---:|:---:|:---:|
| Dashboard and own account | Yes | Yes | Yes | Yes |
| View institution profile | Yes | Yes | Yes | Yes |
| Manage institution profile | Yes | Yes | No | No |
| Manage all user roles | Yes | No | No | No |
| Manage Auditor/Auditee accounts | Yes | Yes | No | No |
| Manage organization unit master | Yes | Yes | No | No |
| Manage user unit/position assignments | Yes, all users | Auditor/Auditee targets | No | No |
| `spmi.version.manage` | Yes | Yes | No | No |
| `spmi.standard.manage` | Yes | Yes | No | No |
| `spmi.indicator.manage` | Yes | Yes | No | No |
| `spmi.import` | Yes | Yes | No | No |
| `audit.period.manage` | Yes | Yes | No | No |
| `audit.package.manage` | Yes | Yes | No | No |
| `audit.assignment.manage` | Yes | Yes | No | No |
| `audit.submission.fill` | No | No | No | Assigned owner only |
| `audit.submission.submit` | No | No | No | Assigned owner/state only |
| `audit.assessment.fill` | No | No | Assigned owner only | No |
| `audit.assessment.submit` | No | No | Assigned owner/state only | No |
| `audit.report.view` | Yes | Yes | No | No |
| `audit.report.export` | Yes | Yes | No | No |
| `rtm.manage`, `rtm.finalize` | No | No | No | No |
| `followup.fill`, `followup.verify` | No | No | No | No |
| `security.auditlog.view` | Yes | No | No | No |
| View Auditor evidence file | Explicit override only | No | Assigned owner only | No current download route |

All SPMI/audit/RTM/follow-up capabilities above are classified as organization
scoped; `security.auditlog.view` and identity administration are global.
`allowsInOrganizationUnit()` combines the role grant with an active target
unit. Super Admin has explicit institution-wide organization responsibility;
other roles require a direct active `user_unit_assignment`. Parent membership
does not imply descendant access. Legacy objects without an
`organization_unit_id` retain their existing ownership/state checks and are
not mapped from free-text `nama_unit`.

## Object policy API

The central API provides:

- `canViewAuditAssignment()`;
- `canEditAuditeeSubmission()`;
- `canAssessAssignment()`;
- `canViewEvidence()`;
- `canManageOrganizationUnits()`;
- `canManageUserUnitAssignments()`;
- `activeOrganizationAssignments()`;
- `activeOrganizationUnitIds()`;
- `canAccessOrganizationUnit()`;
- `capabilityMatrix()`;
- `capabilityScope()`;
- `allowsInOrganizationUnit()`;
- `allowsInAnyOrganizationUnit()`;
- `canManageSpmiVersion()`;
- `canManageRtm()`;
- `canSubmitFollowUp()`;
- `canVerifyFollowUp()`.

Controllers use the corresponding scoped getters when they need data, such as `getViewableAssignment()`, `getAssessableAssignment()`, and `getViewableEvidence()`. Participant assignment and evidence queries include both object ID and current `auditee_id`/`auditor_id`.

Direct user organization membership now exists. An assignment is effective
only when the user and unit are active and the requested date is inside
`valid_from`/`valid_until`. Parent-to-descendant scope inheritance is not
assumed. RTM, PIC membership, follow-up verification, lead Auditor, observer,
and finalizer assignments still do not exist; their target policy methods
remain deny-by-default.

## Super Admin override

A sensitive ownership override is not implied by the `super_admin` role. The explicit methods:

- require an allowlisted override capability;
- require an existing object;
- require an active Super Admin;
- require a reason of at least 10 characters;
- write `authorization_override` to `auth_security_events`;
- write actor, capability, object type/ID, reason, and request ID to the protected technical security log.

There is no current UI endpoint that invokes an override. Adding one requires a reviewed use case and must not call the ordinary scoped getter as a bypass. M1-08 now records the override in the general immutable audit ledger. Formal retention, review UI, external anchoring, and integrity monitoring remain operational follow-ups.

## Route and compatibility coverage

- Standalone `Users`, `Standar`, `Tugas_audit`, `Dashboard`, root `Auditee`, and root `Auditor` controllers use capabilities.
- `MY_Controller` subclasses use the same `Auth_guard` and policy; the old role-only `only()`/`_check_role()` path was removed.
- LPMPI account, assignment, and report controllers add their specific capabilities.
- M2-01 organization master routes require `organization_units.manage`; Auditor and Auditee requests are denied.
- M2-02 assignment routes require `user_unit_assignments.manage`, plus
  object-level target checks; Admin LPMPI is limited to Auditor/Auditee.
- M2-03 maps SPMI, period, package, assignment, submission, assessment,
  report, and export controllers to fine-grained capabilities. Import,
  submission, assessment finalization/revision, and export receive an
  additional action-specific guard.
- M3-01 adds `spmi_versions.organization_unit_id` as a stable target scope but
  intentionally exposes no mutation route. M3-02 endpoints must require
  `spmi.version.manage` through the combined organization-unit guard, not only
  the role-level `allows()` result.
- Sidebar entries are capability-filtered, but controller policy remains the
  enforcement boundary.
- Auditee route aliases and the duplicate `auditee/Tugas` controller use the same object policy.
- Auditor route aliases and the `auditor/Penilaian` bridge inherit the same object policy.
- The legacy `Auditor::simpan_nilai()` mutation now requires POST and the assessable-assignment policy.
- Account photo/update always derives the target user from the authenticated session.

## Verification

Run:

```powershell
php tests/authorization_policy_regression.php
php tests/role_capability_matrix_regression.php
php tests/spmi_versions_regression.php
php tests/authentication_security_regression.php
php tests/security_configuration_regression.php
php tests/hardening_regression.php
php tests/account_settings_regression.php
php tests/smoke/run.php
```

The isolated HTTP/database matrix covers:

- Auditee A cannot open Auditee B's assignment;
- Auditor A cannot open Auditor B's assignment;
- an Auditor cannot download another Auditor's evidence ID;
- a finalized score cannot be changed and its database row remains unchanged;
- Auditee, Auditor, and Admin LPMPI cannot cross restricted global capabilities;
- valid role and ownership workflows continue to pass.

PIC-to-PIC and RTM-finalizer HTTP tests are not applicable until those entities exist. Their callable policy methods are regression-tested to return `FALSE`, so adding a route before the model/policy is completed fails closed.
