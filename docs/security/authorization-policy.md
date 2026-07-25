# M1-04 — Central Authorization Policy

Status: implemented for the current AMI surface and verified on 2026-07-24.

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
| Manage current SPMI master data | Yes | Yes | No | No |
| Manage audit assignments | Yes | Yes | No | No |
| View current LPMPI reports/export | Yes | Yes | No | No |
| Work on assigned Auditee submission | No | No | No | Assigned owner only |
| Assess assigned audit | No | No | Assigned owner only | No |
| View Auditor evidence file | Explicit override only | No | Assigned owner only | No current download route |

The admin capabilities above are direct, declared administrative capabilities—not silent ownership overrides. M2-01 provides stable organization-unit IDs and a guarded master-data capability, but organization/unit scoping for other admin screens cannot be enforced yet because no user membership/scope relation exists. Until M2-02 introduces that relation, admin access remains institution-wide and no narrower scope is inferred from legacy free-text `nama_unit`.

## Object policy API

The central API provides:

- `canViewAuditAssignment()`;
- `canEditAuditeeSubmission()`;
- `canAssessAssignment()`;
- `canViewEvidence()`;
- `canManageOrganizationUnits()`;
- `canManageSpmiVersion()`;
- `canManageRtm()`;
- `canSubmitFollowUp()`;
- `canVerifyFollowUp()`.

Controllers use the corresponding scoped getters when they need data, such as `getViewableAssignment()`, `getAssessableAssignment()`, and `getViewableEvidence()`. Participant assignment and evidence queries include both object ID and current `auditee_id`/`auditor_id`.

User organization membership/scope, RTM, PIC membership, follow-up verification, lead Auditor, observer, and finalizer assignments do not exist in the current schema. Their target policy methods return `FALSE` for every role. This implements deny-by-default without inventing unresolved business rules BIZ-008 through BIZ-010.

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
- Auditee route aliases and the duplicate `auditee/Tugas` controller use the same object policy.
- Auditor route aliases and the `auditor/Penilaian` bridge inherit the same object policy.
- The legacy `Auditor::simpan_nilai()` mutation now requires POST and the assessable-assignment policy.
- Account photo/update always derives the target user from the authenticated session.

## Verification

Run:

```powershell
php tests/authorization_policy_regression.php
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
