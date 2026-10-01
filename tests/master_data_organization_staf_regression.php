<?php

function mdos_source($path)
{
    $source = file_get_contents(dirname(__DIR__) . DIRECTORY_SEPARATOR . $path);
    if ($source === FALSE) throw new RuntimeException('Tidak dapat membaca ' . $path);
    return $source;
}

function mdos_check($condition, $message)
{
    if (!$condition) throw new RuntimeException($message);
}

function mdos_table_block($schema, $table)
{
    $pattern = '/CREATE TABLE IF NOT EXISTS `' . preg_quote($table, '/') . '` \((?P<body>.*?)\) ENGINE=InnoDB/s';
    if (!preg_match($pattern, $schema, $matches)) {
        throw new RuntimeException('Tabel schema hilang: ' . $table);
    }

    return $matches['body'];
}

function mdos_has_no_statement($source, $verb, $message)
{
    mdos_check(preg_match('/(?:^|[;$])\s*' . preg_quote($verb, '/') . '\b/i', $source) !== 1, $message);
}

$schema = mdos_source('database_schema.sql');
$migration = mdos_source('migrations/042_link_profil_prodi_to_organization_units.sql');
$readme = mdos_source('README.md');
$controller = mdos_source('application/controllers/lpmpi/Master_data_prodi_staf.php');
$service = mdos_source('application/services/Organization_service.php');
$routes = mdos_source('application/config/routes.php');
$organization_model = mdos_source('application/models/Organization_model.php');
$profil_model = mdos_source('application/models/Profil_model.php');
$profil_service = mdos_source('application/services/Profil_service.php');
$profil_controller = mdos_source('application/controllers/Profil.php');
$view = mdos_source('application/views/lpmpi/master_data_prodi_staf/index.php');
$prodi_link_form = mdos_source('application/views/lpmpi/master_data_prodi_staf/prodi_link_form.php');
$create_form = mdos_source('application/views/lpmpi/master_data_prodi_staf/create_form.php');
$prodi_form = mdos_source('application/views/lpmpi/profil/prodi_form.php');
$unit_detail = mdos_source('application/views/lpmpi/master_data_prodi_staf/unit_detail.php');
$unit_form = mdos_source('application/views/lpmpi/master_data_prodi_staf/unit_form.php');
$placement_form = mdos_source('application/views/lpmpi/master_data_prodi_staf/placement_form.php');

$profil_prodi = mdos_table_block($schema, 'profil_prodi');
$organization_units = mdos_table_block($schema, 'organization_units');
$migration_names = array_values(array_filter(scandir(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'migrations'), function ($name) {
    return preg_match('/^\d{3}_.*\.sql$/', $name) === 1;
}));

mdos_check(in_array('041_create_staf_prodi.sql', $migration_names, TRUE), 'Migration 041 harus tetap ada.');
mdos_check(in_array('042_link_profil_prodi_to_organization_units.sql', $migration_names, TRUE), 'Migration 042 harus ada.');
mdos_check(array_search('041_create_staf_prodi.sql', $migration_names, TRUE) < array_search('042_link_profil_prodi_to_organization_units.sql', $migration_names, TRUE), 'Migration 042 harus berurutan setelah 041.');

mdos_check(strpos($migration, 'INFORMATION_SCHEMA.COLUMNS') !== FALSE, 'Migration 042 harus guard kolom secara idempotent.');
mdos_check(strpos($migration, 'INFORMATION_SCHEMA.STATISTICS') !== FALSE, 'Migration 042 harus guard unique key secara idempotent.');
mdos_check(strpos($migration, 'INFORMATION_SCHEMA.TABLE_CONSTRAINTS') !== FALSE, 'Migration 042 harus guard FK secara idempotent.');
mdos_check(preg_match('/ADD COLUMN `organization_unit_id` INT NULL AFTER `id`/i', $migration) === 1, 'Migration 042 harus menambah organization_unit_id nullable.');
mdos_check(strpos($migration, 'ADD UNIQUE KEY `uq_profil_prodi_organization_unit` (`organization_unit_id`)') !== FALSE, 'Migration 042 harus menambah unique key organization_unit_id.');
mdos_check(strpos($migration, 'ADD CONSTRAINT `fk_profil_prodi_organization_unit`') !== FALSE, 'Migration 042 harus menambah FK profil_prodi ke organization_units.');
mdos_check(strpos($migration, 'FOREIGN KEY (`organization_unit_id`) REFERENCES `organization_units` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT') !== FALSE, 'Migration 042 FK harus RESTRICT untuk delete dan update.');
mdos_check(strpos($migration, 'CREATE TABLE') === FALSE, 'Migration 042 tidak boleh membuat tabel baru.');
mdos_check(strpos($migration, 'FOREIGN_KEY_CHECKS') === FALSE, 'Migration 042 tidak boleh menonaktifkan foreign key checks.');
foreach (['INSERT', 'UPDATE', 'DELETE'] as $verb) mdos_has_no_statement($migration, $verb, 'Migration 042 tidak boleh menjalankan DML: ' . $verb);

