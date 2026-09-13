<?php

$root = dirname(__DIR__);

function users_consolidation_source($path)
{
    $value = file_get_contents(dirname(__DIR__) . DIRECTORY_SEPARATOR . $path);
    if ($value === FALSE) {
        throw new RuntimeException('Tidak dapat membaca ' . $path);
    }
    return $value;
}

function users_consolidation_check($condition, $message)
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$users = users_consolidation_source('application/controllers/Users.php');
$akun = users_consolidation_source('application/controllers/lpmpi/Akun.php');
$service = users_consolidation_source('application/services/User_service.php');
$model = users_consolidation_source('application/models/User_model.php');
$routes = users_consolidation_source('application/config/routes.php');
$index_view = users_consolidation_source('application/views/users/index.php');
$create_view = users_consolidation_source('application/views/users/create.php');
$edit_view = users_consolidation_source('application/views/users/edit.php');
$sidebar_view = users_consolidation_source('application/views/layouts/sidebar.php');

users_consolidation_check(strpos($users, 'class Users extends Admin_Lpmpi_Controller') !== FALSE, 'Users harus memakai Admin_Lpmpi_Controller.');
users_consolidation_check(strpos($users, "'actor_role' => \$this->session->userdata('role')") !== FALSE, 'Users harus meneruskan role aktor ke service.');
users_consolidation_check(strpos($users, "\$data['users'] = \$this->user_service->get_all_users(\$filters);") !== FALSE, 'Users index harus memakai service canonical untuk list.');
users_consolidation_check(strpos($users, "'nama_unit' => \$this->input->post('nama_unit', TRUE)") !== FALSE && strpos($users, "'jenis_unit' => \$this->input->post('jenis_unit', TRUE)") !== FALSE, 'Users harus meneruskan field unit canonical.');
users_consolidation_check(substr_count($users, '$this->require_post();') >= 3, 'Store/update/delete Users harus POST-only.');

users_consolidation_check(strpos($service, 'function allowed_managed_roles') !== FALSE, 'Service harus punya policy role aktor.');
users_consolidation_check(strpos($service, "\$allowed_roles = \$this->allowed_managed_roles(isset(\$filters['actor_role']) ? \$filters['actor_role'] : '')") !== FALSE, 'List pengguna harus memakai actor_role dari filter.');
users_consolidation_check(strpos($service, 'array_filter($users, function ($user) use ($allowed_roles)') !== FALSE, 'List pengguna harus difilter ulang server-side sesuai policy aktor.');
users_consolidation_check(strpos($service, "'super_admin' => self::ALLOWED_ROLES") !== FALSE, 'Super admin harus boleh mengelola semua role.');
users_consolidation_check(strpos($service, "'admin_lpmpi' => ['auditor', 'auditee']") !== FALSE, 'Admin LPMPI hanya boleh mengelola auditor/auditee.');
users_consolidation_check(strpos($service, 'create_lpmpi_account') === FALSE && strpos($service, 'update_lpmpi_account') === FALSE && strpos($service, 'delete_lpmpi_account') === FALSE, 'Method CRUD legacy Akun harus dihapus dari service.');
users_consolidation_check(strpos($service, 'is_valid_unit_fields') !== FALSE && strpos($service, "in_array(\$data['jenis_unit'], ['prodi', 'unit', 'lembaga'], TRUE)") !== FALSE, 'Validasi unit auditee harus canonical dan terikat jenis yang diizinkan.');
users_consolidation_check(strpos($service, 'dependency = $this->user_model->user_dependency_category') !== FALSE, 'Service harus memakai dependency blocker model.');
users_consolidation_check(strpos($service, 'Super Admin terakhir tidak dapat dihapus.') !== FALSE, 'Delete harus melindungi super admin terakhir.');
users_consolidation_check(strpos($service, 'Anda tidak dapat menghapus akun yang sedang digunakan.') !== FALSE, 'Delete harus blok akun sesi sendiri.');

