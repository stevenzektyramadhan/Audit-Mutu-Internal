<?php

$root = dirname(__DIR__);

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

$account = source($root, 'application/controllers/Account.php');
$service = source($root, 'application/services/Account_service.php');
$model = source($root, 'application/models/User_model.php');
$routes = source($root, 'application/config/routes.php');
$sidebar = source($root, 'application/views/layouts/sidebar.php');
$helper = source($root, 'application/helpers/app_helper.php');
$schema = source($root, 'database_schema.sql');
$migration = source($root, 'migrations/011_add_users_profile_photo_path.sql');

check(strpos($account, 'class Account extends MY_Controller') !== FALSE, 'Account harus memakai MY_Controller.');
check(substr_count($account, '$this->_user_id()') >= 3, 'Akun harus memakai ID sesi untuk semua identitas.');
check(strpos($account, 'function photo()') !== FALSE, 'Endpoint foto tidak boleh menerima parameter identitas.');
check(strpos($account, "method(TRUE) !== 'POST'") !== FALSE, 'Update akun harus POST-only.');
check(strpos($account, 'is_uploaded_file') !== FALSE && strpos($account, 'finfo_file') !== FALSE && strpos($account, 'getimagesize') !== FALSE, 'Upload foto harus memeriksa upload, MIME server, dan image data.');
check(strpos($account, "['image/jpeg', 'image/png']") !== FALSE && strpos($account, '2 * 1024 * 1024') !== FALSE, 'Upload foto harus hanya JPEG/PNG sampai 2 MiB.');
check(strpos($account, "private_storage_dir('user_photos')") !== FALSE && strpos($account, "private_storage_path('user_photos', \$file_name)") !== FALSE, 'Foto akun harus memakai storage private user_photos.');
check(strpos($account, "set_header('Cache-Control: private, no-store, max-age=0')") !== FALSE
    && strpos($account, "set_header('Pragma: no-cache')") !== FALSE
    && strpos($account, "set_header('Expires: 0')") !== FALSE,
    'Respons foto private harus melarang cache lintas sesi.');
check(strpos($account, 'sess_regenerate(TRUE)') !== FALSE, 'Update akun harus meregenerasi sesi.');
check(strpos($model, 'function update_own_profile') !== FALSE, 'Model harus memiliki write path akun sendiri.');
check(strpos($model, 'function find_profile_photo_for_update') !== FALSE
    && strpos($model, "select('profile_photo_path')") !== FALSE
    && strpos($model, "query(\$query . ' FOR UPDATE')") !== FALSE,
    'Model harus membaca foto sebelumnya dengan Query Builder dan FOR UPDATE.');
$transaction_start = strpos($service, '$this->ci->db->trans_start();');
$locked_read = strpos($service, '$this->user_model->find_profile_photo_for_update');
$profile_update = strpos($service, '$this->user_model->update_own_profile');
$transaction_complete = strpos($service, '$this->ci->db->trans_complete();');
check($transaction_start !== FALSE && $locked_read > $transaction_start
    && $profile_update > $locked_read && $transaction_complete > $profile_update,
    'Read FOR UPDATE dan update akun harus berada dalam transaksi yang sama.');
check(strpos($service, "'previous_profile_photo_path' => \$account->profile_photo_path") !== FALSE
    && strpos($account, "delete_private_file('user_photos', \$result['previous_profile_photo_path'])") !== FALSE
    && strpos($account, "delete_private_file('user_photos', \$account->profile_photo_path)") === FALSE,
    'Cleanup foto lama harus memakai filename yang dikunci dan dikembalikan transaksi sukses.');
check(strpos($routes, "\$route['account'] = 'Account/index';") !== FALSE && strpos($routes, "\$route['account/photo'] = 'Account/photo';") !== FALSE, 'Semua route akun eksplisit wajib ada.');
check(strpos($sidebar, "'key' => 'account'") !== FALSE, 'Navigasi semua role harus memuat Akun Saya.');
check(substr_count($sidebar, "'group' => 'Pengaturan'") === 2, 'Profil Lembaga hanya boleh berada pada pengaturan dua role manajemen.');
check(strpos($helper, "'user_photos'") !== FALSE && strpos($helper, "if (\$category === 'user_photos')") !== FALSE, 'Foto akun tidak boleh punya fallback publik legacy.');
check(strpos($schema, '`profile_photo_path` VARCHAR(255) NULL') !== FALSE, 'Baseline schema harus memiliki kolom foto profil.');
check(strpos($migration, 'INFORMATION_SCHEMA.COLUMNS') !== FALSE && strpos($migration, 'profile_photo_path') !== FALSE, 'Migration 011 harus idempotent.');

fwrite(STDOUT, "Account settings regression checks passed.\n");