mdos_check(strpos($schema, 'current parity migration 001-041') !== FALSE, 'Marker historis 001-041 harus tetap ada.');
mdos_check(strpos($schema, 'current parity migration 001-043') !== FALSE, 'Marker parity 001-043 harus ada.');
mdos_check(strpos($profil_prodi, '`organization_unit_id` INT NULL') !== FALSE, 'Bootstrap schema profil_prodi harus punya organization_unit_id nullable.');
mdos_check(strpos($profil_prodi, 'UNIQUE KEY `uq_profil_prodi_organization_unit` (`organization_unit_id`)') !== FALSE, 'Bootstrap schema profil_prodi harus punya unique key organization_unit_id.');
mdos_check(strpos($organization_units, "ENUM('university','faculty','upps','study_program','institute','bureau','unit')") !== FALSE, 'Bootstrap schema organization_units harus mempertahankan tipe UPPS.');

$organization_table_pos = strpos($schema, 'CREATE TABLE IF NOT EXISTS `organization_units`');
$fk_pos = strpos($schema, 'ADD CONSTRAINT `fk_profil_prodi_organization_unit`');
mdos_check($organization_table_pos !== FALSE && $fk_pos !== FALSE && $organization_table_pos < $fk_pos, 'Bootstrap FK profil_prodi harus dideklarasikan setelah organization_units.');
mdos_check(strpos($schema, 'FOREIGN KEY (`organization_unit_id`) REFERENCES `organization_units` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT') !== FALSE, 'Bootstrap FK profil_prodi harus RESTRICT untuk delete dan update.');

mdos_check(strpos($readme, 'parity migration `001-043`') !== FALSE, 'README harus mendokumentasikan parity 001-043.');
mdos_check(strpos($readme, '`042_link_profil_prodi_to_organization_units.sql`') !== FALSE, 'README harus mencantumkan migration 042.');
mdos_check(strpos($readme, 'nullable `profil_prodi.organization_unit_id`') !== FALSE, 'README harus menjelaskan link nullable.');
mdos_check(strpos($readme, 'tidak melakukan auto-map') !== FALSE, 'README harus melarang auto-map fase 042.');
mdos_check(strpos($readme, 'backup database lengkap dan `APP_PRIVATE_STORAGE_PATH`') !== FALSE, 'README harus menyebut backup deployment untuk migration 042.');

