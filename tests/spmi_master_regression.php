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

$headers = ['Standard Code', 'Standard Order', 'Standard Title', 'Standard Description', 'Indicator Code', 'Indicator Type', 'Indicator Title', 'Scope Unit Code', 'Responsible Unit Code', 'Responsible PIC', 'Evidence Requirement', 'Target Year', 'Target Value', 'Evidence Policy'];
foreach ($headers as $header) spmi_master_check(strpos($service, $header) !== FALSE, 'XLSX header missing: ' . $header);
foreach (['SPMI Master', 'formula', 'dataOnly', '10000', '14', "'N'", 'A1:N1', 'trans_begin', 'trans_commit', 'trans_rollback', 'draft', 'review', 'upsert', 'has_formula', 'continue', 'rollback_result', 'evidence_policy', 'Kebijakan bukti tidak valid', 'strtolower($v[13])'] as $literal) spmi_master_check(stripos($service, $literal) !== FALSE, 'M5 service contract missing: ' . $literal);
foreach (['standards_seen', 'validate_standard_metadata', 'strtoupper($v[0])', 'Data standar berulang bertentangan'] as $literal) spmi_master_check(stripos($service, $literal) !== FALSE, 'M5 standard metadata contract missing: ' . $literal);
foreach (['standard_orders_seen', 'Urutan standar digunakan oleh Standard Code lain', 'find_standard_by_version_order', 'Kode atau urutan standar sudah digunakan.', 'db_debug = FALSE'] as $literal) spmi_master_check(stripos($service . $standards_model, $literal) !== FALSE, 'M5 standard-order conflict guard missing: ' . $literal);
foreach (['active_unit_codes', 'Kode unit organisasi aktif tidak ditemukan:', 'validate_units'] as $literal) spmi_master_check(stripos($service, $literal) !== FALSE, 'M5 preview must reject unavailable organization-unit codes: ' . $literal);
spmi_master_check(strpos($service, "'Kode unit organisasi aktif tidak ditemukan: ' . strtoupper(") !== FALSE, 'M5 unavailable organization-unit error must identify the invalid code.');
spmi_master_check(strpos($service, 'trans_start') === FALSE, 'M5 confirm must not use trans_start.');
spmi_master_check(strpos($service, 'trans_commit();') !== FALSE && strpos($service, 'trans_rollback();') !== FALSE, 'M5 confirm atomic transaction guard missing.');
spmi_master_check(strpos($service, '$seen[$values[0] . \'|\' . $values[4]]') === FALSE, 'M5 seen composite key must not use raw workbook casing.');
spmi_master_check(strpos($service, '$seen[strtoupper($values[0]) . \'|\' . strtoupper($values[4])]') !== FALSE, 'M5 seen composite key must use normalized uppercase codes.');
spmi_master_check(stripos($service . $index, 'no-delete') !== FALSE, 'M5 additive/no-delete contract missing.');
spmi_master_check(stripos($service . $model . $standards_model, 'FOR UPDATE') !== FALSE, 'M5 version/row lock contract missing.');
spmi_master_check(strpos($service, "in_array(strtolower(\$v[13]), ['none', 'file', 'url', 'either', 'both'], TRUE)") !== FALSE, 'M5 evidence policy must be allowlisted case-insensitively.');
spmi_master_check(strpos($service, "'evidence_policy' => \$row['evidence_policy']") !== FALSE, 'M5 re-import must intentionally upsert evidence policy.');
spmi_master_check(strpos($model, 'i.evidence_policy') !== FALSE, 'M5 export must include evidence policy.');
spmi_master_check(strpos($controller, "'evidence_policy'") !== FALSE, 'M5 export row order must include evidence policy.');
spmi_master_check(strpos($preview, 'Kebijakan Bukti') !== FALSE && strpos($preview, "html_escape(\$row['evidence_policy'])") !== FALSE, 'M5 preview must render escaped evidence policy.');
foreach (['TYPE_STRING', 'is_uploaded_file', 'UPLOAD_ERR_OK', '2 * 1024 * 1024', 'sha256', '1800', 'rename', 'log_message', 'Import Master SPMI gagal', 'clear_preview', '0700'] as $literal) spmi_master_check(stripos($controller, $literal) !== FALSE, 'M5 controller contract missing: ' . $literal);
foreach (['purge_expired_artifacts', 'spmi_master_preview_*.json', 'spmi_master_claim_*.json', 'filemtime', 'GLOB_NOSORT', 'basename($current[\'basename\'])', 'time() - 1800'] as $literal) spmi_master_check(stripos($controller, $literal) !== FALSE, 'M5 artifact cleanup contract missing: ' . $literal);
spmi_master_check(substr_count($controller, '$this->purge_expired_artifacts();') === 3, 'M5 cleanup must run before preview, confirm, and cancel.');
spmi_master_check(strpos($controller, 'extends Admin_Lpmpi_Controller') !== FALSE, 'M5 controller base class missing.');
spmi_master_check(strpos($controller, "redirect('lpmpi/spmi-standards')") !== FALSE, 'M5 index must redirect to canonical standards page.');
spmi_master_check(strpos($controller, "keep_flashdata(['success', 'error'])") !== FALSE, 'M5 index must preserve import feedback through the canonical redirect.');
spmi_master_check(substr_count($controller, "redirect('lpmpi/spmi-standards');") >= 4, 'M5 master import feedback must redirect directly to the standards page.');
spmi_master_check(strpos($controller, 'if (empty($result[\'valid\'])) return $this->render(\'import_preview\'') !== FALSE, 'M5 all-invalid preview must render specific parser errors.');
spmi_master_check(strpos($controller, '\'valid\' => $result[\'valid\'], \'errors\' => $result[\'errors\'], \'total\' => $result[\'total\']') !== FALSE, 'M5 all-invalid preview must retain parser result details.');
spmi_master_check(strpos($controller, "'message' => \$e->getMessage()") === FALSE && strpos($controller, "'message' => 'Workbook tidak dapat diproses.'") !== FALSE, 'M5 parser exception must not expose internal details.');
spmi_master_check(strpos($routes, '$route[\'lpmpi/spmi-master\'] = \'lpmpi/Spmi_master/index\';') !== FALSE, 'M5 route missing.');
spmi_master_check(strpos($sidebar, "'key' => 'spmi_master'") === FALSE, 'M5 sidebar entry must be removed.');
spmi_master_check(strpos($preview, 'html_escape') !== FALSE, 'M5 preview must escape output.');

fwrite(STDOUT, "SPMI master regression checks passed.\n");
