<?php

function retirement_source($path)
{
    $source = file_get_contents(dirname(__DIR__) . DIRECTORY_SEPARATOR . $path);
    if ($source === FALSE) throw new RuntimeException('Tidak dapat membaca ' . $path);
    return $source;
}

function retirement_check($condition, $message)
{
    if (!$condition) throw new RuntimeException($message);
}

function retirement_table($schema, $table)
{
    if (!preg_match('/CREATE TABLE IF NOT EXISTS `' . preg_quote($table, '/') . '` \\((.*?)\\n\\) ENGINE=/s', $schema, $match)) throw new RuntimeException('Tabel bootstrap tidak ditemukan: ' . $table);
    return $match[1];
}

$schema = retirement_source('database_schema.sql');
$migration = retirement_source('migrations/034_retire_spmi_instruments.sql');
$routes = retirement_source('application/config/routes.php');
$sidebar = retirement_source('application/views/layouts/sidebar.php');
$controller = retirement_source('application/controllers/lpmpi/Spmi_audits.php');
$service = retirement_source('application/services/Spmi_audits_service.php');
$model = retirement_source('application/models/Spmi_audits_model.php');

foreach (['spmi_instrument_packages', 'spmi_instrument_questions', 'spmi_instrument_rubrics'] as $retired) {
    retirement_check(strpos($schema, 'CREATE TABLE IF NOT EXISTS `' . $retired . '`') === FALSE, 'Bootstrap schema still exposes retired SPMI instrument table: ' . $retired);
}
foreach (['spmi_audit_assignments', 'spmi_audit_assignment_items', 'spmi_reports', 'spmi_report_items'] as $table) {
    foreach (['source_package_id', 'source_question_id', 'question_code_snapshot', 'question_text_snapshot'] as $retired) {
        retirement_check(strpos(retirement_table($schema, $table), '`' . $retired . '`') === FALSE, 'Bootstrap schema still exposes retired SPMI field: ' . $table . '.' . $retired);
    }
}
foreach (['DROP TABLE `spmi_instrument_rubrics`', 'DROP TABLE `spmi_instrument_questions`', 'DROP TABLE `spmi_instrument_packages`', 'DROP COLUMN `source_package_id`', 'DROP COLUMN `source_question_id`', 'DROP COLUMN `question_code_snapshot`', 'DROP COLUMN `question_text_snapshot`', 'source_standard_id'] as $required) {
    retirement_check(strpos($migration, $required) !== FALSE, 'Migration 034 retirement contract missing: ' . $required);
}
retirement_check(strpos($migration, 'Destructive preflight:') !== FALSE, 'Migration 034 must retain destructive preflight guidance.');
retirement_check(preg_match('/\b(INSERT|UPDATE|DELETE)\b/i', $migration) === 0, 'Migration 034 must contain DDL only.');
retirement_check(stripos($migration, 'foreign_key_checks') === FALSE, 'Migration 034 must not disable foreign keys.');
foreach (['spmi-instruments', 'Spmi_instruments', 'spmi_instruments'] as $retired) {
    retirement_check(strpos($routes . $sidebar, $retired) === FALSE, 'Route or sidebar still exposes retired instrument surface: ' . $retired);
}
foreach (['source_standard_id', 'versions()', 'standards_by_version()', 'version_for_update', 'standards_for_version_for_update', 'standard_indicators', "'evidence_policy' => \$indicator->evidence_policy", 'skor_audit_options()', 'Standar SPMI belum memiliki indikator.'] as $required) {
    retirement_check(strpos($controller . $service . $model, $required) !== FALSE, 'Indicator assignment contract missing: ' . $required);
}
foreach (['package_for_update', 'package_questions', 'source_package_id', 'source_question_id', 'spmi_instrument_'] as $retired) {
    retirement_check(strpos($controller . $service . $model, $retired) === FALSE, 'Assignment source still depends on retired package behavior: ' . $retired);
}
retirement_check(!is_file(dirname(__DIR__) . '/application/controllers/lpmpi/Spmi_instruments.php'), 'Retired instrument controller still exists.');
retirement_check(!is_file(dirname(__DIR__) . '/application/services/Spmi_instruments_service.php'), 'Retired instrument service still exists.');
retirement_check(!is_file(dirname(__DIR__) . '/application/models/Spmi_instruments_model.php'), 'Retired instrument model still exists.');

fwrite(STDOUT, "SPMI instrument retirement regression checks passed.\n");
