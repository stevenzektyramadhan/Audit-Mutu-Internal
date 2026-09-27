<?php

function prodi_import_source($path)
{
    $source = file_get_contents(dirname(__DIR__) . '/' . $path);
    if ($source === FALSE) throw new RuntimeException('Tidak dapat membaca ' . $path);
    return $source;
}

function prodi_import_check($condition, $message)
{
    if (!$condition) throw new RuntimeException($message);
}

$model = prodi_import_source('application/models/Prodi_staf_import_model.php');
$service = prodi_import_source('application/services/Prodi_staf_import_service.php');
$controller = prodi_import_source('application/controllers/lpmpi/Prodi_staf_import.php');
$routes = prodi_import_source('application/config/routes.php');
$index = prodi_import_source('application/views/lpmpi/prodi_staf_import/index.php');
$preview = prodi_import_source('application/views/lpmpi/prodi_staf_import/preview.php');
$profile = prodi_import_source('application/views/lpmpi/profil/index.php');
$master = prodi_import_source('application/views/lpmpi/master_data_prodi_staf/index.php');
$profile_model = prodi_import_source('application/models/Profil_model.php');
$pddikti = prodi_import_source('application/services/Pddikti_service.php');

prodi_import_check(strpos($controller, 'extends Admin_Lpmpi_Controller') !== FALSE, 'Import Prodi harus memakai guard manajemen.');
foreach (['private function require_post()', 'private_storage_dir(\'tmp\')', 'hash_file(\'sha256\'', 'rename($path, $claim)', 'move_uploaded_file', 'private function preflight_xlsx($path)', 'const XLSX_MAX_ENTRIES = 100'] as $contract) prodi_import_check(strpos($controller, $contract) !== FALSE, 'Controller kehilangan pengamanan: ' . $contract);
prodi_import_check(strpos($controller, "['prodi_import_preview_*.json', 'prodi_import_claim_*.json', 'prodi_staf_preview_*.json', 'prodi_staf_claim_*.json']") !== FALSE, 'Purge harus membersihkan artifact Prodi baru dan Prodi-Staf lama.');
prodi_import_check(strpos($controller, "preg_match('/\\Aprodi_import_preview_[a-f0-9]{24}\\.json\\z/'") !== FALSE && strpos($controller, "userdata('prodi_import_preview')") !== FALSE, 'Claim hanya boleh membaca preview Prodi baru; preview lama harus invalid.');
prodi_import_check(strpos($controller, "filename=\"prodi_validation_errors.csv\"") !== FALSE && strpos($controller, 'Cache-Control: no-store, private') !== FALSE && strpos($controller, 'X-Content-Type-Options: nosniff') !== FALSE && strpos($controller, 'fputcsv($output') !== FALSE, 'CSV error harus private, no-store, nosniff, dan memakai fputcsv.');
prodi_import_check(strpos($controller, "if (empty(\$result['prodi']) && empty(\$result['errors']))") !== FALSE && strpos($controller, "'token' => !empty(\$result['prodi']) ? \$token : ''") !== FALSE, 'Preview semua-error harus tetap dapat diekspor tanpa token konfirmasi.');

