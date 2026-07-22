<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$role = $this->session->userdata('role');
$nama = $this->session->userdata('nama');
$profile_photo_path = $this->session->userdata('profile_photo_path');
$active_menu = isset($active_menu) ? $active_menu : 'dashboard';
$menu_badges = isset($menu_badges) ? $menu_badges : [];
$menus = [
    'super_admin' => [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'fa-tachometer-alt', 'url' => 'dashboard'],
        ['key' => 'account', 'label' => 'Akun Saya', 'icon' => 'fa-user-circle', 'url' => 'account'],
        ['key' => 'periode', 'label' => 'Periode Audit', 'icon' => 'fa-calendar-alt', 'url' => 'periode'],
        ['key' => 'users', 'label' => 'Data Pengguna', 'icon' => 'fa-users', 'url' => 'users'],
        ['key' => 'akun', 'label' => 'Akun Auditee/Auditor', 'icon' => 'fa-user-cog', 'url' => 'lpmpi/akun'],
        ['key' => 'standar', 'label' => 'Data Standar', 'icon' => 'fa-award', 'url' => 'standar'],
        ['key' => 'pertanyaan', 'label' => 'Data Pertanyaan', 'icon' => 'fa-tasks', 'url' => 'pertanyaan'],
        ['key' => 'instrumen', 'label' => 'Instrumen Standar', 'icon' => 'fa-file-upload', 'url' => 'lpmpi/instrumen'],
        ['key' => 'tugas_audit', 'label' => 'Tugas Audit', 'icon' => 'fa-clipboard-list', 'url' => 'tugas_audit'],
        ['key' => 'penugasan', 'label' => 'Penugasan Auditor', 'icon' => 'fa-clipboard-list', 'url' => 'lpmpi/penugasan'],
        ['key' => 'penetapan', 'label' => 'Penetapan', 'icon' => 'fa-gavel', 'url' => 'lpmpi/penetapan'],
        ['key' => 'hasil_audit', 'label' => 'Hasil Audit', 'icon' => 'fa-chart-bar', 'url' => 'tugas_audit/hasil'],
        ['key' => 'laporan', 'label' => 'Laporan & Statistik', 'icon' => 'fa-chart-pie', 'url' => 'lpmpi/laporan'],
        ['key' => 'profil', 'label' => 'Profil Lembaga', 'icon' => 'fa-university', 'url' => 'profil', 'group' => 'Pengaturan'],
    ],
    'admin_lpmpi' => [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'fa-tachometer-alt', 'url' => 'dashboard'],
        ['key' => 'account', 'label' => 'Akun Saya', 'icon' => 'fa-user-circle', 'url' => 'account'],
        ['key' => 'periode', 'label' => 'Periode Audit', 'icon' => 'fa-calendar-alt', 'url' => 'periode'],
        ['key' => 'akun', 'label' => 'Akun Auditee/Auditor', 'icon' => 'fa-user-cog', 'url' => 'lpmpi/akun'],
        ['key' => 'instrumen', 'label' => 'Instrumen Standar', 'icon' => 'fa-file-upload', 'url' => 'lpmpi/instrumen'],
        ['key' => 'penugasan', 'label' => 'Penugasan Auditor', 'icon' => 'fa-clipboard-list', 'url' => 'lpmpi/penugasan'],
        ['key' => 'penetapan', 'label' => 'Penetapan', 'icon' => 'fa-gavel', 'url' => 'lpmpi/penetapan'],
        ['key' => 'laporan', 'label' => 'Laporan & Statistik', 'icon' => 'fa-chart-pie', 'url' => 'lpmpi/laporan'],
        ['key' => 'profil', 'label' => 'Profil Lembaga', 'icon' => 'fa-university', 'url' => 'profil', 'group' => 'Pengaturan'],
    ],
    'auditor' => [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'fa-tachometer-alt', 'url' => 'dashboard'],
        ['key' => 'account', 'label' => 'Akun Saya', 'icon' => 'fa-user-circle', 'url' => 'account'],
        ['key' => 'tugas_audit', 'label' => 'Tugas Audit', 'icon' => 'fa-clipboard-list', 'url' => 'auditor/tugas'],
        ['key' => 'penilaian', 'label' => 'Penilaian Auditee', 'icon' => 'fa-star', 'url' => 'auditor/penilaian'],
    ],
    'auditee' => [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'fa-tachometer-alt', 'url' => 'dashboard'],
        ['key' => 'account', 'label' => 'Akun Saya', 'icon' => 'fa-user-circle', 'url' => 'account'],
        ['key' => 'tugas_saya', 'label' => 'Tugas Saya', 'icon' => 'fa-clipboard-list', 'url' => 'auditee/tugas'],
        ['key' => 'pengisian', 'label' => 'Pengisian Audit', 'icon' => 'fa-pen', 'url' => 'auditee/tugas?status=belum_diisi'],
        ['key' => 'hasil_penilaian', 'label' => 'Hasil Penilaian', 'icon' => 'fa-eye', 'url' => 'auditee/tugas?status=dinilai'],
    ],
];
$page_title = isset($page_title) ? $page_title : 'Dashboard';
$page_subtitle = isset($page_subtitle) ? $page_subtitle : '';
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

    <nav class="ami-nav">
        <div class="ami-nav-label">Menu</div>
        <?php $current_group = ''; ?>
        <?php foreach ($current_menus as $menu): ?>
            <?php if (isset($menu['group']) && $menu['group'] !== $current_group): ?>
                <?php $current_group = $menu['group']; ?>
                <div class="ami-nav-label"><?php echo html_escape($current_group); ?></div>
            <?php endif; ?>
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
        <?php echo form_open('auth/logout'); ?>
            <button type="submit" class="ami-nav-link w-100 border-0 bg-transparent text-left">
                <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
                <span>Logout</span>
            </button>
        <?php echo form_close(); ?>
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
                <h1 class="ami-page-title"><?php echo html_escape($page_title ?? 'Dashboard'); ?></h1>
                <div class="ami-page-subtitle"><?php echo html_escape($page_subtitle ?? ''); ?></div>
            </div>
        </div>
        <div class="ami-topbar-actions">
            <button type="button" class="ami-theme-toggle" data-theme-toggle aria-label="Aktifkan mode terang" title="Ubah tema">
                <i class="fas fa-sun" data-theme-icon aria-hidden="true"></i>
                <span class="d-none d-sm-inline" data-theme-label>Mode terang</span>
            </button>
            <div class="dropdown">
                <button type="button" class="ami-account-toggle" id="account-menu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="Menu akun">
                    <span class="ami-avatar ami-topbar-avatar avatar-<?php echo html_escape($role); ?>">
                        <?php if ($profile_photo_path): ?>
                            <img src="<?php echo site_url('account/photo'); ?>" alt="" class="ami-avatar-image">
                        <?php else: ?>
                            <?php echo html_escape($initial); ?>
                        <?php endif; ?>
                    </span>
                    <span class="d-none d-md-inline"><?php echo html_escape($nama); ?></span>
                    <i class="fas fa-chevron-down" aria-hidden="true"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-right ami-account-menu" aria-labelledby="account-menu">
                    <a class="dropdown-item" href="<?php echo site_url('account'); ?>"><i class="fas fa-user-circle" aria-hidden="true"></i>Akun Saya</a>
                    <div class="dropdown-divider"></div>
                    <?php echo form_open('auth/logout', ['class' => 'mb-0']); ?>
                        <button type="submit" class="dropdown-item"><i class="fas fa-sign-out-alt" aria-hidden="true"></i>Logout</button>
                    <?php echo form_close(); ?>
                </div>
            </div>
        </div>
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
