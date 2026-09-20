<?php

$root = dirname(__DIR__);

function upload_settings_source($path)
{
    $contents = file_get_contents(dirname(__DIR__) . DIRECTORY_SEPARATOR . $path);
    if ($contents === FALSE) throw new RuntimeException('Tidak dapat membaca ' . $path);
    return $contents;
}

function upload_settings_check($condition, $message)
{
    if (!$condition) throw new RuntimeException($message);
}

$migration = upload_settings_source('migrations/039_create_upload_size_settings.sql');
$schema = upload_settings_source('database_schema.sql');
$model = upload_settings_source('application/models/Upload_size_settings_model.php');
$service = upload_settings_source('application/services/Upload_size_settings_service.php');
$controller = upload_settings_source('application/controllers/lpmpi/Upload_size_settings.php');
$view = upload_settings_source('application/views/lpmpi/upload_size_settings/index.php');
$routes = upload_settings_source('application/config/routes.php');
$sidebar = upload_settings_source('application/views/layouts/sidebar.php');

foreach ([
    'CREATE TABLE IF NOT EXISTS `spmi_upload_size_settings`',
    '`category` VARCHAR(64) NOT NULL PRIMARY KEY',
    '`label` VARCHAR(100) NOT NULL',
    '`limit_mib` TINYINT UNSIGNED NOT NULL',
    'CONSTRAINT `chk_spmi_upload_size_settings_limit` CHECK (`limit_mib` BETWEEN 1 AND 10)',
] as $literal) {
    upload_settings_check(strpos($migration, $literal) !== FALSE, 'Migration upload setting missing: ' . $literal);
    upload_settings_check(strpos($schema, $literal) !== FALSE, 'Schema upload setting parity missing: ' . $literal);
}
upload_settings_check(strpos($schema, 'current parity migration 001-039') !== FALSE, 'Schema parity comment must indicate 001-039.');

$defaults = [
    "'spmi_evidence', 'Bukti SPMI', 5",
    "'ppepp_documents', 'Dokumen PPEPP', 10",
    "'profile_photos', 'Foto Profil', 2",
    "'spreadsheet_imports', 'Import Spreadsheet', 2",
    "'spmi_source_pdf', 'PDF Sumber SPMI', 5",
    "'institution_logo', 'Logo Lembaga', 4",
];
foreach ($defaults as $literal) {
    upload_settings_check(strpos($migration, $literal) !== FALSE, 'Migration default missing: ' . $literal);
    upload_settings_check(strpos($schema, $literal) !== FALSE, 'Schema default missing: ' . $literal);
}
foreach ([
    "'spmi_evidence' => ['label' => 'Bukti SPMI', 'limit_mib' => 5]",
    "'ppepp_documents' => ['label' => 'Dokumen PPEPP', 'limit_mib' => 10]",
    "'profile_photos' => ['label' => 'Foto Profil', 'limit_mib' => 2]",
    "'spreadsheet_imports' => ['label' => 'Import Spreadsheet', 'limit_mib' => 2]",
    "'spmi_source_pdf' => ['label' => 'PDF Sumber SPMI', 'limit_mib' => 5]",
    "'institution_logo' => ['label' => 'Logo Lembaga', 'limit_mib' => 4]",
] as $literal) {
    upload_settings_check(strpos($service, $literal) !== FALSE, 'Service rollout default missing: ' . $literal);
}
upload_settings_check(strpos($migration, 'ON DUPLICATE KEY UPDATE') !== FALSE && strpos($migration, '`category` = `category`') !== FALSE, 'Migration must be idempotent without overwriting existing setting rows.');
upload_settings_check(substr_count($migration, 'spmi_upload_size_settings') >= 3, 'Migration must create and seed upload size settings.');

foreach (['all()', "table_exists('spmi_upload_size_settings')", 'replace_all(array $rows)', "order_by('category', 'ASC')", "count_all_results('spmi_upload_size_settings')", "insert('spmi_upload_size_settings'", "update('spmi_upload_size_settings'"] as $literal) {
    upload_settings_check(strpos($model, $literal) !== FALSE, 'Persistence model contract missing: ' . $literal);
}
upload_settings_check(strpos($model, 'ini_get') === FALSE && strpos($model, 'input->post') === FALSE, 'Model must stay persistence-only.');

