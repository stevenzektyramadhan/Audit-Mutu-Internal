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
foreach (['index', 'package_form', 'package_detail', 'question_form', 'question_detail'] as $view) $views[] = spmi_instrument_source('application/views/lpmpi/spmi_instruments/' . $view . '.php');
foreach (['spmi_instrument_packages', 'spmi_instrument_questions', 'spmi_instrument_rubrics', 'standard_id', 'package_id', 'indicator_id', 'question_id', 'TINYINT UNSIGNED', 'UNIQUE KEY', 'ON DELETE RESTRICT'] as $literal) spmi_instrument_check(strpos($migration, $literal) !== FALSE, 'M6 migration contract missing: ' . $literal);
foreach (['current parity migration 001-018', 'spmi_instrument_packages', 'spmi_instrument_questions', 'spmi_instrument_rubrics', 'uq_spmi_instrument_packages_standard_code', 'uq_spmi_instrument_questions_package_order', 'uq_spmi_instrument_rubrics_question_score'] as $literal) spmi_instrument_check(strpos($schema, $literal) !== FALSE, 'M6 schema parity missing: ' . $literal);
spmi_instrument_check(strpos($migration, 'INSERT') === FALSE && strpos($migration, '`standar`') === FALSE && strpos($migration, '`pertanyaan`') === FALSE, 'M6 migration must be additive and seed-free.');
foreach (['FOR UPDATE', 'spmi_versions', 'get_all_packages', 'find_indicator', 'find_question_by_code', 'find_question_by_order', 'count_questions', 'count_rubrics'] as $literal) spmi_instrument_check(strpos($model, $literal) !== FALSE, 'M6 model contract missing: ' . $literal);
foreach (['trans_begin', 'find_version', 'is_mutable', "['draft', 'review']", 'standard_id', 'find_package_by_code', 'find_package_by_order', 'find_question_by_code', 'find_question_by_order', 'count_questions', 'count_rubrics', 'Paket tidak dapat dihapus karena masih memiliki pertanyaan.', 'Pertanyaan tidak dapat dihapus karena masih memiliki rubrik.', 'rollback'] as $literal) spmi_instrument_check(strpos($service, $literal) !== FALSE, 'M6 service contract missing: ' . $literal);
foreach (['extends Admin_Lpmpi_Controller', 'form_validation', "method(TRUE) !== 'POST'", 'show_error', 'package_create', 'package_detail', 'question_create', 'question_detail'] as $literal) spmi_instrument_check(strpos($controller, $literal) !== FALSE, 'M6 controller contract missing: ' . $literal);
foreach (['lpmpi/spmi-instruments', 'package/create', 'package/store', 'package/detail', 'package/update', 'question/create', 'question/store', 'question/detail'] as $literal) spmi_instrument_check(strpos($routes, $literal) !== FALSE, 'M6 route missing: ' . $literal);
spmi_instrument_check(substr_count($sidebar, "'key' => 'spmi_instruments', 'label' => 'Instrumen Audit SPMI', 'icon' => 'fa-clipboard-check', 'url' => 'lpmpi/spmi-instruments', 'group' => 'Management'") === 2, 'M6 sidebar entry must exist only for management roles.');
foreach ($views as $view) { spmi_instrument_check(strpos($view, 'html_escape') !== FALSE, 'M6 view must escape output.'); spmi_instrument_check(strpos($view, "include APPPATH . 'views/layouts/header.php'") !== FALSE, 'M6 view header missing.'); }
spmi_instrument_check(strpos($views[1], 'form_open(') !== FALSE && strpos($views[3], 'form_open(') !== FALSE, 'M6 forms missing.');
spmi_instrument_check(strpos($views[2], "form_open('lpmpi/spmi-instruments/package/delete/") !== FALSE && strpos($views[2], 'Hapus paket') !== FALSE, 'M6 package delete form missing.');
spmi_instrument_check(strpos($views[4], "form_open('lpmpi/spmi-instruments/question/delete/") !== FALSE && strpos($views[4], 'Hapus pertanyaan') !== FALSE, 'M6 question delete form missing.');
spmi_instrument_check(substr_count($views[2], '$mutable') >= 2 && substr_count($views[4], '$mutable') >= 2, 'M6 delete controls must remain mutable-state-only.');
spmi_instrument_check(strpos($service, "delete('spmi_instrument_questions'") === FALSE, 'M6 package delete must not cascade questions.');
spmi_instrument_check(strpos($service, "delete('spmi_instrument_rubrics'") === FALSE, 'M6 question delete must not cascade rubrics.');
spmi_instrument_check(strpos($views[2], 'Hanya-baca') !== FALSE && strpos($views[4], 'skala global skor audit 1–4') !== FALSE, 'M6 immutable and global scale notice missing.');
foreach (['rubric_create', 'rubric_store', 'rubric_edit', 'rubric_update', 'rubric_delete', 'set_rubric_rules'] as $literal) spmi_instrument_check(strpos($controller, $literal) === FALSE, 'Rubric authoring controller surface must be retired: ' . $literal);
foreach (['create_rubric', 'update_rubric', 'delete_rubric', 'rubric_write', 'find_rubric', 'find_question_for_rubric'] as $literal) spmi_instrument_check(strpos($service . $model, $literal) === FALSE, 'Rubric authoring service/model surface must be retired: ' . $literal);
foreach (['lpmpi/spmi-instruments/rubric/', 'rubric/create', 'rubric/store', 'rubric/update', 'rubric/delete'] as $literal) spmi_instrument_check(strpos($routes . $views[4], $literal) === FALSE, 'Rubric authoring route/view surface must be retired: ' . $literal);

// V2 M17-02 red phase: static CRUD wiring only. Runtime UI/POST authorization remains M17-07 verification.
spmi_instrument_check(strpos($controller, "set_rules('evidence_policy', 'Kebijakan bukti', 'required|in_list[none,file,url,either,both]'") !== FALSE, 'M17-02 controller must require evidence_policy in the closed five-value allowlist.');
foreach (['none', 'file', 'url', 'either', 'both'] as $policy) spmi_instrument_check(strpos($service, "'" . $policy . "'") !== FALSE, 'M17-02 service evidence_policy allowlist missing: ' . $policy);
spmi_instrument_check(strpos($service, 'isset($data[\'evidence_policy\'])') !== FALSE && strpos($service, '\'evidence_policy\' => $evidence_policy') !== FALSE, 'M17-02 service must independently default/validate and persist evidence_policy.');
spmi_instrument_check(strpos($views[3], 'name="evidence_policy"') !== FALSE && strpos($views[3], '<select') !== FALSE, 'M17-02 question form must expose evidence_policy as a controlled select.');
foreach (['value="none"', 'value="file"', 'value="url"', 'value="either"', 'value="both"'] as $literal) spmi_instrument_check(strpos($views[3], $literal) !== FALSE, 'M17-02 question form evidence_policy option missing: ' . $literal);
spmi_instrument_check(strpos($views[3], "evidence_policy : 'none'") !== FALSE, 'M17-02 question form must default missing/new evidence_policy to none.');
spmi_instrument_check(strpos($views[4], 'evidence_policy') !== FALSE && strpos($views[4], 'html_escape($question->evidence_policy') !== FALSE, 'M17-02 question detail must render saved evidence_policy safely.');

fwrite(STDOUT, "SPMI instruments regression checks passed.\n");
