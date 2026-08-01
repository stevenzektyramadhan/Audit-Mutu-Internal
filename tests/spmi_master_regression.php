<?php
$root = dirname(__DIR__);
function spmi_master_source($path) { $value = file_get_contents(dirname(__DIR__) . DIRECTORY_SEPARATOR . $path); if ($value === FALSE) throw new RuntimeException('Tidak dapat membaca ' . $path); return $value; }
function spmi_master_check($condition, $message) { if (!$condition) throw new RuntimeException($message); }

$service = spmi_master_source('application/services/Spmi_master_service.php');
$controller = spmi_master_source('application/controllers/lpmpi/Spmi_master.php');
$model = spmi_master_source('application/models/Spmi_indicators_model.php');
$standards_model = spmi_master_source('application/models/Spmi_standards_model.php');
$routes = spmi_master_source('application/config/routes.php');
$sidebar = spmi_master_source('application/views/layouts/sidebar.php');
$index = spmi_master_source('application/views/lpmpi/spmi_master/index.php');
$preview = spmi_master_source('application/views/lpmpi/spmi_master/import_preview.php');

$headers = ['Standard Code', 'Standard Order', 'Standard Title', 'Standard Description', 'Indicator Code', 'Indicator Type', 'Indicator Title', 'Scope Unit Code', 'Responsible Unit Code', 'Responsible PIC', 'Evidence Requirement', 'Target Year', 'Target Value'];
foreach ($headers as $header) spmi_master_check(strpos($service, $header) !== FALSE, 'XLSX header missing: ' . $header);
foreach (['SPMI Master', 'formula', 'dataOnly', '10000', '13', 'trans_begin', 'trans_commit', 'trans_rollback', 'draft', 'review', 'upsert', 'has_formula', 'continue', 'rollback_result'] as $literal) spmi_master_check(stripos($service, $literal) !== FALSE, 'M5 service contract missing: ' . $literal);
foreach (['standards_seen', 'validate_standard_metadata', 'strtoupper($v[0])', 'Data standar berulang bertentangan'] as $literal) spmi_master_check(stripos($service, $literal) !== FALSE, 'M5 standard metadata contract missing: ' . $literal);
spmi_master_check(strpos($service, 'trans_start') === FALSE, 'M5 confirm must not use trans_start.');
spmi_master_check(strpos($service, 'trans_commit();') !== FALSE && strpos($service, 'trans_rollback();') !== FALSE, 'M5 confirm atomic transaction guard missing.');
spmi_master_check(strpos($service, '$seen[$values[0] . \'|\' . $values[4]]') === FALSE, 'M5 seen composite key must not use raw workbook casing.');
spmi_master_check(strpos($service, '$seen[strtoupper($values[0]) . \'|\' . strtoupper($values[4])]') !== FALSE, 'M5 seen composite key must use normalized uppercase codes.');
spmi_master_check(stripos($service . $index, 'no-delete') !== FALSE, 'M5 additive/no-delete contract missing.');
spmi_master_check(stripos($service . $model . $standards_model, 'FOR UPDATE') !== FALSE, 'M5 version/row lock contract missing.');
foreach (['TYPE_STRING', 'is_uploaded_file', 'UPLOAD_ERR_OK', '2 * 1024 * 1024', 'sha256', '1800', 'rename', 'log_message', 'Import Master SPMI gagal', 'clear_preview', '0700'] as $literal) spmi_master_check(stripos($controller, $literal) !== FALSE, 'M5 controller contract missing: ' . $literal);
foreach (['purge_expired_artifacts', 'spmi_master_preview_*.json', 'spmi_master_claim_*.json', 'filemtime', 'GLOB_NOSORT', 'basename($current[\'basename\'])', 'time() - 1800'] as $literal) spmi_master_check(stripos($controller, $literal) !== FALSE, 'M5 artifact cleanup contract missing: ' . $literal);
spmi_master_check(substr_count($controller, '$this->purge_expired_artifacts();') === 3, 'M5 cleanup must run before preview, confirm, and cancel.');
foreach (['extends Admin_Lpmpi_Controller', 'require_post', 'confirm', 'template', 'export'] as $literal) spmi_master_check(strpos($controller, $literal) !== FALSE, 'M5 controller contract missing: ' . $literal);
spmi_master_check(strpos($index, 'form_open_multipart(') !== FALSE && strpos($preview, 'form_open(') !== FALSE, 'M5 forms missing.');
foreach (['find_standard_by_version_code', 'upsert_standard', 'find_indicator_by_standard_code', 'upsert_indicator', 'find_target_by_indicator_year', 'upsert_target'] as $literal) spmi_master_check(strpos($standards_model . $model, $literal) !== FALSE, 'M5 model helper missing: ' . $literal);
foreach (['lpmpi/spmi-master', 'template/(:num)', 'preview/(:num)', 'confirm/(:num)', 'cancel/(:num)', 'export/(:num)'] as $literal) spmi_master_check(strpos($routes, $literal) !== FALSE, 'M5 route missing: ' . $literal);
spmi_master_check(substr_count($sidebar, "'key' => 'spmi_master', 'label' => 'Import/Export Master SPMI', 'icon' => 'fa-file-excel', 'url' => 'lpmpi/spmi-master', 'group' => 'Management'") === 2, 'M5 sidebar entry must exist only for management roles.');
foreach ([$index, $preview] as $view) { spmi_master_check(strpos($view, 'html_escape') !== FALSE, 'M5 view must escape output.'); spmi_master_check(strpos($view, "include APPPATH . 'views/layouts/header.php'") !== FALSE, 'M5 view header missing.'); }
spmi_master_check(strpos($index, 'Hanya-baca') !== FALSE && strpos($index, 'Belum ada versi') !== FALSE && strpos($index, 'form_open_multipart') !== FALSE, 'M5 index states/form missing.');
spmi_master_check(strpos($preview, 'valid') !== FALSE && strpos($preview, 'errors') !== FALSE && strpos($preview, 'form_open(\'lpmpi/spmi-master/cancel/') !== FALSE && strpos($preview, 'Batal') !== FALSE, 'M5 preview contract missing.');

fwrite(STDOUT, "SPMI master regression checks passed.\n");