foreach ([
    'const APP_MAX_MIB = 10',
    'const DEFAULT_LIMITS = [',
    'php_ceiling_mib()',
    "ini_bytes('upload_max_filesize')",
    'post_max_payload_bytes()',
    "ini_bytes('post_max_size') - 1024 * 1024",
    'parse_ini_bytes($value)',
    '$unit === \'g\'',
    '$unit === \'m\'',
    '$unit === \'k\'',
    'min(self::APP_MAX_MIB',
    'clamp_mib((int) $row->limit_mib)',
    "preg_match('/^[1-9][0-9]*$/'",
    '$submitted_categories !== $allowed_categories',
    '(int) $value > $ceiling',
    'terlalu kecil untuk menyimpan batas upload minimal 1 MiB',
    'tidak boleh melebihi ceiling aman server',
    'trans_begin()',
    'replace_all($rows)',
    'trans_commit()',
    'trans_rollback()',
    'public static function limit_bytes($category)',
    'clamp_static_mib($mib) * 1024 * 1024',
] as $literal) {
    upload_settings_check(strpos($service, $literal) !== FALSE, 'Service validation/persistence contract missing: ' . $literal);
}
upload_settings_check(strpos($service, "'limit_mib' => (int) " . '$value') !== FALSE, 'Submitted valid limits must persist exactly without silent clamping.');
upload_settings_check(strpos($service, "'limit_mib' => $" . "this->clamp_mib((int) " . '$value' . ")") === FALSE, 'Submitted oversized values must be rejected, not clamped into success.');

upload_settings_check(strpos($controller, 'class Upload_size_settings extends Admin_Lpmpi_Controller') !== FALSE, 'Controller must be management-only via Admin_Lpmpi_Controller.');
upload_settings_check(strpos($controller, "require_once APPPATH . 'services/Upload_size_settings_service.php'") !== FALSE, 'Controller must use upload settings service.');
upload_settings_check(strpos($controller, "'active_menu' => 'upload_size_settings'") !== FALSE, 'Controller must set active upload settings menu.');
$update_pos = strpos($controller, 'public function update()');
upload_settings_check($update_pos !== FALSE && strpos($controller, '$this->require_post();', $update_pos) !== FALSE, 'Update action must require POST.');
upload_settings_check(strpos($controller, "input->post(NULL, TRUE)") !== FALSE, 'Controller must pass escaped POST data to service.');
upload_settings_check(strpos($controller, "show_error('Method tidak diizinkan.', 405, 'Method Not Allowed')") !== FALSE, 'Controller POST guard must return 405.');

foreach ([
    "form_open('lpmpi/upload-size-settings/update')",
    'html_escape($setting[\'label\'])',
    'html_escape($category)',
    'html_escape((string) $setting[\'limit_mib\'])',
    'html_escape((string) $php_ceiling_mib)',
    'uploader aktif yang sudah terhubung',
    '1 MiB headroom multipart',
    'type="number"',
    'min="1"',
] as $literal) {
    upload_settings_check(strpos($view, $literal) !== FALSE, 'View CSRF/escaping contract missing: ' . $literal);
}

foreach ([
    "\$route['lpmpi/upload-size-settings'] = 'lpmpi/Upload_size_settings/index';",
    "\$route['lpmpi/upload-size-settings/update'] = 'lpmpi/Upload_size_settings/update';",
] as $literal) {
    upload_settings_check(strpos($routes, $literal) !== FALSE, 'Upload settings route missing: ' . $literal);
}
upload_settings_check(substr_count($routes, 'upload-size-settings') === 2, 'Upload settings must expose exactly index and update routes.');

$menu = "['key' => 'upload_size_settings', 'label' => 'Pengaturan Upload', 'icon' => 'fa-upload', 'url' => 'lpmpi/upload-size-settings', 'group' => 'Settings']";
upload_settings_check(substr_count($sidebar, $menu) === 2, 'Upload settings sidebar menu must appear once for each management role.');
$super_admin_start = strpos($sidebar, "'super_admin' => [");
$admin_lpmpi_start = strpos($sidebar, "'admin_lpmpi' => [");
$auditor_start = strpos($sidebar, "'auditor' => [");
$auditee_start = strpos($sidebar, "'auditee' => [");
upload_settings_check(strpos($sidebar, $menu, $super_admin_start) !== FALSE && strpos($sidebar, $menu, $super_admin_start) < $admin_lpmpi_start, 'Upload settings menu must be inside super_admin menu.');
upload_settings_check(strpos($sidebar, $menu, $admin_lpmpi_start) !== FALSE && strpos($sidebar, $menu, $admin_lpmpi_start) < $auditor_start, 'Upload settings menu must be inside admin_lpmpi menu.');
upload_settings_check(strpos($sidebar, $menu, $auditor_start) === FALSE || strpos($sidebar, $menu, $auditor_start) > $auditee_start, 'Upload settings menu must not be available to auditor.');
upload_settings_check(strpos($sidebar, $menu, $auditee_start) === FALSE, 'Upload settings menu must not be available to auditee.');

fwrite(STDOUT, "Upload size settings regression checks passed.\n");
