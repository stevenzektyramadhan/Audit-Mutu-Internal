<?php

function akun_import_source($path)
{
    $source = file_get_contents(dirname(__DIR__) . '/' . $path);
    if ($source === FALSE) throw new RuntimeException('Tidak dapat membaca ' . $path);
    return $source;
}

function akun_import_check($condition, $message)
{
    if (!$condition) throw new RuntimeException($message);
}

function akun_import_method($source, $signature)
{
    $start = strpos($source, $signature);
    if ($start === FALSE) throw new RuntimeException('Method tidak ditemukan: ' . $signature);
    $body_start = strpos($source, '{', $start);
    $depth = 1; $offset = $body_start + 1;
    while ($offset < strlen($source) && $depth > 0) {
        if ($source[$offset] === '{') $depth++;
        if ($source[$offset] === '}') $depth--;
        $offset++;
    }
    if ($depth !== 0) throw new RuntimeException('Brace method tidak seimbang: ' . $signature);
    return substr($source, $body_start + 1, $offset - $body_start - 2);
}

$migration = akun_import_source('migrations/036_add_users_import_support.sql');
$schema = akun_import_source('database_schema.sql');
$model = akun_import_source('application/models/User_model.php');
$service = akun_import_source('application/services/Akun_import_service.php');
$controller = akun_import_source('application/controllers/lpmpi/Akun_import.php');
$auth = akun_import_source('application/services/Auth_service.php');
$login = akun_import_source('application/views/auth/login.php');
$sidebar = akun_import_source('application/views/layouts/sidebar.php');
$routes = akun_import_source('application/config/routes.php');
$index = akun_import_source('application/views/users/index.php');
$result = akun_import_source('application/views/lpmpi/akun_import/result.php');
$preview = akun_import_source('application/views/lpmpi/akun_import/preview.php');

$autoload_position = strpos($service, "require_once FCPATH . 'vendor/autoload.php';");
$filter_position = strpos($service, 'class AkunImportReadFilter implements');
akun_import_check($autoload_position !== FALSE && $filter_position !== FALSE && $autoload_position < $filter_position, 'Composer autoload harus dimuat sebelum deklarasi AkunImportReadFilter.');

foreach (['INFORMATION_SCHEMA.COLUMNS', "COLUMN_NAME = 'identity_number'", "ADD COLUMN `identity_number` VARCHAR(32) NULL AFTER `email`", 'INFORMATION_SCHEMA.STATISTICS', "INDEX_NAME = 'uq_users_identity_number'", 'ADD UNIQUE KEY `uq_users_identity_number` (`identity_number`)', "COLUMN_NAME = 'must_change_password'", "ADD COLUMN `must_change_password` TINYINT(1) NOT NULL DEFAULT 0 AFTER `password`"] as $contract) akun_import_check(strpos($migration, $contract) !== FALSE, 'Migration 036 kehilangan kontrak additive: ' . $contract);
foreach (['INSERT', 'UPDATE', 'DELETE', 'FOREIGN_KEY_CHECKS'] as $forbidden) akun_import_check(stripos($migration, $forbidden) === FALSE, 'Migration 036 tidak boleh memuat: ' . $forbidden);
akun_import_check(strpos($schema, '`identity_number` VARCHAR(32) NULL,') !== FALSE && strpos($schema, 'UNIQUE KEY `uq_users_identity_number` (`identity_number`)') !== FALSE, 'Bootstrap users schema harus memakai named identity unique key.');
akun_import_check(strpos($model, 'find_by_identity_number') !== FALSE && strpos($model, "'must_change_password' => 0") !== FALSE, 'Model harus lookup identity dan clear flag bersama password reset.');

$parse = akun_import_method($service, 'public function parse($path)');
foreach (["const HEADERS = ['NIP/NIDN', 'Nama', 'Email', 'Role']", 'listWorksheetInfo($path)', "'worksheetName'] === 'Master Akun'", "['totalRows'] > 1001", "['totalColumns'] !== 4", "setLoadSheetsOnly('Master Akun')", 'setReadFilter(new AkunImportReadFilter())', "rangeToArray('A1:D1'", "'Formula tidak diizinkan.'", 'getFormattedValue()', 'TYPE_NUMERIC', "'NIP/NIDN harus diisi sebagai teks.'", 'disconnectWorksheets()'] as $contract) akun_import_check(strpos($service, $contract) !== FALSE, 'Parser import kehilangan kontrak: ' . $contract);
akun_import_check(strpos($parse, 'setReadDataOnly(TRUE)') === FALSE, 'Parser tidak boleh memakai ReadDataOnly karena metadata formula wajib dipertahankan.');
akun_import_check(strpos($parse, 'reader->load($path)') > strpos($parse, 'listWorksheetInfo($path)'), 'Parser harus memeriksa metadata sebelum load.');
akun_import_check(strpos($parse, 'setLoadSheetsOnly') < strpos($parse, 'reader->load($path)') && strpos($parse, 'setReadFilter') < strpos($parse, 'reader->load($path)'), 'Parser harus membatasi sheet/range sebelum load.');
akun_import_check(strpos($service, 'class AkunImportReadFilter implements') !== FALSE && strpos($service, '$row <= 1001') !== FALSE && strpos($service, "['A', 'B', 'C', 'D']") !== FALSE, 'Read filter harus membatasi A:D dan maksimum 1.000 data row.');

