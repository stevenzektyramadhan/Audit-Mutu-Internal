<?php
$root = dirname(__DIR__);
function m10_source($path) { $value = file_get_contents(dirname(__DIR__) . DIRECTORY_SEPARATOR . $path); if ($value === FALSE) throw new RuntimeException('Tidak dapat membaca ' . $path); return $value; }
function m10_check($condition, $message) { if (!$condition) throw new RuntimeException($message); }

$migration = m10_source('migrations/020_create_spmi_reports.sql');
$schema = m10_source('database_schema.sql');
$model = m10_source('application/models/Spmi_reports_model.php');
$service = m10_source('application/services/Spmi_reports_service.php');
$controller = m10_source('application/controllers/lpmpi/Spmi_reports.php');
$routes = m10_source('application/config/routes.php');
$sidebar = m10_source('application/views/layouts/sidebar.php');
$index = m10_source('application/views/lpmpi/spmi_reports/index.php');
$detail = m10_source('application/views/lpmpi/spmi_reports/detail.php');
$print = m10_source('application/views/lpmpi/spmi_reports/print.php');

foreach (['spmi_reports', 'spmi_report_items', 'UNIQUE KEY `uq_spmi_reports_assessment`', 'UNIQUE KEY `uq_spmi_reports_report_number`', 'generated_by', 'generated_at', 'descriptor_snapshot', '`score` TINYINT UNSIGNED NOT NULL', 'ON DELETE RESTRICT', 'ON UPDATE RESTRICT'] as $literal) m10_check(strpos($migration, $literal) !== FALSE, 'M10 migration contract missing: ' . $literal);
m10_check(!preg_match('/(^|;|\R)\s*(INSERT|UPDATE|DELETE)\s+/i', $migration), 'M10 migration must be seed-free.');
foreach (['current parity migration 001-020', 'spmi_reports', 'spmi_report_items', 'uq_spmi_reports_assessment', 'uq_spmi_reports_report_number', 'uq_spmi_report_items_order'] as $literal) m10_check(strpos($schema, $literal) !== FALSE, 'M10 schema parity missing: ' . $literal);
foreach (['assessment_for_update', 'cycle_for_update', 'submission_for_update', 'report_for_assessment_for_update', 'insert_item', 'report_by_id', 'reports', 'FOR UPDATE'] as $literal) m10_check(strpos($model, $literal) !== FALSE, 'M10 model contract missing: ' . $literal);
foreach (['trans_begin', 'finalized', 'submitted', 'configured', 'closed', 'score', 'descriptor', 'realization_snapshot', 'affected_rows', 'report_number'] as $literal) m10_check(strpos($service, $literal) !== FALSE, 'M10 service contract missing: ' . $literal);
foreach (['extends Admin_Lpmpi_Controller', "method(TRUE) !== 'POST'", 'generate', 'export', 'print', 'Spmi_reports_service', 'show_error'] as $literal) m10_check(strpos($controller, $literal) !== FALSE, 'M10 controller contract missing: ' . $literal);
foreach (['lpmpi/spmi-reports', 'assessment/create/(:num)', 'detail/(:num)', 'export/(:num)', 'print/(:num)'] as $literal) m10_check(strpos($routes, $literal) !== FALSE, 'M10 route missing: ' . $literal);
m10_check(substr_count($sidebar, "'key' => 'spmi_reports', 'label' => 'Laporan SPMI', 'icon' => 'fa-file-alt', 'url' => 'lpmpi/spmi-reports', 'group' => 'Insights'") === 2, 'M10 sidebar entry must exist only for management roles.');
foreach ([$index, $detail, $print] as $view) m10_check(strpos($view, 'html_escape') !== FALSE && strpos($view, 'nl2br(html_escape(') !== FALSE, 'M10 views must escape text and multiline snapshots.');
m10_check(strpos($detail, 'spmi_report_items') === FALSE && strpos($print, 'spmi_report_items') === FALSE, 'M10 detail/print must not expose source table names.');
foreach (['TYPE_STRING', 'setCellValueExplicit', 'formula_prefixes', 'while (ob_get_level() > 0)', 'Save as PDF', 'window.print'] as $literal) m10_check(strpos($controller, $literal) !== FALSE || strpos($print, $literal) !== FALSE, 'M10 export/print contract missing: ' . $literal);
foreach (['Laporan.php', 'Laporan_model.php', 'tugas_audit', 'jawaban_audit', 'pdf'] as $legacy) m10_check(strpos($model . $service . $controller . $index . $detail . $print, $legacy) === FALSE, 'M10 legacy isolation broken: ' . $legacy);

