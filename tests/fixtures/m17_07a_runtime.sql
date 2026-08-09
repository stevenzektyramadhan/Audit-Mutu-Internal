START TRANSACTION;

INSERT INTO `users` (`nama`, `email`, `password`, `role`, `nama_unit`, `jenis_unit`) VALUES
('M17-07A Super Admin', 'super-admin@m17-07a.test', '$2y$10$AUi3Erh.pKrVP2IMkn.rBeVa/i/n7yALM61aocLEPw/H0aEKh34om', 'super_admin', 'LPMPI Runtime', 'lembaga'),
('M17-07A Admin LPMPI', 'admin-lpmpi@m17-07a.test', '$2y$10$AUi3Erh.pKrVP2IMkn.rBeVa/i/n7yALM61aocLEPw/H0aEKh34om', 'admin_lpmpi', 'LPMPI Runtime', 'lembaga'),
('M17-07A Auditor A', 'auditor-a@m17-07a.test', '$2y$10$AUi3Erh.pKrVP2IMkn.rBeVa/i/n7yALM61aocLEPw/H0aEKh34om', 'auditor', 'Unit Runtime A', 'unit'),
('M17-07A Auditor B', 'auditor-b@m17-07a.test', '$2y$10$AUi3Erh.pKrVP2IMkn.rBeVa/i/n7yALM61aocLEPw/H0aEKh34om', 'auditor', 'Unit Runtime B', 'unit'),
('M17-07A Auditee A', 'auditee-a@m17-07a.test', '$2y$10$AUi3Erh.pKrVP2IMkn.rBeVa/i/n7yALM61aocLEPw/H0aEKh34om', 'auditee', 'Unit Runtime A', 'unit'),
('M17-07A Auditee B', 'auditee-b@m17-07a.test', '$2y$10$AUi3Erh.pKrVP2IMkn.rBeVa/i/n7yALM61aocLEPw/H0aEKh34om', 'auditee', 'Unit Runtime B', 'unit');

INSERT INTO `organization_units` (`parent_id`, `code`, `name`, `type`, `is_active`, `metadata_json`) VALUES
(NULL, 'M17R', 'M17-07A Runtime University', 'university', 1, '{}');
SET @root_unit := LAST_INSERT_ID();
INSERT INTO `organization_units` (`parent_id`, `code`, `name`, `type`, `is_active`, `metadata_json`) VALUES
(@root_unit, 'M17R-A', 'M17-07A Unit A', 'unit', 1, '{}'),
(@root_unit, 'M17R-B', 'M17-07A Unit B', 'unit', 1, '{}');
SET @unit_a := (SELECT `id` FROM `organization_units` WHERE `code` = 'M17R-A');
SET @unit_b := (SELECT `id` FROM `organization_units` WHERE `code` = 'M17R-B');

INSERT INTO `capabilities` (`code`, `label`, `description`) VALUES
('CAP_SPMI_RUNTIME', 'M17-07A runtime fixture', 'Synthetic capability for disposable M17-07A verification');
SET @capability_id := LAST_INSERT_ID();
INSERT INTO `role_capabilities` (`role`, `capability_id`) VALUES
('super_admin', @capability_id), ('admin_lpmpi', @capability_id), ('auditor', @capability_id), ('auditee', @capability_id);

SET @admin_id := (SELECT `id` FROM `users` WHERE `email` = 'admin-lpmpi@m17-07a.test');
SET @auditor_a_id := (SELECT `id` FROM `users` WHERE `email` = 'auditor-a@m17-07a.test');
SET @auditor_b_id := (SELECT `id` FROM `users` WHERE `email` = 'auditor-b@m17-07a.test');
SET @auditee_a_id := (SELECT `id` FROM `users` WHERE `email` = 'auditee-a@m17-07a.test');
SET @auditee_b_id := (SELECT `id` FROM `users` WHERE `email` = 'auditee-b@m17-07a.test');
INSERT INTO `user_unit_assignments` (`user_id`, `organization_unit_id`, `position_code`, `valid_from`, `valid_until`, `is_primary`) VALUES
(@auditor_a_id, @unit_a, 'auditor', '2026-01-01', NULL, 1),
(@auditee_a_id, @unit_a, 'auditee', '2026-01-01', NULL, 1),
(@auditor_b_id, @unit_b, 'auditor', '2026-01-01', NULL, 1),
(@auditee_b_id, @unit_b, 'auditee', '2026-01-01', NULL, 1);

