<?php

$root = dirname(__DIR__);

function master_data_source($path)
{
    $source = file_get_contents(dirname(__DIR__) . DIRECTORY_SEPARATOR . $path);
    if ($source === FALSE) {
        throw new RuntimeException('Tidak dapat membaca ' . $path);
    }

    return $source;
}

function master_data_check($condition, $message)
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$controller = master_data_source('application/controllers/lpmpi/Master_data_prodi_staf.php');
$profil_model = master_data_source('application/models/Profil_model.php');
$routes = master_data_source('application/config/routes.php');
$view = master_data_source('application/views/lpmpi/master_data_prodi_staf/index.php');
$profile_controller = master_data_source('application/controllers/Profil.php');
$sidebar = master_data_source('application/views/layouts/sidebar.php');

master_data_check(strpos($controller, 'class Master_data_prodi_staf extends Admin_Lpmpi_Controller') !== FALSE, 'Hub Master Data harus memakai guard Admin_Lpmpi_Controller.');
master_data_check(strpos($controller, 'public function index()') !== FALSE && strpos($controller, "method(TRUE) !== 'GET'") !== FALSE, 'Hub Master Data harus memiliki endpoint GET-only.');
master_data_check(substr_count($controller, 'get_prodi_master_directory_data()') === 1, 'Controller hub harus membaca agregat Prodi melalui read-model directory khusus hub.');
master_data_check(strpos($controller, 'jenjang_counts') !== FALSE && strpos($controller, 'prodi_active_staff_count') !== FALSE && strpos($controller, 'total_staff') !== FALSE, 'Controller hub harus menyiapkan ringkasan jenjang, staf Prodi, dan staf aktif canonical dari data aktual.');
master_data_check(strpos($profil_model, 'get_prodi_master_data()') !== FALSE && strpos($profil_model, "COUNT(CASE WHEN staf_prodi.status = 'active'") !== FALSE, 'Model hub harus menghitung relasi staf aktif secara agregat.');
master_data_check(strpos($controller, "load->view('lpmpi/master_data_prodi_staf/index'") !== FALSE && strpos($controller, "'page_title' => 'Master Data Organisasi & Staf'") !== FALSE && strpos($controller, "'active_menu' => 'master_data_prodi_staf'") !== FALSE, 'Controller hub harus merender view, judul canonical, dan active menu yang benar.');
master_data_check(strpos($routes, "\$route['lpmpi/master-data-prodi-staf'] = 'lpmpi/Master_data_prodi_staf/index';") !== FALSE, 'Route hub Master Data hilang.');
master_data_check(strpos($routes, "\$route['lpmpi/master-data-prodi-staf/prodi/link/(:num)'] = 'lpmpi/Master_data_prodi_staf/prodi_link/$1';") !== FALSE, 'Route GET form link Prodi dari Master Data hilang.');
foreach (['public function prodi_link($id)', "require_capability('organization.manage')", 'Profil_model->find_prodi((int) $id)', "load->view('lpmpi/master_data_prodi_staf/prodi_link_form'", "'action' => 'profil/prodi/link/' . (int) $" . "id", "'faculties' => $" . "this->Profil_model->active_faculties()"] as $literal) {
    master_data_check(strpos($controller, $literal) !== FALSE, 'Controller hub harus merender form link Prodi canonical: ' . $literal);
}