mdos_check(strpos($controller, 'class Master_data_prodi_staf extends Admin_Lpmpi_Controller') !== FALSE, 'Controller direktori organisasi harus mempertahankan guard Admin_Lpmpi_Controller.');
mdos_check(strpos($controller, "method(TRUE) !== 'GET'") !== FALSE, 'Controller index direktori organisasi harus tetap GET-only.');
mdos_check(strpos($controller, "'page_title' => 'Master Data Organisasi & Staf'") !== FALSE, 'Controller harus mengirim judul canonical Master Data Organisasi & Staf.');
mdos_check(strpos($controller, "load->view('lpmpi/master_data_prodi_staf/index'") !== FALSE && strpos($controller, "'active_menu' => 'master_data_prodi_staf'") !== FALSE, 'Controller harus mempertahankan view, URL internal, dan active menu lama.');
foreach (['organization_units', 'organization_summary', 'summary', 'prodi_directory', 'mapped_prodi_count', 'unmapped_prodi_count', 'non_prodi_staff_placements', 'non_prodi_staff_count', 'prodi_active_staff_count', 'total_staff'] as $key) {
    mdos_check(strpos($controller, "'" . $key . "'") !== FALSE, 'Controller kehilangan view-data key: ' . $key);
}
mdos_check(strpos($controller, 'get_prodi_master_directory_data()') !== FALSE, 'Controller harus membaca data Prodi directory yang memuat status mapping organisasi.');
mdos_check(strpos($controller, 'get_master_directory_units()') !== FALSE && strpos($controller, 'get_master_directory_summary()') !== FALSE, 'Controller harus membaca unit hierarchy dan summary dari Organization_model.');
mdos_check(strpos($controller, 'get_current_non_prodi_staff_placements()') !== FALSE && strpos($controller, 'count_current_non_prodi_staff_placements()') !== FALSE, 'Controller harus membaca data/count penempatan non-Prodi aktif saat ini.');
mdos_check(strpos($controller, "'total_staff' => (int) $" . "summary['active_staff']") !== FALSE, 'Controller total_staff harus berasal dari summary active_staff distinct canonical.');
foreach (['unit_detail', 'unit_create', 'unit_store', 'unit_edit', 'unit_update', 'unit_toggle', 'placement_create', 'placement_store', 'placement_end'] as $method) {
    mdos_check(strpos($controller, 'public function ' . $method . '(') !== FALSE, 'Controller canonical harus punya action: ' . $method);
}
foreach (['require_post_capability', 'organization.manage', 'organization.assignment.manage', "method(TRUE) !== 'POST'", 'form_validation->set_rules'] as $literal) {
    mdos_check(strpos($controller, $literal) !== FALSE, 'Controller canonical mutation kehilangan guard/validasi: ' . $literal);
}
mdos_check(strpos($controller, "in_list[faculty,bureau,unit,institute]") !== FALSE, 'Generic Master UI hanya boleh membuat faculty/bureau/unit/institute.');
foreach (['create_canonical_unit($this->unit_input())', 'update_canonical_unit((int) $id, $this->unit_input())', 'toggle_canonical_unit((int) $id)', 'create_canonical_non_prodi_assignment($this->assignment_input())', 'end_canonical_non_prodi_assignment((int) $id'] as $literal) {
    mdos_check(strpos($controller, $literal) !== FALSE, 'Controller harus memanggil service canonical: ' . $literal);
}
mdos_check(substr_count($controller, "'lpmpi/master-data-prodi-staf'") >= 4, 'Mutation Master harus redirect ke URL canonical Master Data.');
mdos_check(strpos($routes, "\$route['lpmpi/master-data-prodi-staf/create'] = 'lpmpi/Master_data_prodi_staf/create';") !== FALSE, 'Route GET unified create Master Data hilang.');
foreach (['unit/detail/(:num)', 'unit/create', 'unit/store', 'unit/edit/(:num)', 'unit/update/(:num)', 'unit/toggle/(:num)', 'placement/create', 'placement/store', 'placement/end/(:num)'] as $literal) {
    mdos_check(strpos($routes, 'lpmpi/master-data-prodi-staf/' . $literal) !== FALSE, 'Route canonical Master hilang: ' . $literal);
}
foreach ([
    "\$route['lpmpi/master-data-prodi-staf/unit/store'] = 'lpmpi/Master_data_prodi_staf/unit_store';",
    "\$route['lpmpi/master-data-prodi-staf/prodi/store'] = 'lpmpi/Master_data_prodi_staf/prodi_store';",
    "\$route['profil/prodi/store'] = 'Profil/prodi_store';",
    "\$route['lpmpi/master-data-prodi-staf/unit/create'] = 'lpmpi/Master_data_prodi_staf/unit_create';",
    "\$route['profil/prodi/create'] = 'Profil/prodi_create';",
] as $literal) {
    mdos_check(strpos($routes, $literal) !== FALSE, 'Route direct existing harus tetap tersedia: ' . $literal);
}
foreach (['public function create()', "method(TRUE) !== 'GET'", "require_capability('organization.manage')", "load->view('lpmpi/master_data_prodi_staf/create_form'", "'unit_store_action' => 'lpmpi/master-data-prodi-staf/unit/store'", "'prodi_store_action' => 'lpmpi/master-data-prodi-staf/prodi/store'", "'units' => $" . "this->organization_service->units()", "'faculties' => $" . "this->Profil_model->active_faculties()"] as $literal) {
    mdos_check(strpos($controller, $literal) !== FALSE, 'Controller unified create kehilangan kontrak GET-only: ' . $literal);
}
foreach (['public function prodi_store()', "require_post_capability('organization.manage')", '$this->prodi_rules(TRUE);', 'Profil_service.php', '$this->profil_service = new Profil_service();', 'create_prodi($this->prodi_input())', "'lpmpi/master-data-prodi-staf/create'", 'validation_errors(', "set_flashdata('error'", "redirect('lpmpi/master-data-prodi-staf')"] as $literal) {
    mdos_check(strpos($controller, $literal) !== FALSE, 'Controller Master receiver Prodi kehilangan kontrak: ' . $literal);
}
mdos_check(strpos($controller, 'public function create_store(') === FALSE && strpos($routes, 'master-data-prodi-staf/create/store') === FALSE, 'Unified create tidak boleh menambah handler POST generic baru.');