INSERT INTO `spmi_versions` (`version_code`, `title`, `description`, `status`, `source_file_path`, `created_by`) VALUES
('M17R-V1', 'M17-07A Runtime Version', 'Disposable runtime fixture source version', 'active', NULL, @admin_id);
SET @version_id := LAST_INSERT_ID();
INSERT INTO `spmi_standards` (`version_id`, `standard_code`, `display_order`, `title`, `description`) VALUES
(@version_id, 'M17R-S1', 1, 'M17-07A Runtime Standard', 'Minimal standard for lifecycle verification');
SET @standard_id := LAST_INSERT_ID();
INSERT INTO `spmi_indicators` (`standard_id`, `indicator_code`, `indicator_type`, `title`, `scope_organization_unit_id`, `responsible_organization_unit_id`, `responsible_pic_name`, `evidence_requirement`) VALUES
(@standard_id, 'M17R-I1', 'IKU', 'M17-07A Runtime Indicator', @unit_a, @unit_a, 'M17-07A Fixture', 'Fixture evidence required');
SET @indicator_id := LAST_INSERT_ID();
INSERT INTO `spmi_indicator_targets` (`indicator_id`, `target_year`, `target_value`) VALUES (@indicator_id, 2026, '100');
INSERT INTO `spmi_instrument_packages` (`standard_id`, `package_code`, `display_order`, `title`, `description`) VALUES
(@standard_id, 'M17R-P1', 1, 'M17-07A Runtime Package', 'All evidence-policy fixtures');
SET @package_id := LAST_INSERT_ID();
INSERT INTO `spmi_instrument_questions` (`package_id`, `indicator_id`, `question_code`, `display_order`, `question_text`, `evidence_instruction`, `evidence_policy`) VALUES
(@package_id, @indicator_id, 'M17R-Q-NONE', 1, 'Fixture policy none', 'No evidence required', 'none'),
(@package_id, @indicator_id, 'M17R-Q-FILE', 2, 'Fixture policy file', 'File evidence required', 'file'),
(@package_id, @indicator_id, 'M17R-Q-URL', 3, 'Fixture policy url', 'URL evidence required', 'url'),
(@package_id, @indicator_id, 'M17R-Q-EITHER', 4, 'Fixture policy either', 'File or URL evidence required', 'either'),
(@package_id, @indicator_id, 'M17R-Q-BOTH', 5, 'Fixture policy both', 'File and URL evidence required', 'both');
INSERT INTO `spmi_instrument_rubrics` (`question_id`, `score`, `descriptor`)
SELECT question.`id`, scores.`score`, CONCAT('Fixture rubric ', scores.`score`)
FROM `spmi_instrument_questions` AS question
CROSS JOIN (SELECT 1 AS score UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4) AS scores
WHERE question.`package_id` = @package_id;

INSERT INTO `spmi_audit_cycles` (`cycle_code`, `title`, `description`, `start_date`, `end_date`, `state`, `created_by`) VALUES
('M17R-C1', 'M17-07A Runtime Cycle', 'Configured disposable lifecycle fixture', '2026-01-01', '2026-12-31', 'configured', @admin_id);
SET @cycle_id := LAST_INSERT_ID();
INSERT INTO `spmi_audit_assignments` (`cycle_id`, `source_package_id`, `auditor_id`, `auditee_id`, `created_by`, `source_version_id`, `source_version_code`, `source_version_title`, `source_standard_id`, `source_standard_code`, `source_standard_title`, `source_package_code`, `source_package_title`, `source_package_description`, `auditor_name`, `auditor_email`, `auditee_name`, `auditee_email`) VALUES
(@cycle_id, @package_id, @auditor_a_id, @auditee_a_id, @admin_id, @version_id, 'M17R-V1', 'M17-07A Runtime Version', @standard_id, 'M17R-S1', 'M17-07A Runtime Standard', 'M17R-P1', 'M17-07A Runtime Package', 'All evidence-policy fixtures', 'M17-07A Auditor A', 'auditor-a@m17-07a.test', 'M17-07A Auditee A', 'auditee-a@m17-07a.test'),
(@cycle_id, @package_id, @auditor_b_id, @auditee_b_id, @admin_id, @version_id, 'M17R-V1', 'M17-07A Runtime Version', @standard_id, 'M17R-S1', 'M17-07A Runtime Standard', 'M17R-P1', 'M17-07A Runtime Package', 'All evidence-policy fixtures', 'M17-07A Auditor B', 'auditor-b@m17-07a.test', 'M17-07A Auditee B', 'auditee-b@m17-07a.test');
SET @assignment_a_id := (SELECT `id` FROM `spmi_audit_assignments` WHERE `auditor_id` = @auditor_a_id AND `auditee_id` = @auditee_a_id);
SET @assignment_b_id := (SELECT `id` FROM `spmi_audit_assignments` WHERE `auditor_id` = @auditor_b_id AND `auditee_id` = @auditee_b_id);
INSERT INTO `spmi_audit_assignment_items` (`assignment_id`, `source_question_id`, `source_indicator_id`, `display_order`, `question_code`, `question_text`, `evidence_instruction`, `evidence_policy`, `indicator_code`, `indicator_title`)
SELECT assignments.assignment_id, question.`id`, @indicator_id, question.`display_order`, question.`question_code`, question.`question_text`, question.`evidence_instruction`, question.`evidence_policy`, 'M17R-I1', 'M17-07A Runtime Indicator'
FROM (SELECT @assignment_a_id AS assignment_id UNION ALL SELECT @assignment_b_id) AS assignments
CROSS JOIN `spmi_instrument_questions` AS question
WHERE question.`package_id` = @package_id;
INSERT INTO `spmi_audit_assignment_item_rubrics` (`assignment_item_id`, `score`, `descriptor`)
SELECT assignment_item.`id`, rubric.`score`, rubric.`descriptor`
FROM `spmi_audit_assignment_items` AS assignment_item
JOIN `spmi_instrument_rubrics` AS rubric ON rubric.`question_id` = assignment_item.`source_question_id`
WHERE assignment_item.`assignment_id` IN (@assignment_a_id, @assignment_b_id);

COMMIT;
