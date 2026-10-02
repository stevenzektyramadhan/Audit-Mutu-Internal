# PPEPP Stage Lock

- Date: 2026-10-02
- Scope: `form.php`, `Spmi_ppepp_documents_service.php`, and `spmi_ppepp_documents_regression.php`.
- Decision: create/edit forms submit the selected stage through a hidden field and render its configured label read-only; categories remain rendered only from the selected stage.
- Integrity: update locks the persisted document before rejecting a valid but different submitted stage, rolls back, and removes a newly saved replacement upload. Matching-stage updates and create validation are unchanged.
- Verification: run PHP lint for the three PHP files, `php tests/spmi_ppepp_documents_regression.php`, and `git diff --check`; no database mutation or Docker.