foreach (['create_canonical_unit', 'update_canonical_unit', 'toggle_canonical_unit', 'create_canonical_non_prodi_assignment', 'end_canonical_non_prodi_assignment', 'valid_canonical_parent', 'valid_non_prodi_unit', 'is_canonical_generic_type'] as $method) {
    mdos_check(strpos($service, $method) !== FALSE, 'Service canonical missing: ' . $method);
}
foreach (["'faculty' => 'university'", "'study_program' => 'faculty'", "'bureau' => 'university'", "'unit' => 'bureau'", "'institute' => 'university'"] as $literal) {
    mdos_check(strpos($service, $literal) !== FALSE, 'Service strict parent matrix missing: ' . $literal);
}
mdos_check(strpos($service, '$parent = $this->model->find_unit($parent_id)') !== FALSE && strpos($service, '$parent->type === $matrix[$type]') !== FALSE, 'Service canonical harus re-fetch parent dan validasi tipe parent.');
mdos_check(strpos($service, "in_array($" . "type, ['faculty', 'bureau', 'unit', 'institute'], TRUE)") !== FALSE && strpos($service, 'Tipe unit Master Data tidak valid.') !== FALSE, 'Service generic Master harus menolak study_program/upps/university sebelum validasi parent.');
mdos_check(strpos($service, "!$" . "unit || !$" . "this->is_canonical_generic_type($" . "unit->type)") !== FALSE && strpos($service, 'Unit Master Data tidak ditemukan.') !== FALSE, 'Toggle generic Master harus menolak root/study_program/upps di service.');
mdos_check(strpos($service, 'return $unit && $this->is_canonical_generic_type($unit->type);') !== FALSE, 'Service penempatan non-Prodi harus memakai allowlist generic canonical untuk target aktif.');
mdos_check(strpos($service, 'return $this->create_assignment($data);') !== FALSE && strpos($service, 'return $this->end_assignment($id, $until);') !== FALSE, 'Penempatan canonical harus reuse semantik transaksi/date create_assignment/end_assignment.');
mdos_check(strpos($service, 'Unit root tidak dapat diubah melalui Master Data.') !== FALSE, 'Master canonical tidak boleh mutasi unit root.');

