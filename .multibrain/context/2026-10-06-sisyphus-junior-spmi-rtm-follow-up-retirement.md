# SPMI RTM Follow-up Retirement

Date: 2026-10-06

The active SPMI RTM follow-up workflow was removed without a migration or database mutation. `migrations/022_create_spmi_rtm_follow_ups.sql` and the `spmi_rtm_follow_ups` block in `database_schema.sql` remain unchanged, preserving deployment parity and historic rows.

Removed active artifacts: management routes, the follow-up controller/service/model/views/styles, sidebar entries, RTM decision `has_follow_up` join and controls, management dashboard metrics/overdue notification, PPEPP recap metrics, Tailwind scan source, and manuals that claimed an active feature. The pre-existing `User_model` RESTRICT-reference entry remains so historical follow-up rows continue to prevent unsafe referenced-user deletion.

Verification passed: PHP lint for every changed PHP file; `spmi_rtm_follow_ups_regression.php`, `spmi_rtm_regression.php`, `spmi_dashboard_regression.php`, `spmi_ppepp_recap_regression.php`, `sidebar_navigation_regression.php`, `spmi_ui_consistency_regression.php`, `m17_schema_regression.php`; and `git diff --check`.