$confirm = akun_import_method($service, 'public function confirm($rows)');
foreach (['trans_begin()', "'must_change_password' => 1", 'password_hash($password, PASSWORD_DEFAULT)', 'find_by_identity_number', "find_by_email(\$row['email'], \$check_database === 'lock')", "'identity_number' => \$row['identity_number']", "'role' => \$row['role']", "'password' => \$password"] as $contract) akun_import_check(strpos($confirm, $contract) !== FALSE || strpos($service, $contract) !== FALSE, 'Confirm import kehilangan kontrak: ' . $contract);
if (preg_match_all("/log_message\\(.*?\\);/s", $service, $log_calls)) foreach ($log_calls[0] as $log_call) akun_import_check(strpos($log_call, '$password') === FALSE, 'Log import tidak boleh memuat plaintext password.');
akun_import_check(strpos($result, "html_escape(\$account['identity_number'])") !== FALSE && strpos($result, "html_escape(\$account['role'])") !== FALSE && strpos($result, "html_escape(\$account['password'])") !== FALSE && strpos($result, 'form_open') === FALSE, 'Hasil sekali tampil harus escape NIP/NIDN, role, password dan tanpa export/postback.');

foreach (['extends Admin_Lpmpi_Controller', 'private function require_post()', 'move_uploaded_file', "private_storage_dir('tmp')", "hash_file('sha256'", 'rename($path, $claim)', "time() - (int) (\$meta['created_at'] ?? 0) > 1800"] as $contract) akun_import_check(strpos($controller, $contract) !== FALSE, 'Controller import kehilangan kontrak keamanan: ' . $contract);
akun_import_check(strpos($controller, "upload_limit_bytes('spreadsheet_imports')") !== FALSE && strpos($controller, 'Upload_size_settings_service::limit_bytes($category)') !== FALSE, 'Controller import harus memakai batas upload spreadsheet dinamis.');
akun_import_check(strpos($controller, "getStyle('A2:A1001')->getNumberFormat()->setFormatCode") !== FALSE && strpos($controller, 'NumberFormat::FORMAT_TEXT') !== FALSE, 'Template harus memformat NIP/NIDN sebagai teks pada range import.');
akun_import_check(strpos($controller, "if (!\$result['valid']) return \$this->load->view('lpmpi/akun_import/preview'") !== FALSE, 'Preview zero-valid harus merender error parser, bukan redirect generik.');
akun_import_check(strpos($controller, "'token' => ''") !== FALSE && strpos($controller, "'errors' => \$result['errors']") !== FALSE && strpos($controller, "'total' => \$result['total']") !== FALSE, 'Preview zero-valid harus meneruskan errors dan total tanpa token konfirmasi.');
akun_import_check(strpos($preview, '<?php if ($valid): ?>') !== FALSE && strpos($preview, 'Konfirmasi import') !== FALSE, 'Preview zero-valid tidak boleh menampilkan konfirmasi import.');
akun_import_check(strpos($controller, "'message' => \$exception->getMessage()") === FALSE && strpos($controller, "'message' => 'Workbook tidak dapat diproses.'") !== FALSE, 'Preview exception tidak boleh mengekspos detail internal parser.');
foreach (['lpmpi/akun-import/template', 'lpmpi/akun-import/preview', 'lpmpi/akun-import/confirm', 'lpmpi/akun-import/cancel'] as $route) akun_import_check(strpos($routes, $route) !== FALSE, 'Route import hilang: ' . $route);
akun_import_check(strpos($index, "site_url('lpmpi/akun-import')") !== FALSE, 'Users index harus memiliki entry import.');
akun_import_check(strpos($auth, 'must_change_password') !== FALSE && strpos($auth, 'Lupa Password') !== FALSE && strpos($login, "flashdata('warning')") !== FALSE && strpos($sidebar, "flashdata('warning')") !== FALSE, 'Advisory impor harus tampil di redirect login biasa.');

fwrite(STDOUT, "Akun import regression checks passed.\n");