mdos_check(strpos($profil_model, 'public function get_prodi_master_data()') !== FALSE, 'Profil_model harus mempertahankan get_prodi_master_data untuk caller lama.');
mdos_check(strpos($profil_model, 'public function get_prodi_master_directory_data()') !== FALSE, 'Profil_model harus punya read-model directory Prodi canonical.');
mdos_check(strpos($profil_model, "join('organization_units ou', 'ou.id = profil_prodi.organization_unit_id', 'left')") !== FALSE, 'Mapped Prodi harus join lewat profil_prodi.organization_unit_id dengan left join.');
mdos_check(strpos($profil_model, 'is_mapped_to_organization') !== FALSE && strpos($profil_model, 'organization_mapping_status') !== FALSE, 'Directory Prodi harus menyediakan boolean/status mapping eksplisit.');
mdos_check(strpos($profil_model, "COUNT(CASE WHEN staf_prodi.status = 'active' THEN staf_prodi.id END) AS active_staff_count") !== FALSE, 'Directory Prodi harus menghitung roster aktif dari staf_prodi.status active.');
mdos_check(strpos($profil_model, "WHEN profil_prodi.organization_unit_id IS NULL THEN 'unmapped'") !== FALSE, 'Unmapped profil_prodi harus tetap terwakili sebagai status unmapped.');
foreach (['profil/prodi/link/(:num)', 'Profil/prodi_link/$1'] as $literal) {
    mdos_check(strpos($routes, $literal) !== FALSE, 'Route link Prodi canonical hilang: ' . $literal);
}
mdos_check(strpos($routes, "\$route['lpmpi/master-data-prodi-staf/prodi/link/(:num)'] = 'lpmpi/Master_data_prodi_staf/prodi_link/$1';") !== FALSE, 'Route GET canonical Master untuk form link Prodi hilang.');
foreach (['public function prodi_link($id)', "require_capability('organization.manage')", 'Profil_model->find_prodi((int) $id)', "load->view('lpmpi/master_data_prodi_staf/prodi_link_form'", "'faculties' => $" . "this->Profil_model->active_faculties()", "'action' => 'profil/prodi/link/' . (int) $" . "id", "redirect('lpmpi/master-data-prodi-staf')"] as $literal) {
    mdos_check(strpos($controller, $literal) !== FALSE, 'Controller GET link Prodi kehilangan kontrak: ' . $literal);
}
foreach (['public function prodi_link($id)', 'require_post()', "set_rules('faculty_id', 'Fakultas', 'required|integer|greater_than[0]')", 'link_prodi((int) $id'] as $literal) {
    mdos_check(strpos($profil_controller, $literal) !== FALSE, 'Controller link Prodi kehilangan kontrak: ' . $literal);
}
foreach (['public function link_prodi($id, $faculty_id)', 'find_prodi_for_update((int) $id)', 'valid_faculty((int) $faculty_id)', 'matching_unbound_study_program', "'type' => 'study_program'", "'parent_id' => (int) $" . "faculty_id", "'organization_unit_id' => $" . "unit_id"] as $literal) {
    mdos_check(strpos($profil_service, $literal) !== FALSE, 'Service link Prodi kehilangan kontrak: ' . $literal);
}
mdos_check(strpos($profil_service, "update_organization_unit((int) $" . "unit->id, ['code' => trim((string) $" . "prodi->kode_prodi), 'name' => trim((string) $" . "prodi->nama_prodi), 'parent_id' => (int) $" . "faculty_id])") !== FALSE, 'Reuse study_program saat link Prodi harus sinkron code/name/parent_id sesuai pilihan Fakultas eksplisit.');
mdos_check(strpos($profil_service, 'Kode unit organisasi ambigu') !== FALSE && strpos($profil_service, 'Kode unit organisasi sudah digunakan oleh unit non-Prodi.') !== FALSE && strpos($profil_service, 'Unit organisasi Program Studi sudah terhubung ke Prodi lain.') !== FALSE, 'Link Prodi harus menolak konflik/ambigu tanpa inferensi.');
foreach (['public function active_faculties()', "where(['type' => 'faculty', 'is_active' => 1])", 'public function prodi_faculty_id($organization_unit_id)', "where(['id' => (int) $" . "organization_unit_id, 'type' => 'study_program'])"] as $literal) {
    mdos_check(strpos($profil_model, $literal) !== FALSE, 'Profil_model harus menyediakan read helper Faculty/link parent: ' . $literal);
}

mdos_check(strpos($organization_model, 'public function get_master_directory_units()') !== FALSE, 'Organization_model harus punya read-model unit hierarchy directory.');
mdos_check(strpos($organization_model, 'public function get_master_directory_summary()') !== FALSE, 'Organization_model harus punya summary directory.');
foreach (['faculty', 'study_program', 'bureau', 'unit', 'institute', 'active_staff'] as $summary_key) {
    mdos_check(strpos($organization_model, "'" . $summary_key . "' => 0") !== FALSE, 'Summary harus memiliki key stabil: ' . $summary_key);
}
mdos_check(strpos($organization_model, "where_in('ou.type', ['university', 'faculty', 'study_program', 'bureau', 'unit', 'institute'])") !== FALSE, 'Directory unit harus memuat tipe canonical termasuk inactive untuk filter directory.');
mdos_check(strpos($organization_model, "join('profil_prodi pp_linked', 'pp_linked.organization_unit_id = ou.id', 'left')") !== FALSE, 'Directory unit harus join profil_prodi untuk mengecualikan hanya duplicate Program Studi yang sudah linked.');
mdos_check(strpos($organization_model, "where('(ou.type != ' . $" . "this->db->escape('study_program') . ' OR pp_linked.organization_unit_id IS NULL)', NULL, FALSE)") !== FALSE, 'Directory unit harus tetap menampilkan study_program legacy yang belum linked ke profil_prodi.');
mdos_check(strpos($organization_model, "where_in('ou.type', ['faculty', 'bureau', 'unit', 'institute'])") !== FALSE, 'Penempatan non-Prodi tidak boleh menghitung study_program.');
mdos_check(strpos($organization_model, 'a.valid_from <= CURDATE()') !== FALSE && strpos($organization_model, 'a.valid_until > CURDATE()') !== FALSE, 'Penempatan aktif harus mengikuti semantik current Organization_service.');
mdos_check(strpos($organization_model, 'COUNT(DISTINCT user_id) AS total') !== FALSE, 'Total staf aktif harus distinct user lintas roster Prodi dan penempatan current non-Prodi.');
mdos_check(strpos($organization_model, "from('staf_prodi')") !== FALSE && strpos($organization_model, "where('status', 'active')") !== FALSE, 'Total staf aktif harus memasukkan roster Prodi aktif dari staf_prodi.');
foreach (['insert(', 'update(', 'delete(', 'empty_table', 'insert_batch'] as $mutation) {
    $read_model = substr($organization_model, strpos($organization_model, 'public function get_master_directory_units()'), strpos($organization_model, 'public function find_unit(') - strpos($organization_model, 'public function get_master_directory_units()'));
    mdos_check(stripos($read_model, $mutation) === FALSE, 'Read-model Organization_model tidak boleh memuat mutasi: ' . $mutation);
}

