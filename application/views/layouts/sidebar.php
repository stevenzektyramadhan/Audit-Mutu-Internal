<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$role = $this->session->userdata('role');
$nama = $this->session->userdata('nama');
$active_menu = isset($active_menu) ? $active_menu : 'dashboard';
$menu_badges = isset($menu_badges) ? $menu_badges : [];
$role_labels = [
    'super_admin' => 'Super Admin',
    'auditor' => 'Auditor',
    'auditee' => 'Auditee',
];
$menus = [
    'super_admin' => [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'fa-tachometer-alt', 'url' => 'dashboard'],
        ['key' => 'users', 'label' => 'Data Pengguna', 'icon' => 'fa-users', 'url' => 'users'],
        ['key' => 'standar', 'label' => 'Data Standar', 'icon' => 'fa-award', 'url' => 'standar'],
        ['key' => 'pertanyaan', 'label' => 'Data Pertanyaan', 'icon' => 'fa-tasks', 'url' => 'pertanyaan'],
        ['key' => 'tugas_audit', 'label' => 'Tugas Audit', 'icon' => 'fa-clipboard-list', 'url' => 'tugas_audit'],
        ['key' => 'hasil_audit', 'label' => 'Hasil Audit', 'icon' => 'fa-chart-bar', 'url' => 'tugas_audit/hasil'],
    ],
    'auditor' => [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'fa-tachometer-alt', 'url' => 'dashboard'],
        ['key' => 'tugas_audit', 'label' => 'Tugas Audit', 'icon' => 'fa-clipboard-list', 'url' => 'auditor/tugas'],
        ['key' => 'penilaian', 'label' => 'Penilaian Auditee', 'icon' => 'fa-star', 'url' => 'auditor'],
    ],
    'auditee' => [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'fa-tachometer-alt', 'url' => 'dashboard'],
        ['key' => 'tugas_saya', 'label' => 'Tugas Saya', 'icon' => 'fa-clipboard-list', 'url' => 'auditee/tugas'],
        ['key' => 'pengisian', 'label' => 'Pengisian Audit', 'icon' => 'fa-pen', 'url' => 'auditee'],
        ['key' => 'hasil_penilaian', 'label' => 'Hasil Penilaian', 'icon' => 'fa-eye', 'url' => 'auditee/tugas?status=dinilai'],
    ],
];
$current_menus = isset($menus[$role]) ? $menus[$role] : [];
$name_parts = preg_split('/\s+/', trim((string) $nama));
$initial = '';
foreach (array_slice($name_parts, 0, 2) as $name_part) {
    if ($name_part !== '') {
        $initial .= strtoupper(substr($name_part, 0, 1));
    }
}
if ($initial === '') {
    $initial = 'A';
}
?>
<aside class="ami-sidebar" id="ami-sidebar" aria-label="Navigasi utama">
    <div class="ami-brand">
        <img src="<?= base_url('assets/img/logo-2.png'); ?>" alt="Logo LPM" class="ami-logo-img">
        <div class="ami-brand-title">AMI<br>Perguruan Tinggi</div>
        <button type="button" class="ami-sidebar-close" data-sidebar-close aria-label="Tutup menu">
            <i class="fas fa-times" aria-hidden="true"></i>
        </button>
    </div>

    <div class="ami-user">
        <div class="ami-avatar avatar-<?php echo html_escape($role); ?>"><?php echo html_escape($initial); ?></div>
        <div>
            <div class="ami-user-name"><?php echo html_escape($nama); ?></div>
            <div class="ami-user-role"><?php echo html_escape(isset($role_labels[$role]) ? $role_labels[$role] : $role); ?></div>
        </div>
    </div>

    <nav class="ami-nav">
        <div class="ami-nav-label">Menu</div>
        <?php foreach ($current_menus as $menu): ?>
            <a class="ami-nav-link <?php echo $active_menu === $menu['key'] ? 'active' : ''; ?>" href="<?php echo site_url($menu['url']); ?>">
                <i class="fas <?php echo html_escape($menu['icon']); ?>" aria-hidden="true"></i>
                <span><?php echo html_escape($menu['label']); ?></span>
                <?php if (isset($menu_badges[$menu['key']]) && (int) $menu_badges[$menu['key']] > 0): ?>
                    <span class="ami-nav-badge"><?php echo html_escape((string) $menu_badges[$menu['key']]); ?></span>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="ami-logout">
        <a class="ami-nav-link" href="<?php echo site_url('auth/logout'); ?>">
            <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
            <span>Logout</span>
        </a>
    </div>
</aside>

<button type="button" class="ami-sidebar-overlay" data-sidebar-close aria-label="Tutup menu"></button>

<main class="ami-main">
    <div class="ami-topbar">
        <div class="ami-topbar-heading">
            <button type="button" class="ami-menu-toggle" data-sidebar-toggle aria-controls="ami-sidebar" aria-expanded="false" aria-label="Buka menu">
                <i class="fas fa-bars" aria-hidden="true"></i>
            </button>
            <div>
                <h1 class="ami-page-title"><?php echo html_escape($page_title); ?></h1>
                <div class="ami-page-subtitle"><?php echo html_escape($page_subtitle); ?></div>
            </div>
        </div>
        <button type="button" class="ami-theme-toggle" data-theme-toggle aria-label="Aktifkan mode terang" title="Ubah tema">
            <i class="fas fa-sun" data-theme-icon aria-hidden="true"></i>
            <span class="d-none d-sm-inline" data-theme-label>Mode terang</span>
        </button>
    </div>
    <div class="ami-content">
        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert ami-flash ami-flash-success" role="alert">
                <i class="fas fa-check-circle" aria-hidden="true"></i>
                <span><?php echo html_escape($this->session->flashdata('success')); ?></span>
            </div>
        <?php endif; ?>
        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert ami-flash ami-flash-error" role="alert">
                <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
                <span><?php echo html_escape($this->session->flashdata('error')); ?></span>
            </div>
        <?php endif; ?>
