<?php

function prodi_staf_manual_source($path)
{
    $source = file_get_contents(dirname(__DIR__) . '/' . $path);
    if ($source === FALSE) throw new RuntimeException('Tidak dapat membaca ' . $path);
    return $source;
}

function prodi_staf_manual_check($condition, $message)
{
    if (!$condition) throw new RuntimeException($message);
}

$model = prodi_staf_manual_source('application/models/Prodi_staf_model.php');
$service = prodi_staf_manual_source('application/services/Prodi_staf_service.php');
$controller = prodi_staf_manual_source('application/controllers/Profil.php');
$routes = prodi_staf_manual_source('application/config/routes.php');
$view = prodi_staf_manual_source('application/views/lpmpi/profil/prodi_staf.php');
$profile_view = prodi_staf_manual_source('application/views/lpmpi/profil/index.php');
$master_view = prodi_staf_manual_source('application/views/lpmpi/master_data_prodi_staf/index.php');
$pddikti = prodi_staf_manual_source('application/services/Pddikti_service.php');

foreach (['profil/prodi/(:num)/staf', 'profil/prodi/(:num)/staf/add', 'profil/prodi/(:num)/staf/update/(:num)', 'profil/prodi/(:num)/staf/move/(:num)', 'profil/prodi/(:num)/staf/deactivate/(:num)', 'profil/prodi/(:num)/staf/reactivate/(:num)'] as $route) {
    prodi_staf_manual_check(strpos($routes, $route) !== FALSE, 'Route roster numerik hilang: ' . $route);
}
foreach (['prodi_staf(', 'prodi_staf_add(', 'prodi_staf_update(', 'prodi_staf_move(', 'prodi_staf_deactivate(', 'prodi_staf_reactivate('] as $method) {
    prodi_staf_manual_check(strpos($controller, 'function ' . $method) !== FALSE, 'Endpoint roster hilang: ' . $method);
}
foreach (['require_manage();', 'require_roster_schema_ready();', 'require_post();', 'roster_prodi($id)', 'show_404()', 'Prodi_staf_service'] as $contract) {
    prodi_staf_manual_check(strpos($controller, $contract) !== FALSE, 'Controller roster kehilangan guard: ' . $contract);
}
prodi_staf_manual_check(strpos($controller, "redirect('profil/prodi/' . (int) \$prodi_id . '/staf')") !== FALSE, 'Mutasi roster harus kembali ke Prodi sumber.');

foreach (['list_eligible_users', 'find_eligible_user', 'find_prodi_pair_for_update', 'find_staf', 'find_pair', 'create_staf', 'update_jabatan', 'move_staf', 'update_status'] as $method) {
    prodi_staf_manual_check(strpos($model, 'function ' . $method . '(') !== FALSE, 'Operasi model roster hilang: ' . $method);
}
foreach (["where_in('role', ['auditor', 'auditee'])", "role IN ('auditor', 'auditee') FOR UPDATE", 'WHERE id = ? AND id_prodi = ? FOR UPDATE', 'WHERE id_akun = ? AND id_prodi = ? FOR UPDATE', "->where('id', (int) \$id)->where('id_prodi', (int) \$prodi_id)"] as $contract) {
    prodi_staf_manual_check(strpos($model, $contract) !== FALSE, 'Model roster harus role/pair-scoped dan lock-capable: ' . $contract);
}
prodi_staf_manual_check(strpos($model, "get('users')->result()") !== FALSE && strpos($model, "->update('users'") === FALSE && strpos($model, "->insert('users'") === FALSE && strpos($model, "->delete('users'") === FALSE, 'Roster hanya boleh membaca akun tanpa mutasi users.');

foreach (['trans_begin()', 'trans_commit()', 'trans_rollback()', 'find_prodi($prodi_id, TRUE)', 'find_pair($account_id, $prodi_id, TRUE)', "'active'", "'inactive'", 'mb_strlen($value) > 100', 'return $value === \'\' ? NULL : $value'] as $contract) {
    prodi_staf_manual_check(strpos($service, $contract) !== FALSE, 'Service roster kehilangan kontrak transaksi/lifecycle: ' . $contract);
}
prodi_staf_manual_check(strpos($service, 'find_eligible_user($account_id, TRUE)') !== FALSE, 'Tambah staf harus recheck role akun pada transaksi.');
prodi_staf_manual_check(strpos($service, 'Akun sudah memiliki relasi dengan program studi ini.') !== FALSE, 'Tambah harus menolak pasangan aktif maupun nonaktif yang sudah ada.');
prodi_staf_manual_check(strpos($service, '$staf->status !== \'active\'') !== FALSE && strpos($service, 'Program studi tujuan harus berbeda.') !== FALSE && strpos($service, 'Akun sudah memiliki relasi dengan program studi tujuan.') !== FALSE, 'Pindah harus aktif-only dan menolak target konflik.');
prodi_staf_manual_check(strpos($service, "transition(\$prodi_id, \$staf_id, 'active', 'inactive'") !== FALSE && strpos($service, "transition(\$prodi_id, \$staf_id, 'inactive', 'active'") !== FALSE, 'Deactivate/reactivate harus state-specific.');
prodi_staf_manual_check(strpos($service, 'move_staf($staf_id, $source_prodi_id, $target_prodi_id)') !== FALSE, 'Pindah harus mengubah relasi aktif yang sama, bukan membuat relasi baru.');
prodi_staf_manual_check(strpos($model, 'SELECT * FROM profil_prodi WHERE id IN (?, ?) ORDER BY id ASC FOR UPDATE') !== FALSE && strpos($service, 'find_prodi_pair_for_update($source_prodi_id, $target_prodi_id)') !== FALSE, 'Pindah dua arah harus mengunci Prodi sumber/tujuan dalam urutan ID stabil sebelum validasi.');

prodi_staf_manual_check(strpos($view, 'form_open(') !== FALSE && strpos($view, 'html_escape(') !== FALSE, 'View roster harus memakai form CSRF dan escaping.');
prodi_staf_manual_check(strpos($view, 'password') === FALSE && strpos($view, 'credential') === FALSE, 'View roster tidak boleh mengekspos kredensial.');
prodi_staf_manual_check(strpos($master_view, "site_url('profil/prodi/' . (int) \$row->id . '/staf')") !== FALSE, 'Master Data harus memiliki link roster setiap Prodi.');
prodi_staf_manual_check(strpos($profile_view, 'Kelola Staf') === FALSE && strpos($profile_view, 'profil/prodi/') === FALSE, 'Profil tidak boleh merender UI roster Prodi.');
prodi_staf_manual_check(strpos($controller, "'active_menu' => 'master_data_prodi_staf'") !== FALSE && strpos($view, "master-data-prodi-staf") !== FALSE, 'Roster harus memakai konteks aktif dan Back Master Data.');
prodi_staf_manual_check(strpos($pddikti, 'class Pddikti_service') !== FALSE && strpos($service, 'Pddikti') === FALSE, 'Roster manual tidak boleh menggantikan perilaku PDDikti.');

fwrite(STDOUT, "Prodi staf manual regression checks passed.\n");
