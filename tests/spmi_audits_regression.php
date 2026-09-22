<?php

function spmi_audit_source($path)
{
    $value = file_get_contents(dirname(__DIR__) . DIRECTORY_SEPARATOR . $path);
    if ($value === FALSE) throw new RuntimeException('Tidak dapat membaca ' . $path);
    return $value;
}

function spmi_audit_check($condition, $message)
{
    if (!$condition) throw new RuntimeException($message);
}

function spmi_audit_table($schema, $table)
{
    if (!preg_match('/CREATE TABLE IF NOT EXISTS `' . preg_quote($table, '/') . '` \\((.*?)\\n\\) ENGINE=/s', $schema, $match)) throw new RuntimeException('Tabel bootstrap tidak ditemukan: ' . $table);
    return $match[1];
}

$schema = spmi_audit_source('database_schema.sql');
$migration = spmi_audit_source('migrations/034_retire_spmi_instruments.sql');
$model = spmi_audit_source('application/models/Spmi_audits_model.php');
$service = spmi_audit_source('application/services/Spmi_audits_service.php');
$controller = spmi_audit_source('application/controllers/lpmpi/Spmi_audits.php');
$form = spmi_audit_source('application/views/lpmpi/spmi_audits/assignment_form.php');
$detail = spmi_audit_source('application/views/lpmpi/spmi_audits/assignment_detail.php');
$cycle_form = spmi_audit_source('application/views/lpmpi/spmi_audits/cycle_form.php');
$cycle_index = spmi_audit_source('application/views/lpmpi/spmi_audits/index.php');
$cycle_detail = spmi_audit_source('application/views/lpmpi/spmi_audits/cycle_detail.php');

foreach (['spmi_audit_cycles', 'spmi_audit_assignments', 'spmi_audit_assignment_items', 'spmi_audit_assignment_item_rubrics', 'source_standard_id', 'source_indicator_id', 'evidence_policy'] as $required) {
    spmi_audit_check(strpos($schema, $required) !== FALSE, 'Indicator assignment schema missing: ' . $required);
}
foreach (['spmi_audit_assignments', 'spmi_audit_assignment_items'] as $table) {
    foreach (['source_package_id', 'source_question_id', 'question_code', 'question_text'] as $retired) {
        spmi_audit_check(strpos(spmi_audit_table($schema, $table), '`' . $retired . '`') === FALSE, 'Bootstrap schema retains retired assignment field: ' . $table . '.' . $retired);
    }
}
foreach (['trans_begin', 'version_for_update', 'standards_for_version_for_update', 'source_standard_ids', 'standard_indicators', 'skor_audit_options()', "'evidence_policy' => \$indicator->evidence_policy", 'array_keys($rubric_options) !== [1, 2, 3, 4]', 'Standar SPMI belum memiliki indikator.', 'assignment_workspace_descendant_exists'] as $required) {
    spmi_audit_check(strpos($service, $required) !== FALSE, 'Assignment service contract missing: ' . $required);
}
foreach (['packages()', 'package_for_update', 'package_questions', 'source_package_id', 'source_question_id'] as $retired) {
    spmi_audit_check(strpos($service . $model . $controller, $retired) === FALSE, 'Assignment flow retains package dependency: ' . $retired);
}
foreach (['source_version_id', 'versions()', 'standards_by_version()', 'required|integer', "method(TRUE) !== 'POST'"] as $required) {
    spmi_audit_check(strpos($controller, $required) !== FALSE, 'Assignment controller contract missing: ' . $required);
}
spmi_audit_check(strpos($controller, "set_rules('source_standard_id'") === FALSE, 'Assignment controller must not require one authoritative standard ID.');
foreach (['versions()', 'standards_by_version()', 'standards_for_version_for_update'] as $required) {
    spmi_audit_check(strpos($model, $required) !== FALSE, 'Assignment model version scope contract missing: ' . $required);
}
spmi_audit_check(strpos($model, 'ORDER BY s.display_order ASC FOR UPDATE') !== FALSE, 'Locked version standard resolver must preserve standard display order.');
spmi_audit_check(strpos($form, 'name="source_version_id"') !== FALSE && strpos($form, "checkbox.name = 'source_standard_ids[]'") !== FALSE, 'Assignment form must select a version and submit an optional standard subset.');
spmi_audit_check(strpos($form, '$versions') !== FALSE && strpos($form, '$standards_by_version') !== FALSE, 'Assignment form must receive version-scoped standard data.');
spmi_audit_check(strpos($form, 'source_package') === FALSE, 'Assignment form must not expose a retired package picker.');
spmi_audit_check(strpos($detail, 'source_standard_code') !== FALSE && strpos($detail, 'evidence_instruction') !== FALSE, 'Assignment detail must render standard and indicator evidence snapshots.');
spmi_audit_check(strpos($detail, 'source_package') === FALSE && strpos($detail, 'question_text') === FALSE, 'Assignment detail must not render package/question snapshots.');
spmi_audit_check(strpos($migration, 'DROP FOREIGN KEY `fk_spmi_audit_assignments_package`') !== FALSE && strpos($migration, 'DROP FOREIGN KEY `fk_spmi_audit_assignment_items_question`') !== FALSE, 'Migration 034 must remove assignment package/question FKs.');
spmi_audit_check(strpos($controller, "set_rules('academic_year'") !== FALSE, 'Annual cycle controller must retain required academic year validation.');
spmi_audit_check(strpos($controller, "set_rules('semester'") === FALSE, 'Annual cycle controller must not require semester.');
spmi_audit_check(strpos($service, "'semester' =>") === FALSE && strpos($service, "in_array(\$data['semester']") === FALSE, 'Annual cycle service must not persist or validate semester.');
spmi_audit_check(strpos($cycle_form, 'name="academic_year"') !== FALSE, 'Annual cycle form must retain academic year input.');
spmi_audit_check(strpos($cycle_form, 'name="semester"') === FALSE && strpos($cycle_form, '>Semester<') === FALSE, 'Annual cycle form must not render semester input.');
spmi_audit_check(strpos($cycle_index, '$academic_year') !== FALSE && strpos($cycle_index, '$semester') === FALSE && strpos($cycle_index, 'Semester') === FALSE, 'Annual cycle index must render academic year without semester.');
spmi_audit_check(strpos($cycle_detail, '$academic_year') !== FALSE && strpos($cycle_detail, '$semester') === FALSE && strpos($cycle_detail, 'Semester') === FALSE, 'Annual cycle detail must render academic year without semester.');

fwrite(STDOUT, "SPMI audits regression checks passed.\n");
