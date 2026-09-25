<?php

$root = dirname(__DIR__);

function profile_manual_source($path)
{
    $source = file_get_contents(dirname(__DIR__) . DIRECTORY_SEPARATOR . $path);
    if ($source === FALSE) {
        throw new RuntimeException('Tidak dapat membaca ' . $path);
    }

    return $source;
}

function profile_manual_check($condition, $message)
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$routes = profile_manual_source('application/config/routes.php');
$controller = profile_manual_source('application/controllers/Profil.php');
$model = profile_manual_source('application/models/Profil_model.php');
$service = profile_manual_source('application/services/Profil_service.php');
$index = profile_manual_source('application/views/lpmpi/profil/index.php');
$prodi_form = profile_manual_source('application/views/lpmpi/profil/prodi_form.php');
$mahasiswa_form = profile_manual_source('application/views/lpmpi/profil/mahasiswa_stat_form.php');

foreach (['profil/prodi/create', 'profil/prodi/store', 'profil/prodi/edit/(:num)', 'profil/prodi/update/(:num)', 'profil/prodi/delete/(:num)', 'profil/mahasiswa/create', 'profil/mahasiswa/store', 'profil/mahasiswa/edit/(:num)', 'profil/mahasiswa/update/(:num)', 'profil/mahasiswa/delete/(:num)'] as $route) {
    profile_manual_check(strpos($routes, $route) !== FALSE, 'Route data profil manual hilang: ' . $route);
}

foreach (['prodi_create', 'prodi_store', 'prodi_edit', 'prodi_update', 'prodi_delete', 'mahasiswa_create', 'mahasiswa_store', 'mahasiswa_edit', 'mahasiswa_update', 'mahasiswa_delete'] as $method) {
    profile_manual_check(strpos($controller, 'function ' . $method . '(') !== FALSE, 'Aksi controller profil manual hilang: ' . $method);
}

foreach (['require_manage()', 'require_schema_ready()', 'require_post()', 'Profil_service'] as $literal) {
    profile_manual_check(strpos($controller, $literal) !== FALSE, 'Kontrak controller profil manual hilang: ' . $literal);
}

foreach (['find_prodi', 'create_prodi', 'update_prodi', 'delete_prodi', 'find_mahasiswa_stat', 'create_mahasiswa_stat', 'update_mahasiswa_stat', 'delete_mahasiswa_stat'] as $method) {
    profile_manual_check(strpos($model, 'function ' . $method . '(') !== FALSE, 'Operasi model profil manual hilang: ' . $method);
}
profile_manual_check(strpos($model, "->where('id', (int) \$id)") !== FALSE, 'Mutasi profil manual harus dibatasi oleh primary key.');

foreach (['create_prodi', 'update_prodi', 'delete_prodi', 'create_mahasiswa_stat', 'update_mahasiswa_stat', 'delete_mahasiswa_stat', "'success' =>", "'message' =>", 'DateTime::createFromFormat', 'nama_prodi', 'jenjang', 'jumlah'] as $literal) {
    profile_manual_check(strpos($service, $literal) !== FALSE, 'Kontrak service profil manual hilang: ' . $literal);
}
profile_manual_check(strpos($service, 'replace_prodi') === FALSE && strpos($service, 'replace_mahasiswa_stats') === FALSE, 'CRUD manual tidak boleh memakai penggantian massal PDDikti.');

foreach ([$prodi_form, $mahasiswa_form] as $view) {
    profile_manual_check(strpos($view, 'form_open(') !== FALSE && strpos($view, 'html_escape') !== FALSE, 'Form profil manual harus memakai form_open dan html_escape.');
}
profile_manual_check(strpos($prodi_form, 'id_prodi_pddikti') === FALSE, 'ID Prodi PDDikti tidak boleh diedit manual.');
profile_manual_check(strpos($mahasiswa_form, 'type="number"') !== FALSE && strpos($mahasiswa_form, 'min="0"') !== FALSE, 'Form statistik mahasiswa harus membatasi jumlah non-negatif.');
profile_manual_check(strpos($index, '$can_manage') !== FALSE && strpos($index, 'profil/prodi/create') !== FALSE && strpos($index, 'profil/mahasiswa/create') !== FALSE, 'Aksi manual hanya boleh tampil untuk pengelola.');

fwrite(STDOUT, "Profile manual data regression checks passed.\n");