users_consolidation_check(strpos($model, "or_like('nama_unit', \$filters['q'])") !== FALSE, 'Search Users harus mencakup nama_unit.');
users_consolidation_check(strpos($model, 'function user_dependency_category') !== FALSE, 'Model harus punya lookup dependency user.');
foreach (['tugas_audit', 'spmi_audit_assignments', 'spmi_audit_cycles', 'spmi_versions', 'spmi_reports', 'spmi_rtm_meetings', 'spmi_rtm_participants', 'spmi_rtm_follow_ups', 'user_unit_assignments'] as $table) {
    users_consolidation_check(strpos($model, "'table' => '" . $table . "'") !== FALSE, 'Dependency table wajib dicek: ' . $table);
}
foreach (['auditor_id', 'auditee_id', 'created_by', 'generated_by', 'resolved_by', 'user_id', 'responsible_user_id', 'started_by', 'completed_by'] as $field) {
    users_consolidation_check(strpos($model, "'" . $field . "'") !== FALSE, 'Dependency field wajib dicek: ' . $field);
}
users_consolidation_check(strpos($model, 'table_exists') !== FALSE && strpos($model, 'field_exists') !== FALSE, 'Dependency lookup harus guard variasi schema test.');

foreach (['Nama', 'Email', 'Role', 'Unit', 'Jenis Unit', 'Dibuat', 'Aksi'] as $column) {
    users_consolidation_check(strpos($index_view, '>' . $column . '<') !== FALSE, 'Users index harus memuat kolom: ' . $column);
}
users_consolidation_check(strpos($index_view, "user->role === 'auditee'") !== FALSE, 'Users index harus menampilkan unit hanya untuk auditee.');
users_consolidation_check(strpos($index_view, 'tidak dapat dihapus jika masih memiliki data terkait') !== FALSE, 'Delete copy harus menjelaskan dependency blocker.');
users_consolidation_check(strpos($create_view, 'data-role-select') !== FALSE && strpos($create_view, 'name="nama_unit"') !== FALSE && strpos($create_view, 'name="jenis_unit"') !== FALSE, 'Create Users harus punya selector role dan field unit canonical.');
users_consolidation_check(strpos($edit_view, 'data-role-select') !== FALSE && strpos($edit_view, '$user->nama_unit') !== FALSE && strpos($edit_view, '$user->jenis_unit') !== FALSE, 'Edit Users harus mempertahankan nilai unit.');
users_consolidation_check(strpos($create_view, 'bg-dark') === FALSE && strpos($edit_view, 'bg-dark') === FALSE, 'Form Users tidak boleh memakai style dark legacy.');
users_consolidation_check(substr_count($sidebar_view, "['key' => 'akun'") === 0 && substr_count($sidebar_view, "['key' => 'users'") === 2, 'Sidebar harus memakai menu Users canonical tanpa Akun.');

users_consolidation_check(strpos($akun, 'class Akun extends Admin_Lpmpi_Controller') !== FALSE, 'Akun bridge harus tetap Admin_Lpmpi_Controller.');
users_consolidation_check(strpos($akun, 'create_lpmpi_account') === FALSE && strpos($akun, 'update_lpmpi_account') === FALSE && strpos($akun, 'delete_lpmpi_account') === FALSE, 'Akun bridge tidak boleh memanggil method CRUD legacy.');
users_consolidation_check(substr_count($akun, 'redirect(') >= 6 && strpos($akun, 'function canonical_index_uri') !== FALSE, 'Akun bridge harus redirect-only dengan filter aman.');
users_consolidation_check(strpos($akun, "in_array(\$role, ['auditor', 'auditee'], TRUE)") !== FALSE, 'Akun bridge hanya boleh forward role auditor/auditee.');

foreach ([
    "\$route['lpmpi/akun'] = 'lpmpi/Akun/index';",
    "\$route['lpmpi/akun/create'] = 'lpmpi/Akun/create';",
    "\$route['lpmpi/akun/store'] = 'lpmpi/Akun/store';",
    "\$route['lpmpi/akun/edit/(:num)'] = 'lpmpi/Akun/edit/$1';",
    "\$route['lpmpi/akun/update/(:num)'] = 'lpmpi/Akun/update/$1';",
    "\$route['lpmpi/akun/delete/(:num)'] = 'lpmpi/Akun/delete/$1';",
] as $route) {
    users_consolidation_check(strpos($routes, $route) !== FALSE, 'Route lama Akun wajib eksplisit: ' . $route);
}

fwrite(STDOUT, "Users consolidation regression checks passed.\n");
