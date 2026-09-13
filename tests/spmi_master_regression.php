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
spmi_master_check(strpos($controller, 'extends Admin_Lpmpi_Controller') !== FALSE, 'M5 controller base class missing.');
spmi_master_check(strpos($controller, "redirect('lpmpi/spmi-standards')") !== FALSE, 'M5 index must redirect to canonical standards page.');
spmi_master_check(strpos($routes, '$route[\'lpmpi/spmi-master\'] = \'lpmpi/Spmi_master/index\';') !== FALSE, 'M5 route missing.');
spmi_master_check(strpos($sidebar, "'key' => 'spmi_master'") === FALSE, 'M5 sidebar entry must be removed.');
spmi_master_check(strpos($preview, 'html_escape') !== FALSE, 'M5 preview must escape output.');

fwrite(STDOUT, "SPMI master regression checks passed.\n");