foreach (['finding_type_snapshot', 'evidence_url_snapshot', 'evidence_file_original_name_snapshot', 'evidence_file_mime_type_snapshot', 'evidence_file_size_bytes_snapshot', 'evidence_file_sha256_snapshot'] as $field) m10_check(strpos($schema, '`' . $field . '`') !== FALSE, 'M17-06 schema snapshot field missing: ' . $field);
m10_check(strpos($service, "['submitted', 'resubmitted']") !== FALSE || strpos($service, '[\'submitted\', \'resubmitted\']') !== FALSE, 'M17-06 report generation must accept submitted and resubmitted final submissions.');
foreach (['finding_type_snapshot', 'evidence_url_snapshot', 'evidence_file_original_name_snapshot', 'evidence_file_mime_type_snapshot', 'evidence_file_size_bytes_snapshot', 'evidence_file_sha256_snapshot'] as $field) m10_check(strpos($service, "'" . $field . "'") !== FALSE, 'M17-06 generate must persist report item snapshot: ' . $field);
m10_check(strpos($model, 'submission_items_for_report') !== FALSE && strpos($model, 'spmi_auditee_submission_items') !== FALSE && strpos($model, 'spmi_auditee_evidence') !== FALSE, 'M17-06 report model must join locked submission item/evidence source data.');
m10_check(preg_match('/ORDER BY\s+e\.id\s+ASC/i', $model) || preg_match('/MIN\(e2\.id\)/i', $model) || preg_match('/order_by\(\s*[\'\"]e\.id[\'\"]\s*,\s*[\'\"]ASC[\'\"]/i', $model), 'M17-06 report evidence selection must choose one deterministic evidence row.');
foreach (['report_item_evidence', 'spmi_report_item_evidence', 'evidence_for_read', 'private_storage_path', 'stored_name'] as $forbidden) m10_check(strpos($model . $service . $controller . $detail . $print, $forbidden) === FALSE, 'M17-06 reports must not add per-file schema or live-read evidence output: ' . $forbidden);
foreach (['finding_type_snapshot', 'evidence_url_snapshot', 'evidence_file_original_name_snapshot', 'evidence_file_mime_type_snapshot', 'evidence_file_size_bytes_snapshot', 'evidence_file_sha256_snapshot'] as $field) m10_check(strpos($detail, '$item->' . $field) !== FALSE && strpos($print, '$item->' . $field) !== FALSE && strpos($detail, 'html_escape') !== FALSE && strpos($print, 'html_escape') !== FALSE, 'M17-06 detail/print must safely consume item snapshot field: ' . $field);
foreach (['finding_type_snapshot', 'evidence_url_snapshot', 'evidence_file_original_name_snapshot', 'evidence_file_mime_type_snapshot', 'evidence_file_size_bytes_snapshot', 'evidence_file_sha256_snapshot'] as $field) m10_check(strpos($controller, '$item->' . $field) !== FALSE, 'M17-06 export must consume item snapshot field: ' . $field);
foreach (['spmi_reports', 'spmi_report_items'] as $literal) m10_check(strpos(m10_source('application/models/Spmi_rtm_model.php') . m10_source('application/services/Spmi_rtm_service.php'), $literal) !== FALSE, 'M17-06 RTM must remain linked to report snapshots: ' . $literal);

m10_check(preg_match('/finalized_assessments\(\).*spmi_auditee_submissions s.*s\.assignment_id = a\.id.*aa\.source_submission_version = s\.version/s', $model) === 1, 'M17-07B finalized assessment list must require current submission provenance match.');
m10_check(preg_match('/assessment_for_update\(\$assessment_id\).*JOIN spmi_auditee_submissions s ON s\.assignment_id = a\.id AND s\.version = aa\.source_submission_version/s', $model) === 1, 'M17-07B report assessment lock must include matching source_submission_version.');
m10_check(preg_match('/submission_for_update\(\$assignment_id, \$source_submission_version\).*version = \?/s', $model) === 1, 'M17-07B report submission lock must select exact assessment source submission version.');
m10_check(preg_match('/\$this->model->submission_for_update\(\$assessment->assignment_id, \$assessment->source_submission_version\)/', $service) === 1, 'M17-07B report generation must load submission matching assessment provenance.');
m10_check(preg_match('/\(int\) \$assessment->source_submission_version\s*!==\s*\(int\) \$submission->version/', $service) === 1, 'M17-07B report generation must reject finalized assessment when current submission provenance mismatches.');

fwrite(STDOUT, "SPMI reports regression checks passed.\n");
