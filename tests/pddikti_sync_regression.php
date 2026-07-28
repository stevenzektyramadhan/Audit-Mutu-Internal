<?php

$root = dirname(__DIR__);

if (!defined('BASEPATH')) {
    define('BASEPATH', $root . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR);
}
if (!defined('APPPATH')) {
    define('APPPATH', $root . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR);
}

function source($root, $path)
{
    $contents = file_get_contents($root . DIRECTORY_SEPARATOR . $path);
    if ($contents === FALSE) {
        throw new RuntimeException('Tidak dapat membaca ' . $path);
    }
    return $contents;
}

function check($condition, $message)
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function private_property($object, $property)
{
    $ref = new ReflectionClass($object);
    $prop = $ref->getProperty($property);
    $prop->setAccessible(TRUE);
    return $prop->getValue($object);
}

require_once $root . DIRECTORY_SEPARATOR . 'application/services/Pddikti_service.php';

putenv('PDDIKTI_BASE_URLS=https://local-pddikti.example/api/, https://backup-pddikti.example/api, https://local-pddikti.example/api');
$service = new Pddikti_service();
check(private_property($service, 'base_urls') === [
    'https://local-pddikti.example/api',
    'https://backup-pddikti.example/api',
], 'PDDIKTI_BASE_URLS harus dibaca sebagai daftar comma-separated, trim slash, dan dedupe.');
check(private_property($service, 'active_base_url') === 'https://local-pddikti.example/api', 'Base URL aktif harus memakai env pertama.');

putenv('PDDIKTI_BASE_URLS');
$default_service = new Pddikti_service();
check(private_property($default_service, 'base_urls') === [
    'https://pddikti.rone.dev/api',
    'https://pddikti.fastapicloud.dev/api',
], 'Default proxy PDDikti harus tetap pddikti.rone.dev dan pddikti.fastapicloud.dev.');

$constants = [
    'source_unavailable' => Pddikti_service::ERROR_SOURCE_UNAVAILABLE,
    'not_found' => Pddikti_service::ERROR_NOT_FOUND,
    'rate_limited' => Pddikti_service::ERROR_RATE_LIMITED,
    'client_error' => Pddikti_service::ERROR_CLIENT_ERROR,
    'server_error' => Pddikti_service::ERROR_SERVER_ERROR,
    'transport' => Pddikti_service::ERROR_TRANSPORT,
    'invalid_response' => Pddikti_service::ERROR_INVALID_RESPONSE,
];
check(count(array_unique(array_values($constants))) === count($constants), 'Setiap klasifikasi PDDikti harus punya Exception code stabil unik.');

$service_source = source($root, 'application/services/Pddikti_service.php');
foreach ($constants as $classification => $code) {
    check(strpos($service_source, "'" . $classification . "' => self::ERROR_") !== FALSE, 'Mapping code hilang untuk ' . $classification . '.');
}
foreach ([503 => 'source_unavailable', 408 => 'source_unavailable', 404 => 'not_found', 429 => 'rate_limited'] as $status => $classification) {
    check(strpos($service_source, '$status === ' . $status) !== FALSE, 'HTTP ' . $status . ' harus diklasifikasi sebagai ' . $classification . '.');
}
check(strpos($service_source, '$status >= 400 && $status < 500') !== FALSE, 'HTTP 4xx selain 404/408/429 harus client_error.');
check(strpos($service_source, '$status >= 500') !== FALSE, 'HTTP 5xx selain 503 harus server_error.');
check(strpos($service_source, "'transport'") !== FALSE && strpos($service_source, "'invalid_response'") !== FALSE, 'Transport dan invalid_response harus termapped.');
check(strpos($service_source, 'sleep($attempt)') !== FALSE && strpos($service_source, '$attempts = 3') !== FALSE, 'Retry/backoff PDDikti harus tetap ada.');
check(strpos($service_source, '$status >= 500 || $status === 429 || $status === 408') !== FALSE, 'HTTP 408 harus ikut retry/fallback seperti source_unavailable.');
foreach (['/search/pt/', '/pt/detail/', '/pt/prodi/', '/pt/rasio/', '/pt/mahasiswa/', '/pt/logo/'] as $endpoint) {
    check(strpos($service_source, $endpoint) !== FALSE, 'Endpoint PDDikti berubah atau hilang: ' . $endpoint);
}

$profil = source($root, 'application/controllers/Profil.php');
$sinkronisasi_start = strpos($profil, 'public function sinkronisasi()');
$upload_logo_start = strpos($profil, 'public function upload_logo()');
check($sinkronisasi_start !== FALSE && $upload_logo_start !== FALSE && $upload_logo_start > $sinkronisasi_start, 'Method sinkronisasi Profil harus tetap ada.');
$sinkronisasi = substr($profil, $sinkronisasi_start, $upload_logo_start - $sinkronisasi_start);

