<?php
$root = dirname(__DIR__);
function spmi_instrument_source($path) { $value = file_get_contents(dirname(__DIR__) . DIRECTORY_SEPARATOR . $path); if ($value === FALSE) throw new RuntimeException('Tidak dapat membaca ' . $path); return $value; }
function spmi_instrument_check($condition, $message) { if (!$condition) throw new RuntimeException($message); }

$migration = spmi_instrument_source('migrations/016_create_spmi_instrument_packages.sql');
$schema = spmi_instrument_source('database_schema.sql');
$model = spmi_instrument_source('application/models/Spmi_instruments_model.php');
$service = spmi_instrument_source('application/services/Spmi_instruments_service.php');
$controller = spmi_instrument_source('application/controllers/lpmpi/Spmi_instruments.php');
$routes = spmi_instrument_source('application/config/routes.php');
$sidebar = spmi_instrument_source('application/views/layouts/sidebar.php');
$views = [];
foreach (['index', 'package_form', 'package_detail', 'question_form', 'question_detail', 'rubric_form'] as $view) $views[] = spmi_instrument_source('application/views/lpmpi/spmi_instruments/' . $view . '.php');
foreach (['spmi_instrument_packages', 'spmi_instrument_questions', 'spmi_instrument_rubrics', 'standard_id', 'package_id', 'indicator_id', 'question_id', 'TINYINT UNSIGNED', 'UNIQUE KEY', 'ON DELETE RESTRICT'] as $literal) spmi_instrument_check(strpos($migration, $literal) !== FALSE, 'M6 migration contract missing: ' . $literal);
foreach (['current parity migration 001-018', 'spmi_instrument_packages', 'spmi_instrument_questions', 'spmi_instrument_rubrics', 'uq_spmi_instrument_packages_standard_code', 'uq_spmi_instrument_questions_package_order', 'uq_spmi_instrument_rubrics_question_score'] as $literal) spmi_instrument_check(strpos($schema, $literal) !== FALSE, 'M6 schema parity missing: ' . $literal);
spmi_instrument_check(strpos($migration, 'INSERT') === FALSE && strpos($migration, '`standar`') === FALSE && strpos($migration, '`pertanyaan`') === FALSE, 'M6 migration must be additive and seed-free.');
foreach (['FOR UPDATE', 'spmi_versions', 'get_all_packages', 'find_indicator', 'find_question_by_code', 'find_question_by_order', 'count_questions', 'count_rubrics'] as $literal) spmi_instrument_check(strpos($model, $literal) !== FALSE, 'M6 model contract missing: ' . $literal);
foreach (['trans_begin', 'find_version', 'is_mutable', "['draft', 'review']", 'standard_id', '1', '4', 'find_package_by_code', 'find_package_by_order', 'find_question_by_code', 'find_question_by_order', 'count_questions', 'count_rubrics', 'Paket tidak dapat dihapus karena masih memiliki pertanyaan.', 'Pertanyaan tidak dapat dihapus karena masih memiliki rubrik.', 'rollback'] as $literal) spmi_instrument_check(strpos($service, $literal) !== FALSE, 'M6 service contract missing: ' . $literal);
foreach (['extends Admin_Lpmpi_Controller', 'form_validation', "method(TRUE) !== 'POST'", 'show_error', 'package_create', 'package_detail', 'question_create', 'question_detail', 'rubric_create', 'rubric_edit'] as $literal) spmi_instrument_check(strpos($controller, $literal) !== FALSE, 'M6 controller contract missing: ' . $literal);
foreach (['lpmpi/spmi-instruments', 'package/create', 'package/store', 'package/detail', 'package/update', 'question/create', 'question/store', 'question/detail', 'rubric/create', 'rubric/store', 'rubric/update'] as $literal) spmi_instrument_check(strpos($routes, $literal) !== FALSE, 'M6 route missing: ' . $literal);
spmi_instrument_check(substr_count($sidebar, "'key' => 'spmi_instruments', 'label' => 'Instrumen Audit SPMI', 'icon' => 'fa-clipboard-check', 'url' => 'lpmpi/spmi-instruments', 'group' => 'Management'") === 2, 'M6 sidebar entry must exist only for management roles.');
foreach ($views as $view) { spmi_instrument_check(strpos($view, 'html_escape') !== FALSE, 'M6 view must escape output.'); spmi_instrument_check(strpos($view, "include APPPATH . 'views/layouts/header.php'") !== FALSE, 'M6 view header missing.'); }
spmi_instrument_check(strpos($views[1], 'form_open(') !== FALSE && strpos($views[3], 'form_open(') !== FALSE && strpos($views[5], 'form_open(') !== FALSE, 'M6 forms missing.');
spmi_instrument_check(strpos($views[2], "form_open('lpmpi/spmi-instruments/package/delete/") !== FALSE && strpos($views[2], 'Hapus paket') !== FALSE, 'M6 package delete form missing.');
spmi_instrument_check(strpos($views[4], "form_open('lpmpi/spmi-instruments/question/delete/") !== FALSE && strpos($views[4], 'Hapus pertanyaan') !== FALSE, 'M6 question delete form missing.');
spmi_instrument_check(strpos($views[4], "form_open('lpmpi/spmi-instruments/rubric/delete/") !== FALSE && strpos($views[4], 'Hapus</button>') !== FALSE, 'M6 rubric delete form missing.');
spmi_instrument_check(substr_count($views[2], '$mutable') >= 2 && substr_count($views[4], '$mutable') >= 3, 'M6 delete controls must remain mutable-state-only.');
spmi_instrument_check(strpos($service, "delete('spmi_instrument_questions'") === FALSE, 'M6 package delete must not cascade questions.');
spmi_instrument_check(strpos($service, "delete('spmi_instrument_rubrics'") === FALSE, 'M6 question delete must not cascade rubrics.');
spmi_instrument_check(strpos($views[2], 'Hanya-baca') !== FALSE && strpos($views[4], 'Belum ada rubrik') !== FALSE, 'M6 immutable and completeness states missing.');

fwrite(STDOUT, "SPMI instruments regression checks passed.\n");
