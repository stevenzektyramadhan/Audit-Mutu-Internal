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
$master = profile_manual_source('application/views/lpmpi/master_data_prodi_staf/index.php');
$mahasiswa_form = profile_manual_source('application/views/lpmpi/profil/mahasiswa_stat_form.php');

foreach (['profil/prodi/create', 'profil/prodi/store', 'profil/prodi/edit/(:num)', 'profil/prodi/update/(:num)', 'profil/prodi/delete/(:num)', 'profil/mahasiswa/create', 'profil/mahasiswa/store', 'profil/mahasiswa/edit/(:num)', 'profil/mahasiswa/update/(:num)', 'profil/mahasiswa/delete/(:num)'] as $route) {
    profile_manual_check(strpos($routes, $route) !== FALSE, 'Route data profil manual hilang: ' . $route);
}

foreach (['prodi_create', 'prodi_store', 'prodi_edit', 'prodi_update', 'prodi_delete', 'mahasiswa_create', 'mahasiswa_store', 'mahasiswa_edit', 'mahasiswa_update', 'mahasiswa_delete'] as $method) {
    profile_manual_check(strpos($controller, 'function ' . $method . '(') !== FALSE, 'Aksi controller profil manual hilang: ' . $method);
}
foreach (['prodi_staf', 'prodi_staf_add', 'prodi_staf_update', 'prodi_staf_move', 'prodi_staf_deactivate', 'prodi_staf_reactivate'] as $method) {
    profile_manual_check(strpos($controller, 'function ' . $method . '(') !== FALSE, 'Aksi roster staf Prodi hilang: ' . $method);
}

foreach (['require_manage()', 'require_schema_ready()', 'require_post()', 'Profil_service'] as $literal) {
    profile_manual_check(strpos($controller, $literal) !== FALSE, 'Kontrak controller profil manual hilang: ' . $literal);
}

foreach (['find_prodi', 'create_prodi', 'update_prodi', 'delete_prodi', 'find_mahasiswa_stat', 'create_mahasiswa_stat', 'update_mahasiswa_stat', 'delete_mahasiswa_stat'] as $method) {
    profile_manual_check(strpos($model, 'function ' . $method . '(') !== FALSE, 'Operasi model profil manual hilang: ' . $method);
}
profile_manual_check(strpos($model, "->where('id', (int) \$id)") !== FALSE, 'Mutasi profil manual harus dibatasi oleh primary key.');
profile_manual_check(strpos($model, 'function find_prodi_for_update') !== FALSE && strpos($model, 'function prodi_staf_for_update') !== FALSE && strpos($model, 'SELECT id FROM staf_prodi WHERE id_prodi = ? FOR UPDATE') !== FALSE, 'Penghapusan Prodi harus menyediakan lock Prodi dan semua relasi staf, termasuk inactive.');

foreach (['create_prodi', 'update_prodi', 'delete_prodi', 'create_mahasiswa_stat', 'update_mahasiswa_stat', 'delete_mahasiswa_stat', "'success' =>", "'message' =>", 'DateTime::createFromFormat', 'nama_prodi', 'jenjang', 'jumlah'] as $literal) {
    profile_manual_check(strpos($service, $literal) !== FALSE, 'Kontrak service profil manual hilang: ' . $literal);
}
profile_manual_check(strpos($service, 'replace_prodi') === FALSE && strpos($service, 'replace_mahasiswa_stats') === FALSE, 'CRUD manual tidak boleh memakai penggantian massal PDDikti.');
profile_manual_check(strpos($service, 'trans_begin()') !== FALSE && strpos($service, 'find_prodi_for_update($id)') !== FALSE && strpos($service, 'prodi_staf_for_update($id)') !== FALSE && strpos($service, 'trans_commit()') !== FALSE && strpos($service, 'trans_rollback()') !== FALSE, 'Penghapusan Prodi harus memeriksa relasi terkunci dan delete dalam satu transaksi tanpa cascade race.');

foreach ([$prodi_form, $mahasiswa_form] as $view) {
    profile_manual_check(strpos($view, 'form_open(') !== FALSE && strpos($view, 'html_escape') !== FALSE, 'Form profil manual harus memakai form_open dan html_escape.');
}
profile_manual_check(strpos($prodi_form, 'id_prodi_pddikti') === FALSE, 'ID Prodi PDDikti tidak boleh diedit manual.');
profile_manual_check(strpos($mahasiswa_form, 'type="number"') !== FALSE && strpos($mahasiswa_form, 'min="0"') !== FALSE, 'Form statistik mahasiswa harus membatasi jumlah non-negatif.');
profile_manual_check(strpos($controller, "'prodi' => \$prodi") !== FALSE && strpos($controller, 'get_prodi()') !== FALSE, 'Profil harus memasok daftar Prodi terurut dari Profil_model::get_prodi().');
profile_manual_check(strpos($index, '$can_manage') !== FALSE && strpos($index, 'profil/prodi/create') === FALSE && strpos($index, 'lpmpi/prodi-import') === FALSE && strpos($index, 'profil/mahasiswa/create') !== FALSE, 'Profil hanya boleh menampilkan aksi statistik mahasiswa, bukan CRUD Prodi.');
foreach (['Daftar Program Studi', 'kode_prodi', 'nama_prodi', 'jenjang', 'table-responsive', 'html_escape', 'Belum ada data program studi', 'lpmpi/master-data-prodi-staf'] as $literal) {
    profile_manual_check(strpos($index, $literal) !== FALSE, 'Direktori Prodi read-only kehilangan kontrak: ' . $literal);
}
foreach (['profil/prodi/create', 'profil/prodi/edit', 'profil/prodi/delete', 'lpmpi/prodi-import', 'Kelola Staf'] as $forbidden) {
    profile_manual_check(strpos($index, $forbidden) === FALSE, 'Profil tidak boleh memuat aksi/mutasi Prodi: ' . $forbidden);
}
profile_manual_check(strpos($master, 'profil/prodi/create') !== FALSE && strpos($master, 'lpmpi/prodi-import') !== FALSE && strpos($master, 'profil/prodi/edit/') !== FALSE && strpos($master, 'profil/prodi/delete/') !== FALSE, 'Master Data harus menjadi pemilik UI CRUD Prodi.');
profile_manual_check(strpos($prodi_form, "master-data-prodi-staf") !== FALSE, 'Form Prodi harus kembali ke Master Data.');

fwrite(STDOUT, "Profile manual data regression checks passed.\n");