foreach (['Master Data Organisasi &amp; Staf', 'Fakultas', 'Program Studi', 'Biro', 'Unit', 'Lembaga', 'Staf Aktif', 'organization_units', 'prodi_directory', 'Belum terhubung ke struktur organisasi', 'master-search', 'master-type-filter', 'master-status-filter', 'data-master-row', 'data-master-prev', 'data-master-next', 'form_open', 'window.confirm', 'html_escape'] as $literal) {
    mdos_check(strpos($view, $literal) !== FALSE, 'View canonical kehilangan kontrak Phase 2: ' . $literal);
}
mdos_check(strpos($view, "'label' => 'Faculty'") === FALSE && strpos($view, 'Prodi akademik') === FALSE, 'Label entity directory harus konsisten Bahasa Indonesia dan memakai Program Studi.');
mdos_check(substr_count($view, "['key' =>") >= 6 && substr_count($view, "'label' =>") >= 6, 'View canonical harus memiliki enam summary card: Fakultas, Program Studi, Biro, Unit, Lembaga, dan Staf Aktif.');
foreach (['Organisasi', 'Tipe', 'Parent', 'Staf', 'Status', 'Aksi'] as $header) {
    mdos_check(strpos($view, '>' . $header . '<') !== FALSE, 'Header directory canonical hilang: ' . $header);
}
mdos_check(strpos($view, '>Kode<') === FALSE && strpos($view, '>Nama<') === FALSE && strpos($view, '>Metadata<') === FALSE, 'Directory canonical harus memakai enam kolom ringkas tanpa Kode/Nama/Metadata terpisah.');
mdos_check(strpos($view, 'data-mapping=') !== FALSE && strpos($view, 'data-status="<?php echo $mapped ? ($prodi_active ? \'active\' : \'inactive\') : \'\'; ?>"') !== FALSE, 'Mapping Prodi harus terpisah dari status lifecycle unit.');
mdos_check(strpos($view, '<details class="master-row-menu">') === FALSE && strpos($view, 'data-toggle="dropdown"') !== FALSE && strpos($view, 'class="dropdown-menu dropdown-menu-right"') !== FALSE && strpos($view, 'data-boundary="viewport"') !== FALSE && strpos($view, ' Hubungkan</a>') !== FALSE, 'Aksi utama dan sekunder directory harus memakai Bootstrap dropdown adaptif tanpa menghapus route lama.');
mdos_check(strpos($view, "root.addEventListener('click'") !== FALSE && strpos($view, 'getBoundingClientRect()') !== FALSE && strpos($view, "classList.toggle('dropup'") !== FALSE, 'Dropdown directory harus menghitung arah berdasarkan geometri aktual saat dibuka.');
mdos_check(strpos($view, 'top: calc(100% + 4px)') === FALSE && substr_count($view, 'Hubungkan ke Fakultas') === 0, 'Dropdown directory tidak boleh memakai posisi statis atau duplicate Hubungkan ke Fakultas.');
mdos_check(strpos($view, 'dropdown-divider') !== FALSE, 'Menu Prodi harus mempertahankan separator sebelum aksi Hapus.');
mdos_check(strpos($view, 'Tambah Penempatan Non-Prodi') === FALSE, 'CTA penempatan non-Prodi hanya boleh tampil pada section Penempatan Staf.');
mdos_check(strpos($view, 'data-search=') !== FALSE && strpos($view, 'data-type=') !== FALSE && strpos($view, 'data-status=') !== FALSE, 'View canonical harus menyiapkan corpus pencarian, tipe, dan status untuk seluruh baris.');
mdos_check(strpos($view, '$type !== \'study_program\'') !== FALSE && strpos($view, '<option value="prodi">Program Studi</option>') !== FALSE && strpos($view, '<option value="study_program">Program Studi legacy belum terhubung</option>') !== FALSE, 'Filter tipe harus memetakan Program Studi canonical ke profil_prodi dan memberi label eksplisit untuk unit study_program legacy.');
mdos_check(strpos($view, 'visible.slice((page - 1) * pageSize, page * pageSize)') !== FALSE, 'View canonical harus memiliki pagination client-side.');
mdos_check(strpos($view, 'master-status-active') !== FALSE && strpos($view, 'master-status-inactive') !== FALSE, 'Status aktif/nonaktif harus terlihat melalui teks dan badge.');
mdos_check(strpos($view, 'non_prodi_staff_placements') !== FALSE && strpos($view, 'Penempatan Staf Non-Prodi') !== FALSE, 'View canonical harus menyediakan daftar staf non-Prodi read-only.');
mdos_check(strpos($view, "in_array($" . "unit_type, ['faculty', 'bureau', 'unit', 'institute'], TRUE)") !== FALSE, 'Aksi generic Master hanya boleh terlihat untuk faculty/bureau/unit/institute.');
$value_helper_start = strpos($view, '$value = static function');
$value_helper_end = strpos($view, '$type_labels =', $value_helper_start);
$value_helper = $value_helper_start !== FALSE && $value_helper_end !== FALSE ? substr($view, $value_helper_start, $value_helper_end - $value_helper_start) : '';
mdos_check(strpos($value_helper, 'property_exists($row, $key)') !== FALSE && strpos($value_helper, 'array_key_exists($key, $row)') !== FALSE, 'View value helper harus mempertahankan nilai NULL dari object dan array row.');
mdos_check(strpos($view, '$unit_is_root = $value($unit, \'parent_id\') === NULL') !== FALSE && strpos($view, '<span class="text-muted" aria-label="Unit root">—</span>') !== FALSE && strpos($view, '<a class="btn btn-sm btn-outline-ami" href="<?php echo html_escape(site_url(\'lpmpi/master-data-prodi-staf/unit/detail/\' . (int) $value($unit, \'id\'))); ?>">') !== FALSE, 'Unit root harus memakai marker netral, sedangkan unit non-root harus mempertahankan tautan Detail.');
mdos_check(strpos($view, "site_url('lpmpi/master-data-prodi-staf/create')") !== FALSE, 'Index Master Data harus memakai CTA unified create.');
mdos_check(strpos($view, "site_url('lpmpi/master-data-prodi-staf/unit/create')") === FALSE && strpos($view, "site_url('profil/prodi/create')") === FALSE, 'Index Master Data tidak boleh menampilkan CTA create direct lama.');
foreach (['form_open($unit_store_action', 'lpmpi/master-data-prodi-staf/unit/store', 'lpmpi/master-data-prodi-staf/prodi/store', 'name="entity_type"', 'name="type"', 'name="require_faculty"', '$faculties', 'html_escape'] as $literal) {
    mdos_check(strpos($create_form, $literal) !== FALSE, 'Form unified create kehilangan kontrak dasar: ' . $literal);
}
mdos_check(strpos($create_form, 'profil/prodi/store') === FALSE, 'Unified form tidak boleh POST Program Studi ke endpoint legacy Profil.');
foreach (["var unitAction = <?php echo json_encode(site_url($" . "unit_store_action)); ?>", "var prodiAction = <?php echo json_encode(site_url($" . "prodi_store_action)); ?>", "form.action = isProdi ? prodiAction : unitAction"] as $literal) {
    mdos_check(strpos($create_form, $literal) !== FALSE, 'Form unified create harus mengatur action dari JS: ' . $literal);
}
foreach (["'faculty' => 'Fakultas'", "'bureau' => 'Biro'", "'unit' => 'Unit'", "'institute' => 'Lembaga'", "'study_program' => 'Program Studi'"] as $literal) {
    mdos_check(strpos($create_form, $literal) !== FALSE, 'Selector tipe unified create kehilangan opsi: ' . $literal);
}
foreach (['name="kode_prodi"', 'name="nama_prodi"', 'name="faculty_id"', 'name="status"', 'name="jenjang"', 'name="akreditasi"', 'name="tanggal_sk_akreditasi"', 'name="rasio_dosen_mahasiswa"'] as $literal) {
    mdos_check(strpos($create_form, $literal) !== FALSE, 'Form unified create kehilangan field akademik Prodi: ' . $literal);
}
foreach (['data-parent-type', 'requiredParentType', 'parent_id', 'option.hidden = !matches', 'option.disabled = !matches'] as $literal) {
    mdos_check(strpos($create_form, $literal) !== FALSE, 'Form unified create kehilangan scoping parent generic: ' . $literal);
}
foreach ([
    [$unit_detail, 'parent_unit', 'Detail unit harus menampilkan parent dari view-data controller.'],
    [$unit_detail, 'assignments', 'Detail unit harus menampilkan penempatan unit.'],
    [$unit_detail, 'placement/end/', 'Detail unit harus menyediakan endpoint akhiri penempatan.'],
    [$unit_form, 'form_open($action)', 'Form unit harus POST melalui form_open.'],
    [$unit_form, "['faculty' => 'Fakultas', 'bureau' => 'Biro', 'unit' => 'Unit', 'institute' => 'Lembaga']", 'Form unit hanya boleh menawarkan empat tipe generic canonical.'],
    [$unit_form, 'parent_types', 'Form unit harus menyaring parent ke tipe canonical yang tersedia.'],
    [$placement_form, 'form_open($action)', 'Form penempatan harus POST melalui form_open.'],
    [$placement_form, 'valid_until', 'Form penempatan harus menyediakan valid_until.'],
    [$placement_form, "['faculty' => 'Fakultas', 'bureau' => 'Biro', 'unit' => 'Unit', 'institute' => 'Lembaga']", 'Form penempatan hanya boleh menawarkan unit non-Prodi canonical.'],
] as $contract) {
    mdos_check(strpos($contract[0], $contract[1]) !== FALSE, $contract[2]);
}
foreach (['lpmpi/master-data-prodi-staf/placement/create', 'lpmpi/master-data-prodi-staf/unit/detail/', 'lpmpi/master-data-prodi-staf/unit/toggle/', 'lpmpi/master-data-prodi-staf/placement/end/'] as $literal) {
    mdos_check(strpos($view, $literal) !== FALSE || strpos($unit_detail, $literal) !== FALSE, 'Affordance canonical hilang: ' . $literal);
}
mdos_check(strpos($view, 'Read-only directory') === FALSE, 'Placeholder Read-only directory harus diganti dengan aksi canonical.');
foreach (['lpmpi/master-data-prodi-staf/prodi/link/', 'Belum terhubung ke struktur organisasi', 'profil/prodi/edit/', 'profil/prodi/delete/', '/staf'] as $literal) {
    mdos_check(strpos($view, $literal) !== FALSE, 'View Phase 4 kehilangan kontrak: ' . $literal);
}
foreach (['$faculties', 'form_open($action)', 'name="faculty_id"', 'required', 'set_select', 'html_escape', 'tidak menebak', 'return_url'] as $literal) {
    mdos_check(strpos($prodi_form . $prodi_link_form, $literal) !== FALSE, 'Form Phase 4 kehilangan kontrak: ' . $literal);
}
mdos_check(strpos($prodi_form, '$require_faculty ? \'required\' : \'\'') !== FALSE && strpos($prodi_form, 'name="require_faculty"') !== FALSE && strpos($prodi_form, 'isset($selected_faculty_id) ? $selected_faculty_id') !== FALSE, 'Form Prodi manual harus memakai required Faculty kondisional dan selected parent dari controller.');
foreach (['$row', '$faculties', '$action', '$return_url'] as $key) {
    mdos_check(strpos($prodi_link_form, $key) !== FALSE, 'Form link harus memakai view-data key: ' . $key);
}
mdos_check(strpos($prodi_form, 'set_value(\'faculty_id\'') !== FALSE, 'Form Prodi harus mempertahankan selected faculty setelah validasi gagal.');

fwrite(STDOUT, "Master Data Organisasi & Staf schema regression checks passed.\n");