foreach (['Master Data Organisasi &amp; Staf', 'Fakultas', 'Program Studi', 'Biro', 'Unit', 'Lembaga', 'Staf Aktif', 'organization_units', 'prodi_directory', 'kode_prodi', 'nama_prodi', 'jenjang', 'Belum terhubung ke struktur organisasi', 'profil/prodi/', '/staf', 'profil/prodi/edit/', 'profil/prodi/delete/', 'form_open', 'window.confirm', 'html_escape', 'master-search', 'master-type-filter', 'master-status-filter', 'data-master-row', 'data-master-prev', 'data-master-next', 'pageSize'] as $literal) {
    master_data_check(strpos($view, $literal) !== FALSE, 'View hub kehilangan kontrak: ' . $literal);
}
master_data_check(strpos($view, "'label' => 'Faculty'") === FALSE, 'Summary card harus memakai label Bahasa Indonesia: Fakultas.');
master_data_check(strpos($view, 'Prodi akademik') === FALSE, 'Semua profil_prodi harus memakai label Program Studi.');
foreach (['Organisasi', 'Tipe', 'Parent', 'Staf', 'Status', 'Aksi'] as $header) {
    master_data_check(strpos($view, '>' . $header . '<') !== FALSE, 'Header directory wajib memuat kolom: ' . $header);
}
master_data_check(strpos($view, '>Kode<') === FALSE && strpos($view, '>Nama<') === FALSE && strpos($view, '>Metadata<') === FALSE, 'Directory utama tidak boleh lagi memakai kolom Kode, Nama, atau Metadata terpisah.');
master_data_check(strpos($view, 'master-row-secondary') !== FALSE, 'Kode dan metadata ringkas harus tampil sebagai secondary text di bawah nama organisasi.');
master_data_check(strpos($view, 'data-mapping=') !== FALSE, 'Directory harus memisahkan status mapping dari status lifecycle unit.');
master_data_check(strpos($view, 'data-status="<?php echo $prodi_active ? \'active\' : \'inactive\'; ?>"') === FALSE, 'Prodi belum terhubung tidak boleh diklasifikasikan sebagai Nonaktif.');
master_data_check(strpos($view, 'data-status="<?php echo $mapped ? ($prodi_active ? \'active\' : \'inactive\') : \'\'; ?>"') !== FALSE, 'Status lifecycle Prodi harus kosong bila belum terhubung.');
master_data_check(strpos($view, '<details class="master-row-menu">') === FALSE && strpos($view, 'data-toggle="dropdown"') !== FALSE && strpos($view, 'class="dropdown-menu dropdown-menu-right"') !== FALSE && strpos($view, 'data-boundary="viewport"') !== FALSE, 'Aksi sekunder harus memakai Bootstrap dropdown adaptif yang right-aligned dan berbatas viewport.');
master_data_check(strpos($view, "root.addEventListener('click'") !== FALSE && strpos($view, 'getBoundingClientRect()') !== FALSE && strpos($view, "classList.toggle('dropup'") !== FALSE, 'Dropdown harus menghitung arah berdasarkan geometri trigger/menu saat dibuka.');
master_data_check(strpos($view, 'top: calc(100% + 4px)') === FALSE, 'Dropdown tidak boleh memakai posisi statis yang selalu membuka ke bawah.');
master_data_check(substr_count($view, 'Hubungkan ke Fakultas') === 0, 'Menu Prodi tidak boleh menduplikasi aksi Hubungkan ke Fakultas.');
master_data_check(strpos($view, 'dropdown-divider') !== FALSE, 'Menu Prodi harus mempertahankan separator sebelum aksi Hapus.');
master_data_check(strpos($view, ' Kelola</a>') !== FALSE && strpos($view, ' Hubungkan</a>') !== FALSE, 'Directory harus menyediakan CTA Kelola dan Hubungkan yang ringkas.');
master_data_check(strpos($view, 'Tambah Penempatan Non-Prodi') === FALSE, 'Header tidak boleh menduplikasi CTA penempatan non-Prodi.');
foreach (['Total Staf Terhubung', 'Jumlah Staf', 'Direktori program studi, jumlah staf aktif terhubung, dan tindakan pengelolaan'] as $ambiguous_literal) {
    master_data_check(strpos($view, $ambiguous_literal) === FALSE, 'View hub masih memuat label staf yang ambigu: ' . $ambiguous_literal);
}
foreach (['<form', 'method="post"', 'Pddikti', 'user->', 'get_users'] as $forbidden) {
    master_data_check(stripos($view, $forbidden) === FALSE, 'View hub tidak boleh memuat implementasi atau data terlarang: ' . $forbidden);
}
master_data_check(strpos($view, "site_url('lpmpi/prodi-import')") !== FALSE, 'View hub harus memakai tautan Import Prodi canonical.');
master_data_check(strpos($view, "form_open('profil/prodi/delete/'") !== FALSE, 'View hub harus menghapus Prodi melalui POST+CSRF.');
master_data_check(strpos($view, 'master-management-card') !== FALSE && strpos($view, 'fas fa-sitemap') !== FALSE, 'View hub harus menampilkan kartu Management organisasi dengan ikon sitemap.');
master_data_check(strpos($view, 'Direktori canonical untuk Fakultas, Program Studi, Biro, Unit, Lembaga, dan staf aktif') !== FALSE, 'Kartu Management harus menjelaskan cakupan direktori canonical.');
master_data_check(strpos($view, 'role="search"') !== FALSE && strpos($view, 'aria-label="Filter direktori"') !== FALSE, 'Kontrol direktori harus memiliki landmark search yang aksesibel.');
master_data_check(strpos($view, 'master-directory-note') !== FALSE && strpos($view, 'master-mapping-mapped') !== FALSE, 'Direktori harus membedakan Prodi mapped dan unmapped.');
master_data_check(strpos($view, "site_url('profil')") === FALSE && strpos($view, 'Profil Lembaga') === FALSE, 'View hub tidak boleh memuat aksi Profil Lembaga yang tidak relevan.');
master_data_check(strpos($view, 'master-management-card') < strpos($view, 'master-data-summary'), 'Kartu Management harus berada sebelum summary cards.');
master_data_check(substr_count($view, "['key' =>") >= 6 && substr_count($view, "'label' =>") >= 6, 'View hub harus memiliki tepat enam summary card canonical.');
master_data_check(strpos($view, 'data-search=') !== FALSE && strpos($view, 'data-type=') !== FALSE && strpos($view, 'data-status=') !== FALSE, 'Setiap baris hub harus menyediakan data untuk pencarian, tipe, dan status client-side.');
master_data_check(strpos($view, 'function render()') !== FALSE && strpos($view, 'visible.slice((page - 1) * pageSize, page * pageSize)') !== FALSE && strpos($view, 'master-type-filter') !== FALSE && strpos($view, 'master-status-filter') !== FALSE, 'View hub harus menjalankan pagination dan tiga kontrol filter client-side.');
foreach (['.master-controls .form-control { background-color: var(--ami-panel); border: 1px solid var(--ami-border); color: var(--ami-text); }', '.master-controls .form-control::placeholder { color: var(--ami-muted); opacity: 1; }', '.master-controls .form-control:focus { background-color: var(--ami-panel); border-color: var(--ami-link); color: var(--ami-text); box-shadow: 0 0 0 .2rem var(--ami-link-soft); }', '.master-controls select.form-control option { background-color: var(--ami-panel); color: var(--ami-text); }'] as $contrast_rule) {
    master_data_check(strpos($view, $contrast_rule) !== FALSE, 'Kontrol pencarian/filter harus memiliki aturan kontras terscope: ' . $contrast_rule);
}
master_data_check(strpos($profile_controller, "redirect('lpmpi/master-data-prodi-staf')") !== FALSE, 'CRUD Prodi harus kembali ke Master Data.');
master_data_check(strpos($view, 'empty($organization_units) && empty($prodi_directory)') !== FALSE && strpos($view, 'Belum ada data direktori') !== FALSE, 'View hub harus memiliki empty state direktori.');

$menu_contract = "['key' => 'master_data_prodi_staf', 'label' => 'Master Data Organisasi & Staf', 'icon' => 'fa-graduation-cap', 'url' => 'lpmpi/master-data-prodi-staf', 'group' => 'Management']";
master_data_check(substr_count($sidebar, $menu_contract) === 2, 'Sidebar hub harus tersedia tepat untuk dua role management.');
master_data_check(strpos($sidebar, "'key' => 'auditor'") === FALSE && strpos($sidebar, "'key' => 'auditee'") === FALSE, 'Kontrak role sidebar tidak boleh bergeser.');

fwrite(STDOUT, "Master Data Organisasi & Staf view regression checks passed.\n");
