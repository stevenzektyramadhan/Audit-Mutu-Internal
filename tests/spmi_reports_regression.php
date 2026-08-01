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

fwrite(STDOUT, "SPMI reports regression checks passed.\n");