foreach (["const PRODI_HEADERS = ['kode_prodi', 'nama_prodi', 'jenjang']", "count(\$info) !== 1 || count(\$sheets) !== 1 || !isset(\$sheets['Prodi'])", "['totalColumns'] !== 3", "rangeToArray('A1:C1'", 'setLoadSheetsOnly([\'Prodi\'])', 'setReadFilter(new ProdiStafImportReadFilter())', 'Formula tidak diizinkan.', "'row_number' => \$row_number"] as $contract) prodi_import_check(strpos($service, $contract) !== FALSE, 'Parser Prodi kehilangan kontrak: ' . $contract);
prodi_import_check(strpos($service, '$valid_prodi = []; $seen_codes = []; $errors = []; $total = 0;') !== FALSE && strpos($service, "if (\$row === NULL) continue;\n                 \$total++;") !== FALSE && strpos($service, "'total' => \$total") !== FALSE, 'Total preview hanya boleh menghitung baris data Prodi yang tidak kosong.');
foreach (['STAF_HEADERS', "'staf'", 'find_user', 'staf_prodi', 'users', 'email', 'nidn_nip', 'jabatan', 'fakultas', 'jumlah_mahasiswa'] as $forbidden) prodi_import_check(stripos($service, $forbidden) === FALSE, 'Service import tidak boleh memuat jalur Staf: ' . $forbidden);
foreach (['users', 'staf_prodi', 'find_user', 'upsert_staf'] as $forbidden) prodi_import_check(stripos($model, $forbidden) === FALSE, 'Model import tidak boleh query atau menulis Staf/users: ' . $forbidden);
prodi_import_check(strpos($service, "array_diff(array_keys(\$payload), ['prodi', 'errors', 'total'])") !== FALSE && strpos($service, "count(\$payload['prodi']) + count(\$payload['errors']) === \$payload['total']") !== FALSE, 'Payload confirm harus tepat prodi/errors/total.');
prodi_import_check(strpos($service, "'row_number' => 'integer'") !== FALSE, 'Validasi payload harus menerima row_number PHP bertipe integer setelah JSON round-trip.');
prodi_import_check(strpos($service, "['Prodi', '-']") !== FALSE && strpos($service, "\$error['sheet'] === '-' && \$error['row'] === 0") !== FALSE, 'Validator laporan error hanya menerima Prodi baris 2-1001 atau workbook - baris 0.');
foreach (['trans_begin()', 'FOR UPDATE', 'trans_commit()', 'trans_rollback()', "GET_LOCK('prodi_import_confirm', 10)"] as $contract) prodi_import_check(strpos($service . $model, $contract) !== FALSE, 'Confirm Prodi harus tetap atomik: ' . $contract);

foreach (['lpmpi/prodi-import', 'lpmpi/prodi-import/template', 'lpmpi/prodi-import/preview', 'lpmpi/prodi-import/errors', 'lpmpi/prodi-import/confirm', 'lpmpi/prodi-import/cancel', 'lpmpi/prodi-staf-import', 'lpmpi/prodi-staf-import/template', 'lpmpi/prodi-staf-import/preview', 'lpmpi/prodi-staf-import/errors', 'lpmpi/prodi-staf-import/confirm', 'lpmpi/prodi-staf-import/cancel'] as $route) prodi_import_check(strpos($routes, $route) !== FALSE, 'Route canonical atau alias hilang: ' . $route);
foreach ([$index, $preview] as $view) prodi_import_check(strpos($view, 'form_open') !== FALSE && strpos($view, 'html_escape') !== FALSE, 'View import harus memakai form CSRF dan escaping.');
foreach ([$index, $preview] as $view) foreach (['fakultas', 'jumlah_mahasiswa', 'STAF_HEADERS'] as $forbidden) prodi_import_check(stripos($view, $forbidden) === FALSE, 'View import tidak boleh menyebut jalur Staf/deferred: ' . $forbidden);
prodi_import_check(strpos($index, "lpmpi/prodi-import/preview") !== FALSE && strpos($preview, "lpmpi/prodi-import/errors") !== FALSE && strpos($preview, "lpmpi/prodi-import/confirm") !== FALSE && strpos($preview, "lpmpi/prodi-import/cancel") !== FALSE, 'Form import harus memakai route canonical.');
prodi_import_check(strpos($profile, "site_url('lpmpi/prodi-import')") === FALSE && strpos($profile, 'Kelola Staf') === FALSE && strpos($master, "site_url('lpmpi/prodi-import')") !== FALSE && strpos($master, 'Lihat Staf') !== FALSE, 'Import dan roster Prodi hanya boleh ditautkan dari Master Data.');
prodi_import_check(strpos($controller, "redirect('lpmpi/master-data-prodi-staf')") !== FALSE && strpos($index, "lpmpi/master-data-prodi-staf") !== FALSE, 'Import page dan confirm/cancel harus kembali ke Master Data.');
prodi_import_check(strpos($profile_model, 'function replace_prodi($rows)') !== FALSE && strpos($profile_model, 'staf_prodi') !== FALSE && strpos($pddikti, '/pt/prodi/') !== FALSE, 'PDDikti dan preservasi roster harus tetap terpisah dari import.');

fwrite(STDOUT, "Prodi import regression checks passed.\n");
