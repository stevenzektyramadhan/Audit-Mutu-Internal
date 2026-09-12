# SPMI Auditor Ownership Predicate Grouping

- Reproduced as `rere@ami.test` (user 10): the SPMI workspace listed assignments owned by user 3 despite the session and initial SQL owner predicate being correct.
- Cause: raw Query Builder `OR` clauses were not grouped, so SQL precedence let an assessment condition bypass `a.auditor_id`.
- Fixed list, cycle options, workspace attention count, and dashboard attention count by using Query Builder groups. All owner, cycle, submission, and current-version assessment constraints remain required.
- No assignment, assessment, session, schema, or other audit data changed.
- Verification: PHP syntax and SPMI workspace/auditee/audits regression scripts passed; rebuilt Docker app; `rere` sees no task rows or badges and gets 404 for assignment 11; `auditor@ami.test` still sees owned tasks and opens assignment 11.
