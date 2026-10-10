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
foreach (['trans_begin', 'version_for_update', 'standards_for_version_for_update', 'assignment_standard_auditors', 'standard_indicators', 'skor_audit_options()', "'evidence_policy' => \$indicator->evidence_policy", 'array_keys($rubric_options) !== [1, 2, 3, 4]', 'Standar SPMI belum memiliki indikator.', 'assignment_workspace_descendant_exists'] as $required) {
    spmi_audit_check(strpos($service, $required) !== FALSE, 'Assignment service contract missing: ' . $required);
}
foreach (['assignment_groups', 'isset($mapping[$standard_id])', 'array_unique($standard_auditor_ids)', 'assignment_by_standard_auditee', 'Standar SPMI sudah ditugaskan kepada auditee pada siklus ini.'] as $required) {
    spmi_audit_check(strpos($service, $required) !== FALSE, 'Grouped assignment distribution contract missing: ' . $required);
}
spmi_audit_check(strpos($service, "if (!isset(\$group['source_standard_ids']) || \$group['source_standard_ids'] === []) continue;") !== FALSE && strpos($service, "if (!is_array(\$group['source_standard_ids'])) return NULL;") !== FALSE && strpos($service, 'if (!$auditor_id) return NULL;') !== FALSE, 'Grouped assignment parser must ignore disabled unselected groups while requiring auditors for selected standards.');
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
spmi_audit_check(strpos($model, 'assignment_by_standard_auditee') !== FALSE && strpos($model, "'auditor_id' =>") === FALSE, 'Assignment duplicate lookup must ignore auditor and enforce one auditor per standard/auditee.');
spmi_audit_check(strpos($model, 'ORDER BY s.display_order ASC FOR UPDATE') !== FALSE, 'Locked version standard resolver must preserve standard display order.');
spmi_audit_check(strpos($form, 'name="source_version_id"') !== FALSE && strpos($form, "assignment_groups[' + index + '][source_standard_ids][]") !== FALSE && strpos($form, "assignment_groups[' + index + '][auditor_id]") !== FALSE, 'Assignment form must select a version and submit grouped standard/auditor mappings.');
spmi_audit_check(strpos($form, '$versions') !== FALSE && strpos($form, '$standards_by_version') !== FALSE, 'Assignment form must receive version-scoped standard data.');
spmi_audit_check(strpos($form, 'data-role-search') !== FALSE && strpos($form, 'type="search"') !== FALSE && strpos($form, 'data-role-search-status') !== FALSE && strpos($form, 'data-search="<?php echo html_escape(mb_strtolower($user->nama, \'UTF-8\')); ?>"') !== FALSE && strpos($form, '\'search\' => mb_strtolower($user->nama, \'UTF-8\')') !== FALSE, 'Assignment form must progressively enhance auditor and auditee selectors with escaped name search.');
spmi_audit_check(strpos($form, "querySelectorAll('[data-role-search]')") !== FALSE && strpos($form, "search.addEventListener('input'") !== FALSE && strpos($form, 'option.hidden =') !== FALSE, 'Assignment form must filter native role options locally from name search.');
spmi_audit_check(strpos($form, 'checkbox.addEventListener') !== FALSE && strpos($form, 'select.disabled = !checkbox.checked') !== FALSE, 'Assignment form must not require auditors for unselected standards.');
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

foreach (['pending_auditee_count', 'pending_auditor_count', 'aa.source_submission_version = s.version'] as $required) {
    spmi_audit_check(strpos($model, $required) !== FALSE, 'Overdue LPMPI model contract missing: ' . $required);
}
spmi_audit_check(strpos($model, 'submission_status') !== FALSE && strpos($model, 'assessment_status') !== FALSE, 'Overdue LPMPI detail must read current submission and assessment states.');
spmi_audit_check(strpos($cycle_index, "(string) \$cycle->end_date < date('Y-m-d')") !== FALSE && strpos($cycle_index, "\$cycle->state !== 'draft'") !== FALSE, 'Overdue LPMPI cycle summary must be informational for elapsed non-draft cycles only.');
spmi_audit_check(strpos($cycle_index, 'Terlambat auditee:') !== FALSE && strpos($cycle_index, 'Terlambat auditor:') !== FALSE, 'Overdue LPMPI cycle labels missing.');
spmi_audit_check(strpos($cycle_detail, "(string) \$cycle->end_date < date('Y-m-d')") !== FALSE && strpos($cycle_detail, 'Terlambat: submission belum dikirim') !== FALSE && strpos($cycle_detail, 'Terlambat: penilaian belum difinalisasi') !== FALSE, 'Overdue LPMPI assignment labels missing.');

fwrite(STDOUT, "SPMI audits regression checks passed.\n");
