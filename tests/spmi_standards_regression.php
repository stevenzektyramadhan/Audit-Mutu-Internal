<?php
$root = dirname(__DIR__);
function spmi_source($path) { $value = file_get_contents(dirname(__DIR__) . DIRECTORY_SEPARATOR . $path); if ($value === FALSE) throw new RuntimeException('Tidak dapat membaca ' . $path); return $value; }
function spmi_check($condition, $message) { if (!$condition) throw new RuntimeException($message); }

$migration = spmi_source('migrations/013_create_spmi_versioned_standards.sql');
$migration_014 = spmi_source('migrations/014_enforce_spmi_version_invariants.sql');
$schema = spmi_source('database_schema.sql');
$helper = spmi_source('application/helpers/app_helper.php');
$model = spmi_source('application/models/Spmi_standards_model.php');
$service = spmi_source('application/services/Spmi_standards_service.php');
$controller = spmi_source('application/controllers/lpmpi/Spmi_standards.php');
$routes = spmi_source('application/config/routes.php');
$sidebar = spmi_source('application/views/layouts/sidebar.php');
$views = [spmi_source('application/views/lpmpi/spmi_standards/index.php'), spmi_source('application/views/lpmpi/spmi_standards/version_form.php'), spmi_source('application/views/lpmpi/spmi_standards/version_detail.php'), spmi_source('application/views/lpmpi/spmi_standards/standard_form.php')];

