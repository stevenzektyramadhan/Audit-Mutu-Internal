<?php
$root = dirname(__DIR__);
function spmi_indicator_source($path) { $value = file_get_contents(dirname(__DIR__) . DIRECTORY_SEPARATOR . $path); if ($value === FALSE) throw new RuntimeException('Tidak dapat membaca ' . $path); return $value; }
function spmi_indicator_check($condition, $message) { if (!$condition) throw new RuntimeException($message); }

$migration = spmi_indicator_source('migrations/015_create_spmi_indicators.sql');
$schema = spmi_indicator_source('database_schema.sql');
$model = spmi_indicator_source('application/models/Spmi_indicators_model.php');
$service = spmi_indicator_source('application/services/Spmi_indicators_service.php');
$controller = spmi_indicator_source('application/controllers/lpmpi/Spmi_indicators.php');
$routes = spmi_indicator_source('application/config/routes.php');
$sidebar = spmi_indicator_source('application/views/layouts/sidebar.php');
$views = [
    spmi_indicator_source('application/views/lpmpi/spmi_indicators/index.php'),
    spmi_indicator_source('application/views/lpmpi/spmi_indicators/indicator_form.php'),
    spmi_indicator_source('application/views/lpmpi/spmi_indicators/indicator_detail.php'),
    spmi_indicator_source('application/views/lpmpi/spmi_indicators/target_form.php'),
];

foreach (['spmi_indicators', 'spmi_indicator_targets', "ENUM('IKU','IKT')", 'VARCHAR(64)', 'VARCHAR(200)', 'TEXT NOT NULL', 'UNIQUE KEY', 'ON DELETE RESTRICT', 'scope_organization_unit_id', 'responsible_organization_unit_id'] as $literal) spmi_indicator_check(strpos($migration, $literal) !== FALSE, 'Migration contract missing: ' . $literal);
foreach (['current parity migration 001-018', 'spmi_indicators', 'spmi_indicator_targets', 'uq_spmi_indicators_standard_code', 'uq_spmi_indicator_targets_indicator_year'] as $literal) spmi_indicator_check(strpos($schema, $literal) !== FALSE, 'Schema parity missing: ' . $literal);
spmi_indicator_check(strpos($migration, 'INSERT') === FALSE, 'M4 migration must not seed data.');
spmi_indicator_check(strpos($migration, '`standar`') === FALSE && strpos($migration, '`pertanyaan`') === FALSE, 'M4 migration must not mutate legacy tables.');
foreach (['find_standard', 'find_version', 'FOR UPDATE', 'get_units', 'get_indicators', 'get_targets', 'find_active_unit', 'indicator_code', 'target_year'] as $literal) spmi_indicator_check(strpos($model, $literal) !== FALSE, 'Model contract missing: ' . $literal);
spmi_indicator_check(strpos($model, 'v.title AS version_title') !== FALSE && strpos($model, 'v.version_title') === FALSE, 'Indicator query must alias spmi_versions.title as version_title.');
foreach (['trans_start', 'find_version', 'TRUE', 'is_mutable', 'IKU', 'IKT', '2000', '2100', 'strlen', 'find_by_code', 'find_target_by_year', 'target_value'] as $literal) spmi_indicator_check(strpos($service, $literal) !== FALSE, 'Service contract missing: ' . $literal);
spmi_indicator_check(strpos($service, 'standard_version') !== FALSE && strpos($service, 'find_version($standard->version_id)') !== FALSE, 'Standard parent-version lookup missing.');
foreach (['extends Admin_Lpmpi_Controller', 'form_validation', 'method(TRUE) !== \'POST\'', 'show_error', 'indicator_create', 'indicator_store', 'indicator_detail', 'indicator_edit', 'indicator_update', 'target_create', 'target_store', 'target_edit', 'target_update'] as $literal) spmi_indicator_check(strpos($controller, $literal) !== FALSE, 'Controller contract missing: ' . $literal);
spmi_indicator_check(strpos($controller, 'standard_version($standard_id)') !== FALSE && strpos($controller, 'readonly_redirect') !== FALSE, 'Immutable indicator create guard missing.');
foreach (['lpmpi/spmi-indicators', 'indicator/create', 'indicator/store', 'indicator/detail', 'indicator/edit', 'indicator/update', 'target/create', 'target/store', 'target/edit', 'target/update'] as $literal) spmi_indicator_check(strpos($routes, $literal) !== FALSE, 'Route missing: ' . $literal);
spmi_indicator_check(substr_count($sidebar, "'key' => 'spmi_indicators', 'label' => 'Indikator SPMI', 'icon' => 'fa-chart-line', 'url' => 'lpmpi/spmi-indicators', 'group' => 'Management'") === 2, 'M4 sidebar entry must exist only for management roles.');
foreach ($views as $view) { spmi_indicator_check(strpos($view, 'html_escape') !== FALSE, 'M4 view must escape output.'); spmi_indicator_check(strpos($view, "include APPPATH . 'views/layouts/header.php'") !== FALSE, 'M4 view header missing.'); }
spmi_indicator_check(strpos($views[1], 'form_open(') !== FALSE && strpos($views[3], 'form_open(') !== FALSE, 'M4 forms missing.');
spmi_indicator_check(strpos($views[2], 'Hanya-baca') !== FALSE && strpos($views[2], 'Belum ada target') !== FALSE, 'M4 read-only and empty target states missing.');
spmi_indicator_check(strpos($views[0], "in_array(\$standard->version_status, ['draft', 'review'], TRUE)") !== FALSE && strpos($views[0], 'Hanya-baca') !== FALSE, 'M4 index immutable UI guard missing.');

fwrite(STDOUT, "SPMI indicators regression checks passed.\n");
