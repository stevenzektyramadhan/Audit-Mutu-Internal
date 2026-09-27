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
master_data_check(substr_count($controller, 'get_prodi_master_data()') === 1, 'Controller hub harus membaca agregat Prodi melalui model khusus hub.');
master_data_check(strpos($controller, 'jenjang_counts') !== FALSE && strpos($controller, 'total_staff') !== FALSE, 'Controller hub harus menyiapkan ringkasan jenjang dan staf dari data aktual.');
master_data_check(strpos($profil_model, 'get_prodi_master_data()') !== FALSE && strpos($profil_model, "COUNT(CASE WHEN staf_prodi.status = 'active'") !== FALSE, 'Model hub harus menghitung relasi staf aktif secara agregat.');
master_data_check(strpos($controller, "load->view('lpmpi/master_data_prodi_staf/index'") !== FALSE && strpos($controller, "'page_title' => 'Master Data Prodi & Staf'") !== FALSE && strpos($controller, "'active_menu' => 'master_data_prodi_staf'") !== FALSE, 'Controller hub harus merender view, judul, dan active menu yang benar.');
master_data_check(strpos($routes, "\$route['lpmpi/master-data-prodi-staf'] = 'lpmpi/Master_data_prodi_staf/index';") !== FALSE, 'Route hub Master Data hilang.');

foreach (['Master Data Prodi &amp; Staf', 'Total Program Studi', 'Total Staf Terhubung', 'Jenjang Tersedia', 'Tambah Prodi', 'Import Prodi', 'kode_prodi', 'nama_prodi', 'jenjang', 'Jumlah Staf', 'Terakhir Diperbarui', 'profil/prodi/', '/staf', 'profil/prodi/edit/', 'profil/prodi/delete/', 'form_open', 'window.confirm', 'html_escape', 'master-search', 'master-level-filter', 'data-master-row', 'data-master-prev', 'data-master-next', 'pageSize'] as $literal) {
    master_data_check(strpos($view, $literal) !== FALSE, 'View hub kehilangan kontrak: ' . $literal);
}
foreach (['<form', 'method="post"', 'Pddikti', 'user->', 'get_users'] as $forbidden) {
    master_data_check(stripos($view, $forbidden) === FALSE, 'View hub tidak boleh memuat implementasi atau data terlarang: ' . $forbidden);
}
master_data_check(strpos($view, "site_url('lpmpi/prodi-import')") !== FALSE, 'View hub harus memakai tautan Import Prodi canonical.');
master_data_check(strpos($view, "form_open('profil/prodi/delete/'") !== FALSE, 'View hub harus menghapus Prodi melalui POST+CSRF.');
master_data_check(strpos($view, 'master-management-card') !== FALSE && strpos($view, 'fas fa-graduation-cap') !== FALSE, 'View hub harus menampilkan satu kartu informasi Management dengan ikon graduation-cap.');
master_data_check(strpos($view, 'Halaman ini bukan halaman manajemen akun; pembuatan akun tetap dilakukan melalui menu Manajemen Pengguna.') !== FALSE, 'Kartu Management harus menjelaskan batas manajemen akun sesuai copy yang disepakati.');
master_data_check(strpos($view, 'Pusat pengelolaan program studi dan roster staf untuk kebutuhan pengelolaan mutu.') !== FALSE, 'Kartu Management harus memuat purpose copy pengelolaan program studi dan roster staf.');
master_data_check(strpos($view, 'Kelola data Prodi atau pilih roster untuk melihat staf yang sudah terhubung.') !== FALSE, 'Directory card harus memuat subtitle pengelolaan Prodi dan roster.');
master_data_check(strpos($view, '.master-level-d3 { color: var(--ami-blue);') !== FALSE && strpos($view, '.master-level-s1 { color: var(--ami-green);') !== FALSE && strpos($view, '.master-level-s2 { color: #534ab7;') !== FALSE, 'Badge jenjang harus memetakan D3 biru, S1 hijau, dan S2 ungu.');
master_data_check(strpos($view, "site_url('profil')") === FALSE && strpos($view, 'Profil Lembaga') === FALSE, 'View hub tidak boleh memuat aksi Profil Lembaga yang tidak relevan.');
master_data_check(strpos($view, 'master-management-card') < strpos($view, 'master-data-summary'), 'Kartu Management harus berada sebelum summary cards.');
master_data_check(substr_count($view, '<div class="ami-stat-card') === 3, 'View hub harus memiliki tepat tiga summary card.');
master_data_check(strpos($view, 'data-search=') !== FALSE && strpos($view, 'data-level=') !== FALSE, 'Setiap baris hub harus menyediakan data untuk pencarian dan filter client-side.');
master_data_check(strpos($view, 'function render()') !== FALSE && strpos($view, 'visible.slice((page - 1) * pageSize, page * pageSize)') !== FALSE, 'View hub harus benar-benar menjalankan pagination client-side 10 per halaman.');
foreach (['.master-controls .form-control { background-color: var(--ami-panel); border: 1px solid var(--ami-border); color: var(--ami-text); }', '.master-controls .form-control::placeholder { color: var(--ami-muted); opacity: 1; }', '.master-controls .form-control:focus { background-color: var(--ami-panel); border-color: var(--ami-link); color: var(--ami-text); box-shadow: 0 0 0 .2rem var(--ami-link-soft); }', '.master-controls select.form-control option { background-color: var(--ami-panel); color: var(--ami-text); }'] as $contrast_rule) {
    master_data_check(strpos($view, $contrast_rule) !== FALSE, 'Kontrol pencarian/filter harus memiliki aturan kontras terscope: ' . $contrast_rule);
}
master_data_check(strpos($profile_controller, "redirect('lpmpi/master-data-prodi-staf')") !== FALSE, 'CRUD Prodi harus kembali ke Master Data.');
master_data_check(strpos($view, 'empty($prodi)') !== FALSE && strpos($view, 'Belum ada data program studi') !== FALSE, 'View hub harus memiliki empty state dengan import.');

$menu_contract = "['key' => 'master_data_prodi_staf', 'label' => 'Master Data Prodi & Staf', 'icon' => 'fa-graduation-cap', 'url' => 'lpmpi/master-data-prodi-staf', 'group' => 'Management']";
master_data_check(substr_count($sidebar, $menu_contract) === 2, 'Sidebar hub harus tersedia tepat untuk dua role management.');
master_data_check(strpos($sidebar, "'key' => 'auditor'") === FALSE && strpos($sidebar, "'key' => 'auditee'") === FALSE, 'Kontrak role sidebar tidak boleh bergeser.');

fwrite(STDOUT, "Master Data Prodi & Staf regression checks passed.\n");
