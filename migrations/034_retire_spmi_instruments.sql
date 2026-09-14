-- Destructive preflight: apply only after confirming all versioned SPMI instrument,
-- assignment, workspace, assessment, and report records are disposable or empty.
-- This migration intentionally contains schema DDL only: no DML and no FK disabling.
ALTER TABLE `spmi_report_items`
    DROP COLUMN `question_code_snapshot`,
    DROP COLUMN `question_text_snapshot`;

ALTER TABLE `spmi_reports`
    DROP COLUMN `source_package_code_snapshot`,
    DROP COLUMN `source_package_title_snapshot`;

ALTER TABLE `spmi_audit_assignment_items`
    DROP FOREIGN KEY `fk_spmi_audit_assignment_items_question`,
    DROP INDEX `uq_spmi_audit_assignment_items_question`,
    DROP COLUMN `source_question_id`,
    DROP COLUMN `question_code`,
    DROP COLUMN `question_text`;

ALTER TABLE `spmi_audit_assignments`
    DROP FOREIGN KEY `fk_spmi_audit_assignments_package`,
    DROP INDEX `uq_spmi_audit_assignments_tuple`,
    DROP COLUMN `source_package_id`,
    DROP COLUMN `source_package_code`,
    DROP COLUMN `source_package_title`,
    DROP COLUMN `source_package_description`,
    ADD UNIQUE KEY `uq_spmi_audit_assignments_tuple` (`cycle_id`, `source_standard_id`, `auditor_id`, `auditee_id`);

DROP TABLE `spmi_instrument_rubrics`;
DROP TABLE `spmi_instrument_questions`;
DROP TABLE `spmi_instrument_packages`;