check(strpos($sinkronisasi, '$this->require_manage();') !== FALSE
    && strpos($sinkronisasi, '$this->require_schema_ready();') !== FALSE
    && strpos($sinkronisasi, '$this->require_post();') !== FALSE,
    'Sinkronisasi profil harus tetap role-protected, schema-ready, dan POST-only.');
check(strpos($sinkronisasi, '$this->pddikti_sync_error_message($exception)') !== FALSE, 'Catch sinkronisasi harus memakai mapper flash PDDikti.');
check(strpos($sinkronisasi, 'log_message(\'error\', \'Sinkronisasi PDDikti gagal untuk user \' . $this->_user_id() . \': \' . $exception->getMessage())') !== FALSE, 'Detail exception PDDikti harus tetap masuk log private.');

$busy_message = 'Layanan PDDikti sedang sibuk atau tidak tersedia. Data profil lokal tetap dipertahankan. Coba lagi beberapa saat.';
$not_found_message = 'Data perguruan tinggi tidak ditemukan di PDDikti. Periksa nama PT atau ID PT.';
$invalid_message = 'Respons PDDikti tidak valid. Coba lagi nanti.';
$generic_message = 'Sinkronisasi PDDikti gagal. Coba lagi nanti.';
foreach ([$busy_message, $not_found_message, $invalid_message, $generic_message] as $message) {
    check(strpos($profil, $message) !== FALSE, 'Pesan flash PDDikti hilang: ' . $message);
}
foreach (['ERROR_SOURCE_UNAVAILABLE', 'ERROR_RATE_LIMITED', 'ERROR_TRANSPORT', 'ERROR_NOT_FOUND', 'ERROR_INVALID_RESPONSE'] as $constant) {
    check(strpos($profil, 'Pddikti_service::' . $constant) !== FALSE, 'Controller tidak memetakan ' . $constant . '.');
}

$catch_pos = strpos($sinkronisasi, '} catch (Exception $exception) {');
$trans_pos = strpos($sinkronisasi, '$this->db->trans_start();');
$upsert_pos = strpos($sinkronisasi, '$this->Profil_model->upsert_profil');
check($catch_pos !== FALSE && $trans_pos !== FALSE && $upsert_pos !== FALSE && $catch_pos < $trans_pos && $catch_pos < $upsert_pos, 'Total remote failure harus berhenti sebelum transaksi dan write DB.');
check(strpos(substr($sinkronisasi, $catch_pos, $trans_pos - $catch_pos), 'last_sync_at') === FALSE, 'last_sync_at tidak boleh disentuh pada total remote failure.');
check(strpos($service_source, "\$profil['last_sync_at'] = date('Y-m-d H:i:s');") !== FALSE, 'last_sync_at harus diset hanya setelah profil remote dimap untuk upsert sukses.');

check(strpos($sinkronisasi, '$replace_prodi = isset($result[\'prodi\']) && is_array($result[\'prodi\']) && !empty($result[\'prodi\']);') !== FALSE, 'replace_prodi harus hanya true saat data remote non-empty.');
check(strpos($sinkronisasi, '$replace_mahasiswa_stats = isset($result[\'mahasiswa_stats\']) && is_array($result[\'mahasiswa_stats\']) && !empty($result[\'mahasiswa_stats\']);') !== FALSE, 'replace_mahasiswa_stats harus hanya true saat data remote non-empty.');
check(strpos($sinkronisasi, 'if ($replace_prodi)') !== FALSE && strpos($sinkronisasi, '$this->Profil_model->replace_prodi($result[\'prodi\']);') !== FALSE, 'Data prodi lokal hanya boleh diganti saat replace_prodi true.');
check(strpos($sinkronisasi, 'if ($replace_mahasiswa_stats)') !== FALSE && strpos($sinkronisasi, '$this->Profil_model->replace_mahasiswa_stats($result[\'mahasiswa_stats\']);') !== FALSE, 'Data mahasiswa lokal hanya boleh diganti saat replace_mahasiswa_stats true.');
check(strpos($sinkronisasi, 'Data program studi lokal dipertahankan karena PDDikti tidak mengirim data prodi.') !== FALSE, 'Warning pertahankan prodi lokal harus tetap ada.');
check(strpos($sinkronisasi, 'Data statistik mahasiswa lokal dipertahankan karena PDDikti tidak mengirim data statistik.') !== FALSE, 'Warning pertahankan statistik mahasiswa lokal harus tetap ada.');

$model = source($root, 'application/models/Profil_model.php');
check(strpos($model, 'function replace_prodi($rows)') !== FALSE && strpos($model, '$this->db->empty_table($this->prodi_table);') !== FALSE, 'Model replace_prodi tetap destructive hanya jika controller memanggilnya.');
check(strpos($model, 'function replace_mahasiswa_stats($rows)') !== FALSE && strpos($model, '$this->db->empty_table($this->mahasiswa_table);') !== FALSE, 'Model replace_mahasiswa_stats tetap destructive hanya jika controller memanggilnya.');

fwrite(STDOUT, "PDDikti sync regression checks passed.\n");