foreach (['spmi_versions', 'spmi_standards', "ENUM('draft','review','approved','active','retired')", 'UNIQUE', 'ON DELETE RESTRICT', 'INSERT IGNORE', 'M3-INITIAL', 'SPMI-', 'Standar SPMI ', '21', '`created_by` INT NULL', '`title` VARCHAR(200) NOT NULL'] as $literal) spmi_check(strpos($migration, $literal) !== FALSE, 'Migration contract missing: ' . $literal);
foreach (['spmi_versions', 'spmi_standards', 'M3-INITIAL', 'SPMI-', 'Standar SPMI ', '21', 'current parity migration 001-018', '`created_by` INT NULL', '`title` VARCHAR(200) NOT NULL', '`active_slot` TINYINT GENERATED ALWAYS AS', 'UNIQUE KEY `uq_spmi_versions_active_slot` (`active_slot`)'] as $literal) spmi_check(strpos($schema, $literal) !== FALSE, 'Schema parity missing: ' . $literal);
foreach (['INFORMATION_SCHEMA.COLUMNS', 'INFORMATION_SCHEMA.STATISTICS', 'active_slot', 'GENERATED ALWAYS AS', 'UNIQUE KEY `uq_spmi_versions_active_slot` (`active_slot`)', "COUNT(*) FROM `spmi_versions` WHERE `status` = 'active'", "SIGNAL SQLSTATE '45000'", 'more than one active version exists', 'DROP PROCEDURE'] as $literal) spmi_check(strpos($migration_014, $literal) !== FALSE, 'Migration 014 invariant missing: ' . $literal);
spmi_check(substr_count($migration, 'UNION ALL SELECT') >= 20, 'Migration must seed exactly 21 placeholders.');
spmi_check(strpos($migration, '`standar`') === FALSE && strpos($migration, '`pertanyaan`') === FALSE, 'Migration must not mutate legacy tables.');
spmi_check(strpos($routes, "lpmpi/instrumen/download") !== FALSE && strpos($routes, "lpmpi/penetapan/download") !== FALSE, 'Legacy routes changed.');
foreach (['draft', 'review', 'approved', 'active', 'retired', 'TRANSITIONS', "'review' => ['draft', 'approved']", 'MUTABLE_STATUSES', 'Hanya satu', 'status', 'trans_start', 'active', 'set_source', 'clear_source', 'strlen($payload[\'title\']) > 200'] as $literal) spmi_check(strpos($service, $literal) !== FALSE, 'Lifecycle/service contract missing: ' . $literal);
spmi_check(strpos($service, "const MUTABLE_STATUSES = ['draft', 'review']") !== FALSE && strpos($service, 'Versi ini bersifat hanya-baca.') !== FALSE, 'State guard missing.');
foreach (['update_version', 'create_standard', 'update_standard', 'guard_mutable_update'] as $method) { $start = strpos($service, 'function ' . $method); $end = strpos("\n    public function ", $start + 1); if ($end === FALSE) $end = strpos("\n    private function ", $start + 1); if ($end === FALSE) $end = strlen($service); $body = substr($service, $start, $end - $start); spmi_check(strpos($body, 'trans_start') !== FALSE && strpos($body, 'find_version(') !== FALSE && strpos($body, 'TRUE') !== FALSE && strpos($body, 'is_mutable') !== FALSE && strpos($body, 'find_version(') < strpos($body, 'is_mutable'), 'Mutable write must lock version before checking state: ' . $method); }
spmi_check(strpos($service, 'count_all_results') === FALSE && strpos($service, 'is_duplicate_active_error') !== FALSE && strpos($service, 'db_debug = FALSE') !== FALSE && strpos($service, '(int) $error[\'code\'] === 1062') !== FALSE, 'Transition must rely on unique active slot and map duplicate active error.');
spmi_check(strpos($service, '$previous_source_file_path = $version->source_file_path;') !== FALSE && strpos($service, '$result[\'previous_source_file_path\'] = $previous_source_file_path;') !== FALSE && strpos($service, 'if ($result[\'success\'])') !== FALSE && strpos($service, 'clear_source($id) { return $this->guard_mutable_update($id, [\'source_file_path\' => NULL], TRUE); }') !== FALSE, 'Source mutation must return locked prior filename only after success.');
spmi_check(strpos($controller, 'elseif (!empty($result[\'previous_source_file_path\'])) delete_private_file(\'spmi_source\', $result[\'previous_source_file_path\']);') !== FALSE && strpos($controller, 'if ($result[\'success\'] && !empty($result[\'previous_source_file_path\'])) delete_private_file(\'spmi_source\', $result[\'previous_source_file_path\']);') !== FALSE && strpos($controller, '$old = $version->source_file_path;') === FALSE && strpos($controller, 'delete_private_file(\'spmi_source\', $version->source_file_path)') === FALSE, 'Controller must clean only service-returned locked stale source.');
spmi_check(substr_count($migration, "CONCAT('Standar SPMI ', LPAD(numbers.n, 2, '0'))") === 1, 'Migration placeholder titles must be generated exactly once.');
spmi_check(substr_count($schema, "CONCAT('Standar SPMI ', LPAD(numbers.n, 2, '0'))") === 1, 'Schema placeholder titles must be generated exactly once.');
spmi_check(strpos($migration, "VALUES ('M3-INITIAL', 'Katalog Standar SPMI M3', NULL, 'draft', NULL)") !== FALSE, 'Migration must seed system version without users.');
spmi_check(strpos($schema, "VALUES ('M3-INITIAL', 'Katalog Standar SPMI M3', NULL, 'draft', NULL)") !== FALSE, 'Schema must seed system version without users.');
spmi_check(strpos($helper, "'spmi_source'") !== FALSE && strpos($helper, "in_array(\$category, ['user_photos', 'spmi_source']") !== FALSE, 'Private storage category contract missing.');
foreach (['extends Admin_Lpmpi_Controller', "method(TRUE) !== 'POST'", 'allowed_types', "private_storage_dir('spmi_source')", "private_storage_path('spmi_source'", 'force_download', 'random_bytes', 'delete_private_file', 'Spmi_standards_service::TRANSITIONS', 'is_mutable($version)', 'Versi ini bersifat hanya-baca.'] as $literal) spmi_check(strpos($controller, $literal) !== FALSE, 'Controller security contract missing: ' . $literal);
foreach (['lpmpi/spmi-standards', 'version/create', 'version/store', 'version/detail', 'version/edit', 'version/update', 'version/transition', 'source/upload', 'source/download', 'source/delete', 'standard/create', 'standard/store', 'standard/edit', 'standard/update'] as $literal) spmi_check(strpos($routes, $literal) !== FALSE, 'Route missing: ' . $literal);
spmi_check(substr_count($sidebar, "'key' => 'spmi_standards', 'label' => 'Standar SPMI', 'icon' => 'fa-layer-group', 'url' => 'lpmpi/spmi-standards', 'group' => 'Management'") === 2, 'SPMI sidebar entry must exist only for two management roles.');
foreach ($views as $view) { spmi_check(strpos($view, 'html_escape') !== FALSE, 'SPMI view must escape output.'); spmi_check(strpos($view, 'include APPPATH . \'views/layouts/header.php\'') !== FALSE, 'SPMI view header missing.'); }
spmi_check(strpos($views[1], 'form_open(') !== FALSE && strpos($views[2], 'form_open(') !== FALSE && strpos($views[2], 'form_open_multipart(') !== FALSE && strpos($views[3], 'maxlength="200"') !== FALSE, 'SPMI forms missing.');
spmi_check(strpos($views[2], 'Versi approved, active, dan retired bersifat hanya-baca.') !== FALSE, 'Exact read-only notice missing.');
spmi_check(strpos($views[2], 'foreach ($transitions as $next_status)') !== FALSE && strpos($views[2], "form_open('lpmpi/spmi-standards/version/transition/") !== FALSE && strpos($views[2], 'html_escape($next_status)') !== FALSE, 'Legal lifecycle transition controls missing.');
spmi_check(strpos($controller, "public function standard_create(\$version_id)") !== FALSE && strpos($controller, "if (!\$version)") !== FALSE && strpos($controller, "if (!\$this->service->is_mutable(\$version))") !== FALSE, 'Standard create immutable-version guard missing.');
spmi_check(strpos($sidebar, "'key' => 'dashboard'") !== FALSE && strpos($sidebar, "form_open('auth/logout');") !== FALSE, 'Legacy sidebar hooks changed.');

fwrite(STDOUT, "SPMI standards regression checks passed.\n");
